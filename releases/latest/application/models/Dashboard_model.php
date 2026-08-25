<?php

class Dashboard_model extends CI_Model
{

	//Datatable start
		var $table = 'db_items a';
		var $column_order = array(
									'a.item_code',
									'a.item_name',
									'b.category_name',
									'c.brand_name',
									'a.stock'
									); //set column field database for datatable orderable
		var $column_search = array( 
									'a.item_code',
									'a.item_name',
									'b.category_name',
									'c.brand_name',
									'a.stock'
									); //set column field database for datatable searchable 
		var $order = array('a.id' => 'desc'); // default order 

	
		
		private function _get_datatables_query()
		{	
			$this->db->select($this->column_order);
			$this->db->from($this->table);
			$this->db->where('a.store_id',get_current_store_id());
	        $this->db->where('(a.stock<=a.alert_qty or a.stock is null)');
	        $this->db->where('a.service_bit',0);
	        
	        $this->db->where('a.status=1');
	        $this->db->join('db_category b','b.id=a.category_id','left');
	        $this->db->join('db_brands c','c.id=a.brand_id','left');

	     

			//	echo $this->db->get_compiled_select();exit();
			$i = 0;
		
			foreach ($this->column_search as $item) // loop column 
			{
				if($_POST['search']['value']) // if datatable send POST for search
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
			
			if(isset($_POST['order'])) // here order processing
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
			if($_POST['length'] != -1)
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
			$this->db->from($this->table);
			return $this->db->count_all_results();
		}
		//Datatable end

	public function get_subscription_chart()
	{
		$sub_chart = array();
		for ($i=6; $i >= 0; $i--) {
			//Date
            $sub_chart['date'][$i] = date("Y-m-d",strtotime("-".$i." months"));
            $sub_chart['sub_year'][$i] = date("Y",strtotime($sub_chart['date'][$i]));
            $sub_chart['sub_month'][$i] = date("M",strtotime($sub_chart['date'][$i]));

            $this->db->select("count(*) as tot_subscribes");
            $this->db->from("db_subscription");
            $this->db->where("month(subscription_date)",date("m",strtotime($sub_chart['date'][$i])));
            $q3=$this->db->get();
            $sub_chart['tot_subscribes'][$i] = $q3->row()->tot_subscribes;
        }
        return $sub_chart;
	}

	public function get_pie_chart($warehouse_id = '')
	{
		
            $this->db->where("c.store_id",get_current_store_id());
            /*if(!is_admin() && !is_store_admin()){
              $this->db->where("c.created_by",$this->session->userdata('inv_username'));  
            }*/
            $this->db->select("COALESCE(SUM(b.sales_qty),0) AS sales_qty, a.item_name");
            $this->db->from("db_items AS a, db_salesitems AS b ,db_sales AS c");
            $this->db->where("a.id=b.`item_id` AND b.sales_id=c.`id` AND c.`sales_status`='Final'");
            if(!empty($warehouse_id)){ $this->db->where("c.warehouse_id", $warehouse_id); }
            $this->db->group_by("a.id");
            $this->db->limit("10");
            $this->db->order_by("sales_qty","asc");

            $q3=$this->db->get();
            $pie_chart=array();
            $i=0;
            if($q3->num_rows() >0){
              foreach($q3->result() as $res3){
                  if($res3->sales_qty>0){
                  	++$i;
                  	$pie_chart['tranding_item'][$i]['name'] = $res3->item_name;
                  	$pie_chart['tranding_item'][$i]['sales_qty'] = $res3->sales_qty;
                  }
              }
            }
            $pie_chart['tranding_item']['tot_rec'] = $i;
            return $pie_chart;
	}
	public function get_bar_chart(){
		$bar_chart=array();
          for ($i=6; $i >= 0; $i--) { 

              //Date
              $bar_chart['date'][$i] = date("Y-m-d",strtotime("-".$i." months"));
              $bar_chart['month'][$i] = date("M",strtotime($bar_chart['date'][$i])).",".date("Y",strtotime($bar_chart['date'][$i]));

              //Find purchase total
              $this->db->where("store_id",get_current_store_id());
              $this->db->select("COALESCE(SUM(grand_total),0) AS pur_total");
              $this->db->from("db_purchase");
              $this->db->where("purchase_status='Received'");
              $this->db->where("month(purchase_date)",date("m",strtotime($bar_chart['date'][$i])));
              $this->db->where("year(purchase_date)",date("Y",strtotime($bar_chart['date'][$i])));
              $q1=$this->db->get()->row();
              $this->db->get_compiled_select();
              $bar_chart['purchase'][$i]=$q1->pur_total;
              
              //Find sales total
              $this->db->where("store_id",get_current_store_id());
              $this->db->select("COALESCE(SUM(grand_total),0) AS sal_total");
              $this->db->from("db_sales");
              $this->db->where("sales_status='Final'");
              $this->db->where("month(sales_date)",date("m",strtotime($bar_chart['date'][$i])));
              $this->db->where("year(sales_date)",date("Y",strtotime($bar_chart['date'][$i])));
              $q1=$this->db->get()->row();
              $bar_chart['sales'][$i]=$q1->sal_total;

              //Find expense total
              $this->db->where("store_id",get_current_store_id());
              $this->db->select("COALESCE(SUM(expense_amt),0) AS expense_amt");
              $this->db->from("db_expense");
              $this->db->where("month(expense_date)",date("m",strtotime($bar_chart['date'][$i])));
              $this->db->where("year(expense_date)",date("Y",strtotime($bar_chart['date'][$i])));
              $q1=$this->db->get()->row();
              $bar_chart['expense'][$i]=$q1->expense_amt;
          }
          return $bar_chart;
	}
	public function get_by_date($table_date)
	{
		$dates = $this->input->post('dates');
		if($dates=='Today'){
      		//$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
      		$this->db->where("$table_date",date("Y-m-d"));
      	}
      	if($dates=='Weekly'){
      		$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
      	}
      	if($dates=='Monthly'){
      		$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
      	}
      	if($dates=='Yearly'){
      		$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
      	}
	}
	public function breadboard_values()
	{	
		$dates = $this->input->post('dates');
		//$store_id=$this->input->post('store_id');
		$CI =& get_instance();
		$info=array();

		///Find total suppliers
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->db->select("coalesce(count(*),0) as tot_sup");
		$this->db->from("db_suppliers");
		$this->db->where("status=1");
		
		$tot_sup=$this->db->get()->row()->tot_sup;	
		$info['tot_sup']=$tot_sup;

		///Find total Products
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->db->select("coalesce(count(*),0) as tot_pro");
		$this->db->from("db_items");
		$this->db->where("status=1");
		$tot_pro=$this->db->get()->row()->tot_pro;	
		$info['tot_pro']=$tot_pro;

		//Total Customers
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->db->select("coalesce(count(*),0) as tot_cust");
		$this->db->from("db_customers");
		$this->db->where("status=1");
		$tot_cust=$this->db->get()->row()->tot_cust;	
		$info['tot_cust']=$tot_cust;

  		//Total Purchases Active
  		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('purchase_date');//DATES FUNCTION
		$this->db->select("coalesce(count(*),0) as tot_pur");
		$this->db->from("db_purchase");
		$this->db->where("purchase_status='Received'");
		//echo $this->db->get_compiled_select();exit();
		$tot_pur=$this->db->get()->row()->tot_pur;	
		$info['tot_pur']=$tot_pur;

  		//Total SAles Active
  		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('sales_date');//DATES FUNCTION
		$this->db->select("coalesce(count(*),0) as tot_sal");
		$this->db->from("db_sales");
		$this->db->where("`sales_status`= 'Final'");
		$tot_sal=$this->db->get()->row()->tot_sal;
		$info['tot_sal']=$tot_sal;


		//Total SAles return amount
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('return_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_ret_grand_total");
		$this->db->from("db_salesreturn");
		$tot_sal_ret_grand_total=$this->db->get()->row()->tot_sal_ret_grand_total;
		$info['tot_sal_ret_grand_total']=$CI->currency(kmb($tot_sal_ret_grand_total));

		//Total SAles amount
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('sales_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_grand_total");
		$this->db->from("db_sales");
		$this->db->where("`sales_status`= 'Final'");
		$tot_sal_grand_total=$this->db->get()->row()->tot_sal_grand_total;
		$info['tot_sal_grand_total']=$CI->currency(kmb($tot_sal_grand_total-$tot_sal_ret_grand_total));

		


		//Total expense amount
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('expense_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(expense_amt),0) AS tot_exp");
		$this->db->from("db_expense");
		$tot_exp=$this->db->get()->row()->tot_exp;
		$info['tot_exp']=$CI->currency(kmb($tot_exp,2));

		//Total SAles Due
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('sales_date');//DATES FUNCTION
		$this->db->select("(COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0)) as sales_due");
		$this->db->from("db_sales");
		$this->db->where("`sales_status`= 'Final'");
		$sales_due=$this->db->get()->row()->sales_due;
		$info['sales_due']=$CI->currency(kmb($sales_due));

		//Total Purchase  Due
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->get_by_date('purchase_date');//DATES FUNCTION
		$this->db->select("(COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0)) as purchase_due");
		$this->db->from("db_purchase");
		$this->db->where("`purchase_status`= 'Received'");
		$purchase_due=$this->db->get()->row()->purchase_due;
		$info['purchase_due']=$CI->currency(kmb($purchase_due));

		return $info;
	}

	// ============================================================
	// MARTPOINT RETAIL DASHBOARD V2 - KPI DATA
	// ============================================================

	/**
	 * Get today's sales vs yesterday for comparison
	 */
	public function get_today_sales($warehouse_id = ''){
		$store_id = get_current_store_id();
		$today = date('Y-m-d');
		$yesterday = date('Y-m-d', strtotime('-1 day'));

		// Today
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->from("db_sales");
		$this->db->where("sales_date", $today);
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$today_sales = $this->db->get()->row()->total;

		// Yesterday
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->from("db_sales");
		$this->db->where("sales_date", $yesterday);
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$yesterday_sales = $this->db->get()->row()->total;

		$change = 0;
		if($yesterday_sales > 0){
			$change = round((($today_sales - $yesterday_sales) / $yesterday_sales) * 100, 1);
		}

		return array('today'=>$today_sales, 'yesterday'=>$yesterday_sales, 'change'=>$change);
	}

	/**
	 * Get today's expenses from db_expense
	 */
	public function get_today_expenses($warehouse_id = ''){
		$store_id = get_current_store_id();
		$today = date('Y-m-d');

		$this->db->select("COALESCE(SUM(expense_amt),0) as total");
		$this->db->from("db_expense");
		$this->db->where("expense_date", $today);
		$this->db->where("store_id", $store_id);
		// Note: db_expense does not have warehouse_id column
		return $this->db->get()->row()->total;
	}

	/**
	 * Get today's profit and margin
	 */
	public function get_today_profit($warehouse_id = ''){
		$store_id = get_current_store_id();
		$today = date('Y-m-d');

		// Revenue from db_sales
		$this->db->select("COALESCE(SUM(grand_total),0) as revenue");
		$this->db->from("db_sales");
		$this->db->where("sales_date", $today);
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$revenue = $this->db->get()->row()->revenue;

		// Cost = purchase_price * qty from db_salesitems joined to today's sales
		$this->db->select("COALESCE(SUM(b.purchase_price * b.sales_qty),0) as cost");
		$this->db->from("db_sales a");
		$this->db->join("db_salesitems b", "a.id = b.sales_id", "left");
		$this->db->where("a.sales_date", $today);
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("a.warehouse_id", $warehouse_id); }
		$cost = $this->db->get()->row()->cost;

		$profit = $revenue - $cost;
		$margin = ($revenue > 0) ? round(($profit / $revenue) * 100, 1) : 0;

		return array('profit'=>$profit, 'margin'=>$margin, 'revenue'=>$revenue);
	}

	/**
	 * Get outstanding customer debts
	 */
	public function get_outstanding_debts($warehouse_id = ''){
		$store_id = get_current_store_id();

		$this->db->select("COALESCE(SUM(grand_total - paid_amount),0) as total_debt, COUNT(*) as debtor_count");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("(grand_total - paid_amount) >", 0);
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$result = $this->db->get()->row();

		return array('total'=>$result->total_debt, 'count'=>$result->debtor_count);
	}

	/**
	 * Get low stock items
	 */
	public function get_low_stock_items($warehouse_id = ''){
		$store_id = get_current_store_id();

		$this->db->select("a.item_name, a.stock, a.alert_qty");
		$this->db->from("db_items a");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("a.stock <= a.alert_qty");
		$this->db->where("a.status", 1);
		$this->db->where("a.alert_qty >", 0);
		$this->db->order_by("a.stock", "asc");
		$this->db->limit(10);
		// Note: db_items master table does not have warehouse_id column
		$query = $this->db->get();

		$items = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $row){
				$items[] = array(
					'name' => $row->item_name,
					'qty' => $row->stock,
					'min' => $row->alert_qty
				);
			}
		}
		return $items;
	}

	public function get_low_stock_count($warehouse_id = ''){
		$store_id = get_current_store_id();
		$this->db->where("store_id", $store_id);
		$this->db->where("stock <= alert_qty");
		$this->db->where("status", 1);
		$this->db->where("alert_qty >", 0);
		// Note: db_items master table does not have warehouse_id column
		return $this->db->count_all_results("db_items");
	}

	/**
	 * Get top debtors
	 */
	public function get_top_debtors($warehouse_id = ''){
		$store_id = get_current_store_id();

		$this->db->select("b.customer_name, SUM(a.grand_total - a.paid_amount) as amount_owing");
		$this->db->from("db_sales a");
		$this->db->join("db_customers b", "a.customer_id = b.id", "left");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("(a.grand_total - a.paid_amount) >", 0);
		$this->db->where("a.store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("a.warehouse_id", $warehouse_id); }
		$this->db->group_by("a.customer_id");
		$this->db->order_by("amount_owing", "desc");
		$this->db->limit(5);
		$query = $this->db->get();

		$debtors = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $row){
				$debtors[] = array(
					'name' => $row->customer_name,
					'amount' => $row->amount_owing
				);
			}
		}
		return $debtors;
	}

	/**
	 * Get top selling products (last 30 days)
	 */
	public function get_top_selling_products($warehouse_id = '', $range = 'Today'){
		$store_id = get_current_store_id();

		$this->db->select("b.item_name, SUM(a.sales_qty) as qty_sold, SUM(a.total_cost) as revenue");
		$this->db->from("db_salesitems a");
		$this->db->join("db_items b", "a.item_id = b.id", "left");
		$this->db->join("db_sales c", "a.sales_id = c.id", "left");
		$this->db->where("c.sales_status", "Final");
		$this->db->where("c.store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("c.warehouse_id", $warehouse_id); }
		$this->apply_range_where('c.sales_date', $range);
		$this->db->group_by("a.item_id");
		$this->db->order_by("revenue", "desc");
		$this->db->limit(5);
		$query = $this->db->get();

		$products = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $row){
				$products[] = array(
					'name' => $row->item_name,
					'qty' => $row->qty_sold,
					'revenue' => $row->revenue
				);
			}
		}
		return $products;
	}

	/**
	 * Get cash in hand (sum of all cash-affecting sales payments)
	 * Uses db_payment_modes.affects_cash_in_hand so any mode marked as cash is counted.
	 */
	public function get_cash_in_hand($warehouse_id = ''){
		$store_id = get_current_store_id();

		// Sum all sales payments whose payment_mode has affects_cash_in_hand = 1
		// Filter via db_sales join since db_salespayments has no warehouse_id column
		$this->db->select("COALESCE(SUM(sp.payment),0) as cash");
		$this->db->from("db_salespayments sp");
		$this->db->join("db_payment_modes pm", "pm.code = sp.payment_type AND pm.store_id = sp.store_id", "left");
		$this->db->join("db_sales s", "s.id = sp.sales_id", "left");
		$this->db->where("sp.store_id", $store_id);
		$this->db->where("pm.affects_cash_in_hand", 1);
		if(!empty($warehouse_id)){ $this->db->where("s.warehouse_id", $warehouse_id); }
		$cash = $this->db->get()->row()->cash;

		// Fallback: if payment_modes join yields nothing, try raw CASH type
		if($cash == 0){
			$this->db->select("COALESCE(SUM(sp.payment),0) as cash");
			$this->db->from("db_salespayments sp");
			$this->db->join("db_sales s", "s.id = sp.sales_id", "left");
			$this->db->where("UPPER(sp.payment_type)", "CASH");
			$this->db->where("sp.store_id", $store_id);
			if(!empty($warehouse_id)){ $this->db->where("s.warehouse_id", $warehouse_id); }
			$cash = $this->db->get()->row()->cash;
		}

		return $cash;
	}

	/**
	 * Get recent activities
	 */
	public function get_recent_activities($warehouse_id = ''){
		$store_id = get_current_store_id();
		$activities = array();

		// Recent sales
		$this->db->select("sales_code, grand_total, created_date, created_time, customer_id");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$this->db->order_by("id", "desc");
		$this->db->limit(3);
		$sales = $this->db->get();
		if($sales->num_rows() > 0){
			foreach($sales->result() as $row){
				$activities[] = array(
					'type' => 'sale',
					'title' => 'Sale #' . $row->sales_code,
					'amount' => $row->grand_total,
					'date' => $row->created_date . ' ' . $row->created_time
				);
			}
		}

		// Recent customers
		$this->db->select("customer_name, created_date, created_time");
		$this->db->from("db_customers");
		$this->db->where("store_id", $store_id);
		$this->db->order_by("id", "desc");
		$this->db->limit(2);
		$customers = $this->db->get();
		if($customers->num_rows() > 0){
			foreach($customers->result() as $row){
				$activities[] = array(
					'type' => 'customer',
					'title' => $row->customer_name,
					'amount' => 0,
					'date' => $row->created_date . ' ' . $row->created_time
				);
			}
		}

		return $activities;
	}

	/**
	 * Get simple insights based on existing data
	 */
	public function get_insights($warehouse_id = ''){
		$insights = array();
		$store_id = get_current_store_id();

		// Sales change this week
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 7 DAY)");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$this_week = $this->db->get()->row()->total;

		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 14 DAY)");
		$this->db->where("sales_date <= DATE_SUB(NOW(), INTERVAL 7 DAY)");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$last_week = $this->db->get()->row()->total;

		if($last_week > 0 && $this_week > 0){
			$change = round((($this_week - $last_week) / $last_week) * 100, 1);
			$dir = $change >= 0 ? 'increased' : 'decreased';
			$insights[] = "Sales {$dir} by " . abs($change) . "% this week.";
		}

		// Low stock alert
		$low_stock = $this->get_low_stock_count($warehouse_id);
		if($low_stock > 0){
			$insights[] = "{$low_stock} products need restocking.";
		}

		// Outstanding debt
		$debts = $this->get_outstanding_debts($warehouse_id);
		if($debts['total'] > 0){
			$insights[] = number_format($debts['count']) . " customers owe money.";
		}

		return $insights;
	}

	/**
	 * Get branch performance (sales by warehouse)
	 * Returns array of branches with sales, sorted by sales desc
	 */
	public function get_branch_performance($range = 'Today'){
		$store_id = get_current_store_id();
		$range_info = $this->get_range_info($range);

		$date_clause = '';
		if($range_info['from'] === $range_info['to']){
			$date_clause = " AND s.sales_date = '{$range_info['from']}'";
		} else {
			$date_clause = " AND s.sales_date >= '{$range_info['from']}' AND s.sales_date <= '{$range_info['to']}'";
		}

		$this->db->select("w.id, w.warehouse_name, COALESCE(SUM(s.grand_total),0) as sales");
		$this->db->from("db_warehouse w");
		$this->db->join("db_sales s", "s.warehouse_id = w.id AND s.sales_status = 'Final'{$date_clause}", "left");
		$this->db->where("w.store_id", $store_id);
		$this->db->where("w.status", 1);
		$this->db->group_by("w.id");
		$this->db->order_by("sales", "desc");
		$query = $this->db->get();

		$branches = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $row){
				$branches[] = array(
					'id' => $row->id,
					'name' => $row->warehouse_name,
					'sales' => $row->sales
				);
			}
		}
		return $branches;
	}

	/**
	 * Get daily business summary for a specific date or date range
	 * @param string $date   Start date (Y-m-d)
	 * @param string $date_to End date (Y-m-d), optional — same as start if omitted
	 */
	public function get_daily_summary($date = null, $date_to = null){
		$store_id = get_current_store_id();
		if(empty($date)){
			$date = date('Y-m-d');
		}
		if(empty($date_to)){
			$date_to = $date;
		}
		$isRange = ($date !== $date_to);
		$dateLabel = $isRange ? show_date($date) . ' — ' . show_date($date_to) : show_date($date);

		$summary = array(
			'store_id' => $store_id,
			'date' => $date,
			'date_to' => $date_to,
			'date_label' => $dateLabel,
			'is_range' => $isRange,
			'sales' => array('total'=>0, 'transactions'=>0, 'cash_expected'=>0, 'payment_breakdown'=>array()),
			'profit' => array('gross_profit'=>0, 'margin'=>0, 'available'=>false),
			'expenses' => array('total'=>0),
			'net_position' => 0,
			'top_products' => array(),
			'low_stock_items' => array(),
			'outstanding_debts' => array('total'=>0, 'customers'=>0),
			'purchase_due' => 0,
			'attendance' => array('present'=>0, 'absent'=>0, 'total_staff'=>0),
			'insights' => array(),
			'has_data' => false
		);

		// Helper for date range WHERE
		$dateWhere = function($field) use ($date, $date_to, $isRange){
			if($isRange){
				$this->db->where("{$field} >=", $date);
				$this->db->where("{$field} <=", $date_to);
			} else {
				$this->db->where($field, $date);
			}
		};

		// Total Sales & Transaction Count
		$this->db->select("COALESCE(SUM(grand_total),0) as total, COUNT(*) as transactions");
		$this->db->from("db_sales");
		$dateWhere('sales_date');
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		$sales_result = $this->db->get()->row();
		$summary['sales']['total'] = floatval($sales_result->total);
		$summary['sales']['transactions'] = intval($sales_result->transactions);

		if($summary['sales']['transactions'] > 0 || $summary['sales']['total'] > 0){
			$summary['has_data'] = true;
		}

		// Profit (revenue - cost)
		$this->db->select("COALESCE(SUM(b.purchase_price * b.sales_qty),0) as cost");
		$this->db->from("db_sales a");
		$this->db->join("db_salesitems b", "a.id = b.sales_id", "left");
		$dateWhere('a.sales_date');
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		$cost_result = $this->db->get()->row();
		$cost = floatval($cost_result->cost);

		if($cost > 0 || $summary['sales']['total'] > 0){
			$summary['profit']['gross_profit'] = $summary['sales']['total'] - $cost;
			$summary['profit']['margin'] = ($summary['sales']['total'] > 0) ? round((($summary['profit']['gross_profit'] / $summary['sales']['total']) * 100), 1) : 0;
			$summary['profit']['available'] = true;
		}

		// Expenses
		$this->db->select("COALESCE(SUM(expense_amt),0) as total");
		$this->db->from("db_expense");
		$dateWhere('expense_date');
		$this->db->where("store_id", $store_id);
		$exp_result = $this->db->get()->row();
		$summary['expenses']['total'] = floatval($exp_result->total);
		if($summary['expenses']['total'] > 0){
			$summary['has_data'] = true;
		}

		// Net Position
		$summary['net_position'] = $summary['sales']['total'] - $summary['expenses']['total'];

		// Payment Breakdown — uses db_payment_modes to categorize
		$this->db->select("sp.payment_type, COALESCE(SUM(sp.payment),0) as amount, COUNT(*) as txn_count, MAX(pm.affects_cash_in_hand) as affects_cash, SUM(CASE WHEN sp.confirmation_status=0 THEN 1 ELSE 0 END) as pending_count");
		$this->db->from("db_salespayments sp");
		$dateWhere('sp.payment_date');
		$this->db->where("sp.store_id", $store_id);
		$this->db->join("db_payment_modes pm", "pm.code = sp.payment_type AND pm.store_id = sp.store_id", "left");
		$this->db->group_by("sp.payment_type");
		$payments = $this->db->get();
		$summary['sales']['cash_expected'] = 0;
		$summary['sales']['bank_pos_expected'] = 0;
		if($payments->num_rows() > 0){
			foreach($payments->result() as $row){
				$summary['sales']['payment_breakdown'][] = array(
					'type' => $row->payment_type,
					'amount' => floatval($row->amount),
					'txn_count' => intval($row->txn_count),
					'affects_cash' => intval($row->affects_cash),
					'pending_count' => intval($row->pending_count)
				);
				if($row->affects_cash == 1){
					$summary['sales']['cash_expected'] += floatval($row->amount);
				} else {
					$summary['sales']['bank_pos_expected'] += floatval($row->amount);
				}
			}
		}
		if($summary['sales']['cash_expected'] == 0 && $summary['sales']['bank_pos_expected'] == 0){
			$summary['sales']['cash_expected'] = $summary['sales']['total'];
		}

		// Top Selling Products
		$this->db->select("b.item_name, SUM(a.sales_qty) as qty_sold, SUM(a.total_cost) as revenue");
		$this->db->from("db_salesitems a");
		$this->db->join("db_items b", "a.item_id = b.id", "left");
		$this->db->join("db_sales c", "a.sales_id = c.id", "left");
		$this->db->where("c.sales_status", "Final");
		$this->db->where("c.store_id", $store_id);
		$dateWhere('c.sales_date');
		$this->db->group_by("a.item_id");
		$this->db->order_by("qty_sold", "desc");
		$this->db->limit(5);
		$top = $this->db->get();
		if($top->num_rows() > 0){
			foreach($top->result() as $row){
				$summary['top_products'][] = array(
					'name' => $row->item_name,
					'qty' => $row->qty_sold,
					'revenue' => $row->revenue
				);
			}
		}

		// Low Stock Items (snapshot — not range dependent)
		$summary['low_stock_items'] = $this->get_low_stock_items();

		// Outstanding Debts (all-time snapshot)
		$debt_data = $this->get_outstanding_debts();
		$summary['outstanding_debts'] = $debt_data;

		// Purchase Due (all-time snapshot)
		$this->db->select("COALESCE(SUM(grand_total - paid_amount),0) as purchase_due");
		$this->db->from("db_purchase");
		$this->db->where("purchase_status", "Received");
		$this->db->where("store_id", $store_id);
		$purchase_due = $this->db->get()->row();
		$summary['purchase_due'] = floatval($purchase_due->purchase_due);

		// Attendance - detailed staff list
		$this->db->select("u.id, u.username, u.first_name, u.last_name, r.role_name");
		$this->db->from("db_users u");
		$this->db->join("db_roles r", "r.id = u.role_id", "left");
		$this->db->where("u.store_id", $store_id);
		$this->db->where("u.status", 1);
		$users = $this->db->get()->result();
		$staff_list = array();
		$present_count = 0;
		foreach($users as $user){
			$name = trim(($user->first_name ?: '') . ' ' . ($user->last_name ?: ''));
			if(empty($name)){ $name = $user->username; }
			// Check attendance for the date (single day view for daily summary)
			$this->db->select("1");
			$this->db->from("db_attendance");
			$this->db->where("store_id", $store_id);
			$this->db->where("user_id", $user->id);
			$this->db->where("attendance_date", $date);
			$this->db->where("clock_in IS NOT NULL");
			$is_present = ($this->db->get()->num_rows() > 0);
			if($is_present){ $present_count++; }
			$staff_list[] = array(
				'name' => $name,
				'position' => $user->role_name ?: 'Staff',
				'status' => $is_present ? 'Present' : 'Absent'
			);
		}
		$summary['attendance']['total_staff'] = count($staff_list);
		$summary['attendance']['present'] = $present_count;
		$summary['attendance']['absent'] = max(0, count($staff_list) - $present_count);
		$summary['attendance']['staff_list'] = $staff_list;
		if($present_count > 0 || count($staff_list) > 0){
			$summary['has_data'] = true;
		}

		// Insights
		$insights = array();
		if($summary['sales']['total'] > 0){
			$label = $isRange ? "Total sales in period" : "Total sales today";
			$insights[] = $label . ": " . kmb($summary['sales']['total']);
		}
		if($summary['profit']['available'] && $summary['profit']['gross_profit'] > 0){
			$insights[] = "Profit margin: " . $summary['profit']['margin'] . "%";
		}
		if(count($summary['low_stock_items']) > 0){
			$insights[] = count($summary['low_stock_items']) . " products running low.";
		}
		if($summary['outstanding_debts']['total'] > 0){
			$insights[] = "Outstanding debts: " . kmb($summary['outstanding_debts']['total']);
		}
		if(count($summary['top_products']) > 0){
			$insights[] = $summary['top_products'][0]['name'] . " was your best seller.";
		}
		if($summary['attendance']['total_staff'] > 0){
			$att_label = $isRange ? "Staff present in period" : "Staff present today";
			$insights[] = $att_label . ": " . $summary['attendance']['present'] . "/" . $summary['attendance']['total_staff'];
		}
		$summary['insights'] = $insights;

		return $summary;
	}

	// ============================================================
	// RANGE-AWARE KPI METHODS (for dashboard date filter)
	// ============================================================

	/**
	 * Get date range info for a named range
	 * Returns ['from','to','prev_from','prev_to','label']
	 */
	public function get_range_info($range){
		$today = date('Y-m-d');
		switch($range){
			case '7Days':
				return [
					'from' => date('Y-m-d', strtotime('-7 days')),
					'to' => $today,
					'prev_from' => date('Y-m-d', strtotime('-14 days')),
					'prev_to' => date('Y-m-d', strtotime('-1 day')),
					'label' => '7 Days'
				];
			case '30Days':
				return [
					'from' => date('Y-m-d', strtotime('-30 days')),
					'to' => $today,
					'prev_from' => date('Y-m-d', strtotime('-60 days')),
					'prev_to' => date('Y-m-d', strtotime('-31 days')),
					'label' => '30 Days'
				];
			case 'LastMonth':
				return [
					'from' => date('Y-m-01', strtotime('-1 month')),
					'to' => date('Y-m-t', strtotime('-1 month')),
					'prev_from' => date('Y-m-01', strtotime('-2 month')),
					'prev_to' => date('Y-m-t', strtotime('-2 month')),
					'label' => 'Last Month'
				];
			case 'ThisMonth':
				return [
					'from' => date('Y-m-01'),
					'to' => date('Y-m-t'),
					'prev_from' => date('Y-m-01', strtotime('-1 month')),
					'prev_to' => date('Y-m-t', strtotime('-1 month')),
					'label' => 'This Month'
				];
			case 'ThisYear':
				return [
					'from' => date('Y-01-01'),
					'to' => $today,
					'prev_from' => date('Y-01-01', strtotime('-1 year')),
					'prev_to' => date('Y-m-d', strtotime('-1 year')),
					'label' => 'This Year'
				];
			default: // Today
				return [
					'from' => $today,
					'to' => $today,
					'prev_from' => date('Y-m-d', strtotime('-1 day')),
					'prev_to' => date('Y-m-d', strtotime('-1 day')),
					'label' => 'Today'
				];
		}
	}

	/**
	 * Apply date range WHERE clause to a query
	 */
	public function apply_range_where($table_date, $range){
		$info = $this->get_range_info($range);
		if($info['from'] === $info['to']){
			$this->db->where($table_date, $info['from']);
		} else {
			$this->db->where("{$table_date} >=", $info['from']);
			$this->db->where("{$table_date} <=", $info['to']);
		}
	}

	/**
	 * Get sales total for a date range with comparison
	 */
	public function get_sales_by_range($range, $warehouse_id = ''){
		$store_id = get_current_store_id();
		$info = $this->get_range_info($range);

		// Current period
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$this->apply_range_where('sales_date', $range);
		$current = $this->db->get()->row()->total;

		// Previous period
		$prev_info = $this->get_range_info($range);
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		if($prev_info['prev_from'] === $prev_info['prev_to']){
			$this->db->where("sales_date", $prev_info['prev_from']);
		} else {
			$this->db->where("sales_date >=", $prev_info['prev_from']);
			$this->db->where("sales_date <=", $prev_info['prev_to']);
		}
		$previous = $this->db->get()->row()->total;

		$change = 0;
		if($previous > 0){
			$change = round((($current - $previous) / $previous) * 100, 1);
		}

		return array('today'=>$current, 'yesterday'=>$previous, 'change'=>$change);
	}

	/**
	 * Get profit for a date range
	 */
	public function get_profit_by_range($range, $warehouse_id = ''){
		$store_id = get_current_store_id();
		$info = $this->get_range_info($range);

		// Revenue
		$this->db->select("COALESCE(SUM(grand_total),0) as revenue");
		$this->db->from("db_sales");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("warehouse_id", $warehouse_id); }
		$this->apply_range_where('sales_date', $range);
		$revenue = $this->db->get()->row()->revenue;

		// Cost = purchase_price * qty
		$info = $this->get_range_info($range);
		$this->db->select("COALESCE(SUM(b.purchase_price * b.sales_qty),0) as cost");
		$this->db->from("db_sales a");
		$this->db->join("db_salesitems b", "a.id = b.sales_id", "left");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		if(!empty($warehouse_id)){ $this->db->where("a.warehouse_id", $warehouse_id); }
		$this->apply_range_where('a.sales_date', $range);
		$cost = $this->db->get()->row()->cost;

		$profit = $revenue - $cost;
		$margin = ($revenue > 0) ? round(($profit / $revenue) * 100, 1) : 0;

		return array('profit'=>$profit, 'margin'=>$margin, 'revenue'=>$revenue);
	}

	/**
	 * Get expenses for a date range
	 */
	public function get_expenses_by_range($range, $warehouse_id = ''){
		$store_id = get_current_store_id();
		$this->db->select("COALESCE(SUM(expense_amt),0) as total");
		$this->db->from("db_expense");
		$this->db->where("store_id", $store_id);
		$this->apply_range_where('expense_date', $range);
		return $this->db->get()->row()->total;
	}
}