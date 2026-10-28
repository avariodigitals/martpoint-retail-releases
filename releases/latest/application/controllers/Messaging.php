<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Messaging extends MY_Controller {
    public function __construct() {
        parent::__construct(); $this->load_global();
        if (!($this->permissions('send_email') || $this->permissions('send_sms') || is_admin() || is_store_admin())) { $this->show_access_denied_page(); exit; }
        $this->load->library('messaging_client');
    }
    public function index() {
        if (mp_is_central()) { redirect('messaging_admin'); return; }
        $data = $this->data; $data['page_title'] = 'Messaging balance & packages';
        $data['messaging'] = $this->messaging_client->request('snapshot',get_current_store_id());
        $data['purchase_key'] = bin2hex(random_bytes(20));
        $data['store_name'] = get_store_name(); $data['SITE_TITLE'] = $data['store_name'];
        if (is_mobile() || $this->input->get('screen')==='mobile') { $this->load->view('mobile/messaging_balance',$data); return; }
        $data['content'] = $this->load->view('messaging/balance',$data,true); $this->load->view('mp_layout',$data);
    }
    public function action() {
        if ($this->input->method()!=='post') show_error('POST is required.',405);
        $action = (string)$this->input->post('action');
        if (!in_array($action,array('purchase','limits','request_addon','verify_purchase'),true)) show_error('Unknown messaging action.',400);
        $result = $this->messaging_client->request($action,get_current_store_id(),array(
            'provider'=>$this->input->post('provider'),'amount'=>$this->input->post('amount'),'email'=>$this->input->post('email'),
            'request_key'=>$this->input->post('request_key'),'daily_limit'=>$this->input->post('daily_limit'),
            'low_balance'=>$this->input->post('low_balance'),'addon'=>$this->input->post('addon'),'period'=>$this->input->post('period'),
            'reference'=>$this->input->post('reference')));
        if ($action==='purchase' && !empty($result['success']) && parse_url($result['checkout_url'] ?? '',PHP_URL_SCHEME)==='https') { redirect($result['checkout_url']); return; }
        $this->session->set_flashdata(!empty($result['success'])?'success':'failed',$result['message'] ?? 'Action completed.');
        redirect('messaging'.($this->input->post('screen')==='mobile'?'?screen=mobile':''));
    }
}
