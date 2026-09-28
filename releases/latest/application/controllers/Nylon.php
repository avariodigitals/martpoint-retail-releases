<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Nylon & Polythene Manufacturing Controller
 *
 * Factory module for the nylon_polythene business profile:
 *   - Products & film specs (raw materials, film rolls, finished goods)
 *   - Customer job orders with artwork approval (rides db_custom_orders)
 *   - Production jobs: material allocation → extrusion → printing →
 *     cutting/sealing → packing → QC, with shift-level output reporting
 *   - Machines, job costing, factory dashboard and operational reports
 *
 * Capability flags (Business Profile → Feature Flags) configure the factory:
 *   nylon_extrusion  — the factory extrudes its own film
 *   nylon_conversion — the factory converts film into bags
 *   nylon_roll_trading — the factory sells film rolls without converting
 *
 * Permissions (role matrix):
 *   nylon_view      see the module
 *   nylon_jobs_*    create/edit/delete production jobs (supervisor)
 *   nylon_report    report shift output & waste (operators)
 *   nylon_approve   approve QC, complete stages/jobs, reverse reports (supervisor)
 *   nylon_artwork   upload & manage order artwork (sales)
 *   nylon_costing   see job costing and margins (finance)
 *   nylon_settings  factory settings & machines
 */
class Nylon extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('nylon_model', 'nylon');
        $this->load->model('custom_orders_model', 'custom_orders');
    }

    private function _check_feature() {
        if (!mp_feature_enabled('nylon_workflow')
            && !mp_feature_enabled('nylon_extrusion')
            && !mp_feature_enabled('nylon_conversion')
            && !mp_feature_enabled('nylon_roll_trading')) {
            $this->show_feature_not_activated('nylon_workflow',
                'Enable the Nylon / Polythene Production module from Business Profile, then pick the factory capabilities (extrusion, conversion, roll trading).');
        }
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    private function _json($arr) {
        $arr['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($arr);
    }

    /* ============================ dashboard ============================== */

    public function index() {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $store_id = get_current_store_id();
        $warehouse_id = $this->input->get('branch_id') ?: null;
        if ($warehouse_id) {
            $ok = $this->db->where('id',$warehouse_id)->where('store_id',$store_id)->count_all_results('db_warehouse') > 0;
            if (!$ok) $warehouse_id = null;
        }
        $this->_render('Nylon Factory', 'nylon/dashboard', [
            'stats'      => $this->nylon->dashboard_stats($store_id, $warehouse_id),
            'open_jobs'  => $this->nylon->open_jobs($store_id, $warehouse_id),
            'materials'  => $this->nylon->material_stock($store_id, $warehouse_id),
            'machines'   => $this->nylon->output_by_machine($store_id),
            'products'   => $this->nylon->output_by_product($store_id),
            'balances'   => $this->nylon->outstanding_balances($store_id),
            'mode'       => $this->nylon->mode_flags(),
            'mode_label' => $this->nylon->mode_label(),
            'warehouses' => $this->db->where('store_id',$store_id)->where('status',1)->get('db_warehouse')->result(),
            'warehouse_id' => $warehouse_id,
            'can_costing'=> $this->permissions('nylon_costing'),
        ]);
    }

    /* ============================ products & specs ======================= */

    public function products() {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $store_id = get_current_store_id();
        $edit_id = (int)$this->input->get('edit');
        $edit = null;
        if ($edit_id) {
            $this->permission_check('items_edit');
            $edit = [
                'item' => $this->db->where('id',$edit_id)->where('store_id',$store_id)->get('db_items')->row(),
                'spec' => $this->nylon->get_spec($edit_id, $store_id),
            ];
        }
        $this->_render('Materials & Products', 'nylon/products', [
            'spec_items'   => $this->nylon->get_spec_items(null, $store_id),
            'edit'         => $edit,
            'units'        => $this->nylon->get_units($store_id),
            'categories'   => $this->db->where('store_id',$store_id)->where('status',1)->get('db_category')->result(),
            'classes'      => Nylon_model::item_classes(),
            'materials'    => Nylon_model::materials(),
            'forms'        => Nylon_model::product_forms(),
            'bag_types'    => Nylon_model::bag_types(),
            'print_types'  => Nylon_model::print_types(),
            'can_edit'     => $this->permissions('items_add') || $this->permissions('items_edit'),
        ]);
    }

    /** Create/update a nylon item + its spec card. */
    public function product_save() {
        $this->_check_feature();
        $id = (int)$this->input->post('item_id', TRUE);
        $this->permission_check($id ? 'items_edit' : 'items_add');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('item_name','Item name','trim|required');
        $this->form_validation->set_rules('item_class','Item class','trim|required|in_list[raw_material,film_roll,finished_good,consumable]');
        $this->form_validation->set_rules('unit_id','Base unit','trim|required|numeric');
        if ($this->form_validation->run() == FALSE) {
            $this->_json(['success'=>false,'message'=>validation_errors()]);
            return;
        }

        $item_class = $this->input->post('item_class', TRUE);
        $item_data = [
            'store_id'       => $store_id,
            'item_name'      => $this->input->post('item_name', TRUE),
            'category_id'    => (int)$this->input->post('category_id', TRUE) ?: null,
            'unit_id'        => (int)$this->input->post('unit_id', TRUE),
            'alert_qty'      => (float)$this->input->post('alert_qty', TRUE) ?: 0,
            'purchase_price' => (float)str_replace(',','',$this->input->post('purchase_price', TRUE) ?: 0),
            'sales_price'    => (float)str_replace(',','',$this->input->post('sales_price', TRUE) ?: 0),
            // materials & consumables are never sold directly
            'not_for_sale'   => in_array($item_class,['raw_material','consumable'],true) ? 1 : 0,
            'status'         => 1,
            'accept_custom_order' => $item_class === 'finished_good' ? 1 : 0,
            'workflow_template_key' => 'nylon',
        ];
        if (!$id) {
            $item_data['item_code'] = $this->input->post('item_code', TRUE) ?: get_init_code('item');
            $item_data['stock'] = 0;
            $item_data['price'] = $item_data['purchase_price'];
            $item_data['created_date'] = date('Y-m-d');
            $item_data['created_time'] = date('H:i:s');
            $item_data['created_by'] = $this->session->userdata('username') ?: 'System';
            $this->db->insert('db_items',$item_data);
            $id = $this->db->insert_id();
        } else {
            $this->db->where('id',$id)->where('store_id',$store_id)->update('db_items',$item_data);
        }
        if (!$id) { $this->_json(['success'=>false,'message'=>'Could not save the item.']); return; }

        $this->nylon->save_spec($id, [
            'item_class'       => $item_class,
            'material'         => $this->input->post('material', TRUE) ?: null,
            'product_form'     => $this->input->post('product_form', TRUE) ?: null,
            'bag_type'         => $this->input->post('bag_type', TRUE) ?: null,
            'width_cm'         => $this->input->post('width_cm', TRUE) !== '' ? (float)$this->input->post('width_cm', TRUE) : null,
            'length_cm'        => $this->input->post('length_cm', TRUE) !== '' ? (float)$this->input->post('length_cm', TRUE) : null,
            'thickness_micron' => $this->input->post('thickness_micron', TRUE) !== '' ? (float)$this->input->post('thickness_micron', TRUE) : null,
            'colour'           => $this->input->post('colour', TRUE) ?: null,
            'print_type'       => $this->input->post('print_type', TRUE) ?: 'none',
            'design_ref'       => $this->input->post('design_ref', TRUE) ?: null,
            'kg_per_piece'     => $this->input->post('kg_per_piece', TRUE) !== '' ? (float)$this->input->post('kg_per_piece', TRUE) : null,
            'kg_per_roll'      => $this->input->post('kg_per_roll', TRUE) !== '' ? (float)$this->input->post('kg_per_roll', TRUE) : null,
            'pieces_per_roll'  => $this->input->post('pieces_per_roll', TRUE) !== '' ? (float)$this->input->post('pieces_per_roll', TRUE) : null,
            'notes'            => $this->input->post('spec_notes', TRUE) ?: null,
        ], $store_id);

        // Selling/purchase units — same POST contract as Item_selling_units_model
        $this->load->model('item_selling_units_model','isu');
        $this->isu->save_units($id, $store_id, 0);

        $this->_json(['success'=>true,'id'=>$id,'message'=>'Saved.']);
    }

    /** JSON: selling units for an item (used by the product form). */
    public function product_units($item_id) {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $this->load->model('item_selling_units_model','isu');
        header('Content-Type: application/json');
        echo json_encode($this->isu->get_units((int)$item_id, get_current_store_id(), 0));
    }

    /* ============================ customer orders ======================== */

    public function orders() {
        $this->_check_feature();
        $this->permission_check('custom_orders_view');
        $store_id = get_current_store_id();
        $orders = $this->nylon->get_orders($store_id);
        foreach ($orders as $o) { $o->artwork_approved = $this->nylon->artwork_ready($o->id); }
        $this->_render('Job Orders', 'nylon/orders', [
            'orders'     => $orders,
            'customers'  => $this->db->where('store_id',$store_id)->where('status',1)->order_by('customer_name','asc')->get('db_customers')->result(),
            'products'   => $this->nylon->get_spec_items('finished_good', $store_id),
            'rolls'      => $this->nylon->get_spec_items('film_roll', $store_id),
            'units'      => $this->nylon->get_units($store_id),
            'workflow'   => Custom_orders_model::get_workflow('nylon'),
            'can_add'    => $this->permissions('custom_orders_add'),
            'can_edit'   => $this->permissions('custom_orders_edit'),
        ]);
    }

    public function order_save() {
        $this->_check_feature();
        $this->permission_check('custom_orders_add');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('customer_id','Customer','trim|required|numeric');
        $this->form_validation->set_rules('item_id','Product','trim|required|numeric');
        $this->form_validation->set_rules('order_date','Order date','trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->_json(['success'=>false,'message'=>validation_errors()]);
            return;
        }
        $item = $this->db->where('id',(int)$this->input->post('item_id',TRUE))->where('store_id',$store_id)->get('db_items')->row();
        if (!$item) { $this->_json(['success'=>false,'message'=>'Product not found.']); return; }
        $spec = $this->nylon->get_spec($item->id, $store_id);
        $print = $spec && $spec->print_type && $spec->print_type !== 'none';

        $labels = $this->input->post('spec_label', TRUE) ?: [];
        $values = $this->input->post('spec_value', TRUE) ?: [];
        $specs = [];
        for ($i=0; $i<count($labels); $i++) {
            if ($labels[$i] !== '' && $labels[$i] !== null) $specs[$labels[$i]] = $values[$i] ?? '';
        }
        $total   = (float)str_replace(',','',$this->input->post('total_amount', TRUE) ?: 0);
        $deposit = (float)str_replace(',','',$this->input->post('deposit_amount', TRUE) ?: 0);
        $paid    = (float)str_replace(',','',$this->input->post('deposit_paid', TRUE) ?: 0);

        $data = [
            'store_id'      => $store_id,
            'customer_id'   => (int)$this->input->post('customer_id', TRUE),
            'item_id'       => $item->id,
            'item_name'     => $item->item_name,
            'specifications_json' => !empty($specs) ? json_encode($specs) : null,
            'order_qty'     => (float)$this->input->post('order_qty', TRUE) ?: null,
            'order_unit_id' => (int)$this->input->post('order_unit_id', TRUE) ?: null,
            'artwork_required' => $print ? 1 : (int)(bool)$this->input->post('artwork_required'),
            'design_ref'    => $this->input->post('design_ref', TRUE) ?: ($spec->design_ref ?? null),
            'quoted_price'  => (float)str_replace(',','',$this->input->post('quoted_price', TRUE) ?: 0),
            'deposit_amount'=> $deposit,
            'deposit_paid'  => $paid,
            'total_amount'  => $total,
            'balance_due'   => $total - $paid,
            'status'        => $this->input->post('status', TRUE) ?: 'new',
            'notes'         => $this->input->post('notes', TRUE),
            'order_date'    => $this->input->post('order_date', TRUE),
            'due_date'      => $this->input->post('due_date', TRUE) ?: null,
        ];
        $id = (int)$this->input->post('id', TRUE) ?: null;
        // Status edits stay inside the nylon workflow
        if ($id) { unset($data['store_id'], $data['customer_id'], $data['item_id'], $data['item_name']); }
        $saved = $this->nylon->save_order($data, $id);
        $this->_json(['success'=>(bool)$saved,'id'=>$saved,'message'=>$saved?'Order saved.':'Save failed.']);
    }

    public function order_view($id) {
        $this->_check_feature();
        $this->permission_check('custom_orders_view');
        $order = $this->nylon->get_order((int)$id);
        if (!$order || $order->store_id != get_current_store_id()) {
            $this->show_access_denied_page('Order not found.');
        }
        $jobs = $this->db->where('custom_order_id',$order->id)->order_by('id','desc')->get('db_nylon_jobs')->result();
        $spec = $this->nylon->get_spec($order->item_id, $order->store_id);
        $this->_render('Order '.$order->order_code, 'nylon/order_view', [
            'order'    => $order,
            'spec'     => $spec,
            'jobs'     => $jobs,
            'history'  => $this->custom_orders->get_history($order->id),
            'workflow' => Custom_orders_model::get_workflow('nylon'),
            'can_edit' => $this->permissions('custom_orders_edit'),
            'can_artwork' => $this->permissions('nylon_artwork'),
            'can_approve' => $this->permissions('nylon_approve'),
            'can_jobs'  => $this->permissions('nylon_jobs_add'),
        ]);
    }

    public function order_status() {
        $this->_check_feature();
        $this->permission_check('custom_orders_edit');
        $id = (int)$this->input->post('id', TRUE);
        $status = $this->input->post('status', TRUE);
        if (!in_array($status, Custom_orders_model::get_workflow('nylon'), true)) {
            $this->_json(['success'=>false,'message'=>'Invalid status.']); return;
        }
        $this->custom_orders->save(['status'=>$status], $id);
        $this->_json(['success'=>true,'message'=>'Status updated to '.Custom_orders_model::status_label($status)]);
    }

    /** Record a (partial) dispatch against an order. */
    public function order_dispatch() {
        $this->_check_feature();
        $this->permission_check('custom_orders_edit');
        $id = (int)$this->input->post('id', TRUE);
        $order = $this->nylon->get_order($id);
        if (!$order) { $this->_json(['success'=>false,'message'=>'Order not found.']); return; }
        $qty = (float)$this->input->post('dispatch_qty', TRUE);
        if ($qty <= 0) { $this->_json(['success'=>false,'message'=>'Enter a dispatch quantity.']); return; }
        $new = (float)$order->dispatched_qty + $qty;
        if (!empty($order->order_qty) && $new > (float)$order->order_qty + 0.0001) {
            $this->_json(['success'=>false,'message'=>'Dispatch exceeds the ordered quantity.']); return;
        }
        $status = (!empty($order->order_qty) && $new >= (float)$order->order_qty) ? 'delivered' : $order->status;
        $this->custom_orders->save(['dispatched_qty'=>$new,'status'=>$status,'delivery_date'=>date('Y-m-d')], $id);
        $this->custom_orders->log_history($id, $order->status, $status, 'Dispatched '.rtrim(rtrim(number_format($qty,3),'0'),'.').' '.($order->unit_name ?: 'units'));
        $this->_json(['success'=>true,'message'=>'Dispatch recorded — '.number_format($new).' dispatched in total.']);
    }

    public function order_repeat() {
        $this->_check_feature();
        $this->permission_check('custom_orders_add');
        $id = (int)$this->input->post('id', TRUE);
        $new_id = $this->nylon->repeat_order($id, $this->input->post('order_date', TRUE) ?: null, $this->input->post('due_date', TRUE) ?: null);
        $this->_json(['success'=>(bool)$new_id,'id'=>$new_id,'message'=>$new_id?'Repeat order created with the approved specification.':'Could not create the repeat order.']);
    }

    /* ============================ artwork ================================ */

    public function artwork_upload() {
        $this->_check_feature();
        $this->permission_check('nylon_artwork');
        $order_id = (int)$this->input->post('order_id', TRUE);
        $order = $this->nylon->get_order($order_id);
        if (!$order) { $this->_json(['success'=>false,'message'=>'Order not found.']); return; }

        $path = FCPATH.'uploads/artwork/';
        if (!is_dir($path)) { @mkdir($path, 0755, true); }
        $config = [
            'upload_path'   => $path,
            'allowed_types' => 'jpg|jpeg|png|gif|pdf|ai|psd|cdr|svg|zip',
            'max_size'      => 51200,
            'file_name'     => 'art_'.$order_id.'_'.time().'_'.rand(100,999),
        ];
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('artwork_file')) {
            $this->_json(['success'=>false,'message'=>$this->upload->display_errors('','')]); return;
        }
        $up = $this->upload->data();
        $id = $this->nylon->add_artwork($order_id, $up['client_name'], 'uploads/artwork/'.$up['file_name'], $this->input->post('note', TRUE) ?: '');
        $this->_json(['success'=>(bool)$id,'message'=>$id?'Artwork uploaded — pending approval.':'Upload saved but record failed.']);
    }

    public function artwork_status() {
        $this->_check_feature();
        $this->permission_check('nylon_approve');
        $id = (int)$this->input->post('id', TRUE);
        $status = $this->input->post('status', TRUE);
        $ok = $this->nylon->set_artwork_status($id, $status, $this->input->post('note', TRUE) ?: '');
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Artwork '.$status.'.':'Update failed.']);
    }

    /* ============================ production jobs ======================== */

    public function jobs() {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $store_id = get_current_store_id();
        $status = $this->input->get('status') ?: null;
        $warehouse_id = $this->input->get('branch_id') ?: null;

        $open_orders = $this->db->select('o.id, o.order_code, o.item_id, o.order_qty, o.order_unit_id, o.due_date, c.customer_name, i.item_name')
            ->from('db_custom_orders o')
            ->join('db_customers c','c.id=o.customer_id','left')
            ->join('db_items i','i.id=o.item_id','left')
            ->where('o.store_id',$store_id)->where('o.workflow_template_key','nylon')
            ->where_in('o.status',['new','quoted','awaiting_artwork','approved','deposit_paid','in_production'])
            ->order_by('o.due_date','asc')->get()->result();
        foreach ($open_orders as $o) { $o->artwork_approved = $this->nylon->artwork_ready($o->id); }

        $this->_render('Production Jobs', 'nylon/jobs', [
            'jobs'         => $this->nylon->get_jobs($store_id, $status, $warehouse_id),
            'open_orders'  => $open_orders,
            'products'     => array_merge($this->nylon->get_spec_items('finished_good',$store_id), $this->nylon->get_spec_items('film_roll',$store_id)),
            'materials'    => $this->nylon->get_spec_items('raw_material',$store_id),
            'rolls'        => $this->nylon->get_spec_items('film_roll',$store_id),
            'units'        => $this->nylon->get_units($store_id),
            'warehouses'   => $this->db->where('store_id',$store_id)->where('status',1)->get('db_warehouse')->result(),
            'status_filter'=> $status,
            'warehouse_id' => $warehouse_id,
            'mode'         => $this->nylon->mode_flags(),
            'can_add'      => $this->permissions('nylon_jobs_add'),
            'can_edit'     => $this->permissions('nylon_jobs_edit'),
            'can_delete'   => $this->permissions('nylon_jobs_delete'),
        ]);
    }

    public function job_save() {
        $this->_check_feature();
        $this->permission_check('nylon_jobs_add');
        $this->form_validation->set_rules('product_item_id','Product','trim|required|numeric');
        $this->form_validation->set_rules('planned_qty','Planned qty','trim|required|numeric');
        if ($this->form_validation->run() == FALSE) {
            $this->_json(['success'=>false,'message'=>validation_errors()]); return;
        }
        $store_id = get_current_store_id();
        $product_id = (int)$this->input->post('product_item_id', TRUE);
        $input_id   = (int)$this->input->post('input_item_id', TRUE) ?: null;
        $roll_id    = (int)$this->input->post('roll_item_id', TRUE) ?: null;
        $order_id   = (int)$this->input->post('custom_order_id', TRUE) ?: null;

        $flags = $this->nylon->mode_flags();
        $input_spec = $input_id ? $this->nylon->get_spec($input_id,$store_id) : null;
        $input_is_roll = $input_spec && $input_spec->item_class === 'film_roll';
        if (!$flags['extrusion'] && !$input_is_roll && $flags['conversion']) {
            $this->_json(['success'=>false,'message'=>'This factory has no extrusion — the job input must be a purchased film roll.']); return;
        }

        $planned_qty = (float)$this->input->post('planned_qty', TRUE);
        $planned_unit_id = (int)$this->input->post('planned_unit_id', TRUE) ?: null;
        // Normalise the planned qty into the product's base unit for the stage plan
        $planned_base = $planned_qty;
        if ($planned_unit_id) {
            $conv = $this->nylon->to_base_qty($product_id, $planned_unit_id, $planned_qty, $store_id);
            if ($conv === null) {
                $this->_json(['success'=>false,'message'=>'No conversion defined between that unit and the product base unit. Add it under Products → Selling Units.']); return;
            }
            $planned_base = $conv;
        }

        $data = [
            'job_kind'        => $order_id ? 'order' : 'stock',
            'custom_order_id' => $order_id,
            'product_item_id' => $product_id,
            'planned_qty'     => $planned_base,
            'planned_unit_id' => null, // stored in product base unit
            'due_date'        => $this->input->post('due_date', TRUE) ?: null,
            'priority'        => $this->input->post('priority', TRUE) ?: 'normal',
            'warehouse_id'    => (int)$this->input->post('warehouse_id', TRUE) ?: get_store_warehouse_id(),
            'est_material_cost'=> (float)str_replace(',','',$this->input->post('est_material_cost', TRUE) ?: 0),
            'est_other_cost'  => (float)str_replace(',','',$this->input->post('est_other_cost', TRUE) ?: 0),
            'notes'           => $this->input->post('notes', TRUE),
            'created_by'      => $this->session->userdata('username') ?: 'System',
        ];
        $stage_cfg = [
            'product_item_id' => $product_id,
            'input_item_id'   => $input_id,
            'roll_item_id'    => $roll_id,
            'planned_qty'     => $planned_base,
            'planned_input_qty' => (float)$this->input->post('planned_input_qty', TRUE) ?: null,
            'print'           => (bool)$this->input->post('print', TRUE),
        ];
        $job_id = $this->nylon->save_job($data, $stage_cfg);
        if ($job_id && $order_id) {
            $this->custom_orders->save(['status'=>'in_production'], $order_id);
        }
        $this->_json(['success'=>(bool)$job_id,'id'=>$job_id,'message'=>$job_id?'Job created with its stage plan.':'Save failed.']);
    }

    public function job_view($id) {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $job = $this->nylon->get_job((int)$id);
        if (!$job || $job->store_id != get_current_store_id()) {
            $this->show_access_denied_page('Job not found.');
        }
        $report = $this->nylon->job_report($job->id);
        $order = $job->custom_order_id ? $this->nylon->get_order($job->custom_order_id) : null;
        $this->_render('Job '.$job->job_code, 'nylon/job_view', [
            'job'        => $job,
            'report'     => $report,
            'order'      => $order,
            'stages'     => $this->nylon->get_stages($job->id),
            'machines'   => $this->nylon->get_machines(),
            'operators'  => $this->db->where('store_id',get_current_store_id())->where('status',1)->order_by('first_name','asc')->get('db_users')->result(),
            'scrap_items'=> $this->nylon->get_spec_items(null, get_current_store_id()),
            'stage_defs' => Nylon_model::stage_defs(),
            'can_report' => $this->permissions('nylon_report'),
            'can_approve'=> $this->permissions('nylon_approve'),
            'can_costing'=> $this->permissions('nylon_costing'),
            'can_edit'   => $this->permissions('nylon_jobs_edit'),
        ]);
    }

    public function job_status() {
        $this->_check_feature();
        $this->permission_check('nylon_jobs_edit');
        $ok = $this->nylon->set_job_status((int)$this->input->post('id',TRUE), $this->input->post('status',TRUE));
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Job updated.':'Invalid status.']);
    }

    public function job_delete() {
        $this->_check_feature();
        $this->permission_check('nylon_jobs_delete');
        $id = (int)$this->input->post('id', TRUE);
        $job = $this->nylon->get_job($id);
        if (!$job || $job->store_id != get_current_store_id()) { $this->_json(['success'=>false,'message'=>'Job not found.']); return; }
        $posted = $this->db->where('job_id',$id)->where('adjustment_id IS NOT NULL',null,false)->count_all_results('db_nylon_job_logs');
        if ($posted > 0) {
            $this->_json(['success'=>false,'message'=>'This job has posted stock movements — reverse the reports instead of deleting.']); return;
        }
        $this->db->where('job_id',$id)->delete('db_nylon_job_stages');
        $this->db->where('job_id',$id)->delete('db_nylon_job_logs');
        $this->db->where('job_id',$id)->delete('db_nylon_job_costs');
        $this->db->where('id',$id)->delete('db_nylon_jobs');
        $this->_json(['success'=>true,'message'=>'Job deleted.']);
    }

    /** Operator shift report. */
    public function stage_log() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_report');
        $r = $this->nylon->report_log(
            (int)$this->input->post('job_id', TRUE),
            (int)$this->input->post('stage_id', TRUE),
            [
                'shift_label'  => $this->input->post('shift_label', TRUE),
                'machine_id'   => $this->input->post('machine_id', TRUE),
                'operator_id'  => $this->input->post('operator_id', TRUE) ?: get_current_user_id(),
                'work_date'    => $this->input->post('work_date', TRUE),
                'qty_in'       => $this->input->post('qty_in', TRUE),
                'good_qty'     => $this->input->post('good_qty', TRUE),
                'reject_qty'   => $this->input->post('reject_qty', TRUE),
                'scrap_qty'    => $this->input->post('scrap_qty', TRUE),
                'waste_qty'    => $this->input->post('waste_qty', TRUE),
                'scrap_item_id'=> $this->input->post('scrap_item_id', TRUE),
                'notes'        => $this->input->post('notes', TRUE),
            ]
        );
        $this->_json($r);
    }

    public function log_approve() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_approve');
        $this->_json($this->nylon->approve_log((int)$this->input->post('id',TRUE)));
    }

    public function log_reverse() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_approve');
        $this->_json($this->nylon->reverse_log((int)$this->input->post('id',TRUE), $this->input->post('reason',TRUE) ?: ''));
    }

    public function stage_done() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_approve');
        $ok = $this->nylon->complete_stage((int)$this->input->post('id',TRUE));
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Stage completed.':'Could not complete the stage.']);
    }

    public function stage_skip() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_approve');
        $ok = $this->nylon->skip_stage((int)$this->input->post('id',TRUE));
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Stage skipped.':'Could not skip the stage.']);
    }

    public function job_complete() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_approve');
        $this->_json($this->nylon->complete_job((int)$this->input->post('id',TRUE)));
    }

    /* ============================ costs ================================== */

    public function cost_save() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_costing');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $ok = $this->nylon->add_cost($job_id, [
            'cost_type'   => $this->input->post('cost_type', TRUE) ?: 'other',
            'description' => $this->input->post('description', TRUE),
            'estimated'   => (int)(bool)$this->input->post('estimated'),
            'amount'      => (float)str_replace(',','',$this->input->post('amount', TRUE) ?: 0),
        ]);
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Cost recorded.':'Save failed.']);
    }

    public function cost_delete() {
        $this->_check_feature();
        $this->permission_check_with_msg('nylon_costing');
        $ok = $this->nylon->delete_cost((int)$this->input->post('id', TRUE));
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Cost removed.':'Delete failed.']);
    }

    /* ============================ machines =============================== */

    public function machines() {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $this->_render('Machines', 'nylon/machines', [
            'machines' => $this->nylon->get_machines(),
            'types'    => Nylon_model::machine_types(),
            'can_edit' => $this->permissions('nylon_settings'),
        ]);
    }

    public function machine_save() {
        $this->_check_feature();
        $this->permission_check('nylon_settings');
        $id = (int)$this->input->post('id', TRUE) ?: null;
        $ok = $this->nylon->save_machine([
            'machine_code' => $this->input->post('machine_code', TRUE),
            'machine_name' => $this->input->post('machine_name', TRUE),
            'machine_type' => $this->input->post('machine_type', TRUE) ?: 'other',
            'notes'        => $this->input->post('notes', TRUE),
        ], $id);
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Machine saved.':'Save failed.']);
    }

    public function machine_delete() {
        $this->_check_feature();
        $this->permission_check('nylon_settings');
        $ok = $this->nylon->delete_machine((int)$this->input->post('id', TRUE));
        $this->_json(['success'=>(bool)$ok,'message'=>$ok?'Machine removed.':'Delete failed.']);
    }

    /* ============================ reports ================================ */

    public function reports() {
        $this->_check_feature();
        $this->permission_check('nylon_view');
        $store_id = get_current_store_id();
        $warehouse_id = $this->input->get('branch_id') ?: null;
        $this->_render('Production Reports', 'nylon/reports', [
            'by_machine' => $this->nylon->output_by_machine($store_id, 90),
            'by_product' => $this->nylon->output_by_product($store_id, 90),
            'job_rows'   => $this->permissions('nylon_costing') ? $this->nylon->profit_by_job($store_id, $warehouse_id) : [],
            'warehouses' => $this->db->where('store_id',$store_id)->where('status',1)->get('db_warehouse')->result(),
            'warehouse_id' => $warehouse_id,
            'can_costing'=> $this->permissions('nylon_costing'),
        ]);
    }

    /* ============================ settings =============================== */

    public function settings() {
        $this->_check_feature();
        $this->permission_check('nylon_settings');
        $message = '';
        if ($this->input->post('save_settings')) {
            $this->load->model('business_profile_model','bp_model');
            $profile = $this->bp_model->get_profile(get_current_store_id());
            $settings = json_decode($profile['industry_settings_json'] ?? '', true) ?: [];
            $settings['nylon'] = [
                'default_scrap_item_id' => (int)$this->input->post('default_scrap_item_id', TRUE) ?: null,
                'require_deposit_before_job' => (int)(bool)$this->input->post('require_deposit_before_job'),
                'notify_due_days' => (int)$this->input->post('notify_due_days', TRUE) ?: 3,
            ];
            $ok = $this->bp_model->update_profile(get_current_store_id(), ['industry_settings_json'=>json_encode($settings)]);
            $message = $ok ? 'Settings saved.' : 'Could not save settings.';
        }
        $this->load->model('business_profile_model','bp_model');
        $profile = $this->bp_model->get_profile(get_current_store_id());
        $settings = json_decode($profile['industry_settings_json'] ?? '', true) ?: [];
        $this->_render('Factory Settings', 'nylon/settings', [
            'message'  => $message,
            'settings' => $settings['nylon'] ?? [],
            'mode'     => $this->nylon->mode_flags(),
            'scrap_items' => $this->nylon->get_spec_items(null, get_current_store_id()),
        ]);
    }

    /* ============================ demo / acceptance ====================== */

    /**
     * Seed the six acceptance scenarios with realistic data and render the
     * resulting inventory positions and job margins. Admin only.
     */
    public function demo() {
        $this->_check_feature();
        if (!is_admin()) { $this->show_access_denied_page('Only an admin can seed demo data.'); }
        $store_id = get_current_store_id();
        $result = null;
        if ($this->input->post('run')) {
            $result = $this->nylon->seed_demo();
            if (empty($result['seeded'])) {
                $this->session->set_flashdata('demo_error', $result['message'] ?? 'Seeding failed.');
                $result = null;
            }
        }

        // Shape the flat seed result into per-scenario cards for the view
        $cards = [];
        if (!empty($result['seeded'])) {
            $defs = [
                'scenario_1' => ['Resin → Film → Printed Bag', 'Full pipeline: 262 kg LDPE extruded into 10 rolls, printed against approved artwork, cut into bags, QC released the output to stock. Raw material is deducted once — at extrusion — not again when the rolls are consumed.'],
                'scenario_2' => ['Purchased Roll → Plain Bag', 'Conversion-only run: 5 purchased HDPE rolls enter directly at the roll stage — the extrusion stage is not in this job’s plan.'],
                'scenario_3' => ['Film-Roll-Only Sale', '10 purchased HDPE rolls sold and dispatched as-is. No production job — trading stock, not manufacturing.'],
                'scenario_4' => ['Branded Repeat Order', 'Repeat order cloned from the approved spec of order 1 — artwork carried over still approved, design reference intact.'],
                'scenario_5' => ['Partial Production & Dispatch', 'First shift produced 4,900 of 10,000 bags; 4,000 dispatched against the repeat order. Job and order stay open for the next shift.'],
                'scenario_6' => ['Rejected Output', '600 of 2,000 planned bags rejected at cutting; only the 1,400 QC-approved bags reached saleable stock.'],
            ];
            foreach ($defs as $key => $t) {
                $job_id   = $result['jobs'][$key]   ?? null;
                $order_id = $result['orders'][$key] ?? null;
                $job_code = null;
                if ($job_id) {
                    $j = $this->nylon->get_job($job_id);
                    $job_code = $j ? $j->job_code : null;
                }
                $cards[] = [
                    'scenario' => $t[0], 'summary' => $t[1],
                    'job_id'   => $job_id, 'job_code' => $job_code,
                    'order_id' => $order_id,
                    'report'   => $result['margins'][$key] ?? null,
                ];
            }
        }

        $existing_jobs = $this->db->where('store_id',$store_id)->where('created_by','Demo')->get('db_nylon_jobs')->result();
        $this->_render('Nylon Demo Scenarios', 'nylon/demo', [
            'result'        => $result,
            'results'       => $cards,
            'inventory'     => $result['inventory'] ?? [],
            'existing_jobs' => $existing_jobs,
        ]);
    }
}
