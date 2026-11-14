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
require_once('tcpdf_include.php');

class MYPDF extends TCPDF {

    protected $last_page_flag = false;

    public function Close() {
        $this->last_page_flag = true;
        parent::Close();
    }



    public function _get_invoice_title()
    {
        // Set font
        $this->setFont('helvetica', '', 15);
        // Title
        $this->writeHTMLCell(0, 50, $x ='', $y='8', strtoupper('Tax Invoice'), $border = 0, 1, 0, true, 'C', true);
        $this->Ln();
        return $this;
    }

    public function _get_logo()
    {   
        $image_file = K_PATH_IMAGES.'logo_example.jpg';
        $this->Image($image_file, $x = 5.5, $y = 16, 30, '', 'JPG', '', 'T', false, 300, '', false, false, $border =0, false, false, false);
        return $this;
    }

    public function _get_company_details()
    {
        $txt='';
        $txt .= '<span style="font-size:16px;font-weight:500">Ultimate Inventory POS Billing</span>';
        $txt .= '<br><span style="font-size:12px;">Channamma Circle, Kabbur PIN- 591222,</span>';
        $txt .= '<br><span style="font-size:12px;">Tal-Chikodi, Dist-Belgaum, State-Karnataka, </span>';
        $txt .= '<br><span style="font-size:12px;"><b>Mobile :</b> 08012345678, 08098765432 <b>Email:</b> support@martpoint.com.ng</span>';
        $txt .= '<br><span style="font-size:12px;"><b>TAX 1 :</b> TAX98797788889 <b>PAN:</b> PAN876876867</span>';
        

        $this->setFont('dejavusans', '', 14, '', true);

        $this->writeHTMLCell($w =135, 0, $x='38', $y='16', $txt, $border = 0, 0, 0, true, '', true);
        return $this;
    }
    public function _get_qr()
    {
        // new style
        $style = array(
            'border' => false,
            'padding' => 0,
            'fgcolor' => array(3, 1, 56),
            'bgcolor' => false
        );

        $w = $h = 30;
        
        // QRCODE,H : QR-CODE Best error correction
        $x = 174;// for RTL 203
        $this->write2DBarcode('www.tcpdf.org', 'QRCODE,H', $x, $y='', $w, $h, $style, 'N');
        //$this->Text(140, 205, 'QRCODE H - NO PADDING');
        return $this;
    }

    public function _get_hr()
    {
        // set color for background
        $this->setFillColor(65, 59, 212);

        $this->setFont('helvetica', '', 0.80);

        $this->MultiCell('', '', $txt ='', $border =0, 'L', 1, 1, $x = '', $y = '50', true,1, true);
        return $this;
    }

    public function _get_customer_details()
    {   
        $w = 100;
        $h = 40;

        $custmer_details = '<b style="color:rgb(65, 59, 212);font-style:italic;">Customer Details:</b>';
        $custmer_details .= "<br><b>Name :</b> MartPoint Retail";
        $custmer_details .= "<br><b>Address :</b> 123 Business Street, Lagos, Nigeria";
        $custmer_details .= "<br><b>Mobile :</b> +2348012345678, +2348098765432";
        $custmer_details .= "<br><b>Email :</b> support@martpoint.com.ng";
        $custmer_details .= "<br><b>GSTIN :</b> GSTIN12313";

        //$this->setCellMargins(1,1,1,1);
        $this->setCellPaddings(2,1,1,1);
        $this->setFont('dejavusans', '', 10);
        $this->setFillColor(255, 255, 255);

        $this->writeHTMLCell($w, $h, $x ='', $y='52', $custmer_details, 1, 0, 1, true, 'J', true);
        return $this;
    } 

    public function _get_invoice_details()
    {
        $w = 100;
        $h = 40;
        $invoice_details = "";
        $invoice_details .= '<b>Invoice No. :</b> <span style="font-size:16px;">INV/2022/00014</span>';
        $invoice_details .= '<br><b>Invoice Date :</b> <span style="">05-Sep-2022</span>';
        $invoice_details .= '<br><b>Reference No. :</b> <span style="">102121</span>';
        

        $this->writeHTMLCell($w, $h, $x ='104', $y='', $invoice_details, 1, 1, 1, true, 'J', true);
        return $this;
    }

    public function _get_shipping_address()
    {
        $w = 100;
        $h = 40;

        $custmer_details = '<b style="color:rgb(65, 59, 212);font-style:italic;">Shipping Details:</b>';
        $custmer_details .= "<br><b>Name :</b> MartPoint Retail";
        $custmer_details .= "<br><b>Address :</b> 123 Business Street, Lagos, Nigeria";
        $custmer_details .= "<br><b>Mobile :</b> +2348012345678, +2348098765432";
        $custmer_details .= "<br><b>Email :</b> support@martpoint.com.ng";
        $custmer_details .= "<br><b>GSTIN :</b> GSTIN12313";

        //$this->setCellMargins(1,1,1,1);
        $this->setCellPaddings(2,1,1,1);
        $this->setFont('dejavusans', '', 10);
        $this->setFillColor(255, 255, 255);

        $this->writeHTMLCell($w, $h, $x ='', $y='90', $custmer_details, 1, 0, 1, true, 'J', true);
        return $this;
    }

    public function _get_e_way_details()
    {
        $w = 100;
        $h = 40;
        $invoice_details = "";
        $invoice_details .= '<b>Invoice No. :</b> <span style="font-size:16px;">INV/2022/00014</span>';
        $invoice_details .= '<br><b>Invoice Date :</b> <span style="">05-Sep-2022</span>';
        $invoice_details .= '<br><b>Reference No. :</b> <span style="">102121</span>';
        

        $this->writeHTMLCell($w, $h, $x ='104', $y='', $invoice_details, 1, 1, 1, true, 'J', true);
        return $this;
    }

    public function _get_terms()
    {   
        $w = 70;
        $h = 40;


        $tc = '<div style="font-size:10px;"><b style="color:rgb(65, 59, 212);font-style:italic;">Terms & Conditions:</b>';
        $tc .= "<br>";
        $tc .= "<br>1.Lorem ipsum dolor sit amet, consectetur adip sit amet, consectetur adip";
        $tc .= "<br>2.Lorem ipsum dolor sit amet, consectetur adip";
        $tc .= "<br>4.Lorem ipsum dolor sit amet, consectetur adip ";
        $tc .= "<br>4.Lorem ipsum dolor sit amet, consectetur adip</div>";

        //$this->setCellMargins(1,1,1,1);
        $this->setCellPaddings(2,1,1,1);
        $this->setFont('dejavusans', '', 10);
        $this->setFillColor(255, 255, 255);

        //$this->writeHTMLCell($w, $h, $x ='7', $y='-45', $tc, 1, 0, 1, true, 'J', true);


            $this->MultiCell($w, $h, $tc, $border = 1, 'L', 1, 1, $x = '7', $y = '250', true, 1, true);


        //$this->Cell(0, 10, 'Hello Footer '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        return $this;
    }
    public function _get_signature()
    {
        $w = 57;
        $h = 40;


        $tc = '<div style="font-size:10px;">';
        $tc .= "<br>";
        $tc .= "<br>";
        $tc .= "<br>";
        $tc .= "<br>";
        $tc .= "<br>";
        $tc .= "<br>";
        $tc .= "<br>";
        $tc .= "<br>";


        $tc .= "<br>Authorized Signature</div>";

        //$this->setCellMargins(1,1,1,1);
        $this->setCellPaddings(2,1,1,1);
        $this->setFont('dejavusans', '', 10);
        $this->setFillColor(255, 255, 255);

        $this->MultiCell($w, $h, $tc, $border = 1, 'C', 1, 1, $x = '147', $y = '250', true, 1, true, $autopadding=true, $maxh=0, $valign='T', $fitcell=false);

        return $this;
    }


    public function _get_bank_details()
    {
        $w = 70;
        $h = 40;


        $bank = '<div style="font-size:10px;"><b style="color:rgb(65, 59, 212);font-style:italic;">Bank Details:</b>';
        $bank .= "<br>";
        $bank .= "<br>Bank Name : First Bank of Nigeria";
        $bank .= "<br>Holder Name: MartPoint Retail";
        $bank .= "<br>Account No.: 0123456789";
        $bank .= "<br>Sort Code: 011234567";

        //$this->setCellMargins(1,1,1,1);
        $this->setCellPaddings(2,1,1,1);
        $this->setFont('dejavusans', '', 10);
        $this->setFillColor(255, 255, 255);

        //$this->writeHTMLCell($w, $h, $x ='7', $y='-45', $tc, 1, 0, 1, true, 'J', true);


        $this->MultiCell($w, $h, $bank, $border = 1, 'L', 1, 1, $x = '77', $y = '250', true, 1, true);


        //$this->Cell(0, 10, 'Hello Footer '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        return $this;
    }

    //Page header
    public function Header() {
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

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->setY(-8);
        // Set font
        $this->setFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Hello Footer '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        //Last page
        if($this->last_page_flag){
            //T&C
            $this->_get_terms();

            //Bank Details
            $this->_get_bank_details();

            //Signature
            $this->_get_signature();
        }

    }

    public function _table_tag($thead='',$tbody='',$tfoot='')
    {

        $tbl = '<table border="0.02" cellpadding="1" cellspacing="0">'.$thead.$tbody.$tfoot.'</table>';

        return $tbl;

    }

    public function _thead_tag($tr='')
    {

        $tbl = '<thead>'.$tr.'</thead>';

    }
    public function _tbody_tag($tr='')
    {

        $tbl = '<tbody>'.$tr.'</tbody>';

    }


    public function _tr_th_tag()
    {
        $th =  '<td>Sl.No</td>';
        $th .=  '<td>Name</td>';
        $th .=  '<td>Mobile</td>';
        $th .=  '<td>Email</td>';
        $tbl = '<tr>'.$th.'</tr>';
        return $tbl;
    }

    public function _tr_td_tag()
    {
        $data1 = rand(1,3);
        $data1 = rand(1,20);
        $data1 = rand(1,30);
        $data1 = rand(1,40);
        $td =  '<td>'.$data1.'</td>';
        $td .=  '<td>'.$data2.'</td>';
        $td .=  '<td>'.$data3.'</td>';
        $td .=  '<td>'.$data4.'</td>';
        $tbl = '<tr>'.$td.'</tr>';
        return $tbl;
    }

    public function _create_record()
    {
        $th = $this->_tr_th_tag();
        $thead = $this->_thead_tag($th);

        $td = $this->_tr_td_tag();
        $tbody = $this->_tbody_tag($td);

    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$rtl = false;

if($rtl == true){
    // set some language dependent data:
    $lg = Array();
    $lg['a_meta_charset'] = 'UTF-8';
    $lg['a_meta_dir'] = 'rtl';
    $lg['a_meta_language'] = 'fa';
    $lg['w_page'] = 'page';

    // set some language-dependent strings (optional)
    $pdf->setLanguageArray($lg);
}
else{
    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }
}


// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Nicola Asuni');
$pdf->setTitle('TCPDF Example 048');
$pdf->setSubject('TCPDF Tutorial');
$pdf->setKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 048', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
 $pdf->setMargins($PDF_MARGIN_LEFT=5, $PDF_MARGIN_TOP=52, $PDF_MARGIN_RIGHT=5);
 $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
//$pdf->setAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM=50);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

 

// ---------------------------------------------------------

// set font
$pdf->setFont('helvetica', 'B', 20);

// add a page
$pdf->AddPage();

// Cusomer Details
$pdf->_get_customer_details($pdf); 

// Cusomer Details
$pdf->_get_invoice_details($pdf);

// Shipping Details
$pdf->_get_shipping_address($pdf); 

// E-way Details
$pdf->_get_e_way_details($pdf);


$pdf->setFont('helvetica', '', 8);





/*******************/
// set cell padding
//$pdf->setCellPaddings(1, 1, 1, 1);

// set cell margins
//$pdf->setCellMargins(1, 1, 1, 1);

// set color for background
$pdf->setFillColor(255, 255, 255);

$pdf->Ln(0);

//$pdf->Cell(199, 0, 'TEST CELL STRETCH: no stretch', 1, 1, 'C', 0, '', 0);
/*$pdf->Cell(0, 0, 'TEST CELL STRETCH: no stretch', 1, 1, 'C', 0, '', $stretch = 0);
$pdf->Ln(5);
$pdf->Cell(45, 0, 'TEST CELL STRETCH: scaling sakdhkashdk\nadd', 1, 1, 'C', 0, '', $stretch = 1);
$pdf->Ln(5);

$html_top = '';*/

$html = '';


// Table with rowspans and THEAD
$table_footer ='
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Total</td>
        <td colspan="1">10000.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Before Tax</td>
        <td colspan="1">8000.00</td>
    </tr>
     <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Tax</td>
        <td colspan="1">2000.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Other Charges</td>
        <td colspan="1">1000.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Total Discount</td>
        <td colspan="1">3000.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Grand Total</td>
        <td colspan="1">9000.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Paid Amount</td>
        <td colspan="1">8000.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Previous Due</td>
        <td colspan="1">00.00</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;font-weight: bold;">Total Due</td>
        <td colspan="1">1000.00</td>
    </tr>
    ';
$tbl = '

<table border="0.02" cellpadding="1" cellspacing="0">
    <thead>
        <tr style="background-color:#FF0000;">
            <th>#</th>
            <th>#</th>
            <th>#</th>
            <th>#</th>
        </tr>
    </thead>
    <tbody>';
        $j = 40;
        if($j <= 10){
            // set auto page breaks
            $pdf->setAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM=50);
        }
        else{
            // set auto page breaks
            $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        }
        for ($i=1; $i <= $j ; $i++) {
            $tbl .= '<tr>';
                $tbl .= '<td>'.$i.'</td>';
                $tbl .= '<td>'.rand().'</td>';
                $tbl .= '<td>'.rand().'</td>';
                $tbl .= '<td>'.rand().'</td>';
            $tbl .= '</tr>';

            if($i==25){
                $tbl .='<tr style="background-color:#FFFF00;color:#0000FF;">
                    <td colspan="2">Total</td>
                    <td colspan="2" >1000.00</td>
                </tr>';

                $tbl .='<tr style="text-align:right;font-style: italic;">
                    <td colspan="4">Continue</td>
                    
                </tr>';
                $tbl .="<tcpdf method=\"AddPage\" />";
            }

        }

        $tbl .=$table_footer;

    $tbl .='</tbody>

</table>
';
$first = $pdf->getY();
//$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->writeHTMLCell('', '', $x ='', $y='', $tbl, 0, 1, 1, true, 'J', true);


//$pdf->writeHTMLCell('', '', $x ='', $y='', $tbl, 0, 1, 1, true, 'J', true);


/*$fonts = array('times', 'dejavuserif');
$alignments = array('L' => 'LEFT', 'C' => 'CENTER', 'R' => 'RIGHT', 'J' => 'JUSTIFY');

// Test all cases using CSS stretching/spacing properties
foreach ($fonts as $fkey => $font) {
    $pdf->setFont($font, '', 11);
    foreach ($alignments as $align_mode => $align_name) {
        for ($stretching = 90; $stretching <= 110; $stretching += 10) {
            for ($spacing = -0.254; $spacing <= 0.254; $spacing += 0.254) {
                $html = '<span style="font-stretch:'.$stretching.'%;letter-spacing:'.$spacing.'mm;"><span style="color:red;">'.$align_name.'</span> | <span style="color:green;">Stretching = '.$stretching.'%</span> | <span style="color:blue;">Spacing = '.sprintf('%+.3F', $spacing).'mm</span><br />Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</span>';
                $pdf->writeHTMLCell(0, 0, '', '', $html, 1, 1, false, true, $align_mode, false);
            }
        }
        if (!(($fkey == 1) AND ($align_mode == 'J'))) {
            $pdf->AddPage();
        }
    }
}*/
$second = $pdf->getY();


$getNumLines = $pdf->getStringHeight('241',$tbl);



$html = 'getNumLines = '.$getNumLines.'Second = '.$second.'<span style="font-stretch:100%;letter-spacing:normal;">

<br />Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed im
perdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, 
ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. 
ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. 
ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. 
ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. 
ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. 
Class aptent taciti sociosqu ad litora torquent per conubia nostra, 
per inceptos himenaeos.</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 1, 1, false, true, 'Left', false);


//Close and output PDF document
$pdf->Output('example_048.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
