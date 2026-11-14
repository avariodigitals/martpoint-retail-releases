<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_digital_product_fields extends CI_Migration {

    public function up() {
        // 1. Add product_type and digital file fields to db_items
        $item_fields = [
            'product_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'physical',
                'null'       => false,
                'after'      => 'package_bit',
                'comment'    => 'physical | service | digital'
            ],
            'digital_file' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'item_image',
                'comment'    => 'Relative path to the downloadable file'
            ],
            'download_limit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3,
                'null'       => false,
                'after'      => 'digital_file',
                'comment'    => 'Max number of times a customer can download'
            ],
            'download_expiry_hours' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 72,
                'null'       => false,
                'after'      => 'download_limit',
                'comment'    => 'Hours after payment the download link remains valid'
            ],
        ];

        foreach ($item_fields as $column => $definition) {
            if (!$this->db->field_exists($column, 'db_items')) {
                $this->dbforge->add_column('db_items', [$column => $definition]);
            }
        }

        // Backfill existing rows from service_bit / package_bit
        if ($this->db->field_exists('product_type', 'db_items')) {
            $this->db->query("UPDATE db_items SET product_type='service' WHERE service_bit=1 OR package_bit=1");
        }

        // 2. Extend db_online_order_items.item_type to allow 'digital'
        if ($this->db->field_exists('item_type', 'db_online_order_items')) {
            $this->db->query("ALTER TABLE db_online_order_items MODIFY COLUMN item_type ENUM('product','service','digital') DEFAULT 'product'");
        }

        // 3. Add download tracking to db_online_order_items
        $order_item_fields = [
            'download_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'service_note',
                'comment'    => 'Secure signed token for file access'
            ],
            'download_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'download_token',
                'comment'    => 'Number of times the customer has downloaded'
            ],
            'download_expires_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'download_count',
                'comment'    => 'When the download link expires'
            ],
        ];

        foreach ($order_item_fields as $column => $definition) {
            if (!$this->db->field_exists($column, 'db_online_order_items')) {
                $this->dbforge->add_column('db_online_order_items', [$column => $definition]);
            }
        }
    }

    public function down() {
        $item_columns = [
            'product_type',
            'digital_file',
            'download_limit',
            'download_expiry_hours',
        ];

        foreach ($item_columns as $column) {
            if ($this->db->field_exists($column, 'db_items')) {
                $this->dbforge->drop_column('db_items', $column);
            }
        }

        $order_item_columns = [
            'download_token',
            'download_count',
            'download_expires_at',
        ];

        foreach ($order_item_columns as $column) {
            if ($this->db->field_exists($column, 'db_online_order_items')) {
                $this->dbforge->drop_column('db_online_order_items', $column);
            }
        }

        // Leave the extended item_type enum in place to avoid data loss on rollbacks
    }
}
