<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auto_parts_demo_bootstrap — creates a disposable local Automotive Parts &
 * Accessories store so the preset can be walked through in a real browser.
 *
 *   php index.php auto_parts_demo_bootstrap run    # store 9996 + owner login + seed
 *   php index.php auto_parts_demo_bootstrap clean  # remove everything created here
 *
 * Login (local only): asahiauto / martpoint123
 */
class Auto_parts_demo_bootstrap extends CI_Controller {

	const STORE_ID = 9996;
	const USERNAME = 'asahiauto';

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
	}

	public function run(){
		$sid = self::STORE_ID;

		$store = $this->db->where('id', $sid)->get('db_store')->row();
		if(!$store){
			$ngn = $this->db->select('id')->where('currency_code', 'NGN')->get('db_currency')->row();
			$cur_id = $ngn ? (int)$ngn->id : (int)($this->db->select('id')->where('status', 1)->limit(1)->get('db_currency')->row()->id ?? 0);
			$this->db->insert('db_store', ['id'=>$sid, 'store_name'=>'Asahi Auto Parts Demo', 'user_id'=>0, 'status'=>1, 'language_id'=>1, 'currency_id'=>$cur_id, 'currency_placement'=>'Left', 'date_format'=>'dd-mm-yyyy', 'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s')]);
			echo "created store $sid\n";
		} else {
			echo "store $sid exists\n";
		}

		$this->load->model('default_data_model', 'seeder');
		$this->seeder->seed_store_defaults($sid);
		echo "store defaults seeded\n";

		$hq = $this->db->where('store_id', $sid)->order_by('id', 'asc')->get('db_warehouse')->row();
		if(!$hq){
			$this->db->insert('db_warehouse', [
				'store_id' => $sid, 'warehouse_type' => 'System', 'warehouse_name' => 'Depot-A',
				'branch_type' => 'branch', 'is_distribution_center' => 0, 'status' => 1,
				'delivery_fee' => 0, 'pickup_enabled' => 1, 'public_enabled' => 1,
			]);
			$hq = $this->db->where('id', $this->db->insert_id())->get('db_warehouse')->row();
			echo "depot created id={$hq->id}\n";
		}

		$role = $this->db->select('id, role_name')->where('store_id', $sid)
			->where('UPPER(role_name)', 'BUSINESS OWNER')->where('status', 1)
			->limit(1)->get('db_roles')->row();
		if(!$role){ show_error('Business Owner role missing for demo store', 500); }
		$user = $this->db->where('store_id', $sid)->where('username', self::USERNAME)->get('db_users')->row();
		if(!$user){
			$next = (int)$this->db->select('COALESCE(MAX(id),0)+1 n')->get('db_users')->row()->n;
			$this->db->insert('db_users', [
				'id'=>$next, 'store_id'=>$sid, 'username'=>self::USERNAME,
				'first_name'=>'Asahi', 'last_name'=>'Auto Parts',
				'email'=>'asahiauto@demo.local',
				'password'=>password_hash('martpoint123', PASSWORD_BCRYPT),
				'role_id'=>(int)$role->id, 'role_name'=>$role->role_name,
				'default_warehouse_id'=>(int)$hq->id,
				'status'=>1, 'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'), 'created_by'=>'auto_parts_demo_bootstrap',
			]);
			$user = $this->db->where('id', $next)->get('db_users')->row();
			$nid = (int)$this->db->select('COALESCE(MAX(id),0)+1 n')->get('db_userswarehouses')->row()->n;
			$this->db->insert('db_userswarehouses', ['id'=>$nid, 'user_id'=>$user->id, 'warehouse_id'=>$hq->id]);
			echo "owner user created id={$user->id}\n";
		} else {
			echo "owner user exists id={$user->id}\n";
		}
		$this->db->where('id', $sid)->update('db_store', ['user_id'=>(int)$user->id]);

		// Apply the auto_parts preset exactly as Business Profile save does.
		$this->load->model('business_profile_model', 'bp_model');
		$presets = mp_get_business_presets();
		if(empty($presets['auto_parts'])){ show_error('auto_parts preset missing', 500); }
		$preset = $presets['auto_parts'];
		$flags = [];
		foreach(mp_get_feature_flags() as $key=>$label){
			$flags[$key] = in_array($key, $preset['features'] ?? [], true) ? '1' : '0';
		}
		$this->bp_model->update_profile($sid, [
			'industry_type'=>'auto_parts',
			'business_model'=>$preset['business_model'] ?? 'product_based',
			'workflow_template_key'=>$preset['workflow_template'] ?? 'retail_standard',
			'dashboard_template_key'=>$preset['dashboard_template'] ?? 'general_retail',
			'storefront_theme_key'=>$preset['theme_key'] ?? 'general_retail',
			'feature_flags_json'=>json_encode($flags),
			'label_overrides_json'=>!empty($preset['labels']) ? json_encode($preset['labels']) : null,
			'industry_settings_json'=>null,
		]);
		$this->seeder->seed_industry_defaults($sid, 'auto_parts');
		echo "auto_parts profile applied and catalogue seeded\n";

		$this->_demo_catalogue($sid, $hq);
		$this->_demo_workshop($sid);

		$profile = mp_get_store_profile($sid);
		echo "profile: industry=".$profile['industry_type']." business_model=".($profile['business_model'] ?? '?')."\n";
		foreach(['automobile_workflow','item_variants','serial_number_tracking','warranty_tracking','service_workflow','auto_parts'] as $f){
			echo "  feature $f: ".(mp_feature_enabled_for_store($f, $sid) ? 'ON' : 'off')."\n";
		}
		echo "LOGIN: ".self::USERNAME." / martpoint123\n";
	}

	/** A few real parts with OEM numbers, each stocked in the depot. */
	private function _demo_catalogue($sid, $hq){
		$unit = $this->db->where('store_id', $sid)->order_by('id', 'asc')->get('db_units')->row();
		$cats = [];
		foreach($this->db->where('store_id', $sid)->where('status', 1)->get('db_category')->result() as $c){
			$cats[strtoupper(trim($c->category_name))] = (int)$c->id;
		}
		$brands = [];
		if($this->db->table_exists('db_brands')){
			foreach($this->db->where('store_id', $sid)->where('status', 1)->get('db_brands')->result() as $b){
				$brands[strtoupper(trim($b->brand_name))] = (int)$b->id;
			}
		}
		$parts = [
			['Oil Filter', 'ENGINE PARTS', 'BOSCH', '0986AF0201', '90915-YZZE1', 'Fram PH2875, Wix 51356', 4500, 8],
			['Brake Pad Set Front', 'BRAKES', 'BOSCH', '0986494087', '04465-33471', 'Fit NV200, Sentra', 18500, 6],
			['Air Filter', 'ENGINE PARTS', 'DENSO', 'DCA-801', '13780-54010', 'Masuma FSA0025H', 6500, 12],
			['Alternator Belt', 'ELECTRICAL', '', 'PK-850', '87865-B2000', '', 9500, 5],
		];
		$count = (int)$this->db->where('store_id', $sid)->count_all_results('db_items');
		$made = 0;
		foreach($parts as $p){
			list($name, $cat_key, $brand_key, $part_no, $oem, $xref, $price, $qty) = $p;
			$exists = $this->db->where('store_id', $sid)->where('item_name', $name)->get('db_items')->row();
			if($exists){
				$item_id = (int)$exists->id;
				// The catalogue screens and the phone till both hide NULL
				// item_group rows out of the variant filter; write it like the
				// item form does.
				if(($exists->item_group ?? '') === ''){
					$this->db->where('id', $item_id)->update('db_items', ['item_group' => 'Single']);
				}
			}
			else {
				$count++;
				$this->db->insert('db_items', [
					'store_id'=>$sid, 'count_id'=>$count,
					'item_code'=>'IT/'.$sid.'/'.$count,
					'item_name'=>$name,
					'category_id'=>(int)($cats[$cat_key] ?? 0),
					'sku'=>'ASAHI-'.$sid.'-'.$count,
					'unit_id'=>$unit ? (int)$unit->id : null,
					'brand_id'=>isset($brands[$brand_key]) ? $brands[$brand_key] : null,
					'price'=>$price, 'purchase_price'=>$price, 'sales_price'=>$price, 'mrp'=>$price,
					'stock'=>$qty, 'status'=>1, 'product_type'=>'physical', 'service_bit'=>0,
					'item_group'=>'Single',
					'discount_type'=>'Percentage', 'discount'=>0,
					'manufacturer_part_no'=>$part_no, 'oem_reference'=>$oem, 'cross_reference'=>$xref,
					'description'=>'Demo '.$name.' for the Automotive Parts preset.',
					'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'), 'created_by'=>'auto_parts_demo_bootstrap',
				]);
				$item_id = (int)$this->db->insert_id();
				$made++;
			}
			$stock_row = $this->db->where('store_id', $sid)->where('warehouse_id', $hq->id)->where('item_id', $item_id)->get('db_warehouseitems')->row();
			if(!$stock_row){
				$this->db->insert('db_warehouseitems', ['store_id'=>$sid, 'warehouse_id'=>$hq->id, 'item_id'=>$item_id, 'available_qty'=>(float)$qty]);
			}
			$this->_opening_stock($sid, $hq, $item_id, $qty, $name);
		}
		echo "demo parts created: $made, depot stock rows ensured\n";
	}

	/**
	 * Opening stock must be a real adjustment document: db_items.stock is derived
	 * (adjustments + receipts - sales), so setting it on the item row alone makes
	 * the first sale drive the figure negative.
	 */
	private function _opening_stock($sid, $hq, $item_id, $qty, $name){
		$already = $this->db->where('store_id', $sid)->where('item_id', $item_id)
			->where('description', 'Opening stock — '.$name)->get('db_stockadjustmentitems')->row();
		if(!$already){
			$header = $this->db->where('store_id', $sid)
				->where('adjustment_note', 'Demo opening stock (auto_parts store)')->get('db_stockadjustment')->row();
			$adjustment_id = $header ? (int)$header->id : 0;
			if(!$adjustment_id){
				$this->db->insert('db_stockadjustment', [
					'store_id'=>$sid, 'warehouse_id'=>(int)$hq->id,
					'reference_no'=>'SA-'.$sid.'-DEMO', 'adjustment_date'=>date('Y-m-d'),
					'adjustment_note'=>'Demo opening stock (auto_parts store)',
					'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'),
					'created_by'=>'auto_parts_demo_bootstrap', 'status'=>1,
				]);
				$adjustment_id = (int)$this->db->insert_id();
			}
			$this->db->insert('db_stockadjustmentitems', [
				'store_id'=>$sid, 'warehouse_id'=>(int)$hq->id, 'adjustment_id'=>$adjustment_id,
				'item_id'=>$item_id, 'adjustment_qty'=>(float)$qty,
				'description'=>'Opening stock — '.$name, 'status'=>1,
			]);
		}
		$this->load->model('pos_model');
		$this->pos_model->update_items_quantity($item_id);
		// Session-free recompute: the generic helper resolves the store from the
		// session, which CLI requests do not have.
		update_warehousewise_items_qty($item_id, (int)$hq->id, $sid);
	}

	/** One counter customer with a vehicle, plus fitment knowledge on two parts. */
	private function _demo_workshop($sid){
		$customer = $this->db->where('store_id', $sid)->where('customer_name', 'Kelvin Ogede')->get('db_customers')->row();
		if(!$customer){
			$count = (int)$this->db->where('store_id', $sid)->count_all_results('db_customers') + 1;
			$this->db->insert('db_customers', [
				'store_id'=>$sid, 'count_id'=>$count, 'customer_code'=>'CU/'.$sid.'/'.$count,
				'customer_name'=>'Kelvin Ogede', 'mobile'=>'08030000000', 'status'=>1, 'delete_bit'=>0,
				'opening_balance'=>0, 'sales_due'=>0, 'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'),
				'created_by'=>'auto_parts_demo_bootstrap',
			]);
			$customer = $this->db->where('id', $this->db->insert_id())->get('db_customers')->row();
			echo "demo customer created id={$customer->id}\n";
		}
		$vehicle = $this->db->where('store_id', $sid)->where('customer_id', (int)$customer->id)
			->where('license_plate', 'KPO-4411')->get('db_vehicles')->row();
		if(!$vehicle){
			$this->db->insert('db_vehicles', [
				'store_id'=>$sid, 'vehicle_code'=>'VH-ASAHI-1', 'make'=>'Toyota', 'model'=>'Corolla',
				'year'=>2018, 'mileage'=>96500, 'vehicle_condition'=>'used', 'license_plate'=>'KPO-4411',
				'status'=>'available', 'customer_id'=>(int)$customer->id, 'price'=>0, 'cost'=>0,
				'amount_paid'=>0, 'is_deleted'=>0, 'created_date'=>date('Y-m-d'), 'created_by'=>'auto_parts_demo_bootstrap',
			]);
			$vehicle = $this->db->where('id', $this->db->insert_id())->get('db_vehicles')->row();
			echo "demo vehicle created id={$vehicle->id}\n";
		}

		foreach($this->db->where('store_id', $sid)->where('created_by', 'auto_parts_demo_bootstrap')->get('db_items')->result() as $item){
			$has = $this->db->where('store_id', $sid)->where('item_id', (int)$item->id)
				->where('model', 'Corolla')->get('db_item_vehicle_compat')->row();
			if($has) continue;
			$this->db->insert('db_item_vehicle_compat', [
				'store_id'=>$sid, 'item_id'=>(int)$item->id, 'make'=>'Toyota', 'model'=>'Corolla',
				'year_from'=>2014, 'year_to'=>2021, 'note'=>'Fits 1.8L petrol',
				'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'),
			]);
		}

		// One past fitting visit with a service due soon, so the history page and
		// the reminder screen have real data to show (reminders stay switched off).
		if($vehicle && $this->db->table_exists('db_vehicle_service_records')){
			$has_record = $this->db->where('store_id', $sid)->where('vehicle_id', (int)$vehicle->id)
				->where('created_by', 'auto_parts_demo_bootstrap')->get('db_vehicle_service_records')->row();
			if(!$has_record){
				$this->db->insert('db_vehicle_service_records', [
					'store_id'=>$sid, 'vehicle_id'=>(int)$vehicle->id, 'customer_id'=>(int)$customer->id,
					'service_type'=>'Oil & Filter Change', 'service_date'=>date('Y-m-d', strtotime('-4 months')),
					'mileage_at_service'=>91500, 'technician_name'=>'Idris Bello',
					'notes'=>'Demo service record from the Automotive Parts preset store.',
					'next_due_date'=>date('Y-m-d', strtotime('+21 days')), 'next_due_mileage'=>96500,
					'is_deleted'=>0, 'created_by'=>'auto_parts_demo_bootstrap',
					'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'),
				]);
				echo "demo service record created\n";
			}
		}
		echo "fitment rows: ".$this->db->where('store_id', $sid)->count_all_results('db_item_vehicle_compat')."\n";
	}

	public function clean(){
		$sid = self::STORE_ID;
		$tables = $this->db->list_tables();
		$dropped = 0;
		foreach($tables as $t){
			if($t === 'db_store') continue;
			if(!$this->db->field_exists('store_id', $t)) continue;
			$this->db->where('store_id', $sid)->delete($t);
			$dropped++;
		}
		foreach($this->db->where('store_id', $sid)->get('db_users')->result() as $u){
			$this->db->where('user_id', $u->id)->delete('db_userswarehouses');
			$this->db->where('id', $u->id)->delete('db_users');
		}
		$this->db->where('id', $sid)->delete('db_store');
		echo "cleaned store $sid from $dropped tenant tables\n";
	}
}
