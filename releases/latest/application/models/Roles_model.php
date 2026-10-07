<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles_model extends CI_Model {

	var $table = 'db_roles';
	var $column_order = array('role_name','description','status','store_id'); //set column field database for datatable orderable
	var $column_search = array('role_name','description','status','store_id'); //set column field database for datatable searchable 
	var $order = array('id' => 'desc'); // default order 

	private function _get_datatables_query()
	{
		
		$this->db->from($this->table);
		// Hide Super Admin (role id 1) from role management
		$this->db->where("id !=", 1);
		$this->db->where("store_id",$this->input->post('store_id'));

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if(isset($_POST['length']) && $_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->where("store_id",get_current_store_id());
		$this->db->where("id !=", 1);
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}


	public function verify_and_save(){
		$role_name = $this->input->post('role_name', TRUE);
		$description = $this->input->post('description', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];
		$SYSTEM_IP = $this->data['SYSTEM_IP'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'];
		
		// Determine store_id: admin can specify via POST, otherwise use current store
		$store_id = (store_module() && is_admin()) ? $this->input->post('store_id', TRUE) : get_current_store_id();
		if(empty($store_id)){
			$store_id = get_current_store_id();
		}
		if(strtoupper(trim($role_name))==strtoupper('Admin')){
			echo "You Couldn't Create Admin Role!!";exit();
		}
		$this->db->where("upper(role_name)", strtoupper($role_name));
		$this->db->where('store_id', $store_id);
		$query = $this->db->get('db_roles');
		if($query->num_rows()>0){
			return "This Role Name Name already Exist.";
			
		}
		else{

			$this->db->trans_begin();

			$info = array(
		    				'role_name' 				=> $role_name, 
		    				'description' 				=> $description,
		    				'status' 				=> 1,
		    			);
			
			$info['store_id'] = $store_id;	

			$q1 = $this->db->insert('db_roles', $info);
			if ($q1){
				if(!$this->set_persmissions($this->db->insert_id(),$info['store_id'])){
						$this->db->trans_rollback();
						return "Please Select Permissions";
					}
					$this->db->trans_commit();
					if(function_exists('mp_audit_log')){
						mp_audit_log('roles', 'create', $role_name, 'Created role '.$role_name);
					}
					$this->session->set_flashdata('success', 'Success!! New Role Name Added Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	//Get role_name_details
	public function get_details($id,$data){
		//Validate This role_name already exist or not
		$query=$this->db->query("select * from db_roles where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['role_name']=$query->role_name;
			$data['description']=$query->description;
			$data['store_id']=$query->store_id;
			return $data;
		}
	}
	public function update_role(){
		$q_id = $this->input->post('q_id', TRUE);
		$role_name = $this->input->post('role_name', TRUE);
		$description = $this->input->post('description', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];
		$SYSTEM_IP = $this->data['SYSTEM_IP'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'];

		// Determine store_id: admin can specify via POST, otherwise use current store
		$store_id = (store_module() && is_admin()) ? $this->input->post('store_id', TRUE) : get_current_store_id();
		if(empty($store_id)){
			$store_id = get_current_store_id();
		}
		if(strtoupper(trim($role_name))==strtoupper('Admin')){
			echo "You Couldn't Create Admin Role!!";exit();
		}
		$this->db->where("upper(role_name)", strtoupper($role_name));
		$this->db->where("id !=", $q_id);
		$this->db->where('store_id', $store_id);
		$query = $this->db->get('db_roles');
		if($query->num_rows()>0){
			return "This Role Name Name already Exist.";
			
		}
		else{
			$this->db->trans_begin();

			$info = array(
		    				'role_name' 				=> $role_name, 
		    				'description' 				=> $description,
		    			);
			
			$info['store_id'] = $store_id;

			$q1 = $this->db->where('id',$q_id)->update('db_roles', $info);
		
			if ($q1){
					if(!$this->set_persmissions($q_id,$info['store_id'])){
						$this->db->trans_rollback();
						return "Please Select Permissions";
					}

					$this->db->trans_commit();
					if(function_exists('mp_audit_log')){
						mp_audit_log('roles', 'update', $q_id, 'Updated role '.$role_name);
					}
					$this->session->set_flashdata('success', 'Success!! Role Updated Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	public function update_status($id,$status){
		if($id==1){
			echo "Restricted! Can't Update this User Status!";exit();
		}
       if (set_status_of_table($id,$status,'db_roles')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_roles_from_table($ids){
			if($ids==1){
				echo "Restricted! Can't Delete this User!";exit();
			}

			$this->db->trans_begin();
			#----------------------------------Delete Role
			$this->db->where("id in ($ids)");
			//if not admin
			if(!is_admin()){
				$this->db->where("store_id",get_current_store_id());
			}

			$query1=$this->db->delete("db_roles");
			#----------------------------------
			#----------------------------------Delete permissions
			$this->db->where("role_id in ($ids)");
			//if not admin
			if(!is_admin()){
				$this->db->where("store_id",get_current_store_id());
			}

			$query2=$this->db->delete("db_permissions");
			#----------------------------------

	        if ($query1 && $query2){
	        	$this->db->trans_commit();
	        	if(function_exists('mp_audit_log')){
	        		mp_audit_log('roles', 'delete', $ids, 'Deleted role(s) '.$ids);
	        	}
	            echo "success";
	        }
	        else{
	            echo "failed";
	        }
	        exit();
	}

	function get_selected($role_id= 0,$store_id=null,$permissions_array=null){
		$info=array();
		foreach ($permissions_array as $key => $value) {
			if(isset($_POST['permission'][$value])) {
				 array_push ($info,array('permissions'  =>  $value,'role_id'   =>  $role_id, 'store_id'=>$store_id));
			}
		}
		return $info;
	}

	public function set_persmissions($role_id= 0,$store_id=null){
		//echo "<pre>"; print_r($this->security->xss_clean(html_escape(array_merge($_POST))));exit;
		$result =array();
		//PERMISSIONS KEY FROM FRONT END
		$result= ($this->get_selected($role_id,$store_id,array(
														'users_add',
														'users_edit',
														'users_delete',
														'users_view',
														'tax_add',
														'tax_edit',
														'tax_delete',
														'tax_view',
														/*'currency_add',
														'currency_edit',
														'currency_delete',
														'currency_view',*/
														'store_edit', 'business_setup',
														/*'site_edit',*/
														'units_add',
														'units_edit',
														'units_delete',
														'units_view',
														'roles_add',
							                            'roles_edit',
							                            'roles_delete',
							                            'roles_view',
							                            'places_add',
							                            'places_edit',
							                            'places_delete',
							                            'places_view',
							                            'expense_add',
							                            'expense_edit',
							                            'expense_delete',
							                            'expense_view',
							                            'items_add',
							                            'items_edit',
							                            'items_delete',
							                            'items_view',
							                            'import_items',
							                            'brand_add',
														'brand_edit',
														'brand_delete',
														'brand_view',
							                            'suppliers_add',
							                            'suppliers_edit',
							                            'suppliers_delete',
							                            'suppliers_view',
							                            'customers_add',
							                            'customers_edit',
							                            'customers_delete',
							                            'customers_view',
							                            'purchase_add',
							                            'purchase_edit',
							                            'purchase_delete',
							                            'purchase_view',
							                            'sales_add',
							                            'sales_edit',
							                            'sales_delete',
							                            'sales_view',
							                            'sales_payment_view',
							                            'sales_payment_add',
							                            'sales_payment_delete',
							                            'sales_report',
							                            'purchase_report',
							                            'expense_report',
							                            'profit_report',
							                            'stock_report',
							                            'item_sales_report',
							                            'purchase_payments_report',
							                            'sales_payments_report',
							                            'expired_items_report',
							                            'items_category_add',
							                            'items_category_edit',
							                            'items_category_delete',
							                            'items_category_view',
							                            'print_labels',
							                            'expense_category_add',
				                                        'expense_category_edit',
				                                        'expense_category_delete',
				                                        'expense_category_view',
				                                        'dashboard_view',
				                                        'dashboard_info_box_1',
				                                        'dashboard_info_box_2',
				                                        'dashboard_pur_sal_chart',
				                                        'dashboard_recent_items',
				                                        'dashboard_expired_items',
				                                        'dashboard_stock_alert',
				                                        'dashboard_trending_items_chart',
				                                        'send_sms',
				                                        'sms_template_edit',
				                                        'sms_template_view',
				                                        'sms_api_view',
				                                        'sms_api_edit',
				                                        'purchase_return_add',
				                                        'purchase_return_edit',
				                                        'purchase_return_delete',
				                                        'purchase_return_view',
				                                        'purchase_return_report',
				                                        'sales_return_add',
				                                        'sales_return_edit',
				                                        'sales_return_delete',
				                                        'sales_return_view',
				                                        'sales_return_report',
				                                        'sales_return_payment_view',
							                            'sales_return_payment_add',
							                            'sales_return_payment_delete',
							                            'purchase_return_payment_view',
							                            'purchase_return_payment_add',
							                            'purchase_return_payment_delete',
							                            'purchase_payment_view',
							                            'purchase_payment_add',
							                            'purchase_payment_delete',
							                            'payment_types_add',
							                            'payment_types_edit',
							                            'payment_types_delete',
							                            'payment_types_view',
							                            'payment_modes_add',
							                            'payment_modes_edit',
							                            'payment_modes_delete',
							                            'payment_modes_view',
							                            'paystack_settings',
							                            'monnify_settings',
							                            'monnify_transfers',
																					'expiry_settings',
							                            'import_customers',
							                            'import_suppliers',
							                            'stock_transfer_add',
			                                         	'stock_transfer_edit',
			                                          	'stock_transfer_delete',
			                                          	'stock_transfer_view',
			                                          	'warehouse_add',
			                                         	'warehouse_edit',
			                                          	'warehouse_delete',
			                                          	'warehouse_view',
			                                          	'supplier_items_report',
			                                          	'seller_points_report',
			                                          	'services_add',
				                                        'services_edit',
				                                        'services_delete',
				                                        'services_view',
												'service_packages_add',
												'service_packages_edit',
												'service_packages_delete',
												'service_packages_view',
												'memberships_add',
												'memberships_edit',
												'memberships_delete',
												'memberships_view',
												'treatment_notes_add',
												'treatment_notes_edit',
												'treatment_notes_delete',
												'treatment_notes_view',
																		'custom_orders_add',
																		'custom_orders_edit',
																		'custom_orders_delete',
																		'custom_orders_view',
																		'production_batches_add',
																		'production_batches_edit',
																		'production_batches_delete',
																		'production_batches_view',
																		'nylon_view',
																		'nylon_jobs_add',
																		'nylon_jobs_edit',
																		'nylon_jobs_delete',
																		'nylon_report',
																		'nylon_approve',
																		'nylon_artwork',
																		'nylon_costing',
																		'nylon_settings',
																		'recipes_add',
																		'recipes_edit',
																		'recipes_delete',
																		'recipes_view',
			                                          	'quotation_add',
							                            'quotation_edit',
							                            'quotation_delete',
							                            'quotation_view',
							                            'import_services',
							                            'stock_adjustment_add',
		                                                'stock_adjustment_edit',
		                                                'stock_adjustment_delete',
		                                                'stock_adjustment_view',
		                                                'variant_add',
														'variant_edit',
														'variant_delete',
														'variant_view',
														'accounts_add',
				                                          'accounts_edit',
				                                          'accounts_delete',
				                                          'accounts_view',
				                                          'money_transfer_add',
				                                          'money_transfer_edit',
				                                          'money_transfer_delete',
				                                          'money_transfer_view',
				                                          'money_deposit_add',
				                                          'money_deposit_edit',
				                                          'money_deposit_delete',
				                                          'money_deposit_view',
				                                          'sales_tax_report',
				                                          'purchase_tax_report',
				                                          'cash_transactions',
				                                        'show_all_users_sales_invoices',
														'show_all_users_sales_return_invoices',
														'show_all_users_purchase_invoices',
														'show_all_users_purchase_return_invoices',
														'show_all_users_expenses',
														'show_all_users_quotations',
														'subscription',
														'smtp_settings',
														'send_email',
														'sms_settings',
														'email_template_edit',
														'email_template_view',
														'cust_adv_payments_add',
														'cust_adv_payments_edit',
														'cust_adv_payments_delete',
														'cust_adv_payments_view',
														'approval_settings_edit',
														'approval_logs_view',
														'can_approve',
														'gstr_1_report',
														'gstr_2_report',
														'customer_orders_report',
														'load_sheet_report',
														'delivery_sheet_report',
														'show_purchase_price',

														'discountCouponAdd',
														'discountCouponEdit',
														'discountCouponDelete',
														'discountCouponView',

														'sales_gst_report',
														'purchase_gst_report',

														'customerCouponAdd',
														'customerCouponEdit',
														'customerCouponDelete',
														'customerCouponView',

														'return_items_report',
														'recent_sales_invoice_list',
														'stock_transfer_report',
														'pos',
														'sales_summary_report',
														'sales_return_payments',//report
														
														'debt_reminder_view',
														'debt_reminder_edit',
														
																		'online_store_view',
																		'online_store_edit',
																		'online_store_orders',
																		'attendance_edit',
                    'attendance_view',
 
                    'installment_plans',
                    'installment_payment',
                    'installment_report',

                    'nin_verify',
                    'nin_settings',
                    'nin_usage',
                    'nin_logs',
                    'system_settings',
                    'loyalty_view',
                    'loyalty_add',
                    'loyalty_edit',
                    'loyalty_delete',
                    'gift_cards_view',
                    'gift_cards_add',
                    'gift_cards_edit',
                    'gift_cards_delete',
                    'store_credit_view',
                    'store_credit_add',
                    'store_credit_edit',
                    'store_credit_delete',
                    'leads_view',
                    'leads_add',
                    'leads_edit',
                    'leads_delete',

                    // Physiotherapy & Rehabilitation — clinical permissions are
                    // explicit grants only (checked via physio_can(), no admin bypass)
                    'patients_view','patients_add','patients_edit','patients_merge','patients_export',
                    'episodes_view','episodes_add','episodes_edit','episodes_close',
                    'appointments_view','appointments_add','appointments_edit','appointments_cancel',
                    'care_queue_view','care_checkin',
                    'vitals_view','vitals_add',
                    'encounters_view','encounters_add','encounters_finalize','encounters_amend',
                    'assessments_view','assessments_add','assessments_finalize',
                    'investigations_view','investigations_request','investigations_result_enter','investigations_review',
                    'plans_view','plans_add','plans_amend',
                    'sessions_view','sessions_checkin','sessions_complete',
                    'admissions_view','admissions_manage','beds_manage',
                    'nursing_tasks_view','nursing_tasks_complete','nursing_notes_add',
                    'porter_tasks_view','porter_tasks_complete',
                    'referrals_view','referrals_manage',
                    'meals_view','meals_manage',
                    'leave_manage','leave_approve',
                    'daily_billing_view','daily_billing_run',
                    'deceased_record',
                    'discharge_recommend','discharge_decide',
                    'patient_docs_view','patient_docs_upload','patient_docs_release','patient_docs_clinical_view',
                    'patient_funds_view','patient_funds_add','payment_evidence_verify',
                    'funds_adjust_request','refund_request',
                    'patient_billing_view','patient_billing_add',
                    'opening_positions_view','opening_positions_enter','opening_positions_review',
                    'patient_feedback_view','patient_feedback_manage','testimonial_publish',
                    'portal_manage','assessment_templates_manage',
                    'imports_view','imports_run','imports_rollback',
                    'clinical_reports_view','clinical_export','clinical_cross_branch',
                    'md_authority',

                    'audit_trail_view',

                    // Store management
                    'store_add',
                    'store_delete',
                    'store_view',

                    // Attributes (product attributes module)
                    'attributes_add',
                    'attributes_edit',
                    'attributes_delete',
                    'attributes_view',

                    // Cash & shifts
                    'cash_delete',
                    'tills_add',
                    'tills_edit',
                    'tills_delete',
                    'tills_view',
                    'cashier_shifts_manage',
                    'z_report',

                    // Medical notes
                    'medical_notes_add',
                    'medical_notes_delete',
                    'medical_notes_view',

                    // Promotions
                    'promotions_manage',

                    // Template management
                    'sms_template_add',
                    'sms_template_delete',
                    'email_template_add',
                    'email_template_delete',

                    // Subscription admin
                    'subscription_delete',

                    // System
                    'database_backup',

                    // Reports
                    'cash_flow_report',
                    'inventory_aging_report',
                    'receivables_aging_report',
                    'reorder_suggestion_report',
                    'sell_through_report',
                    'variant_attribute_report',

													)));
		
		if(count($result) == 0){
			return false;
			
		}



		$this->db->trans_begin();		

		//BEFORE SAVING DELETE ALL PERSMISSIONS OF THE SPESIFIED ROLE
		// Record deliberate revocations first: any key the admin leaves
		// unchecked is logged so the additive physio role sync never re-adds it.
		$old = $this->db->select('permissions')->where('role_id', $role_id)
			->where('store_id', $store_id)->get('db_permissions')->result_array();
		$old_keys = array_map('strval', array_column($old, 'permissions'));
		$new_keys = array_map('strval', array_column($result, 'permissions'));
		$removed = array_values(array_diff($old_keys, $new_keys));
		$this->load->helper('physio');
		foreach($removed as $perm){
			if($this->db->table_exists('db_permission_revocations')){
				$this->db->query(
					'INSERT INTO db_permission_revocations (store_id, role_id, permissions, revoked_by, revoked_at)
					 VALUES (?,?,?,?,NOW())
					 ON DUPLICATE KEY UPDATE revoked_at=NOW()',
					array((int)$store_id, (int)$role_id, $perm, $this->session->userdata('inv_username') ?: 'system'));
			}
		}
		// Re-granted keys clear their revocation so a later sync can restore
		// them if they are ever removed again.
		if($this->db->table_exists('db_permission_revocations') && !empty($new_keys)){
			$this->db->where('role_id', $role_id)->where('store_id', $store_id)
				->where_in('permissions', $new_keys)->delete('db_permission_revocations');
		}

		$this->db->where("role_id",$role_id);
		$this->db->where("store_id",$store_id);
		$this->db->delete('db_permissions');

		//SAVE PERSMISSIONS
		$q1= $this->db->insert_batch('db_permissions',$result);
		if(!$q1){
			return false;
		}
		//SAVE PERMANENTALY
		$this->db->trans_commit();
		return true;
	}

}
