<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_model extends CI_Model {

	public function show_supplier_items_report(){
		$store_id = $this->input->post('store_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);

		if(!empty($store_id)){
			$this->db->where("store_id",$store_id);
		}
		if($item_id!=''){
			$this->db->where("id",$item_id);
		}
		$q1 = $this->db->select("*")->from("db_items");
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_grand_total=0;
			$tot_paid_amount=0;
			$tot_due_amount=0;
			foreach ($q1->result() as $res1) {
				

				$this->db->select("a.unit_total_cost,b.store_id,b.warehouse_id, b.id,a.item_id,b.supplier_id,b.purchase_code,b.purchase_date");
				$this->db->from("db_purchaseitems a");
				$this->db->where("a.item_id",$res1->id);
				$this->db->from("db_purchase b");
				$this->db->where("a.purchase_id=b.id");
				

				if($supplier_id!=''){
					$this->db->where("b.supplier_id",$supplier_id);
				}
				if(!empty($store_id)){
					$this->db->where("b.store_id",$store_id);
				}
				$this->db->order_by("a.id","desc");
				//$this->db->limit(1);

				$q2=$this->db->get();
				if($q2->num_rows()>0){
					foreach ($q2->result() as $res2){
						$supplier_name = $this->db->select("supplier_name")->from("db_suppliers")->where("id",$res2->supplier_id)->get()->row()->supplier_name;
						
						$q3 = $this->db->select("*")->from("db_items")->where("id",$res2->item_id)->get()->row();
						$item_code = $q3->item_code;
						$item_name = $q3->item_name;

						echo "<tr>";
						echo "<td>".++$i."</td>";
						
						if(store_module() && is_admin()){
							echo "<td>".get_store_name($res2->store_id)."</td>";	
						}
						if(warehouse_module() && warehouse_count()>0){
							echo "<td>".get_warehouse_name($res2->warehouse_id)."</td>";	
						}
						echo "<td><a title='View Invoice' href='".base_url("purchase/invoice/$res2->id")."'>".$res2->purchase_code."</a></td>";
						echo "<td>".show_date($res2->purchase_date)."</td>";
						echo "<td>".$supplier_name."</td>";
						echo "<td>".$item_code."</td>";
						echo "<td>".$item_name."</td>";
						echo "<td class='text-right'>".store_number_format($res2->unit_total_cost)."</td>";
						echo "</tr>";

					}
				}


			}

		}
		else{
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan=8>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	public function show_sales_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.id,a.warehouse_id,a.sales_code,a.sales_date,b.customer_name,b.customer_code,a.grand_total,a.paid_amount,a.store_id,a.created_by");
	    
		if($customer_id!=''){
			
			$this->db->where("a.customer_id=$customer_id");
		}
		if($view_all=="no"){
			$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		}
		$this->db->where("b.`id`= a.`customer_id`");
		$this->db->from("db_sales as a");
		$this->db->where("a.`sales_status`= 'Final'");

		if(!empty($created_by)){
			$this->db->where("upper(a.created_by)=upper('$created_by')");
		}
		
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		$this->db->from("db_customers as b");
		if($show_account_receivable==1){
			$this->db->where("a.grand_total!=a.paid_amount");
		}
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_grand_total=0;
			$tot_paid_amount=0;
			$tot_due_amount=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if(warehouse_module() && warehouse_count()>0){
					echo "<td>".get_warehouse_name($res1->warehouse_id)."</td>";	
				}
				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->customer_code."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->paid_amount)."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->grand_total-$res1->paid_amount))."</td>";
				echo "<td>".$res1->created_by."</td>";
				echo "</tr>";
				$tot_grand_total+=$res1->grand_total;
				$tot_paid_amount+=$res1->paid_amount;
				$tot_due_amount+=($res1->grand_total-$res1->paid_amount);

			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_paid_amount)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_due_amount)."</td>
					  <td></td>
				  </tr>";
		}
		else{
			$total_columns_count=9;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	public function show_sales_return_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.id,a.warehouse_id,a.return_code,a.return_date,b.customer_name,b.customer_code,a.grand_total,a.paid_amount,a.store_id");
	    
		if($customer_id!=''){
			
			$this->db->where("a.customer_id=$customer_id");
		}
		if($view_all=="no"){
			$this->db->where("(a.return_date>='$from_date' and a.return_date<='$to_date')");
		}
		$this->db->where("b.`id`= a.`customer_id`");
		$this->db->from("db_salesreturn as a");
		$this->db->from("db_customers as b");
		$this->db->select("CASE WHEN c.sales_code IS NULL THEN '' ELSE c.sales_code END AS sales_code");
		$this->db->join('db_sales as c','c.id=a.sales_id','left');
		
		
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_grand_total=0;
			$tot_paid_amount=0;
			$tot_due_amount=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if(warehouse_module() && warehouse_count()>0){
					echo "<td>".get_warehouse_name($res1->warehouse_id)."</td>";	
				}
				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales_return/invoice/$res1->id")."'>".$res1->return_code."</a></td>";
				}
				else{
				echo "<td>".$res1->return_code."</td>";	
				}

				
				echo "<td>".show_date($res1->return_date)."</td>";
				
				echo (!empty($res1->sales_code)) ? "<td><a title='Return Raised Against this Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>" : '<td>-NA-</td>';
				echo "<td>".$res1->customer_name."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->paid_amount)."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->grand_total-$res1->paid_amount))."</td>";
				echo "</tr>";
				$tot_grand_total+=$res1->grand_total;
				$tot_paid_amount+=$res1->paid_amount;
				$tot_due_amount+=($res1->grand_total-$res1->paid_amount);

			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_paid_amount)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_due_amount)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	public function show_purchase_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.id,a.warehouse_id,a.purchase_code,a.purchase_date,b.supplier_name,b.supplier_code,a.grand_total,a.paid_amount,a.store_id");
	    
		if($supplier_id!=''){
			$this->db->where("a.supplier_id=$supplier_id");
		}
		if($view_all=="no"){
			$this->db->where("(a.purchase_date>='$from_date' and a.purchase_date<='$to_date')");
		}
		$this->db->where("b.`id`= a.`supplier_id`");
		$this->db->from("db_purchase as a");
		$this->db->where("a.`purchase_status` IN ('Received','Partially Received')");
		$this->db->from("db_suppliers as b");
		
		
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		if($show_account_payble==1){
			$this->db->where("a.grand_total!=a.paid_amount");
		}
		//echo $this->db->get_compiled_select();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_grand_total=0;
			$tot_paid_amount=0;
			$tot_due_amount=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if(warehouse_module() && warehouse_count()>0){
					echo "<td>".get_warehouse_name($res1->warehouse_id)."</td>";	
				}
				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("purchase/invoice/$res1->id")."'>".$res1->purchase_code."</a></td>";
				}
				else{
				echo "<td>".$res1->purchase_code."</td>";	
				}

				
				echo "<td>".show_date($res1->purchase_date)."</td>";
				echo "<td>".$res1->supplier_code."</td>";
				echo "<td>".$res1->supplier_name."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->paid_amount)."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->grand_total-$res1->paid_amount))."</td>";
				echo "</tr>";
				$tot_grand_total+=$res1->grand_total;
				$tot_paid_amount+=$res1->paid_amount;
				$tot_due_amount+=($res1->grand_total-$res1->paid_amount);

			}
			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_paid_amount)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_due_amount)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	public function show_purchase_return_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}
		
		$this->db->select("a.id,a.warehouse_id,a.return_code,a.return_date,b.supplier_name,a.grand_total,a.paid_amount,a.store_id");
	    
		if($supplier_id!=''){
			$this->db->where("a.supplier_id=$supplier_id");
		}
		if($view_all=="no"){
			$this->db->where("(a.return_date>='$from_date' and a.return_date<='$to_date')");
		}
		$this->db->where("b.`id`= a.`supplier_id`");
		$this->db->from("db_purchasereturn as a");
		$this->db->from("db_suppliers as b");
		$this->db->select("CASE WHEN c.purchase_code IS NULL THEN '' ELSE c.purchase_code END AS purchase_code");
		$this->db->join('db_purchase as c','c.id=a.purchase_id','left');

		
		
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		//echo $this->db->get_compiled_select();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_grand_total=0;
			$tot_paid_amount=0;
			$tot_due_amount=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if(warehouse_module() && warehouse_count()>0){
					echo "<td>".get_warehouse_name($res1->warehouse_id)."</td>";	
				}
				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("purchase_return/invoice/$res1->id")."'>".$res1->return_code."</a></td>";
				}
				else{
				echo "<td>".$res1->return_code."</td>";	
				}

				
				echo "<td>".show_date($res1->return_date)."</td>";
				echo (!empty($res1->purchase_code)) ? "<td><a title='Return Raised Against this Invoice' href='".base_url("purchase/invoice/$res1->id")."'>".$res1->purchase_code."</a></td>" : '<td>-NA-</td>';
				
				echo "<td>".$res1->supplier_name."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->paid_amount)."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->grand_total-$res1->paid_amount))."</td>";
				echo "</tr>";
				$tot_grand_total+=$res1->grand_total;
				$tot_paid_amount+=$res1->paid_amount;
				$tot_due_amount+=($res1->grand_total-$res1->paid_amount);

			}
			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_paid_amount)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_due_amount)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}
	
	public function show_expense_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/

		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		/*$q1=$this->db->query("SELECT a.*,b.category_name from db_expense as a,db_expense_category as b where b.id=a.category_id and a.expense_date>='$from_date' and expense_date<='$to_date'");*/
		
		$this->db->select("a.*,b.category_name");
	    
		if($category_id!=''){
			$this->db->where("a.category_id=$category_id");
		}
		if($view_all=="no"){
			$this->db->where("(a.expense_date>='$from_date' and a.expense_date<='$to_date')");
		}
		$this->db->where("b.`id`= a.`category_id`");
		$this->db->from("db_expense as a");
		$this->db->from("db_expense_category as b");
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		//echo $this->db->get_compiled_select();
		
		$q1=$this->db->get();
		
		if($q1->num_rows()>0){
			$i=0;
			$tot_expense_amt=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				echo "<td>".$res1->expense_code."</td>";
				echo "<td>".show_date($res1->expense_date)."</td>";
				echo "<td>".$res1->category_name."</td>";
				echo "<td>".$res1->reference_no."</td>";
				echo "<td>".$res1->expense_for."</td>";
				echo "<td class='text-right'>".store_number_format($res1->expense_amt)."</td>";
				echo "<td>".$res1->note."</td>";
				echo "<td>".ucfirst($res1->created_by)."</td>";
				echo "</tr>";
				$tot_expense_amt+=$res1->expense_amt;
			}
			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total Expense :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_expense_amt)."</td>
					  <td colspan='2'></td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}
	public function show_stock_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$as_of_date = '';
		if(!empty($to_date)){
			$as_of_date = system_fromatted_date($to_date);
		}

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		if(!is_admin()){
			$this->db->where("a.store_id",get_current_store_id());
		}
		$this->db->select("a.sales_price,a.item_code,a.price,a.purchase_price,a.item_name,a.tax_type,a.store_id,a.id as item_id,a.item_group,
			d.category_name,
			c.brand_name,
			");
		$this->db->select("b.tax_name");
		$this->db->from("db_items as a");

		$this->db->from("db_tax as b");
		$this->db->where("b.id=a.tax_id");
		$this->db->where("a.service_bit=0");
		$this->db->join("db_brands as c","c.id=a.brand_id","left");
		$this->db->join("db_category as d","d.id=a.category_id","left");

		if(!empty($brand_id)){
			$this->db->where("a.brand_id",$brand_id);
		}
		if(!empty($category_id)){
			$this->db->where("a.category_id",$category_id);
		}

		//echo $this->db->get_compiled_select();exit;
		$q1=$this->db->get();
		$str='';


		if($q1->num_rows()>0){
			$i=0;
			$tot_stock = 0;
			$tot_value = 0;
			$tot_stock_value_by_purchase_price = 0;
			foreach ($q1->result() as $res1) {

				if($res1->item_group=='Variants'){continue;}

					if(!empty($as_of_date)){
						$available_qty_wh = get_total_qty_of_warehouse_item_as_of_date($res1->item_id,$warehouse_id,$res1->store_id,$as_of_date);
					}else{
						$available_qty_wh = total_available_qty_items_of_warehouse($warehouse_id,$res1->store_id,$res1->item_id);
					}

					$value = $available_qty_wh * $res1->sales_price;

					$stock_value_by_purchase_price = $available_qty_wh * $res1->price;

					/*if($available_qty_wh>0){*/
						$tax_type = ($res1->tax_type=='Inclusive') ? 'Inc.' : 'Exc.';
						$str .= "<tr>";
						$str .= "<td>".++$i."</td>";
						if(store_module() && is_admin()){
							$str .= "<td>".get_store_name($res1->store_id)."</td>";	
						}
						$str .= "<td>".$res1->item_code."</td>";
						$str .= "<td>".$res1->item_name."</td>";
						$str .= "<td>".$res1->brand_name."</td>";
						$str .= "<td>".$res1->category_name."</td>";
						$str .= "<td class='text-right'>".store_number_format($res1->purchase_price)."</td>";
						$str .= "<td>".$res1->tax_name."[".$tax_type."]</td>";
						$str .= "<td class='text-right'>".store_number_format($res1->price)."</td>";
						$str .= "<td class='text-right'>".store_number_format($res1->sales_price)."</td>";
						$str .= "<td>".format_qty($available_qty_wh)."</td>";
						$str .= "<td class='text-right'>".store_number_format($value)."</td>";
						$str .= "<td class='text-right'>".store_number_format($stock_value_by_purchase_price)."</td>";
						$str .= "</tr>";
						$tot_stock +=$available_qty_wh;
						$tot_value +=$value;
						$tot_stock_value_by_purchase_price += $stock_value_by_purchase_price;

					/*}*/

			}
			$total_columns_count=9;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			$date_note = (!empty($as_of_date)) ? " <span style='font-size:12px;color:#666;'>[Stock as of ".show_date($as_of_date)."]</span>" : "";
			$str .= "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b>".$date_note."</td>
					  <td class='text-left text-bold'>".format_qty($tot_stock)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_value)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_stock_value_by_purchase_price)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=12;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			$str .= "<tr>";
			$str .= "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			$str .= "</tr>";
		}
		
		return $str;
		
	    exit;
	}
	public function brand_wise_stock(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		if(!empty($store_id)){
			$this->db->where("b.store_id",$store_id);
		}
		if(!is_admin()){
			$this->db->where("b.store_id",get_current_store_id());
		}
		$this->db->from("db_brands as b");
		$this->db->select("b.id,b.brand_name,b.store_id");
		if(!empty($brand_id)){
			$this->db->where("b.id",$brand_id);
		}


		//echo $this->db->get_compiled_select();exit();
		$q1=$this->db->get();
		$str1='';
		if($q1->num_rows()>0){
			$i=0;
			$tot_stock=0;
			foreach ($q1->result() as $res1) {
					$available_qty=0;
					$brand_id=$res1->id;
					$store_id=$res1->store_id;

					$str='';
					if(empty($store_id)){
					     $str =" and store_id= $store_id ";
					}
					if(!empty($warehouse_id)){
				         $str =" and warehouse_id= $warehouse_id ";
				    }
					$q3 = "select COALESCE(sum(available_qty),0) as available_qty from 
							db_warehouseitems where 
							item_id in (select id from db_items where brand_id=$brand_id)
							$str
							";

					$q3=$this->db->query($q3);

					$available_qty = $q3->row()->available_qty;
					$str1 .= "<tr>";
					$str1 .= "<td>".++$i."</td>";
					if(store_module() && is_admin()){
						$str1 .= "<td>".get_store_name($res1->store_id)."</td>";	
					}
					$str1 .= "<td>".$res1->brand_name."</td>";
					$str1 .= "<td>".format_qty($available_qty)."</td>";
					$str1 .= "</tr>";
					$tot_stock+=$available_qty;
			}
			$total_columns_count=2;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			$str1 .= "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_stock)."</td>
				  </tr>";

		}
		else{
			$total_columns_count=3;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			$str1 .= "<tr>";
			$str1 .= "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			$str1 .= "</tr>";
		}
		
		return $str1;
	    exit;
	}
	public function show_item_sales_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}
		
		$this->db->select("a.id,a.sales_code,a.sales_date,b.customer_name,b.customer_code,a.grand_total,a.paid_amount,a.store_id,c.unit_total_cost,c.total_cost,e.category_name");
		$this->db->select("c.sales_qty,d.item_name");
	    
	    
		$this->db->from("db_sales as a");
		//$this->db->where("a.`id`= c.`sales_id`");
		$this->db->where("a.`sales_status`= 'Final'");
		
		if($view_all=="no"){
			$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		}
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		$this->db->order_by("a.`sales_date`,a.sales_code",'desc');

		$str = "" ; 
		if($item_id!=''){
			$str = " and c.item_id=$item_id"; 
		}
		$this->db->join("db_salesitems as c","c.sales_id = a.id $str","left");

		//$this->db->join("db_category as e","e.`id`= d.category_id","left");
		$this->db->join("db_items as d","d.`id`= c.`item_id`","left");

		$str = "" ; 
		if($category_id!=''){
			$str = " and e.id=$category_id"; 
		}

		$this->db->join("db_category as e","e.`id`= d.`category_id` $str","right");
		$this->db->join("db_customers as b","b.`id`= a.`customer_id`","left");

		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_total_cost=0;
			$tot_sales_qty=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}

				
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".$res1->category_name."</td>";
				echo "<td>".format_qty($res1->sales_qty)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->unit_total_cost)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->total_cost)."</td>";
				echo "</tr>";
				$tot_sales_qty+=$res1->sales_qty;
				$tot_total_cost+=$res1->total_cost;
			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}

			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_sales_qty)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_total_cost)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}
	public function show_purchase_payments_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		$supplier_id = $this->input->post('supplier_id');
		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/

		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);
		
		$this->db->select("c.id,c.purchase_code,a.payment_date,b.supplier_name,b.supplier_code,a.payment_type,a.payment_note,a.payment,a.store_id");
	    
		if($supplier_id!=''){
			$this->db->where("c.supplier_id=$supplier_id");
		}
		$this->db->where("b.id=c.`supplier_id`");
		$this->db->where("(a.payment_date>='$from_date' and a.payment_date<='$to_date')");
		
		$this->db->where("c.id=a.purchase_id");

		$this->db->from("db_purchasepayments as a");
		$this->db->from("db_suppliers as b");
		$this->db->from("db_purchase as c");
		$this->db->where("c.`purchase_status` IN ('Received','Partially Received')");
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		//$this->db->group_by("c.purchase_code");
		
		//echo $this->db->get_compiled_select();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_payment=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if($res1->store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("purchase/invoice/$res1->id")."'>".$res1->purchase_code."</a></td>";
				}
				else{
				echo "<td>".$res1->purchase_code."</td>";	
				}

				
				echo "<td>".show_date($res1->payment_date)."</td>";
				echo "<td>".$res1->supplier_code."</td>";
				echo "<td>".$res1->supplier_name."</td>";
				echo "<td>".$res1->payment_type."</td>";
				echo "<td>".$res1->payment_note."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->payment))."</td>";
				echo "</tr>";
				$tot_payment+=$res1->payment;
			}
			$total_columns_count=7;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_payment)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}
	public function show_sales_payments_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);
		
		
		$this->db->select("c.id,c.sales_code,a.payment_date,b.customer_name,b.customer_code,a.payment_type,a.payment_note,a.payment,c.store_id,c.created_by");
		
	    $this->db->from("db_sales as c");
	    $this->db->where("c.id=a.sales_id and c.`sales_status`= 'Final'");
	    

	    if($customer_id!=''){
			$this->db->where("c.customer_id",$customer_id);
		}
		if(!empty($created_by)){
			$this->db->where("upper(c.created_by)=upper('$created_by')");
		}
		if(!empty($store_id)){
			$this->db->where("c.store_id",$store_id);
		}

		$str = '';
		if(!empty($payment_type)){
			$str .= " and a.payment_type = '$payment_type'";
		}

		$str .=(" and (a.payment_date>='$from_date' and a.payment_date<='$to_date')");

	    $this->db->join("db_salespayments as a","a.sales_id = c.id $str","left");

		$this->db->join("db_customers as b","b.id=c.`customer_id`","left");
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_payment=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if($res1->store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}
				
				echo "<td>".show_date($res1->payment_date)."</td>";
				echo "<td>".$res1->customer_code."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->payment_type."</td>";
				echo "<td>".$res1->payment_note."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->payment))."</td>";
				echo "<td>".$res1->created_by."</td>";
				echo "</tr>";
				$tot_payment+=$res1->payment;
			}
			$total_columns_count=7;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_payment)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}



	public function show_sales_return_payments_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);
		
		
		$this->db->select("c.id,c.return_code,a.payment_date,b.customer_name,b.customer_code,a.payment_type,a.payment_note,a.payment,c.store_id,c.created_by");
		
	    $this->db->from("db_salesreturn as c");
	    $this->db->where("c.id=a.return_id");
	    

	    if($customer_id!=''){
			$this->db->where("c.customer_id",$customer_id);
		}
		if(!empty($created_by)){
			$this->db->where("upper(c.created_by)=upper('$created_by')");
		}
		if(!empty($store_id)){
			$this->db->where("c.store_id",$store_id);
		}

		$str = '';
		if(!empty($payment_type)){
			$str .= " and a.payment_type = '$payment_type'";
		}

		$str .=(" and (a.payment_date>='$from_date' and a.payment_date<='$to_date')");

	    $this->db->join("db_salespaymentsreturn as a","a.return_id = c.id $str","left");

		$this->db->join("db_customers as b","b.id=c.`customer_id`","left");
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_payment=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if($res1->store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales_return/invoice/$res1->id")."'>".$res1->return_code."</a></td>";
				}
				else{
				echo "<td>".$res1->return_code."</td>";	
				}
				
				echo "<td>".show_date($res1->payment_date)."</td>";
				echo "<td>".$res1->customer_code."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->payment_type."</td>";
				echo "<td>".$res1->payment_note."</td>";
				echo "<td class='text-right'>".store_number_format(($res1->payment))."</td>";
				echo "<td>".$res1->created_by."</td>";
				echo "</tr>";
				$tot_payment+=$res1->payment;
			}
			$total_columns_count=7;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_payment)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}


	/*Expired Items Report*/
	public function show_expired_items_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		
		//$to_date=date("Y-m-d",strtotime($to_date));
		$to_date = system_fromatted_date($to_date);

		$this->db->select("id,item_code,item_name,expire_date,stock,lot_number,store_id");
	    
		if($item_id!=''){
			
			$this->db->where("id=$item_id");
		}
		if($view_all=="no"){
			$this->db->where("(expire_date<='$to_date')");
		}
		$this->db->from("db_items");
		if(!empty($store_id)){
			$this->db->where("store_id",$store_id);
		}
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			foreach ($q1->result() as $res1) {
				if(get_total_qty_of_warehouse_item($res1->id,$warehouse_id,$res1->store_id) > 0 && !empty($warehouse_id)){
					echo "<tr>";
					echo "<td>".++$i."</td>";
					if(store_module() && is_admin()){
						echo "<td>".get_store_name($res1->store_id)."</td>";	
					}
					echo "<td>".$res1->item_code."</td>";
					echo "<td>".$res1->item_name."</td>";
					echo "<td>".$res1->lot_number."</td>";
					echo "<td>".show_date($res1->expire_date)."</td>";
					echo "<td>".format_qty(get_total_qty_of_warehouse_item($res1->id,$warehouse_id,$res1->store_id))."</td>";

				}
				else{
					echo "<tr>";
					echo "<td>".++$i."</td>";
					if(store_module() && is_admin()){
						echo "<td>".get_store_name($res1->store_id)."</td>";	
					}
					echo "<td>".$res1->item_code."</td>";
					echo "<td>".$res1->item_name."</td>";
					echo "<td>".$res1->lot_number."</td>";
					echo "<td>".show_date($res1->expire_date)."</td>";
					echo "<td>".format_qty($res1->stock)."</td>";
				}

			}
		}
		else{
			$total_columns_count=6;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	function _create_query($store_id='',$table_name,$table_column,$from_date,$to_date){
		$ids = array();

		if($table_column=='db_purchase'){
			$this->db->where("purchase_status IN ('Received','Partially Received')");
		}
		else if($table_column=='db_sales'){
			$this->db->where("sales_status='Final'");
		}

		$this->db->where("(".$table_column.">='$from_date' and ".$table_column."<='$to_date')");

		if(is_admin()){
			if(!empty($store_id)){
				$this->db->where("store_id",$store_id);
			}
		}else{
			$this->db->where("store_id",get_current_store_id());
		}

		$this->db->select("id");
		$this->db->from($table_name);
		$query = $this->db->get();
		if($query->num_rows()>0){
			foreach ($query->result() as $res) {
				$ids[] = $res->id;
	    	}	
		}
		return (count($ids)>0) ? implode (", ", $ids) : 'null';
	}
	function _get_db_sales_ids($store_id='',$from_date,$to_date){

		return $this->_create_query($store_id,'db_sales','sales_date',$from_date,$to_date);

	}
	function _get_db_sales_return_ids($store_id='',$from_date,$to_date){

		return $this->_create_query($store_id,'db_salesreturn','return_date',$from_date,$to_date);

	}
	function _get_db_purchase_ids($store_id='',$from_date,$to_date){

		return $this->_create_query($store_id,'db_purchase','purchase_date',$from_date,$to_date);

	}
	function _get_db_purchase_return_ids($store_id='',$from_date,$to_date){

		return $this->_create_query($store_id,'db_purchasereturn','return_date',$from_date,$to_date);

	}
	function _get_db_expense_ids($store_id='',$from_date,$to_date){
		
		return $this->_create_query($store_id,'db_expense','expense_date',$from_date,$to_date);

	}

	public function get_profit_loss_report(){

			$store_id=$this->input->post('store_id');
			$from_date=$this->input->post('from_date');
			$to_date=$this->input->post('to_date');
			$from_date = system_fromatted_date($from_date);
			$to_date = system_fromatted_date($to_date);

			$sales_ids = $this->_get_db_sales_ids($store_id,$from_date,$to_date);
			$sales_return_ids = $this->_get_db_sales_return_ids($store_id,$from_date,$to_date);
			$purchase_ids = $this->_get_db_purchase_ids($store_id,$from_date,$to_date);
			$purchase_return_ids = $this->_get_db_purchase_return_ids($store_id,$from_date,$to_date);
			$expense_ids = $this->_get_db_expense_ids($store_id,$from_date,$to_date);

			
			
			$info=array();

			//Get opening Balance
			if(store_module() && is_admin()){if(!empty($store_id)){ 
						$this->db->where("a.store_id",$store_id);}
					}else{ 
						$this->db->where("a.store_id",get_current_store_id());	
				}
			$this->db->select("SUM(b.adjustment_qty * a.purchase_price) AS  opening_stock_price");
			$this->db->from("db_items AS a , db_stockadjustmentitems AS b");
			$this->db->where("a.id=b.item_id");
			$query = $this->db->get()->row();
            $opening_stock_price=$query->opening_stock_price;
            $info['opening_stock_price']=(store_number_format($opening_stock_price));
            


            //total purchase amt
			$this->db->select("COALESCE(SUM(a.tax_amt),0) AS tax_amt");
			$this->db->select("COALESCE(SUM(a.discount_amt),0) AS discount_amt");
			$this->db->from("db_purchaseitems as a");
			$this->db->where("a.purchase_id in (".$purchase_ids.")");
			$query = $this->db->get()->row();
            $purchase_discount_amt=$query->discount_amt;
            $purchase_tax_amt=$query->tax_amt;
            $info['purchase_tax_amt']=(store_number_format($purchase_tax_amt));
            

            //total purchase amt
			$this->db->select("COALESCE(SUM(grand_total),0) AS pur_total");
			$this->db->select("COALESCE(SUM(other_charges_amt),0) AS other_charges_amt");
			$this->db->select("COALESCE(SUM(tot_discount_to_all_amt),0) AS tot_discount_to_all_amt");
			$this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount");
			$this->db->from("db_purchase");
			$this->db->where("id in (".$purchase_ids.")");

			$query = $this->db->get()->row();

            $pur_total=$query->pur_total;
            $pur_total-=$purchase_tax_amt;
            $info['pur_total']=(store_number_format($pur_total));

            //Other Charge of Purchase entry
            $pur_other_charges_amt=$query->other_charges_amt;
            $info['pur_other_charges_amt']=(store_number_format($pur_other_charges_amt));

            //Disount purchase entry
			$purchase_discount_amt+=$query->tot_discount_to_all_amt;
            $info['purchase_discount_amt']=(store_number_format($purchase_discount_amt));

            //purchase Paid Amount
            $purchase_paid_amount=$query->paid_amount;
            $info['purchase_paid_amount']=(store_number_format($purchase_paid_amount));
            
            //total purchase due
            $purchase_due_total=$pur_total - $query->paid_amount;
            $info['purchase_due_total']=(store_number_format($purchase_due_total));





            /***********Purchase Return**********/
            //return tax amt
			$this->db->select("COALESCE(SUM(tax_amt),0) AS tax_amt");
			$this->db->select("COALESCE(SUM(discount_amt),0) AS discount_amt");
			$this->db->where("return_id in (".$purchase_return_ids.")");
			$this->db->from("db_purchaseitemsreturn");
			$query = $this->db->get()->row();
			
			//Disount purchase return entry
            $purchase_return_discount_amt=$query->discount_amt;

            $purchase_return_tax_amt=$query->tax_amt;
            $info['purchase_return_tax_amt']=(store_number_format($purchase_return_tax_amt));
            
            //total purchase return amt
			$this->db->select("COALESCE(SUM(grand_total),0) AS pur_total");
			$this->db->select("COALESCE(SUM(other_charges_amt),0) AS other_charges_amt");
			$this->db->select("COALESCE(SUM(tot_discount_to_all_amt),0) AS tot_discount_to_all_amt");
			$this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount");
			$this->db->where("id in (".$purchase_return_ids.")");
			$this->db->from("db_purchasereturn");
			$query = $this->db->get()->row();
			
            $pur_return_total=$query->pur_total;
            $pur_return_total-=$purchase_return_tax_amt;
            $info['pur_return_total']=(store_number_format($pur_return_total));

            //Due
            $purchase_return_due_total=$pur_return_total - $query->paid_amount;
            $info['purchase_return_due_total']=(store_number_format($purchase_return_due_total));

            //Other Charge of Purchase return entry
            $pur_return_other_charges_amt=$query->other_charges_amt;
            $info['pur_return_other_charges_amt']=(store_number_format($pur_return_other_charges_amt));
            
            //Disount purchase return entry
            $purchase_return_discount_amt+=$query->tot_discount_to_all_amt;
            $info['purchase_return_discount_amt']=(store_number_format($purchase_return_discount_amt));

            //Purchase Return Paid Amount
            $purchase_return_paid_amount=$query->paid_amount;
            $info['purchase_return_paid_amount']=(store_number_format($purchase_return_paid_amount));
            
            
            /***********Sales Entry**********/
			$this->db->select("COALESCE(SUM(tax_amt),0) AS tax_amt");
			$this->db->select("COALESCE(SUM(discount_amt),0) AS discount_amt");
			$this->db->where("sales_id in (".$sales_ids.")");
			$this->db->from("db_salesitems");
			$query = $this->db->get()->row();
			
            
			//total sales amt
            $sales_tax_amt=$query->tax_amt;
            $info['sales_tax_amt']=(store_number_format($sales_tax_amt));
            
            
            
            //Disount sales entry
            $sales_discount_amt=$query->discount_amt;
            

            //Coupon Disount sales entry
			$this->db->select("COALESCE(SUM(b.coupon_amt),0) AS coupon_amt");
			$this->db->select("COALESCE(SUM(other_charges_amt),0) AS other_charges_amt");
			$this->db->select("COALESCE(SUM(tot_discount_to_all_amt),0) AS tot_discount_to_all_amt");
			$this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_grand_total");
			$this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount");
			$this->db->from("db_sales as b");
			$this->db->where("id in (".$sales_ids.")");
			$this->db->where("b.sales_status='Final'");
			$query = $this->db->get()->row();

            $coupon_discount_amt=$query->coupon_amt;
            $info['coupon_discount_amt']=(store_number_format($coupon_discount_amt));

            //Other Charge of Sales entry
            $sal_other_charges_amt=$query->other_charges_amt;
            $info['sal_other_charges_amt']=(store_number_format($sal_other_charges_amt));

            //Discount Amount
            $sales_discount_amt+=$query->tot_discount_to_all_amt;
            $info['sales_discount_amt']=(store_number_format($sales_discount_amt));
            
            
            //Total SAles amount
            $sal_total=$query->tot_sal_grand_total;
            $tot_sal_total=($sal_total-$sales_tax_amt+$coupon_discount_amt);
            $info['sal_total']=(store_number_format($tot_sal_total));

            //Due
            $sales_due_total=$sal_total - $query->paid_amount;
            $info['sales_due_total']=(store_number_format($sales_due_total));
        
            //sales Paid Amount
            $sales_paid_amount=$query->paid_amount;
            $info['sales_paid_amount']=(store_number_format($sales_paid_amount));
            


            /***********Sales Return**********/
			$this->db->select("COALESCE(SUM(tax_amt),0) AS tax_amt");
			$this->db->select("COALESCE(SUM(discount_amt),0) AS discount_amt");
			$this->db->from("db_salesitemsreturn");
			$this->db->where("return_id in (".$sales_return_ids.")");
			$query = $this->db->get()->row();

			//Disount sales return entry
            $sales_return_discount_amt=$query->discount_amt;

			//total sales return amt
            $sales_return_tax_amt=$query->tax_amt;
            $info['sales_return_tax_amt']=(store_number_format($sales_return_tax_amt));
            
            
			$this->db->select("COALESCE(SUM(grand_total),0) AS sal_total");
			$this->db->select("COALESCE(SUM(other_charges_amt),0) AS other_charges_amt");
			$this->db->select("COALESCE(SUM(coupon_amt),0) AS coupon_amt");
			$this->db->select("COALESCE(SUM(tot_discount_to_all_amt),0) AS tot_discount_to_all_amt");
			$this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount");
			$this->db->where("id in (".$sales_return_ids.")");
			$this->db->from("db_salesreturn");
			$query = $this->db->get()->row();
			

			//total sales return amt
            $sal_return_total=$query->sal_total;
            $sal_return_total-=$sales_return_tax_amt;
            $info['sal_return_total']=(store_number_format($sal_return_total));

            //Due
            $sales_return_due_total=$sal_return_total - $query->paid_amount;
            $info['sales_return_due_total']=(store_number_format($sales_return_due_total));

            //Other Charge of Sales return entry
            $sal_return_other_charges_amt=$query->other_charges_amt;
            $info['sal_return_other_charges_amt']=(store_number_format($sal_return_other_charges_amt));
            
            //Coupon Disount sales entry
            $coupon_discount_amt=$query->coupon_amt;
            $info['return_coupon_discount_amt']=(store_number_format($coupon_discount_amt));

            //Discount 
            $sales_return_discount_amt+=$query->tot_discount_to_all_amt;
            $info['sales_return_discount_amt']=(store_number_format($sales_return_discount_amt));
            
            //sales Return Paid Amount;
            $sales_return_paid_amount=$query->paid_amount;
            $info['sales_return_paid_amount']=(store_number_format($sales_return_paid_amount));
            
            
            /***********Sales Return**********/
			$this->db->select("COALESCE(SUM(expense_amt),0) AS exp_total");
			$this->db->where("id in (".$expense_ids.")");
			$this->db->from("db_expense");
			$query = $this->db->get()->row();

			//expense total
            $exp_total=$query->exp_total;
            $info['exp_total']=(store_number_format($exp_total));;
                        
           	//GROSS PROFIT & NET PROFIT
            $sales_details = $this->get_sales_item_sum($sales_ids);
            $sal_item_pur_price = $sales_details['purchase_price'];
            $sal_cost = $sales_details['sales_price'];
            $sal_tax_amt = $sales_details['tax_amt'];
            $net_sales = $sal_cost-$sal_item_pur_price;//ACTUAL SALES VALUE


            $return_details = $this->get_sales_return_item_sum($sales_return_ids);
            $ret_item_pur_price = $return_details['purchase_price'];
            $ret_cost = $return_details['return_price'];
            $ret_tax_amt = $return_details['tax_amt'];
            $net_return = $ret_cost - $ret_item_pur_price;//ACTUAL RETURN VALUE
            

            //TO FIND GROSS PROFIT = (SALES PRICE - PURCHASE PRICE OF ITEM) - (SALES RETURN - PURCHASE PRICE OF ITEM)
            /**
             * Gorss prfit = Sales - Purchase price
             * To find gross prfit we are also want deduct return sales records
            */
            $gross_profit = $net_sales - $net_return;

            $info['net_sales']=(store_number_format($sal_cost));
            $info['sales_return_total']=(store_number_format($ret_cost));
            $info['gross_profit']=(store_number_format($gross_profit));

            
            //Tax
            //Sales & Retuern tax, because we are calculating sum
            $tot_tax = $sal_tax_amt - $ret_tax_amt;
            //Now you got valid tax

            //To find net profit now need to deduct valid tax from gross profit
            //also deduct expenses
            $net_profit = ($gross_profit - $tot_tax) - $exp_total;
            $info['tot_net_profit']=(store_number_format($net_profit));

            //Find purchase price of the sales item
            

            return $info;
	}


	public function get_sales_item_sum($sales_ids){
		$this->db->select("a.sales_qty,a.purchase_price,a.total_cost,a.tax_amt,a.tax_type");
		$this->db->select("b.tax");
		$this->db->from('db_salesitems a');
		$this->db->where("a.sales_id in (".$sales_ids.")");

		$this->db->join('db_tax b','b.id=a.tax_id','left');
		

		$Q1 = $this->db->get();
		$sum_pur_price = 0;
		$sum_sal_price = 0;
		$sum_tax_amt = 0;
		if($Q1->num_rows()>0){
			foreach ($Q1->result() as $res1){

				$sales_tax_amt = $res1->tax_amt;
				$sales_qty = $res1->sales_qty;

				$purchase_price = ($res1->tax_type == 'Exclusive') ? $res1->purchase_price : original_cost($res1->purchase_price,$res1->tax,'Inclusive');

				

				$sales_price = $res1->total_cost;

				$tot_pur_price = $sales_qty * $purchase_price;

				$sum_pur_price+=$tot_pur_price;
				$sum_sal_price+=$sales_price;
				$sum_tax_amt+=$sales_tax_amt;
			}
		}

		return array(
					'purchase_price' => $sum_pur_price,
					'sales_price' => $sum_sal_price,
					'tax_amt' => $sum_tax_amt,
				);
	}
	public function get_sales_return_item_sum($sales_return_ids){
		$this->db->select("a.return_qty,a.purchase_price,a.total_cost,a.tax_amt,a.tax_type");
		$this->db->select("b.tax");
		
		$this->db->from('db_salesitemsreturn a');
		$this->db->where("a.return_id in (".$sales_return_ids.")");

		$this->db->join('db_tax b','b.id=a.tax_id','left');

		$Q1 = $this->db->get();
		$sum_pur_price = 0;
		$sum_return_price = 0;
		$sum_tax_amt = 0;
		if($Q1->num_rows()>0){
			foreach ($Q1->result() as $res1){
				$return_qty = $res1->return_qty;

				$purchase_price = ($res1->tax_type == 'Exclusive') ? $res1->purchase_price : original_cost($res1->purchase_price,$res1->tax,'Inclusive');


				$return_price = $res1->total_cost;
				$tax_amt = $res1->tax_amt;

				$tot_pur_price = $return_qty * $purchase_price;

				$sum_pur_price+=$tot_pur_price;
				$sum_return_price+=$return_price;
				$sum_tax_amt+=$tax_amt;
			}
		}

		return array(
					'purchase_price' => $sum_pur_price,
					'return_price' => $sum_return_price,
					'tax_amt' => $sum_tax_amt,
				);
	}
	
	
	public function get_profit_by_item(){
		
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
	
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		
		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("c.warehouse_id",$warehouse_id);
		}
		if(store_module() && is_admin()){
			if(!empty($store_id)){ $this->db->where("c.store_id",$store_id);}
			}else{ $this->db->where("c.store_id",get_current_store_id());	
		}
		$this->db->select("a.service_bit, b.tax_amt,b.item_id,a.item_name,COALESCE(sum(b.sales_qty),0) as sales_qty,a.purchase_price,
						COALESCE(SUM(total_cost),0) as total_cost");
		$this->db->from("db_items as a, db_salesitems as b, db_sales as c");
		$this->db->where("c.id=b.sales_id and a.id=b.item_id and c.sales_status='Final'");
		$this->db->where("( c.sales_date>='".$from_date."' and  c.sales_date<='".$to_date."')");
		$this->db->group_by("item_id");
		//echo $this->db->get_compiled_select();exit();
        $q1=$this->db->get();
		
		if($q1->num_rows()>0){
			$i=0;
			$tot_purchase_price=0;
			$tot_sales_cost=0;
			$gross_profit=0;
			$tot_purchase_return_price=0;
			$tot_sales_return_price=0;
			$tot_sales_qty=0;
			$tot_purchase_return_qty=0;
			$tot_sales_return_qty=0;
			$grand_profit=0;
			$tot_net_profit=0;
			foreach ($q1->result() as $res1) {
				/*Purchase Return Quantity*/
				$purchase_return_qty=$this->db->query("
						SELECT COALESCE(sum(return_qty),0) as return_qty
						FROM db_purchaseitemsreturn
						WHERE 
						item_id =".$res1->item_id)->row()->return_qty;

				/*Sales Return Quantity*/
				$q3=$this->db->query("
						SELECT COALESCE(sum(total_cost),0) as total_cost,COALESCE(sum(return_qty),0) as return_qty
						FROM db_salesitemsreturn
						WHERE 
						item_id =".$res1->item_id);
				$sales_return_total_cost=$q3->row()->total_cost;
				$sales_return_qty=$q3->row()->return_qty;
				
				$qty = $res1->sales_qty-$sales_return_qty;
				//$purchase_price =  $res1->purchase_price * $qty;
				$purchase_price = ($res1->service_bit==0) ? $res1->purchase_price * $qty : 0;

				$total_cost = ($res1->total_cost - $sales_return_total_cost);
				//$purchase_return_price = $res1->purchase_price*$purchase_return_qty;
				$profit = $total_cost - $purchase_price;

				$tax_amt = $res1->tax_amt/$res1->sales_qty;

			    $net_profit =$profit-($tax_amt*$qty);

				echo "<tr>";
				echo "<td>".++$i."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".format_qty($qty)."</td>";
				echo "<td style='text-align:right;'>".store_number_format($total_cost)."</td>";
				echo "<td style='text-align:right;'>".(store_number_format($purchase_price))."</td>";
				/*echo "<td style=''>".$purchase_return_qty."</td>";
				echo "<td style='text-align:right;'>".(store_number_format($purchase_return_price))."</td>";*/
				/*echo "<td style=''>".$sales_return_qty."</td>";
				echo "<td style='text-align:right;'>".($sales_return_total_cost)."</td>";*/
				echo "<td style='text-align:right;'>".(store_number_format($profit))."</td>";
				//echo "<td style='text-align:right;'>".(store_number_format($net_profit))."</td>";
				echo "</tr>";
				$tot_purchase_price+=$purchase_price;
				//$tot_purchase_return_price+=$purchase_return_price;
				$tot_sales_cost+=$total_cost;
				//$tot_sales_return_cost+=$sales_return_total_cost;
				//$gross_profit+=(($profit + $purchase_return_price)-$sales_return_total_cost);
				$tot_sales_qty+=($res1->sales_qty-$sales_return_qty);
				$tot_purchase_return_qty+=$purchase_return_qty;
				$tot_sales_return_qty+=$sales_return_qty;
				$gross_profit+=$profit;
				$tot_net_profit+=$net_profit;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='2'><b>Total :</b></td>
					  <td class='text-bold'>".format_qty($tot_sales_qty)."</td>
					  <td class='text-right text-bold'>".(store_number_format($tot_sales_cost))."</td>
					  <td class='text-right text-bold'>".(store_number_format($tot_purchase_price))."</td>
					  
					  <td class='text-right text-bold'>".(store_number_format($gross_profit))."</td>
				  </tr>";
				  /*<td class='text-bold'>".$tot_purchase_return_qty."</td>
					  <td class='text-right text-bold'>".(store_number_format($tot_purchase_return_price))."</td>
					  <td class='text-bold'>".$tot_sales_return_qty."</td>
					  <td class='text-right text-bold'>".(store_number_format($tot_sales_return_cost))."</td>
					  */
		}
		else{
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan=7>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}
	public function get_profit_by_invoice(){
		
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/

		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);
		

		
		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}
		if(store_module() && is_admin()){
			if(!empty($store_id)){ $this->db->where("a.store_id",$store_id);}
			}else{ $this->db->where("a.store_id",get_current_store_id());	
		}
		$this->db->select("a.coupon_amt,a.id,a.sales_date,a.sales_code,b.customer_name");
		$this->db->from("db_sales as a,db_customers as b");
		$this->db->where("a.sales_status='Final' and b.id=a.customer_id");

		$q1=$this->db->get();

		if($q1->num_rows()>0){
			$i=0;
			$tot_purchase_price=0;
			$tot_sales_cost=0;
			$tot_profit=0;
			$net_profit=0;
			$tot_net_profit=0;

			foreach ($q1->result() as $res1) {
				$q2=$this->db->query("SELECT b.sales_qty,COALESCE(SUM(a.purchase_price*sales_qty),0) AS purchase_price, COALESCE(SUM(total_cost),0) AS total_cost FROM db_items AS a, db_salesitems AS b, db_sales AS c WHERE c.id=b.sales_id AND a.id=b.item_id and c.sales_status='Final'
					AND b.sales_id=".$res1->id);

				$q3=$this->db->query("SELECT COALESCE(SUM(a.purchase_price*return_qty),0) AS purchase_price, COALESCE(SUM(total_cost),0) AS total_cost FROM db_items AS a, db_salesitemsreturn AS b, db_salesreturn AS c WHERE c.id=b.return_id AND a.id=b.item_id and c.return_status!='Final'
					AND b.sales_id=".$res1->id);
				$purchase_return_price=$q3->row()->purchase_price;



				//Total price item_purchase_price * qty
				$purchase_price = ($q2->row()->purchase_price-$purchase_return_price);
				//Total price item_sales_price * qty
				$sales_price = ($q2->row()->total_cost-$q3->row()->total_cost);

				$coupon_amt = $res1->coupon_amt;
				$profit = $sales_price - $purchase_price;
				
				/*$sales_tax_amt =$this->db->query("select COALESCE(SUM(tax_amt),0) AS tax_amt from db_salesitems where sales_id=".$res1->id)->row()->tax_amt;
				
				$sales_return_tax_amt =$this->db->query("select COALESCE(SUM(tax_amt),0) AS tax_amt from db_salesitemsreturn where sales_id=".$res1->id)->row()->tax_amt;

				$net_profit = $profit + ($sales_tax_amt-$sales_return_tax_amt);*/
				echo "<tr>";
				echo "<td>".++$i."</td>";
				echo "<td>".$res1->sales_code."</td>";
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td style='text-align:right;'>".store_number_format($sales_price)."</td>";
				echo "<td style='text-align:right;'>".store_number_format($purchase_price)."</td>";
				echo "<td style='text-align:right;'>".store_number_format($profit)."</td>";
				//echo "<td style='text-align:right;'>".(store_number_format($net_profit))."</td>";
				echo "</tr>";
				$tot_purchase_price+=$purchase_price;
				$tot_sales_cost+=$sales_price;
				$tot_profit+=$profit;
				$tot_net_profit+=$net_profit;
			}
			echo "<tr>
					  <td class='text-right text-bold' colspan='4'><b>Total :</b></td>
					  <td class='text-right text-bold'>".(store_number_format($tot_sales_cost))."</td>
					  <td class='text-right text-bold'>".(store_number_format($tot_purchase_price))."</td>
					  <td class='text-right text-bold'>".(store_number_format($tot_profit))."</td>
					  
				  </tr>";
		}
		else{
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan=7>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	public function show_seller_points_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}
		
		$this->db->select("a.created_by,c.seller_points, a.id,a.sales_code,a.sales_date,b.customer_name,b.customer_code,a.grand_total,a.paid_amount,a.store_id");
		$this->db->select("c.sales_qty,d.item_name");
	    
	    
		if($view_all=="no"){
			$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		}
//		$this->db->group_by("c.`item_id`");
		$this->db->order_by("a.`sales_date`,a.sales_code",'desc');
		$this->db->from("db_sales as a");
		$this->db->where("a.`id`= c.`sales_id`");
		$this->db->where("a.`sales_status`= 'Final'");
		$this->db->from("db_items as d");
		$this->db->where("d.`id`= c.`item_id`");
		$this->db->from("db_customers as b");
		$this->db->where("b.`id`= a.`customer_id`");
		$this->db->from("db_salesitems as c");
		if(!empty($created_by)){
			$this->db->where("upper(a.created_by)=upper('$created_by')");
		}
		if($item_id!=''){
			$this->db->where("c.item_id=$item_id");
		}
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_seller_points=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}

				
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".format_qty($res1->sales_qty)."</td>";
				echo "<td>".ucfirst($res1->created_by)."</td>";
				echo "<td>".$res1->seller_points."</td>";
				echo "</tr>";
				$tot_seller_points+=$res1->seller_points;
			}

			$total_columns_count=6;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}

			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".number_format($tot_seller_points,2)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=7;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//report end

	public function show_sales_tax_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.warehouse_id,a.store_id,");
		$this->db->select("a.id,a.sales_code,a.sales_date,b.customer_name,a.grand_total,b.tax_number");
		$this->db->select("a.tot_discount_to_all_amt");
		$this->db->select("a.round_off");
		
		/*if($customer_id!=''){	
			$this->db->where("a.customer_id=$customer_id");
		}*/
		
		$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		
		$this->db->from("db_sales as a");

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		$this->db->from("db_customers as b");
		$this->db->where("b.`id`= a.`customer_id`");
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_price_per_unit=0;
			$tot_discount_amt=0;
			$tot_tax_amt=0;
			$tot_round_off=0;
			$tot_grand_total=0;
			foreach ($q1->result() as $res1) {

				/*Find Tax Amount*/
				$q2 = $this->db->select("COALESCE(sum(tax_amt),0) as tax_amt")
								->select("COALESCE(sum(price_per_unit),0) as price_per_unit")
								->select("COALESCE(sum(discount_amt),0) as discount_amt")
								->where("sales_id",$res1->id)->get("db_salesitems")->row();
				$tax_amt = $q2->tax_amt;
				$discount_amt = $q2->discount_amt;
				$price_per_unit = $q2->price_per_unit;

				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}

				if($res1->store_id==get_current_store_id()){
				echo "<td><a data-toggle='tooltip' target='_blank' title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}

				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->tax_number."</td>";
				echo "<td class='text-right'>".store_number_format($price_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($discount_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($tax_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->round_off)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "</tr>";
				$tot_price_per_unit+=$price_per_unit;
				$tot_discount_amt+=$discount_amt;
				$tot_tax_amt+=$tax_amt;
				$tot_round_off+=$res1->round_off;
				$tot_grand_total+=$res1->grand_total;

			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_price_per_unit)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_discount_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_tax_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_round_off)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=10;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//end

	public function show_purchase_tax_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.warehouse_id,a.store_id,");
		$this->db->select("a.id,a.purchase_code,a.purchase_date,b.supplier_name,a.grand_total,b.tax_number");
		$this->db->select("a.tot_discount_to_all_amt");
		$this->db->select("a.round_off");
		
		/*if($supplier_id!=''){	
			$this->db->where("a.supplier_id=$supplier_id");
		}*/
		
		$this->db->where("(a.purchase_date>='$from_date' and a.purchase_date<='$to_date')");
		
		$this->db->from("db_purchase as a");

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		$this->db->from("db_suppliers as b");
		$this->db->where("b.`id`= a.`supplier_id`");
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_price_per_unit=0;
			$tot_discount_amt=0;
			$tot_tax_amt=0;
			$tot_round_off=0;
			$tot_grand_total=0;
			foreach ($q1->result() as $res1) {

				/*Find Tax Amount*/
				$q2 = $this->db->select("COALESCE(sum(tax_amt),0) as tax_amt")
								->select("COALESCE(sum(price_per_unit),0) as price_per_unit")
								->select("COALESCE(sum(discount_amt),0) as discount_amt")
								->where("purchase_id",$res1->id)->get("db_purchaseitems")->row();
				$tax_amt = $q2->tax_amt;
				$discount_amt = $q2->discount_amt;
				$price_per_unit = $q2->price_per_unit;

				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}

				if($res1->store_id==get_current_store_id()){
				echo "<td><a data-toggle='tooltip' target='_blank' title='View Invoice' href='".base_url("purchase/invoice/$res1->id")."'>".$res1->purchase_code."</a></td>";
				}
				else{
				echo "<td>".$res1->purchase_code."</td>";	
				}

				echo "<td>".show_date($res1->purchase_date)."</td>";
				echo "<td>".$res1->supplier_name."</td>";
				echo "<td>".$res1->tax_number."</td>";
				echo "<td class='text-right'>".store_number_format($price_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($discount_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($tax_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->round_off)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "</tr>";
				$tot_price_per_unit+=$price_per_unit;
				$tot_discount_amt+=$discount_amt;
				$tot_tax_amt+=$tax_amt;
				$tot_round_off+=$res1->round_off;
				$tot_grand_total+=$res1->grand_total;

			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_price_per_unit)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_discount_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_tax_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_round_off)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=10;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//end

	public function show_gstr_1_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.warehouse_id,a.store_id,a.return_bit");
		$this->db->select("a.id,a.sales_code,a.sales_date,b.customer_name,a.grand_total,b.gstin,a.customer_id,b.state_id,a.coupon_amt");
		$this->db->select("a.tot_discount_to_all_amt");
		$this->db->select("a.round_off");
		
		
		$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		
		$this->db->from("db_sales as a");

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		$this->db->join("db_customers as b","b.`id`= a.`customer_id`","left");
		
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_price_per_unit=0;
			$tot_discount_amt=0;
			$tot_tax_amt=0;
			$tot_round_off=0;
			$tot_grand_total=0;

			$tot_cgst_amt=0;
			$tot_sgst_amt=0;
			$tot_igst_amt=0;

			foreach ($q1->result() as $res1) {
				$coupon_amt = $res1->coupon_amt;

				/*Find Tax Amount*/
				$q2 = $this->db->select("COALESCE(sum(tax_amt),0) as tax_amt,tax_id")
								->select("COALESCE(sum(price_per_unit),0) as price_per_unit")
								->select("COALESCE(sum(discount_amt),0) as discount_amt")
								->where("sales_id",$res1->id)->get("db_salesitems")->row();
				$tax_amt = $q2->tax_amt;
				$discount_amt = $q2->discount_amt;
				$discount_amt += $coupon_amt;
				$price_per_unit = $q2->price_per_unit;


				/*Find Customer State*/
				$customer_state='';
				if(!empty($res1->state_id)){
					$customer_state=$this->db->query("select state from db_states where id='".$res1->state_id."'")->row()->state;
				}

				/*Set GST type*/
				$sgst_amt =$cgst_amt=$igst_amt = 0;

				$total_before_tax = $res1->grand_total - $discount_amt - $tax_amt;

				$total_after_tax = $total_before_tax + $tax_amt;

				

				if(empty($customer_state) || (strtoupper($customer_state) == strtoupper(get_store_details($res1->store_id)->state))){
				    $sgst_amt = $cgst_amt = $tax_amt / 2;
				}else{
				    $sgst_amt = $cgst_amt = 0;
				    $igst_amt = $tax_amt;
				}


				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}

				$return_label = ($res1->return_bit==1) ? '<br><span class="text-danger">(Returned)</span>' : '';
				if($res1->store_id==get_current_store_id()){
				echo "<td><a data-toggle='tooltip' target='_blank' title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a>".$return_label."</td>";
				}
				else{
				echo "<td>".$res1->sales_code.$return_label."</td>";	
				}

				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->gstin."</td>";
				echo "<td class='text-right'>".store_number_format($price_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($discount_amt)."</td>";
				echo "<td class='text-right'>".get_tax_details($q2->tax_id)->tax_name."</td>";
				echo "<td class='text-right'>".store_number_format($cgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($sgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($igst_amt)."</td>";

				echo "<td class='text-right'>".store_number_format($res1->round_off)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "</tr>";
				$tot_price_per_unit+=$price_per_unit;
				$tot_discount_amt+=$discount_amt;
				$tot_tax_amt+=$tax_amt;
				$tot_round_off+=$res1->round_off;
				$tot_grand_total+=$res1->grand_total;

				$tot_cgst_amt+=$cgst_amt;
				$tot_sgst_amt+=$sgst_amt;
				$tot_igst_amt+=$igst_amt;


			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_price_per_unit)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_discount_amt)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_cgst_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_sgst_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_igst_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_round_off)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=13;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//end
	
	public function show_gstr_2_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}

		$this->db->select("a.warehouse_id,a.store_id,");
		$this->db->select("a.id,a.purchase_code,a.purchase_date,b.supplier_name,a.grand_total,b.tax_number,a.supplier_id,b.state_id");
		$this->db->select("a.tot_discount_to_all_amt");
		$this->db->select("a.round_off");
		
		
		$this->db->where("(a.purchase_date>='$from_date' and a.purchase_date<='$to_date')");
		
		$this->db->from("db_purchase as a");

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		$this->db->from("db_suppliers as b");
		$this->db->where("b.`id`= a.`supplier_id`");
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_price_per_unit=0;
			$tot_discount_amt=0;
			$tot_tax_amt=0;
			$tot_round_off=0;
			$tot_grand_total=0;

			$tot_cgst_amt=0;
			$tot_sgst_amt=0;
			$tot_igst_amt=0;

			foreach ($q1->result() as $res1) {

				/*Find Tax Amount*/
				$q2 = $this->db->select("COALESCE(sum(tax_amt),0) as tax_amt,tax_id")
								->select("COALESCE(sum(price_per_unit),0) as price_per_unit")
								->select("COALESCE(sum(discount_amt),0) as discount_amt")
								->where("purchase_id",$res1->id)->get("db_purchaseitems")->row();
				$tax_amt = $q2->tax_amt;
				$discount_amt = $q2->discount_amt;
				$price_per_unit = $q2->price_per_unit;


				/*Find supplier State*/
				$supplier_state='';
				if(!empty($res1->state_id)){
					$supplier_state=$this->db->query("select state from db_states where id='".$res1->state_id."'")->row()->state;
				}

				/*Set GST type*/
				$sgst_amt =$cgst_amt=$igst_amt = 0;

				$total_before_tax = $res1->grand_total - $discount_amt - $tax_amt;

				$total_after_tax = $total_before_tax + $tax_amt;

				
				
				if(empty($supplier_state) || (strtoupper($supplier_state) == strtoupper(get_store_details($res1->store_id)->state))){
				    $sgst_amt = $cgst_amt = $tax_amt / 2;
				}else{
				    $sgst_amt = $cgst_amt = 0;
				    $igst_amt = $tax_amt;
				}


				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}

				if($res1->store_id==get_current_store_id()){
				echo "<td><a data-toggle='tooltip' target='_blank' title='View Invoice' href='".base_url("purchase/invoice/$res1->id")."'>".$res1->purchase_code."</a></td>";
				}
				else{
				echo "<td>".$res1->purchase_code."</td>";	
				}

				echo "<td>".show_date($res1->purchase_date)."</td>";
				echo "<td>".$res1->supplier_name."</td>";
				echo "<td>".$res1->tax_number."</td>";
				echo "<td class='text-right'>".store_number_format($price_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($discount_amt)."</td>";
				echo "<td class='text-right'>".get_tax_details($q2->tax_id)->tax_name."</td>";
				echo "<td class='text-right'>".store_number_format($cgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($sgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($igst_amt)."</td>";

				echo "<td class='text-right'>".store_number_format($res1->round_off)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->grand_total)."</td>";
				echo "</tr>";
				$tot_price_per_unit+=$price_per_unit;
				$tot_discount_amt+=$discount_amt;
				$tot_tax_amt+=$tax_amt;
				$tot_round_off+=$res1->round_off;
				$tot_grand_total+=$res1->grand_total;

				$tot_cgst_amt+=$cgst_amt;
				$tot_sgst_amt+=$sgst_amt;
				$tot_igst_amt+=$igst_amt;


			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".store_number_format($tot_price_per_unit)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_discount_amt)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_cgst_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_sgst_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_igst_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_round_off)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=13;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//end


	public function show_customer_orders(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		
		$within_date = (!empty($within_date)) ? system_fromatted_date($within_date) : '';
	
		$this->db->select("*");
		$this->db->from("db_customers");
	    if(!empty($store_id)){
			$this->db->where("store_id",$store_id);
		}
		if($customer_id!=''){
			$this->db->where("id=$customer_id");
		}
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();

		//print_r($q1);exit;
		if($q1->num_rows()>0){

			$i=0;
			foreach ($q1->result() as $res1) {

				$this->db->select("sales_date,sales_code,id")
						->from("db_sales")
						->where("customer_id",$res1->id);
				if(!empty($within_date)){
					$this->db->where("sales_date<='$within_date'");
				}
				$this->db->order_by("sales_date",'asc');
						/*echo "<br> ".$this->db->get_compiled_select();
						continue;*/
						
						$q2 = $this->db->get();

						//echo "<br> ".$q2->num_rows();
						//continue;
				if($q2->num_rows()>0){
					
						//$res2 = $q2->row();

						foreach($q2->result() as $res2){
							$date_difference = date_difference($res2->sales_date,date("Y-m-d"));

							echo "<tr>";
							echo "<td>".++$i."</td>";
							if(store_module() && is_admin()){
								echo "<td>".get_store_name($res1->store_id)."</td>";	
							}
							echo "<td>".$res1->customer_name."</td>";
							echo "<td>".show_date($res2->sales_date)."</td>";
							
							if($store_id==get_current_store_id()){
							echo "<td><a title='View Invoice' href='".base_url("sales/invoice/$res2->id")."'>".$res2->sales_code."</a></td>";
							}
							else{
							echo "<td>".$res2->sales_code."</td>";	
							}
							echo "<td>".$date_difference."</td>";
							echo "</tr>";
						}
						
				}
				

			}

		}
		else{
			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	public function show_delivery_sheet(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		/*$from_date=date("Y-m-d",strtotime($from_date));
		$to_date=date("Y-m-d",strtotime($to_date));*/
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		$this->db->select("a.id,a.sales_code,a.sales_date,b.customer_name,a.grand_total,a.paid_amount,a.store_id,a.created_time");
	    
		if($customer_id!=''){
			
			$this->db->where("a.customer_id=$customer_id");
		}
		
		$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		
		$this->db->where("b.`id`= a.`customer_id`");
		$this->db->from("db_sales as a");
		$this->db->where("a.`sales_status`= 'Final'");

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		$this->db->from("db_customers as b");
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_grand_total=0;
			$tot_paid_amount=0;
			$due_amount=0;
			$tot_due_amount=0;
			$tot_sales_qty=0;
			$tot_return_qty=0;
			foreach ($q1->result() as $res1) {

				$tot_grand_total+=$res1->grand_total;
				$tot_paid_amount+=$res1->paid_amount;
				

				//Find the Sales items of the item
				$q3 = $this->db->select("coalesce(sum(sales_qty),0) as sales_qty")
								->where("sales_id",$res1->id)
								->get("db_salesitems")->row();
				$sales_qty = $q3->sales_qty;

				$q4 = $this->db->select("coalesce(sum(grand_total),0) as grand_total")
								->select("coalesce(sum(paid_amount),0) as paid_amount")
								->where("sales_id",$res1->id)
								->get("db_salesreturn")->row();

				//Find the return of the item
				$q2 = $this->db->select("coalesce(sum(return_qty),0) as return_qty")
								->where("sales_id",$res1->id)
								->get("db_salesitemsreturn")->row();

				$return_qty = $q2->return_qty;

				$return_tot = $q4->grand_total;

				$invoices_tot = $res1->grand_total - $return_tot;

				$due_amount=($res1->grand_total-$res1->paid_amount);
				$due_amount-=($q4->paid_amount);
				$tot_due_amount+=$due_amount;


				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->created_time."</td>";

				if($store_id==get_current_store_id()){
				echo "<td><a title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}
				
				echo "<td>".$res1->customer_name."</td>";
				echo "<td class='text-left'>".format_qty($sales_qty)."</td>";
				echo "<td class='text-left'>".format_qty($return_qty)."</td>";
				echo "<td class='text-right'>".store_number_format($invoices_tot)."</td>";
				echo "<td class='text-right'>".store_number_format($due_amount)."</td>";
				
				echo "</tr>";

				$tot_return_qty+=$return_qty;
				$tot_sales_qty+=$sales_qty;
				

			}

			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_sales_qty)."</td>
					  <td class='text-left text-bold'>".format_qty($tot_return_qty)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_grand_total)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_due_amount)."</td>
					  
				  </tr>";
		}
		else{
			$total_columns_count=9;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}


	public function show_load_sheet(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		
		$this->db->select("a.id,a.sales_code,a.sales_date,b.customer_name,b.customer_code,a.grand_total,a.paid_amount,a.store_id");
		$this->db->select("sum(c.sales_qty) as sales_qty");
		$this->db->select("d.item_name,d.brand_id");
	    
	    
			$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		$this->db->order_by("a.`sales_date`,a.sales_code",'desc');
		$this->db->from("db_sales as a");
		$this->db->where("a.`id`= c.`sales_id`");
		$this->db->where("a.`sales_status`= 'Final'");
		$this->db->from("db_items as d");
		$this->db->where("d.`id`= c.`item_id`");
		$this->db->from("db_customers as b");
		$this->db->where("b.`id`= a.`customer_id`");
		$this->db->from("db_salesitems as c");
		$this->db->group_by("c.item_id");
		
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			
			$tot_qty=0;
			foreach ($q1->result() as $res1) {

				$brand_name = (!empty($res1->brand_id)) ? get_brand_details($res1->brand_id)->brand_name : '';

				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
							
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$brand_name."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".format_qty($res1->sales_qty)."</td>";
				echo "</tr>";

				$tot_qty+=$res1->sales_qty;

			}

			$total_columns_count=4;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
		

			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_qty)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=5;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}
	public function show_sales_gst_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		/*if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}*/

		if(!empty($customer_id)){
			$this->db->where("a.customer_id",$customer_id);
		}
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}

		$this->db->select("
							a.store_id,
							a.id,
							a.sales_code,
							a.sales_date,
							a.customer_id,
							a.round_off,
							a.coupon_amt,

							b.state_id,
							b.gstin,
							b.customer_name,

							c.item_id,
							c.sales_qty,
							c.price_per_unit,
							c.tax_type,
							c.tax_id,
							c.tax_amt,
							c.discount_type,
							c.discount_input,
							c.discount_amt,
							c.unit_total_cost,
							c.total_cost,

							d.item_name,
							d.hsn,

							t.tax_name,
							t.tax,

							s.state,
						");
		
		
		$this->db->from("db_sales as a");
		$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");
		//Customer - Join
		$this->db->join("db_customers as b","b.`id`= a.`customer_id`","left");
		//Sales Items - Join
		$this->db->join("db_salesitems as c","c.`sales_id`= a.id","left");
		//Item Details - Join
		$this->db->join("db_items as d","d.`id`= c.item_id","left");
		//Tax - Join
		$this->db->join("db_tax as t","t.`id`= c.tax_id","left");
		//Store - Join
		$this->db->join("db_store as s","s.`id`= a.store_id","left");

		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_price_per_unit=0;
			$tot_discount_amt=0;
			$tot_tax_amt=0;
			$tot_round_off=0;
			$tot_total_cost=0;
			$tot_sales_qty=0;
			$tot_taxable=0;

			$tot_cgst_amt = $tot_sgst_amt = $tot_igst_amt = 0;

			foreach ($q1->result() as $res1) {
				//Coupon amount
				$coupon_amt = $res1->coupon_amt;
				//Taxable
				$taxable = $res1->price_per_unit-$res1->tax_amt;

				//Customer State
				/*Find Customer State*/
				$customer_state='';
				if(!empty($res1->state_id)){
					$customer_state=$this->db->query("select state from db_states where id='".$res1->state_id."'")->row()->state;
				}

				//validate GST Type
				$gst_type = (strtoupper($customer_state)==strtoupper($res1->state)) ? "GST" : "IGST";

				//Set GST type
				//if($gst_type=='GST'){//Within state
				if(empty($customer_state) || (strtoupper($customer_state) == strtoupper(get_store_details($res1->store_id)->state))){
					$gst_tax_per = ($res1->tax/2);
				    $cgst_amt = ($res1->tax_amt / 2);
				    $sgst_amt = $cgst_amt;
				    $igst_amt =0;
				    $igst_tax_per = 0;
				}else{//IGST
					$igst_tax_per = $res1->tax;
				    $igst_amt = $res1->tax_amt;
				    $cgst_amt = 0;
				    $sgst_amt = 0;
				    $gst_tax_per=0;
				}


				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}

				if($res1->store_id==get_current_store_id()){
				echo "<td><a data-toggle='tooltip' target='_blank' title='View Invoice' href='".base_url("sales/invoice/$res1->id")."'>".$res1->sales_code."</a></td>";
				}
				else{
				echo "<td>".$res1->sales_code."</td>";	
				}

				echo "<td>".$res1->customer_name."</td>";
				echo "<td>".$res1->gstin."</td>";
				echo "<td>".show_date($res1->sales_date)."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".format_qty($res1->sales_qty)."</td>";
				echo "<td>".$res1->hsn."</td>";
				echo "<td class='text-right'>".store_number_format($res1->price_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->discount_amt+$coupon_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($taxable)."</td>";
				echo "<td class='text-right'>".$res1->tax_name."</td>";
				echo "<td class='text-right'>".store_number_format($gst_tax_per)."</td>";
				echo "<td class='text-right'>".store_number_format($cgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($gst_tax_per)."</td>";
				echo "<td class='text-right'>".store_number_format($sgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($igst_tax_per)."</td>";
				echo "<td class='text-right'>".store_number_format($igst_amt)."</td>";

				echo "<td class='text-right'>".store_number_format($res1->round_off)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->total_cost)."</td>";
				echo "</tr>";
				$tot_price_per_unit+=$res1->price_per_unit;
				$tot_discount_amt+=$res1->discount_amt+$coupon_amt;
				$tot_tax_amt+=$res1->tax_amt;
				$tot_round_off+=$res1->round_off;
				$tot_total_cost+=$res1->total_cost;
				$tot_sales_qty+=$res1->sales_qty;
				$tot_taxable+=$taxable;

				$tot_cgst_amt+=$cgst_amt;
				$tot_sgst_amt+=$sgst_amt;
				$tot_igst_amt+=$igst_amt;


			}

			$total_columns_count=6;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-right text-bold'>".format_qty($tot_sales_qty)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_price_per_unit)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_discount_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_taxable)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_cgst_amt)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_sgst_amt)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_igst_amt)."</td>

					  <td class='text-right text-bold'>".store_number_format($tot_round_off)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_total_cost)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=20;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//end

	public function show_purchase_gst_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);


		/*if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
				$this->db->where("a.warehouse_id",$warehouse_id);
		}*/

		if(!empty($supplier_id)){
			$this->db->where("a.supplier_id",$supplier_id);
		}
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}

		$this->db->select("
							a.store_id,
							a.id,
							a.purchase_code,
							a.purchase_date,
							a.supplier_id,
							a.round_off,

							b.state_id,
							b.gstin,
							b.supplier_name,

							c.item_id,
							c.purchase_qty,
							c.price_per_unit,
							c.tax_type,
							c.tax_id,
							c.tax_amt,
							c.discount_type,
							c.discount_input,
							c.discount_amt,
							c.unit_total_cost,
							c.total_cost,

							d.item_name,
							d.hsn,

							t.tax_name,
							t.tax,

							s.state,
						");
		
		
		$this->db->from("db_purchase as a");
		$this->db->where("(a.purchase_date>='$from_date' and a.purchase_date<='$to_date')");
		//supplier - Join
		$this->db->join("db_suppliers as b","b.`id`= a.`supplier_id`","left");
		//purchase Items - Join
		$this->db->join("db_purchaseitems as c","c.`purchase_id`= a.id","left");
		//Item Details - Join
		$this->db->join("db_items as d","d.`id`= c.item_id","left");
		//Tax - Join
		$this->db->join("db_tax as t","t.`id`= c.tax_id","left");
		//Store - Join
		$this->db->join("db_store as s","s.`id`= a.store_id","left");

		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			$tot_price_per_unit=0;
			$tot_discount_amt=0;
			$tot_tax_amt=0;
			$tot_round_off=0;
			$tot_total_cost=0;
			$tot_purchase_qty=0;
			$tot_taxable=0;

			$tot_cgst_amt = $tot_sgst_amt = $tot_igst_amt = 0;

			foreach ($q1->result() as $res1) {

				//Taxable
				$taxable = $res1->price_per_unit-$res1->tax_amt;

				//validate GST Type
				$supplier_state='';
				if(!empty($res1->state_id)){
					$supplier_state=$this->db->query("select state from db_states where id='".$res1->state_id."'")->row()->state;
				}

				//$gst_type = (strtoupper($supplier_state)==strtoupper($res1->state)) ? "GST" : "IGST";

				//Set GST type
				//if($gst_type=='GST'){//Within state

				if(empty($supplier_state) || (strtoupper($supplier_state) == strtoupper(get_store_details($res1->store_id)->state))){
					$gst_tax_per = ($res1->tax/2);
				    $cgst_amt = ($res1->tax_amt / 2);
				    $sgst_amt = $cgst_amt;
				    $igst_amt =0;
				    $igst_tax_per =0;
				}else{//IGST
					$igst_tax_per = $res1->tax;
				    $igst_amt = $res1->tax_amt;
				    $cgst_amt = 0;
				    $sgst_amt = 0;
				    $gst_tax_per = 0;
				}


				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}

				if($res1->store_id==get_current_store_id()){
				echo "<td><a data-toggle='tooltip' target='_blank' title='View Invoice' href='".base_url("purchase/invoice/$res1->id")."'>".$res1->purchase_code."</a></td>";
				}
				else{
				echo "<td>".$res1->purchase_code."</td>";	
				}

				echo "<td>".$res1->supplier_name."</td>";
				echo "<td>".$res1->gstin."</td>";
				echo "<td>".show_date($res1->purchase_date)."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".format_qty($res1->purchase_qty)."</td>";
				echo "<td>".$res1->hsn."</td>";
				echo "<td class='text-right'>".store_number_format($res1->price_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->discount_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($taxable)."</td>";
				echo "<td class='text-right'>".$res1->tax_name."</td>";
				echo "<td class='text-right'>".store_number_format($gst_tax_per)."</td>";
				echo "<td class='text-right'>".store_number_format($cgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($gst_tax_per)."</td>";
				echo "<td class='text-right'>".store_number_format($sgst_amt)."</td>";
				echo "<td class='text-right'>".store_number_format($igst_tax_per)."</td>";
				echo "<td class='text-right'>".store_number_format($igst_amt)."</td>";

				echo "<td class='text-right'>".store_number_format($res1->round_off)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->total_cost)."</td>";
				echo "</tr>";
				$tot_price_per_unit+=$res1->price_per_unit;
				$tot_discount_amt+=$res1->discount_amt;
				$tot_tax_amt+=$res1->tax_amt;
				$tot_round_off+=$res1->round_off;
				$tot_total_cost+=$res1->total_cost;
				$tot_purchase_qty+=$res1->purchase_qty;
				$tot_taxable+=$taxable;

				$tot_cgst_amt+=$cgst_amt;
				$tot_sgst_amt+=$sgst_amt;
				$tot_igst_amt+=$igst_amt;


			}

			$total_columns_count=6;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_purchase_qty)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_price_per_unit)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_discount_amt)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_taxable)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_cgst_amt)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_sgst_amt)."</td>
					  <td class='text-right text-bold'></td>
					  <td class='text-right text-bold'>".store_number_format($tot_igst_amt)."</td>
					  
					  <td class='text-right text-bold'>".store_number_format($tot_round_off)."</td>
					  <td class='text-right text-bold'>".store_number_format($tot_total_cost)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=20;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}//end


	public function show_return_items_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);
		

		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		
		$this->db->select("a.id,a.return_date,a.return_code,b.customer_name,a.store_id,a.return_status");
		$this->db->from("db_salesreturn a");
		$this->db->where("(a.return_date>='$from_date' and a.return_date<='$to_date')");
		if(!empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		$this->db->join("db_customers b","b.`id`= a.`customer_id`",'left');
		$this->db->order_by("a.`return_date`,a.return_code",'desc');
		//echo $this->db->get_compiled_select();exit();
		$q1 = $this->db->get();
		if($q1->num_rows()>0){
			$tot_total_cost=0;
			
			foreach ($q1->result() as $res1) {
				$sales_id = $res1->id;

				$this->db->select(" c.total_cost,
									c.return_qty,
									d.item_name,
								");
				//Join
				$this->db->from("db_salesitemsreturn c");
				$this->db->where("c.return_id",$sales_id);
				if($item_id!=''){
					$this->db->where("c.item_id=$item_id");
				}
				$this->db->join("db_items d","d.`id`= c.`item_id`","left");
				//echo $this->db->get_compiled_select();exit();
				$q2=$this->db->get();

					if($q2->num_rows()>0){
					$i=0;
					
					
					foreach ($q2->result() as $res2) {
							echo "<tr>";
							echo "<td>".++$i."</td>";
							if(store_module() && is_admin()){
								echo "<td>".get_store_name($res1->store_id)."</td>";	
							}
							if($store_id==get_current_store_id()){
							echo "<td><a title='View Invoice' href='".base_url("sales_return/invoice/$res1->id")."'>".$res1->return_code."</a></td>";
							}
							else{
							echo "<td>".$res1->return_code."</td>";	
							}

							
							echo "<td>".show_date($res1->return_date)."</td>";
							echo "<td>".$res1->return_status."</td>";
							echo "<td>".$res1->customer_name."</td>";
							echo "<td>".$res2->item_name."</td>";
							echo "<td>".format_qty($res2->return_qty)."</td>";
							echo "<td class='text-right'>".store_number_format($res2->total_cost)."</td>";
							echo "</tr>";
							
							$tot_total_cost+=$res2->total_cost;

					}// foreach $res2
					//Print Total
					$total_columns_count=6;
					if(store_module() && is_admin()){
						$total_columns_count ++;
					}
					if(warehouse_module() && warehouse_count()>0){
						$total_columns_count ++;
					}

					
				}//num_rows $q2
				else{
					echo "No Records Found";
				}


			}//foreach $res1

			echo "<tr>
							  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
							  <td class='text-right text-bold'>".store_number_format($tot_total_cost)."</td>
							  
						  </tr>";

		}//num_rows
		else{
			$total_columns_count=8;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
	}

	public function sales_and_payments_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		$from_date = (!empty($from_date)) ? system_fromatted_date($from_date) : '';
		$to_date = (!empty($to_date)) ? system_fromatted_date($to_date) : '';

		$i=0;
              $tot_qty=0;
              $tot_sales_price=0;
              
              $tot_total_cost=0;
              $tot_payment=0;

              $str1= " SELECT 
	              					a.id as id,
	              					a.`sales_date` AS tr_date,
	              					'invoice' as tr_type,
	              					a.sales_code as tr_code,
	              					a.`created_time` AS created_time
              					FROM 
              						db_sales a";
  						$str1.=" WHERE 
						a.customer_id =".$customer_id ;
              		if(!empty($from_date) && !empty($to_date)){
              			$str1.="  and
									(sales_date>='".$from_date."' and sales_date<='".$to_date."') ";
              		}
              		$str1.=" UNION ";

              		$str1.= " SELECT 
									b.id as id, 
									b.`payment_date` AS tr_date,
									'payments' as tr_type,
									b.payment_code as tr_code,
									b.`created_time` AS created_time
								FROM 
									`db_salespayments` b";
									$str1.=" WHERE 
						b.customer_id =".$customer_id ;
              		if(!empty($from_date) && !empty($to_date)){
              			$str1.="  and
									(b.payment_date>='".$from_date."' and b.payment_date<='".$to_date."') ";
              		}

              		//$str1.=" ORDER BY `tr_date`,`id`,`created_time` ";
              		$str1.=" ORDER BY `tr_date`,`created_time` ";
              	//echo $str1;exit;
              $q1 = $this->db->query($str1);

        	$tot_qty = 0;
        	$tot_total_cost = 0;
        	$tot_payment=0;
        	$tot_bal=0;
        	if($q1->num_rows()>0){
        		foreach ($q1->result() as $res2){

        			$details = ($res2->tr_type ==	'invoice') ? 
        							get_sales_details($res2->id)
        							: get_sales_payment_details($res2->id);
        			
        			if($res2->tr_type=='invoice'){
        				$this->db->select("b.item_name");
	        			$this->db->from("db_salesitems a");
	        			$this->db->where("a.sales_id=",$res2->id);	
	        			$this->db->join("db_items b","b.id=a.item_id","left");
	        			//echo $this->db->get_compiled_select();
	        			$item_name = $this->db->get()->row()->item_name;	

        			}
        			else{
        				$item_name = '';
        			}
        			

        			if($res2->tr_type=='payments'){
        				if($details->payment==0){
        					continue;
        				}

        			}

        			
        			echo "<tr>";  
	                  echo "<td colspan='1' class='text-center'>".++$i."</td>";

	                  echo "<td colspan='1'>";
	                    echo show_date($res2->tr_date);
	                  echo "</td>";

	                  echo "<td colspan='1'>";
	                  $str2 = '';
	                  if($res2->tr_type!='invoice'){
	                  	$str2 = "<br><b>Bill Ref.No :<b>".get_sales_details($details->sales_id)->reference_no;
	                  }
	                    echo ($res2->tr_type=='invoice') ? $res2->tr_code : 'Receive'.$str2;
	                  echo "</td>";

	                  echo "<td colspan='1'>";
	                  	//echo $res2->tr_code;
	                  	//Show sales code for the payment
	                  	if($res2->tr_type=='payments'){
	                  		echo get_sales_details(get_sales_payment_details($res2->id)->sales_id)->sales_code;
	                  	}
		                  if(!empty(isset($details->reference_no))){
		                  	echo $details->reference_no."<br>";          
		                  }
	                  echo "</td>";

	                  echo "<td colspan='1'>";
	                  	//echo $res2->item_name;  
	                  	if($res2->tr_type=='payments')  {
	                  	  echo "Payment Type: ".$details->payment_type."<br>";
		                  if(!empty(trim($details->payment_note))){
			                  echo "<b>Note:</b><i>".nl2br($details->payment_note)."</i>";
		                  }	
	                  	}
	                  	else{
	                  		echo "<b>Item:</b><i>".$item_name."</i><br>";
	                  	}
	                  	
	                  echo "</td>";

	                  echo "<td colspan='1'>";
	                  	if($res2->tr_type=='invoice'){
	                  		$res3 = $this->db->query("select coalesce(sum(sales_qty),0) as tot_sales_qty from db_salesitems where sales_id=".$res2->id);
	                    	echo format_qty($res3->row()->tot_sales_qty) ;
	                  	}
	                  	else{
	                  		echo format_qty(0);
	                  	}
	                  echo "</td>";

	                  echo "<td class='text-right' colspan='1'>";
	                  	if($res2->tr_type=='invoice'){
	                  		echo store_number_format($details->grand_total);
	                  	}
	                  	else{
	                  		echo store_number_format(0);
	                  	}
	                    
	                  echo "</td>";

	                  echo "<td class='text-right' colspan='1'>";
	                    if($res2->tr_type=='payments'){
	                  		echo store_number_format($details->payment);
	                  	}
	                  	else{
	                  		echo store_number_format(0);
	                  	}
	                  echo "</td>";

	                  /*echo "<td  class='text-right' colspan='1'>";
	                    echo store_number_format(0);
	                  echo "</td>";*/

	                  echo "<td  class='text-right' colspan='1'>";
	                  		$tot_bal = ($res2->tr_type=='invoice') ? $details->grand_total+$tot_bal : $tot_bal-$details->payment ;
	                    echo store_number_format($tot_bal);
	                  echo "</td>";

	               
	                  echo "</tr>";
	                  if($res2->tr_type=='invoice'){
	                  	$tot_qty +=$res3->row()->tot_sales_qty;
	                  	$tot_total_cost +=$details->grand_total;
	                  }
	                  else{
	                  	$tot_payment +=$details->payment;
	                  }
                  	

        		}
        	}
        	else{
        		echo "<tr><td colspan='8'>No Records Found</td></tr>";
        	}

        	/*************************************/
        	//Total
              echo "<tr>";
              	echo "<td class='text-right' colspan='5'>";
              		echo "Total";
              	echo "</td>";
              	echo "<td>";
              		echo format_qty($tot_qty);
              	echo "</td>";
              	echo "<td class='text-right'>";
              		echo store_number_format($tot_total_cost);
              	echo "</td>";
              	echo "<td class='text-right'>";
              		echo store_number_format($tot_payment);
              	echo "</td>";
              	/*echo "<td class='text-right'>";
              		echo store_number_format($tot_total_cost-$tot_payment);
              	echo "</td>";*/
              	echo "<td class='text-right'>";
              		echo store_number_format($tot_bal);
              	echo "</td>";

              echo "</tr>";


	}

	public function show_stock_transfer_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		$item_id = isset($item_id) ? $item_id : "";

		$this->db->where("a.warehouse_from",$from_warehouse);
		$this->db->where("a.warehouse_to",$to_warehouse);
		
		$this->db->select("a.id,a.transfer_date,a.created_by,a.store_id,e.category_name,f.brand_name");
		$this->db->select("c.transfer_qty,d.item_name");
		$this->db->select("w1.warehouse_name as from_warehouse");
		$this->db->select("w2.warehouse_name as to_warehouse");
	    
	    
		$this->db->from("db_stocktransfer as a");
		
		
		$this->db->where("(a.transfer_date>='$from_date' and a.transfer_date<='$to_date')");
		
		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}
		$this->db->order_by("a.`transfer_date`",'desc');

		$str = "" ; 


		if($item_id!=''){
			$str = " and c.item_id=$item_id"; 
		}
		$this->db->join("db_stocktransferitems as c","c.stocktransfer_id = a.id $str","left");

		
		$this->db->join("db_items as d","d.`id`= c.`item_id`","left");

		$str = "" ; 
		if($category_id!=''){
			$str = " and e.id=$category_id"; 
		}

		$this->db->join("db_category as e","e.`id`= d.`category_id` $str","right");
			
		$str ="";

		if($brand_id!=''){
			$str = " and f.id=$brand_id"; 
		}

		$this->db->join("db_brands as f","f.`id`= d.`brand_id` $str","right");

		$this->db->join("db_warehouse as w1","w1.`id`= c.`warehouse_from`","right");
		$this->db->join("db_warehouse as w2","w2.`id`= c.`warehouse_to`","right");


		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			
			$tot_transfer_qty=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				
				echo "<td>".show_date($res1->transfer_date)."</td>";
				echo "<td>".$res1->from_warehouse."</td>";
				echo "<td>".$res1->to_warehouse."</td>";
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".$res1->category_name."</td>";
				echo "<td>".$res1->brand_name."</td>";
				echo "<td>".format_qty($res1->transfer_qty)."</td>";
				
				echo "</tr>";
				$tot_transfer_qty+=$res1->transfer_qty;
				
			}

			$total_columns_count=7;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			

			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_sales_qty)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=9;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}


	public function show_sales_summary_report(){
		$from_date = $this->input->post('from_date', TRUE);
		$to_date = $this->input->post('to_date', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$item_id = $this->input->post('item_id', TRUE);
		$brand_id = $this->input->post('brand_id', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$view_all = $this->input->post('view_all', TRUE);
		$created_by = $this->input->post('created_by', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$within_date = $this->input->post('within_date', TRUE);
		$from_warehouse = $this->input->post('from_warehouse', TRUE);
		$to_warehouse = $this->input->post('to_warehouse', TRUE);
		$item_type = $this->input->post('item_type', TRUE);

		
		$from_date = system_fromatted_date($from_date);
		$to_date = system_fromatted_date($to_date);

		$item_id = isset($item_id) ? $item_id : "";
		$item_type = isset($item_type) ? $item_type : "";

		if(warehouse_module() && warehouse_count()>0 && !empty($warehouse_id)){
			$this->db->where("a.warehouse_id",$warehouse_id);
		}
		
		$this->db->select("a.id,a.store_id,e.category_name");
		$this->db->select("sum(c.sales_qty) as sales_qty,d.item_name");
	    
	    
		$this->db->from("db_sales as a");
		
		$this->db->where("a.`sales_status`= 'Final'");
		
		
		$this->db->where("(a.sales_date>='$from_date' and a.sales_date<='$to_date')");

		if(!empty($store_id)){
			$this->db->where("a.store_id",$store_id);
		}

		$this->db->group_by("c.`item_id`");

		$str = "" ; 
		if($item_id!=''){
			$str = " and c.item_id=$item_id"; 
		}
		$this->db->join("db_salesitems as c","c.sales_id = a.id $str","left");

		$this->db->join("db_items as d","d.`id`= c.`item_id`","left");

		if(!empty($item_type)){
			$this->db->where("service_bit", (($item_type=="Items") ? 0 : 1) );
		}

		$str = "" ; 
		if($category_id!=''){
			$str = " and e.id=$category_id"; 
		}

		$this->db->join("db_category as e","e.`id`= d.`category_id` $str","right");
		
		//echo $this->db->get_compiled_select();exit();
		
		$q1=$this->db->get();
		if($q1->num_rows()>0){
			$i=0;
			
			$tot_sales_qty=0;
			foreach ($q1->result() as $res1) {
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){
					echo "<td>".get_store_name($res1->store_id)."</td>";	
				}
				
				echo "<td>".$res1->item_name."</td>";
				echo "<td>".$res1->category_name."</td>";
				echo "<td>".format_qty($res1->sales_qty)."</td>";
				
				echo "</tr>";
				$tot_sales_qty+=$res1->sales_qty;
				
			}

			$total_columns_count=2;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}

			echo "<tr>
					  <td class='text-right text-bold' colspan='$total_columns_count'><b>Total :</b></td>
					  <td class='text-left text-bold'>".format_qty($tot_sales_qty)."</td>
				  </tr>";
		}
		else{
			$total_columns_count=3;
			if(store_module() && is_admin()){
				$total_columns_count ++;
			}
			if(warehouse_module() && warehouse_count()>0){
				$total_columns_count ++;
			}
			echo "<tr>";
			echo "<td class='text-center text-danger' colspan='$total_columns_count'>No Records Found</td>";
			echo "</tr>";
		}
		
	    exit;
	}

	/* ===================== PRODUCTION REPORTS ===================== */
	public function show_production_summary_report(){
		$store_id = $this->input->post('store_id', TRUE);
		$from_date = get_date_format($this->input->post('from_date'),'Y-m-d');
		$to_date = get_date_format($this->input->post('to_date'),'Y-m-d');
		$status = $this->input->post('status', TRUE);

		$this->db->select('a.*, COUNT(b.id) as item_count');
		$this->db->from('db_production_batches a');
		$this->db->join('db_production_batch_items b', 'b.batch_id = a.id', 'left');
		if(!empty($store_id)){ $this->db->where('a.store_id', $store_id); }
		if(!empty($from_date)){ $this->db->where('a.scheduled_date >=', $from_date); }
		if(!empty($to_date)){ $this->db->where('a.scheduled_date <=', $to_date); }
		if(!empty($status)){ $this->db->where('a.status', $status); }
		$this->db->group_by('a.id');
		$this->db->order_by('a.scheduled_date', 'DESC');
		$q1 = $this->db->get();

		$i=0;
		$tot_qty=0;
		if($q1->num_rows()>0){
			foreach($q1->result() as $res1){
				$tot_qty += (float)$res1->quantity;
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){ echo "<td>".get_store_name($res1->store_id)."</td>"; }
				echo "<td>".htmlspecialchars($res1->batch_code)."</td>";
				echo "<td>".htmlspecialchars($res1->batch_name)."</td>";
				echo "<td><span class='label label-".Production_batches_model::status_badge($res1->status)."'>".Production_batches_model::status_label($res1->status)."</span></td>";
				echo "<td>".show_date($res1->scheduled_date)."</td>";
				echo "<td>".($res1->scheduled_time ?: '-')."</td>";
				echo "<td class='text-right'>".format_qty($res1->quantity)."</td>";
				echo "<td>".htmlspecialchars($res1->equipment ?: '-')."</td>";
				echo "<td>".htmlspecialchars($res1->staff_name ?: '-')."</td>";
				echo "</tr>";
			}
			echo "<tr><td class='text-right text-bold' colspan='".((store_module() && is_admin())?9:8)."'><b>Total Quantity:</b></td><td class='text-left text-bold'>".format_qty($tot_qty)."</td><td></td></tr>";
		} else {
			$colspan = (store_module() && is_admin()) ? 10 : 9;
			echo "<tr><td class='text-center text-danger' colspan='$colspan'>No Records Found</td></tr>";
		}
		exit;
	}

	public function show_ingredient_usage_report(){
		$store_id = $this->input->post('store_id', TRUE);
		$from_date = get_date_format($this->input->post('from_date'),'Y-m-d');
		$to_date = get_date_format($this->input->post('to_date'),'Y-m-d');
		$item_id = $this->input->post('item_id', TRUE);

		$this->db->select('b.item_name as ingredient_name, b.item_code, ri.qty as recipe_qty, ri.cost_per_unit, ri.wastage_pct, r.name as recipe_name, r.yield_qty, r.yield_unit, pb.batch_code, pb.scheduled_date, pbi.quantity as produce_qty, pbi.item_name as product_name');
		$this->db->from('db_production_batches pb');
		$this->db->join('db_production_batch_items pbi', 'pbi.batch_id = pb.id');
		$this->db->join('db_recipes r', 'r.id = pbi.item_id AND pbi.item_type = "recipe_product"');
		$this->db->join('db_recipe_ingredients ri', 'ri.recipe_id = r.id');
		$this->db->join('db_items b', 'b.id = ri.item_id');
		$this->db->where('pb.status', 'completed');
		if(!empty($store_id)){ $this->db->where('pb.store_id', $store_id); }
		if(!empty($from_date)){ $this->db->where('pb.scheduled_date >=', $from_date); }
		if(!empty($to_date)){ $this->db->where('pb.scheduled_date <=', $to_date); }
		if(!empty($item_id)){ $this->db->where('ri.item_id', $item_id); }
		$this->db->order_by('pb.scheduled_date', 'DESC');
		$q1 = $this->db->get();

		$i=0;
		$tot_used=0;
		$tot_cost=0;
		if($q1->num_rows()>0){
			foreach($q1->result() as $res1){
				$scale = (float)$res1->produce_qty / (float)$res1->yield_qty;
				$used_qty = (float)$res1->recipe_qty * $scale;
				$line_cost = $used_qty * (float)$res1->cost_per_unit;
				$tot_used += $used_qty;
				$tot_cost += $line_cost;

				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){ echo "<td>".get_store_name($this->input->post('store_id', TRUE) ?: get_current_store_id())."</td>"; }
				echo "<td>".htmlspecialchars($res1->ingredient_name)."</td>";
				echo "<td>".htmlspecialchars($res1->item_code ?: '-')."</td>";
				echo "<td>".htmlspecialchars($res1->recipe_name)."</td>";
				echo "<td>".htmlspecialchars($res1->batch_code)."</td>";
				echo "<td>".show_date($res1->scheduled_date)."</td>";
				echo "<td class='text-right'>".format_qty($used_qty)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->cost_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($line_cost)."</td>";
				echo "</tr>";
			}
			$colspan = (store_module() && is_admin()) ? 9 : 8;
			echo "<tr><td class='text-right text-bold' colspan='$colspan'><b>Total:</b></td><td class='text-left text-bold'>".format_qty($tot_used)."</td><td></td><td class='text-left text-bold'>".store_number_format($tot_cost)."</td></tr>";
		} else {
			$colspan = (store_module() && is_admin()) ? 10 : 9;
			echo "<tr><td class='text-center text-danger' colspan='$colspan'>No Records Found</td></tr>";
		}
		exit;
	}

	public function show_recipe_costing_report(){
		$store_id = $this->input->post('store_id', TRUE);
		$recipe_id = $this->input->post('recipe_id', TRUE);

		$this->db->select('r.*, i.item_name as product_name, i.item_code as product_code, i.sales_price');
		$this->db->from('db_recipes r');
		$this->db->join('db_items i', 'i.id = r.product_item_id', 'left');
		if(!empty($store_id)){ $this->db->where('r.store_id', $store_id); }
		if(!empty($recipe_id)){ $this->db->where('r.id', $recipe_id); }
		$this->db->where('r.status', 1);
		$this->db->order_by('r.name', 'ASC');
		$q1 = $this->db->get();

		$i=0;
		if($q1->num_rows()>0){
			foreach($q1->result() as $res1){
				$ings = $this->db->where('recipe_id', $res1->id)->get('db_recipe_ingredients')->result();
				$total_cost = 0;
				foreach($ings as $ing){
					$total_cost += (float)$ing->qty * (float)$ing->cost_per_unit;
				}
				$cost_per_unit = $res1->yield_qty > 0 ? $total_cost / (float)$res1->yield_qty : 0;
				$margin = ((float)$res1->sales_price - $cost_per_unit);
				$margin_pct = $cost_per_unit > 0 ? ($margin / $cost_per_unit) * 100 : 0;

				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){ echo "<td>".get_store_name($res1->store_id)."</td>"; }
				echo "<td>".htmlspecialchars($res1->name)."</td>";
				echo "<td>".htmlspecialchars($res1->category ?: '-')."</td>";
				echo "<td>".htmlspecialchars($res1->product_name ?: '-')." <small class='text-muted'>".htmlspecialchars($res1->product_code ?: '')."</small></td>";
				echo "<td class='text-right'>".format_qty($res1->yield_qty).' '.htmlspecialchars($res1->yield_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($total_cost)."</td>";
				echo "<td class='text-right'>".store_number_format($cost_per_unit)."</td>";
				echo "<td class='text-right'>".store_number_format($res1->sales_price)."</td>";
				echo "<td class='text-right'>".store_number_format($margin)." <small class='text-muted'>(".number_format($margin_pct,1)."%)</small></td>";
				echo "</tr>";
			}
		} else {
			$colspan = (store_module() && is_admin()) ? 10 : 9;
			echo "<tr><td class='text-center text-danger' colspan='$colspan'>No Records Found</td></tr>";
		}
		exit;
	}

	public function show_production_runs_report(){
		$store_id = $this->input->post('store_id', TRUE);
		$from_date = get_date_format($this->input->post('from_date'),'Y-m-d');
		$to_date = get_date_format($this->input->post('to_date'),'Y-m-d');
		$recipe_id = $this->input->post('recipe_id', TRUE);

		$this->db->select('pr.*, r.name as recipe_name, r.yield_unit, u.first_name, u.last_name');
		$this->db->from('db_recipe_production_runs pr');
		$this->db->join('db_recipes r', 'r.id = pr.recipe_id', 'left');
		$this->db->join('db_users u', 'u.id = pr.staff_id', 'left');
		if(!empty($store_id)){ $this->db->where('pr.store_id', $store_id); }
		if(!empty($from_date)){ $this->db->where('pr.run_date >=', $from_date); }
		if(!empty($to_date)){ $this->db->where('pr.run_date <=', $to_date); }
		if(!empty($recipe_id)){ $this->db->where('pr.recipe_id', $recipe_id); }
		$this->db->order_by('pr.run_date', 'DESC');
		$q1 = $this->db->get();

		$i=0;
		$tot_planned=0;
		$tot_actual=0;
		$tot_cost=0;
		if($q1->num_rows()>0){
			foreach($q1->result() as $res1){
				$tot_planned += (float)$res1->planned_qty;
				$tot_actual += (float)$res1->actual_yield;
				$tot_cost += (float)$res1->actual_cost;
				echo "<tr>";
				echo "<td>".++$i."</td>";
				if(store_module() && is_admin()){ echo "<td>".get_store_name($res1->store_id)."</td>"; }
				echo "<td>".show_date($res1->run_date)."</td>";
				echo "<td>".htmlspecialchars($res1->recipe_name ?: '-')."</td>";
				echo "<td class='text-right'>".format_qty($res1->planned_qty).' '.htmlspecialchars($res1->yield_unit ?: '')."</td>";
				echo "<td class='text-right'>".format_qty($res1->actual_yield).' '.htmlspecialchars($res1->yield_unit ?: '')."</td>";
				echo "<td class='text-right'>".store_number_format($res1->actual_cost)."</td>";
				echo "<td>".htmlspecialchars(($res1->first_name ? $res1->first_name.' '.$res1->last_name : '-'))."</td>";
				echo "<td>".htmlspecialchars($res1->notes ?: '-')."</td>";
				echo "</tr>";
			}
			$colspan = (store_module() && is_admin()) ? 8 : 7;
			echo "<tr><td class='text-right text-bold' colspan='$colspan'><b>Total:</b></td><td class='text-left text-bold'>".format_qty($tot_actual)."</td><td class='text-left text-bold'>".store_number_format($tot_cost)."</td><td></td><td></td></tr>";
		} else {
			$colspan = (store_module() && is_admin()) ? 9 : 8;
			echo "<tr><td class='text-center text-danger' colspan='$colspan'>No Records Found</td></tr>";
		}
		exit;
	}

}
