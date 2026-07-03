<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Operations Controller — Placeholder foundation for Phase 4 feature engines
 * Handles: Custom Orders, Packages, Memberships, Kitchen, Laundry, Production, Recipes, Price Catalogue, Public Catalogue
 */
class Operations extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_info();
        $this->load->model('Business_profile_model', 'bp_model');
    }

    private function _check_feature($flag) {
        if (!mp_feature_enabled($flag)) {
            show_error('This feature is not enabled for your business profile.', 403);
            exit;
        }
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $this->load->view($view, $d);
    }

    /* ===================== CUSTOM ORDERS ===================== */
    public function custom_orders() {
        $this->_check_feature('custom_orders');
        $this->permission_check('custom_orders_view');
        $this->load->model('custom_orders_model', 'custom_orders');
        $store_id = get_current_store_id();
        $data['counts'] = [];
        foreach (['new','quoted','deposit_paid','in_production','ready','delivered'] as $s) {
            $data['counts'][$s] = $this->custom_orders->count_by_status($store_id, $s);
        }
        $this->_render('Custom Orders', 'operations/custom_orders', $data);
    }

    public function custom_orders_ajax() {
        $this->_check_feature('custom_orders');
        $this->permission_check('custom_orders_view');
        $this->load->model('custom_orders_model', 'custom_orders');
        $list = $this->custom_orders->get_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        foreach ($list as $order) {
            $no++;
            $badge = Custom_orders_model::status_badge($order->status);
            $row = [];
            $row[] = $no;
            $row[] = '<span class="label label-default">'.htmlspecialchars($order->order_code).'</span>';
            $row[] = htmlspecialchars($order->customer_name ?: '-');
            $row[] = htmlspecialchars($order->template_item_name ?: $order->item_name ?: '-');
            $row[] = '<span class="label label-'.$badge.'">'.Custom_orders_model::status_label($order->status).'</span>';
            $row[] = show_date($order->due_date);
            $row[] = store_number_format($order->total_amount);
            $actions = '<a href="'.base_url('operations/custom_order/'.$order->id).'" class="btn btn-xs btn-primary" title="Edit"><i class="fa fa-pencil"></i></a> ';
            $actions .= '<button onclick="delete_custom_order('.$order->id.')" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></button>';
            $row[] = $actions;
            $data[] = $row;
        }
        $output = [
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => $this->custom_orders->count_all(),
            "recordsFiltered" => $this->custom_orders->count_filtered(),
            "data" => $data,
        ];
        echo json_encode($output);
    }

    public function custom_order($id = null) {
        $this->_check_feature('custom_orders');
        $this->permission_check('custom_orders_add');
        $this->load->model('custom_orders_model', 'custom_orders');
        $store_id = get_current_store_id();
        $data['edit_order'] = null;
        $data['history'] = [];
        if ($id) {
            $data['edit_order'] = $this->custom_orders->get($id);
            $data['history'] = $this->custom_orders->get_history($id);
        }
        $data['customers'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_customers')->result();
        // Only items that accept custom orders
        $data['items'] = $this->db->where('store_id', $store_id)->where('status', 1)->where('accept_custom_order', 1)->get('db_items')->result();
        $data['staff'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_users')->result();
        $data['preselect_customer_id'] = $this->input->get('customer_id', TRUE) ?: null;
        $this->_render($id ? 'Edit Custom Order' : 'New Custom Order', 'operations/custom_order', $data);
    }

    public function custom_order_save() {
        $this->_check_feature('custom_orders');
        $this->permission_check('custom_orders_add');
        $this->load->model('custom_orders_model', 'custom_orders');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('customer_id', 'Customer', 'trim|required|numeric');
        $this->form_validation->set_rules('item_id', 'Item', 'trim|required|numeric');
        $this->form_validation->set_rules('order_date', 'Order Date', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $customer = $this->db->where('id', $this->input->post('customer_id', TRUE))->get('db_customers')->row();
        $item = $this->db->where('id', $this->input->post('item_id', TRUE))->get('db_items')->row();
        $staff = $this->db->where('id', $this->input->post('staff_id', TRUE))->get('db_users')->row();

        $specs = [];
        $spec_labels = $this->input->post('spec_label', TRUE) ?: [];
        $spec_values = $this->input->post('spec_value', TRUE) ?: [];
        for ($i = 0; $i < count($spec_labels); $i++) {
            if (!empty($spec_labels[$i])) {
                $specs[$spec_labels[$i]] = $spec_values[$i] ?? '';
            }
        }

        $quoted = floatval($this->input->post('quoted_price', TRUE) ?: 0);
        $deposit = floatval($this->input->post('deposit_amount', TRUE) ?: 0);
        $deposit_paid = floatval($this->input->post('deposit_paid', TRUE) ?: 0);
        $total = floatval($this->input->post('total_amount', TRUE) ?: 0);
        $balance = $total - $deposit_paid;

        $order_data = [
            'store_id' => $store_id,
            'customer_id' => (int)$this->input->post('customer_id', TRUE),
            'item_id' => (int)$this->input->post('item_id', TRUE),
            'item_name' => $item ? $item->item_name : '',
            'specifications_json' => !empty($specs) ? json_encode($specs) : null,
            'quoted_price' => $quoted,
            'deposit_amount' => $deposit,
            'deposit_paid' => $deposit_paid,
            'total_amount' => $total,
            'balance_due' => $balance,
            'status' => $this->input->post('status', TRUE) ?: 'new',
            'workflow_template_key' => $item ? ($item->workflow_template_key ?: 'standard') : 'standard',
            'notes' => $this->input->post('notes', TRUE),
            'staff_id' => (int)($this->input->post('staff_id', TRUE) ?: 0),
            'staff_name' => $staff ? $staff->first_name . ' ' . $staff->last_name : '',
            'order_date' => $this->input->post('order_date', TRUE),
            'due_date' => $this->input->post('due_date', TRUE) ?: null,
        ];

        $id = $this->input->post('id', TRUE) ?: null;
        $saved_id = $this->custom_orders->save($order_data, $id);
        echo json_encode(['success' => true, 'id' => $saved_id, 'message' => $id ? 'Order updated.' : 'Order saved.']);
    }

    public function custom_order_delete() {
        $this->_check_feature('custom_orders');
        $this->permission_check('custom_orders_delete');
        $this->load->model('custom_orders_model', 'custom_orders');
        $id = $this->input->post('id', TRUE);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }
        $this->custom_orders->delete($id);
        echo json_encode(['success' => true, 'message' => 'Order deleted.']);
    }

    public function custom_order_update_status() {
        $this->_check_feature('custom_orders');
        $this->permission_check('custom_orders_edit');
        $this->load->model('custom_orders_model', 'custom_orders');
        $id = $this->input->post('id', TRUE);
        $status = $this->input->post('status', TRUE);
        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            return;
        }
        $this->custom_orders->save(['status' => $status], $id);
        echo json_encode(['success' => true, 'message' => 'Status updated to ' . Custom_orders_model::status_label($status)]);
    }

    /* ===================== PRODUCTION BATCHES ===================== */
    public function production_schedule() {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_view');
        $this->load->model('production_batches_model', 'pb');
        $this->load->model('custom_orders_model'); // ensures db_custom_orders table exists
        $store_id = get_current_store_id();

        // Default to this week
        $date_from = $this->input->get('from') ?: date('Y-m-d');
        $date_to = $this->input->get('to') ?: date('Y-m-d', strtotime('+6 days'));
        $status_filter = $this->input->get('status') ?: null;

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['status_filter'] = $status_filter;
        $data['batches'] = $this->pb->get_schedule($store_id, $date_from, $date_to, $status_filter);
        $data['pending_items'] = $this->pb->get_pending_items($store_id);

        $data['counts'] = [];
        foreach (['planned','prepping','in_production','ready','completed'] as $s) {
            $data['counts'][$s] = $this->pb->count_by_status($store_id, $s);
        }

        $this->_render('Production Schedule', 'operations/production_schedule', $data);
    }

    public function production_batches_ajax() {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_view');
        $this->load->model('production_batches_model', 'pb');
        $can_edit = $this->permissions('production_batches_edit');
        $list = $this->pb->get_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        $statuses = Production_batches_model::get_statuses();
        $warehouse_id = get_store_warehouse_id();
        $store_id = get_current_store_id();
        foreach ($list as $batch) {
            $no++;
            $badge = Production_batches_model::status_badge($batch->status);
            $row = [];
            $row[] = $no;
            $row[] = '<span class="label label-default">'.htmlspecialchars($batch->batch_code).'</span>';
            $row[] = htmlspecialchars($batch->batch_name);
            $row[] = show_date($batch->scheduled_date) . ($batch->scheduled_time ? ' <small class="text-muted">'.$batch->scheduled_time.'</small>' : '');
            // Interactive status dropdown
            $statusDropdown = '<select class="form-control input-xs batch-status-select" data-batch-id="'.$batch->id.'" data-current-status="'.$batch->status.'" style="min-width:130px;font-size:12px;">';
            foreach ($statuses as $st) {
                $selected = ($st === $batch->status) ? 'selected' : '';
                $label = Production_batches_model::status_label($st);
                $statusDropdown .= '<option value="'.$st.'" '.$selected.'>'.$label.'</option>';
            }
            $statusDropdown .= '</select> <span class="label label-'.$badge.' status-badge-inline" style="display:none;">'.Production_batches_model::status_label($batch->status).'</span>';
            $row[] = $statusDropdown;
            // Stock check: planned qty vs available for the batch's product item
            $batch_items = $this->pb->get_items($batch->id);
            $stockInfo = '';
            foreach ($batch_items as $bi) {
                $product_item_id = null;
                $planned = (float)$bi->quantity;
                if ($bi->item_type == 'recipe_product') {
                    $rec = $this->db->where('id', $bi->item_id)->get('db_recipes')->row();
                    $product_item_id = $rec ? $rec->product_item_id : null;
                } else if ($bi->item_type == 'custom_order') {
                    $co = $this->db->where('id', $bi->item_id)->get('db_custom_orders')->row();
                    if ($co && $co->item_id) {
                        $product_item_id = $co->item_id;
                    }
                }
                if ($product_item_id && $planned > 0) {
                    $avail = total_available_qty_items_of_warehouse($warehouse_id, $store_id, $product_item_id);
                    $color = ($avail >= $planned) ? 'green' : (($avail > 0) ? 'orange' : 'red');
                    $stockInfo .= '<small class="text-'.$color.'">'.format_qty($planned).' needed / '.format_qty($avail).' in stock</small><br>';
                }
            }
            $row[] = $stockInfo ?: '<small class="text-muted">-</small>';
            $row[] = htmlspecialchars($batch->equipment ?: '-');
            $row[] = htmlspecialchars($batch->staff_name ?: '-');
            $actions = '<a href="'.base_url('operations/production_batch/'.$batch->id).'" class="btn btn-xs btn-primary" title="Edit"><i class="fa fa-pencil"></i></a> ';
            $actions .= '<button onclick="delete_production_batch('.$batch->id.')" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></button>';
            $row[] = $actions;
            $data[] = $row;
        }
        $output = [
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => $this->pb->count_all(),
            "recordsFiltered" => $this->pb->count_filtered(),
            "data" => $data,
        ];
        echo json_encode($output);
    }

    public function production_batch($id = null) {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_add');
        $this->load->model('production_batches_model', 'pb');
        $this->load->model('custom_orders_model', 'custom_orders');
        $store_id = get_current_store_id();

        $data['edit_batch'] = null;
        $data['batch_items'] = [];
        if ($id) {
            $data['edit_batch'] = $this->pb->get($id);
            $data['batch_items'] = $this->pb->get_items($id);
        }

        $data['staff'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_users')->result();
        // Pending custom orders that can be batched
        $data['pending_orders'] = $this->pb->get_pending_items($store_id);
        $data['preselect_order_id'] = $this->input->get('order_id', TRUE) ?: null;
        // Active recipes for direct recipe-based production
        $data['active_recipes'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_recipes')->result();

        $this->_render($id ? 'Edit Production Batch' : 'New Production Batch', 'operations/production_batch', $data);
    }

    public function production_batch_save() {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_add');
        $this->load->model('production_batches_model', 'pb');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('batch_name', 'Batch Name', 'trim|required');
        $this->form_validation->set_rules('scheduled_date', 'Scheduled Date', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $staff = $this->db->where('id', $this->input->post('staff_id', TRUE))->get('db_users')->row();

        $batch_data = [
            'store_id' => $store_id,
            'batch_name' => $this->input->post('batch_name', TRUE),
            'batch_type' => $this->input->post('batch_type', TRUE) ?: 'general',
            'scheduled_date' => $this->input->post('scheduled_date', TRUE),
            'scheduled_time' => $this->input->post('scheduled_time', TRUE) ?: null,
            'equipment' => $this->input->post('equipment', TRUE),
            'staff_id' => (int)($this->input->post('staff_id', TRUE) ?: 0),
            'staff_name' => $staff ? ($staff->first_name . ' ' . $staff->last_name) : '',
            'status' => $this->input->post('status', TRUE) ?: 'planned',
            'notes' => $this->input->post('notes', TRUE),
        ];

        $id = $this->input->post('id', TRUE) ?: null;
        $batch_id = $this->pb->save($batch_data, $id);

        // Save batch items
        $item_types = $this->input->post('item_type', TRUE) ?: [];
        $item_ids = $this->input->post('item_id', TRUE) ?: [];
        $item_names = $this->input->post('item_name', TRUE) ?: [];
        $quantities = $this->input->post('quantity', TRUE) ?: [];
        $item_notes = $this->input->post('item_notes', TRUE) ?: [];

        $items = [];
        for ($i = 0; $i < count($item_ids); $i++) {
            if (!empty($item_ids[$i])) {
                $items[] = [
                    'item_type' => $item_types[$i] ?? 'custom_order',
                    'item_id' => (int)$item_ids[$i],
                    'item_name' => $item_names[$i] ?? '',
                    'quantity' => (int)($quantities[$i] ?: 1),
                    'notes' => $item_notes[$i] ?? '',
                ];
            }
        }
        $this->pb->save_items($batch_id, $items);

        echo json_encode(['success' => true, 'id' => $batch_id, 'message' => $id ? 'Batch updated.' : 'Batch saved.']);
    }

    public function production_batch_delete() {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_delete');
        $this->load->model('production_batches_model', 'pb');
        $id = $this->input->post('id', TRUE);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }
        $this->pb->delete($id);
        echo json_encode(['success' => true, 'message' => 'Batch deleted.']);
    }

    public function production_batch_update_status() {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_edit');
        $this->load->model('production_batches_model', 'pb');
        $id = $this->input->post('id', TRUE);
        $status = $this->input->post('status', TRUE);
        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            return;
        }
        if ($status == 'completed') {
            $completed = $this->pb->complete_batch($id);
            if (!$completed) {
                echo json_encode(['success' => false, 'message' => 'Stock update failed. Please check the error log or contact support.']);
                return;
            }
        }
        $this->pb->save(['status' => $status], $id);
        echo json_encode([
            'success' => true,
            'message' => 'Status updated to ' . Production_batches_model::status_label($status),
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }

    /**
     * Quick status change from the production schedule list.
     * Forward moves allowed freely. Backward moves require edit permission.
     */
    public function production_batch_quick_status() {
        $this->_check_feature('production_workflow');
        $this->permission_check('production_batches_view');
        $this->load->model('production_batches_model', 'pb');

        $id = $this->input->post('id', TRUE);
        $new_status = $this->input->post('status', TRUE);
        $current_status = $this->input->post('current_status', TRUE);

        if (!$id || !$new_status || !$current_status) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            return;
        }

        $status_order = array_flip(Production_batches_model::get_statuses());
        $current_idx = $status_order[$current_status] ?? -1;
        $new_idx = $status_order[$new_status] ?? -1;

        if ($new_idx < $current_idx) {
            // Backward move: requires edit/approval permission
            if (!$this->permissions('production_batches_edit')) {
                echo json_encode(['success' => false, 'message' => 'Moving status backward requires manager approval. Please ask a team lead to make this change.']);
                return;
            }
        }

        if ($new_status == 'completed') {
            $shortages = $this->pb->validate_stock_for_batch($id);
            if (!empty($shortages)) {
                $msg = 'Cannot complete — not enough stock:\n';
                foreach ($shortages as $s) {
                    $msg .= '- ' . $s['item_name'] . ': need ' . $s['needed'] . ', have ' . $s['available'] . '\n';
                }
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $completed = $this->pb->complete_batch($id);
            if (!$completed) {
                echo json_encode(['success' => false, 'message' => 'Stock update failed. Please check the error log or contact support.']);
                return;
            }
        }
        $this->pb->save(['status' => $new_status], $id);
        echo json_encode([
            'success' => true,
            'message' => 'Status updated to ' . Production_batches_model::status_label($new_status),
            'status' => $new_status,
            'label' => Production_batches_model::status_label($new_status),
            'badge' => Production_batches_model::status_badge($new_status),
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }

    /* ===================== RECIPES ===================== */
    public function recipes() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('recipes_view');
        $this->load->model('recipe_model', 'recipe');
        $store_id = get_current_store_id();

        $data['categories'] = $this->db->distinct()->select('category')->where('store_id', $store_id)->where('status', 1)->get('db_recipes')->result();
        $data['total_recipes'] = $this->recipe->count_all();

        $this->_render('Recipe Book', 'operations/recipes', $data);
    }

    public function recipes_ajax() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('recipes_view');
        $this->load->model('recipe_model', 'recipe');
        $list = $this->recipe->get_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        foreach ($list as $recipe) {
            $no++;
            $total_cost = $this->recipe->calculate_cost($recipe->id);
            $cost_per_unit = $this->recipe->calculate_cost_per_unit($recipe->id);
            $row = [];
            $row[] = $no;
            // Fetch linked product name if any
            $product_name = '-';
            if (!empty($recipe->product_item_id)) {
                $prod = $this->db->where('id', $recipe->product_item_id)->get('db_items')->row();
                if ($prod) $product_name = $prod->item_name;
            }
            $row[] = '<span class="label label-default">'.htmlspecialchars($recipe->recipe_code).'</span>';
            $row[] = htmlspecialchars($recipe->name);
            $row[] = htmlspecialchars($recipe->category ?: '-');
            $row[] = htmlspecialchars($product_name);
            $row[] = '<b>'.number_format($recipe->yield_qty, 2).'</b> <small>'.htmlspecialchars($recipe->yield_unit).'</small>';
            $row[] = '<span class="text-success">'.number_format($cost_per_unit, 2).'</span>';
            $row[] = number_format($total_cost, 2);
            $status = ($recipe->status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
            $row[] = $status;
            $actions = '<a href="'.base_url('operations/recipe/'.$recipe->id).'" class="btn btn-xs btn-primary" title="Edit"><i class="fa fa-pencil"></i></a> ';
            $actions .= '<button onclick="delete_recipe('.$recipe->id.')" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></button>';
            $row[] = $actions;
            $data[] = $row;
        }
        $output = [
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => $this->recipe->count_all(),
            "recordsFiltered" => $this->recipe->count_filtered(),
            "data" => $data,
        ];
        echo json_encode($output);
    }

    public function recipe($id = null) {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('recipes_add');
        $this->load->model('recipe_model', 'recipe');
        $store_id = get_current_store_id();

        $data['edit_recipe'] = null;
        $data['ingredients'] = [];
        $data['production_runs'] = [];
        if ($id) {
            $data['edit_recipe'] = $this->recipe->get($id);
            $data['ingredients'] = $this->recipe->get_ingredients($id);
            $data['production_runs'] = $this->recipe->get_production_runs($id);
            $data['total_cost'] = $this->recipe->calculate_cost($id);
            $data['cost_per_unit'] = $this->recipe->calculate_cost_per_unit($id);
        }
        // Only show not_for_sale items (raw materials) in ingredient dropdown
        $data['items'] = $this->db->where('store_id', $store_id)->where('status', 1)->where('not_for_sale', 1)->get('db_items')->result();
        // Load unit hierarchy for centralized multi-unit conversion
        $data['unit_hierarchy'] = [];
        if ($this->db->field_exists('parent_unit_id', 'db_units')) {
            $all_units = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_units')->result();
            foreach ($all_units as $u) {
                $data['unit_hierarchy'][$u->id] = $u;
            }
        }
        $data['staff'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_users')->result();
        $data['recipe_categories'] = $this->recipe->get_active_categories($store_id);
        // Product items for Final Product dropdown (items with their unit names)
        // Exclude items flagged "Not for Sale" — those are raw materials, not final products
        $data['product_items'] = $this->db->select('a.id, a.item_name, c.unit_name')
            ->from('db_items a')
            ->join('db_units c', 'c.id = a.unit_id', 'left')
            ->where('a.store_id', $store_id)
            ->where('a.status', 1)
            ->where('a.not_for_sale', 0)
            ->order_by('a.item_name', 'asc')
            ->get()->result();
        $this->_render($id ? 'Edit Recipe' : 'New Recipe', 'operations/recipe', $data);
    }

    public function recipe_save() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('recipes_add');
        $this->load->model('recipe_model', 'recipe');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('name', 'Recipe Name', 'trim|required');
        $this->form_validation->set_rules('yield_qty', 'Yield Quantity', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $recipe_data = [
            'store_id' => $store_id,
            'name' => $this->input->post('name', TRUE),
            'category' => $this->input->post('category', TRUE),
            'description' => $this->input->post('description', TRUE),
            'product_item_id' => $this->input->post('product_item_id', TRUE) ? (int)$this->input->post('product_item_id', TRUE) : null,
            'yield_qty' => $this->input->post('yield_qty', TRUE),
            'yield_unit' => $this->input->post('yield_unit', TRUE) ?: 'piece',
            'prep_time' => $this->input->post('prep_time', TRUE) ?: null,
            'cook_time' => $this->input->post('cook_time', TRUE) ?: null,
            'margin_pct' => $this->input->post('margin_pct', TRUE) ?? 30,
            'notes' => $this->input->post('notes', TRUE),
            'status' => $this->input->post('status', TRUE) ?? 1,
        ];

        $id = $this->input->post('id', TRUE) ?: null;
        $recipe_id = $this->recipe->save($recipe_data, $id);

        $item_ids = $this->input->post('item_id', TRUE) ?: [];
        $item_names = $this->input->post('item_name', TRUE) ?: [];
        $qtys = $this->input->post('qty', TRUE) ?: [];
        $units = $this->input->post('unit', TRUE) ?: [];
        $costs = $this->input->post('cost_per_unit', TRUE) ?: [];
        $wastages = $this->input->post('wastage_pct', TRUE) ?: [];

        $ingredients = [];
        for ($i = 0; $i < count($item_names); $i++) {
            if (!empty($item_names[$i]) && $qtys[$i] > 0) {
                $ingredients[] = [
                    'item_id' => (int)($item_ids[$i] ?: 0),
                    'item_name' => $item_names[$i],
                    'qty' => (float)$qtys[$i],
                    'unit' => $units[$i] ?: 'gram',
                    'cost_per_unit' => (float)($costs[$i] ?: 0),
                    'wastage_pct' => (float)($wastages[$i] ?: 0),
                ];
            }
        }
        $this->recipe->save_ingredients($recipe_id, $ingredients);

        // Propagate updated recipe cost to linked product items
        $this->recipe->update_linked_items_cost($recipe_id);

        echo json_encode(['success' => true, 'id' => $recipe_id, 'message' => $id ? 'Recipe updated.' : 'Recipe saved.']);
    }

    public function recipe_delete() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('recipes_delete');
        $this->load->model('recipe_model', 'recipe');
        $id = $this->input->post('id', TRUE);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }
        $this->recipe->delete($id);
        echo json_encode(['success' => true, 'message' => 'Recipe deleted.']);
    }

    public function recipe_production_run_save() {
        $this->_check_feature('recipe_tracking');
        $this->load->model('recipe_model', 'recipe');
        $store_id = get_current_store_id();

        $data = [
            'store_id' => $store_id,
            'recipe_id' => $this->input->post('recipe_id', TRUE),
            'batch_id' => $this->input->post('batch_id', TRUE) ?: null,
            'planned_qty' => $this->input->post('planned_qty', TRUE),
            'actual_yield' => $this->input->post('actual_yield', TRUE) ?: null,
            'actual_cost' => $this->input->post('actual_cost', TRUE) ?: null,
            'staff_id' => $this->input->post('staff_id', TRUE) ?: null,
            'notes' => $this->input->post('notes', TRUE),
            'run_date' => $this->input->post('run_date', TRUE),
        ];
        $this->recipe->save_production_run($data);
        echo json_encode(['success' => true, 'message' => 'Production run recorded.']);
    }

    /* ===================== PACKAGES ===================== */
    public function packages() {
        $this->_check_feature('packages');
        $this->permission_check('store_view');
        $this->_render('Packages', 'operations/packages');
    }

    /* ===================== MEMBERSHIPS ===================== */

    /**
     * Membership Plans List
     */
    public function memberships() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_view');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        // Update expired memberships before showing list
        $this->membership->update_expired_memberships();

        $data['active_count'] = $this->membership->count_active_memberships($store_id);
        $data['expiring_count'] = $this->membership->count_expiring_soon(7);
        $data['plans'] = $this->membership->get_active_plans($store_id);

        $this->_render('Memberships', 'operations/memberships', $data);
    }

    /**
     * Add / Edit Membership Plan
     */
    public function membership_plan($id = null) {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_add');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        $data['edit_plan'] = null;
        if ($id) {
            $data['edit_plan'] = $this->membership->get_plan($id);
        }

        $this->_render($id ? 'Edit Plan' : 'New Plan', 'operations/membership_plan', $data);
    }

    /**
     * Save Membership Plan (AJAX)
     */
    public function membership_plan_save() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_add');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('plan_name', 'Plan Name', 'trim|required');
        $this->form_validation->set_rules('price', 'Price', 'trim|required|numeric');
        $this->form_validation->set_rules('billing_cycle', 'Billing Cycle', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $plan_data = [
            'store_id' => $store_id,
            'plan_name' => $this->input->post('plan_name', TRUE),
            'plan_code' => $this->input->post('plan_code', TRUE),
            'description' => $this->input->post('description', TRUE),
            'price' => (float)$this->input->post('price', TRUE),
            'billing_cycle' => $this->input->post('billing_cycle', TRUE),
            'discount_percent' => (float)($this->input->post('discount_percent', TRUE) ?: 0),
            'free_services_per_period' => (int)($this->input->post('free_services_per_period', TRUE) ?: 0),
            'priority_booking' => (int)($this->input->post('priority_booking', TRUE) ?: 0),
            'status' => 1
        ];

        $id = $this->input->post('id', TRUE) ?: null;
        $saved_id = $this->membership->save_plan($plan_data, $id);

        echo json_encode(['success' => true, 'id' => $saved_id, 'message' => $id ? 'Plan updated.' : 'Plan created.']);
    }

    /**
     * Delete (deactivate) Membership Plan
     */
    public function membership_plan_delete() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_delete');

        $id = $this->input->post('id', TRUE);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }

        $this->load->model('membership_model', 'membership');
        $this->membership->delete_plan($id);
        echo json_encode(['success' => true, 'message' => 'Plan deactivated.']);
    }

    /**
     * Toggle plan status
     */
    public function membership_plan_toggle_status() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_edit');

        $id = $this->input->post('id', TRUE);
        $status = $this->input->post('status', TRUE);
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }
        $this->load->model('membership_model', 'membership');
        $this->membership->toggle_plan_status($id, $status);
        echo json_encode(['success' => true]);
    }

    /**
     * AJAX: Membership Plans Datatable
     */
    public function membership_plans_ajax() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_view');

        $this->load->model('membership_model', 'membership');

        $list = $this->membership->get_plan_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        foreach ($list as $plan) {
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = '<b>' . $plan->plan_name . '</b><br><small class="text-muted">' . $plan->plan_code . '</small>';
            $row[] = $this->data['currency'] . ' ' . store_number_format($plan->price);
            $row[] = '<span class="label label-info">' . ucfirst($plan->billing_cycle) . '</span>';
            $row[] = ($plan->discount_percent > 0) ? '<span class="text-green"><b>' . $plan->discount_percent . '% OFF</b></span>' : '<span class="text-muted">-</span>';
            $row[] = ($plan->status == 1)
                ? '<span class="label label-success" style="cursor:pointer" onclick="toggle_plan_status(' . $plan->id . ',0)"> Active </span>'
                : '<span class="label label-danger" style="cursor:pointer" onclick="toggle_plan_status(' . $plan->id . ',1)"> Inactive </span>';
            $actions = '<a class="btn btn-sm btn-primary" href="' . base_url('operations/membership_plan/' . $plan->id) . '" title="Edit"><i class="fa fa-edit"></i></a> ';
            $actions .= '<a class="btn btn-sm btn-danger" href="#" onclick="delete_plan(' . $plan->id . ')" title="Deactivate"><i class="fa fa-trash"></i></a>';
            $row[] = $actions;
            $data[] = $row;
        }

        echo json_encode([
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => $this->membership->count_plan_all(),
            "recordsFiltered" => $this->membership->count_plan_filtered(),
            "data" => $data,
        ]);
    }

    /**
     * Customer Memberships List
     */
    public function customer_memberships() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_view');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        $this->membership->update_expired_memberships();

        $data['memberships'] = $this->membership->get_store_memberships();
        $data['active_count'] = $this->membership->count_active_memberships($store_id);
        $data['expiring_count'] = $this->membership->count_expiring_soon(7);
        $data['plans'] = $this->membership->get_active_plans($store_id);

        $this->_render('Customer Memberships', 'operations/customer_memberships', $data);
    }

    /**
     * Assign / Renew Membership Form
     */
    public function assign_membership($customer_id = null) {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_add');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        $data['customer_id'] = $customer_id;
        $data['customers'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_customers')->result();
        $data['plans'] = $this->membership->get_active_plans($store_id);
        $data['edit_membership'] = null;

        $membership_id = $this->input->get('renew');
        if ($membership_id) {
            $data['edit_membership'] = $this->membership->get_customer_membership($membership_id);
        }

        $this->_render('Assign Membership', 'operations/assign_membership', $data);
    }

    /**
     * Save Customer Membership (AJAX)
     */
    public function membership_assign_save() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_add');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('customer_id', 'Customer', 'trim|required|numeric');
        $this->form_validation->set_rules('plan_id', 'Plan', 'trim|required|numeric');
        $this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $plan = $this->membership->get_plan($this->input->post('plan_id', TRUE));
        if (!$plan) {
            echo json_encode(['success' => false, 'message' => 'Invalid plan selected.']);
            return;
        }

        $start_date = $this->input->post('start_date', TRUE);
        $billing_cycle = $plan->billing_cycle;

        // Calculate end date based on billing cycle
        switch ($billing_cycle) {
            case 'monthly': $end_date = date('Y-m-d', strtotime($start_date . ' +1 month -1 day')); break;
            case 'quarterly': $end_date = date('Y-m-d', strtotime($start_date . ' +3 months -1 day')); break;
            case 'annual': $end_date = date('Y-m-d', strtotime($start_date . ' +1 year -1 day')); break;
            default: $end_date = date('Y-m-d', strtotime($start_date . ' +1 month -1 day'));
        }

        $membership_data = [
            'store_id' => $store_id,
            'customer_id' => (int)$this->input->post('customer_id', TRUE),
            'plan_id' => (int)$this->input->post('plan_id', TRUE),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'next_billing_date' => $end_date,
            'auto_renew' => (int)($this->input->post('auto_renew', TRUE) ?: 0),
            'status' => 'active',
            'payment_status' => 'paid',
            'amount_paid' => (float)($this->input->post('amount_paid', TRUE) ?: $plan->price),
            'payment_method' => $this->input->post('payment_method', TRUE) ?: 'cash',
            'notes' => $this->input->post('notes', TRUE)
        ];

        $membership_id = $this->input->post('membership_id', TRUE) ?: null;
        if ($membership_id) {
            $this->membership->renew_membership($membership_id, $membership_data);
        } else {
            $membership_id = $this->membership->assign_membership($membership_data);
        }

        echo json_encode(['success' => true, 'membership_id' => $membership_id, 'message' => 'Membership saved.']);
    }

    /**
     * Cancel Customer Membership
     */
    public function membership_cancel() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_delete');

        $id = $this->input->post('id', TRUE);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }

        $this->load->model('membership_model', 'membership');
        $this->membership->cancel_membership($id);
        echo json_encode(['success' => true, 'message' => 'Membership cancelled.']);
    }

    /**
     * AJAX: Customer Memberships Datatable
     */
    public function customer_memberships_ajax() {
        $this->_check_feature('memberships');
        $this->permission_check('memberships_view');

        $this->load->model('membership_model', 'membership');
        $store_id = get_current_store_id();

        $list = $this->membership->get_store_memberships();
        $data = [];
        $no = 0;
        foreach ($list as $m) {
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = '<b>' . $m->customer_name . '</b><br><small class="text-muted">' . $m->mobile . '</small>';
            $row[] = '<b>' . $m->plan_name . '</b><br><small>' . $m->plan_code . '</small>';
            $row[] = show_date($m->start_date) . ' <i class="fa fa-arrow-right text-muted"></i> ' . show_date($m->end_date);

            $today = strtotime(date('Y-m-d'));
            $end = strtotime($m->end_date);
            $days_left = round(($end - $today) / 86400);
            if ($m->status == 'active' && $days_left <= 7 && $days_left >= 0) {
                $row[] = '<span class="label label-warning">' . $days_left . ' days left</span>';
            } else if ($m->status == 'active') {
                $row[] = '<span class="label label-success">Active</span>';
            } else if ($m->status == 'expired') {
                $row[] = '<span class="label label-danger">Expired</span>';
            } else {
                $row[] = '<span class="label label-default">' . ucfirst($m->status) . '</span>';
            }

            $row[] = ($m->auto_renew ? '<span class="label label-info"><i class="fa fa-refresh"></i> Auto</span>' : '<span class="text-muted">-</span>');

            $actions = '<a class="btn btn-sm btn-success" href="' . base_url('operations/assign_membership/' . $m->customer_id . '?renew=' . $m->id) . '" title="Renew"><i class="fa fa-refresh"></i></a> ';
            $actions .= '<a class="btn btn-sm btn-danger" href="#" onclick="cancel_membership(' . $m->id . ')" title="Cancel"><i class="fa fa-times"></i></a>';
            $row[] = $actions;
            $data[] = $row;
        }

        echo json_encode([
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => count($list),
            "recordsFiltered" => count($list),
            "data" => $data,
        ]);
    }

    /**
     * AJAX: Get active membership discount for a customer (used by POS)
     */
    public function ajax_get_customer_membership() {
        $customer_id = $this->input->get('customer_id', TRUE);
        if (!$customer_id) {
            echo json_encode(null);
            return;
        }

        $this->load->model('membership_model', 'membership');
        $discount = $this->membership->get_customer_discount($customer_id);
        echo json_encode($discount);
    }

    /* ===================== TREATMENT NOTES ===================== */

    /**
     * Treatment Notes List
     */
    public function treatment_notes() {
        $this->_check_feature('treatment_notes');
        $this->permission_check('treatment_notes_view');

        $this->load->model('treatment_notes_model', 'notes');
        $store_id = get_current_store_id();

        $data['this_month_count'] = $this->notes->count_this_month($store_id);
        $data['latest_notes'] = $this->notes->get_latest($store_id, 5);

        $this->_render('Treatment Notes', 'operations/treatment_notes', $data);
    }

    /**
     * Add / Edit Treatment Note
     */
    public function treatment_note($id = null) {
        $this->_check_feature('treatment_notes');
        $this->permission_check('treatment_notes_add');

        $this->load->model('treatment_notes_model', 'notes');
        $store_id = get_current_store_id();

        $data['edit_note'] = null;
        if ($id) {
            $data['edit_note'] = $this->notes->get($id);
        }

        $data['customers'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_customers')->result();
        $data['staff'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_users')->result();

        $this->_render($id ? 'Edit Treatment Note' : 'New Treatment Note', 'operations/treatment_note', $data);
    }

    /**
     * Save Treatment Note (AJAX)
     */
    public function treatment_note_save() {
        $this->_check_feature('treatment_notes');
        $this->permission_check('treatment_notes_add');

        $this->load->model('treatment_notes_model', 'notes');
        $store_id = get_current_store_id();

        $this->form_validation->set_rules('customer_id', 'Customer', 'trim|required|numeric');
        $this->form_validation->set_rules('service_type', 'Service Type', 'trim|required');
        $this->form_validation->set_rules('treatment_date', 'Treatment Date', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $customer = $this->db->where('id', $this->input->post('customer_id', TRUE))->get('db_customers')->row();
        $staff = $this->db->where('id', $this->input->post('staff_id', TRUE))->get('db_users')->row();

        $note_data = [
            'store_id' => $store_id,
            'customer_id' => (int)$this->input->post('customer_id', TRUE),
            'service_type' => $this->input->post('service_type', TRUE),
            'notes' => $this->input->post('notes', TRUE),
            'treatment_date' => $this->input->post('treatment_date', TRUE),
            'staff_id' => (int)($this->input->post('staff_id', TRUE) ?: 0),
            'staff_name' => $staff ? $staff->first_name . ' ' . $staff->last_name : '',
            'products_used' => $this->input->post('products_used', TRUE),
            'recommendations' => $this->input->post('recommendations', TRUE),
        ];

        // Parse consumable items
        $consumables = [];
        $item_ids = $this->input->post('consumable_item_id', TRUE) ?: [];
        $item_qtys = $this->input->post('consumable_qty', TRUE) ?: [];
        for ($i = 0; $i < count($item_ids); $i++) {
            $item_id = (int) ($item_ids[$i] ?? 0);
            $qty = floatval($item_qtys[$i] ?? 0);
            if ($item_id > 0 && $qty > 0) {
                $consumables[] = ['item_id' => $item_id, 'qty' => $qty];
            }
        }

        $id = $this->input->post('id', TRUE) ?: null;
        $saved_id = $this->notes->save($note_data, $id, $consumables);

        echo json_encode(['success' => true, 'id' => $saved_id, 'message' => $id ? 'Note updated.' : 'Note saved.']);
    }

    /**
     * AJAX: Get Consumable Items (Not for Sale) for Treatment Notes
     */
    public function ajax_consumable_items() {
        $this->_check_feature('treatment_notes');
        $this->permission_check('treatment_notes_view');
        $store_id = get_current_store_id();
        $term = $this->input->get('term', TRUE);
        $this->db->select('id, item_name, stock, consumable_unit');
        $this->db->from('db_items');
        $this->db->where('store_id', $store_id);
        $this->db->where('status', 1);
        $this->db->where('(not_for_sale = 1)');
        if ($term) {
            $this->db->like('item_name', $term);
        }
        $this->db->limit(50);
        $items = $this->db->get()->result();
        echo json_encode($items);
    }

    /**
     * Delete Treatment Note
     */
    public function treatment_note_delete() {
        $this->_check_feature('treatment_notes');
        $this->permission_check('treatment_notes_delete');

        $id = $this->input->post('id', TRUE);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }

        $this->load->model('treatment_notes_model', 'notes');
        $this->notes->delete($id);
        echo json_encode(['success' => true, 'message' => 'Note deleted.']);
    }

    /**
     * AJAX: Treatment Notes Datatable
     */
    public function treatment_notes_ajax() {
        $this->_check_feature('treatment_notes');
        $this->permission_check('treatment_notes_view');

        $this->load->model('treatment_notes_model', 'notes');

        $list = $this->notes->get_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        foreach ($list as $note) {
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = '<b>' . htmlspecialchars($note->customer_name ?? 'Unknown') . '</b><br><small class="text-muted">' . htmlspecialchars($note->mobile ?? '') . '</small>';
            $row[] = htmlspecialchars($note->service_type);
            $row[] = '<span class="text-muted" style="font-size:12px;">' . nl2br(htmlspecialchars(mb_strimwidth($note->notes ?? '', 0, 100, '...'))) . '</span>';
            $row[] = htmlspecialchars($note->staff_name ?: '-');
            $row[] = show_date($note->treatment_date);
            $actions = '<a class="btn btn-sm btn-primary" href="' . base_url('operations/treatment_note/' . $note->id) . '" title="Edit"><i class="fa fa-edit"></i></a> ';
            $actions .= '<a class="btn btn-sm btn-danger" href="#" onclick="delete_note(' . $note->id . ')" title="Delete"><i class="fa fa-trash"></i></a>';
            $row[] = $actions;
            $data[] = $row;
        }

        echo json_encode([
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => $this->notes->count_all(),
            "recordsFiltered" => $this->notes->count_filtered(),
            "data" => $data,
        ]);
    }

    /* ===================== MENU ITEMS (Restaurant Quick-Add) ===================== */
    public function menu_items() {
        $this->_check_feature('kitchen_workflow');
        $this->permission_check('items_add');

        $this->load->model('items_model', 'items');

        $store_id = get_current_store_id();

        if ($this->input->post('save_menu_item')) {
            // Minimal restaurant item save — reuse items_model logic
            $this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
            $this->form_validation->set_rules('sales_price', 'Menu Price', 'trim|required|numeric');
            $this->form_validation->set_rules('category_id', 'Category', 'trim|required');

            if ($this->form_validation->run() == TRUE) {
                $result = $this->items->save_record(['command'=>'save']);
                if ($result === 'success' || strpos($result, 'success') !== false) {
                    $this->session->set_flashdata('success', 'Menu item added! It is now available in POS search.');
                    redirect('operations/kitchen');
                } else {
                    $data['error'] = $result;
                }
            }
        }

        $data['categories'] = $this->db->where('status', 1)->get('db_category')->result();
        $data['units'] = $this->db->get('db_units')->result();
        $data['taxes'] = $this->db->get('db_tax')->result();
        $this->_render('Menu Items', 'operations/menu_items', $data);
    }

    /* ===================== KITCHEN WORKFLOW ===================== */
    public function kitchen() {
        $this->_check_feature('kitchen_workflow');
        $this->permission_check('store_view');

        $this->load->model('kitchen_model', 'kitchen');
        $store_id = get_current_store_id();

        // Auto-sync any new POS sales into kitchen queue
        $this->kitchen->sync_new_orders($store_id);

        // AJAX polling: return just JSON counts
        if ($this->input->get('ajax')) {
            header('Content-Type: application/json');
            echo json_encode([
                'status_counts' => $this->kitchen->count_by_status($store_id)
            ]);
            return;
        }

        $data['orders'] = $this->kitchen->get_orders($store_id, ['new','preparing','ready']);
        $data['status_counts'] = $this->kitchen->count_by_status($store_id);
        $data['served'] = $this->kitchen->get_served_orders($store_id, 10);
        $this->_render('Kitchen Display', 'operations/kitchen', $data);
    }

    public function kitchen_update_status() {
        $this->_check_feature('kitchen_workflow');
        $this->permission_check('store_view');

        $kitchen_order_id = $this->input->post('kitchen_order_id', TRUE);
        $status = $this->input->post('status', TRUE);

        if (empty($kitchen_order_id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $this->load->model('kitchen_model', 'kitchen');
        $ok = $this->kitchen->update_status($kitchen_order_id, $status);

        echo json_encode(['success' => $ok, 'status' => $status]);
    }

    /* ===================== LAUNDRY WORKFLOW ===================== */
    public function laundry() {
        $this->_check_feature('laundry_workflow');
        $this->permission_check('store_view');

        $this->load->model('laundry_model', 'laundry');
        $store_id = get_current_store_id();

        // Auto-run DB migration for laundry tables
        if (!$this->db->table_exists('db_laundry_orders')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_laundry_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sales_id INT NOT NULL,
                store_id INT NOT NULL,
                tag_number VARCHAR(50) NULL,
                service_type VARCHAR(50) NULL DEFAULT 'standard',
                notes TEXT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'dropped_off',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_sales_id (sales_id),
                INDEX idx_store_id (store_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            // Fix old schema: ensure datetime columns have defaults so NULL doesn't become 1970
            $this->db->query("ALTER TABLE db_laundry_orders
                MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                MODIFY updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            // Patch any existing NULL dates from old schema
            $this->db->query("UPDATE db_laundry_orders
                SET created_at = NOW(), updated_at = NOW()
                WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
        }
        if (!$this->db->table_exists('db_laundry_order_items')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_laundry_order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                laundry_order_id INT NOT NULL,
                salesitem_id INT NOT NULL,
                item_id INT NOT NULL,
                service_type VARCHAR(20) NOT NULL DEFAULT 'wash_iron',
                item_status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_laundry_order_id (laundry_order_id),
                INDEX idx_item_status (item_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        // Auto-sync any new POS sales into laundry queue
        $this->laundry->sync_new_orders($store_id);

        // Load workflow config from industry_settings_json
        $profile = mp_get_store_profile();
        $lw_config = [];
        if (!empty($profile['industry_settings_json'])) {
            $json = json_decode($profile['industry_settings_json'], true);
            if (!empty($json['laundry_workflow'])) {
                $lw_config = $json['laundry_workflow'];
            }
        }
        $data['lw_stages'] = [
            'has_washing' => $lw_config['has_washing'] ?? true,
            'has_ironing' => $lw_config['has_ironing'] ?? true,
        ];

        // Save workflow config if posted
        if ($this->input->post('save_lw_config')) {
            $new_config = [
                'has_washing' => (bool)$this->input->post('has_washing'),
                'has_ironing' => (bool)$this->input->post('has_ironing'),
            ];
            $json = json_decode($profile['industry_settings_json'] ?: '{}', true);
            $json['laundry_workflow'] = $new_config;
            $this->db->where('id', $store_id)->update('db_store', ['industry_settings_json' => json_encode($json)]);
            $this->session->set_flashdata('success', 'Workflow settings saved.');
            redirect('operations/laundry');
        }

        // AJAX polling: return just JSON counts
        if ($this->input->get('ajax')) {
            header('Content-Type: application/json');
            echo json_encode([
                'status_counts' => $this->laundry->count_by_status($store_id)
            ]);
            return;
        }

        $data['orders'] = $this->laundry->get_orders($store_id, ['dropped_off', 'washing', 'ironing', 'ready']);
        $data['status_counts'] = $this->laundry->count_by_status($store_id);
        $data['collected'] = $this->laundry->get_collected_orders($store_id, 10);
        $this->_render('Laundry Workflow', 'operations/laundry', $data);
    }

    public function laundry_update_status() {
        $this->_check_feature('laundry_workflow');
        $this->permission_check('store_view');

        $id = $this->input->post('laundry_order_id', TRUE);
        $status = $this->input->post('status', TRUE);
        $store_id = get_current_store_id();

        $this->load->model('laundry_model', 'laundry');
        $ok = $this->laundry->update_status((int)$id, $store_id, $status);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    }

    /* ===================== PRODUCTION WORKFLOW ===================== */
    public function production() {
        $this->_check_feature('production_workflow');
        $this->permission_check('store_view');
        $this->_render('Production Workflow', 'operations/production');
    }

    /* ===================== PRICE CATALOGUE ===================== */
    public function price_catalogue() {
        $this->_check_feature('price_catalogue');
        $this->permission_check('store_view');
        $this->_render('Price Catalogue', 'operations/price_catalogue');
    }

    /* ===================== PUBLIC CATALOGUE SETTINGS ===================== */
    public function public_catalogue_settings() {
        $this->_check_feature('public_catalogue');
        $this->permission_check('store_view');
        $store_id = get_current_store_id();
        $profile = $this->bp_model->get_profile($store_id);
        $settings = [];
        if (!empty($profile['industry_settings_json'])) {
            $decoded = json_decode($profile['industry_settings_json'], true);
            if (is_array($decoded) && isset($decoded['catalogue'])) {
                $settings = $decoded['catalogue'];
            }
        }
        $this->_render('Public Catalogue Settings', 'operations/public_catalogue_settings', ['settings' => $settings]);
    }

    /* ===================== STAFF ASSIGNMENT ===================== */
    public function staff_assignment() {
        $this->_check_feature('staff_assignment');
        $this->permission_check('store_view');

        $store_id = get_current_store_id();

        // Handle AJAX: assign/unassign staff to service
        if ($this->input->is_ajax_request()) {
            $action = $this->input->post('action', TRUE);
            $service_id = (int)$this->input->post('service_id', TRUE);
            $staff_id   = (int)$this->input->post('staff_id', TRUE);

            if ($action === 'assign' && $service_id && $staff_id) {
                // Upsert
                $exists = $this->db->where('store_id', $store_id)->where('service_id', $service_id)->where('staff_id', $staff_id)->get('db_service_staff')->row();
                if ($exists) {
                    $this->db->where('id', $exists->id)->update('db_service_staff', ['status' => 1]);
                } else {
                    $this->db->insert('db_service_staff', [
                        'store_id'   => $store_id,
                        'service_id' => $service_id,
                        'staff_id'   => $staff_id,
                        'status'     => 1
                    ]);
                }
                echo json_encode(['success' => true]);
                return;
            }
            if ($action === 'unassign' && $service_id && $staff_id) {
                $this->db->where('store_id', $store_id)->where('service_id', $service_id)->where('staff_id', $staff_id)->delete('db_service_staff');
                echo json_encode(['success' => true]);
                return;
            }
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        // Load all active services for this store
        $services = $this->db->where('status', 1)->where('store_id', $store_id)->order_by('service_name', 'asc')->get('db_services')->result();

        // Load all active staff for this store
        $staff_list = $this->db->where('status', 1)->where('store_id', $store_id)->order_by('username', 'asc')->get('db_users')->result();

        // Load existing assignments
        $assignments = [];
        $assigned = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_service_staff')->result();
        foreach ($assigned as $a) {
            $assignments[$a->service_id][$a->staff_id] = true;
        }

        $data['services']    = $services;
        $data['staff_list']    = $staff_list;
        $data['assignments']   = $assignments;
        $this->_render('Staff Assignment', 'operations/staff_assignment', $data);
    }

    /* ===================== STAFF COMMISSION ===================== */
    public function staff_commission() {
        $this->_check_feature('staff_commission');
        $this->permission_check('store_view');

        $store_id = get_current_store_id();
        $staff_id = $this->input->get('staff_id', TRUE) ?: '';
        $from_date = $this->input->get('from_date', TRUE) ?: date('Y-m-01');
        $to_date = $this->input->get('to_date', TRUE) ?: date('Y-m-d');

        $this->db->select('si.staff_id, u.username as staff_name, si.sales_id, s.sales_code, s.sales_date, si.item_id, i.item_name, si.sales_qty, si.price_per_unit, si.commission_amount, si.total_cost');
        $this->db->from('db_salesitems si');
        $this->db->join('db_sales s', 's.id = si.sales_id', 'left');
        $this->db->join('db_items i', 'i.id = si.item_id', 'left');
        $this->db->join('db_users u', 'u.id = si.staff_id', 'left');
        $this->db->where('si.store_id', $store_id);
        $this->db->where('si.staff_id IS NOT NULL', null, false);
        $this->db->where('si.commission_amount >', 0);
        $this->db->where("(s.sales_date >= '$from_date' AND s.sales_date <= '$to_date')", null, false);
        if(!empty($staff_id)){
            $this->db->where('si.staff_id', $staff_id);
        }
        $this->db->order_by('s.sales_date', 'desc');
        $data['commissions'] = $this->db->get()->result();

        // Summary per staff
        $this->db->select('si.staff_id, u.username as staff_name, SUM(si.commission_amount) as total_commission, COUNT(DISTINCT si.sales_id) as invoice_count, SUM(si.sales_qty) as total_qty');
        $this->db->from('db_salesitems si');
        $this->db->join('db_users u', 'u.id = si.staff_id', 'left');
        $this->db->join('db_sales s', 's.id = si.sales_id', 'left');
        $this->db->where('si.store_id', $store_id);
        $this->db->where('si.staff_id IS NOT NULL', null, false);
        $this->db->where('si.commission_amount >', 0);
        $this->db->where("(s.sales_date >= '$from_date' AND s.sales_date <= '$to_date')", null, false);
        if(!empty($staff_id)){
            $this->db->where('si.staff_id', $staff_id);
        }
        $this->db->group_by('si.staff_id');
        $data['summary'] = $this->db->get()->result();

        $data['staff_list'] = $this->db->where('status', 1)->where('store_id', $store_id)->get('db_users')->result();
        $data['selected_staff_id'] = $staff_id;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->_render('Staff Commission', 'operations/staff_commission', $data);
    }

    /* ===================== TABLE MANAGEMENT ===================== */
    public function table_management() {
        $this->_check_feature('table_management');
        $this->permission_check('store_view');

        $this->load->model('tables_model', 'tables');
        $store_id = get_current_store_id();

        // Handle delete
        $del_id = $this->input->get('delete');
        if (!empty($del_id)) {
            $this->tables->delete((int)$del_id, $store_id);
            $this->session->set_flashdata('success', 'Table deleted.');
            redirect('operations/table_management');
        }

        // Handle save (add / edit)
        if ($this->input->post('save_table')) {
            $this->form_validation->set_rules('table_name', 'Table Name', 'trim|required');
            $this->form_validation->set_rules('capacity', 'Capacity', 'trim|integer');

            if ($this->form_validation->run() == TRUE) {
                $save_data = [
                    'store_id'    => $store_id,
                    'table_name'  => $this->input->post('table_name', TRUE),
                    'table_code'  => $this->input->post('table_code', TRUE),
                    'zone'        => $this->input->post('zone', TRUE),
                    'capacity'    => (int)$this->input->post('capacity', TRUE) ?: 4,
                    'status'      => $this->input->post('status', TRUE) ?: 'available',
                    'sort_order'  => (int)$this->input->post('sort_order', TRUE),
                ];
                $edit_id = $this->input->post('edit_id', TRUE);
                $this->tables->save($save_data, $edit_id ?: null);
                $this->session->set_flashdata('success', $edit_id ? 'Table updated.' : 'Table added.');
                redirect('operations/table_management');
            }
        }

        $data['tables'] = $this->tables->get_all($store_id);
        $data['zones'] = $this->tables->get_zones($store_id);
        $data['status_counts'] = $this->tables->count_by_status($store_id);
        $data['edit_table'] = null;

        $edit_id = $this->input->get('edit');
        if (!empty($edit_id)) {
            $data['edit_table'] = $this->tables->get_by_id((int)$edit_id, $store_id);
        }

        $this->_render('Table Management', 'operations/table_management', $data);
    }

    public function table_update_status() {
        $this->_check_feature('table_management');
        $this->permission_check('store_view');

        $id = $this->input->post('table_id', TRUE);
        $status = $this->input->post('status', TRUE);
        $store_id = get_current_store_id();

        $this->load->model('tables_model', 'tables');
        $ok = $this->tables->update_status((int)$id, $store_id, $status);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    }

    /* ===================== DELIVERY SCHEDULING ===================== */
    public function delivery_scheduling() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $this->_render('Delivery Scheduling', 'operations/delivery_scheduling');
    }

    public function delivery_schedules_ajax() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $list = $this->delivery_model->get_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        foreach ($list as $row) {
            $no++;
            $status_badge = [
                'planned' => 'label-default',
                'ready' => 'label-info',
                'out_for_delivery' => 'label-warning',
                'completed' => 'label-success',
                'cancelled' => 'label-danger',
            ][$row->status] ?? 'label-default';

            $items_count = $this->db->where('schedule_id', $row->id)->count_all_results('db_delivery_schedule_items');
            $delivered = $this->db->where('schedule_id', $row->id)->where('delivery_status', 'delivered')->count_all_results('db_delivery_schedule_items');

            $r = [];
            $r[] = $no;
            $r[] = '<b>' . $row->schedule_code . '</b>';
            $r[] = $row->route_name ?: '-';
            $r[] = show_date($row->schedule_date);
            $r[] = $row->driver_name ?: '<span class="text-muted">Unassigned</span>';
            $r[] = $row->vehicle ?: '-';
            $r[] = '<span class="label ' . $status_badge . '">' . ucwords(str_replace('_', ' ', $row->status)) . '</span>';
            $r[] = '<span class="badge bg-green">' . $delivered . '</span> / ' . $items_count;

            $str = '';
            $str .= '<a class="btn btn-sm btn-info" href="' . base_url('operations/delivery_schedule_view/' . $row->id) . '" title="View"><i class="fa fa-eye"></i></a> ';
            if ($this->permissions('store_view')) {
                $str .= '<a class="btn btn-sm btn-primary" href="' . base_url('operations/delivery_schedule_form/' . $row->id) . '" title="Edit"><i class="fa fa-edit"></i></a> ';
            }
            if ($this->permissions('store_view')) {
                $str .= '<button class="btn btn-sm btn-danger" onclick="delete_schedule(' . $row->id . ')" title="Delete"><i class="fa fa-trash"></i></button>';
            }
            $r[] = $str;
            $data[] = $r;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->delivery_model->count_all(),
            "recordsFiltered" => $this->delivery_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function delivery_schedule_form($id = '') {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $data = [];
        if (!empty($id)) {
            $data = $this->delivery_model->get_details($id);
            $data['schedule_items'] = $this->delivery_model->get_schedule_items($id);
        }
        $data['drivers'] = $this->delivery_model->get_drivers(get_current_store_id());
        $data['pending_sales'] = $this->delivery_model->get_pending_sales(get_current_store_id());
        $this->_render(empty($id) ? 'New Delivery Schedule' : 'Edit Delivery Schedule', 'operations/delivery_schedule_form', $data);
    }

    public function delivery_schedule_save() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $result = $this->delivery_model->save_and_update();
        echo $result;
    }

    public function delivery_schedule_view($id) {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $data = $this->delivery_model->get_details($id);
        $data['schedule_items'] = $this->delivery_model->get_schedule_items($id);
        $this->_render('Delivery Route — ' . ($data['route_name'] ?: $data['schedule_code']), 'operations/delivery_schedule_view', $data);
    }

    public function delivery_schedule_delete() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $id = $this->input->post('q_id');
        echo $this->delivery_model->delete_schedules($id);
    }

    public function delivery_schedule_status() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        echo $this->delivery_model->update_status($id, $status);
    }

    // AJAX: Get pending sales for delivery assignment
    public function ajax_sales_for_delivery() {
        $this->_check_feature('delivery_scheduling');
        $this->load->model('delivery_model');
        $sales = $this->delivery_model->get_pending_sales(get_current_store_id());
        echo json_encode($sales);
    }

    // AJAX: Get drivers
    public function ajax_drivers() {
        $this->_check_feature('delivery_scheduling');
        $this->load->model('delivery_model');
        $drivers = $this->delivery_model->get_drivers(get_current_store_id());
        echo json_encode($drivers);
    }

    // AJAX: Get single driver
    public function ajax_driver_detail() {
        $this->_check_feature('delivery_scheduling');
        $this->load->model('delivery_model');
        $id = (int)$this->input->post('id');
        $driver = $this->delivery_model->get_driver($id);
        if (!$driver) {
            echo json_encode(['status' => false, 'message' => 'Driver not found']);
            return;
        }
        echo json_encode(['status' => true, 'driver' => $driver]);
    }

    // AJAX: Save driver
    public function ajax_save_driver() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $res = $this->delivery_model->save_driver();
        echo json_encode(['status' => $res == 'success', 'message' => $res == 'success' ? 'Saved' : 'Failed', 'csrf_hash' => $this->security->get_csrf_hash()]);
    }

    // AJAX: Delete driver
    public function ajax_delete_driver() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $id = $this->input->post('id');
        $res = $this->delivery_model->delete_driver($id);
        echo json_encode(['status' => $res == 'success', 'message' => $res == 'success' ? 'Removed' : 'Failed', 'csrf_hash' => $this->security->get_csrf_hash()]);
    }

    /**
     * Driver profile page with route history
     */
    public function driver_profile($driver_id = 0) {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');

        $driver = $this->delivery_model->get_driver($driver_id);
        if (!$driver) {
            show_404();
            return;
        }

        $history = $this->delivery_model->get_driver_routes($driver_id);

        $data['page_title'] = 'Driver Profile: ' . ($driver->name ?? 'Unknown');
        $data['driver'] = $driver;
        $data['routes'] = $history['routes'];
        $data['stats'] = $history['stats'];

        $this->load->view('operations/driver_profile', $data);
    }

    // AJAX: Mark item delivered
    public function ajax_mark_delivered() {
        $this->_check_feature('delivery_scheduling');
        $this->permission_check('store_view');
        $this->load->model('delivery_model');
        $item_id = $this->input->post('item_id');
        $status = $this->input->post('status');
        $notes = $this->input->post('notes', TRUE);
        echo $this->delivery_model->mark_item_status($item_id, $status, $notes);
    }

    /* ===================== WARRANTY LOOKUP ===================== */
    public function warranty_lookup() {
        if (!mp_feature_enabled('serial_number_tracking') && !mp_feature_enabled('imei_tracking') && !mp_feature_enabled('warranty_tracking')) {
            show_error('This feature is not enabled for your business profile.', 403);
            exit;
        }
        $this->permission_check('store_view');
        $search = $this->input->get('search', TRUE);
        $results = [];
        if (!empty($search)) {
            // Search sold items first (from sales records)
            $this->db->select('si.*, s.sales_date, s.customer_id, s.sales_code, i.item_name, i.warranty_months, c.customer_name, c.mobile');
            $this->db->from('db_salesitems si');
            $this->db->join('db_sales s', 's.id = si.sales_id', 'left');
            $this->db->join('db_items i', 'i.id = si.item_id', 'left');
            $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
            $this->db->where('s.store_id', get_current_store_id());
            $this->db->where('s.sales_status', 'Final');
            $this->db->group_start();
            $this->db->like('si.sold_serial_number', $search);
            $this->db->or_like('si.sold_imei_number', $search);
            $this->db->group_end();
            $results = $this->db->get()->result();

            // Also search unsold units in db_item_barcodes
            if(empty($results)){
                $this->db->select("b.id as barcode_id, b.item_id, b.serial_number as sold_serial_number, b.imei_number as sold_imei_number, b.warranty_months, b.created_date as sales_date, i.item_name, 'In Stock' as sales_code, NULL as customer_id, NULL as customer_name, NULL as mobile, 0 as sales_id");
                $this->db->from('db_item_barcodes b');
                $this->db->join('db_items i', 'i.id = b.item_id', 'left');
                $this->db->where('b.status', 1);
                $this->db->group_start();
                $this->db->like('b.serial_number', $search);
                $this->db->or_like('b.imei_number', $search);
                $this->db->group_end();
                $results = $this->db->get()->result();
            }
        }
        $this->_render('Warranty Lookup', 'operations/warranty_lookup', ['results' => $results, 'search' => $search]);
    }

    /* ===================== RECIPE CATEGORIES ===================== */
    public function recipe_categories() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('store_view');
        $this->_render('Recipe Categories', 'operations/recipe-categories');
    }

    public function recipe_categories_ajax() {
        $this->_check_feature('recipe_tracking');
        $this->load->model('recipe_category_model', 'rc');
        $list = $this->rc->get_datatables();
        $data = [];
        $no = $_POST['start'] ?? 0;
        foreach ($list as $cat) {
            $no++;
            $row = [];
            $row[] = '<input type="checkbox" name="checkbox[]" value='.$cat->id.' class="checkbox column_checkbox" >';
            $row[] = htmlspecialchars($cat->name);
            $status = ($cat->status == 1)
                ? "<span onclick='update_status(".$cat->id.",0)' id='span_".$cat->id."' class='label label-success' style='cursor:pointer'>Active</span>"
                : "<span onclick='update_status(".$cat->id.",1)' id='span_".$cat->id."' class='label label-danger' style='cursor:pointer'>Inactive</span>";
            $row[] = $status;
            $actions = '<div class="btn-group"><a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">Action <span class="caret"></span></a><ul class="dropdown-menu dropdown-light pull-right">';
            if ($this->permissions('store_view')) {
                $actions .= '<li><a href="'.base_url('operations/recipe_category_update/'.$cat->id).'"><i class="fa fa-fw fa-edit text-blue"></i>Edit</a></li>';
            }
            if ($this->permissions('store_view')) {
                $actions .= '<li><a style="cursor:pointer" onclick="delete_category('.$cat->id.')"><i class="fa fa-fw fa-trash text-red"></i>Delete</a></li>';
            }
            $actions .= '</ul></div>';
            $row[] = $actions;
            $data[] = $row;
        }
        $output = [
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => $this->rc->count_all(),
            "recordsFiltered" => $this->rc->count_filtered(),
            "data" => $data,
        ];
        echo json_encode($output);
    }

    public function recipe_category_update($id = null) {
        $this->_check_feature('recipe_tracking');
        $this->permission_check('store_view');
        $this->load->model('recipe_category_model', 'rc');
        $data = [];
        if ($id) {
            $data = $this->rc->get_details($id, $data);
        }
        $this->_render($id ? 'Edit Recipe Category' : 'New Recipe Category', 'operations/recipe-category', $data);
    }

    public function recipe_category_save() {
        $this->_check_feature('recipe_tracking');
        $this->load->model('recipe_category_model', 'rc');
        $this->form_validation->set_rules('name', 'Category Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            echo "Please Enter Category name.";
            return;
        }
        if ($this->input->post('q_id')) {
            echo $this->rc->update_category();
        } else {
            echo $this->rc->verify_and_save();
        }
    }

    public function recipe_category_update_status() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check_with_msg('store_view');
        $this->load->model('recipe_category_model', 'rc');
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        $this->rc->update_status($id, $status);
    }

    public function recipe_category_delete() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check_with_msg('store_view');
        $this->load->model('recipe_category_model', 'rc');
        $id = $this->input->post('q_id');
        echo $this->rc->delete_categories_from_table($id);
    }

    public function recipe_category_multi_delete() {
        $this->_check_feature('recipe_tracking');
        $this->permission_check_with_msg('store_view');
        $this->load->model('recipe_category_model', 'rc');
        $ids = implode(",", $_POST['checkbox']);
        echo $this->rc->delete_categories_from_table($ids);
    }
}
