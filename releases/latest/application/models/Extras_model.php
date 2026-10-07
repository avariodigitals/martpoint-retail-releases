<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Phase 4 commerce extensions: composite bundles, product/service add-ons,
 * checkout upsells and per-item quantity rules.
 *
 * Bundle semantics (documented scope):
 *  - A bundle is a db_items row with is_bundle=1 selling a fixed set of
 *    component items. It is NOT a variant parent (item_group='Variants').
 *  - bundle_pricing 'fixed'      -> the bundle sells at its own sales price;
 *                                   component prices only feed cost/reporting.
 *  - bundle_pricing 'components' -> the unit price is the sum of each
 *                                   component's effective price x its per-
 *                                   bundle qty, re-resolved server-side.
 *  - The bundle row itself never holds sellable stock. Availability is
 *    min(floor(component_stock / per-bundle qty)) across components.
 *  - Consumption always goes through the stock-adjustment ledger
 *    (db_stockadjustment/db_stockadjustmentitems) so ledger-driven stock
 *    recomputes (update_items_quantity) keep the deduction instead of
 *    silently restoring it.
 */
class Extras_model extends CI_Model {

	// ---------------- Quantity rules ----------------

	/**
	 * Validate a purchase qty against an item's min/max/step rules.
	 * Returns null when acceptable, or a shopper-facing message.
	 * Scope: per item — a variant child is its own db_items row, so rules set
	 * on a child govern that variant; rules on a variant parent do not cascade.
	 */
	public function qtyRuleError($item, $qty){
		if(!$item) return null;
		$qty = (float)$qty;
		$min = (float)($item->min_order_qty ?? 0);
		$max = (float)($item->max_order_qty ?? 0);
		$step = (float)($item->qty_step ?? 0);
		$name = $item->item_name ?? 'This item';
		if($min > 0 && $qty < $min){
			return "$name requires a minimum quantity of " . rtrim(rtrim(number_format($min,2),'0'),'.') . ".";
		}
		if($max > 0 && $qty > $max){
			return "$name is limited to " . rtrim(rtrim(number_format($max,2),'0'),'.') . " per order.";
		}
		if($step > 0){
			// Step counts from the minimum when one is set, else from 0.
			$base = ($min > 0) ? $min : 0;
			$rem = fmod(round($qty - $base, 4), $step);
			if(abs($rem) > 0.0001 && abs($rem - $step) > 0.0001){
				return "$name must be ordered in multiples of " . rtrim(rtrim(number_format($step,2),'0'),'.') . ".";
			}
		}
		return null;
	}

	// ---------------- Bundles ----------------

	public function getBundleComponents($bundleItemId, $storeId){
		return $this->db->select('c.id, c.component_item_id, c.qty, i.item_name, i.sales_price, i.online_price, i.discount, i.discount_type, i.stock, i.item_image, i.service_bit, i.status AS item_status, i.publish_online')
			->from('db_bundle_components c')
			->join('db_items i', 'i.id = c.component_item_id', 'left')
			->where('c.bundle_item_id', (int)$bundleItemId)
			->where('c.store_id', (int)$storeId)
			->get()->result();
	}

	/** Replace a bundle's component set. Components are validated for store scope. */
	public function saveBundleComponents($bundleItemId, array $components, $storeId){
		$bundleItemId = (int)$bundleItemId; $storeId = (int)$storeId;
		$bundle = $this->db->where('id', $bundleItemId)->where('store_id', $storeId)->where('is_bundle', 1)->get('db_items')->row();
		if(!$bundle) return false;
		$validated = [];
		$seen = [];
		foreach($components as $c){
			$cid = (int)($c['component_item_id'] ?? 0);
			$qty = (float)($c['qty'] ?? 0);
			if($cid <= 0 || $qty <= 0 || $cid === $bundleItemId || isset($seen[$cid])) return false;
			$exists = $this->db->where('id', $cid)->where('store_id', $storeId)->where('status', 1)->get('db_items')->row();
			if(!$exists || !empty($exists->is_bundle)) return false;
			$seen[$cid] = true;
			$validated[] = [
				'store_id' => $storeId,
				'bundle_item_id' => $bundleItemId,
				'component_item_id' => $cid,
				'qty' => $qty,
				'created_at' => date('Y-m-d H:i:s'),
			];
		}
		$this->db->trans_begin();
		$this->db->where('bundle_item_id', $bundleItemId)->where('store_id', $storeId)->delete('db_bundle_components');
		foreach($validated as $row){
			if(!$this->db->insert('db_bundle_components', $row)){
				$this->db->trans_rollback();
				return false;
			}
		}
		if($this->db->trans_status() === FALSE){ $this->db->trans_rollback(); return false; }
		$this->db->trans_commit();
		return true;
	}

	public function deleteBundleComponent($componentRowId, $storeId){
		return $this->db->where('id', (int)$componentRowId)->where('store_id', (int)$storeId)->delete('db_bundle_components');
	}

	/**
	 * Sellable bundle quantity = min over components of floor(stock / per-qty).
	 * Components with service_bit=1 (non-stocked) do not constrain.
	 * Returns null when the item is not a bundle or has no components
	 * (an unconfigured bundle is treated as unsellable online).
	 */
	public function bundleAvailability($bundleItemId, $storeId, $warehouseId = null){
		$components = $this->getBundleComponents($bundleItemId, $storeId);
		if(empty($components)) return null;
		$avail = null;
		foreach($components as $c){
			if((int)($c->service_bit ?? 0) === 1) continue;
			$per = (float)$c->qty;
			if($per <= 0) continue;
			$componentStock = (float)$c->stock;
			if($warehouseId && function_exists('total_available_qty_items_of_warehouse')){
				$componentStock = (float)total_available_qty_items_of_warehouse($warehouseId, $storeId, (int)$c->component_item_id);
			}
			$can = floor($componentStock / $per);
			$avail = ($avail === null) ? $can : min($avail, $can);
		}
		return $avail === null ? 999999 : (int)$avail;
	}

	/**
	 * Server-side bundle unit price. 'components' pricing sums each
	 * component's effective storefront/POS price x per-bundle qty.
	 */
	public function bundleUnitPrice($bundleItem, $storeId){
		$bundleItem = is_array($bundleItem) ? (object)$bundleItem : $bundleItem;
		if(!$bundleItem) return 0.0;
		if(($bundleItem->bundle_pricing ?? 'fixed') !== 'components'){
			return round(max(0.0, (float)($bundleItem->sales_price ?? 0.0)), 2);
		}
		$sum = 0.0;
		foreach($this->getBundleComponents((int)($bundleItem->id ?? 0), (int)$storeId) as $c){
			$component = is_object($c) ? $c : (object)$c;
			$price = (!empty($component->online_price) && (float)$component->online_price > 0) ? (float)$component->online_price : (float)($component->sales_price ?? 0);
			if(!empty($component->discount) && (float)$component->discount > 0){
				$discountType = strtolower((string)($component->discount_type ?? ''));
				$price = ($discountType === 'percentage') ? $price - ($price * ((float)$component->discount / 100)) : $price - (float)$component->discount;
			}
			$sum += max(0.0, (float)$price) * (float)($component->qty ?? 0);
		}
		return round($sum, 2);
	}

	/**
	 * Deduct bundle components through the stock-adjustment ledger for a POS
	 * sale. Mirrors deplete_recipe_at_sale: one adjustment document per sale,
	 * one line per component, then stock recompute. Runs inside the sale
	 * transaction — a failure rolls back the whole sale.
	 */
	public function deplete_bundle_at_sale($item_id, $sales_qty, $sales_id, $store_id, $warehouse_id){
		$item = $this->db->where('id', (int)$item_id)->get('db_items')->row();
		if(!$item || empty($item->is_bundle)) return true;
		$components = $this->getBundleComponents($item_id, $store_id);
		if(empty($components)) return true;

		$adj = [
			'store_id' => $store_id,
			'warehouse_id' => $warehouse_id,
			'reference_no' => 'SALE-' . $sales_id,
			'adjustment_date' => date('Y-m-d'),
			'adjustment_note' => 'Bundle components for ' . $item->item_name,
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => 'POS',
			'system_ip' => '127.0.0.1',
			'system_name' => 'POS',
			'status' => 1,
		];
		if(!$this->db->insert('db_stockadjustment', $adj)) return false;
		$adjustment_id = $this->db->insert_id();

		foreach($components as $c){
			if((int)($c->service_bit ?? 0) === 1) continue;
			$deduct = (float)$c->qty * (float)$sales_qty;
			if($deduct <= 0) continue;
			$this->db->set('stock', 'stock - ' . $deduct, false);
			$this->db->where('id', (int)$c->component_item_id)->where('stock >=', $deduct)->update('db_items');
			if($this->db->affected_rows() !== 1) return false;
			$ok = $this->db->insert('db_stockadjustmentitems', [
				'store_id' => $store_id,
				'warehouse_id' => $warehouse_id,
				'adjustment_id' => $adjustment_id,
				'item_id' => (int)$c->component_item_id,
				'adjustment_qty' => -$deduct,
				'description' => 'Sale #' . $sales_id . ' / ' . $item->item_name . ' x' . $sales_qty,
				'status' => 1,
			]);
			if(!$ok) return false;
		}
		foreach($components as $c){
			if((int)($c->service_bit ?? 0) === 1) continue;
			$this->load->model('pos_model');
			if(!$this->pos_model->update_items_quantity((int)$c->component_item_id)) return false;
		}
		return true;
	}

	/**
	 * Atomic component reservation for a storefront order. Runs inside the
	 * caller's transaction. Each component stock is decremented with a
	 * stock>=need guard; the matching ledger rows are written so a later
	 * ledger recompute does not erase the reservation. On any shortage the
	 * component units already held by this call are put back and the caller
	 * rolls back (which also removes the ledger rows).
	 * Returns the component draw list used for the order's snapshot lines.
	 */
	public function reserveBundleComponents(array $draws, $orderId, $allowBackorder = false, $storeId = null, $warehouseId = null){
		$reserved = [];
		$storeId = $storeId ?: $this->db->where('id', (int)$orderId)->get('db_online_orders')->row()->store_id ?? null;
		$warehouseId = $warehouseId ?: (function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null);
		if(empty($draws) || !$orderId) return $reserved;

		$this->db->trans_begin();
		$headerInserted = $this->db->insert('db_stockadjustment', [
			'store_id' => $storeId,
			'warehouse_id' => $warehouseId,
			'reference_no' => 'ONLINE-ORDER-' . (int)$orderId,
			'adjustment_date' => date('Y-m-d'),
			'adjustment_note' => 'Bundle reservation for online order #' . (int)$orderId,
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => 'Storefront',
			'system_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
			'system_name' => 'Storefront',
			'status' => 1,
		]);
		if(!$headerInserted){ $this->db->trans_rollback(); return false; }
		$adjustmentId = (int)$this->db->insert_id();

		foreach($draws as $d){
			$itemId = (int)($d['item_id'] ?? 0);
			$need = (float)($d['qty'] ?? 0);
			if($itemId <= 0 || $need <= 0) continue;

			$this->db->set('stock', 'stock - ' . $need, false);
			$this->db->where('id', $itemId);
			if(!$allowBackorder) $this->db->where('stock >=', $need);
			$this->db->update('db_items');
			if(!$allowBackorder && $this->db->affected_rows() !== 1){
				foreach($reserved as $r){
					$this->db->set('stock', 'stock + ' . $r['qty'], false);
					$this->db->where('id', $r['item_id'])->update('db_items');
				}
				$this->db->trans_rollback();
				return false;
			}

			$this->db->insert('db_stockadjustmentitems', [
				'store_id' => $storeId,
				'warehouse_id' => $warehouseId,
				'adjustment_id' => $adjustmentId,
				'item_id' => $itemId,
				'adjustment_qty' => -$need,
				'description' => 'Online order #'.$orderId.' bundle reservation',
				'status' => 1,
			]);
			if($this->db->affected_rows() !== 1){
				foreach($reserved as $r){
					$this->db->set('stock', 'stock + ' . $r['qty'], false);
					$this->db->where('id', $r['item_id'])->update('db_items');
				}
				$this->db->trans_rollback();
				return false;
			}
			$reserved[] = ['item_id' => $itemId, 'qty' => $need];
		}
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();
		return $reserved;
	}

	// ---------------- Add-ons ----------------

	public function getAddonsForItem($itemId, $storeId, $activeOnly = true){
		$q = $this->db->where('store_id', (int)$storeId)->where('item_id', (int)$itemId);
		if($activeOnly) $q->where('is_active', 1);
		return $q->order_by('sort_order', 'asc')->order_by('id', 'asc')->get('db_item_addons')->result();
	}

	public function getAddonsForService($serviceId, $storeId, $activeOnly = true){
		$q = $this->db->where('store_id', (int)$storeId)->where('service_id', (int)$serviceId);
		if($activeOnly) $q->where('is_active', 1);
		return $q->order_by('sort_order', 'asc')->order_by('id', 'asc')->get('db_item_addons')->result();
	}

	public function saveAddon(array $data, $storeId, $addonId = null){
		$storeId = (int)$storeId;
		$parentType = ($data['parent_type'] ?? '') === 'service' ? 'service' : 'product';
		$parentId = (int)($data['parent_id'] ?? 0);
		$name = trim((string)($data['name'] ?? ''));
		$price = (float)($data['price'] ?? 0);
		$maxQty = (int)($data['max_qty'] ?? 1);
		$linkedId = (int)($data['linked_item_id'] ?? 0);
		if($parentId <= 0 || $name === '' || mb_strlen($name) > 150 || $price < 0 || $maxQty < 1) return false;
		$parentTable = $parentType === 'service' ? 'db_services' : 'db_items';
		$parent = $this->db->where('id', $parentId)->where('store_id', $storeId)->get($parentTable)->row();
		if(!$parent) return false;
		if($linkedId){
			$linked = $this->db->where('id', $linkedId)->where('store_id', $storeId)->where('status', 1)->where('service_bit', 0)->get('db_items')->row();
			if(!$linked || !empty($linked->is_bundle)) return false;
		}
		$row = [
			'store_id' => $storeId,
			'item_id' => $parentType === 'product' ? $parentId : null,
			'service_id' => $parentType === 'service' ? $parentId : null,
			'linked_item_id' => $linkedId ?: null,
			'name' => $name,
			'price' => $price,
			'max_qty' => $maxQty,
			'is_active' => !empty($data['is_active']) ? 1 : 0,
		];
		if($addonId){
			return $this->db->where('id', (int)$addonId)->where('store_id', $storeId)->update('db_item_addons', $row);
		}
		$row['created_at'] = date('Y-m-d H:i:s');
		return $this->db->insert('db_item_addons', $row);
	}

	public function deleteAddon($addonId, $storeId){
		return $this->db->where('id', (int)$addonId)->where('store_id', (int)$storeId)->delete('db_item_addons');
	}

	public function saveUpsell(array $data, $storeId, $upsellId = null){
		$storeId = (int)$storeId;
		$triggerId = (int)($data['trigger_item_id'] ?? 0);
		$upsellItemId = (int)($data['upsell_item_id'] ?? 0);
		if($upsellItemId <= 0 || $upsellItemId === $triggerId) return false;
		$upsell = $this->db->where('id', $upsellItemId)->where('store_id', $storeId)->where('status', 1)->where('publish_online', 1)->where('service_bit', 0)->get('db_items')->row();
		if(!$upsell) return false;
		if($triggerId){
			$trigger = $this->db->where('id', $triggerId)->where('store_id', $storeId)->where('status', 1)->get('db_items')->row();
			if(!$trigger) return false;
		}
		$row = ['store_id' => $storeId, 'trigger_item_id' => $triggerId ?: null, 'upsell_item_id' => $upsellItemId, 'sort_order' => max(0, (int)($data['sort_order'] ?? 0)), 'is_active' => !empty($data['is_active']) ? 1 : 0];
		if($upsellId) return $this->db->where('id', (int)$upsellId)->where('store_id', $storeId)->update('db_item_upsells', $row);
		return $this->db->insert('db_item_upsells', $row);
	}

	public function deleteUpsell($upsellId, $storeId){
		return $this->db->where('id', (int)$upsellId)->where('store_id', (int)$storeId)->delete('db_item_upsells');
	}

	public function saveQuantityRules($itemId, $storeId, $min, $max, $step){
		$itemId = (int)$itemId; $storeId = (int)$storeId;
		$min = (float)$min; $max = (float)$max; $step = (float)$step;
		if($itemId <= 0 || $min < 0 || $max < 0 || $step < 0 || ($min > 0 && $max > 0 && $max < $min)) return false;
		return $this->db->where('id', $itemId)->where('store_id', $storeId)->update('db_items', [
			'min_order_qty' => $min > 0 ? $min : null,
			'max_order_qty' => $max > 0 ? $max : null,
			'qty_step' => $step > 0 ? $step : null,
		]);
	}

	/**
	 * Re-resolve posted add-on selections against the store's own rows.
	 * $selections: [{id, qty}] from the cart line. Prices and stock linkage
	 * come only from db_item_addons — client-sent prices are ignored.
	 * Returns [rows, error].
	 */
	public function resolveAddonSelections($parentType, $parentId, array $selections, $storeId){
		$resolved = [];
		$seen = [];
		foreach($selections as $sel){
			$aid = (int)($sel['id'] ?? 0);
			$qty = (int)($sel['qty'] ?? 1);
			if($aid <= 0) continue;
			if(isset($seen[$aid])) return [null, 'An extra was selected more than once.'];
			$seen[$aid] = true;
			$addon = $this->db->where('id', $aid)->where('store_id', (int)$storeId)->where('is_active', 1)->get('db_item_addons')->row();
			if(!$addon) return [null, 'One of the selected extras is not available.'];
			if($parentType === 'service'){
				if((int)$addon->service_id !== (int)$parentId) return [null, 'An extra does not belong to the selected service.'];
			} else {
				if((int)$addon->item_id !== (int)$parentId) return [null, 'An extra does not belong to the selected product.'];
			}
			if($qty < 1 || $qty > max(1, (int)$addon->max_qty)){
				return [null, 'Extra "' . $addon->name . '" is limited to ' . (int)$addon->max_qty . '.'];
			}
			if((float)$addon->price < 0){
				return [null, 'One of the selected extras has an invalid price.'];
			}
			if(!empty($addon->linked_item_id)){
				$linked = $this->db->where('id', (int)$addon->linked_item_id)
					->where('store_id', (int)$storeId)->where('status', 1)->where('service_bit', 0)
					->get('db_items')->row();
				if(!$linked || !empty($linked->is_bundle)) return [null, 'The inventory for one of the selected extras is unavailable.'];
				$addon->linked_item = $linked;
			}
			$addon->resolved_qty = $qty;
			$resolved[] = $addon;
		}
		return [$resolved, null];
	}

	// ---------------- Upsells ----------------

	/**
	 * Live upsell suggestions for a cart: configured triggers matching the
	 * cart's items (or cart-wide), resolved to sellable online products that
	 * are not already in the cart and (for physical goods) in stock.
	 * Variant parents are excluded — shoppers must buy a concrete variant.
	 */
	public function getUpsellsForCart(array $cartItemIds, $storeId, $limit = 4){
		if(!$this->db->table_exists('db_item_upsells')) return [];
		$cartItemIds = array_values(array_filter(array_map('intval', $cartItemIds)));
		$this->db->select('u.upsell_item_id, MIN(u.sort_order) AS sort_order', false)
			->from('db_item_upsells u')
			->where('u.store_id', (int)$storeId)
			->where('u.is_active', 1);

		if(!empty($cartItemIds)){
			$this->db->group_start()
				->where('u.trigger_item_id IS NULL', null, false)
				->or_where_in('u.trigger_item_id', $cartItemIds)
				->group_end();
		} else {
			$this->db->where('u.trigger_item_id IS NULL', null, false);
		}

		if(!empty($cartItemIds)) $this->db->where_not_in('u.upsell_item_id', $cartItemIds);
		$links = $this->db->group_by('u.upsell_item_id')->order_by('sort_order', 'asc')->limit(20)->get()->result();
		if(empty($links)) return [];

		$out = [];
		foreach($links as $l){
			$item = $this->db->where('id', (int)$l->upsell_item_id)
				->where('store_id', (int)$storeId)
				->where('status', 1)
				->where('publish_online', 1)
				->get('db_items')->row();
			if(!$item) continue;
			if(($item->item_group ?? '') === 'Variants') continue; // parent shell
			if(!empty($item->is_bundle)){
				$avail = $this->bundleAvailability($item->id, $storeId);
				if($avail === null || $avail <= 0) continue;
				$item->effective_price = $this->bundleUnitPrice($item, $storeId);
			} else {
				if(($item->product_type ?? 'physical') === 'physical' && (float)$item->stock <= 0) continue;
				$this->load->model('Storefront_model', 'storefront_model');
				$item->effective_price = $this->storefront_model->getProductEffectivePrice($item);
			}
			$out[] = $item;
			if(count($out) >= $limit) break;
		}
		return $out;
	}

	public function getUpsellLinks($storeId){
		return $this->db->select('u.*, t.item_name AS trigger_name, s.item_name AS upsell_name')
			->from('db_item_upsells u')
			->join('db_items t', 't.id = u.trigger_item_id', 'left')
			->join('db_items s', 's.id = u.upsell_item_id', 'left')
			->where('u.store_id', (int)$storeId)
			->order_by('u.trigger_item_id', 'asc')->order_by('u.sort_order', 'asc')
			->get()->result();
	}
}
