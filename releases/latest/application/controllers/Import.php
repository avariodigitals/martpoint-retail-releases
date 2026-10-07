<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('country_model','country');
	}

	public function customers(){
		$this->permission_check('import_customers');
		$data=$this->data;
		$data['page_title']=$this->lang->line('import_customers');
		$data['import_fields'] = $this->_customer_import_fields();
		$data['import_history'] = array();
		if($this->db->table_exists('db_import_batches')){
			$data['import_history'] = $this->db->where('store_id',get_current_store_id())
				->where('import_type','customers')->order_by('id','desc')->limit(20)
				->get('db_import_batches')->result();
		}
		$data['content']=$this->load->view('customers/desktop/import_customers', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

    public function xss_html_filter($input){
        return $this->security->xss_clean(html_escape($input));
    }

	public function import_customers_csv() {

                $store_id=(store_module() && is_admin() && isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();   

                $filename = $_FILES["import_file"]["name"];
                
                if($_FILES['import_file']['size'] > 0)
                {   
                    
                	$config['upload_path']          = './uploads/csv/customers';
	                $config['allowed_types']        = 'csv';
	                $this->load->library('upload', $config);

	                if ( ! $this->upload->do_upload('import_file')){
			                $error = array('error' => $this->upload->display_errors());
			                print($error['error']);
			                exit();
			        }
			        else{
			        	    $file_name=$this->upload->data('file_name');
			        }

                    $file = fopen('uploads/csv/customers/'.$file_name,"r");
                    
                    //Save flag
                    $flag='true';
                    $this->db->trans_begin();
                    $i=1;
                    while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                        if($i++==1){ continue; }

                        //Customers name should not be empty
                        if(empty($importdata[0])){
                          continue;
                        }

                        //Neutralise formula injection / markup before any use
                        $importdata = array_map(function($v){ return $this->xss_html_filter($v); }, $importdata);

                        $customer_name=$importdata[0];
                        $mobile=trim($importdata[1]);

                        if(!empty($mobile)){
                            $query2=$this->db->select('id')->from('db_customers')
                                ->where('mobile', $mobile)->where('store_id', $store_id)->get();
                            if($query2->num_rows()>0){
                                $this->db->trans_rollback();
                                fclose($file);
                                echo "Import Failed!<br>'".htmlspecialchars($mobile)."' Mobile Number already Exist.<br>Row Number:".$i;
                                return;
                            }
                        }

                        $country_name=trim($importdata[8]);
                        $state_name=trim($importdata[9]);

                        $shipping_country_name=trim($importdata[14]);
                        $shipping_state_name  =trim($importdata[15]);

                        
                        //if not exist country create it and return id, else just return id if exist
                    	$country_id=(!empty($country_name)) ? $this->get_country_id($country_name) : null;

                        //if not exist state create it and return id, else just return id if exist
                        $state_id=(!empty($state_name)) ? $this->get_state_id($state_name,$country_name,$country_id,$store_id) : null;

                       

                        //if not exist country create it and return id, else just return id if exist
                        $shipping_country_id=(!empty($shipping_country_name)) ? $this->get_country_id($shipping_country_name) : null;
                        //if not exist country create it and return id, else just return id if exist
                        $shipping_state_id=(!empty($shipping_state_name)) ? $this->get_state_id($shipping_state_name,$shipping_country_name,$shipping_country_id,$store_id) : null;


                        
                        $row = array(
                            'store_id'    	=>  $store_id,
                            'count_id'              => get_count_id('db_customers'), 
                            'customer_code'     =>  get_init_code('customer'), 
                            'customer_name'     =>  $customer_name,
                            'mobile'     		=>  !empty($mobile)?$mobile:'',
                            'email'         	=>  !empty($importdata[2])?$importdata[2]:'',
                            'phone'       	 	=>  !empty($importdata[3])?$importdata[3]:'',
                            'gstin'       		=>  !empty($importdata[4])?$importdata[4]:'',
                            'tax_number'       	=>  !empty($importdata[5])?$importdata[5]:'',

                            'opening_balance'   =>  !empty($importdata[6])?$importdata[6]:'',
                            'credit_limit'      =>  !empty($importdata[7])?$importdata[7]:'',

                            'country_id'       	=>  $country_id,//8
                            'state_id'       	=>  $state_id,//9
                            'postcode'       	=>  !empty($importdata[10])?$importdata[10]:'',
                            'city'       	    =>  !empty($importdata[11])?$importdata[11]:'',
                            'address'           =>  !empty($importdata[12])?$importdata[12]:'',
                            
                            'location_link'       =>  !empty($this->xss_html_filter($importdata[13]))?$this->xss_html_filter($importdata[13]):null,//
                            
                            /*System Info*/
                            'created_date'              => $CUR_DATE,
                            'created_time'              => $CUR_TIME,
                            'created_by'                => $CUR_USERNAME,
                            'system_ip'                 => $SYSTEM_IP,
                            'system_name'               => $SYSTEM_NAME,
                            'status'                    => 1,
                        );
                        
                        //If any record failed to save flag will be set false,then all records rolled back
                        if(!$this->db->insert('db_customers',$row)){
                            $flag='false';
                        }
                        $customer_id = $this->db->insert_id();
                        //Insert Shipping Address
                        $shipping_address_details = array(
                                                            'store_id'      =>$store_id,
                                                            'country_id'    =>$shipping_country_id,
                                                            'state_id'      =>$shipping_state_id,
                                                            'city'          =>!empty($importdata[16])?$importdata[16]:'',
                                                            'postcode'      =>!empty($importdata[17])?$importdata[17]:'',
                                                            'address'       =>!empty($importdata[18])?$importdata[18]:'',
                                                            'customer_id'   =>$customer_id,
                                                            'status'        =>1,
                                                        );
                        $Q2 = $this->db->insert('db_shippingaddress', $shipping_address_details);
                        if(!$Q2){
                            $flag='false';
                            continue;
                        }
                        $shipping_address_id=$this->db->insert_id();
                        //end
                        //Update shipping address to customer
                        $Q3 = $this->db->set('shippingaddress_id',$shipping_address_id)->where('id',$customer_id)->update('db_customers');
                        if(!$Q3){
                            $flag='false';
                            continue;
                        }
                        //end

                    }
                    
                    
                    if(!$flag){
                        $this->db->trans_rollback();
                        echo 'failed';
                    }else{
                        $this->db->trans_commit();
                        echo "success";
                        $this->session->set_flashdata('success', 'Success!! Customers Data Imported Successfully!');
                    }
                    fclose($file);
                }
            
 			//unlink('uploads/csv/customers/'.$file_name);
        }

    /* ============================================================
     * Customer import v2 — staged pipeline
     * upload -> map -> preview -> chunked run. Each stage is a separate
     * request; db_import_batches is the audit record and db_import_rows
     * holds per-row status so a failed run can be resumed.
     * ============================================================ */

    private function _customer_import_fields(){
        return array(
            'customer_name'       => 'Customer Name *',
            'mobile'              => 'Mobile',
            'email'               => 'Email',
            'phone'               => 'Phone',
            'gstin'               => 'GST Number',
            'tax_number'          => 'Tax Number',
            'opening_balance'     => 'Opening Balance',
            'credit_limit'        => 'Credit Limit',
            'customer_type'       => 'Customer Type',
            'country'             => 'Country',
            'state'               => 'State',
            'postcode'            => 'Postcode',
            'city'                => 'City',
            'address'             => 'Address',
            'location_link'       => 'Location Link',
            'ship_country'        => 'Shipping Country',
            'ship_state'          => 'Shipping State',
            'ship_city'           => 'Shipping City',
            'ship_postcode'       => 'Shipping Postcode',
            'ship_address'        => 'Shipping Address',
            'payment_terms_days'  => 'Payment Terms (days)',
            'birthday'            => 'Birthday (YYYY-MM-DD)',
            'notes'               => 'Notes',
        );
    }

    private function _import_json($arr){
        $arr['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($arr);
        exit;
    }

    private function _load_import_batch($batch_id, $store_id){
        return $this->db->where('id',(int)$batch_id)->where('store_id',$store_id)
            ->where('import_type','customers')->get('db_import_batches')->row();
    }

    private function _guess_customer_map($headers){
        $aliases = array(
            'customer_name' => array('customer_name','name','customer','client','full name','fullname','customer name','client name'),
            'mobile'        => array('mobile','mobile number','phone','phone number','contact','whatsapp','telephone'),
            'email'         => array('email','e-mail','email address','mail'),
            'phone'         => array('phone','alt phone','alternate phone','landline','phone2'),
            'gstin'         => array('gstin','gst','gst number','gst_number'),
            'tax_number'    => array('tax_number','tax number','tax id','vat','vat number','vatin'),
            'opening_balance'=> array('opening_balance','opening balance','previous_due','previous due','balance','due'),
            'credit_limit'  => array('credit_limit','credit limit'),
            'customer_type' => array('customer_type','type','customer type'),
            'country'       => array('country','country_name','country name'),
            'state'         => array('state','state_name','state name'),
            'postcode'      => array('postcode','postal code','zip','zip code','pincode','pin code'),
            'city'          => array('city','town'),
            'address'       => array('address','street','address line'),
            'location_link' => array('location_link','location link','map link','location'),
            'ship_country'  => array('shipping_country','ship_country','shipping country','shipping country name'),
            'ship_state'    => array('shipping_state','ship_state','shipping state','shipping state name'),
            'ship_city'     => array('shipping_city','ship_city','shipping city'),
            'ship_postcode' => array('shipping_postcode','ship_postcode','shipping postcode','shipping zip'),
            'ship_address'  => array('shipping_address','ship_address','shipping address'),
            'payment_terms_days' => array('payment_terms_days','payment terms','payment_terms'),
            'birthday'      => array('birthday','dob','date of birth','birth date'),
            'notes'         => array('notes','note','comments','remarks'),
        );
        $map = array();
        foreach($headers as $idx=>$h){
            $norm = strtolower(trim((string)$h));
            $norm = preg_replace('/\s+/',' ',str_replace(array('_','-'),' ',$norm));
            foreach($aliases as $field=>$names){
                if(isset($map[$field])) continue;
                if(in_array($norm,$names,true) || in_array(strtolower(str_replace(' ','_',$norm)),$names,true)){
                    $map[$field] = $idx;
                    break;
                }
            }
        }
        return $map;
    }

    public function import_customers_upload(){
        $this->permission_check_with_msg('import_customers');
        $store_id = get_current_store_id();

        if(empty($_FILES['import_file']['name']) || $_FILES['import_file']['size'] <= 0){
            $this->_import_json(array('status'=>'error','message'=>'Please choose a CSV file.'));
        }

        $config['upload_path']   = './uploads/csv/customers';
        $config['allowed_types'] = 'csv';
        $config['encrypt_name']  = TRUE;
        $config['max_size']      = 10240;
        $this->load->library('upload', $config);
        if(!$this->upload->do_upload('import_file')){
            $this->_import_json(array('status'=>'error','message'=>strip_tags($this->upload->display_errors())));
        }
        $file_name = $this->upload->data('file_name');
        $filepath  = 'uploads/csv/customers/'.$file_name;

        $fh = fopen($filepath,'r');
        if(!$fh){ $this->_import_json(array('status'=>'error','message'=>'Could not read the uploaded file.')); }
        $header = fgetcsv($fh);
        if(!$header || count(array_filter($header)) === 0){
            fclose($fh);
            $this->_import_json(array('status'=>'error','message'=>'The file does not look like a valid CSV.'));
        }
        $samples = array();
        while(count($samples) < 5 && ($row = fgetcsv($fh)) !== FALSE){
            $samples[] = $row;
        }
        fclose($fh);

        $this->db->insert('db_import_batches', array(
            'store_id'    => $store_id,
            'import_type' => 'customers',
            'filename'    => $this->security->xss_clean($_FILES['import_file']['name']),
            'filepath'    => $filepath,
            'status'      => 'uploaded',
            'created_by'  => isset($this->data['CUR_USERNAME']) ? $this->data['CUR_USERNAME'] : '',
        ));
        $batch_id = $this->db->insert_id();

        $this->_import_json(array(
            'status'    => 'success',
            'batch_id'  => $batch_id,
            'headers'   => array_values($header),
            'samples'   => $samples,
            'suggested' => $this->_guess_customer_map($header),
            'fields'    => $this->_customer_import_fields(),
        ));
    }

    private function _validate_customer_row($data){
        $errs = array();
        if(empty($data['customer_name'])){
            $errs[] = 'Customer name is required';
        }
        if(!empty($data['mobile']) && !preg_match('/^[0-9+\-\s()]{5,25}$/',$data['mobile'])){
            $errs[] = 'Invalid mobile number';
        }
        if(!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]{5,25}$/',$data['phone'])){
            $errs[] = 'Invalid phone number';
        }
        if(!empty($data['email']) && !filter_var($data['email'],FILTER_VALIDATE_EMAIL)){
            $errs[] = 'Invalid email address';
        }
        if(!empty($data['opening_balance']) && !is_numeric($data['opening_balance'])){
            $errs[] = 'Opening balance must be a number';
        }
        if(!empty($data['credit_limit']) && !is_numeric($data['credit_limit'])){
            $errs[] = 'Credit limit must be a number';
        }
        if(!empty($data['birthday']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$data['birthday'])){
            $errs[] = 'Birthday must be YYYY-MM-DD';
        }
        if(!empty($data['payment_terms_days']) && !ctype_digit((string)$data['payment_terms_days'])){
            $errs[] = 'Payment terms must be whole days';
        }
        return $errs;
    }

    public function import_customers_preview(){
        $this->permission_check_with_msg('import_customers');
        $store_id  = get_current_store_id();
        $batch_id  = (int)$this->input->post('batch_id', TRUE);
        $batch     = $this->_load_import_batch($batch_id,$store_id);
        if(!$batch){ $this->_import_json(array('status'=>'error','message'=>'Import batch not found.')); }
        if(in_array($batch->status,array('processing','completed'))){
            $this->_import_json(array('status'=>'error','message'=>'This import is already '.$batch->status.'.'));
        }

        $map = $this->input->post('map');
        $has_header = (int)$this->input->post('has_header', TRUE) ? 1 : 0;
        $dup_policy = $this->input->post('dup_policy', TRUE);
        if(!in_array($dup_policy,array('skip','update','reject'),true)){ $dup_policy = 'skip'; }
        if(!is_array($map)){ $map = array(); }
        // Whitelist + one column per field
        $fields = array_keys($this->_customer_import_fields());
        $clean_map = array();
        $used_cols = array();
        foreach($map as $field=>$col){
            if(!in_array($field,$fields,true)) continue;
            $col = (int)$col;
            if($col < 0 || isset($used_cols[$col])) continue;
            $used_cols[$col] = true;
            $clean_map[$field] = $col;
        }
        if(!isset($clean_map['customer_name'])){
            $this->_import_json(array('status'=>'error','message'=>'Map a CSV column to Customer Name before previewing.'));
        }

        $fh = fopen($batch->filepath,'r');
        if(!$fh){ $this->_import_json(array('status'=>'error','message'=>'Uploaded file is no longer available.')); }

        $this->db->trans_begin();
        $this->db->where('batch_id',$batch_id)->where('store_id',$store_id)->delete('db_import_rows');

        $row_no = 0;
        $total = $err_rows = $dup_rows = 0;
        $seen_mobile = array();
        $seen_email  = array();
        while(($csv = fgetcsv($fh)) !== FALSE){
            if($row_no === 0 && $has_header){ $row_no++; continue; }
            $row_no++;
            if(count($csv) === 1 && trim((string)$csv[0]) === ''){ continue; } // skip blank lines
            $total++;

            $data = array();
            foreach($clean_map as $field=>$col){
                $v = isset($csv[$col]) ? $csv[$col] : '';
                $data[$field] = trim((string)$this->xss_html_filter($v));
            }

            $errors = $this->_validate_customer_row($data);
            $status = 'pending';
            $msg = null;

            if(empty($errors)){
                // within-file duplicates
                if(!empty($data['mobile']) && isset($seen_mobile[$data['mobile']])){
                    $status='duplicate'; $msg='Mobile repeated from row '.$seen_mobile[$data['mobile']];
                } elseif(!empty($data['email']) && isset($seen_email[strtolower($data['email'])])){
                    $status='duplicate'; $msg='Email repeated from row '.$seen_email[strtolower($data['email'])];
                } else {
                    // duplicates against the live table (store-scoped)
                    if(!empty($data['mobile'])){
                        $exists = $this->db->where('store_id',$store_id)->where('mobile',$data['mobile'])
                            ->where('status',1)->where('delete_bit',0)
                            ->limit(1)->get('db_customers')->num_rows();
                        if($exists){ $status='duplicate'; $msg='Mobile already exists'; }
                    }
                    if($status==='pending' && !empty($data['email'])){
                        $exists = $this->db->where('store_id',$store_id)->where('email',$data['email'])
                            ->where('status',1)->where('delete_bit',0)
                            ->limit(1)->get('db_customers')->num_rows();
                        if($exists){ $status='duplicate'; $msg='Email already exists'; }
                    }
                }
            } else {
                $status='error'; $msg=implode('; ',$errors);
            }

            if($status==='pending'){
                if(!empty($data['mobile'])) $seen_mobile[$data['mobile']] = $row_no;
                if(!empty($data['email']))  $seen_email[strtolower($data['email'])] = $row_no;
            } elseif($status==='error'){ $err_rows++; } else { $dup_rows++; }

            $this->db->insert('db_import_rows',array(
                'batch_id'=>$batch_id,'store_id'=>$store_id,'row_number'=>$row_no,
                'status'=>$status,'error_message'=>$msg,'raw_json'=>json_encode($data),
            ));
        }
        fclose($fh);

        $this->db->where('id',$batch_id)->where('store_id',$store_id)->update('db_import_batches',array(
            'field_map_json'=>json_encode($clean_map),'dup_policy'=>$dup_policy,'has_header'=>$has_header,
            'total_rows'=>$total,'error_rows'=>$err_rows,'dup_rows'=>$dup_rows,
            'status'=>'previewed',
        ));

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $this->_import_json(array('status'=>'error','message'=>'Failed to stage the file. Please try again.'));
        }
        $this->db->trans_commit();

        $sample_errors = $this->db->select('row_number,status,error_message')
            ->where('batch_id',$batch_id)->where_in('status',array('error','duplicate'))
            ->order_by('row_number','asc')->limit(10)->get('db_import_rows')->result_array();

        $this->_import_json(array(
            'status'=>'success','batch_id'=>$batch_id,'total'=>$total,
            'valid'=>$total-$err_rows-$dup_rows,'errors'=>$err_rows,'duplicates'=>$dup_rows,
            'sample_errors'=>$sample_errors,'dup_policy'=>$dup_policy,
        ));
    }

    // Fields that may be written when dup_policy=update (never balances)
    private function _customer_update_fields(){
        return array('customer_name','email','phone','gstin','tax_number','customer_type',
            'postcode','city','address','location_link','payment_terms_days','birthday','notes');
    }

    public function import_customers_run(){
        $this->permission_check_with_msg('import_customers');
        $store_id = get_current_store_id();
        $batch_id = (int)$this->input->post('batch_id', TRUE);
        $batch    = $this->_load_import_batch($batch_id,$store_id);
        if(!$batch){ $this->_import_json(array('status'=>'error','message'=>'Import batch not found.')); }
        if(!in_array($batch->status,array('previewed','processing','failed'))){
            $this->_import_json(array('status'=>'error','message'=>'Preview the file before running the import.'));
        }

        $policy = $batch->dup_policy;
        $chunk  = 200;

        // Rows to apply this pass: valid rows + duplicates when policy=update
        $this->db->where('batch_id',$batch_id)->where('store_id',$store_id)
            ->where_in('status', $policy==='update' ? array('pending','duplicate') : array('pending'))
            ->order_by('row_number','asc')->limit($chunk);
        $rows = $this->db->get('db_import_rows')->result();

        if(empty($rows)){
            $this->_import_customers_finish($batch,$store_id);
            return;
        }

        if($batch->status !== 'processing'){
            $this->db->where('id',$batch_id)->where('store_id',$store_id)
                ->update('db_import_batches',array('status'=>'processing','started_at'=>date('Y-m-d H:i:s'),'error_message'=>null));
        }

        $this->db->trans_begin();
        $failed = null;
        foreach($rows as $r){
            $data = json_decode($r->raw_json,true);
            if(!is_array($data)){ $failed='Row '.$r->row_number.' has corrupt data'; break; }

            // Re-check duplicates at apply time — another import may have
            // created the customer between preview and run.
            $existing = null;
            if(!empty($data['mobile'])){
                $existing = $this->db->where('store_id',$store_id)->where('mobile',$data['mobile'])
                    ->where('status',1)->where('delete_bit',0)->limit(1)->get('db_customers')->row();
            }
            if(!$existing && !empty($data['email'])){
                $existing = $this->db->where('store_id',$store_id)->where('email',$data['email'])
                    ->where('status',1)->where('delete_bit',0)->limit(1)->get('db_customers')->row();
            }

            if($existing){
                if($policy === 'reject'){
                    $this->db->where('id',$r->id)->update('db_import_rows',array('status'=>'error','error_message'=>'Duplicate: matches existing customer #'.$existing->id));
                } elseif($policy === 'update'){
                    $upd = array();
                    foreach($this->_customer_update_fields() as $f){
                        if(isset($data[$f]) && $data[$f] !== ''){ $upd[$f] = $data[$f]; }
                    }
                    if(!empty($upd)){
                        if(!$this->db->where('id',$existing->id)->where('store_id',$store_id)->update('db_customers',$upd)){
                            $failed='Row '.$r->row_number.' update failed'; break;
                        }
                    }
                    $this->db->where('id',$r->id)->update('db_import_rows',array('status'=>'updated','customer_id'=>$existing->id));
                } else {
                    $this->db->where('id',$r->id)->update('db_import_rows',array('status'=>'skipped','error_message'=>'Duplicate: matches existing customer #'.$existing->id,'customer_id'=>$existing->id));
                }
                continue;
            }

            if($r->status === 'duplicate'){
                // in-file duplicate that no longer matches the live table
                if($policy === 'reject'){
                    $this->db->where('id',$r->id)->update('db_import_rows',array('status'=>'error','error_message'=>'Duplicate row rejected by policy'));
                    continue;
                }
                if($policy === 'skip'){
                    $this->db->where('id',$r->id)->update('db_import_rows',array('status'=>'skipped','error_message'=>'Duplicate row skipped'));
                    continue;
                }
            }

            $country_id = !empty($data['country']) ? $this->get_country_id($data['country']) : null;
            $state_id   = !empty($data['state'])   ? $this->get_state_id($data['state'],$data['country'] ?? '',$country_id,$store_id) : null;
            $ship_country_id = !empty($data['ship_country']) ? $this->get_country_id($data['ship_country']) : null;
            $ship_state_id   = !empty($data['ship_state'])   ? $this->get_state_id($data['ship_state'],$data['ship_country'] ?? '',$ship_country_id,$store_id) : null;

            $row = array(
                'store_id'      => $store_id,
                'count_id'      => get_count_id('db_customers'),
                'customer_code' => get_init_code('customer'),
                'customer_name' => $data['customer_name'],
                'mobile'        => $data['mobile'] ?? '',
                'email'         => $data['email'] ?? '',
                'phone'         => $data['phone'] ?? '',
                'gstin'         => $data['gstin'] ?? '',
                'tax_number'    => $data['tax_number'] ?? '',
                'customer_type' => $data['customer_type'] ?? '',
                'opening_balance'   => (isset($data['opening_balance']) && $data['opening_balance'] !== '') ? $data['opening_balance'] : '',
                'credit_limit'      => (isset($data['credit_limit']) && $data['credit_limit'] !== '') ? $data['credit_limit'] : '',
                'country_id'    => $country_id,
                'state_id'      => $state_id,
                'postcode'      => $data['postcode'] ?? '',
                'city'          => $data['city'] ?? '',
                'address'       => $data['address'] ?? '',
                'location_link' => !empty($data['location_link']) ? $data['location_link'] : null,
                'payment_terms_days' => (isset($data['payment_terms_days']) && $data['payment_terms_days'] !== '') ? (int)$data['payment_terms_days'] : null,
                'birthday'      => !empty($data['birthday']) ? $data['birthday'] : null,
                'notes'         => $data['notes'] ?? '',
                'created_date'  => isset($this->data['CUR_DATE']) ? $this->data['CUR_DATE'] : date('Y-m-d'),
                'created_time'  => isset($this->data['CUR_TIME']) ? $this->data['CUR_TIME'] : date('H:i:s'),
                'created_by'    => isset($this->data['CUR_USERNAME']) ? $this->data['CUR_USERNAME'] : '',
                'system_ip'     => isset($this->data['SYSTEM_IP']) ? $this->data['SYSTEM_IP'] : '',
                'system_name'   => isset($this->data['SYSTEM_NAME']) ? $this->data['SYSTEM_NAME'] : '',
                'status'        => 1,
            );

            if(!$this->db->insert('db_customers',$row)){
                $failed='Row '.$r->row_number.' insert failed'; break;
            }
            $customer_id = $this->db->insert_id();

            if(!empty($data['ship_country']) || !empty($data['ship_state']) || !empty($data['ship_city'])
                || !empty($data['ship_postcode']) || !empty($data['ship_address'])){
                $ship = array(
                    'store_id'=>$store_id,'country_id'=>$ship_country_id,'state_id'=>$ship_state_id,
                    'city'=>$data['ship_city'] ?? '','postcode'=>$data['ship_postcode'] ?? '',
                    'address'=>$data['ship_address'] ?? '','customer_id'=>$customer_id,'status'=>1,
                );
                if(!$this->db->insert('db_shippingaddress',$ship)){
                    $failed='Row '.$r->row_number.' shipping address failed'; break;
                }
                $this->db->set('shippingaddress_id',$this->db->insert_id())
                    ->where('id',$customer_id)->where('store_id',$store_id)->update('db_customers');
            }

            $this->db->where('id',$r->id)->update('db_import_rows',array('status'=>'ok','customer_id'=>$customer_id));
        }

        if($failed !== null || $this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $this->db->where('id',$batch_id)->where('store_id',$store_id)
                ->update('db_import_batches',array('status'=>'failed','error_message'=>$failed ?: 'Database error'));
            $this->_import_json(array('status'=>'error','message'=>'Import paused: '.($failed ?: 'database error').'. No rows from this batch were written — you can retry safely.','batch_id'=>$batch_id));
        }
        $this->db->trans_commit();
        $this->_import_customers_finish($batch,$store_id);
    }

    private function _import_customers_finish($batch,$store_id){
        $counts = array('pending'=>0,'ok'=>0,'error'=>0,'skipped'=>0,'duplicate'=>0,'updated'=>0);
        $q = $this->db->select('status,COUNT(*) as n')->where('batch_id',$batch->id)
            ->where('store_id',$store_id)->group_by('status')->get('db_import_rows')->result();
        foreach($q as $c){ if(isset($counts[$c->status])) $counts[$c->status] = (int)$c->n; }

        $remaining = $counts['pending'] + ($batch->dup_policy==='update' ? $counts['duplicate'] : 0);
        $done = ($remaining === 0);
        $this->db->where('id',$batch->id)->where('store_id',$store_id)->update('db_import_batches',array(
            'processed_rows'=>$batch->total_rows - $remaining,
            'ok_rows'=>$counts['ok'],'error_rows'=>$counts['error'],
            'dup_rows'=>$counts['duplicate'] + $counts['skipped'],
            'updated_rows'=>$counts['updated'],
            'status'=> $done ? 'completed' : 'processing',
            'completed_at'=> $done ? date('Y-m-d H:i:s') : null,
        ));
        $this->_import_json(array(
            'status'=>'success','batch_id'=>$batch->id,'done'=>$done,
            'processed'=>$batch->total_rows - $remaining,'total'=>(int)$batch->total_rows,
            'ok'=>$counts['ok'],'updated'=>$counts['updated'],
            'skipped'=>$counts['skipped']+$counts['duplicate'],'errors'=>$counts['error'],
        ));
    }

    public function import_customers_errors($batch_id){
        $this->permission_check('import_customers');
        $store_id = get_current_store_id();
        $batch = $this->_load_import_batch((int)$batch_id,$store_id);
        if(!$batch){ show_404(); return; }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="import_errors_batch_'.$batch_id.'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out,array('row_number','status','error','customer_name','mobile','email'));
        $rows = $this->db->where('batch_id',$batch->id)->where('store_id',$store_id)
            ->where_in('status',array('error','skipped','duplicate'))
            ->order_by('row_number','asc')->get('db_import_rows')->result();
        foreach($rows as $r){
            $d = json_decode($r->raw_json,true);
            fputcsv($out,array($r->row_number,$r->status,$r->error_message,
                isset($d['customer_name'])?$d['customer_name']:'',
                isset($d['mobile'])?$d['mobile']:'',
                isset($d['email'])?$d['email']:''));
        }
        fclose($out);
        exit;
    }

    public function get_country_id($country_name=''){
        $country_name = trim((string)$country_name);
        if($country_name === ''){ return null; }
        $q2=$this->db->select('id')->from('db_country')
            ->where('UPPER(country) = UPPER('.$this->db->escape($country_name).')', null, false)->get();
        if($q2->num_rows()>0){
            return $q2->row()->id;
        }
        else{
            $q2=$this->db->insert('db_country', array('country'=>$country_name,'status'=>1));
            if($q2){
                return $this->db->insert_id();
            }
            return false;
        }
    }
    public function get_state_id($state_name,$country_name,$country_id,$store_id){
        $state_name = trim((string)$state_name);
        if($state_name === ''){ return null; }
        $q2=$this->db->select('id')->from('db_states')
            ->where('UPPER(state) = UPPER('.$this->db->escape($state_name).')', null, false)
            ->where('store_id', (int)$store_id)->get();
        if($q2->num_rows()>0){
            return $q2->row()->id;
        }
        else{
            $q2=$this->db->insert('db_states', array(
                'state'=>$state_name,'country'=>trim((string)$country_name),
                'country_id'=>$country_id,'status'=>1,'store_id'=>(int)$store_id));
            if($q2){
                return $this->db->insert_id();
            }
            return false;
        }
    }
    public function suppliers(){
        $this->permission_check('import_suppliers');
        $data=$this->data;
        $data['page_title']=$this->lang->line('import_suppliers');
        $data['content']=$this->load->view('customers/desktop/import_suppliers', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }
    public function import_suppliers_csv() {
                $filename = $_FILES["import_file"]["name"];

                $store_id=(store_module() && is_admin() && isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();   
                
                if($_FILES['import_file']['size'] > 0)
                {   
                    
                    $config['upload_path']          = './uploads/csv/suppliers';
                    $config['allowed_types']        = 'csv';
                    $this->load->library('upload', $config);

                    if ( ! $this->upload->do_upload('import_file')){
                            $error = array('error' => $this->upload->display_errors());
                            print($error['error']);
                            exit();
                    }
                    else{
                            $file_name=$this->upload->data('file_name');
                    }
                    
                  

                    $file = fopen('uploads/csv/suppliers/'.$file_name,"r");
                    
                    //Save flag
                    $flag='true';
                    $this->db->trans_begin();
                    $i=1;
                    while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                        if($i++==1){ continue; }

                        //supplier name should not be empty
                        if(empty($importdata[0])){
                          continue;
                        }

                        //Neutralise formula injection / markup before any use
                        $importdata = array_map(function($v){ return $this->xss_html_filter($v); }, $importdata);

                        $supplier_name=$importdata[0];
                        $mobile=trim($importdata[1]);
                        if(!empty($mobile)){
                            $query2=$this->db->select('id')->from('db_suppliers')
                                ->where('mobile', $mobile)->where('store_id', $store_id)->get();
                            if($query2->num_rows()>0){
                                $this->db->trans_rollback();
                                fclose($file);
                                echo "Import Failed!<br>'".htmlspecialchars($mobile)."' Mobile Number already Exist.<br>Row Number:".$i;
                                return;
                            }
                        }

                        $country_name=trim($importdata[6]);
                        $state_name=trim($importdata[7]);
                        //if not exist country create it and return id, else just return id if exist
                        $country_id=(!empty($country_name)) ? $this->get_country_id($country_name) : null;

                        //if not exist state create it and return id, else just return id if exist
                        $state_id=(!empty($state_name)) ? $this->get_state_id($state_name,$country_name,$country_id,$store_id) : null;

                        
                        $row = array(
                            'store_id'      =>  $store_id,
                            'count_id'           => get_count_id('db_suppliers'), 
                            'supplier_code'     =>  get_init_code('supplier'), 
                            'supplier_name'     =>  $supplier_name,
                            'mobile'            =>  !empty($mobile)?$mobile:'',
                            'email'             =>  !empty($importdata[2])?$importdata[2]:'',
                            'phone'             =>  !empty($importdata[3])?$importdata[3]:'',
                            'gstin'             =>  !empty($importdata[4])?$importdata[4]:'',
                            'tax_number'        =>  !empty($importdata[5])?$importdata[5]:'',
                            'country_id'        =>  $country_id,
                            'state_id'          =>  $state_id,
                            'postcode'          =>  !empty($importdata[8])?$importdata[8]:'',
                            'address'           =>  !empty($importdata[9])?$importdata[9]:'',
                            'opening_balance'   =>  !empty($importdata[10])?$importdata[10]:'',
                            /*System Info*/
                            'created_date'              => $CUR_DATE,
                            'created_time'              => $CUR_TIME,
                            'created_by'                => $CUR_USERNAME,
                            'system_ip'                 => $SYSTEM_IP,
                            'system_name'               => $SYSTEM_NAME,
                            'status'                    => 1,
                        );
                        
                        //If any record failed to save flag will be set false,then all records rolled back
                        if(!$this->db->insert('db_suppliers',$row)){
                            $flag='false';
                        }
                        
                        //Compulsary records
                        if(empty($importdata[0])){
                          $flag='false';   
                        }


                        
                    }
                    
                    
                    if(!$flag){
                        $this->db->trans_rollback();
                        echo 'failed';
                    }else{
                        $this->db->trans_commit();
                        echo "success";
                        $this->session->set_flashdata('success', 'Success!! suppliers Data Imported Successfully!');
                    }
                    fclose($file);
                }
            
            //unlink('uploads/csv/suppliers/'.$file_name);
        }
    public function items(){
        $this->permission_check('import_items');
        $data=$this->data;
        $data['page_title']=$this->lang->line('import_items');
        $data['content'] = $this->load->view('import/import_items', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }
    public function import_items_csv() {

              
                $warehouse_id = (int)$this->input->post('warehouse_id');
                $filename = $_FILES["import_file"]["name"];
                $this->load->model('pos_model');      
                $this->load->model('items_model');      

                $store_id=get_current_store_id();   
                
                if($_FILES['import_file']['size'] > 0)
                {   
                    
                    $config['upload_path']          = './uploads/csv/items';
                    $config['allowed_types']        = 'csv';
                    $this->load->library('upload', $config);

                    if ( ! $this->upload->do_upload('import_file')){
                            $error = array('error' => $this->upload->display_errors());
                            print($error['error']);
                            exit();
                    }
                    else{
                            $file_name=$this->upload->data('file_name');
                    }
                    
                  

                    $file = fopen('uploads/csv/items/'.$file_name,"r");
                    
                    //Save flag
                    $flag=true;

                    

                    $this->db->trans_begin();
                    $i=1;
                    while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                        if($i++==1){ continue; }

                        //Item name should not be empty
                        /*if(empty($importdata[0])){
                          continue;
                        }*/
                        $item_name = ($importdata[0]);
                       
                    
                        $category_name =$this->xss_html_filter($importdata[1]);
                        $unit_name =$this->xss_html_filter($importdata[4]);
                        $brand_name =$this->xss_html_filter($importdata[6]);
                        $tax_name =$this->xss_html_filter($importdata[9]);
                        $tax_per =$this->xss_html_filter($importdata[10]);
                        $category_id=(!empty($category_name)) ? $this->get_category_id($category_name,$store_id) : null;
                        $unit_id=(!empty($unit_name)) ? $this->get_unit_id($unit_name,$store_id) : null;
                        $brand_id=(!empty($brand_name)) ? $this->get_brand_id($brand_name,$store_id) : null;
                        $tax_id=(!empty($tax_name)) ? $this->get_tax_id($tax_name,$tax_per,$store_id) : null;

                        $sales_price = !empty($this->xss_html_filter($importdata[12]))?$this->xss_html_filter(string_to_number($importdata[12])):0;
                     
                        $mrp = !empty($this->xss_html_filter($importdata[19]))?$this->xss_html_filter(string_to_number($importdata[19])):0;
                        
                        $price = !empty($this->xss_html_filter($importdata[8]))?$this->xss_html_filter(string_to_number($importdata[8])):0;
                        $profit_margin = ($sales_price-$price);
                        $profit_margin = ($price>0) ? ($profit_margin/$price)*100 : $profit_margin;


                        $tax_type = !empty($this->xss_html_filter($importdata[11]))?$this->xss_html_filter($importdata[11]):'Exclusive';

                        $purchase_price = ($tax_type=='Inclusive') ? $price : original_cost($price,$tax_per,$tax_type);
                      
                        $row = array(
                            'store_id'          =>  $store_id,
                            'count_id'          =>  get_count_id('db_items'), 
                            'item_code'         =>  get_init_code('item'), 
                            'item_name'         =>  $item_name,//0
                            'category_id'       =>  $category_id,//1
                            'sku'               =>  !empty($this->xss_html_filter($importdata[2]))?$this->xss_html_filter($importdata[2]):'',
                            'hsn'               =>  !empty($this->xss_html_filter($importdata[3]))?$this->xss_html_filter($importdata[3]):'',
                            'unit_id'           =>  $unit_id,//4
                            'alert_qty'         =>  !empty($this->xss_html_filter($importdata[5]))?$this->xss_html_filter($importdata[5]):'',
                            'brand_id'          =>  $brand_id,//6
                            'lot_number'        =>  !empty($this->xss_html_filter($importdata[7]))?$this->xss_html_filter($importdata[7]):'',
                            
                            'price'             =>  $price,//Actual Price
                            'tax_id'            =>  $tax_id,//10 //ok
                            'purchase_price'    =>  $purchase_price,
                            'tax_type'          =>  $tax_type,//ok
                            'sales_price'       =>  $sales_price,//ok
                            'profit_margin'       =>  $profit_margin,
                            'mrp'       =>  $mrp,
                            'stock'             =>  !empty($this->xss_html_filter($importdata[13]))?$this->xss_html_filter($importdata[13]):0,//ok
                            'custom_barcode'    =>  !empty($this->xss_html_filter($importdata[14]))?$this->xss_html_filter($importdata[14]):0,//ok
                            'seller_points'    =>  !empty($this->xss_html_filter($importdata[15]))?$this->xss_html_filter($importdata[15]):0,//ok
                            'description'    =>  !empty($this->xss_html_filter($importdata[16]))?$this->xss_html_filter($importdata[16]):0,//ok
                            'discount_type'    =>  !empty($this->xss_html_filter($importdata[17]))?$this->xss_html_filter($importdata[17]):0,//ok
                            'discount'    =>  !empty($this->xss_html_filter($importdata[18]))?$this->xss_html_filter(string_to_number($importdata[18])):0,//ok
                            'item_group'        =>  'Single',//10 //ok
                            /*System Info*/
                            'created_date'              => $CUR_DATE,
                            'created_time'              => $CUR_TIME,
                            'created_by'                => $CUR_USERNAME,
                            'system_ip'                 => $SYSTEM_IP,
                            'system_name'               => $SYSTEM_NAME,
                            'status'                    => 1,
                        );

                        //If any record failed to save flag will be set false,then all records rolled back
                        if(!$this->db->insert('db_items',$row)){
                            $flag=false;
                        }
                        
                        //Compulsary records
                        if(empty($item_name)){
                          $flag=false;   
                        }
                        $item_id = $this->db->insert_id();


                        if(!empty($this->xss_html_filter($importdata[13])) && $this->xss_html_filter($importdata[13])>0){
                            $array_params = array(  'store_id'=> $store_id,
                                                    'item_id'=>$item_id, 
                                                    'warehouse_id'=>$warehouse_id, 
                                                    'adjustment_qty'=>$this->xss_html_filter($importdata[13]));
                            $this->load->model('items_model');
                            $q2 = $this->items_model->add_opening_stock($array_params);
                            if(!$q2){
                                $flag=false;
                                continue;
                            }

                      

                        }

                      
                        
                    }
                    

                    /*Update items in all warehouses of the item*/
                   /* $q7=update_warehousewise_items_qty_by_store($store_id);
                    if(!$q7){
                        $flag=false;   
                        return "failed";
                    }*/
                    //echo "HEllo";exit();
                    
                    if(!$flag){
                        $this->db->trans_rollback();
                        echo 'failed';
                    }else{
                        $this->db->where('store_id', (int)$store_id)->where("expire_date LIKE '0000%'", null, false)->update('db_items', array('expire_date'=>null));
                        $this->db->trans_commit();
                        echo "success";
                        $this->session->set_flashdata('success', 'Success!! items Data Imported Successfully!');
                    }
                    fclose($file);
                }
            
            //unlink('uploads/csv/items/'.$file_name);
        }

        public function get_category_id($category_name,$store_id){
            $category_name = trim((string)$category_name);
            if($category_name === ''){ return null; }
            $q2=$this->db->select('id')->from('db_category')
                ->where('UPPER(category_name) = UPPER('.$this->db->escape($category_name).')', null, false)
                ->where('store_id', (int)$store_id)->get();
            if($q2->num_rows()>0){
                return $q2->row()->id;
            }
            else{
                    //If category name not found in destination, then create category
                    $info = array(
                        'count_id'                  => get_count_id('db_category',$store_id), 
                        'category_code'             => get_init_code('category',$store_id), 
                        'store_id'                  => $store_id, 
                        'category_name'             => $category_name, 
                        'description'               => '',
                        'status'                    => 1,
                    );
                    $q1 = $this->db->insert('db_category', $info);
                    return $this->db->insert_id();                    
            }
        }
        public function get_unit_id($unit_name,$store_id){
            $unit_name = trim((string)$unit_name);
            if($unit_name === ''){ return null; }
            $q2=$this->db->select('id')->from('db_units')
                ->where('UPPER(unit_name) = UPPER('.$this->db->escape($unit_name).')', null, false)
                ->where('store_id', (int)$store_id)->get();
            if($q2->num_rows()>0){
                return $q2->row()->id;
            }
            else{
                    //If category name not found in destination, then create category
                    $info = array(
                            'store_id'                  => $store_id, 
                            'unit_name'                 => $unit_name, 
                            'description'               => '',
                            'status'                    => 1,
                        );
                    $q1 = $this->db->insert('db_units', $info);
                    return $this->db->insert_id();                   
            }
        }
        public function get_brand_id($brand_name,$store_id){
            $brand_name = trim((string)$brand_name);
            if($brand_name === ''){ return null; }
            $q2=$this->db->select('id')->from('db_brands')
                ->where('UPPER(brand_name) = UPPER('.$this->db->escape($brand_name).')', null, false)
                ->where('store_id', (int)$store_id)->get();
            if($q2->num_rows()>0){
                return $q2->row()->id;
            }
            else{
                    //If category name not found in destination, then create category
                    $info = array(
                            'store_id'                  => $store_id, 
                            'brand_name'                 => $brand_name, 
                            'description'               => '',
                            'status'                    => 1,
                        );
                    $q1 = $this->db->insert('db_brands', $info);
                    return $this->db->insert_id();                   
            }
        }
        public function get_tax_id($tax_name,$tax_per,$store_id){
            $tax_name = trim((string)$tax_name);
            if($tax_name === ''){ return null; }
            $q2=$this->db->select('id')->from('db_tax')
                ->where('UPPER(tax_name) = UPPER('.$this->db->escape($tax_name).')', null, false)
                ->where('store_id', (int)$store_id)->get();
            if($q2->num_rows()>0){
                return $q2->row()->id;
            }
            else{
                    //If category name not found in destination, then create category
                    $info = array(
                            'store_id'                  => $store_id, 
                            'tax_name'                  => $tax_name, 
                            'tax'                       => $tax_per, 
                            'status'                    => 1,
                        );
                    $q1 = $this->db->insert('db_tax', $info);
                    return $this->db->insert_id();                   
            }
        }
public function services(){
            $this->permission_check('import_services');
            $data=$this->data;
            $data['page_title']=$this->lang->line('import_services');
            $data['content'] = $this->load->view('import/import_services', $data, TRUE);
            $this->load->view('mp_layout', $data);
        }

        public function import_services_csv() {

                //$store_id = $_POST['store_id'];
                
                $filename = $_FILES["import_file"]["name"];
                $this->load->model('pos_model');      
                $this->load->model('items_model');      

                //$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();   
                $store_id=get_current_store_id();   
                
                if($_FILES['import_file']['size'] > 0)
                {   
                    
                    $config['upload_path']          = './uploads/csv/services';
                    $config['allowed_types']        = 'csv';
                    $this->load->library('upload', $config);

                    if ( ! $this->upload->do_upload('import_file')){
                            $error = array('error' => $this->upload->display_errors());
                            print($error['error']);
                            exit();
                    }
                    else{
                            $file_name=$this->upload->data('file_name');
                    }
                    
                  

                    $file = fopen('uploads/csv/services/'.$file_name,"r");
                    
                    //Save flag
                    $flag=true;
                    $this->db->trans_begin();
                    $i=1;
                    while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                        if($i++==1){ continue; }
                        $item_name = $importdata[0];                       
                    
                        $category_name =$importdata[1];
                        $tax_name =$importdata[3];
                        $tax_per =$importdata[4];
                        $category_id=(!empty($category_name)) ? $this->get_category_id($category_name,$store_id) : null;
                        $tax_id=(!empty($tax_name)) ? $this->get_tax_id($tax_name,$tax_per,$store_id) : null;

                        $price = !empty($importdata[2])?$importdata[2]:0;

                        $tax_type = !empty($this->xss_html_filter($importdata[5]))?$this->xss_html_filter($importdata[5]):'Exclusive';

                        $purchase_price = ($tax_type=='Inclusive') ? $price : original_cost($price,$tax_per,$tax_type);
                      
                        $row = array(
                            'store_id'          =>  $store_id,
                            'count_id'          =>  get_count_id('db_items'), 
                            'item_code'         =>  get_init_code('item'), 
                            'item_name'         =>  $item_name,//0
                            'category_id'       =>  $category_id,//1
                            'price'             =>  $price,
                            'tax_id'            =>  $tax_id,//10 //ok
                            'purchase_price'    =>  $purchase_price,
                            'tax_type'          =>  $tax_type,
                            'sales_price'       =>  !empty($importdata[6])?$importdata[6]:0,//ok
                            'hsn'               =>  !empty($this->xss_html_filter($importdata[7]))?$this->xss_html_filter($importdata[7]):'',
                            'sac'               =>  !empty($this->xss_html_filter($importdata[8]))?$this->xss_html_filter($importdata[8]):'',
                            'custom_barcode'    =>  !empty($this->xss_html_filter($importdata[9]))?$this->xss_html_filter($importdata[9]):0,//ok
                            'seller_points'    =>  !empty($this->xss_html_filter($importdata[10]))?$this->xss_html_filter($importdata[10]):0,//ok
                            'description'    =>  !empty($this->xss_html_filter($importdata[11]))?$this->xss_html_filter($importdata[11]):0,//ok
                            'discount_type'    =>  !empty($this->xss_html_filter($importdata[12]))?$this->xss_html_filter($importdata[12]):0,//ok
                            'discount'    =>  !empty($this->xss_html_filter($importdata[13]))?$this->xss_html_filter($importdata[13]):0,//ok
                            /*System Info*/
                            'created_date'              => $CUR_DATE,
                            'created_time'              => $CUR_TIME,
                            'created_by'                => $CUR_USERNAME,
                            'system_ip'                 => $SYSTEM_IP,
                            'system_name'               => $SYSTEM_NAME,
                            'status'                    => 1,
                            'service_bit'                    => 1,
                        );

                        //If any record failed to save flag will be set false,then all records rolled back
                        if(!$this->db->insert('db_items',$row)){
                            $flag=false;
                        }
                        
                        //Compulsary records
                        if(empty($importdata[0])){
                          $flag=false;   
                        }
                        $item_id = $this->db->insert_id();
                        
                    }
                    
                    
                    
                    if(!$flag){
                        $this->db->trans_rollback();
                        echo 'failed';
                    }else{
                        $this->db->where('store_id', (int)$store_id)->where("expire_date LIKE '0000%'", null, false)->update('db_items', array('expire_date'=>null));
                        $this->db->trans_commit();
                        echo "success";
                        $this->session->set_flashdata('success', 'Success!! Services Data Imported Successfully!');
                    }
                    fclose($file);
                }
            
            //unlink('uploads/csv/items/'.$file_name);
        }
        public function import_country_csv() {
                if(!is_admin()){
                    echo "Admin has the right!!";exit;
                }
                
               
                $file_name = 'country.csv';

                

                    $file = fopen('uploads/csv/country/'.$file_name,"r");
                    
                    //Save flag
                    $flag=true;
                    $this->db->trans_begin();
                    $i=1;
                    while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){

                        

                        $country = $importdata[0];   
                        $country = $this->xss_html_filter($country);                    
                        $country = trim($country);
                        
                        $q2=$this->db->select('id')->from('db_country')
                            ->like('UPPER(country)', strtoupper($country), 'both', false)->get();
                        if($q2->num_rows()>0){
                            continue;
                        }

                        $row = array(
                            'country'          =>  $country,
                        );
                        //If any record failed to save flag will be set false,then all records rolled back
                        if(!$this->db->insert('db_country',$row)){
                            $flag=false;
                        }
                    }
                    
                    
                    
                    if(!$flag){
                        $this->db->trans_rollback();
                        echo 'failed';
                    }else{
                      
                        $this->db->trans_commit();
                        echo "success";
                    }
                    fclose($file);
              
            
            //unlink('uploads/csv/items/'.$file_name);
        }
        public function variants(){
        $this->permission_check('import_items');
        $data=$this->data;
        $data['page_title']='Import Variant Products';
        $this->load->view('import/import_variants', $data);
    }

    public function import_variants_csv(){
        $warehouse_id = (int)$this->input->post('warehouse_id');
        $filename = $_FILES["import_file"]["name"];
        $this->load->model('pos_model');
        $this->load->model('items_model');

        $store_id=get_current_store_id();

        if($_FILES['import_file']['size'] > 0)
        {
            $config['upload_path']          = './uploads/csv/items';
            $config['allowed_types']        = 'csv';
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('import_file')){
                    $error = array('error' => $this->upload->display_errors());
                    print($error['error']);
                    exit();
            }
            else{
                    $file_name=$this->upload->data('file_name');
            }

            $file = fopen('uploads/csv/items/'.$file_name,"r");

            $flag=true;
            $this->db->trans_begin();
            $i=1;
            $rows = [];
            while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                if($i++==1){ continue; }
                if(empty($importdata[0]) && empty($importdata[21]) && empty($importdata[22])){ continue; }
                $rows[] = $importdata;
            }

            $parents = [];
            foreach($rows as $importdata){
                $category_name  = $this->xss_html_filter($importdata[1]);
                $unit_name      = $this->xss_html_filter($importdata[4]);
                $brand_name     = $this->xss_html_filter($importdata[6]);
                $tax_name       = $this->xss_html_filter($importdata[9]);
                $tax_per        = $this->xss_html_filter($importdata[10]);
                $category_id    = (!empty($category_name)) ? $this->get_category_id($category_name,$store_id) : null;
                $unit_id        = (!empty($unit_name)) ? $this->get_unit_id($unit_name,$store_id) : null;
                $brand_id       = (!empty($brand_name)) ? $this->get_brand_id($brand_name,$store_id) : null;
                $tax_id         = (!empty($tax_name)) ? $this->get_tax_id($tax_name,$tax_per,$store_id) : null;

                $item_group = !empty($this->xss_html_filter($importdata[20])) ? trim($this->xss_html_filter($importdata[20])) : 'Single';
                if($item_group == 'Variant' || $item_group == 'Child' || !empty($this->xss_html_filter($importdata[22]))){
                    continue;
                }
                if($item_group != 'Variants' && $item_group != 'Single'){
                    $item_group = 'Single';
                }

                $sales_price = !empty($this->xss_html_filter($importdata[12])) ? $this->xss_html_filter(string_to_number($importdata[12])) : 0;
                $mrp         = !empty($this->xss_html_filter($importdata[19])) ? $this->xss_html_filter(string_to_number($importdata[19])) : 0;
                $price       = !empty($this->xss_html_filter($importdata[8])) ? $this->xss_html_filter(string_to_number($importdata[8])) : 0;
                $profit_margin = ($sales_price-$price);
                $profit_margin = ($price>0) ? ($profit_margin/$price)*100 : $profit_margin;

                $tax_type = !empty($this->xss_html_filter($importdata[11])) ? $this->xss_html_filter($importdata[11]) : 'Exclusive';
                $purchase_price = ($tax_type=='Inclusive') ? $price : original_cost($price,$tax_per,$tax_type);

                $sku = !empty($this->xss_html_filter($importdata[2])) ? $this->xss_html_filter($importdata[2]) : '';
                $row = array(
                    'store_id'          =>  $store_id,
                    'count_id'          =>  get_count_id('db_items'),
                    'item_code'         =>  get_init_code('item'),
                    'item_name'         =>  $importdata[0],
                    'category_id'       =>  $category_id,
                    'sku'               =>  $sku,
                    'hsn'               =>  !empty($this->xss_html_filter($importdata[3]))?$this->xss_html_filter($importdata[3]):'',
                    'unit_id'           =>  $unit_id,
                    'alert_qty'         =>  !empty($this->xss_html_filter($importdata[5]))?$this->xss_html_filter($importdata[5]):0,
                    'brand_id'          =>  $brand_id,
                    'lot_number'        =>  !empty($this->xss_html_filter($importdata[7]))?$this->xss_html_filter($importdata[7]):'',
                    'price'             =>  $price,
                    'tax_id'            =>  $tax_id,
                    'purchase_price'    =>  $purchase_price,
                    'tax_type'          =>  $tax_type,
                    'sales_price'       =>  $sales_price,
                    'profit_margin'     =>  $profit_margin,
                    'mrp'               =>  $mrp,
                    'stock'             =>  !empty($this->xss_html_filter($importdata[13]))?$this->xss_html_filter($importdata[13]):0,
                    'custom_barcode'    =>  !empty($this->xss_html_filter($importdata[14]))?$this->xss_html_filter($importdata[14]):'',
                    'seller_points'     =>  !empty($this->xss_html_filter($importdata[15]))?$this->xss_html_filter($importdata[15]):0,
                    'description'       =>  !empty($this->xss_html_filter($importdata[16]))?$this->xss_html_filter($importdata[16]):'',
                    'discount_type'     =>  !empty($this->xss_html_filter($importdata[17]))?$this->xss_html_filter($importdata[17]):'',
                    'discount'          =>  !empty($this->xss_html_filter($importdata[18]))?$this->xss_html_filter(string_to_number($importdata[18])):0,
                    'item_group'        =>  $item_group,
                    'child_bit'         =>  0,
                    'publish_online'    =>  0,
                    'status'            =>  1,
                    'created_date'      =>  $CUR_DATE,
                    'created_time'      =>  $CUR_TIME,
                    'created_by'        =>  $CUR_USERNAME,
                    'system_ip'         =>  $SYSTEM_IP,
                    'system_name'       =>  $SYSTEM_NAME,
                );

                if(!$this->db->insert('db_items',$row)){
                    $flag=false;
                }
                $item_id = $this->db->insert_id();

                if(!empty($sku)){
                    $parents[$sku] = [
                        'id'=>$item_id, 'name'=>$importdata[0],
                        'category_id'=>$category_id, 'unit_id'=>$unit_id, 'brand_id'=>$brand_id, 'tax_id'=>$tax_id,
                        'tax_per'=>$tax_per, 'tax_type'=>$tax_type,
                        'price'=>$price, 'purchase_price'=>$purchase_price, 'sales_price'=>$sales_price, 'mrp'=>$mrp,
                        'discount_type'=>$row['discount_type'], 'discount'=>$row['discount'], 'alert_qty'=>$row['alert_qty']
                    ];
                }

                if(!empty($importdata[13]) && $importdata[13]>0 && $item_group=='Single'){
                    $array_params = array(
                        'store_id'          =>  $store_id,
                        'item_id'           =>  $item_id,
                        'warehouse_id'      =>  $warehouse_id,
                        'adjustment_qty'    =>  $this->xss_html_filter($importdata[13])
                    );
                    $q2 = $this->items_model->add_opening_stock($array_params);
                    if(!$q2){ $flag=false; continue; }
                }
            }

            foreach($rows as $importdata){
                $item_group = !empty($this->xss_html_filter($importdata[20])) ? trim($this->xss_html_filter($importdata[20])) : 'Single';
                if(($item_group != 'Variant' && $item_group != 'Child') && empty($this->xss_html_filter($importdata[22]))){ continue; }

                $parent_sku = !empty($this->xss_html_filter($importdata[21])) ? $this->xss_html_filter($importdata[21]) : '';
                $variant_name = $this->xss_html_filter($importdata[22]);
                if(empty($parent_sku) || empty($variant_name)){ continue; }

                if(!isset($parents[$parent_sku])){
                    $pq = $this->db->select('id,item_name,category_id,unit_id,brand_id,tax_id,tax_type,price,purchase_price,sales_price,mrp,discount_type,discount,alert_qty')->where('sku',$parent_sku)->where('store_id',$store_id)->where('child_bit',0)->get('db_items');
                    if($pq->num_rows()==0){ $flag=false; continue; }
                    $p = $pq->row();
                    $parents[$parent_sku] = [
                        'id'=>$p->id, 'name'=>$p->item_name,
                        'category_id'=>$p->category_id, 'unit_id'=>$p->unit_id, 'brand_id'=>$p->brand_id, 'tax_id'=>$p->tax_id,
                        'tax_per'=>'', 'tax_type'=>$p->tax_type,
                        'price'=>$p->price, 'purchase_price'=>$p->purchase_price, 'sales_price'=>$p->sales_price, 'mrp'=>$p->mrp,
                        'discount_type'=>$p->discount_type, 'discount'=>$p->discount, 'alert_qty'=>$p->alert_qty
                    ];
                }
                $parent = $parents[$parent_sku];

                $child_name = !empty($importdata[0]) ? $importdata[0] : $parent['name'].'-'.$variant_name;
                $child_sku  = !empty($this->xss_html_filter($importdata[2])) ? $this->xss_html_filter($importdata[2]) : '';
                $child_hsn  = !empty($this->xss_html_filter($importdata[3])) ? $this->xss_html_filter($importdata[3]) : '';
                $child_bar  = !empty($this->xss_html_filter($importdata[14])) ? $this->xss_html_filter($importdata[14]) : '';

                $c_price = (!empty($importdata[8]) && is_numeric(string_to_number($importdata[8]))) ? string_to_number($importdata[8]) : $parent['price'];
                $c_sales = (!empty($importdata[12]) && is_numeric(string_to_number($importdata[12]))) ? string_to_number($importdata[12]) : $parent['sales_price'];
                $c_mrp   = (!empty($importdata[19]) && is_numeric(string_to_number($importdata[19]))) ? string_to_number($importdata[19]) : $parent['mrp'];
                $c_tax_type = !empty($this->xss_html_filter($importdata[11])) ? $this->xss_html_filter($importdata[11]) : $parent['tax_type'];
                $c_tax_per  = !empty($this->xss_html_filter($importdata[10])) ? $this->xss_html_filter($importdata[10]) : '';
                $c_tax_id = $parent['tax_id'];
                if(!empty($this->xss_html_filter($importdata[9]))){
                    $c_tax_id = $this->get_tax_id($this->xss_html_filter($importdata[9]), $c_tax_per, $store_id);
                }
                $c_purchase = ($c_tax_type=='Inclusive') ? $c_price : original_cost($c_price,$c_tax_per,$c_tax_type);
                $c_profit = ($c_sales-$c_price);
                $c_profit = ($c_price>0) ? ($c_profit/$c_price)*100 : $c_profit;

                $variant_id = $this->items_model->find_or_create_variant_by_attributes(array('Variant' => $variant_name), $child_sku, $store_id);
                if(empty($variant_id)){ $flag=false; continue; }

                $child = array(
                    'store_id'          =>  $store_id,
                    'count_id'          =>  get_count_id('db_items'),
                    'item_code'         =>  get_init_code('item'),
                    'item_name'         =>  $child_name,
                    'category_id'       =>  $parent['category_id'],
                    'sku'               =>  $child_sku,
                    'hsn'               =>  $child_hsn,
                    'unit_id'           =>  $parent['unit_id'],
                    'alert_qty'         =>  $parent['alert_qty'],
                    'brand_id'          =>  $parent['brand_id'],
                    'lot_number'        =>  !empty($this->xss_html_filter($importdata[7]))?$this->xss_html_filter($importdata[7]):'',
                    'price'             =>  $c_price,
                    'tax_id'            =>  $c_tax_id,
                    'purchase_price'    =>  $c_purchase,
                    'tax_type'          =>  $c_tax_type,
                    'sales_price'       =>  $c_sales,
                    'profit_margin'     =>  $c_profit,
                    'mrp'               =>  $c_mrp,
                    'stock'             =>  !empty($this->xss_html_filter($importdata[13]))?$this->xss_html_filter($importdata[13]):0,
                    'custom_barcode'    =>  $child_bar,
                    'seller_points'     =>  !empty($this->xss_html_filter($importdata[15]))?$this->xss_html_filter($importdata[15]):0,
                    'description'       =>  !empty($this->xss_html_filter($importdata[16]))?$this->xss_html_filter($importdata[16]):'',
                    'discount_type'     =>  !empty($this->xss_html_filter($importdata[17]))?$this->xss_html_filter($importdata[17]):$parent['discount_type'],
                    'discount'          =>  (!empty($importdata[18]) && $importdata[18]!=='') ? string_to_number($importdata[18]) : $parent['discount'],
                    'item_group'        =>  'Single',
                    'parent_id'         =>  $parent['id'],
                    'child_bit'         =>  1,
                    'variant_id'        =>  $variant_id,
                    'status'            =>  1,
                    'created_date'      =>  $CUR_DATE,
                    'created_time'      =>  $CUR_TIME,
                    'created_by'        =>  $CUR_USERNAME,
                    'system_ip'         =>  $SYSTEM_IP,
                    'system_name'       =>  $SYSTEM_NAME,
                );

                if(!$this->db->insert('db_items',$child)){
                    $flag=false;
                }
                $child_id = $this->db->insert_id();

                if(!empty($importdata[13]) && $importdata[13]>0){
                    $array_params = array(
                        'store_id'          =>  $store_id,
                        'item_id'           =>  $child_id,
                        'warehouse_id'      =>  $warehouse_id,
                        'adjustment_qty'    =>  $this->xss_html_filter($importdata[13])
                    );
                    $q2 = $this->items_model->add_opening_stock($array_params);
                    if(!$q2){ $flag=false; continue; }
                }
            }

            if(!$flag){
                $this->db->trans_rollback();
                echo 'failed';
            }else{
                $this->db->where('store_id', (int)$store_id)->where("expire_date LIKE '0000%'", null, false)->update('db_items', array('expire_date'=>null));
                $this->db->trans_commit();
                echo "success";
                $this->session->set_flashdata('success', 'Success!! Variant Products Imported Successfully!');
            }
            fclose($file);
        }
    }

    public function categories(){
        $this->permission_check('items_category_add');
        $data=$this->data;
        $data['page_title']=$this->lang->line('import_categories');
        $data['content'] = $this->load->view('import/import_categories', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function import_categories_csv() {
        $store_id=get_current_store_id();

        if($_FILES['import_file']['size'] > 0)
        {
            if(!is_dir(FCPATH.'uploads/csv/categories')){ mkdir(FCPATH.'uploads/csv/categories', 0755, true); }
            $config['upload_path']          = './uploads/csv/categories';
            $config['allowed_types']        = 'csv';
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('import_file')){
                    $error = array('error' => $this->upload->display_errors());
                    print($error['error']);
                    exit();
            }
            else{
                    $file_name=$this->upload->data('file_name');
            }

            $file = fopen('uploads/csv/categories/'.$file_name,"r");

            $flag=true;
            $this->db->trans_begin();
            $i=1;
            while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                if($i++==1){ continue; }

                //Category name should not be empty
                if(empty($importdata[0])){ continue; }

                $category_name = trim($this->xss_html_filter($importdata[0]));

                //Skip if category already exists for this store
                $this->db->where("upper(category_name)", strtoupper($category_name));
                $this->db->where('store_id', $store_id);
                if($this->db->get('db_category')->num_rows()>0){ continue; }

                $row = array(
                    'store_id'          =>  $store_id,
                    'count_id'          =>  get_count_id('db_category'),
                    'category_code'     =>  get_init_code('category'),
                    'category_name'     =>  $category_name,
                    'description'       =>  !empty($importdata[1])?$this->xss_html_filter($importdata[1]):'',
                    'status'            =>  1,
                );

                if(!$this->db->insert('db_category',$row)){
                    $flag=false;
                }
            }

            if(!$flag){
                $this->db->trans_rollback();
                echo 'failed';
            }else{
                $this->db->trans_commit();
                echo "success";
                $this->session->set_flashdata('success', 'Success!! Categories Imported Successfully!');
            }
            fclose($file);
        }
    }

    public function brands(){
        $this->permission_check('brand_add');
        $data=$this->data;
        $data['page_title']=$this->lang->line('import_brands');
        $data['content'] = $this->load->view('import/import_brands', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function import_brands_csv() {
        $store_id=get_current_store_id();

        if($_FILES['import_file']['size'] > 0)
        {
            if(!is_dir(FCPATH.'uploads/csv/brands')){ mkdir(FCPATH.'uploads/csv/brands', 0755, true); }
            $config['upload_path']          = './uploads/csv/brands';
            $config['allowed_types']        = 'csv';
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('import_file')){
                    $error = array('error' => $this->upload->display_errors());
                    print($error['error']);
                    exit();
            }
            else{
                    $file_name=$this->upload->data('file_name');
            }

            $file = fopen('uploads/csv/brands/'.$file_name,"r");

            $flag=true;
            $this->db->trans_begin();
            $i=1;
            while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                if($i++==1){ continue; }

                //Brand name should not be empty
                if(empty($importdata[0])){ continue; }

                $brand_name = trim($this->xss_html_filter($importdata[0]));

                //Skip if brand already exists for this store
                $this->db->where("upper(brand_name)", strtoupper($brand_name));
                $this->db->where('store_id', $store_id);
                if($this->db->get('db_brands')->num_rows()>0){ continue; }

                $row = array(
                    'store_id'          =>  $store_id,
                    'brand_name'        =>  $brand_name,
                    'description'       =>  !empty($importdata[1])?$this->xss_html_filter($importdata[1]):'',
                    'status'            =>  1,
                );

                if(!$this->db->insert('db_brands',$row)){
                    $flag=false;
                }
            }

            if(!$flag){
                $this->db->trans_rollback();
                echo 'failed';
            }else{
                $this->db->trans_commit();
                echo "success";
                $this->session->set_flashdata('success', 'Success!! Brands Imported Successfully!');
            }
            fclose($file);
        }
    }

    public function attributes(){
        $this->permission_check('attributes_add');
        $data=$this->data;
        $data['page_title']=$this->lang->line('import_attributes');
        $data['content'] = $this->load->view('import/import_attributes', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function import_attributes_csv() {
        $store_id=get_current_store_id();

        if($_FILES['import_file']['size'] > 0)
        {
            if(!is_dir(FCPATH.'uploads/csv/attributes')){ mkdir(FCPATH.'uploads/csv/attributes', 0755, true); }
            $config['upload_path']          = './uploads/csv/attributes';
            $config['allowed_types']        = 'csv';
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('import_file')){
                    $error = array('error' => $this->upload->display_errors());
                    print($error['error']);
                    exit();
            }
            else{
                    $file_name=$this->upload->data('file_name');
            }

            $file = fopen('uploads/csv/attributes/'.$file_name,"r");

            $flag=true;
            $this->db->trans_begin();
            $i=1;
            while(($importdata = fgetcsv($file, NULL, ",")) !== FALSE){
                if($i++==1){ continue; }

                //Attribute type and value should not be empty
                if(empty($importdata[0]) || empty($importdata[1])){ continue; }

                $attribute_type  = strtolower(trim($this->xss_html_filter($importdata[0])));
                $attribute_value = trim($this->xss_html_filter($importdata[1]));
                $sort_order      = (isset($importdata[2]) && is_numeric(trim($importdata[2]))) ? (int)trim($importdata[2]) : 0;

                //Skip if this type/value already exists for this store (matches db unique key)
                $this->db->where('store_id', $store_id);
                $this->db->where('attribute_type', $attribute_type);
                $this->db->where('attribute_value', $attribute_value);
                if($this->db->get('db_attributes')->num_rows()>0){ continue; }

                $row = array(
                    'store_id'          =>  $store_id,
                    'attribute_type'    =>  $attribute_type,
                    'attribute_value'   =>  $attribute_value,
                    'sort_order'        =>  $sort_order,
                    'status'            =>  1,
                    'created_date'      =>  $CUR_DATE,
                    'created_time'      =>  $CUR_TIME,
                    'created_by'        =>  $CUR_USERNAME,
                );

                if(!$this->db->insert('db_attributes',$row)){
                    $flag=false;
                }
            }

            if(!$flag){
                $this->db->trans_rollback();
                echo 'failed';
            }else{
                $this->db->trans_commit();
                echo "success";
                $this->session->set_flashdata('success', 'Success!! Attributes Imported Successfully!');
            }
            fclose($file);
        }
    }

    public function download_file($fileName){
        $fileLoc = FCPATH.'uploads/csv/examples/'.$fileName;
        if(!file_exists($fileLoc)){
            echo "The file $fileName does not exist";
            exit();
        }
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        readfile($fileLoc);
        exit();
    }
        public function download($fileOf=''){
            $fileName='';
            if($fileOf=='items'){
                $fileName = 'import-items-example.csv';
            }
            else if($fileOf=='customers'){
                $fileName = 'import-customers-example.csv';
            }
            else if($fileOf=='suppliers'){
                $fileName = 'import-suppliers-example.csv';
            }
            else if($fileOf=='services'){
                $fileName = 'import-services-example.csv';
            }
            else if($fileOf=='items-variants'){
                $fileName = 'import-items-variants-example.csv';
            }
            else if($fileOf=='items-advanced'){
                $fileName = 'import-items-advanced-example.csv';
            }
            else if($fileOf=='categories'){
                $fileName = 'import-categories-example.csv';
            }
            else if($fileOf=='brands'){
                $fileName = 'import-brands-example.csv';
            }
            else if($fileOf=='attributes'){
                $fileName = 'import-attributes-example.csv';
            }
            else{
                echo 'Something went wrong!!';exit();
            }

            $this->download_file($fileName);
        }

    public function advanced(){
        $this->permission_check('import_items');
        $data=$this->data;
        $data['page_title']='Advanced Product Import';
        $data['content']=$this->load->view('import/import_advanced', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function import_advanced_items_csv(){
        $warehouse_id = (int)$this->input->post('warehouse_id');
        $filename = $_FILES["import_file"]["name"];
        $this->load->model('pos_model');
        $this->load->model('items_model');

        $store_id = get_current_store_id();

        if($_FILES['import_file']['size'] > 0)
        {
            $config['upload_path'] = './uploads/csv/items';
            $config['allowed_types'] = 'csv';
            $this->load->library('upload', $config);

            if( ! $this->upload->do_upload('import_file')){
                $error = array('error' => $this->upload->display_errors());
                print($error['error']);
                exit();
            } else {
                $file_name = $this->upload->data('file_name');
            }

            $file = fopen('uploads/csv/items/'.$file_name, "r");

            $flag = true;
            $this->db->trans_begin();

            $header_map = [];
            $use_header = false;
            $rows = [];
            $i = 1;
            while(($csvdata = fgetcsv($file, NULL, ",")) !== FALSE){
                if($i == 1){
                    $headers = array_map(function($h){
                        return strtolower(trim(preg_replace('/\s+/', '_', trim($h))));
                    }, $csvdata);
                    if(in_array('item_name', $headers) || in_array('item_group', $headers) || in_array('parent_sku', $headers)){
                        $use_header = true;
                        $header_map = $headers;
                        $i++;
                        continue;
                    } else {
                        $header_map = ['item_name','category_name','sku','hsn','unit_name','alert_qty','brand_name','lot_number','price_before_tax','tax_name','tax_value','tax_type','sales_price','opening_stock','custom_barcode','seller_points','description','discount_type','discount','mrp','item_group','parent_sku','variant_name'];
                    }
                }
                $i++;

                $d = [];
                foreach($header_map as $idx => $col){
                    $d[$col] = isset($csvdata[$idx]) ? $csvdata[$idx] : '';
                }

                if($use_header){
                    for($idx = count($header_map); $idx < count($csvdata); $idx++){
                        if(!empty($headers[$idx])){
                            $d[$headers[$idx]] = $csvdata[$idx];
                        }
                    }
                } else {
                    for($idx = count($header_map); $idx < count($csvdata); $idx++){
                        $d['extra_'.$idx] = $csvdata[$idx];
                    }
                }

                if(empty($d['item_name']) && empty($d['parent_sku']) && empty($d['variant_name']) && empty($d['item_group'])){
                    continue;
                }
                $rows[] = $d;
            }

            $parents = [];
            foreach($rows as $d){
                $item_group = !empty($this->xss_html_filter($d['item_group'])) ? trim($this->xss_html_filter($d['item_group'])) : 'Single';
                if($item_group == 'Variant' || $item_group == 'Child' || (!empty($d['parent_sku']) && !empty($d['variant_name']))){
                    continue;
                }
                if($item_group != 'Variants' && $item_group != 'Single'){
                    $item_group = 'Single';
                }

                $category_name = $this->xss_html_filter($d['category_name']);
                $unit_name     = $this->xss_html_filter($d['unit_name']);
                $brand_name    = $this->xss_html_filter($d['brand_name']);
                $tax_name      = $this->xss_html_filter($d['tax_name']);
                $tax_per       = $this->xss_html_filter($d['tax_value']);
                $category_id   = (!empty($category_name)) ? $this->get_category_id($category_name, $store_id) : null;
                $unit_id       = (!empty($unit_name)) ? $this->get_unit_id($unit_name, $store_id) : null;
                $brand_id      = (!empty($brand_name)) ? $this->get_brand_id($brand_name, $store_id) : null;
                $tax_id        = (!empty($tax_name)) ? $this->get_tax_id($tax_name, $tax_per, $store_id) : null;

                $sales_price = !empty($this->xss_html_filter($d['sales_price'])) ? $this->xss_html_filter(string_to_number($d['sales_price'])) : 0;
                $mrp         = !empty($this->xss_html_filter($d['mrp'])) ? $this->xss_html_filter(string_to_number($d['mrp'])) : 0;
                $price       = !empty($this->xss_html_filter($d['price_before_tax'])) ? $this->xss_html_filter(string_to_number($d['price_before_tax'])) : 0;
                $profit_margin = ($sales_price - $price);
                $profit_margin = ($price > 0) ? ($profit_margin / $price) * 100 : $profit_margin;

                $tax_type = !empty($this->xss_html_filter($d['tax_type'])) ? $this->xss_html_filter($d['tax_type']) : 'Exclusive';
                $purchase_price = ($tax_type == 'Inclusive') ? $price : original_cost($price, $tax_per, $tax_type);

                $sku = !empty($this->xss_html_filter($d['sku'])) ? $this->xss_html_filter($d['sku']) : '';

                $row = array(
                    'store_id'          => $store_id,
                    'count_id'          => get_count_id('db_items'),
                    'item_code'         => get_init_code('item'),
                    'item_name'         => $d['item_name'],
                    'category_id'       => $category_id,
                    'sku'               => $sku,
                    'hsn'               => !empty($this->xss_html_filter($d['hsn'])) ? $this->xss_html_filter($d['hsn']) : '',
                    'unit_id'           => $unit_id,
                    'alert_qty'         => !empty($this->xss_html_filter($d['alert_qty'])) ? $this->xss_html_filter($d['alert_qty']) : 0,
                    'brand_id'          => $brand_id,
                    'lot_number'        => !empty($this->xss_html_filter($d['lot_number'])) ? $this->xss_html_filter($d['lot_number']) : '',
                    'price'             => $price,
                    'tax_id'            => $tax_id,
                    'purchase_price'    => $purchase_price,
                    'tax_type'          => $tax_type,
                    'sales_price'       => $sales_price,
                    'profit_margin'     => $profit_margin,
                    'mrp'               => $mrp,
                    'stock'             => !empty($this->xss_html_filter($d['opening_stock'])) ? $this->xss_html_filter($d['opening_stock']) : 0,
                    'custom_barcode'    => !empty($this->xss_html_filter($d['custom_barcode'])) ? $this->xss_html_filter($d['custom_barcode']) : '',
                    'seller_points'     => !empty($this->xss_html_filter($d['seller_points'])) ? $this->xss_html_filter($d['seller_points']) : 0,
                    'description'       => !empty($this->xss_html_filter($d['description'])) ? $this->xss_html_filter($d['description']) : '',
                    'discount_type'     => !empty($this->xss_html_filter($d['discount_type'])) ? $this->xss_html_filter($d['discount_type']) : '',
                    'discount'          => !empty($this->xss_html_filter($d['discount'])) ? $this->xss_html_filter(string_to_number($d['discount'])) : 0,
                    'item_group'        => $item_group,
                    'child_bit'         => 0,
                    'publish_online'    => 0,
                    'status'            => 1,
                    'created_date'      => $CUR_DATE,
                    'created_time'      => $CUR_TIME,
                    'created_by'        => $CUR_USERNAME,
                    'system_ip'         => $SYSTEM_IP,
                    'system_name'       => $SYSTEM_NAME,
                );

                if( ! $this->db->insert('db_items', $row)){
                    $flag = false;
                }
                $item_id = $this->db->insert_id();

                $selling_units = $this->extract_selling_units($d);
                if(!empty($selling_units)){
                    $this->save_selling_units($item_id, $store_id, $selling_units, ($item_group == 'Variants' ? 1 : 0));
                }

                $barcodes = $this->extract_barcodes($d);
                if(!empty($barcodes)){
                    $this->save_item_barcodes($item_id, $store_id, $warehouse_id, $barcodes);
                }

                if(!empty($sku)){
                    $parents[$sku] = [
                        'id' => $item_id, 'name' => $d['item_name'],
                        'category_id' => $category_id, 'unit_id' => $unit_id, 'brand_id' => $brand_id, 'tax_id' => $tax_id,
                        'tax_per' => $tax_per, 'tax_type' => $tax_type,
                        'price' => $price, 'purchase_price' => $purchase_price, 'sales_price' => $sales_price, 'mrp' => $mrp,
                        'discount_type' => $row['discount_type'], 'discount' => $row['discount'], 'alert_qty' => $row['alert_qty']
                    ];
                }

                if(!empty($d['opening_stock']) && $d['opening_stock'] > 0 && $item_group == 'Single'){
                    $array_params = array(
                        'store_id'       => $store_id,
                        'item_id'        => $item_id,
                        'warehouse_id'   => $warehouse_id,
                        'adjustment_qty' => $this->xss_html_filter($d['opening_stock'])
                    );
                    $q2 = $this->items_model->add_opening_stock($array_params);
                    if(!$q2){ $flag=false; continue; }
                }
            }

            foreach($rows as $d){
                $item_group = !empty($this->xss_html_filter($d['item_group'])) ? trim($this->xss_html_filter($d['item_group'])) : 'Single';
                if(($item_group != 'Variant' && $item_group != 'Child') && (empty($d['parent_sku']) || empty($d['variant_name']))){
                    continue;
                }
                $parent_sku   = !empty($this->xss_html_filter($d['parent_sku'])) ? $this->xss_html_filter($d['parent_sku']) : '';
                $variant_name = $this->xss_html_filter($d['variant_name']);
                if(empty($parent_sku) || empty($variant_name)){ continue; }

                if( ! isset($parents[$parent_sku])){
                    $pq = $this->db->select('id,item_name,category_id,unit_id,brand_id,tax_id,tax_type,price,purchase_price,sales_price,mrp,discount_type,discount,alert_qty')->where('sku', $parent_sku)->where('store_id', $store_id)->where('child_bit', 0)->get('db_items');
                    if($pq->num_rows() == 0){ $flag = false; continue; }
                    $p = $pq->row();
                    $parents[$parent_sku] = [
                        'id' => $p->id, 'name' => $p->item_name,
                        'category_id' => $p->category_id, 'unit_id' => $p->unit_id, 'brand_id' => $p->brand_id, 'tax_id' => $p->tax_id,
                        'tax_per' => '', 'tax_type' => $p->tax_type,
                        'price' => $p->price, 'purchase_price' => $p->purchase_price, 'sales_price' => $p->sales_price, 'mrp' => $p->mrp,
                        'discount_type' => $p->discount_type, 'discount' => $p->discount, 'alert_qty' => $p->alert_qty
                    ];
                }
                $parent = $parents[$parent_sku];

                $child_name = !empty($d['item_name']) ? $d['item_name'] : $parent['name'] . '-' . $variant_name;
                $child_sku  = !empty($this->xss_html_filter($d['sku'])) ? $this->xss_html_filter($d['sku']) : '';
                $child_hsn  = !empty($this->xss_html_filter($d['hsn'])) ? $this->xss_html_filter($d['hsn']) : '';
                $child_bar  = !empty($this->xss_html_filter($d['custom_barcode'])) ? $this->xss_html_filter($d['custom_barcode']) : '';

                $c_price = (!empty($d['price_before_tax']) && is_numeric(string_to_number($d['price_before_tax']))) ? string_to_number($d['price_before_tax']) : $parent['price'];
                $c_sales = (!empty($d['sales_price']) && is_numeric(string_to_number($d['sales_price']))) ? string_to_number($d['sales_price']) : $parent['sales_price'];
                $c_mrp   = (!empty($d['mrp']) && is_numeric(string_to_number($d['mrp']))) ? string_to_number($d['mrp']) : $parent['mrp'];
                $c_tax_type = !empty($this->xss_html_filter($d['tax_type'])) ? $this->xss_html_filter($d['tax_type']) : $parent['tax_type'];
                $c_tax_per  = !empty($this->xss_html_filter($d['tax_value'])) ? $this->xss_html_filter($d['tax_value']) : '';
                $c_tax_id = $parent['tax_id'];
                if(!empty($this->xss_html_filter($d['tax_name']))){
                    $c_tax_id = $this->get_tax_id($this->xss_html_filter($d['tax_name']), $c_tax_per, $store_id);
                }
                $c_purchase = ($c_tax_type == 'Inclusive') ? $c_price : original_cost($c_price, $c_tax_per, $c_tax_type);
                $c_profit = ($c_sales - $c_price);
                $c_profit = ($c_price > 0) ? ($c_profit / $c_price) * 100 : $c_profit;

                $variant_id = $this->items_model->find_or_create_variant_by_attributes(array('Variant' => $variant_name), $child_sku, $store_id);
                if(empty($variant_id)){ $flag = false; continue; }

                $child = array(
                    'store_id'          => $store_id,
                    'count_id'          => get_count_id('db_items'),
                    'item_code'         => get_init_code('item'),
                    'item_name'         => $child_name,
                    'category_id'       => $parent['category_id'],
                    'sku'               => $child_sku,
                    'hsn'               => $child_hsn,
                    'unit_id'           => $parent['unit_id'],
                    'alert_qty'         => $parent['alert_qty'],
                    'brand_id'          => $parent['brand_id'],
                    'lot_number'        => !empty($this->xss_html_filter($d['lot_number'])) ? $this->xss_html_filter($d['lot_number']) : '',
                    'price'             => $c_price,
                    'tax_id'            => $c_tax_id,
                    'purchase_price'    => $c_purchase,
                    'tax_type'          => $c_tax_type,
                    'sales_price'       => $c_sales,
                    'profit_margin'     => $c_profit,
                    'mrp'               => $c_mrp,
                    'stock'             => !empty($this->xss_html_filter($d['opening_stock'])) ? $this->xss_html_filter($d['opening_stock']) : 0,
                    'custom_barcode'    => $child_bar,
                    'seller_points'     => !empty($this->xss_html_filter($d['seller_points'])) ? $this->xss_html_filter($d['seller_points']) : 0,
                    'description'       => !empty($this->xss_html_filter($d['description'])) ? $this->xss_html_filter($d['description']) : '',
                    'discount_type'     => !empty($this->xss_html_filter($d['discount_type'])) ? $this->xss_html_filter($d['discount_type']) : $parent['discount_type'],
                    'discount'          => (!empty($d['discount']) && $d['discount'] !== '') ? string_to_number($d['discount']) : $parent['discount'],
                    'item_group'        => 'Single',
                    'parent_id'         => $parent['id'],
                    'child_bit'         => 1,
                    'variant_id'        => $variant_id,
                    'status'            => 1,
                    'created_date'      => $CUR_DATE,
                    'created_time'      => $CUR_TIME,
                    'created_by'        => $CUR_USERNAME,
                    'system_ip'         => $SYSTEM_IP,
                    'system_name'       => $SYSTEM_NAME,
                );

                if( ! $this->db->insert('db_items', $child)){
                    $flag = false;
                }
                $child_id = $this->db->insert_id();

                $child_selling_units = $this->extract_selling_units($d);
                if(!empty($child_selling_units)){
                    $this->save_selling_units($child_id, $store_id, $child_selling_units, 0);
                } elseif(isset($parents[$parent_sku]) && $this->db->table_exists('db_item_selling_units')){
                    if( ! isset($this->selling_units)){
                        $this->load->model('item_selling_units_model','selling_units');
                    }
                    $this->selling_units->clone_template_to_child($parents[$parent_sku]['id'], $child_id, $store_id);
                }

                $child_barcodes = $this->extract_barcodes($d);
                if(!empty($child_barcodes)){
                    $this->save_item_barcodes($child_id, $store_id, $warehouse_id, $child_barcodes);
                }

                if(!empty($d['opening_stock']) && $d['opening_stock'] > 0){
                    $array_params = array(
                        'store_id'       => $store_id,
                        'item_id'        => $child_id,
                        'warehouse_id'   => $warehouse_id,
                        'adjustment_qty' => $this->xss_html_filter($d['opening_stock'])
                    );
                    $q2 = $this->items_model->add_opening_stock($array_params);
                    if(!$q2){ $flag=false; continue; }
                }
            }

            if( ! $flag){
                $this->db->trans_rollback();
                echo 'failed';
            } else {
                $this->db->where('store_id', (int)$store_id)->where("expire_date LIKE '0000%'", null, false)->update('db_items', array('expire_date'=>null));
                $this->db->trans_commit();
                echo "success";
                $this->session->set_flashdata('success', 'Success!! Advanced Products Imported Successfully!');
            }
            fclose($file);
        }
    }

    private function extract_selling_units($d){
        $units = [];
        foreach($d as $col => $val){
            if(preg_match('/^selling_unit_(\d+)_(.+)$/', $col, $m)){
                $idx = (int)$m[1];
                $field = $m[2];
                if( ! isset($units[$idx])) $units[$idx] = [];
                $units[$idx][$field] = $val;
            }
        }
        return $units;
    }

    private function save_selling_units($item_id, $store_id, $units, $is_template = 0){
        if( ! $this->db->table_exists('db_item_selling_units')) return true;
        if(empty($units)) return true;

        $default_index = null;
        foreach($units as $idx => $u){
            if( ! empty($u['default']) && (strtolower($u['default']) == '1' || strtolower($u['default']) == 'yes')){
                if($default_index === null) $default_index = $idx;
            }
        }

        $this->db->where('item_id', $item_id)->where('store_id', $store_id);
        if($this->db->field_exists('is_template', 'db_item_selling_units')){
            $this->db->where('is_template', $is_template);
        }
        $this->db->update('db_item_selling_units', ['status' => 0]);

        $default_set = false;
        foreach($units as $idx => $u){
            if(empty($u['unit'])) continue;

            $unit_name = $this->xss_html_filter($u['unit']);
            $unit_id = $this->get_unit_id($unit_name, $store_id);
            $unit = $this->db->select('unit_name, shortcode')->where('id', $unit_id)->get('db_units')->row();
            $shortcode = ($unit && !empty($unit->shortcode)) ? $unit->shortcode : ($unit ? $unit->unit_name : $unit_name);

            $conversion = !empty($u['conversion']) ? (float)string_to_number($u['conversion']) : 1;
            if($conversion <= 0) $conversion = 1;

            $selling_price = !empty($u['selling_price']) ? (float)string_to_number($u['selling_price']) : 0;
            $wholesale_price = (!empty($u['wholesale_price']) && $u['wholesale_price'] !== '') ? (float)string_to_number($u['wholesale_price']) : null;
            $purchase_price = (!empty($u['purchase_price']) && $u['purchase_price'] !== '') ? (float)string_to_number($u['purchase_price']) : null;
            $sku = isset($u['sku']) ? $this->xss_html_filter($u['sku']) : '';
            $barcode = isset($u['barcode']) ? $this->xss_html_filter($u['barcode']) : '';

            $is_default = 0;
            if($default_index !== null && $idx == $default_index){
                $is_default = 1;
            } elseif($default_index === null && ! $default_set){
                $is_default = 1;
                $default_set = true;
            }

            $data = array(
                'store_id'          => $store_id,
                'item_id'           => $item_id,
                'unit_id'           => $unit_id,
                'unit_shortcode'    => $shortcode,
                'conversion_factor' => $conversion,
                'selling_price'     => $selling_price,
                'wholesale_price'   => $wholesale_price,
                'purchase_price'    => $purchase_price,
                'sku'               => $sku,
                'barcode'           => $barcode,
                'is_default'        => $is_default,
                'status'            => 1,
            );
            if($this->db->field_exists('is_template', 'db_item_selling_units')){
                $data['is_template'] = $is_template;
            }

            $this->db->insert('db_item_selling_units', $data);
        }
        return true;
    }

    private function extract_barcodes($d){
        $barcodes = [];
        foreach($d as $col => $val){
            if(preg_match('/^barcode_(\d+)_(.+)$/', $col, $m)){
                $idx = (int)$m[1];
                $field = $m[2];
                if( ! isset($barcodes[$idx])) $barcodes[$idx] = [];
                $barcodes[$idx][$field] = $val;
            }
        }
        return $barcodes;
    }

    private function save_item_barcodes($item_id, $store_id, $warehouse_id, $barcodes){
        if( ! $this->db->table_exists('db_item_barcodes')) return true;
        if(empty($barcodes)) return true;

        foreach($barcodes as $b){
            $bc_barcode = isset($b['barcode']) ? trim($this->xss_html_filter($b['barcode'])) : '';
            $bc_batch   = isset($b['batch']) ? trim($this->xss_html_filter($b['batch'])) : '';
            $bc_serial  = isset($b['serial']) ? trim($this->xss_html_filter($b['serial'])) : '';
            $bc_imei    = isset($b['imei']) ? trim($this->xss_html_filter($b['imei'])) : '';
            if($bc_barcode === '' && $bc_batch === '' && $bc_serial === '' && $bc_imei === '') continue;

            $data = array(
                'item_id'        => $item_id,
                'barcode'        => $bc_barcode,
                'batch_lot'      => $bc_batch,
                'serial_number'  => $bc_serial,
                'imei_number'    => $bc_imei,
                'purchase_price' => !empty($b['purchase_price']) ? (float)string_to_number($b['purchase_price']) : 0,
                'sales_price'    => !empty($b['sales_price']) ? (float)string_to_number($b['sales_price']) : 0,
                'mrp'            => !empty($b['mrp']) ? (float)string_to_number($b['mrp']) : 0,
                'qty'            => !empty($b['qty']) ? (float)string_to_number($b['qty']) : 0,
                'warehouse_id'   => $warehouse_id,
                'status'         => 1,
                'created_date'   => date('Y-m-d'),
                'created_time'   => date('H:i:s'),
            );
            if( ! empty($b['expire_date'])) $data['expire_date'] = $this->xss_html_filter($b['expire_date']);
            if( ! empty($b['mfg_date']))    $data['mfg_date']    = $this->xss_html_filter($b['mfg_date']);
            if( ! empty($b['warranty']))    $data['warranty_months'] = (int)$b['warranty'];

            $this->db->insert('db_item_barcodes', $data);
        }
        return true;
    }
}


