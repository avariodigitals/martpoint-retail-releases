<?php

include('MyPDF.php');

class GstInvoice extends MyPDF{
	//public $CI=null;

	protected $sales_id =null;

	//public $store =array();

	protected $customer =array();

	protected $sales =array();

	public $customer_state_name = null;


	public function __construct(array $param=array())
	{
		parent::__construct();

		$this->sales_id = $param['sales_id'];

		//$this->CI =& get_instance();

		//$this->store = get_store_details();//Declared in MyPDF Pa

		$this->sales = get_sales_details($this->sales_id);

		$this->customer = get_customer_details($this->sales->customer_id);

	}
	public function _get_customer_details()
    {   

    	$customer = $this->customer;//array()

    	$store = $this->store;//array()

    	//Customer Records
	    $state = (!empty($customer->state_id)) ? get_state_details($customer->state_id) : '';
	    $this->customer_state_name = (!empty($state)) ? $state->state : $store->state;

        $w = 100;
        $h = 40;

        $custmer_details = '<span style="color:rgb(65, 59, 212);font-style:italic;">'.$this->CI->lang->line('bill_to').':</span>';
        $custmer_details .= "<br><b>".$this->CI->lang->line('name')." :</b> ".$customer->customer_name;
        $custmer_details .= "<br><b>".$this->CI->lang->line('address')." :</b> ".$customer->address;
        $custmer_details .= "<br><b>".$this->CI->lang->line('postcode')." :</b> ".$customer->postcode;
        $custmer_details .= "<br><b>".$this->CI->lang->line('mobile')." :</b> ".$customer->mobile;
        $custmer_details .= "<br><b>".$this->CI->lang->line('email')." :</b> ".$customer->email;
        $custmer_details .= "<br><b>".$this->CI->lang->line('gst_number')." :</b> ".$customer->gstin;

        //$this->setCellMargins(1,1,1,1);
        $this->setCellPaddings(2,1,1,1);
        $this->setFont($this->get_font_name(), '', 9);
        $this->setFillColor(255, 255, 255);

        $this->writeHTMLCell($w, $h, $x ='6', $y='52', $custmer_details, 1, 0, 1, true, 'J', true);
        return $this;
    } 

    public function _get_invoice_details()
    {
    	$sales = $this->sales;//array()

        $w = 100;
        $h = 40;
        $invoice_details = "";
        $invoice_details = '<span style="color:rgb(65, 59, 212);font-style:italic;">'.$this->CI->lang->line('invoice_details').'</span>';
        $invoice_details .= '<br><b>'.$this->CI->lang->line('invoice_no').' :</b> <span style="font-size:16px;">'.$sales->sales_code.'</span>';
        $invoice_details .= '<br><b>'.$this->CI->lang->line('date').' :</b> <span style="">'.show_date($sales->sales_date).'</span>';
        $invoice_details .= '<br><b>'.$this->CI->lang->line('due_date').' :</b> <span style="">'.((!empty($sales->due_date)) ? show_date($sales->due_date):'').'</span>';
        $invoice_details .= '<br><b>'.$this->CI->lang->line('reference_no').' :</b> <span style="">'.$sales->reference_no.'</span>';
        $invoice_details .= '<br><b>'.$this->CI->lang->line('payment_status').' :</b> <span style="">'.$sales->payment_status.'</span>';

        $this->writeHTMLCell($w, $h, $x ='104', $y='', $invoice_details, 1, 1, 1, true, 'J', true);
        return $this;
    }

    public function _get_shipping_address()
    {
        $w = 100;
        $h = 40;

        $customer = $this->customer;//array()
        //Customer Shipping Address Records
	    $country='';
	    $state='';
	    $city='';
	    $address='';
	    $postcode='';
	    if(!empty($customer->shippingaddress_id)){
	        $Q2 = $this->CI->db->select("c.country,s.state,a.city,a.postcode,a.address")
	                        ->where("a.id",$customer->shippingaddress_id)
	                        ->from("db_shippingaddress a")
	                        ->join("db_country c","c.id = a.country_id",'left')
	                        ->join("db_states s","s.id = a.state_id",'left')
	                        ->get();                    
	        if($Q2->num_rows()>0){
	          $country=$Q2->row()->country;
	          $address=$Q2->row()->address;
	          $state=$Q2->row()->state;
	          $city=$Q2->row()->city;
	          $postcode=$Q2->row()->postcode;
	        }
	      }

        $custmer_details = '<span style="color:rgb(65, 59, 212);font-style:italic;">'.$this->CI->lang->line('shipping_address').'</span>';

        $custmer_details .= "<br><b>".$this->CI->lang->line('mobile')." :</b> ".$customer->mobile;
        $custmer_details .= "<br><b>".$this->CI->lang->line('name')." :</b> ".$customer->customer_name;
        $custmer_details .= "<br><b>".$this->CI->lang->line('address')." :</b> ".$address;
        $custmer_details .= "<br><b>".$this->CI->lang->line('postcode')." :</b> ".$postcode;
        $custmer_details .= "<br><b>".$this->CI->lang->line('city')." :</b> ".$city;
        $custmer_details .= "<br><b>".$this->CI->lang->line('state')." :</b> ".$state;

        $this->writeHTMLCell($w, $h, $x ='6', $y='90', $custmer_details, 1, 0, 1, true, 'J', true);
        return $this;
    }

    public function _get_bank_details()
    {
    	$store = $this->store;
        $w = 100;
        $h = 40;
        $invoice_details = "";
        $invoice_details = '<span style="color:rgb(65, 59, 212);font-style:italic;">'.$this->CI->lang->line("bank_details").'</span><br>';
        $invoice_details .= nl2br($store->bank_details);

        $this->writeHTMLCell($w, $h, $x ='104', $y='', $invoice_details, 1, 1, 1, true, 'J', true);
        return $this;
    }

   
	public function show_pdf()
	{
		$this->_main_body();
	}

	

	public function _main_body()
	{	
		$sales 		= $this->sales;//array()
		$store 		= $this->store;//array()
		$customer 	= $this->customer;//array()
			
	

		$this->_invoice_name = "Tax Invoice";

		$this->page_title = "GST Invoice";

		//Don't change this
		$this->_invoice_format = 'GST';

		//$this->_QRCODE = $sales->sales_code;

		// set font
		$this->setFont($this->get_font_name(), 'B', 20);

		// add a page
		$this->AddPage();

		// Cusomer Details
		$this->_get_customer_details(); 

		// Cusomer Details
		$this->_get_invoice_details();

		// Shipping Details
		$this->_get_shipping_address(); 

		// Bank Details
		$this->_get_bank_details();

		//Set document name (footer -R)
		$this->_set_document_name($this->CI->lang->line("invoice_number"));
		
		//Sey document number (footer -R)
		$this->_set_document_number($sales->sales_code);

		//Search Coupon Details
		$coupon_code = $coupon_type = '';
	    $coupon_value=0;
	    if(!empty($sales->coupon_id)){
	      $coupon_details =get_customer_coupon_details($sales->coupon_id);
	      $coupon_code =$coupon_details->code;
	      $coupon_value =$coupon_details->value;
	      $coupon_type =$coupon_details->type;
	    } 
		$this->setFont($this->get_font_name(), '', 8);

		// set cell padding
		$this->setCellPaddings(1, 1, 1, 1);

		// set cell margins
		//$this->setCellMargins(1, 1, 1, 1);

		// set color for background
		$this->setFillColor(255, 255, 255);

		$this->Ln(0);

		

		$this->setFont($this->get_font_name(), '', 8);
		
		
		$tbl = '
		<style type="text/css">
			table, td, th {
			    border-collapse: collapse;
			    border: 0.01px solid    #26066c  ;
			    
			}
			
			table + table, table + table tr:first-child th, table + table tr:first-child td {
			    border-top: 0;
			}
			.text-right{
				text-align: right;
			}
			.text-center{
				text-align: center;
			}
			.text-bold{
				font-weight: bold;
			}
			.bg-light-blue{
				background-color: #e4eaff;
			}
			
		</style>
		<table >';

			$widthArray = array(
				'sl_no' 		=> '5',
				'description' 	=> '29',
				'hsn' 			=> '10',
				'gst_rate' 		=> '10',
				'qty' 			=> '8',
				'unit' 			=> '6',
				'before_tax' 	=> '10',
				'discount' 		=> '10',
				'amount' 		=> '12',

			);

			//Sum the value
			$sumOfWidth = 0;

			$colW =array();
			foreach($widthArray as $key => $val){
				

				//Update value
				$colWidthSize[$key] = $val;

				//New Array => Reasssign % symbol
				$colW[$key] = $val.'%';

				//Sum of value
				$sumOfWidth+=$val;
			}

			
			
		    $tbl .='<thead>
		        <tr class="bg-light-blue text-bold">
			        <th colspan="1" style="width: '.$colW['sl_no'].'">'.$this->CI->lang->line("sl_no").'</th>
			        <th colspan="1" style="width: '.$colW['description'].'" >'.$this->CI->lang->line("description").'</th>
			        <th colspan="1" style="width: '.$colW['hsn'].'">'.$this->CI->lang->line("hsn").'</th>
			        <th colspan="1" style="width: '.$colW['gst_rate'].'">'.$this->CI->lang->line("gst_rate").'</th>
			        <th colspan="1" style="width: '.$colW['qty'].'">'.$this->CI->lang->line("qty").'</th>
			        <th colspan="1" style="width: '.$colW['unit'].'">'.$this->CI->lang->line("unit").'</th>
			        <th colspan="1" style="width: '.$colW['before_tax'].'">'.$this->CI->lang->line("unit_price").'</th>
			        <th colspan="1" style="width: '.$colW['discount'].'">'.$this->CI->lang->line("discount_per").'</th>
			        <th colspan="1" style="width: '.$colW['amount'].'">'.$this->CI->lang->line("amount").'</th>
		        </tr>
		    </thead>
		    <tbody>';
		        
		      $i=1;
              $tot_qty=0;
              $tot_sales_price=0;
              $tot_tax_amt=0;
              $tot_discount_amt=0;
              $tot_unit_total_cost=0;
              $tot_total_cost=0;
              $tot_before_tax=0;
              $total_cost=0;
              
              $tot_price_per_unit=0;
              $sum_of_tot_price=0;

          

              $this->CI->db->select(" a.description,c.item_name, a.sales_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost , d.unit_name,c.sku,c.hsn
                              ");
              $this->CI->db->where("a.sales_id",$this->sales_id);
              $this->CI->db->from("db_salesitems a");
              $this->CI->db->join("db_tax b","b.id=a.tax_id","left");
              $this->CI->db->join("db_items c","c.id=a.item_id","left");
              $this->CI->db->join("db_units d","d.id = c.unit_id","left");

              $q2=$this->CI->db->get();

		        foreach ($q2->result() as $res2) {
                  $discount = (empty($res2->discount_input)||$res2->discount_input==0)? store_number_format(0):store_number_format($res2->discount_input)."%";
                  $discount_amt = (empty($res2->discount_amt)||$res2->discount_input==0)? '0':$res2->discount_amt."";
                  $before_tax=$res2->price_per_unit;// * $res2->sales_qty;
                  $total_cost=$res2->total_cost;//$before_tax * $res2->sales_qty;

                  $unit_total_cost = $res2->unit_total_cost - ($res2->tax_amt/$res2->sales_qty);

                  $tax_type = ($res2->tax_type=='Exclusive') ? 'Exc.' : 'Inc.';

                 $tbl .='<tr style="" nobr="true">';
				      $tbl .='<td colspan="1" style="width: '.$colW['sl_no'].'">'.$i++.'</td>';
				      $tbl .='<td colspan="1" style="width: '.$colW['description'].'" >';
				      $tbl .= $res2->item_name;
				      $tbl .= (!empty($res2->description)) ? "<br><i>[".nl2br($res2->description)."]</i>" : '';
				      $tbl .= '</td>';
				      $tbl .='<td colspan="1" style="width: '.$colW['hsn'].'">'.$res2->hsn.'</td>';
				      $tbl .='<td colspan="1" style="width: '.$colW['gst_rate'].'">'.store_number_format($res2->tax)." ".$tax_type.'</td>';

				      $tbl .='<td colspan="1" style="width: '.$colW['qty'].'">'.format_qty($res2->sales_qty).'</td>';
				      $tbl .='<td colspan="1" style="width: '.$colW['unit'].'">'.$res2->unit_name.'</td>';

				      $tbl .='<td colspan="1" class="text-right" style="width: '.$colW['before_tax'].'">'.store_number_format($unit_total_cost).'</td>';
				          
					
					  
				      $tbl .='<td colspan="1" class="text-right" style="width: '.$colW['discount'].'">'.($discount).'</td>';
				      $tbl .='<td colspan="1" class="text-right" style="width: '.$colW['amount'].'">'.store_number_format($total_cost).'</td>';
		          $tbl .='</tr>';

                  $tot_qty +=$res2->sales_qty;
                  $tot_sales_price +=$res2->price_per_unit;
                  $tot_tax_amt +=$res2->tax_amt;
                  $tot_discount_amt +=$res2->discount_amt;
                  $tot_unit_total_cost +=$unit_total_cost;
                  $tot_before_tax +=$before_tax;
                  $tot_total_cost +=$total_cost;
              }

		    $tbl .='</tbody>
		    
		</table>
		';

		$tbl .='<table>
		            <tbody>';

		            	$tbl .='<tr nobr="true" class="text-bold">';
		                	$tbl .='<td colspan="4" style="width:'.($colWidthSize['sl_no']+$colWidthSize['description']+$colWidthSize['hsn']+$colWidthSize['gst_rate']).'%">';
		                	$tbl .=$this->CI->lang->line("total");
		                	$tbl .='</td>';

		                	$tbl .='<td colspan="1" style="width:'.($colWidthSize['qty']).'%">';
		                	$tbl .=format_qty($tot_qty);
		                	$tbl .='</td>';

		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['unit']).'%">';
		                	$tbl .='';
		                	$tbl .='</td>';

		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['before_tax']).'%">';
			                $tbl .='';
			                $tbl .='</td>';


			                $tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['discount']).'%">';
		                	$tbl .='';
		                	$tbl .='</td>';

		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($tot_total_cost);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		                //Total Before Tax
		                $tbl .='<tr nobr="true">
		                	<td colspan="7" class="text-right" style="width:'.($sumOfWidth-$colWidthSize['amount']).'%">';
		                	$tbl .=$this->CI->lang->line("before_tax");
		                	$tbl .='</td>';
		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($tot_total_cost-$tot_tax_amt);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		                //Tax Total
		                $tbl .='<tr nobr="true">
		                	<td colspan="7" class="text-right" style="width:'.($sumOfWidth-$colWidthSize['amount']).'%">';
		                	$tbl .=$this->CI->lang->line("gst");
		                	$tbl .='</td>';
		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($tot_tax_amt);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		                //Tax Total
		                $tbl .='<tr nobr="true">
		                	<td colspan="7" class="text-right" style="width:'.($sumOfWidth-$colWidthSize['amount']).'%">';
		                	$tbl .=$this->CI->lang->line("other_charges");
		                	$tbl .='</td>';
		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($sales->other_charges_amt);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		                //Coupon
		                if(!empty($coupon_code)){
			                $tbl .='<tr nobr="true">
			                	<td colspan="4" style="width:'.($colWidthSize['sl_no']+$colWidthSize['description']+$colWidthSize['hsn']+$colWidthSize['gst_rate']+$colWidthSize['qty']).'%">';
			                	$tbl .="<b>".$this->CI->lang->line("couponCode")."</b>: ".getTruncatedCCNumber($coupon_code);
			                	$tbl .='</td>


			                	<td colspan="3" class="text-right" style="width:'.($colWidthSize['before_tax']+$colWidthSize['discount']).'%">';
			                	$tbl .=$this->CI->lang->line("couponDiscount").":";
			                	$tbl .=($coupon_type=='Percentage') ? $coupon_value .'%' : '[Fixed]' ;
			                	$tbl .='</td>

			                	<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
			                	$tbl .=store_number_format($sales->coupon_amt);
			                	$tbl .='</td>';
			                $tbl .='</tr>';
		            	}

		            	////Discount on All
		                $tbl .='<tr nobr="true">
		                	<td colspan="7" class="text-right" style="width:'.($sumOfWidth-$colWidthSize['amount']).'%">';
		                	$tbl .=$this->CI->lang->line("discount_on_all");
		                	$tbl .="[";
		                	$tbl .=store_number_format($sales->discount_to_all_input)." ".(($sales->discount_to_all_type=='percentage') ? '%' : 'Fixed');
		                	$tbl .="]";
		                	$tbl .='</td>';
		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($sales->tot_discount_to_all_amt);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		            	//Round Off
		                $tbl .='<tr nobr="true">
		                	<td colspan="7" class="text-right" style="width:'.($sumOfWidth-$colWidthSize['amount']).'%">';
		                	$tbl .=$this->CI->lang->line("round_off");
		                	$tbl .='</td>';
		                	$tbl .='<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($sales->round_off);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		                //Grand Total
		                $tbl .='<tr nobr="true">
		                	<td colspan="4" style="width:'.($colWidthSize['sl_no']+$colWidthSize['description']+$colWidthSize['hsn']+$colWidthSize['gst_rate']+$colWidthSize['qty']+$colWidthSize['unit']).'%">';
		                	$tbl .="<b>".$this->CI->lang->line("amount_in_words")."</b>: ".no_to_words($sales->grand_total);
		                	$tbl .='</td>


		                	<td colspan="3" class="text-right" style="width:'.($colWidthSize['before_tax']+$colWidthSize['discount']).'%">';
		                	$tbl .=$this->CI->lang->line("grand_total");
		                	$tbl .='</td>

		                	<td colspan="1" class="text-right" style="width:'.($colWidthSize['amount']).'%">';
		                	$tbl .=store_number_format($sales->grand_total);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		                //Note
		                $tbl .='<tr nobr="true">
		                	<td colspan="8" style="width:'.($sumOfWidth).'%; height:30px;">';
		                	$tbl .="<b>".$this->CI->lang->line("note")."</b>: ".nl2br($sales->sales_note);
		                	$tbl .='</td>';
		                $tbl .='</tr>';

		            $tbl .='</tbody>
		        </table>
		       
		        ';
		 //Tax Details

		$tbl .='<table>
		            <tbody>';
		            	$tbl .='<tr nobr="true">';
		            		$tbl .='<td colspan="9" class="text-bold">';
		            			$tbl .=$this->CI->lang->line("tax_details");
		            		$tbl .='</td>';
		            	$tbl .='</tr>';





		            	$tbl .='<tr class="bg-light-blue text-bold text-center" nobr="true">';
                          $tbl .='<td colspan="1" class="" rowspan="2" width="5%">'.$this->CI->lang->line('sl_no').'</td>';
                          $tbl .='<td colspan="1" class="" rowspan="2" width="10%">'.$this->CI->lang->line('hsn/sac').'</td>';
                          $tbl .='<td colspan="1" class="" rowspan="2" width="15%">'.$this->CI->lang->line('taxable_amount').'</td>';
                          $tbl .='<td class="" colspan="2"  width="20%">';
                          	$tbl .=$this->CI->lang->line('cgst');
                          $tbl .='</td>';
                          $tbl .='<td class="" colspan="2" width="20%">';
                            $tbl .=$this->CI->lang->line('sgst');
                          $tbl .='</td>';
                          $tbl .='<td class="" colspan="2" width="20%">';
                            $tbl .=$this->CI->lang->line('igst');
                          $tbl .='</td>';
                          $tbl .='<td colspan="2" class="" width="10%" rowspan="2">';
                            $tbl .=$this->CI->lang->line('total');
                          $tbl .='</td>';
                        $tbl .='</tr>';

                        $tbl .='<tr nobr="true" class="text-center bg-light-blue text-bold">';
                          $tbl .='<td>Rate(%)</td><td>Amt</td>';
                          $tbl .='<td>Rate(%)</td><td>Amt</td>';
                          $tbl .='<td>Rate(%)</td><td>Amt</td>';
                          
                        $tbl .='</tr>';


                        //HSN wise tax group
                        $tot_price_before_tax = $tot_price_after_tax = $tot_cgst_amt =$tot_sgst_amt=$tot_sgst_amt=$tot_igst_amt = 0;
                        $this->CI->db->select("c.item_name,
                        				   COALESCE(SUM(a.price_per_unit),0) AS price_before_tax, 
                                           b.tax,b.tax_name,c.hsn,
                                           COALESCE(SUM(a.tax_amt),0) AS sum_of_tax_amt,
                                           COALESCE(SUM(a.total_cost),0) AS price_after_tax,c.tax_type,
                                           c.sku 
                                         ");

                        $this->CI->db->where("a.sales_id",$this->sales_id);
                        $this->CI->db->from("db_salesitems a");
                        $this->CI->db->join("db_tax b","b.id=a.tax_id","left");
                        $this->CI->db->join("db_items c","c.id=a.item_id","left");
                        $this->CI->db->join("db_units d","d.id = c.unit_id","left");
                        $this->CI->db->group_by("c.hsn,a.tax_id");
                        $this->CI->db->order_by("a.id");
                        //echo $this->CI->db->get_compiled_select();exit();
                        $q2=$this->CI->db->get();

                        $i = 1;
                        foreach ($q2->result() as $res2) {
                          $hsn = $res2->hsn;
                          //$price_before_tax = $res2->price_before_tax;
                          $price_before_tax = $res2->price_before_tax;
                          $price_after_tax = $res2->price_after_tax;

                          $tax_per = $res2->tax;
                          $sum_of_tax_amt = $res2->sum_of_tax_amt;

                          $price_before_tax = $price_after_tax - $sum_of_tax_amt;

                          $tax_type='';
                          //$tax_type = ($res2->tax_type=='Exclusive') ? 'Exc.' : 'Inc.';
                          if( $customer->id==1 || (strtoupper($this->customer_state_name) == strtoupper($store->state))){
                            $sgst_per = $cgst_per = $tax_per;
                            $sgst_amt = $cgst_amt = $sum_of_tax_amt / 2;
                            $igst_per = $igst_amt = 0;
                          }else{
                            $sgst_per = $cgst_per = 0;
                            $sgst_amt = $cgst_amt = 0;
                            $igst_per = $tax_per;
                            $igst_amt = $sum_of_tax_amt;
                          }
                          

             

                        $tbl .='<tr nobr="true">';
                        			$tbl .='<td>'.$i++.'</td>';
                        			$tbl .='<td>'.$hsn.'</td>';
                        			$tbl .='<td class="text-right">'.store_number_format($price_before_tax)." ".$tax_type.'</td>';
                        			//$tbl .='<td>00</td>';

                        			$tbl .='<td>'.((!empty($cgst_per))? store_number_format($cgst_per/2):'').'</td>';

                        			$tbl .='<td class="text-right">'.store_number_format($cgst_amt).'</td>';

                        			$tbl .='<td>'.((!empty($sgst_per))? store_number_format($sgst_per/2):'').'</td>';
                        			$tbl .='<td class="text-right">'.store_number_format($sgst_amt).'</td>';

                        			$tbl .='<td>'.((!empty($igst_per))? store_number_format($igst_per):'').'</td>';
                        			$tbl .='<td class="text-right">'.store_number_format($igst_amt).'</td>';
                        			
                        			$tbl .='<td colspan="2" class="text-right">'.store_number_format($price_after_tax).'</td>';
                        		$tbl .='</tr>';


                   

                       $tot_price_before_tax +=$price_before_tax;
                       $tot_price_after_tax +=(!empty($price_after_tax)) ? $price_after_tax : 0;
                       $tot_cgst_amt +=(!empty($cgst_amt)) ? $cgst_amt : 0;
                       $tot_sgst_amt +=(!empty($sgst_amt)) ? $sgst_amt : 0;
                       $tot_igst_amt +=(!empty($igst_amt)) ? $igst_amt : 0;
                       
                     } 


                     $tbl .='<tr nobr="true" class="text-bold">';
                        			$tbl .='<td colspan="4" class="text-right">'.$this->CI->lang->line('total').'</td>';
                        			
                        			$tbl .='<td class="text-right">'.store_number_format($tot_cgst_amt).'</td>';

                        			$tbl .='<td></td>';
                        			$tbl .='<td class="text-right">'.store_number_format($tot_sgst_amt).'</td>';

                        			$tbl .='<td></td>';
                        			$tbl .='<td class="text-right">'.store_number_format($tot_igst_amt).'</td>';
                        			
                        			$tbl .='<td colspan="2" class="text-right">'.store_number_format($tot_price_after_tax).'</td>';
                      $tbl .='</tr>';


		            $tbl .='</tbody>
		        </table>
		       
		        ';

		$tbl .='<table nobr="true">
		            <tbody>
		                <tr nobr="true">



		                    <td colspan="12"><div style="font-size:10px;"><span style="color:rgb(65, 59, 212);font-style:italic;">'.$this->CI->lang->line("termsAndConditions").':</span><br>';
		                        	$tbl .=nl2br(html_entity_decode($sales->invoice_terms));
		                    		$tbl .='</div>
		                    </td>


		                    <td colspan="6" style="vertical-align:bottom;text-align:center;min-height:60px;height:60px;">
		                    	<div style="font-size:10px;">
			                    	<span style="color:rgb(65, 59, 212);font-style:italic;vertical-align:bottom;">
			                    	'.$this->CI->lang->line("authorised_signatory").'
			                    	:</span>
		                    	</div>';
		                    	if(!empty($this->get_signature_image_path())){
			                    	$tbl .='<div>
			                    		<img style="height:60px;" src="'.$this->get_signature_image_path().'"/>
			                    	</div>';
		                    	}

		                    $tbl .='</td>


		                </tr>
		                <tr nobr="true">
		                    <td colspan="18" class="text-center">';
		                        	$tbl .=nl2br($store->sales_invoice_footer_text);
		                    		$tbl .='
		                    </td>
		                </tr>
		            </tbody>
		        </table>
		       
		        ';

		      //  echo $tbl;exit;
		$this->writeHTMLCell('', '', $x ='', $y='', $tbl, 0, 1, 1, true, 'J', true);

		$this->Output('invoice_100.pdf', 'I');
	}

}