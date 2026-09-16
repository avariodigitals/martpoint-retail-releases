<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_courses_and_memberships extends CI_Migration {

    public function up() {
        // 1. Extend db_items product_type to support courses and memberships
        if ($this->db->field_exists('product_type', 'db_items')) {
            $this->db->query("ALTER TABLE db_items MODIFY COLUMN product_type VARCHAR(20) NOT NULL DEFAULT 'physical' COMMENT 'physical | service | digital | course | membership'");
        }

        // 2. Courses
        if (!$this->db->table_exists('db_courses')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'item_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'title'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
                'description'     => ['type' => 'TEXT', 'null' => TRUE],
                'status'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => FALSE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('store_id');
            $this->dbforge->add_key('item_id');
            $this->dbforge->create_table('db_courses', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 3. Course modules
        if (!$this->db->table_exists('db_course_modules')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'course_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'title'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
                'sort_order'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'null' => FALSE],
                'status'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => FALSE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('course_id');
            $this->dbforge->create_table('db_course_modules', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 4. Course lessons
        if (!$this->db->table_exists('db_course_lessons')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'course_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'module_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'title'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
                'content'         => ['type' => 'LONGTEXT', 'null' => TRUE],
                'video_url'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => TRUE],
                'video_file'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
                'sort_order'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'null' => FALSE],
                'is_published'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => FALSE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('course_id');
            $this->dbforge->add_key('module_id');
            $this->dbforge->create_table('db_course_lessons', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 5. Course enrollments
        if (!$this->db->table_exists('db_course_enrollments')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'customer_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'course_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'item_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'order_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active', 'null' => FALSE, 'comment' => 'active | completed | cancelled'],
                'expires_at'      => ['type' => 'DATETIME', 'null' => TRUE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['store_id','customer_id']);
            $this->dbforge->add_key('course_id');
            $this->dbforge->add_key('item_id');
            $this->dbforge->create_table('db_course_enrollments', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 6. Course progress
        if (!$this->db->table_exists('db_course_progress')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'enrollment_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'lesson_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'is_completed'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE],
                'completed_at'    => ['type' => 'DATETIME', 'null' => TRUE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['enrollment_id','lesson_id']);
            $this->dbforge->create_table('db_course_progress', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 7. Memberships
        if (!$this->db->table_exists('db_memberships')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'item_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'membership_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
                'description'     => ['type' => 'TEXT', 'null' => TRUE],
                'billing_interval' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'monthly', 'null' => FALSE, 'comment' => 'monthly | yearly | weekly'],
                'trial_days'      => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'null' => FALSE],
                'status'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => FALSE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('store_id');
            $this->dbforge->add_key('item_id');
            $this->dbforge->create_table('db_memberships', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 8. Membership subscriptions
        if (!$this->db->table_exists('db_membership_subscriptions')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'customer_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'membership_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'item_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'order_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'start_date'      => ['type' => 'DATE', 'null' => FALSE],
                'end_date'        => ['type' => 'DATE', 'null' => TRUE],
                'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active', 'null' => FALSE, 'comment' => 'active | cancelled | expired'],
                'payment_status'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid', 'null' => FALSE, 'comment' => 'unpaid | paid | failed'],
                'next_billing_at' => ['type' => 'DATE', 'null' => TRUE],
                'paystack_subscription_code' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['store_id','customer_id']);
            $this->dbforge->add_key('membership_id');
            $this->dbforge->add_key('item_id');
            $this->dbforge->create_table('db_membership_subscriptions', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // 9. Extend online order item_type to support courses and memberships
        if ($this->db->field_exists('item_type', 'db_online_order_items')) {
            $this->db->query("ALTER TABLE db_online_order_items MODIFY COLUMN item_type ENUM('product','service','digital','course','membership') DEFAULT 'product'");
        }
    }

    public function down() {
        $tables = [
            'db_course_progress',
            'db_course_enrollments',
            'db_course_lessons',
            'db_course_modules',
            'db_courses',
            'db_membership_subscriptions',
            'db_memberships',
        ];
        foreach ($tables as $table) {
            if ($this->db->table_exists($table)) {
                $this->dbforge->drop_table($table);
            }
        }
    }
}
