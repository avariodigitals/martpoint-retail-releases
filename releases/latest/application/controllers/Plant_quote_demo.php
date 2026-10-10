<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Local, CLI-only workbook example. Never runs during normal store provisioning. */
class Plant_quote_demo extends CI_Controller {
    private $code = 'PLANTDEMO';
    private $reference = 'SAMPLE-HYKENG-011026';

    public function __construct(){
        parent::__construct();
        if(!is_cli() || getenv('MP_LOCAL_PLANT_DEMO') !== '1'
            || realpath(FCPATH) !== '/Users/ralphmore/Herd/martpointretailapp'){
            show_404(); exit;
        }
        $this->load->model('store_model');
        $this->load->model('business_profile_model');
        $this->load->model('quotation_model');
        $this->load->helper('business_profile');
    }

    public function seed(){
        $sample = json_decode(file_get_contents(APPPATH.'data/plant_quote_hykeng_011026.json'), true);
        if(!$sample || count($sample['lines']) !== 43){ exit("Invalid plant sample fixture.\n"); }
        $store = $this->db->where('store_code', $this->code)->get('db_store')->row();
        if($store && (!in_array($store->store_name, array('Processing Plant Demo', 'Zonat Processing LTD'), true) || $store->industry_type !== 'oil_processing_plant')){
            exit("Store code is already in use by a different business. No changes made.\n");
        }
        if($store && !$store->language_id){
            $this->db->where('id',$store->id)->update('db_store',array('language_id'=>1));
        }
        if($store && $this->db->where('store_id', $store->id)->where('reference_no', $this->reference)->count_all_results('db_quotation')){
            $this->seed_catalog();
            $this->verify();
            return;
        }
        $this->db->trans_begin();
        $password = null;
        if(!$store){
            $today = date('Y-m-d');
            $this->db->insert('db_store', array(
                'store_code'=>$this->code, 'store_name'=>'Zonat Processing LTD',
                'user_id'=>0, 'language_id'=>1, 'currency_id'=>21, 'currency_placement'=>'Left',
                'timezone'=>'Africa/Lagos', 'date_format'=>'dd/mm/yyyy',
                'status'=>1, 'created_date'=>$today, 'created_time'=>date('H:i:s'),
                'created_by'=>'local-demo-seed',
            ));
            $store_id = (int)$this->db->insert_id();
            if(!$store_id){ $this->db->trans_rollback(); exit("Could not create demo store.\n"); }
            $this->session->set_userdata(array('store_id'=>$store_id,'inv_username'=>'plant-demo-seed','inv_userid'=>1,'view_date'=>'dd/mm/yyyy'));
            $this->store_model->seed_modular_settings($store_id);
            $preset = mp_get_business_presets()['oil_processing_plant'];
            $this->business_profile_model->update_profile($store_id, array(
                'industry_type'=>'oil_processing_plant',
                'business_model'=>$preset['business_model'],
                'workflow_template_key'=>$preset['workflow_template'],
                'dashboard_template_key'=>$preset['dashboard_template'],
                'storefront_theme_key'=>$preset['theme_key'],
                'feature_flags_json'=>'{}', 'label_overrides_json'=>'{}',
            ));
            $this->db->insert('db_warehouse', array('store_id'=>$store_id,'warehouse_type'=>'System','warehouse_name'=>'Main Plant','status'=>1));
            $this->db->insert('db_subscription', array(
                'store_id'=>$store_id, 'package_id'=>1, 'package_type'=>'Demo', 'package_name'=>'Local Demo',
                'subscription_date'=>$today, 'expire_date'=>date('Y-m-d', strtotime('+1 year')),
                'payment_status'=>'Paid', 'package_status'=>'Active', 'created_date'=>$today,
                'created_by'=>'local-demo-seed', 'max_users'=>5, 'max_warehouses'=>2,
                'max_items'=>1000, 'max_invoices'=>1000,
            ));
            $this->db->where('id',$store_id)->update('db_store', array('current_subscriptionlist_id'=>$this->db->insert_id()));
            $password = bin2hex(random_bytes(10));
            $this->db->insert('db_users', array(
                'store_id'=>$store_id, 'username'=>'plantdemo', 'first_name'=>'Plant', 'last_name'=>'Demo',
                'email'=>'plantdemo@martpoint.local', 'password'=>password_hash($password, PASSWORD_BCRYPT),
                'role_id'=>2, 'role_name'=>'Store Admin', 'status'=>1,
                'created_date'=>$today, 'created_time'=>date('H:i:s'), 'created_by'=>'local-demo-seed',
            ));
            $this->db->where('id',$store_id)->update('db_store', array('user_id'=>$this->db->insert_id()));
        }else{
            $store_id = (int)$store->id;
            $this->session->set_userdata(array('store_id'=>$store_id,'inv_username'=>'plant-demo-seed','inv_userid'=>1,'view_date'=>'dd/mm/yyyy'));
        }
        $customer = $this->db->where('store_id',$store_id)->where('customer_name','Sample Buyer (review only)')->get('db_customers')->row();
        if(!$customer){
            $this->db->insert('db_customers', array(
                'store_id'=>$store_id, 'count_id'=>get_count_id('db_customers'),
                'customer_code'=>get_init_code('customer',$store_id),
                'customer_name'=>'Sample Buyer (review only)', 'status'=>1,
                'created_date'=>date('Y-m-d'), 'created_by'=>'local-demo-seed',
            ));
            $customer_id = (int)$this->db->insert_id();
        }else{ $customer_id = (int)$customer->id; }
        $_POST = array(
            'customer_id'=>$customer_id, 'quotation_date'=>date('d/m/Y'),
            'reference_no'=>$sample['reference'], 'quotation_note'=>$sample['note'],
            'lines'=>$sample['lines'],
        );
        $result = $this->quotation_model->save_plant_quotation();
        if(strpos($result, 'success<<<###>>>') !== 0 || $this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            exit("Demo quotation save failed: ".$result."\n");
        }
        $this->db->trans_commit();
        $this->seed_catalog();
        $this->verify();
        if($password !== null){ echo "Local demo login: plantdemo / ".$password."\n"; }
    }

    /** Idempotent catalogue for the isolated local Processing Plant demo only. */
    public function seed_catalog(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store || $store->industry_type !== 'oil_processing_plant'
            || !in_array($store->store_name, array('Processing Plant Demo','Zonat Processing LTD'), true)){
            exit("Zonat demo store is unavailable or has been repurposed. No catalogue changes made.\n");
        }
        $store_id = (int)$store->id;
        $this->db->trans_begin();
        if($store->store_name !== 'Zonat Processing LTD'){
            $this->db->where('id',$store_id)->update('db_store',array('store_name'=>'Zonat Processing LTD'));
        }
        $categories = array(
            'Raw materials'=>'ZC-RAW', 'Process inputs'=>'ZC-INPUT', 'Packaging'=>'ZC-PACK',
            'Machinery'=>'ZC-EQUIP', 'Spare parts'=>'ZC-SPARE',
            'Finished goods'=>'ZC-FINISH', 'By-products'=>'ZC-BYPROD',
        );
        $category_ids = array();
        foreach($categories as $name=>$code){
            $row = $this->db->where('store_id',$store_id)->where('category_code',$code)->get('db_category')->row();
            if(!$row){
                $this->db->insert('db_category',array('store_id'=>$store_id,'category_code'=>$code,
                    'category_name'=>$name,'status'=>1));
                $category_ids[$name] = (int)$this->db->insert_id();
            }else{ $category_ids[$name] = (int)$row->id; }
        }
        $units = array('kg'=>'Kilogram','litre'=>'Litre','metre'=>'Metre','piece'=>'Piece','set'=>'Set','drum'=>'Drum');
        $unit_ids = array();
        foreach($units as $code=>$name){
            $row = $this->db->where('store_id',$store_id)->where('shortcode',$code)->get('db_units')->row();
            if(!$row){
                $this->db->insert('db_units',array('store_id'=>$store_id,'unit_name'=>$name,
                    'shortcode'=>$code,'status'=>1));
                $unit_ids[$code] = (int)$this->db->insert_id();
            }else{ $unit_ids[$code] = (int)$row->id; }
        }
        $supplier_names = array('ZSUP-01'=>'Zonat Growers Cooperative (sample)',
            'ZSUP-02'=>'Industrial Plant Spares (sample)');
        foreach($supplier_names as $code=>$name){
            if(!$this->db->where('store_id',$store_id)->where('supplier_code',$code)->count_all_results('db_suppliers')){
                $this->db->insert('db_suppliers',array('store_id'=>$store_id,'supplier_code'=>$code,
                    'supplier_name'=>$name,'opening_balance'=>0,'purchase_due'=>0,
                    'purchase_return_due'=>0,'status'=>1,'created_date'=>date('Y-m-d'),
                    'created_by'=>'local-demo-seed'));
            }
        }
        $catalogue = array(
            array('RM-FFB-001','Fresh fruit bunches','Raw materials','kg',1,'Palm fruit received from growers'),
            array('RM-PK-001','Palm kernels','Raw materials','kg',1,'Palm kernels for kernel-oil processing'),
            array('RM-BE-001','Bleaching earth','Process inputs','kg',1,'Process consumable; record actual use by batch'),
            array('PK-DRUM-200','200 L steel drum','Packaging','piece',1,'Bulk-oil packing drum'),
            array('PK-BOT-1L','1 L PET bottle','Packaging','piece',1,'Oil bottle for packaged output'),
            array('EQ-EK200','EK-200K expeller','Machinery','set',1,'First-pressing expeller equipment'),
            array('EQ-FP01','Filter press machine 30 x 30','Machinery','set',1,'Filter press equipment; 35 plates'),
            array('G11','Gear coupling (M) 18T','Spare parts','piece',1,'EK-200K first-pressing expeller part'),
            array('SP-FC-40','Filter cloth 40-inch width','Spare parts','metre',1,'Measured filter-cloth length'),
            array('FG-CPO-BULK','Crude palm oil, bulk','Finished goods','litre',0,'Bulk crude palm oil output'),
            array('FG-PKO-BULK','Palm kernel oil, bulk','Finished goods','litre',0,'Bulk palm kernel oil output'),
            array('BP-PKC-001','Palm kernel cake','By-products','kg',0,'Recorded processing by-product'),
        );
        foreach($catalogue as $entry){
            list($code,$name,$category,$unit,$not_for_sale,$description) = $entry;
            if($this->db->where('store_id',$store_id)->where('item_code',$code)->count_all_results('db_items')){ continue; }
            $this->db->insert('db_items',array('store_id'=>$store_id,'item_code'=>$code,'item_name'=>$name,
                'category_id'=>$category_ids[$category],'unit_id'=>$unit_ids[$unit],
                'not_for_sale'=>$not_for_sale,'description'=>$description,'stock'=>0,
                'purchase_price'=>0,'sales_price'=>0,'price'=>0,'alert_qty'=>0,'reorder_point'=>0,
                'status'=>1,'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),
                'created_by'=>'local-demo-seed'));
        }
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            exit("Zonat demo catalogue save failed. Changes rolled back.\n");
        }
        $this->db->trans_commit();
        echo "Zonat demo catalogue ready: 7 categories, 6 units, 2 suppliers, 12 items.\n";
    }

    public function verify(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store){ exit("Demo store does not exist.\n"); }
        $quote = $this->db->where('store_id',$store->id)->where('reference_no',$this->reference)->get('db_quotation')->row();
        if(!$quote){ exit("Demo quotation does not exist.\n"); }
        $items = $this->db->select('section_name,manual_item_name,quotation_qty,price_per_unit,total_cost')->where('store_id',$store->id)->where('quotation_id',$quote->id)->order_by('id')->get('db_quotationitems')->result();
        $sections = array_unique(array_map(function($line){ return $line->section_name; }, $items));
        $sum = array_sum(array_map(function($line){ return (float)$line->total_cost; }, $items));
        $other_stores = $this->db->where('store_id !=',$store->id)->where('reference_no',$this->reference)->count_all_results('db_quotation');
        $ok = $store->industry_type === 'oil_processing_plant' && $store->store_name === 'Zonat Processing LTD'
            && count($items) === 43 && count($sections) === 4
            && abs((float)$quote->grand_total-301581840) < .01 && abs($sum-301581840) < .01;
        echo ($ok ? 'PASS' : 'FAIL')." store #{$store->id}, quote #{$quote->id}, ".count($items)." lines, ".count($sections)." sections, total NGN ".number_format($sum,2).", other-store copies {$other_stores}\n";
        if(!$ok){ exit(1); }
        echo 'Open: http://martpointretailapp.test/quotation/invoice/'.$quote->id."\n";
    }

    /** Financial save check inside a transaction that is always rolled back. */
    public function test_tax(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store){ exit("Demo store does not exist.\n"); }
        $store_id = (int)$store->id;
        $customer = $this->db->where('store_id',$store_id)->where('customer_name','Sample Buyer (review only)')->get('db_customers')->row();
        if(!$customer){ exit("Demo customer does not exist.\n"); }
        $this->session->set_userdata(array('store_id'=>$store_id,'inv_username'=>'plant-demo-tax-check','inv_userid'=>1,'view_date'=>'dd/mm/yyyy'));
        $this->db->trans_begin();
        $this->db->insert('db_tax',array('store_id'=>$store_id,'tax_name'=>'Temporary test tax','tax'=>5,'status'=>1));
        $tax_id = (int)$this->db->insert_id();
        $_POST = array('customer_id'=>$customer->id,'quotation_date'=>date('d/m/Y'),'tax_id'=>$tax_id,
            'reference_no'=>'PLANT-TAX-ROLLBACK-TEST','lines'=>array(array('name'=>'Test line','qty'=>2,'price'=>500,'unit'=>'PC')));
        $result = $this->quotation_model->save_plant_quotation();
        $parts = explode('<<<###>>>',$result);
        $quote_id = isset($parts[1]) ? (int)$parts[1] : 0;
        $quote = $quote_id ? $this->db->where('id',$quote_id)->where('store_id',$store_id)->get('db_quotation')->row() : null;
        $line = $quote_id ? $this->db->where('quotation_id',$quote_id)->where('store_id',$store_id)->get('db_quotationitems')->row() : null;
        $ok = $quote && $line && abs((float)$quote->subtotal-1000) < .01
            && abs((float)$quote->grand_total-1050) < .01 && abs((float)$line->tax_amt-50) < .01;
        $this->db->trans_rollback();
        $left = $this->db->where('store_id',$store_id)->where('reference_no','PLANT-TAX-ROLLBACK-TEST')->count_all_results('db_quotation');
        echo ($ok && !$left ? 'PASS' : 'FAIL')." tax save: subtotal 1000, tax 50, total 1050; rolled-back quotes {$left}\n";
        if(!$ok || $left){ exit(1); }
    }

    public function test_banner(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store){ exit("Demo store does not exist.\n"); }
        $this->session->set_userdata('store_id',(int)$store->id);
        $quote = $this->load->view('oil_processing/document_banner',array('document_kind'=>'quotation'),true);
        $invoice = $this->load->view('oil_processing/document_banner',array('document_kind'=>'invoice'),true);
        $ok = strpos($quote,'mp-plant-document-banner') !== false
            && strpos($invoice,'mp-plant-document-banner') !== false
            && strpos($quote,'store logo') === false && strpos($invoice,'store logo') === false;
        echo ($ok ? 'PASS' : 'FAIL')." quotation and invoice use the enabled plant banner without a logo\n";
        if(!$ok){ exit(1); }
    }

    /** Verify percentage payment terms survive the real save path without keeping a test quote. */
    public function test_note(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store){ exit("Demo store does not exist.\n"); }
        $customer = $this->db->where('store_id',$store->id)->where('customer_name','Sample Buyer (review only)')->get('db_customers')->row();
        if(!$customer){ exit("Demo customer does not exist.\n"); }
        $this->session->set_userdata(array('store_id'=>(int)$store->id,'inv_username'=>'plant-demo-note-check','inv_userid'=>1,'view_date'=>'dd/mm/yyyy'));
        $note = '30% deposit, 20% advance, 100% upfront';
        $this->db->trans_begin();
        $_POST = array('customer_id'=>$customer->id,'quotation_date'=>date('d/m/Y'),
            'reference_no'=>'PLANT-NOTE-ROLLBACK-TEST','quotation_note'=>$note,
            'lines'=>array(array('name'=>'Test line','qty'=>1,'price'=>1,'unit'=>'PC')));
        $result = $this->quotation_model->save_plant_quotation();
        $parts = explode('<<<###>>>',$result);
        $quote_id = isset($parts[1]) ? (int)$parts[1] : 0;
        $quote = $quote_id ? $this->db->select('quotation_note')->where('id',$quote_id)->where('store_id',$store->id)->get('db_quotation')->row() : null;
        $ok = $quote && $quote->quotation_note === $note;
        $this->db->trans_rollback();
        echo ($ok ? 'PASS' : 'FAIL')." quotation note preserves literal percentages; test quote rolled back\n";
        if(!$ok){ exit(1); }
    }

    /** Check the shared invoice-terms input path without changing saved settings. */
    public function test_invoice_terms(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store){ exit("Demo store does not exist.\n"); }
        $_POST['invoice_terms'] = '30% deposit and 70% before delivery';
        $terms = mp_post_text('invoice_terms');
        $this->db->trans_begin();
        $this->db->where('store_id',$store->id)->update('db_store_receipt_settings',array('invoice_terms'=>$terms));
        $saved = $this->db->select('invoice_terms')->where('store_id',$store->id)->get('db_store_receipt_settings')->row();
        $ok = $saved && $saved->invoice_terms === $_POST['invoice_terms'];
        $this->db->trans_rollback();
        echo ($ok ? 'PASS' : 'FAIL')." invoice terms preserve literal percentages; setting rolled back\n";
        if(!$ok){ exit(1); }
    }

    /** Exercise the shared document signature with settings rolled back. */
    public function test_signature(){
        $store = $this->db->where('store_code',$this->code)->get('db_store')->row();
        if(!$store){ exit("Demo store does not exist.\n"); }
        $this->session->set_userdata('store_id',(int)$store->id);
        $this->load->helper('document_signature');
        $this->db->trans_begin();
        $this->db->where('id',$store->id)->update('db_store',array('signature'=>'uploads/signature/bs-signature-demo_4.png'));
        $this->db->where('store_id',$store->id)->update('db_store_pos_settings',array('show_signature'=>1));
        $shown = $this->load->view('partials/document_signature',array('signature_store_id'=>$store->id),true);
        $this->db->where('store_id',$store->id)->update('db_store_pos_settings',array('show_signature'=>0));
        $hidden = $this->load->view('partials/document_signature',array('signature_store_id'=>$store->id),true);
        $this->db->trans_rollback();
        $ok = strpos($shown,'data:image/png;base64,') !== false
            && strpos($shown,'Authorized signature') !== false
            && strpos($hidden,'<img') === false;
        echo ($ok ? 'PASS' : 'FAIL')." document signature follows the store switch; settings rolled back\n";
        if(!$ok){ exit(1); }
    }
}
