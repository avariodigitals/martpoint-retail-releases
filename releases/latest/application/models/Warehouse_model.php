<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_model extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
	}
	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}
	public function verify_and_save($data){
		$warehouse_name = $this->input->post('warehouse_name', TRUE);
		$mobile = $this->input->post('mobile', TRUE);
		$email = $this->input->post('email', TRUE);
		$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();

		//varify max sales usage of the package subscription
		validate_package_offers('max_warehouses','db_warehouse');
		//END

		$this->db->where('warehouse_name', $warehouse_name);
		$this->db->where('store_id', $store_id);
		if($this->db->get('db_warehouse')->num_rows()>0){ return "This Warehouse Name Already Exist.";}
		if(!empty($mobile)){
			$this->db->where('mobile', $mobile);
			$this->db->where('store_id', $store_id);
			if($this->db->get('db_warehouse')->num_rows()>0){ return "This Moble Number already exist.";}
		}

		if(!empty($email)){
			$this->db->where('email', $email);
			$this->db->where('store_id', $store_id);
			if($this->db->get('db_warehouse')->num_rows()>0){ return "This Email ID already exist.";}
		}
		
		$info = array(
			'store_id' => $store_id,
			'warehouse_type' => 'Custom',
			'warehouse_name' => $warehouse_name,
			'mobile' => $mobile,
			'email' => $email,
			'status' => 1,
			'created_date' => $this->data['CUR_DATE']
		);
		
		if ($this->db->insert('db_warehouse', $info)){
				$this->session->set_flashdata('success', 'Success!! New Warehouse Created Succssfully!!');
		        return "success";
		}
		else{
		        return "failed";
		}
	}
	public function verify_and_update($data){
		
		$q_id = $this->input->post('q_id', TRUE);
		$warehouse_name = $this->input->post('warehouse_name', TRUE);
		$mobile = $this->input->post('mobile', TRUE);
		$email = $this->input->post('email', TRUE);
		$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();

		$this->db->where('warehouse_name', $warehouse_name);
		$this->db->where('id !=', $q_id);
		$this->db->where('store_id', $store_id);
		if($this->db->get('db_warehouse')->num_rows()>0){ return "This Warehouse Name Already Exist.";}
		if(!empty($mobile)){
			$this->db->where('mobile', $mobile);
			$this->db->where('id !=', $q_id);
			$this->db->where('store_id', $store_id);
			if($this->db->get('db_warehouse')->num_rows()>0){ return "This Moble Number already exist.";}
		}
		if(!empty($email)){
			$this->db->where('email', $email);
			$this->db->where('id !=', $q_id);
			$this->db->where('store_id', $store_id);
			if($this->db->get('db_warehouse')->num_rows()>0){ return "This Email ID already exist.";}
		}
		
		$info = array(
			'warehouse_name' => $warehouse_name,
			'mobile' => $mobile,
			'email' => $email
		);
		$this->db->where('id', $q_id);
		$this->db->where('store_id', $store_id);
		
		if ($this->db->update('db_warehouse', $info)){
				$this->session->set_flashdata('success', 'Success!! Warehouse Updated Succssfully!!');
		        return "success";
		}
		else{
		        return "failed";
		}

		

	}
	public function status_update($id,$status){

		$store_id = get_current_store_id();

		// If reactivating (status=1), check branch subscription limit first
		if($status == 1 || $status == '1'){
			$current = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_warehouse')->row();
			if($current && $current->status == 0){
				$branch_check = check_subscription_limit('branch_limit');
				if($branch_check !== true){
					echo $branch_check;
					return;
				}
			}
		}

        $query1="update db_warehouse set status='$status' where id=$id and warehouse_type='Custom' and store_id=".$store_id;
        if ($this->db->simple_query($query1)){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	
	//Get users deatils
	public function get_details($id){
		$data=$this->data;

		//Validate This suppliers already exist or not
		$query=$this->db->query("select * from db_warehouse where id=$id and store_id=".get_current_store_id());
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['warehouse_name']=$query->warehouse_name;
			$data['mobile']=$query->mobile;
			$data['email']=$query->email;
			return $data;
		}
	}

	public function delete_warehouse($id){
		$this->db->trans_begin();

		$storeId = get_current_store_id();

		// Helper to count rows safely
		$countRef = function($table, $where) use ($storeId) {
			$where['store_id'] = $storeId;
			return (int) $this->db->get_where($table, $where)->num_rows();
		};

		// Check transactions tied to this warehouse
		if($countRef('db_sales', ['warehouse_id'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse has existing sales transactions.";
		}
		if($countRef('db_purchase', ['warehouse_id'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse has existing purchase transactions.";
		}
		if($countRef('db_stockadjustment', ['warehouse_id'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse has existing stock adjustment transactions.";
		}
		if($countRef('db_hold', ['warehouse_id'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse has existing hold transactions.";
		}
		if($countRef('db_quotation', ['warehouse_id'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse has existing quotation records.";
		}
		if($countRef('db_stocktransfer', ['warehouse_from'=>$id]) > 0 || $countRef('db_stocktransfer', ['warehouse_to'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse is referenced in stock transfer records.";
		}

		// Check if any user has this as default warehouse
		if($countRef('db_users', ['default_warehouse_id'=>$id]) > 0){
			$this->db->trans_rollback();
			return "Cannot delete! This warehouse is set as default for one or more users.";
		}

		// Clear warehouse-item stock records
		$this->db->where('warehouse_id', $id)->where('store_id', $storeId)->delete('db_warehouseitems');

		// Clear user-warehouse mappings
		$this->db->where('warehouse_id', $id)->delete('db_userswarehouses');

		// Clear barcode records tied to this warehouse
		$this->db->where('warehouse_id', $id)->delete('db_item_barcodes');

		// Clear hold records tied to this warehouse
		$this->db->where('warehouse_id', $id)->where('store_id', $storeId)->delete('db_hold');

		// Clear quotation records tied to this warehouse
		$this->db->where('warehouse_id', $id)->where('store_id', $storeId)->delete('db_quotation');

		// Now delete the warehouse
		$this->db->where('id', $id);
		$this->db->where('warehouse_type', 'Custom');
		$this->db->where('store_id', $storeId);
		$this->db->delete('db_warehouse');

		if($this->db->affected_rows() > 0){
			$this->db->trans_commit();
			return "success";
		}

		// Check why deletion failed
		$wh = $this->db->where('id', $id)->where('store_id', $storeId)->get('db_warehouse')->row();
		if(!$wh){
			$this->db->trans_rollback();
			return "Warehouse not found.";
		}
		if($wh->warehouse_type !== 'Custom'){
			$this->db->trans_rollback();
			return "System warehouses cannot be deleted.";
		}
		$this->db->trans_rollback();
		return "Failed to delete warehouse. Please try again.";
	}

	public function view_warehouse_wise_stock_item($item_id){
		$CI =& get_instance();
		$this->db->select("a.item_name,a.sales_price,a.store_id");
		$this->db->from("db_items as a");
		$this->db->select("b.brand_name");
		$this->db->join('db_brands as b','b.id=a.brand_id','left');
		$this->db->select("c.category_name");
		$this->db->from("db_category as c");
		$this->db->where("c.id=a.category_id");
		$this->db->where("a.id",$item_id);
		$q1=$this->db->get()->row();
		
		?>
		<div class="modal fade" id="view_warehouse_wise_stock_item_model">
		  <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		      <div class="modal-header header-custom">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title text-center"><?=$this->lang->line('warehouse_wise_stock');?></h4>
		      </div>
		      <div class="modal-body">
		        
		    <div class="row">
		      <div class="col-md-12">
		      	<div class="row invoice-info">
			        <div class="col-sm-4 invoice-col">
			          <i><b><?= $this->lang->line('item_information') ?></b></i>
			          <address>
			            <?php echo (!empty(trim($q1->item_name))) ? $this->lang->line('item_name').": ".$q1->item_name."<br>" : '';?>
			            <?php echo (!empty(trim($q1->brand_name))) ? $this->lang->line('brand_name').": ".$q1->brand_name."<br>" : '';?>
			            <?php echo (!empty(trim($q1->category_name))) ? $this->lang->line('category_name').": ".$q1->category_name."<br>" : '';?>
			            
			            <?php echo (!empty(trim($q1->sales_price))) ? $this->lang->line('sales_price').": ".$CI->currency($q1->sales_price)."<br>" : '';?>
			          </address>
			        </div>
			        <!-- /.col -->
			      </div>
			      <!-- /.row -->
		      </div>
		      <div class="col-md-12">
		       
		     
		              <div class="row">
		         		<div class="col-md-12">
		                  
		                      <table class="table table-bordered">
                                 <thead>
                                  <tr class="bg-primary">
                                    <th>#</th>
                                    <th><?= $this->lang->line('warehouse_type'); ?></th>
                                    <th><?= $this->lang->line('warehouse_name'); ?></th>
                                    <th><?= $this->lang->line('stock_quantity'); ?></th>
                                    
                                  </tr>
                                </thead>
                                <tbody>
                                	<?php
                                	//Only Allowed Warehouse show to loged in user
						         	if(!is_admin() && !is_store_admin()){
						         		//Find the previllaged wareshouses to the user
						         		$privileged_warehouses = get_privileged_warehouses_ids();
						         		$this->db->where("id in ($privileged_warehouses)");
						         	}
						         	$this->db->where("store_id",$q1->store_id);
                                	$q1=$this->db->select("*")->get("db_warehouse");
									$i=1;
									$str = '';
									if($q1->num_rows()>0){
										foreach ($q1->result() as $res1) {
											echo "<tr>";
											echo "<td>".$i++."</td>";
											echo "<td>".$res1->warehouse_type."</td>";
											echo "<td>".$res1->warehouse_name."</td>";
											echo "<td>".format_qty(get_total_qty_of_warehouse_item($item_id,$res1->id))."</td>";
											echo "</tr>";
										}
									}
									else{
										echo "<tr><td colspan='4' class='text-danger text-center'>No Records Found</td></tr>";
									}
									?>
                                </tbody>
                            </table>
		               
		               </div>
		            <div class="clearfix"></div>
		        </div>    
		       
		     
		   
		      </div><!-- col-md-9 -->
		      <!-- RIGHT HAND -->
		    </div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
		        
		      </div>
		    </div>
		    <!-- /.modal-content -->
		  </div>
		  <!-- /.modal-dialog -->
		</div>
		<?php
	}
}
