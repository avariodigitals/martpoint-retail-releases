<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/**
	 * Sales invoices
	 * 3. Default Format
	 * 4. GST invoice Format
	*/
	public function sales($sales_id=null){

		$params = array();

		//Validate Record Authenttication
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}

		//Select Store Invoice Format
		$invoice_format_id = get_invoice_format_id();

		$params['sales_id'] = $sales_id;

		if($invoice_format_id==4){
			//GST invoice
			$this->load->library('tcpdf/invoice/GstInvoice',$params);

			$this->gstinvoice->show_pdf();
		}
		else{
			//Default invoice
			$this->load->library('tcpdf/invoice/Sales',$params);

			$this->sales->show_pdf();
		}

	}

}