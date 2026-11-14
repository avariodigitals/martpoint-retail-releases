<?php
//============================================================+
// File name   : example_048.php
// Begin       : 2009-03-20
// Last Update : 2013-05-14
//
// Description : Example 048 for TCPDF class
//               HTML tables and table headers
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: HTML tables and table headers
 * @author Nicola Asuni
 * @since 2009-03-20
 */

// Include the main TCPDF library (search for installation path).
//require_once('tcpdf_include.php');

include dirname(__DIR__).'/examples/tcpdf_include.php';

class MyPDF extends TCPDF {

    protected $last_page_flag = false;
    
    public $_invoice_name = "Invoice Name";

    //public $_QRCODE = null;

    public $CI = null;

    public $store =array();

    public $_document_name = '';

    public $_document_number = '';

    public $_rtl = false;

    public $page_title = 'Default Invoice';

    public $_invoice_format = 'Defult';

    public $_app_language = 'English';

    public function __construct()
    {
        parent::__construct();
        //Do your magic here

        $this->CI =& get_instance();

        $this->store = get_store_details();

        $this->_document_settings();
    }

    public function Close() {
        $this->last_page_flag = true;
        parent::Close();
    }

    public function _set_header()
    {   

        $customer = $this->customer;//array()

        $store = $this->store;//array()

        //Customer Records
        $state = (!empty($customer->state_id)) ? get_state_details($customer->state_id) : '';
        $customer_state_name = (!empty($state)) ? $state->state : $store->state;

        $w = 100;
        $h = 40;

        $custmer_details = '<span style="color:rgb(65, 59, 212);font-style:italic;">'.$this->CI->lang->line('bill_to').':</span>';
        $custmer_details .= "<br><b>".$this->CI->lang->line('name')." :</b> ".$customer->customer_name;
        $custmer_details .= "<br><b>".$this->CI->lang->line('address')." :</b> ".$customer->address;
        $custmer_details .= "<br><b>".$this->CI->lang->line('postcode')." :</b> ".$customer->postcode;
        $custmer_details .= "<br><b>".$this->CI->lang->line('mobile')." :</b> ".$customer->mobile;
        $custmer_details .= "<br><b>".$this->CI->lang->line('email')." :</b> ".$customer->email;
        $custmer_details .= "<br><b>".$this->CI->lang->line('gst_number')." :</b> ".$customer->gst_no; 

        $this->writeHTMLCell($w, $h, $x ='6', $y='52', $custmer_details, 1, 0, 1, true, 'J', true);
        return $this;
    } 

    public function _rtl($rtl=false)
    {
        $this->_rtl = $rtl;

        //Download fonts from :https://www.fontmirror.com/
        
        /*$path = dirname(__DIR__).'/fonts/Garet Book 300.ttf';
        $strBNFont = TCPDF_FONTS::addTTFfont($path, 'TrueTypeUnicode', '', 32);
        
        if($strBNFont){
            echo "Installation succed";
        }
        else{
            echo "Failed to install font";
        }
        exit;*/

        if($rtl == true){
            // set some language dependent data:
            $lg = Array();
            $lg['a_meta_charset'] = 'UTF-8';
            $lg['a_meta_dir'] = 'rtl';
            $lg['a_meta_language'] = 'fa';
            $lg['w_page'] = 'page';

            // set some language-dependent strings (optional)
            $this->setLanguageArray($lg);
        }
        else{
            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $this->setLanguageArray($l);
            }
        }
    }

    public function get_font_name()
    {   
        /**
         * 1. English
         * 2. Russian
         * 3. Spanish
         * 4. Arabic
         * 5. Bangla
         * 6. French
         * */

        $lang_array = array();
        /**
         * English Fonts:
         * 
         * helvetica 
        */
        
        $lang_array['English'] = 'helvetica';

        /**
         * Arabic Fonts:
         * 
         * aefurat - GOOD
         * aealarabiya
         */
        
        $lang_array['Arabic'] = 'aefurat';

        /**
         * Bangla Fonts:
         * 
         * nikoshgrameem300 -GOOD
         * nikoshlightban300
         * nikosh400
         * 
         */

        $lang_array['Bangla'] = 'nikoshgrameem300';

        /**
         * Russian Fonts:
         * 
         * garetbook300
         * 
         * */
        $lang_array['Russian'] = 'garetbook300';


        if(strtoupper($this->_app_language)==strtoupper('Bangla')){
            return $lang_array['Bangla'];
        }
        else if(strtoupper($this->_app_language)==strtoupper('Arabic')){
            return $lang_array['Arabic'];
        }
        else if(strtoupper($this->_app_language)==strtoupper('Russian')){
            return $lang_array['Russian'];
        }
        else{
            return $lang_array['English'];
        }
    }
    public function _get_invoice_title()
    {
        // Set font
        $this->setFont($this->get_font_name(), '', 15);

        // Title
        $this->writeHTMLCell(0, 50, $x ='', $y='8', strtoupper($this->_invoice_name), $border = 0, 1, 0, true, 'C', true);
        $this->Ln();
        return $this;
    }

    public function _get_logo()
    {   

        $image_file=(!empty($this->store->store_logo)) ? $this->store->store_logo : store_demo_logo();

        $this->Image($image_file, $x = 5.5, $y = 16, 30, '', '', '', 'T', false, 300, '', false, false, $border =0, false, false, false);
        return $this;
    }

    public function _get_company_details()
    {
        $store = $this->store;
        $txt='';
        $txt .= '<span style="font-size:16px;font-weight:500">'.$store->store_name.'</span>';
        $txt .= '<br><span style="font-size:12px;">'.$store->address.'</span>';

        $address_line_2 = '';
        if(!empty($store->city)){
            $address_line_2 .= $store->city;
        }
        if(!empty($store->state)){
            $address_line_2 .= ", ".$store->state;
        }
        if(!empty($store->postcode)){
            $address_line_2 .= ", -".$store->postcode;
        }
            $txt .= '<br><span style="font-size:12px;">'.$address_line_2.'</span>';

        $txt .= '<br><span style="font-size:12px;"><b>'.$this->CI->lang->line('mobile').' :</b>'.$store->mobile.'<b> '.$this->CI->lang->line('email').':</b> '.$store->email.'</span>';
        $txt .= '<br><span style="font-size:12px;"><b>';
            if($this->_invoice_format=='Default'){
               $txt .= $this->CI->lang->line('tax_number').' :</b> '.$store->vat_no; 
            }
            else{
                $txt .= $this->CI->lang->line('gst_number').' :</b> '.$store->gst_no;
            }
        $txt .= ' <b>'.$this->CI->lang->line('website').':</b> '.$store->store_website.'</span>';
        

        $this->setFont($this->get_font_name(), '', 14, '', true);

        $this->writeHTMLCell($w =135, 0, $x='38', $y='16', $txt, $border = 0, 0, 0, true, '', true);
        return $this;
    }
    public function _get_qr()
    {
        $qr_data = $this->_get_qr_data();
        
        // new style
        $style = array(
            'border' => false,
            'padding' => 0,
            'fgcolor' => array( 38, 6, 108 ),
            'bgcolor' => false
        );

        $w = $h = 30;
        
        // QRCODE,H : QR-CODE Best error correction
        $x = ($this->_rtl) ? 203 : 174;// for RTL 203
        $this->write2DBarcode($qr_data, 'QRCODE,H', $x, $y='', $w, $h, $style, 'N');
        //$this->Text(140, 205, 'QRCODE H - NO PADDING');
        return $this;
    }

    public function _get_hr()
    {
        // set color for background
        $this->setFillColor(65, 59, 212);

        $this->setFont($this->get_font_name(), '', 0.80);

        $this->MultiCell('', '', $txt ='', $border =0, 'L', 1, 1, $x = '', $y = '50', true,1, true);
        return $this;
    }

    public function _is_rtl_lang()
    {   
        //Based on Session variable check RTL
        $lang = trim(strtoupper($this->CI->session->userdata('language')));

        $this->_app_language = $lang;

        $rtl_languages = array(strtoupper('arabic'),strtoupper('urdu'));

        return (in_array($lang, $rtl_languages)) ? true : false;

    }

    //Page header
    public function Header() {

        //Auto find RTL language
        $this->_rtl($this->_is_rtl_lang());

        $this->_document_details();

        // Title
        $this->_get_invoice_title();

        // Logo
        $this->_get_logo();

        // Company Details
        $this->_get_company_details();

        // QRCODE
        $this->_get_qr();

        // Horizontal Line
        $this->_get_hr(); 

        /*// Cusomer Details
        $this->_get_customer_details(); 

        // Cusomer Details
        $this->_get_invoice_details();

        // Shipping Details
        $this->_get_shipping_address(); 

        // E-way Details
        $this->_get_e_way_details(); */

        //$this->_main_body();



    }

    public function _get_qr_data(){
        $store = $this->store;
        $sales = $this->sales;
        $customer = $this->customer;

        $str = '';
        $str .= 'Seller Name:'.$store->store_name;
        $str .= PHP_EOL;
        $str .= 'Buyer Name:'.$customer->customer_name;
        $str .= PHP_EOL;
        $str .= 'Buyer Tax Number:'.$store->vat_no;
        $str .= PHP_EOL;
        $str .= 'Invoice Number:'.$sales->sales_code;
        $str .= PHP_EOL;
        $str .= 'Date & Time:'.$sales->created_date." ".$sales->created_time;
        $str .= PHP_EOL;
        $str .= 'TAX Total:'.store_number_format(get_sales_tax_total($sales->id));
        $str .= PHP_EOL;
        $str .= 'Invoice Total:'.store_number_format($sales->grand_total);
        return $str;
    }

    //set footer document details
    public function _set_document_name($value='')
    {
        $this->_document_name = $value;
    }

    //set footer document number
    public function _set_document_number($value='')
    {
        $this->_document_number = $value;
    }
    
    //Signature Image
    public function get_signature_image_path(){
        $image_file=($this->store->show_signature && !empty($this->store->signature)) ? $this->store->signature : '';

       // $this->Image($image_file, $x = 5.5, $y = 16, 30, '', '', '', 'T', false, 300, '', false, false, $border =0, false, false, false);
        return $image_file;
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->setY(-8);
        // Set font
        $this->setFont($this->get_font_name(), 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        $this->Cell(0, 10, $this->_document_name.' : '.$this->_document_number, 0, false, 'R', 0, '', 0, false, 'T', 'M');

        //Last page
        if($this->last_page_flag){
            //T&C
            //$this->_get_terms();

            //Bank Details
            //$this->_get_bank_details();

            //Signature
            //$this->_get_signature();
        }

    }

    // set document information
    public function _document_details()
    {
        $this->setCreator(PDF_CREATOR);
        $this->setAuthor('Billing Boook');
        $this->setTitle($this->page_title);
        $this->setSubject('Invoice Details');
        $this->setKeywords('TCPDF, PDF, example, test, guide');
    }

    //Document Settings
    public function _document_settings($value='')
    {
        // set default header data
        $this->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 048', PDF_HEADER_STRING);

        // set header and footer fonts
        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
         $this->setMargins($PDF_MARGIN_LEFT=5, $PDF_MARGIN_TOP=52, $PDF_MARGIN_RIGHT=5);
         $this->setHeaderMargin(PDF_MARGIN_HEADER);
        $this->setFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }
}
