<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vehicle service history — what was done to a customer's vehicle, when,
 * at what mileage, and when it is due back. Reminders (Cron::vehicle_service_reminders)
 * read next_due_date only; next_due_mileage is a counter-side prompt that
 * staff confirm when the vehicle comes in.
 */
class Vehicle_service_model extends CI_Model {

    const TYPES = [
        'tyre_rotation'      => 'Tyre Rotation',
        'wheel_alignment'    => 'Wheel Alignment',
        'wheel_balancing'    => 'Wheel Balancing',
        'battery_replacement'=> 'Battery Replacement',
        'oil_change'         => 'Oil & Filter Change',
        'plug_replacement'   => 'Spark Plug Change',
        'inspection'         => 'Inspection',
        'repair'             => 'Repair',
        'general'            => 'General Service',
    ];

    public function ready() {
        return $this->db->table_exists('db_vehicle_service_records');
    }

    /** Service rows for several vehicles, keyed by vehicle_id (newest first). */
    public function for_vehicles($store_id, array $vehicle_ids) {
        $out = [];
        if (empty($vehicle_ids) || !$this->ready()) return $out;
        $rows = $this->db->where('store_id', (int)$store_id)
            ->where_in('vehicle_id', $vehicle_ids)
            ->where('is_deleted', 0)
            ->order_by('service_date', 'desc')
            ->order_by('id', 'desc')
            ->limit(400)
            ->get('db_vehicle_service_records')->result();
        foreach ($rows as $r) {
            $out[(int)$r->vehicle_id][] = $r;
        }
        return $out;
    }

    public function get($store_id, $id) {
        if (!$this->ready()) return null;
        return $this->db->where('id', (int)$id)
            ->where('store_id', (int)$store_id)
            ->where('is_deleted', 0)
            ->get('db_vehicle_service_records')->row();
    }

    public function save($data, $id = null) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (!empty($id)) {
            $this->db->where('id', (int)$id);
            unset($data['store_id']);
            return $this->db->update('db_vehicle_service_records', $data);
        }
        $data['created_date'] = date('Y-m-d');
        $data['created_time'] = date('H:i:s');
        return $this->db->insert('db_vehicle_service_records', $data) ? $this->db->insert_id() : false;
    }

    public function soft_delete($store_id, $id) {
        $this->db->where('id', (int)$id)->where('store_id', (int)$store_id);
        return $this->db->update('db_vehicle_service_records', ['is_deleted' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Records whose next_due_date has arrived or is inside the reminder
     * window, and whose due slot has not been messaged yet. Past-due records
     * stay eligible so a vehicle nobody reminded in time is still caught up
     * while the store keeps reminders on. $store_ids limits the sweep to
     * auto-family stores; null = every store with due records.
     */
    public function due_for_reminder($window_days = 7, array $store_ids = null, $limit = 200) {
        if (!$this->ready()) return [];
        $today = date('Y-m-d');
        $horizon = date('Y-m-d', strtotime('+' . max(0, (int)$window_days) . ' days'));
        $this->db->select('r.*, v.make, v.model, v.year, v.license_plate, c.customer_name, c.email, c.mobile')
            ->from('db_vehicle_service_records r')
            ->join('db_vehicles v', 'v.id = r.vehicle_id AND v.store_id = r.store_id AND v.is_deleted = 0', 'inner')
            ->join('db_customers c', 'c.id = r.customer_id AND c.store_id = r.store_id', 'left')
            ->where('r.is_deleted', 0)
            ->where('r.next_due_date IS NOT NULL', null, false)
            ->where('r.next_due_date <=', $horizon)
            ->where('(r.last_reminder_slot IS NULL OR r.last_reminder_slot <> DATE_FORMAT(r.next_due_date, \'%Y-%m-%d\'))', null, false)
            ->order_by('r.next_due_date', 'asc')
            ->limit((int)$limit);
        if ($store_ids !== null) {
            if (empty($store_ids)) return [];
            $this->db->where_in('r.store_id', $store_ids);
        }
        return $this->db->get()->result();
    }

    public function mark_reminder_queued($id, $slot) {
        $this->db->where('id', (int)$id);
        return $this->db->update('db_vehicle_service_records', ['last_reminder_slot' => $slot, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Reminder sweep config — one row per store, OFF unless the store chose
     * otherwise (db_vehicle_reminder_config.enabled defaults to 0).
     */
    public function reminder_config($store_id) {
        if (!$this->db->table_exists('db_vehicle_reminder_config')) return null;
        $row = $this->db->where('store_id', (int)$store_id)->get('db_vehicle_reminder_config')->row();
        if ($row) return $row;
        $this->db->insert('db_vehicle_reminder_config', [
            'store_id'   => (int)$store_id,
            'enabled'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->where('store_id', (int)$store_id)->get('db_vehicle_reminder_config')->row();
    }

    public function save_reminder_config($store_id, array $data) {
        $cfg = $this->reminder_config($store_id);
        if (!$cfg) return false;
        unset($data['store_id'], $data['id'], $data['last_sweep_slot']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('store_id', (int)$store_id);
        return $this->db->update('db_vehicle_reminder_config', $data);
    }

    public function mark_sweep($store_id, $slot) {
        if (!$this->db->table_exists('db_vehicle_reminder_config')) return false;
        $this->db->where('store_id', (int)$store_id);
        return $this->db->update('db_vehicle_reminder_config', ['last_sweep_slot' => $slot, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
