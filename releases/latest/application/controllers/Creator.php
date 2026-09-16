<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Creator Workspace
 * Dedicated admin experience for the Creator / Digital Store business type:
 * digital products, courses, memberships, students, members and sales.
 */
class Creator extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('digital_products') && !mp_feature_enabled('courses') && !mp_feature_enabled('memberships')){
            show_404();
            return;
        }
        $this->load->model('course_model');
        $this->load->model('creator_membership_model', 'membership_model');
        $this->load->model('storefront_model');
    }

    private function _render($title, $view, $data = []){
        $d = $this->data;
        $d['page_title'] = $title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    private function _has_online_orders(){
        return $this->db->table_exists('db_online_orders');
    }

    /* ===================== DASHBOARD ===================== */
    public function index(){
        $storeId = get_current_store_id();
        $month = date('Y-m-01');
        $today = date('Y-m-d');

        $kpi = ['revenue_month' => 0, 'revenue_today' => 0, 'orders_month' => 0, 'orders_pending' => 0];
        $recent_orders = [];
        $top_products = [];
        if($this->_has_online_orders()){
            $row = $this->db->query("SELECT
                COALESCE(SUM(CASE WHEN payment_status='paid' AND created_at >= '$month' THEN grand_total ELSE 0 END),0) AS revenue_month,
                COALESCE(SUM(CASE WHEN payment_status='paid' AND DATE(created_at)='$today' THEN grand_total ELSE 0 END),0) AS revenue_today,
                SUM(CASE WHEN created_at >= '$month' THEN 1 ELSE 0 END) AS orders_month,
                SUM(CASE WHEN order_status='pending' THEN 1 ELSE 0 END) AS orders_pending
                FROM db_online_orders WHERE store_id=".(int)$storeId." AND status=1")->row();
            if($row){
                $kpi['revenue_month'] = (float)$row->revenue_month;
                $kpi['revenue_today'] = (float)$row->revenue_today;
                $kpi['orders_month'] = (int)$row->orders_month;
                $kpi['orders_pending'] = (int)$row->orders_pending;
            }
            $recent_orders = $this->db->where('store_id', $storeId)->where('status', 1)->order_by('id', 'desc')->limit(8)->get('db_online_orders')->result();

            if($this->db->table_exists('db_online_order_items')){
                $top_products = $this->db->query("SELECT oi.item_id, oi.item_name, oi.item_type, SUM(oi.qty) AS qty, SUM(oi.total_price) AS revenue
                    FROM db_online_order_items oi
                    INNER JOIN db_online_orders o ON o.id = oi.order_id
                    WHERE o.store_id=".(int)$storeId." AND o.payment_status='paid' AND o.status=1
                    GROUP BY oi.item_id, oi.item_name, oi.item_type
                    ORDER BY revenue DESC LIMIT 6")->result();
            }
        }

        $counts = [
            'digital'    => $this->db->where('store_id', $storeId)->where('status', 1)->where('product_type', 'digital')->count_all_results('db_items'),
            'course'     => $this->db->where('store_id', $storeId)->where('status', 1)->where('product_type', 'course')->count_all_results('db_items'),
            'membership' => $this->db->where('store_id', $storeId)->where('status', 1)->where('product_type', 'membership')->count_all_results('db_items'),
        ];
        $students = $this->db->table_exists('db_course_enrollments')
            ? $this->db->where('store_id', $storeId)->where('status', 'active')->count_all_results('db_course_enrollments') : 0;
        $members = $this->db->table_exists('db_membership_subscriptions')
            ? $this->db->where('store_id', $storeId)->where('status', 'active')->where('end_date >=', $today)->count_all_results('db_membership_subscriptions') : 0;
        $downloads = 0;
        if($this->_has_online_orders() && $this->db->table_exists('db_online_order_items') && $this->db->field_exists('download_count', 'db_online_order_items')){
            $dl = $this->db->query("SELECT COALESCE(SUM(oi.download_count),0) AS total FROM db_online_order_items oi
                INNER JOIN db_online_orders o ON o.id=oi.order_id WHERE o.store_id=".(int)$storeId)->row();
            $downloads = $dl ? (int)$dl->total : 0;
        }

        $settings = $this->storefront_model->getSettings($storeId);

        $this->_render('Creator Dashboard', 'creator/dashboard', [
            'kpi' => $kpi,
            'counts' => $counts,
            'students' => $students,
            'members' => $members,
            'downloads' => $downloads,
            'recent_orders' => $recent_orders,
            'top_products' => $top_products,
            'store_slug' => $settings->store_slug ?? '',
        ]);
    }

    /* ===================== PRODUCTS ===================== */
    public function products($type = ''){
        $storeId = get_current_store_id();
        $allowed = ['digital', 'course', 'membership'];
        $type = in_array($type, $allowed) ? $type : '';

        $this->db->select('i.*, c.category_name')
            ->from('db_items i')
            ->join('db_category c', 'c.id = i.category_id', 'left')
            ->where('i.store_id', $storeId)
            ->where('i.status', 1);
        if($type){
            $this->db->where('i.product_type', $type);
        } else {
            $this->db->where_in('i.product_type', $allowed);
        }
        $items = $this->db->order_by('i.id', 'desc')->get()->result();

        // Sales per item from online orders
        $sales = [];
        if($this->_has_online_orders() && $this->db->table_exists('db_online_order_items')){
            $rows = $this->db->query("SELECT oi.item_id, SUM(oi.qty) AS qty, SUM(oi.total_price) AS revenue
                FROM db_online_order_items oi INNER JOIN db_online_orders o ON o.id=oi.order_id
                WHERE o.store_id=".(int)$storeId." AND o.payment_status='paid' AND o.status=1 GROUP BY oi.item_id")->result();
            foreach($rows as $r){ $sales[$r->item_id] = $r; }
        }

        // Course / membership linkage
        $courses = [];
        $memberships = [];
        if($this->db->table_exists('db_courses')){
            foreach($this->db->where('store_id', $storeId)->where('status', 1)->get('db_courses')->result() as $c){ $courses[$c->item_id] = $c; }
        }
        if($this->db->table_exists('db_memberships')){
            foreach($this->db->where('store_id', $storeId)->where('status', 1)->get('db_memberships')->result() as $m){ $memberships[$m->item_id] = $m; }
        }

        $titles = ['' => 'All Products', 'digital' => 'Digital Products', 'course' => 'Courses', 'membership' => 'Memberships'];
        $this->_render($titles[$type], 'creator/products', [
            'items' => $items,
            'type' => $type,
            'sales' => $sales,
            'courses' => $courses,
            'memberships' => $memberships,
        ]);
    }

    /* ===================== PRODUCT FORM (dedicated, not the retail item form) ===================== */
    private $allowed_types = ['digital' => 'digital_products', 'course' => 'courses', 'membership' => 'memberships'];

    private function _type_or_404($type){
        if(!isset($this->allowed_types[$type]) || !mp_feature_enabled($this->allowed_types[$type])){ show_404(); exit; }
        return $type;
    }

    private function _form_data($type, $item = null){
        $storeId = get_current_store_id();
        $d = [
            'type'       => $type,
            'item'       => $item,
            'categories' => $this->db->where('store_id', $storeId)->where('status', 1)->order_by('category_name')->get('db_category')->result(),
            'course'     => null,
            'membership' => null,
            'lesson_count' => 0,
        ];
        if($item){
            if($type === 'course' && $this->db->table_exists('db_courses')){
                $d['course'] = $this->db->where('item_id', $item->id)->where('store_id', $storeId)->where('status', 1)->get('db_courses')->row();
                if($d['course']) $d['lesson_count'] = $this->db->where('course_id', $d['course']->id)->where('is_published', 1)->count_all_results('db_course_lessons');
            }
            if($type === 'membership' && $this->db->table_exists('db_memberships')){
                $d['membership'] = $this->db->where('item_id', $item->id)->where('store_id', $storeId)->where('status', 1)->get('db_memberships')->row();
            }
        }
        return $d;
    }

    public function create($type = 'digital'){
        $type = $this->_type_or_404($type);
        $labels = ['digital' => 'New Digital Product', 'course' => 'New Course', 'membership' => 'New Membership'];
        $this->_render($labels[$type], 'creator/product_form', $this->_form_data($type));
    }

    public function edit($id = 0){
        $storeId = get_current_store_id();
        $item = $this->db->where('id', (int)$id)->where('store_id', $storeId)->where('status', 1)->get('db_items')->row();
        if(!$item || !isset($this->allowed_types[$item->product_type])){
            $this->session->set_flashdata('error', 'Product not found.');
            redirect('creator/products'); return;
        }
        $this->_render('Edit ' . htmlspecialchars($item->item_name), 'creator/product_form', $this->_form_data($item->product_type, $item));
    }

    private function _default_category($storeId, $type){
        $names = ['digital' => 'Digital Products', 'course' => 'Courses', 'membership' => 'Memberships'];
        $name = $names[$type];
        $cat = $this->db->where('store_id', $storeId)->where('category_name', $name)->get('db_category')->row();
        if($cat) return $cat->id;
        $row = ['store_id' => $storeId, 'category_name' => $name, 'status' => 1];
        if($this->db->field_exists('category_code', 'db_category')) $row['category_code'] = strtoupper(substr($type, 0, 3)) . '-' . $storeId;
        if($this->db->field_exists('created_date', 'db_category')) $row['created_date'] = date('Y-m-d');
        if($this->db->field_exists('created_by', 'db_category')) $row['created_by'] = $this->session->userdata('user_id');
        $this->db->insert('db_category', $row);
        return $this->db->insert_id();
    }

    private function _first_id($table, $storeId){
        if(!$this->db->table_exists($table)) return 0;
        $row = $this->db->where('store_id', $storeId)->order_by('id', 'asc')->get($table)->row();
        return $row ? (int)$row->id : 0;
    }

    private function _upload_cover(){
        if(empty($_FILES['item_image']['name'])) return null;
        if(!is_dir('./uploads/items/')) mkdir('./uploads/items/', 0755, true);
        $config = ['upload_path' => './uploads/items/', 'allowed_types' => 'jpg|png|jpeg|gif|webp', 'max_size' => 4096, 'file_name' => 'cr_' . time() . '_' . rand(100, 999)];
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if(!$this->upload->do_upload('item_image')){
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
            return false;
        }
        $file = $this->upload->data('file_name');
        $cfg = ['image_library' => 'gd2', 'source_image' => 'uploads/items/' . $file, 'create_thumb' => TRUE, 'maintain_ratio' => TRUE, 'width' => 400, 'height' => 300];
        $this->load->library('image_lib', $cfg);
        $this->image_lib->initialize($cfg);
        $this->image_lib->resize();
        $this->image_lib->clear();
        return 'uploads/items/' . $file;
    }

    private function _upload_digital_file($storeId){
        if(empty($_FILES['digital_file']['name'])) return null;
        $dir = 'uploads/digital/' . $storeId . '/';
        if(!is_dir('./' . $dir)) mkdir('./' . $dir, 0755, true);
        $config = ['upload_path' => './' . $dir, 'allowed_types' => '*', 'max_size' => 204800, 'file_name' => 'dgtl_' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['digital_file']['name'])];
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if(!$this->upload->do_upload('digital_file')){
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
            return false;
        }
        return $dir . $this->upload->data('file_name');
    }

    public function save($id = 0){
        $storeId = get_current_store_id();
        $id = (int)$id;
        $type = $this->_type_or_404($this->input->post('product_type', TRUE));
        $existing = $id ? $this->db->where('id', $id)->where('store_id', $storeId)->get('db_items')->row() : null;
        if($id && !$existing){ show_404(); return; }

        $name  = trim($this->input->post('item_name', TRUE));
        $price = (float)str_replace(',', '', $this->input->post('sales_price', TRUE));
        if($name === '' || $price < 0){
            $this->session->set_flashdata('error', 'Name and price are required.');
            redirect($id ? 'creator/edit/' . $id : 'creator/create/' . $type); return;
        }
        if(!$id){
            $check = check_subscription_limit('product_limit');
            if($check !== true){ $this->session->set_flashdata('error', strip_tags($check)); redirect('creator/products'); return; }
        }
        if(!empty($_FILES['item_image']['name']) || !empty($_FILES['digital_file']['name'])){
            $media = check_media_storage_limit();
            if($media !== true){ $this->session->set_flashdata('error', strip_tags($media)); redirect($id ? 'creator/edit/' . $id : 'creator/create/' . $type); return; }
        }

        $cover = $this->_upload_cover();
        if($cover === false){ redirect($id ? 'creator/edit/' . $id : 'creator/create/' . $type); return; }

        $categoryId = (int)$this->input->post('category_id', TRUE);
        if($categoryId <= 0) $categoryId = $this->_default_category($storeId, $type);

        $discount = (float)$this->input->post('discount', TRUE);
        $row = [
            'item_name'      => $name,
            'category_id'    => $categoryId,
            'description'    => $this->input->post('description', FALSE),
            'sales_price'    => $price,
            'price'          => $price,
            'discount'       => $discount > 0 ? $discount : 0,
            'discount_type'  => 'Percentage',
            'publish_online' => $this->input->post('publish_online') ? 1 : 0,
            'product_type'   => $type,
        ];
        foreach(['is_featured' => 'is_featured', 'is_new_arrival' => 'is_new_arrival'] as $field => $post){
            if($this->db->field_exists($field, 'db_items')) $row[$field] = $this->input->post($post) ? 1 : 0;
        }
        if($cover) $row['item_image'] = $cover;

        if($type === 'digital'){
            $file = $this->_upload_digital_file($storeId);
            if($file === false){ redirect($id ? 'creator/edit/' . $id : 'creator/create/' . $type); return; }
            if($file) $row['digital_file'] = $file;
            $row['download_limit'] = max(1, (int)$this->input->post('download_limit', TRUE) ?: 3);
            $row['download_expiry_hours'] = max(1, (int)$this->input->post('download_expiry_hours', TRUE) ?: 72);
        }

        $now = date('Y-m-d H:i:s');
        if($existing){
            $this->db->where('id', $id)->update('db_items', $row);
            $itemId = $id;
        } else {
            $code = 'CR' . $storeId . '-' . strtoupper(substr($type, 0, 1)) . date('ymdHis');
            $row = array_merge($row, [
                'store_id'        => $storeId,
                'item_code'       => $code,
                'sku'             => $code,
                'unit_id'         => $this->_first_id('db_units', $storeId),
                'tax_id'          => $this->_first_id('db_tax', $storeId),
                'purchase_price'  => 0,
                'stock'           => 999999,
                'status'          => 1,
                'service_bit'     => 0,
                'item_group'      => 'Single',
                'child_bit'       => 0,
                'created_date'    => date('Y-m-d'),
                'created_time'    => date('H:i:s'),
                'created_by'      => $this->session->userdata('user_id'),
            ]);
            foreach(['package_bit' => 0, 'online_excluded' => 0, 'not_for_sale' => 0] as $f => $v){ if($this->db->field_exists($f, 'db_items')) $row[$f] = $v; }
            $this->db->insert('db_items', $row);
            $itemId = $this->db->insert_id();
        }

        // Linked course / membership records
        if($type === 'course' && $this->db->table_exists('db_courses')){
            $course = $this->db->where('item_id', $itemId)->where('store_id', $storeId)->get('db_courses')->row();
            $cData = ['store_id' => $storeId, 'item_id' => $itemId, 'title' => $name, 'description' => strip_tags($row['description']), 'status' => 1];
            if($course){ $this->db->where('id', $course->id)->update('db_courses', array_merge($cData, ['updated_at' => $now])); $courseId = $course->id; }
            else { $this->db->insert('db_courses', array_merge($cData, ['created_at' => $now])); $courseId = $this->db->insert_id(); }
        }
        if($type === 'membership' && $this->db->table_exists('db_memberships')){
            $interval = $this->input->post('billing_interval', TRUE);
            if(!in_array($interval, ['weekly', 'monthly', 'yearly'])) $interval = 'monthly';
            $mData = [
                'store_id' => $storeId, 'item_id' => $itemId, 'membership_name' => $name,
                'description' => trim($this->input->post('benefits', TRUE)),
                'billing_interval' => $interval, 'trial_days' => max(0, (int)$this->input->post('trial_days', TRUE)), 'status' => 1,
            ];
            $m = $this->db->where('item_id', $itemId)->where('store_id', $storeId)->get('db_memberships')->row();
            if($m) $this->db->where('id', $m->id)->update('db_memberships', array_merge($mData, ['updated_at' => $now]));
            else $this->db->insert('db_memberships', array_merge($mData, ['created_at' => $now]));
        }

        $this->session->set_flashdata('success', $existing ? 'Product updated.' : 'Product created.');
        if($type === 'course' && !$existing && !empty($courseId)){
            $this->session->set_flashdata('success', 'Course created. Now build the curriculum.');
            redirect('courses/edit/' . $courseId); return;
        }
        redirect('creator/products/' . $type);
    }

    public function delete($id = 0){
        $storeId = get_current_store_id();
        $item = $this->db->where('id', (int)$id)->where('store_id', $storeId)->get('db_items')->row();
        if($item && isset($this->allowed_types[$item->product_type])){
            $this->db->where('id', $item->id)->update('db_items', ['status' => 0, 'publish_online' => 0]);
            $this->session->set_flashdata('success', 'Product removed from your store.');
        }
        redirect('creator/products');
    }

    /* ===================== STUDENTS ===================== */
    public function students(){
        $storeId = get_current_store_id();
        $rows = [];
        if($this->db->table_exists('db_course_enrollments')){
            $rows = $this->db->select('e.*, c.title AS course_title, cu.customer_name, cu.mobile, cu.email')
                ->from('db_course_enrollments e')
                ->join('db_courses c', 'c.id = e.course_id', 'left')
                ->join('db_customers cu', 'cu.id = e.customer_id', 'left')
                ->where('e.store_id', $storeId)
                ->order_by('e.id', 'desc')->limit(300)->get()->result();
            foreach($rows as $r){
                $r->progress = $this->course_model->getCourseProgressPercent($r->course_id, $r->id);
            }
        }
        $this->_render('Students', 'creator/students', ['rows' => $rows]);
    }

    /* ===================== MEMBERS ===================== */
    public function members(){
        $storeId = get_current_store_id();
        $rows = [];
        if($this->db->table_exists('db_membership_subscriptions')){
            $rows = $this->db->select('s.*, m.membership_name, m.billing_interval, cu.customer_name, cu.mobile, cu.email')
                ->from('db_membership_subscriptions s')
                ->join('db_memberships m', 'm.id = s.membership_id', 'left')
                ->join('db_customers cu', 'cu.id = s.customer_id', 'left')
                ->where('s.store_id', $storeId)
                ->order_by('s.id', 'desc')->limit(300)->get()->result();
        }
        $this->_render('Members', 'creator/members', ['rows' => $rows]);
    }

    /* ===================== BUYERS ===================== */
    public function buyers(){
        $storeId = get_current_store_id();
        $rows = [];
        if($this->_has_online_orders()){
            $rows = $this->db->query("SELECT customer_id, customer_name, customer_email, customer_phone,
                COUNT(*) AS orders, COALESCE(SUM(CASE WHEN payment_status='paid' THEN grand_total ELSE 0 END),0) AS spent, MAX(created_at) AS last_order
                FROM db_online_orders WHERE store_id=".(int)$storeId." AND status=1
                GROUP BY customer_id, customer_name, customer_email, customer_phone
                ORDER BY last_order DESC LIMIT 300")->result();
        }
        $this->_render('Buyers', 'creator/buyers', ['rows' => $rows]);
    }
}
