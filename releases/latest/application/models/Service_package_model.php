<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_package_model extends CI_Model {

    // Datatable config
    var $table = 'db_service_packages as a';
    var $column_order = array('a.id','a.package_code','a.package_name','a.pricing_model','a.package_price','a.redemption_type','a.expiry_type','a.status');
    var $column_search = array('a.id','a.package_code','a.package_name','a.pricing_model','a.package_price','a.redemption_type','a.expiry_type','a.status');
    var $order = array('a.id' => 'desc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        // Main package definitions
        if (!$this->db->table_exists('db_service_packages')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_service_packages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                package_code VARCHAR(50) NOT NULL,
                package_name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                package_image VARCHAR(255) NULL,
                pricing_model ENUM('fixed','calculated') NOT NULL DEFAULT 'fixed',
                package_price DECIMAL(15,2) NOT NULL DEFAULT 0,
                discount_type VARCHAR(20) NULL,
                discount DECIMAL(15,2) NULL,
                redemption_type ENUM('single','multi') NOT NULL DEFAULT 'single',
                expiry_type ENUM('none','days','date') NOT NULL DEFAULT 'none',
                expiry_days INT NULL,
                expiry_date DATE NULL,
                status TINYINT NOT NULL DEFAULT 1,
                created_date DATE NULL,
                created_time VARCHAR(20) NULL,
                created_by VARCHAR(50) NULL,
                system_ip VARCHAR(50) NULL,
                system_name VARCHAR(100) NULL,
                INDEX idx_store_id (store_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        // Items inside each package
        if (!$this->db->table_exists('db_service_package_items')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_service_package_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                package_id INT NOT NULL,
                item_type ENUM('service','product') NOT NULL DEFAULT 'service',
                item_id INT NOT NULL,
                quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 0,
                INDEX idx_package_id (package_id),
                INDEX idx_item_id (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        // Customer-purchased packages
        if (!$this->db->table_exists('db_customer_packages')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_customer_packages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                customer_id INT NOT NULL,
                package_id INT NOT NULL,
                sale_id INT NULL,
                sale_items_id INT NULL,
                package_code VARCHAR(50) NOT NULL,
                total_uses DECIMAL(10,2) NOT NULL DEFAULT 0,
                remaining_uses DECIMAL(10,2) NOT NULL DEFAULT 0,
                expiry_date DATE NULL,
                status ENUM('active','fully_redeemed','expired','cancelled') NOT NULL DEFAULT 'active',
                created_date DATE NULL,
                created_time VARCHAR(20) NULL,
                created_by VARCHAR(50) NULL,
                INDEX idx_customer_id (customer_id),
                INDEX idx_package_id (package_id),
                INDEX idx_store_id (store_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        // Redemption log
        if (!$this->db->table_exists('db_customer_package_redemptions')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_customer_package_redemptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_package_id INT NOT NULL,
                item_id INT NOT NULL,
                quantity_redeemed DECIMAL(10,2) NOT NULL DEFAULT 1,
                service_order_id INT NULL,
                sale_id INT NULL,
                notes TEXT NULL,
                redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                redeemed_by VARCHAR(50) NULL,
                INDEX idx_customer_package_id (customer_package_id),
                INDEX idx_item_id (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    private function _get_datatables_query() {
        $this->db->select($this->column_order);
        $this->db->from($this->table);
        $this->db->where("a.store_id", get_current_store_id());

        $i = 0;
        foreach ($this->column_search as $item) {
            if ($_POST['search']['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables() {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all() {
        $this->db->where("store_id", get_current_store_id());
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    // Save package definition + items
    public function save_and_update() {
        $command = $this->input->post('command', TRUE);
        $q_id = $this->input->post('q_id', TRUE);

        $package_code = $this->input->post('package_code', TRUE);
        $package_name = $this->input->post('package_name', TRUE);
        $description = $this->input->post('description', TRUE);
        $pricing_model = $this->input->post('pricing_model', TRUE);
        $package_price = $this->input->post('package_price', TRUE);
        $discount_type = $this->input->post('discount_type', TRUE);
        $discount = $this->input->post('discount', TRUE);
        $redemption_type = $this->input->post('redemption_type', TRUE);
        $expiry_type = $this->input->post('expiry_type', TRUE);
        $expiry_days = $this->input->post('expiry_days', TRUE);
        $expiry_date = $this->input->post('expiry_date', TRUE);

        $CUR_DATE = $this->data['CUR_DATE'] ?? date("Y-m-d");
        $CUR_TIME = $this->data['CUR_TIME'] ?? date("h:i:s a");
        $CUR_USERNAME = $this->data['CUR_USERNAME'] ?? 'System';
        $SYSTEM_IP = $this->data['SYSTEM_IP'] ?? $_SERVER['REMOTE_ADDR'];
        $SYSTEM_NAME = $this->data['SYSTEM_NAME'] ?? 'localhost';

        $store_id = get_current_store_id();

        // Handle image upload
        $file_name = '';
        if (!empty($_FILES['package_image']['name'])) {
            $new_name = time();
            $config['file_name'] = $new_name;
            $config['upload_path'] = './uploads/packages/';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['max_size'] = 1024;
            $config['max_width'] = 1500;
            $config['max_height'] = 1500;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('package_image')) {
                print($this->upload->display_errors());
                exit();
            } else {
                $file_name = $this->upload->data('file_name');
            }
        }

        $this->db->trans_begin();
        $this->db->trans_strict(TRUE);

        $info = array(
            'store_id'         => $store_id,
            'package_code'     => $package_code,
            'package_name'     => $package_name,
            'description'      => $description,
            'pricing_model'    => $pricing_model,
            'package_price'    => $package_price,
            'discount_type'    => $discount_type,
            'discount'         => $discount,
            'redemption_type'  => $redemption_type,
            'expiry_type'      => $expiry_type,
            'expiry_days'      => !empty($expiry_days) ? $expiry_days : 0,
            'expiry_date'      => !empty($expiry_date) ? system_fromatted_date($expiry_date) : null,
        );

        if (!empty($file_name)) {
            $info['package_image'] = 'uploads/packages/' . $file_name;
        }

        if ($command == 'save') {
            $this->db->query("ALTER TABLE db_service_packages AUTO_INCREMENT = 1");
            $query1 = $this->db->insert('db_service_packages', array_merge($info, array(
                'created_date' => $CUR_DATE,
                'created_time' => $CUR_TIME,
                'created_by'   => $CUR_USERNAME,
                'system_ip'    => $SYSTEM_IP,
                'system_name'  => $SYSTEM_NAME,
                'status'       => 1,
            )));
            $package_id = $this->db->insert_id();
            $this->session->set_flashdata('success', 'Success!! New Package Added Successfully!');
        } else {
            if (!empty($file_name)) {
                $old = $this->db->where('id', $q_id)->get('db_service_packages')->row();
                if ($old && !empty($old->package_image) && file_exists($old->package_image)) {
                    unlink($old->package_image);
                }
            }
            $query1 = $this->db->where('id', $q_id)->update('db_service_packages', $info);
            $package_id = $q_id;
            // Delete old items
            $this->db->where('package_id', $q_id)->delete('db_service_package_items');
            $this->session->set_flashdata('success', 'Success!! Package Updated Successfully!');
        }

        if (!$query1) {
            $this->db->trans_rollback();
            return "failed";
        }

        // Insert package items
        $item_ids = $this->input->post('package_item_id');
        $item_types = $this->input->post('package_item_type');
        $item_qtys = $this->input->post('package_item_qty');

        if (!empty($item_ids)) {
            foreach ($item_ids as $i => $item_id) {
                if (!empty($item_id)) {
                    $this->db->insert('db_service_package_items', array(
                        'package_id' => $package_id,
                        'item_type'  => $item_types[$i] ?? 'service',
                        'item_id'    => $item_id,
                        'quantity'   => !empty($item_qtys[$i]) ? $item_qtys[$i] : 1,
                        'sort_order' => $i,
                    ));
                }
            }
        }

        // Sync to db_items as a catalogue entry
        $this->sync_package_to_items($package_id);

        $this->db->trans_commit();
        return "success";
    }

    // Create or update the package as an item in db_items so POS can find it
    private function sync_package_to_items($package_id) {
        $pkg = $this->db->where('id', $package_id)->get('db_service_packages')->row();
        if (!$pkg) return;

        // Calculate price for db_items
        $sales_price = $pkg->package_price;

        // Check if item already exists
        $existing = $this->db->where('package_bit', 1)->where('description', 'PACKAGE_' . $package_id)->get('db_items')->row();

        $item_data = array(
            'store_id'      => $pkg->store_id,
            'item_name'     => $pkg->package_name,
            'item_code'     => $pkg->package_code,
            'description'   => 'PACKAGE_' . $package_id,
            'sales_price'   => $sales_price,
            'price'         => $sales_price,
            'purchase_price'=> 0,
            'tax_id'        => 0,
            'tax_type'      => 'Exclusive',
            'service_bit'   => 0,
            'package_bit'   => 1,
            'status'        => $pkg->status,
            'stock'         => 0,
        );

        if ($existing) {
            $this->db->where('id', $existing->id)->update('db_items', $item_data);
        } else {
            $item_data['count_id'] = get_count_id('db_items');
            $item_data['created_date'] = date('Y-m-d');
            $item_data['created_time'] = date('h:i:s a');
            $item_data['created_by'] = 'System';
            $this->db->insert('db_items', $item_data);
        }
    }

    public function get_details($id, $data = array()) {
        $query = $this->db->query("select * from db_service_packages where id='$id'");
        if ($query->num_rows() == 0) {
            show_404();
            exit;
        }
        $res = $query->row();
        $data['q_id'] = $res->id;
        $data['package_code'] = $res->package_code;
        $data['package_name'] = $res->package_name;
        $data['description'] = $res->description;
        $data['package_image'] = $res->package_image;
        $data['pricing_model'] = $res->pricing_model;
        $data['package_price'] = $res->package_price;
        $data['discount_type'] = $res->discount_type;
        $data['discount'] = $res->discount;
        $data['redemption_type'] = $res->redemption_type;
        $data['expiry_type'] = $res->expiry_type;
        $data['expiry_days'] = $res->expiry_days;
        $data['expiry_date'] = (!empty($res->expiry_date) && $res->expiry_date != '0000-00-00') ? show_date($res->expiry_date) : '';
        $data['status'] = $res->status;

        // Load package items
        $data['package_items'] = $this->db->where('package_id', $id)->order_by('sort_order', 'asc')->get('db_service_package_items')->result();

        return $data;
    }

    public function delete_packages($ids) {
        $this->db->trans_begin();

        // Get package IDs to clean up db_items
        $pkg_rows = $this->db->where("id in ($ids)")->get('db_service_packages')->result();
        foreach ($pkg_rows as $pkg) {
            $this->db->where('description', 'PACKAGE_' . $pkg->id)->where('package_bit', 1)->delete('db_items');
        }

        $this->db->where("id in ($ids)");
        if (!is_admin()) {
            $this->db->where("store_id", get_current_store_id());
        }
        $this->db->delete("db_service_packages");

        // Clean up orphaned items
        $this->db->where("package_id in ($ids)")->delete("db_service_package_items");

        $this->db->trans_commit();
        return "success";
    }

    public function update_status($id, $status) {
        $this->db->where('id', $id)->update('db_service_packages', array('status' => $status));
        // Also sync to db_items
        $pkg = $this->db->where('id', $id)->get('db_service_packages')->row();
        if ($pkg) {
            $this->db->where('description', 'PACKAGE_' . $id)->where('package_bit', 1)->update('db_items', array('status' => $status));
        }
        return "success";
    }

    // For POS: get package details by item_id (the linked db_items entry)
    public function get_by_item_id($item_id) {
        $item = $this->db->where('id', $item_id)->where('package_bit', 1)->get('db_items')->row();
        if (!$item) return false;

        // Extract package ID from description field (PACKAGE_123)
        if (preg_match('/PACKAGE_(\d+)/', $item->description, $matches)) {
            $package_id = $matches[1];
            return $this->db->where('id', $package_id)->get('db_service_packages')->row();
        }
        return false;
    }

    // Get package items with full details
    public function get_package_items($package_id) {
        $this->db->select('spi.*, i.item_name, i.service_bit, i.item_image');
        $this->db->from('db_service_package_items spi');
        $this->db->join('db_items i', 'i.id = spi.item_id', 'left');
        $this->db->where('spi.package_id', $package_id);
        $this->db->order_by('spi.sort_order', 'asc');
        return $this->db->get()->result();
    }

    // Customer-facing: get active packages for a customer
    public function get_customer_packages($customer_id) {
        $this->db->select('cp.*, sp.package_name, sp.package_image, sp.redemption_type');
        $this->db->from('db_customer_packages cp');
        $this->db->join('db_service_packages sp', 'sp.id = cp.package_id', 'left');
        $this->db->where('cp.customer_id', $customer_id);
        $this->db->where('cp.store_id', get_current_store_id());
        $this->db->order_by('cp.id', 'desc');
        return $this->db->get()->result();
    }

    // Redeem a package item
    public function redeem_package_item($customer_package_id, $item_id, $qty = 1, $notes = '', $service_order_id = null, $sale_id = null) {
        $cp = $this->db->where('id', $customer_package_id)->get('db_customer_packages')->row();
        if (!$cp) return array('status' => 'error', 'message' => 'Package not found');
        if ($cp->status != 'active') return array('status' => 'error', 'message' => 'Package is not active');
        if ($cp->remaining_uses < $qty) return array('status' => 'error', 'message' => 'Not enough remaining uses');
        if (!empty($cp->expiry_date) && $cp->expiry_date != '0000-00-00' && $cp->expiry_date < date('Y-m-d')) {
            return array('status' => 'error', 'message' => 'Package has expired');
        }

        $this->db->trans_begin();

        // Insert redemption record
        $this->db->insert('db_customer_package_redemptions', array(
            'customer_package_id' => $customer_package_id,
            'item_id'             => $item_id,
            'quantity_redeemed'   => $qty,
            'service_order_id'    => $service_order_id,
            'sale_id'             => $sale_id,
            'notes'               => $notes,
            'redeemed_at'         => date('Y-m-d H:i:s'),
            'redeemed_by'         => $this->data['CUR_USERNAME'] ?? 'System',
        ));

        // Update remaining uses
        $new_remaining = $cp->remaining_uses - $qty;
        $update_data = array('remaining_uses' => $new_remaining);
        if ($new_remaining <= 0) {
            $update_data['status'] = 'fully_redeemed';
        }
        $this->db->where('id', $customer_package_id)->update('db_customer_packages', $update_data);

        $this->db->trans_commit();
        return array('status' => 'success', 'message' => 'Redeemed successfully', 'remaining' => $new_remaining);
    }

    // Create customer package record after sale
    public function create_customer_package($sale_id, $sale_items_id, $package_id, $customer_id) {
        $pkg = $this->db->where('id', $package_id)->get('db_service_packages')->row();
        if (!$pkg) return false;

        // Calculate total uses
        $items = $this->db->where('package_id', $package_id)->get('db_service_package_items')->result();
        $total_uses = 0;
        foreach ($items as $it) {
            $total_uses += floatval($it->quantity);
        }
        if ($total_uses == 0) $total_uses = 1;

        // For single-session packages, remaining_uses = 1 (redeem all at once)
        // For multi-session, remaining_uses = total_uses
        $remaining = ($pkg->redemption_type == 'single') ? 1 : $total_uses;

        // Calculate expiry
        $expiry_date = null;
        if ($pkg->expiry_type == 'days' && !empty($pkg->expiry_days)) {
            $expiry_date = date('Y-m-d', strtotime("+{$pkg->expiry_days} days"));
        } else if ($pkg->expiry_type == 'date' && !empty($pkg->expiry_date)) {
            $expiry_date = $pkg->expiry_date;
        }

        $data = array(
            'store_id'        => $pkg->store_id,
            'customer_id'     => $customer_id,
            'package_id'      => $package_id,
            'sale_id'         => $sale_id,
            'sale_items_id'   => $sale_items_id,
            'package_code'    => $pkg->package_code,
            'total_uses'      => $total_uses,
            'remaining_uses'  => $remaining,
            'expiry_date'     => $expiry_date,
            'status'          => 'active',
            'created_date'    => date('Y-m-d'),
            'created_time'    => date('h:i:s a'),
            'created_by'      => $this->data['CUR_USERNAME'] ?? 'System',
        );

        $this->db->insert('db_customer_packages', $data);
        return $this->db->insert_id();
    }
}
