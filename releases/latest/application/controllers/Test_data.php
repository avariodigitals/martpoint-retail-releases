<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_data extends CI_Controller {

    public function seed($storeId = 0){
        $storeId = (int)$storeId;
        if($storeId <= 0){
            $store = $this->db->where('id >', 1)->order_by('id','asc')->get('db_store')->row();
            if($store) $storeId = $store->id;
        }
        if($storeId <= 0){
            echo "No store found.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Get a default category, unit and tax for this store
        $category = $this->db->where('store_id', $storeId)->order_by('id','asc')->get('db_category')->row();
        $unit = $this->db->where('store_id', $storeId)->order_by('id','asc')->get('db_units')->row();
        $tax = $this->db->where('store_id', $storeId)->order_by('id','asc')->get('db_tax')->row();

        $catId = $category ? $category->id : 1;
        $unitId = $unit ? $unit->id : 1;
        $taxId = $tax ? $tax->id : 1;

        // 1. Digital product
        $itemCode = 'DIGITAL_TEST_' . date('YmdHis');
        $this->db->insert('db_items', [
            'store_id'              => $storeId,
            'category_id'           => $catId,
            'unit_id'               => $unitId,
            'tax_id'                => $taxId,
            'item_code'             => $itemCode,
            'item_name'             => 'Test Digital Ebook',
            'sku'                   => $itemCode,
            'description'           => 'A sample digital product for testing downloads.',
            'price'                 => 5000.00,
            'sales_price'           => 5000.00,
            'purchase_price'        => 0.00,
            'stock'                 => 9999,
            'status'                => 1,
            'publish_online'        => 1,
            'online_excluded'       => 0,
            'product_type'          => 'digital',
            'service_bit'           => 0,
            'package_bit'           => 0,
            'item_group'            => 'Single',
            'digital_file'          => '',
            'download_limit'        => 3,
            'download_expiry_hours' => 72,
            'created_date'          => $now,
            'created_time'          => $now,
            'created_by'            => 1,
            'child_bit'             => 0,
        ]);
        $digitalItemId = $this->db->insert_id();

        // 2. Course product
        $courseCode = 'COURSE_TEST_' . date('YmdHis');
        $this->db->insert('db_items', [
            'store_id'              => $storeId,
            'category_id'           => $catId,
            'unit_id'               => $unitId,
            'tax_id'                => $taxId,
            'item_code'             => $courseCode,
            'item_name'             => 'Test Creator Course',
            'sku'                   => $courseCode,
            'description'           => 'A sample online course for testing enrollments.',
            'price'                 => 15000.00,
            'sales_price'           => 15000.00,
            'purchase_price'        => 0.00,
            'stock'                 => 9999,
            'status'                => 1,
            'publish_online'        => 1,
            'online_excluded'       => 0,
            'product_type'          => 'course',
            'service_bit'           => 0,
            'package_bit'           => 0,
            'item_group'            => 'Single',
            'created_date'          => $now,
            'created_time'          => $now,
            'created_by'            => 1,
            'child_bit'             => 0,
        ]);
        $courseItemId = $this->db->insert_id();

        // 3. Course content
        $this->load->model('course_model');
        $courseId = $this->course_model->saveCourse([
            'store_id'    => $storeId,
            'item_id'     => $courseItemId,
            'title'       => 'How to Launch a Digital Product',
            'description' => 'This is a sample course to demonstrate the new creator features.',
            'status'      => 1
        ]);

        $moduleId = $this->course_model->saveModule([
            'course_id'  => $courseId,
            'title'      => 'Module 1: Getting Started',
            'sort_order' => 1,
            'status'     => 1
        ]);

        $this->course_model->saveLesson([
            'course_id'    => $courseId,
            'module_id'    => $moduleId,
            'title'        => 'Lesson 1: What is a digital product?',
            'content'      => 'A digital product is any item you can sell online without shipping. Examples include ebooks, courses, templates and memberships.',
            'video_url'    => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'sort_order'   => 1,
            'is_published' => 1
        ]);

        $this->course_model->saveLesson([
            'course_id'    => $courseId,
            'module_id'    => $moduleId,
            'title'        => 'Lesson 2: Setting up your store',
            'content'      => 'You can use MartPoint to create items, set product types and publish your storefront.',
            'video_url'    => '',
            'sort_order'   => 2,
            'is_published' => 1
        ]);

        // 4. Membership product (optional, for completeness)
        $membershipCode = 'MEMBERSHIP_TEST_' . date('YmdHis');
        $this->db->insert('db_items', [
            'store_id'              => $storeId,
            'category_id'           => $catId,
            'unit_id'               => $unitId,
            'tax_id'                => $taxId,
            'item_code'             => $membershipCode,
            'item_name'             => 'Test Creator Membership',
            'sku'                   => $membershipCode,
            'description'           => 'A sample membership for testing recurring billing.',
            'price'                 => 5000.00,
            'sales_price'           => 5000.00,
            'purchase_price'        => 0.00,
            'stock'                 => 9999,
            'status'                => 1,
            'publish_online'        => 1,
            'online_excluded'       => 0,
            'product_type'          => 'membership',
            'service_bit'           => 0,
            'package_bit'           => 0,
            'item_group'            => 'Single',
            'created_date'          => $now,
            'created_time'          => $now,
            'created_by'            => 1,
            'child_bit'             => 0,
        ]);
        $membershipItemId = $this->db->insert_id();

        $this->load->model('creator_membership_model', 'membership_model');
        $this->membership_model->saveMembership([
            'store_id'         => $storeId,
            'item_id'          => $membershipItemId,
            'membership_name'  => 'Premium Creator Circle',
            'description'      => 'Access all courses and monthly live calls.',
            'billing_interval' => 'monthly',
            'trial_days'       => 7,
            'status'           => 1
        ]);

        $store = $this->db->where('id', $storeId)->get('db_store')->row();
        $storeSlug = $store ? ($store->store_slug ?? $storeId) : $storeId;

        echo "Test data created for store #{$storeId} (slug: {$storeSlug})\n";
        echo "Digital product item: {$digitalItemId}\n";
        echo "Course item: {$courseItemId} (course: {$courseId})\n";
        echo "Membership item: {$membershipItemId}\n";
        echo "Storefront: " . base_url('store/' . $storeSlug) . "\n";
        echo "Products: " . base_url('store/' . $storeSlug . '/products') . "\n";
    }
}
