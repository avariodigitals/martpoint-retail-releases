<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer Notes Model
 * Append-only per-customer note history. Each note belongs to its creator;
 * only the creator may edit it.
 */
class Customer_notes_model extends CI_Model {

    public function get_by_customer($customer_id, $limit = 100) {
        return $this->db->where('customer_id', $customer_id)
                        ->order_by('id', 'DESC')
                        ->limit($limit)
                        ->get('db_customer_notes')->result();
    }

    public function get($id) {
        return $this->db->where('id', $id)->get('db_customer_notes')->row();
    }

    public function add($customer_id, $note) {
        $data = array(
            'store_id'      => get_current_store_id(),
            'customer_id'   => $customer_id,
            'note'          => $note,
            'created_by'    => $this->session->userdata('inv_username'),
            'created_by_id' => $this->session->userdata('inv_userid'),
            'created_date'  => date('Y-m-d'),
            'created_time'  => date('H:i:s'),
        );
        $this->db->insert('db_customer_notes', $data);
        return $this->db->insert_id();
    }

    public function update($id, $note) {
        $this->db->where('id', $id)->update('db_customer_notes', array('note' => $note));
    }

    /**
     * Only the note's creator may edit it. Matches on user id first, then
     * falls back to the recorded username (covers seeded legacy notes that
     * have no created_by_id).
     */
    public function is_owner($note) {
        if (empty($note)) return false;
        $uid   = (int) $this->session->userdata('inv_userid');
        $uname = (string) $this->session->userdata('inv_username');
        if (!empty($note->created_by_id) && (int) $note->created_by_id === $uid) return true;
        if (!empty($note->created_by) && $uname !== '' && strcasecmp($note->created_by, $uname) === 0) return true;
        return false;
    }
}
