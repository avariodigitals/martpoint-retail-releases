<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_unit_tracking_to_barcodes extends CI_Migration {

    public function up() {
        // Add unit tracking fields to db_item_barcodes
        if (!$this->db->field_exists('serial_number', 'db_item_barcodes')) {
            $this->dbforge->add_column('db_item_barcodes', [
                'serial_number' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true, 'after' => 'batch_lot'],
                'imei_number' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true, 'after' => 'serial_number'],
                'warranty_months' => ['type' => 'INT', 'constraint' => 3, 'null' => true, 'default' => 0, 'after' => 'imei_number'],
            ]);
        }

        // Add barcode_id to sales items to link back to exact unit sold
        if (!$this->db->field_exists('barcode_id', 'db_salesitems')) {
            $this->dbforge->add_column('db_salesitems', [
                'barcode_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0, 'after' => 'item_id'],
            ]);
        }

        // Add barcode_id to hold items
        if (!$this->db->field_exists('barcode_id', 'db_holditems')) {
            $this->dbforge->add_column('db_holditems', [
                'barcode_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0, 'after' => 'item_id'],
            ]);
        }

        // Add barcode_id to sales return items
        if (!$this->db->field_exists('barcode_id', 'db_salesitemsreturn')) {
            $this->dbforge->add_column('db_salesitemsreturn', [
                'barcode_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0, 'after' => 'item_id'],
            ]);
        }

        // Migrate existing items with serial/IMEI on db_items into db_item_barcodes rows
        // Only if they don't already have a barcode row with the same serial/imei
        $items = $this->db->where('(serial_number IS NOT NULL AND serial_number != "") OR (imei_number IS NOT NULL AND imei_number != "")', null, false)
                           ->where('status', 1)
                           ->get('db_items')
                           ->result();

        foreach ($items as $item) {
            $existing = $this->db->where('item_id', $item->id)
                                 ->where('serial_number', $item->serial_number)
                                 ->where('imei_number', $item->imei_number)
                                 ->get('db_item_barcodes')
                                 ->row();
            if (!$existing) {
                $this->db->insert('db_item_barcodes', [
                    'item_id' => $item->id,
                    'barcode' => $item->custom_barcode ?? '',
                    'batch_lot' => '',
                    'serial_number' => $item->serial_number ?? null,
                    'imei_number' => $item->imei_number ?? null,
                    'warranty_months' => $item->warranty_months ?? 0,
                    'purchase_price' => $item->purchase_price ?? 0,
                    'sales_price' => $item->sales_price ?? 0,
                    'mrp' => $item->mrp ?? 0,
                    'qty' => $item->opening_stock ?? 1,
                    'warehouse_id' => $item->warehouse_id ?? 0,
                    'status' => 1,
                    'created_date' => date('Y-m-d'),
                    'created_time' => date('H:i:s'),
                ]);
            }
        }
    }

    public function down() {
        $this->dbforge->drop_column('db_item_barcodes', 'serial_number');
        $this->dbforge->drop_column('db_item_barcodes', 'imei_number');
        $this->dbforge->drop_column('db_item_barcodes', 'warranty_months');
        $this->dbforge->drop_column('db_salesitems', 'barcode_id');
        $this->dbforge->drop_column('db_holditems', 'barcode_id');
        $this->dbforge->drop_column('db_salesitemsreturn', 'barcode_id');
    }
}
