<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Palm / vegetable oil plant workspace.
 *
 * This is the operations entry point for the oil_processing_plant preset. It
 * composes the existing purchasing, stock, formula, production, quotation and
 * invoicing modules around the way a plant works, without exposing the retail
 * till as the primary action.
 */
class Oil_processing extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
    }

    private function _check_feature() {
        if (!function_exists('mp_feature_enabled') || !mp_feature_enabled('oil_processing_workflow')) {
            $this->show_feature_not_activated(
                'oil_processing_workflow',
                'Select Processing Plant (Palm / Vegetable Oil) in Business Profile to open the plant workspace.'
            );
        }
    }

    public function index() {
        $this->_check_feature();
        $this->permission_check('dashboard_view');

        $store_id = get_current_store_id();
        $this->load->model('Oil_processing_model', 'oil_workspace');
        $stats = $this->oil_workspace->dashboard_stats($store_id);
        $recent_runs = [];

        if ($this->db->table_exists('db_production_batches')) {
            $recent_runs = $this->db
                ->where('store_id', $store_id)
                ->order_by('scheduled_date', 'DESC')
                ->order_by('id', 'DESC')
                ->limit(6)
                ->get('db_production_batches')->result();
        }

        $data = $this->data ?? [];
        $data['page_title'] = 'Processing Plant';
        $data['stats'] = $stats;
        $data['recent_runs'] = $recent_runs;
        $data['can_sales_view'] = $this->permissions('sales_view');
        $data['commercial'] = null;
        $data['intel'] = array();
        try {
            $this->load->model('workspace_commercial_model', 'workspace_commercial');
            $data['commercial'] = $data['can_sales_view'] ? $this->workspace_commercial->snapshot($store_id) : null;
            $visible_stats = array(
                'low_materials' => $this->permissions('items_view') ? $stats['low_materials'] : 0,
                'open_quotes' => $this->permissions('quotation_view') ? $stats['open_quotes'] : 0,
            );
            $data['intel'] = $this->workspace_commercial->insights(
                $data['commercial'] ?: array('overdue_invoices'=>0, 'billed_today'=>0, 'billed_yesterday'=>0, 'collected_today'=>0),
                false, $visible_stats);
        } catch (Throwable $e) {
            log_message('error', 'Plant commercial dashboard failed: '.$e->getMessage());
        }
        $data['content'] = $this->load->view('oil_processing/dashboard', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /** Plant-only document header. The two switches share one optional banner image. */
    public function document_style(){
        $this->_check_feature();
        if((mp_get_store_profile()['industry_type'] ?? '') !== 'oil_processing_plant'){ show_404(); return; }
        if(!is_store_admin() && !$this->permissions('store_edit')){ $this->show_access_denied_page(); return; }
        $store_id = (int)get_current_store_id();
        $data = $this->data ?? array();
        $data['page_title'] = 'Quotation & Invoice Banner';
        $data['quotation_banner_enabled'] = (int)mp_get_store_setting($store_id, 'plant_documents', 'quotation_banner_enabled', 0);
        $data['invoice_banner_enabled'] = (int)mp_get_store_setting($store_id, 'plant_documents', 'invoice_banner_enabled', 0);
        $data['banner_path'] = (string)mp_get_store_setting($store_id, 'plant_documents', 'banner_path', '');
        if(is_mobile()){
            $this->load->view('mobile/plant_document_style', $data);
            return;
        }
        $data['content'] = $this->load->view('oil_processing/document_style', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function tax_rates(){
        $this->_check_feature();
        if((mp_get_store_profile()['industry_type'] ?? '') !== 'oil_processing_plant'){ show_404(); return; }
        $this->permission_check('tax_view');
        if(!is_mobile()){ redirect('tax'); return; }
        $data = $this->data ?? array();
        $data['page_title'] = 'Tax Rates';
        $data['tax_rates'] = $this->db->select('id,tax_name,tax,status')->where('store_id',get_current_store_id())->where('group_bit IS NULL',null,false)->order_by('tax_name')->get('db_tax')->result();
        $this->load->view('mobile/plant_tax_rates',$data);
    }

    public function save_tax_rate(){
        $this->_check_feature();
        if((mp_get_store_profile()['industry_type'] ?? '') !== 'oil_processing_plant'){ show_404(); return; }
        $this->permission_check('tax_add');
        if(strtoupper($this->input->method()) !== 'POST'){ show_404(); return; }
        $name = trim((string)$this->input->post('tax_name',TRUE));
        $rate = filter_var($this->input->post('tax',TRUE),FILTER_VALIDATE_FLOAT);
        if($name === '' || mb_strlen($name) > 100 || $rate === false || $rate < 0 || $rate > 100){
            $this->session->set_flashdata('error','Enter a tax name and a percentage from 0 to 100.');
        }elseif($this->db->where('store_id',get_current_store_id())->where('tax_name',$name)->count_all_results('db_tax')){
            $this->session->set_flashdata('error','A tax rate with that name already exists.');
        }else{
            $saved = $this->db->insert('db_tax',array('store_id'=>get_current_store_id(),'tax_name'=>$name,'tax'=>$rate,'status'=>1));
            $this->session->set_flashdata($saved ? 'success' : 'error',$saved ? 'Tax rate saved.' : 'Could not save tax rate.');
        }
        redirect(is_mobile() ? 'oil_processing/tax_rates' : 'tax');
    }

    public function save_document_style(){
        $this->_check_feature();
        if((mp_get_store_profile()['industry_type'] ?? '') !== 'oil_processing_plant'){ show_404(); return; }
        if(!is_store_admin() && !$this->permissions('store_edit')){ $this->show_access_denied_page(); return; }
        if(strtoupper($this->input->method()) !== 'POST'){ show_404(); return; }
        $store_id = (int)get_current_store_id();
        $old_path = (string)mp_get_store_setting($store_id, 'plant_documents', 'banner_path', '');
        $new_path = '';
        if(!empty($_FILES['banner']['name'])){
            $file = $_FILES['banner'];
            if($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3145728){
                $this->session->set_flashdata('error', 'Upload a PNG or JPG banner smaller than 3 MB.');
                redirect('oil_processing/document_style'); return;
            }
            $info = @getimagesize($file['tmp_name']);
            $mime = $info['mime'] ?? '';
            if(!in_array($mime, array('image/png','image/jpeg'), true) || $info[0] < 600 || $info[1] < 80){
                $this->session->set_flashdata('error', 'Use a PNG or JPG banner at least 600 × 80 pixels.');
                redirect('oil_processing/document_style'); return;
            }
            $folder = FCPATH.'uploads/plant_documents/'.$store_id.'/';
            if(!is_dir($folder) && !mkdir($folder, 0755, true)){
                $this->session->set_flashdata('error', 'Could not create the banner folder.');
                redirect('oil_processing/document_style'); return;
            }
            $name = bin2hex(random_bytes(12)).($mime === 'image/png' ? '.png' : '.jpg');
            if(!move_uploaded_file($file['tmp_name'], $folder.$name)){
                $this->session->set_flashdata('error', 'Banner upload failed.');
                redirect('oil_processing/document_style'); return;
            }
            $new_path = 'uploads/plant_documents/'.$store_id.'/'.$name;
        }
        $this->db->trans_begin();
        $ok = mp_set_store_setting($store_id, 'plant_documents', 'quotation_banner_enabled', $this->input->post('quotation_banner') ? 1 : 0, 'int');
        $ok = mp_set_store_setting($store_id, 'plant_documents', 'invoice_banner_enabled', $this->input->post('invoice_banner') ? 1 : 0, 'int') && $ok;
        if($new_path !== ''){ $ok = mp_set_store_setting($store_id, 'plant_documents', 'banner_path', $new_path, 'string') && $ok; }
        if($this->input->post('remove_banner')){ $ok = mp_set_store_setting($store_id, 'plant_documents', 'banner_path', '', 'string') && $ok; }
        if(!$ok || $this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            if($new_path !== ''){ @unlink(FCPATH.$new_path); }
            $this->session->set_flashdata('error', 'Could not save document style.');
        }else{
            $this->db->trans_commit();
            if($this->input->post('remove_banner') && $new_path !== ''){ @unlink(FCPATH.$new_path); }
            if(($new_path !== '' || $this->input->post('remove_banner'))
                && preg_match('#^uploads/plant_documents/'.$store_id.'/[a-f0-9]{24}\.(png|jpg)$#', $old_path)){
                @unlink(FCPATH.$old_path);
            }
            $this->session->set_flashdata('success', 'Quotation and invoice banner settings saved.');
        }
        redirect('oil_processing/document_style');
    }
}
