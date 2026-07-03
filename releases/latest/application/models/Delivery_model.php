<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_model extends CI_Model {

    var $table = 'db_delivery_schedules as a';
    var $column_order = array(null,'a.schedule_code','a.route_name','a.schedule_date','a.driver_name','a.vehicle','a.status',null,null);
    var $column_search = array('a.schedule_code','a.route_name','a.driver_name','a.vehicle','a.status');
    var $order = array('a.id' => 'desc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        if (!$this->db->table_exists('db_delivery_schedules')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_delivery_schedules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                schedule_code VARCHAR(50) NOT NULL,
                route_name VARCHAR(255) NULL,
                schedule_date DATE NOT NULL,
                driver_id INT NULL,
                driver_name VARCHAR(255) NULL,
                vehicle VARCHAR(100) NULL,
                notes TEXT NULL,
                status ENUM('planned','ready','out_for_delivery','completed','cancelled') NOT NULL DEFAULT 'planned',
                created_date DATE NULL,
                created_time VARCHAR(20) NULL,
                created_by VARCHAR(50) NULL,
                system_ip VARCHAR(50) NULL,
                system_name VARCHAR(100) NULL,
                INDEX idx_store_id (store_id),
                INDEX idx_status (status),
                INDEX idx_schedule_date (schedule_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->db->table_exists('db_delivery_schedule_items')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_delivery_schedule_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                schedule_id INT NOT NULL,
                sales_id INT NOT NULL,
                sales_code VARCHAR(50) NULL,
                customer_id INT NULL,
                customer_name VARCHAR(255) NULL,
                address TEXT NULL,
                phone VARCHAR(50) NULL,
                delivery_sequence INT NOT NULL DEFAULT 0,
                delivery_status ENUM('pending','out_for_delivery','delivered','failed','cancelled') NOT NULL DEFAULT 'pending',
                delivered_at DATETIME NULL,
                delivery_notes TEXT NULL,
                signature TEXT NULL,
                photo_proof VARCHAR(255) NULL,
                INDEX idx_schedule_id (schedule_id),
                INDEX idx_sales_id (sales_id),
                INDEX idx_delivery_status (delivery_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->db->table_exists('db_delivery_drivers')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_delivery_drivers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NULL,
                email VARCHAR(100) NULL,
                address TEXT NULL,
                emergency_contact_name VARCHAR(255) NULL,
                emergency_contact_phone VARCHAR(50) NULL,
                nin VARCHAR(50) NULL COMMENT 'National Identification Number',
                driver_license VARCHAR(100) NULL COMMENT 'FRSC Driver License Number',
                license_expiry DATE NULL,
                vehicle VARCHAR(100) NULL,
                vehicle_type ENUM('motorcycle','car','van','truck','bicycle','keke') NULL DEFAULT 'motorcycle',
                vehicle_color VARCHAR(50) NULL,
                license_plate VARCHAR(50) NULL,
                employment_type ENUM('full_time','contract','part_time','intern') NULL DEFAULT 'contract',
                hire_date DATE NULL,
                photo VARCHAR(255) NULL,
                notes TEXT NULL,
                status ENUM('active','inactive','on_leave','suspended') NOT NULL DEFAULT 'active',
                created_date DATE NULL,
                created_by VARCHAR(50) NULL,
                INDEX idx_store_id (store_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            // Migrate existing table to add new columns
            $cols = $this->db->list_fields('db_delivery_drivers');
            $newCols = [
                'email' => "VARCHAR(100) NULL AFTER phone",
                'address' => "TEXT NULL AFTER email",
                'emergency_contact_name' => "VARCHAR(255) NULL AFTER address",
                'emergency_contact_phone' => "VARCHAR(50) NULL AFTER emergency_contact_name",
                'nin' => "VARCHAR(50) NULL COMMENT 'National Identification Number' AFTER emergency_contact_phone",
                'driver_license' => "VARCHAR(100) NULL COMMENT 'FRSC Driver License Number' AFTER nin",
                'license_expiry' => "DATE NULL AFTER driver_license",
                'vehicle_type' => "ENUM('motorcycle','car','van','truck','bicycle','keke') NULL DEFAULT 'motorcycle' AFTER vehicle",
                'vehicle_color' => "VARCHAR(50) NULL AFTER vehicle_type",
                'employment_type' => "ENUM('full_time','contract','part_time','intern') NULL DEFAULT 'contract' AFTER license_plate",
                'hire_date' => "DATE NULL AFTER employment_type",
                'photo' => "VARCHAR(255) NULL AFTER hire_date",
                'notes' => "TEXT NULL AFTER photo",
            ];
            foreach ($newCols as $col => $def) {
                if (!in_array($col, $cols)) {
                    $this->db->query("ALTER TABLE db_delivery_drivers ADD COLUMN {$col} {$def}");
                }
            }
            // Migrate status from tinyint to enum if needed
            $statusCol = $this->db->query("SHOW COLUMNS FROM db_delivery_drivers LIKE 'status'")->row();
            if ($statusCol && strpos($statusCol->Type, 'tinyint') !== false) {
                $this->db->query("ALTER TABLE db_delivery_drivers MODIFY status ENUM('active','inactive','on_leave','suspended') NOT NULL DEFAULT 'active'");
            }
        }
    }

    private function _get_datatables_query() {
        $select_cols = array_filter($this->column_order);
        $this->db->select($select_cols);
        $this->db->from($this->table);
        $this->db->where("a.store_id", get_current_store_id());

        $i = 0;
        foreach ($this->column_search as $item) {
            if (!empty($_POST['search']['value'])) {
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
            $col_idx = $_POST['order']['0']['column'];
            if (!empty($this->column_order[$col_idx])) {
                $this->db->order_by($this->column_order[$col_idx], $_POST['order']['0']['dir']);
            }
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
        $this->db->from('db_delivery_schedules');
        return $this->db->count_all_results();
    }

    public function save_and_update() {
        $command = $this->input->post('command', TRUE);
        $q_id = $this->input->post('q_id', TRUE);
        $route_name = $this->input->post('route_name', TRUE);
        $schedule_date = $this->input->post('schedule_date', TRUE);
        $driver_id = $this->input->post('driver_id', TRUE);
        $vehicle = $this->input->post('vehicle', TRUE);
        $notes = $this->input->post('notes', TRUE);
        $status = $this->input->post('status', TRUE) ?: 'planned';

        $CUR_DATE = date("Y-m-d");
        $CUR_TIME = date("h:i:s a");
        $CUR_USERNAME = $this->session->userdata('inv_username') ?: 'System';
        $SYSTEM_IP = $_SERVER['REMOTE_ADDR'] ?? 'localhost';
        $SYSTEM_NAME = 'localhost';
        $store_id = get_current_store_id();

        $driver_name = '';
        if (!empty($driver_id)) {
            $drv = $this->db->where('id', $driver_id)->get('db_delivery_drivers')->row();
            $driver_name = $drv ? $drv->name : '';
        }

        $schedule_code = $this->input->post('schedule_code', TRUE);
        if (empty($schedule_code)) {
            $count = $this->db->where('store_id', $store_id)->count_all_results('db_delivery_schedules') + 1;
            $schedule_code = 'DLV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        $this->db->trans_begin();

        $info = array(
            'store_id'      => $store_id,
            'schedule_code' => $schedule_code,
            'route_name'    => $route_name,
            'schedule_date' => system_fromatted_date($schedule_date),
            'driver_id'     => $driver_id ?: null,
            'driver_name'   => $driver_name,
            'vehicle'       => $vehicle,
            'notes'         => $notes,
            'status'        => $status,
        );

        if ($command == 'save') {
            $this->db->query("ALTER TABLE db_delivery_schedules AUTO_INCREMENT = 1");
            $query1 = $this->db->insert('db_delivery_schedules', array_merge($info, array(
                'created_date' => $CUR_DATE,
                'created_time' => $CUR_TIME,
                'created_by'   => $CUR_USERNAME,
                'system_ip'    => $SYSTEM_IP,
                'system_name'  => $SYSTEM_NAME,
            )));
            $schedule_id = $this->db->insert_id();
        } else {
            $query1 = $this->db->where('id', $q_id)->update('db_delivery_schedules', $info);
            $schedule_id = $q_id;
            // Delete old items
            $this->db->where('schedule_id', $q_id)->delete('db_delivery_schedule_items');
        }

        if (!$query1) {
            $this->db->trans_rollback();
            return "failed";
        }

        // Insert schedule items
        $sales_ids = $this->input->post('sales_id');
        $sequences = $this->input->post('delivery_sequence');

        if (!empty($sales_ids)) {
            foreach ($sales_ids as $i => $sales_id) {
                if (!empty($sales_id)) {
                    $sale = $this->db->select('s.sales_code, s.customer_id, c.customer_name, c.mobile, s.shippingaddress_id')
                        ->from('db_sales s')
                        ->join('db_customers c', 'c.id = s.customer_id', 'left')
                        ->where('s.id', $sales_id)
                        ->get()->row();

                    $address = '';
                    if (!empty($sale->shippingaddress_id)) {
                        $addr = $this->db->where('id', $sale->shippingaddress_id)->get('db_shippingaddress')->row();
                        $address = $addr ? trim($addr->address . ', ' . ($addr->city ?? '') . ', ' . ($addr->state_id ?? '')) : '';
                    }

                    $this->db->insert('db_delivery_schedule_items', array(
                        'schedule_id'       => $schedule_id,
                        'sales_id'          => $sales_id,
                        'sales_code'        => $sale ? $sale->sales_code : '',
                        'customer_id'       => $sale ? $sale->customer_id : null,
                        'customer_name'     => $sale ? $sale->customer_name : '',
                        'address'           => $address,
                        'phone'             => $sale ? $sale->mobile : '',
                        'delivery_sequence' => !empty($sequences[$i]) ? $sequences[$i] : ($i + 1),
                        'delivery_status'   => 'pending',
                    ));
                }
            }
        }

        $this->db->trans_commit();
        return "success";
    }

    public function get_details($id) {
        $res = $this->db->where('id', $id)->get('db_delivery_schedules')->row();
        if (!$res) return [];
        $data = [];
        $data['q_id'] = $res->id;
        $data['schedule_code'] = $res->schedule_code;
        $data['route_name'] = $res->route_name;
        $data['schedule_date'] = (!empty($res->schedule_date) && $res->schedule_date != '0000-00-00') ? show_date($res->schedule_date) : '';
        $data['driver_id'] = $res->driver_id;
        $data['driver_name'] = $res->driver_name;
        $data['vehicle'] = $res->vehicle;
        $data['notes'] = $res->notes;
        $data['status'] = $res->status;
        return $data;
    }

    public function get_schedule_items($schedule_id) {
        return $this->db->where('schedule_id', $schedule_id)
            ->order_by('delivery_sequence', 'asc')
            ->get('db_delivery_schedule_items')
            ->result();
    }

    public function delete_schedules($ids) {
        $this->db->trans_begin();
        $this->db->where("id in ($ids)");
        $this->db->where("store_id", get_current_store_id());
        $this->db->delete("db_delivery_schedules");
        $this->db->where("schedule_id in ($ids)")->delete("db_delivery_schedule_items");
        $this->db->trans_commit();
        return "success";
    }

    public function update_status($id, $status) {
        $this->db->where('id', $id)->update('db_delivery_schedules', array('status' => $status));
        return "success";
    }

    public function mark_item_delivered($item_id, $notes = '') {
        $this->db->where('id', $item_id)->update('db_delivery_schedule_items', array(
            'delivery_status' => 'delivered',
            'delivered_at'    => date('Y-m-d H:i:s'),
            'delivery_notes'  => $notes,
        ));
        return "success";
    }

    public function mark_item_status($item_id, $status, $notes = '') {
        $data = array('delivery_status' => $status, 'delivery_notes' => $notes);
        if ($status == 'delivered') {
            $data['delivered_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', $item_id)->update('db_delivery_schedule_items', $data);
        return "success";
    }

    public function get_pending_sales($store_id) {
        $this->db->select('s.id, s.sales_code, s.sales_date, c.customer_name, c.mobile, s.grand_total');
        $this->db->from('db_sales s');
        $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
        $this->db->where('s.store_id', $store_id);
        $this->db->where('s.sales_status', 'Final');
        // Exclude already assigned
        $assigned = $this->db->query("SELECT sales_id FROM db_delivery_schedule_items WHERE delivery_status != 'cancelled'")->result();
        $assigned_ids = array_column($assigned, 'sales_id');
        if (!empty($assigned_ids)) {
            $this->db->where_not_in('s.id', $assigned_ids);
        }
        $this->db->order_by('s.sales_date', 'DESC');
        $this->db->limit(50);
        return $this->db->get()->result();
    }

    public function get_drivers($store_id) {
        return $this->db->where('store_id', $store_id)
            ->where_in('status', ['active','on_leave'])
            ->order_by('name', 'asc')
            ->get('db_delivery_drivers')
            ->result();
    }

    public function get_driver($id) {
        return $this->db->where('id', $id)
            ->where('store_id', get_current_store_id())
            ->get('db_delivery_drivers')
            ->row();
    }

    public function get_driver_routes($driver_id) {
        $store_id = get_current_store_id();
        $routes = $this->db->where('driver_id', $driver_id)
            ->where('store_id', $store_id)
            ->order_by('schedule_date', 'DESC')
            ->get('db_delivery_schedules')
            ->result();

        $route_ids = array_column($routes, 'id');
        $route_items = [];
        if (!empty($route_ids)) {
            $items = $this->db->where_in('schedule_id', $route_ids)
                ->get('db_delivery_schedule_items')
                ->result();
            foreach ($items as $it) {
                if (!isset($route_items[$it->schedule_id])) {
                    $route_items[$it->schedule_id] = ['total' => 0, 'delivered' => 0];
                }
                $route_items[$it->schedule_id]['total']++;
                if ($it->delivery_status == 'delivered') {
                    $route_items[$it->schedule_id]['delivered']++;
                }
            }
        }

        $stats = [
            'total_routes' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total_stops' => 0,
            'delivered_stops' => 0,
        ];

        foreach ($routes as $r) {
            $stats['total_routes']++;
            if ($r->status == 'completed') $stats['completed']++;
            if ($r->status == 'cancelled') $stats['cancelled']++;

            $ri = $route_items[$r->id] ?? ['total' => 0, 'delivered' => 0];
            $stats['total_stops'] += $ri['total'];
            $stats['delivered_stops'] += $ri['delivered'];

            // Attach to route object for view use
            $r->stops_count = $ri['total'];
            $r->delivered_count = $ri['delivered'];
        }

        return ['routes' => $routes, 'stats' => $stats];
    }

    public function save_driver() {
        $id = $this->input->post('id', TRUE);
        $fields = [
            'name', 'phone', 'email', 'address',
            'emergency_contact_name', 'emergency_contact_phone',
            'nin', 'driver_license', 'license_expiry',
            'vehicle', 'vehicle_type', 'vehicle_color', 'license_plate',
            'employment_type', 'hire_date', 'photo', 'notes', 'status'
        ];
        $data = ['store_id' => get_current_store_id()];
        foreach ($fields as $f) {
            $val = $this->input->post($f, TRUE);
            if ($val !== null) {
                $data[$f] = $val;
            }
        }

        if (!empty($id)) {
            $this->db->where('id', $id)->update('db_delivery_drivers', $data);
        } else {
            $data['created_date'] = date('Y-m-d');
            $data['created_by'] = $this->session->userdata('inv_username') ?: 'System';
            $this->db->insert('db_delivery_drivers', $data);
        }
        return "success";
    }

    public function delete_driver($id) {
        $this->db->where('id', $id)->where('store_id', get_current_store_id())->delete('db_delivery_drivers');
        return "success";
    }
}
