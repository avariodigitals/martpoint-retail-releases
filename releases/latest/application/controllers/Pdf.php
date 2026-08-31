<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf extends MY_Controller {

	private function load_dompdf(){
		require_once(APPPATH . 'libraries/dompdf/autoload.inc.php');
	}
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

		//Validate Record Authentication
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}

		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_invoice');
        $data=array_merge($data,array('sales_id'=>$sales_id));

		$this->load->view('print-sales-invoice-whatsapp',$data);

		// Get output html
        $html = $this->output->get_output();

		$this->load_dompdf();
		mb_internal_encoding('UTF-8');

        $options = new Options();
		$options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html,'UTF-8');

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');/*landscape or portrait*/

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF (1 = download and 0 = preview)
        $dompdf->stream("Sales-invoice-$sales_id-".date('M')."_".date('d')."_".date('Y'), array("Attachment"=>0));
		exit;
	}

}