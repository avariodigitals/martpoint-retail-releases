<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Isolated customer + partner-hub pharmacy demo.
 *
 * The standalone route is a fictional workflow fixture. The store-backed
 * route below uses the same presentation with a read-only MartPoint bootstrap.
 */
class Pharmacy_demo extends CI_Controller {
    /**
     * Store-backed pharmacy experience. The public pharmacy frontend receives
     * a read-only bootstrap from MartPoint's real catalogue, branches and
     * warehouse ledger; browser state is still only a workflow demo.
     */
    public function store($storeSlug = '', $mode = 'customer') {
        $this->load->model('storefront_model');
        $settings = $this->storefront_model->getStoreBySlug($storeSlug);
        if (!$settings || $settings->store_status !== 'active') { show_404(); return; }

        $storeId = (int)$settings->store_id;
        $profile = function_exists('mp_get_store_profile') ? mp_get_store_profile($storeId) : [];
        $industry = strtolower((string)($profile['industry_type'] ?? ''));
        if (!in_array($industry, ['pharmacy', 'medical_store', 'healthcare'], true) || empty($settings->partner_marketplace_enabled)) {
            show_404();
            return;
        }

        if ($mode === 'hub') {
            $this->session->set_userdata('pharmacy_demo_hub_store', $storeId);
            $partnerOutlet = null;
            foreach ($this->storefront_model->getOrderableOutlets($storeId) as $outlet) if ((string)($outlet->branch_type ?? '') === 'partner') { $partnerOutlet = $outlet; break; }
            if ($partnerOutlet) $this->session->set_userdata('pharmacy_demo_hub_warehouse', (int)$partnerOutlet->id);
        }

        $this->load->helper('image');
        $store = get_store_details($storeId);
        $config = $this->_storeConfig($storeId, $settings, $store);
        $this->load->view('pharmacy_demo/app', [
            'mode' => $mode === 'hub' ? 'hub' : 'customer',
            'page_title' => $mode === 'hub' ? ($store->store_name ?? 'Pharmacy').' partner hub' : ($store->store_name ?? 'Pharmacy'),
            'partner_marketplace_enabled' => 1,
            'pharmacy_config' => $config,
            'pharmacy_base_path' => '/store/'.rawurlencode((string)$settings->store_slug).'/pharmacy',
        ]);
    }

    /** Resolve and validate a tenant pharmacy without exposing a generic store. */
    private function _pharmacySettings($storeSlug) {
        if (!isset($this->storefront_model)) $this->load->model('storefront_model');
        $settings = $this->storefront_model->getStoreBySlug($storeSlug);
        if (!$settings || $settings->store_status !== 'active' || empty($settings->partner_marketplace_enabled)) return null;
        $profile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)$settings->store_id) : [];
        if (!in_array(strtolower((string)($profile['industry_type'] ?? '')), ['pharmacy', 'medical_store', 'healthcare'], true)) return null;
        return $settings;
    }

    private function _json($payload) {
        $payload['csrf_hash'] = $this->security->get_csrf_hash();
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function _customerForRequest($storeId, $name, $phone, $email = '') {
        $customer = $this->db->where('store_id', (int)$storeId)->where('mobile', $phone)->where('delete_bit', 0)->get('db_customers')->row();
        if ($customer) return $customer;
        $count = $this->db->select('COALESCE(MAX(count_id),0)+1 AS next_id')->where('store_id', (int)$storeId)->get('db_customers')->row();
        $this->db->insert('db_customers', array_filter([
            'store_id' => (int)$storeId, 'count_id' => (int)($count->next_id ?? 1),
            'customer_code' => 'WEB/'.str_pad((int)$storeId, 2, '0', STR_PAD_LEFT).'/'.date('ymdHis'),
            'customer_name' => $name, 'customer_type' => 'Retail', 'mobile' => $phone,
            'email' => $email ?: null, 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
            'created_by' => 'online-store', 'status' => 1, 'delete_bit' => 0,
        ], function($value){ return $value !== null; }));
        $customerId = (int)$this->db->insert_id();
        return $customerId ? $this->db->where('id', $customerId)->get('db_customers')->row() : null;
    }

    private function _rxRow($storeId, $id = 0, $reference = '', $phone = '') {
        $this->db->select('n.*, c.customer_name, c.mobile, c.email');
        $this->db->from('db_medical_notes n');
        $this->db->join('db_customers c', 'c.id=n.customer_id', 'left');
        $this->db->where('n.store_id', (int)$storeId);
        if ($id) $this->db->where('n.id', (int)$id);
        else $this->db->where('n.prescription_ref', $reference);
        if ($phone !== '') $this->db->where('c.mobile', $phone);
        return $this->db->get()->row();
    }

    private function _rxPayload($row) {
        $items = [];
        if ($row) {
            $this->db->select('m.*, i.item_name, i.sales_price, i.online_price');
            $this->db->from('db_medical_note_items m');
            $this->db->join('db_items i', 'i.id=m.item_id', 'left');
            $this->db->where('m.medical_note_id', (int)$row->id);
            foreach ($this->db->get()->result() as $item) {
                $price = (float)($item->online_price ?: $item->sales_price);
                $items[] = ['productId' => (string)(int)$item->item_id, 'name' => (string)($item->item_name ?: 'Medicine'), 'qty' => (float)$item->qty, 'unit' => '', 'price' => $price, 'dosage' => (string)($item->dosage ?? ''), 'duration' => (string)($item->duration ?? ''), 'instructions' => (string)($item->instructions ?? '')];
            }
        }
        $status = (string)($row->review_status ?? ($row->status === 'closed' ? 'approved' : 'pending'));
        return ['id' => (int)$row->id, 'reference' => (string)$row->prescription_ref, 'status' => $status, 'fileName' => !empty($row->prescription_file) ? basename($row->prescription_file) : 'No file attached', 'note' => (string)($row->diagnosis ?? ''), 'customer' => (string)($row->customer_name ?? ''), 'phone' => (string)($row->mobile ?? ''), 'locationId' => '', 'approvedItems' => $status === 'approved' ? $items : [], 'approvedTotal' => array_reduce($items, function($sum, $item){ return $sum + ($item['price'] * $item['qty']); }, 0), 'createdAt' => (string)($row->created_date ?? '')];
    }

    /** Public prescription intake. It writes to the pharmacy medical-notes queue. */
    public function prescription_request($storeSlug = '') {
        $settings = $this->_pharmacySettings($storeSlug);
        if (!$settings) { $this->_json(['status' => false, 'message' => 'Pharmacy store not found.']); return; }
        $name = trim((string)$this->input->post('customer_name', TRUE));
        $phone = trim((string)$this->input->post('customer_phone', TRUE));
        $email = trim((string)$this->input->post('customer_email', TRUE));
        if ($name === '' || $phone === '') { $this->_json(['status' => false, 'message' => 'Name and phone are required for prescription review.']); return; }
        $this->load->model('medical_notes_model', 'medical_notes');
        $customer = $this->_customerForRequest((int)$settings->store_id, $name, $phone, $email);
        if (!$customer) { $this->_json(['status' => false, 'message' => 'The patient record could not be created.']); return; }

        $filePath = null;
        if (!empty($_FILES['prescription_file']['name'])) {
            $uploadPath = FCPATH.'uploads/prescriptions/';
            if (!is_dir($uploadPath)) @mkdir($uploadPath, 0775, true);
            $this->load->library('upload', ['upload_path' => $uploadPath, 'allowed_types' => 'jpg|jpeg|png|gif|pdf|webp', 'max_size' => 5120, 'encrypt_name' => true]);
            if (!$this->upload->do_upload('prescription_file')) { $this->_json(['status' => false, 'message' => trim(strip_tags($this->upload->display_errors('', ''))) ?: 'Prescription upload failed.']); return; }
            $filePath = 'uploads/prescriptions/'.$this->upload->data('file_name');
        }
        $reference = 'WEB-RX-'.date('YmdHis').'-'.mt_rand(100, 999);
        $now = date('Y-m-d H:i:s');
        $data = ['store_id' => (int)$settings->store_id, 'customer_id' => (int)$customer->id, 'note_date' => date('Y-m-d'), 'prescribing_doctor' => null, 'diagnosis' => trim((string)$this->input->post('note', TRUE)) ?: 'Online prescription request', 'prescription_ref' => $reference, 'allergies_flagged' => null, 'dosage_instructions' => null, 'counselling_notes' => 'Awaiting pharmacist review.', 'refills_remaining' => 0, 'staff_name' => null, 'status' => 'active', 'review_status' => 'pending', 'review_requested_at' => $now, 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'), 'created_by' => 'online-store'];
        if ($filePath) $data['prescription_file'] = $filePath;
        $data = array_intersect_key($data, array_flip($this->db->list_fields('db_medical_notes')));
        try { $noteId = $this->medical_notes->save($data, null, []); } catch (Exception $e) { $this->_json(['status' => false, 'message' => 'Prescription request could not be recorded.']); return; }
        $row = $this->_rxRow((int)$settings->store_id, $noteId, '', '');
        $this->_json(['status' => true, 'request' => $this->_rxPayload($row)]);
    }

    public function prescription_status($storeSlug = '') {
        $settings = $this->_pharmacySettings($storeSlug);
        if (!$settings) { $this->_json(['status' => false, 'message' => 'Pharmacy store not found.']); return; }
        $phone = trim((string)$this->input->get('phone', TRUE));
        if ($phone === '') { $this->_json(['status' => false, 'message' => 'Phone is required to view a prescription request.']); return; }
        $row = $this->_rxRow((int)$settings->store_id, (int)$this->input->get('id'), trim((string)$this->input->get('reference', TRUE)), $phone);
        if (!$row) { $this->_json(['status' => false, 'message' => 'Prescription request not found.']); return; }
        $this->_json(['status' => true, 'request' => $this->_rxPayload($row)]);
    }

    /** Demo hub approval uses the same record the pharmacist workspace reads. */
    public function prescription_approve($storeSlug = '') {
        $settings = $this->_pharmacySettings($storeSlug);
        if (!$settings) { $this->_json(['status' => false, 'message' => 'Pharmacy store not found.']); return; }
        $storeId = (int)$settings->store_id;
        $demoHub = (int)$this->session->userdata('pharmacy_demo_hub_store') === $storeId;
        $staff = (int)$this->session->userdata('inv_userid') > 0;
        if (!$demoHub && !$staff) { $this->_json(['status' => false, 'message' => 'Open the pharmacy hub or sign in as pharmacy staff to review prescriptions.']); return; }
        $row = $this->_rxRow($storeId, (int)$this->input->post('id', TRUE), '', '');
        if (!$row) { $this->_json(['status' => false, 'message' => 'Prescription request not found.']); return; }
        $this->load->model('medical_notes_model', 'medical_notes');
        $itemId = (int)$this->input->post('item_id', TRUE);
        if (!$itemId) { $defaultItem = $this->db->select('id')->where('store_id', $storeId)->like('item_name', 'Amoxicillin')->where('status', 1)->order_by('id', 'asc')->get('db_items')->row(); $itemId = $defaultItem ? (int)$defaultItem->id : 0; }
        $item = $itemId ? $this->db->where('id', $itemId)->where('store_id', $storeId)->get('db_items')->row() : null;
        if (!$item) { $this->_json(['status' => false, 'message' => 'Choose an approved medicine before confirming review.']); return; }
        $this->db->where('medical_note_id', (int)$row->id)->delete('db_medical_note_items');
        $this->db->insert('db_medical_note_items', ['medical_note_id' => (int)$row->id, 'item_id' => (int)$item->id, 'item_name' => $item->item_name, 'qty' => max(1, (float)$this->input->post('qty', TRUE) ?: 1), 'dosage' => trim((string)$this->input->post('dosage', TRUE)) ?: null, 'duration' => trim((string)$this->input->post('duration', TRUE)) ?: null, 'instructions' => trim((string)$this->input->post('instructions', TRUE)) ?: null]);
        $data = ['review_status' => 'approved', 'reviewed_at' => date('Y-m-d H:i:s'), 'reviewed_by' => $staff ? (int)$this->session->userdata('inv_userid') : null, 'counselling_notes' => 'Reviewed by the pharmacy team. Customer confirmation is required before checkout.'];
        $this->db->where('id', (int)$row->id)->where('store_id', $storeId)->update('db_medical_notes', array_intersect_key($data, array_flip($this->db->list_fields('db_medical_notes'))));
        $this->_json(['status' => true, 'request' => $this->_rxPayload($this->_rxRow($storeId, (int)$row->id, '', ''))]);
    }

    private function _orderPayload($order, $settings) {
        $this->load->model('order_lifecycle_model');
        $location = $this->db->where('id', (int)($order->warehouse_id ?? 0))->where('store_id', (int)$order->store_id)->get('db_warehouse')->row();
        $store = $this->db->select('store_name')->where('id', (int)$order->store_id)->get('db_store')->row();
        $sellerName = $store ? (string)$store->store_name : 'Pharmacy catalogue';
        $items = [];
        foreach ($this->storefront_model->getOrderItems((int)$order->id) as $line) $items[] = ['productId' => (string)(int)$line->item_id, 'name' => (string)$line->item_name, 'qty' => (float)$line->qty, 'unitPrice' => (float)$line->unit_price, 'sellerType' => 'client', 'sellerName' => $sellerName];
        $deliveryStatus = (string)($order->delivery_status ?? '');
        return ['id' => (string)$order->order_code, 'backendOrderId' => (int)$order->id, 'status' => $deliveryStatus ?: (string)$order->order_status, 'orderStatus' => (string)$order->order_status, 'deliveryStatus' => $deliveryStatus, 'paymentStatus' => (string)$order->payment_status, 'fulfilment' => (string)($order->fulfilment_type ?? ''), 'locationId' => 'warehouse-'.(int)($order->warehouse_id ?? 0), 'sourceLocationId' => 'warehouse-'.(int)($order->source_warehouse_id ?: $order->warehouse_id), 'locationName' => $location ? (string)$location->warehouse_name : '', 'customer' => (string)$order->customer_name, 'phone' => (string)$order->customer_phone, 'address' => (string)$order->customer_address, 'items' => $items, 'deliveryFee' => (float)$order->delivery_fee, 'total' => (float)$order->grand_total, 'createdAt' => (string)$order->created_at, 'history' => $this->order_lifecycle_model->history((int)$order->store_id, (int)$order->id)];
    }

    public function order_status($storeSlug = '') {
        $settings = $this->_pharmacySettings($storeSlug);
        if (!$settings) { $this->_json(['status' => false, 'message' => 'Pharmacy store not found.']); return; }
        $order = $this->storefront_model->getOrderByCode(trim((string)$this->input->get('order_code', TRUE)), (int)$settings->store_id);
        if (!$order || (int)$order->status !== 1 || !$this->_pharmacyOrderIsVisible((int)$settings->store_id, (int)$order->id) || trim((string)$this->input->get('phone', TRUE)) !== (string)$order->customer_phone) { $this->_json(['status' => false, 'message' => 'Order not found.']); return; }
        $this->_json(['status' => true, 'order' => $this->_orderPayload($order, $settings)]);
    }

    public function hub_orders($storeSlug = '') {
        $settings = $this->_pharmacySettings($storeSlug);
        if (!$settings) { $this->_json(['status' => false, 'message' => 'Pharmacy store not found.']); return; }
        $storeId = (int)$settings->store_id;
        $this->session->set_userdata('pharmacy_demo_hub_store', $storeId);
        $outlets = $this->storefront_model->getOrderableOutlets($storeId); $ids = [];
        foreach ($outlets as $outlet) $ids[] = (int)$outlet->id;
        $hubWarehouse = (int)$this->session->userdata('pharmacy_demo_hub_warehouse');
        $orders = empty($ids) ? [] : $this->db->where('store_id', $storeId)->where_in('warehouse_id', $hubWarehouse > 0 ? [$hubWarehouse] : $ids)->where('status', 1)->order_by('id', 'desc')->limit(50)->get('db_online_orders')->result();
        $orders = array_values(array_filter($orders, function ($order) use ($storeId) { return $this->_pharmacyOrderIsVisible($storeId, (int)$order->id); }));
        $payload = []; foreach ($orders as $order) $payload[] = $this->_orderPayload($order, $settings);
        $this->_json(['status' => true, 'orders' => $payload]);
    }

    public function hub_action($storeSlug = '') {
        $settings = $this->_pharmacySettings($storeSlug); if (!$settings) { $this->_json(['status' => false, 'message' => 'Pharmacy store not found.']); return; }
        $storeId = (int)$settings->store_id; if ((int)$this->session->userdata('pharmacy_demo_hub_store') !== $storeId && (int)$this->session->userdata('inv_userid') < 1) { $this->_json(['status' => false, 'message' => 'Open the pharmacy hub before updating assigned orders.']); return; }
        $order = $this->db->where('id', (int)$this->input->post('order_id', TRUE))->where('store_id', $storeId)->get('db_online_orders')->row();
        $assignedWarehouse = (int)$this->session->userdata('pharmacy_demo_hub_warehouse');
        if (!$order || (int)$order->status !== 1 || !$this->_pharmacyOrderIsVisible($storeId, (int)$order->id) || ($assignedWarehouse > 0 && (int)$order->warehouse_id !== $assignedWarehouse)) { $this->_json(['status' => false, 'message' => 'Assigned order not found.']); return; }
        $action = trim((string)$this->input->post('action', TRUE)); $map = ['accept' => ['accepted', 'accepted'], 'prepare' => ['preparing', 'preparing'], 'ready' => ['ready', 'ready'], 'dispatch' => ['out_for_delivery', null], 'handover' => [preg_match('/pick/i', (string)$order->shipping_method) ? 'collected' : 'delivered', 'fulfilled'], 'unable' => ['reassignment_requested', null]];
        if (!isset($map[$action])) { $this->_json(['status' => false, 'message' => 'Unknown hub action.']); return; }
        $deliveryStatus = $map[$action][0];
        if ($map[$action][1]) { $this->load->model('order_lifecycle_model'); $moved = $this->order_lifecycle_model->move($storeId, (int)$order->id, $map[$action][1], ['channel' => 'online', 'actor' => 'Pharmacy hub', 'reason' => 'Pharmacy fulfilment update']); if (empty($moved['ok'])) { $this->_json(['status' => false, 'message' => $moved['message'] ?? 'Order cannot move to that stage.']); return; } }
        $data = ['delivery_status' => $deliveryStatus, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->where('id', (int)$order->id)->where('store_id', $storeId)->update('db_online_orders', $data);
        $this->_json(['status' => true, 'order' => $this->_orderPayload($this->storefront_model->getOrder((int)$order->id), $settings)]);
    }

    /**
     * Runtime guard for legacy rows that predate an industry switch. The hub
     * must never display an order containing an inactive or non-pharmacy line.
     */
    private function _pharmacyOrderIsVisible($storeId, $orderId) {
        $categoryIds = [];
        if ($this->db->table_exists('db_category')) {
            $allowed = ['Prescription Drugs', 'Over-the-Counter', 'Medical Supplies', 'Vitamins & Supplements', 'Dermatology', 'Skincare', 'Analgesics & Pain Relief', 'Antibiotics', 'Cold, Flu & Allergy', 'Digestive Health', 'First Aid & Wound Care', 'Mother & Baby', 'Personal Care & Hygiene', 'Medical Devices'];
            $rows = $this->db->select('id')->where('store_id', (int)$storeId)->where_in('category_name', $allowed)->get('db_category')->result();
            foreach ($rows as $row) $categoryIds[] = (int)$row->id;
        }
        if (!$categoryIds || !$this->db->table_exists('db_online_order_items')) return false;

        $lines = $this->storefront_model->getOrderItems((int)$orderId);
        if (!$lines) return false;
        $serviceNames = ['Medication Review', 'Blood Pressure Check', 'Prescription Refill Support'];
        foreach ($lines as $line) {
            if ((string)$line->item_type === 'product') {
                $item = $this->db->where('id', (int)$line->item_id)->where('store_id', (int)$storeId)->where('status', 1)->where_in('category_id', $categoryIds)->get('db_items')->row();
                if (!$item) return false;
                continue;
            }
            if ((string)$line->item_type === 'service' && $this->db->table_exists('db_services')) {
                $service = $this->db->where('id', (int)$line->item_id)->where('store_id', (int)$storeId)->where('status', 1)->where_in('service_name', $serviceNames)->get('db_services')->row();
                if ($service) continue;
            }
            return false;
        }
        return true;
    }

    private function _storeConfig($storeId, $settings, $store) {
        $products = $this->storefront_model->getOnlineProducts($storeId, null, '', 500, 0);
        $categories = $this->storefront_model->getCategoriesWithItems($storeId);
        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[(int)$category->id] = (string)$category->category_name;
        }

        $rxMap = [];
        foreach (['prescription_required', 'requires_prescription', 'is_prescription'] as $field) {
            if ($this->db->field_exists($field, 'db_items')) {
                $rows = $this->db->select('id, '.$field.' AS required')->where('store_id', (int)$storeId)->get('db_items')->result();
                foreach ($rows as $row) $rxMap[(int)$row->id] = !empty($row->required);
                break;
            }
        }

        $warehouses = $this->storefront_model->getOrderableOutlets($storeId);
        if (!$warehouses && $this->db->table_exists('db_warehouse')) {
            $warehouses = $this->db->where('store_id', (int)$storeId)->where('status', 1)->order_by('warehouse_name', 'asc')->limit(12)->get('db_warehouse')->result();
        }
        if (!$warehouses) $warehouses = [(object)['id' => 0, 'warehouse_name' => ($store->store_name ?? 'Pharmacy').' main branch']];

        $stock = [];
        if ($this->db->table_exists('db_warehouseitems')) {
            $rows = $this->db->select('warehouse_id, item_id, available_qty')->where('store_id', (int)$storeId)->get('db_warehouseitems')->result();
            foreach ($rows as $row) {
                $stock['warehouse-'.(int)$row->warehouse_id][(string)(int)$row->item_id] = (float)$row->available_qty;
            }
        }

        $palette = ['coral', 'blue', 'peach', 'green', 'navy', 'yellow', 'lavender', 'mint'];
        $itemMeta = [];
        if ($this->db->table_exists('db_items')) {
            $this->db->select('a.id, a.unit_id, a.consumable_unit, u.unit_name');
            $this->db->from('db_items a');
            if ($this->db->table_exists('db_units')) $this->db->join('db_units u', 'u.id=a.unit_id', 'left');
            $this->db->where('a.store_id', (int)$storeId)->where('a.publish_online', 1);
            foreach ($this->db->get()->result() as $meta) $itemMeta[(int)$meta->id] = $meta;
        }
        $mappedProducts = [];
        foreach ($products as $index => $product) {
            $price = (float)$this->storefront_model->getProductEffectivePrice($product);
            $category = $categoryMap[(int)$product->category_id] ?? 'Pharmacy essentials';
            $meta = $itemMeta[(int)$product->id] ?? null;
            $unitName = trim((string)($meta->unit_name ?? '')) ?: 'Pack';
            $unitLabel = trim((string)($meta->consumable_unit ?? '')) ?: $unitName;
            $strength = '';
            if (preg_match('/\b\d+(?:\.\d+)?\s?(?:mg|mcg|g|kg|ml|l|%|iu)\b/i', (string)$product->item_name, $strengthMatch)) $strength = trim($strengthMatch[0]);
            $form = preg_match('/capsule/i', (string)$product->item_name) ? 'Capsule' : (preg_match('/tablet/i', (string)$product->item_name) ? 'Tablet' : (preg_match('/cream|gel|lotion/i', (string)$product->item_name) ? 'Topical' : $category));
            $mappedProducts[] = [
                'id' => (string)(int)$product->id,
                'name' => (string)$product->item_name,
                'image' => !empty($product->item_image) && is_file((string)$product->item_image) ? base_url($product->item_image) : '',
                'category' => 'category-'.(int)$product->category_id,
                'price' => $price,
                'oldPrice' => (float)$product->sales_price > $price ? (float)$product->sales_price : null,
                'unit' => $unitLabel,
                'strength' => $strength,
                'form' => $form,
                'pack' => $unitLabel,
                'prescription' => !empty($rxMap[(int)$product->id]) || (bool)preg_match('/prescription|antibiotic/i', $category.' '.$product->item_name),
                'colour' => $palette[$index % count($palette)],
                'description' => (string)($product->description ?: 'Available from the selected MartPoint fulfilment location.'),
                'tags' => [],
            ];
            foreach ($warehouses as $warehouse) {
                $locationId = 'warehouse-'.(int)$warehouse->id;
                if (!isset($stock[$locationId])) $stock[$locationId] = [];
                if (!array_key_exists((string)(int)$product->id, $stock[$locationId])) {
                    $stock[$locationId][(string)(int)$product->id] = count($warehouses) === 1 ? (float)$product->stock : 0;
                }
            }
        }

        $mappedCategories = [];
        $categoryIcons = ['pill', 'spark', 'heart', 'drop', 'plus', 'device'];
        foreach (array_values($categoryMap) as $index => $label) {
            $categoryId = array_keys($categoryMap)[$index];
            $mappedCategories[] = ['id' => 'category-'.(int)$categoryId, 'label' => $label, 'icon' => $categoryIcons[$index % count($categoryIcons)]];
        }

        $mappedLocations = [];
        foreach ($warehouses as $warehouse) {
            $mappedLocations[] = [
                'id' => 'warehouse-'.(int)$warehouse->id,
                'warehouseId' => (int)$warehouse->id,
                'name' => (string)$warehouse->warehouse_name,
                'kind' => in_array((string)($warehouse->branch_type ?? 'branch'), ['partner', 'pickup_hub'], true) ? (string)$warehouse->branch_type : 'branch',
                'city' => (string)($warehouse->city ?? ($store->city ?? 'Nigeria')),
                'area' => (string)($warehouse->city ?? 'Local area'),
                'address' => (string)($warehouse->address ?? ($settings->store_address ?? 'Address available from the pharmacy')),
                'hours' => (string)($warehouse->business_hours ?? ($settings->business_hours ?: 'Opening hours set by the pharmacy')),
                'coverage' => (string)($warehouse->delivery_coverage ?? 'Delivery coverage confirmed at checkout'),
                'deliveryFee' => (float)($warehouse->delivery_fee ?? 0),
                'pickup' => isset($warehouse->pickup_enabled) ? (bool)$warehouse->pickup_enabled : true,
                'pickupOnly' => (string)($warehouse->branch_type ?? '') === 'pickup_hub',
                'sourceLocationId' => !empty($warehouse->stock_source_id) ? 'warehouse-'.(int)$warehouse->stock_source_id : null,
                'note' => (string)($warehouse->branch_type ?? '') === 'pickup_hub' ? 'This hub does not own stock. Orders are prepared by the linked pharmacy source and transferred here for collection.' : 'Bring your order reference when collecting from this pharmacy location.',
            ];
        }

        return [
            'dataSource' => 'martpoint',
            'storeId' => $storeId,
            'brand' => [
                'name' => (string)($store->store_name ?? 'Pharmacy'),
                'strap' => (string)($settings->store_subheadline ?: 'Pharmacy, wellness and everyday care'),
                'phone' => (string)($settings->store_phone ?? $store->mobile ?? ''),
                'email' => (string)($settings->store_email ?? $store->email ?? ''),
            ],
            'categories' => $mappedCategories,
            'products' => $mappedProducts,
            'locations' => $mappedLocations,
            'stock' => $stock,
            'partnerOffers' => [],
            'partnerMarketplaceEnabled' => !empty($settings->partner_marketplace_enabled),
            'basePath' => '/store/'.rawurlencode((string)$settings->store_slug).'/pharmacy',
            'placeOrderUrl' => base_url('storefront/place_order'),
            'verifyPaymentUrl' => base_url('storefront/verify_payment'),
            'prescriptionRequestUrl' => base_url('store/'.rawurlencode((string)$settings->store_slug).'/pharmacy/prescription'),
            'prescriptionStatusUrl' => base_url('store/'.rawurlencode((string)$settings->store_slug).'/pharmacy/prescription/status'),
            'prescriptionApproveUrl' => base_url('pharmacy_demo/prescription_approve/'.rawurlencode((string)$settings->store_slug)),
            'hubOrdersUrl' => base_url('store/'.rawurlencode((string)$settings->store_slug).'/pharmacy/orders'),
            'hubActionUrl' => base_url('store/'.rawurlencode((string)$settings->store_slug).'/pharmacy/orders/action'),
            'orderStatusUrl' => base_url('store/'.rawurlencode((string)$settings->store_slug).'/pharmacy/order/status'),
            'csrfName' => $this->security->get_csrf_token_name(),
            'csrfHash' => $this->security->get_csrf_hash(),
            'shippingMethods' => json_decode($settings->shipping_methods_json ?? '[]', true) ?: [],
            'paymentMethods' => [
                'online' => !empty($settings->allow_paystack),
                'pay_on_delivery' => !empty($settings->allow_pay_on_delivery),
                'whatsapp' => !empty($settings->allow_whatsapp),
            ],
        ];
    }

    public function index($mode = 'customer') {
        $this->load->view('pharmacy_demo/app', [
            'mode' => $mode === 'hub' ? 'hub' : 'customer',
            'page_title' => $mode === 'hub' ? 'Nevari partner hub demo' : 'Nevari Health pharmacy demo',
            // The demo deliberately exercises the opt-in path. Live stores default off.
            'partner_marketplace_enabled' => 1,
            'pharmacy_config' => [],
            'pharmacy_base_path' => '/pharmacy-demo',
        ]);
    }

    /** Local/demo operator command: refresh only one pharmacy tenant's fictional seed. */
    public function seed_demo($storeId = 0) {
        if (PHP_SAPI !== 'cli' && strpos((string)($_SERVER['HTTP_HOST'] ?? ''), '.test') === false) { show_404(); return; }
        $storeId = (int)$storeId;
        $store = $this->db->where('id', $storeId)->get('db_store')->row();
        if (!$store || !in_array(strtolower((string)$store->industry_type), ['pharmacy', 'medical_store', 'healthcare'], true)) { echo "Not a pharmacy store\n"; return; }
        $this->load->model('Demo_seeder');
        echo json_encode($this->Demo_seeder->seed_examples($storeId, 'pharmacy')).PHP_EOL;
    }

    public function reset() {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'ok', 'message' => 'Demo reset is browser-local. Clear the demo site storage to reset it.']));
    }
}
