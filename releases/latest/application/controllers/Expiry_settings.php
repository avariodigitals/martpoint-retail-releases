<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expiry_settings extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!mp_feature_enabled('expiry_tracking')){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('expiry_settings_model','expiry');
	}

	public function index(){
		$this->permission_check('expiry_settings');
		$data = $this->data;
		$data['settings'] = $this->expiry->get_settings();
		$data['page_title'] = 'Expiry Settings';
		$this->load->view('expiry_settings', $data);
	}

	public function save(){
		$this->permission_check('expiry_settings');
		$result = $this->expiry->save_settings();
		if($result == "success"){
			$this->session->set_flashdata('success', 'Expiry Settings Updated Successfully!');
		} else {
			$this->session->set_flashdata('error', 'Failed to Update Settings!');
		}
		echo $result;
	}

	public function send_email_alert(){
		$this->permission_check('expiry_settings');
		$settings = $this->expiry->get_settings();

		if($settings->email_alerts_enabled != 1 || empty($settings->alert_email)){
			echo "Email alerts not configured.";
			return;
		}

		$expired = $this->expiry->get_expired_items();
		$expiring = $this->expiry->get_expiring_items();

		if(empty($expired) && empty($expiring)){
			echo "No items to alert.";
			return;
		}

		$store = get_store_details();
		$subject = ($store->store_name ?? 'MartPoint') . " - Expiry Alert (" . date('d M Y') . ")";

		// Build HTML body
		$html = "<h2>Expiry Alert Report - " . date('d M Y') . "</h2>";
		$html .= "<p><strong>Store:</strong> " . ($store->store_name ?? 'MartPoint') . "<br>";
		$html .= "<strong>Alert Days:</strong> " . $settings->alert_before_days . "</p><hr>";

		if(!empty($expired)){
			$html .= "<h3 style='color:#dc3545;'>&#9888; Expired Items (" . count($expired) . ")</h3>";
			$html .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;max-width:600px;'>";
			$html .= "<tr style='background:#f8d7da;'><th>Item</th><th>Code</th><th>Expiry</th><th>Stock</th></tr>";
			foreach($expired as $item){
				$html .= "<tr><td>" . htmlspecialchars($item->item_name) . "</td><td>" . htmlspecialchars($item->item_code) . "</td><td>" . $item->expire_date . "</td><td>" . $item->stock . "</td></tr>";
			}
			$html .= "</table><br>";
		}

		if(!empty($expiring)){
			$html .= "<h3 style='color:#ffc107;'>&#9201; Expiring Soon (" . count($expiring) . ")</h3>";
			$html .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;max-width:600px;'>";
			$html .= "<tr style='background:#fff3cd;'><th>Item</th><th>Code</th><th>Expiry</th><th>Stock</th></tr>";
			foreach($expiring as $item){
				$html .= "<tr><td>" . htmlspecialchars($item->item_name) . "</td><td>" . htmlspecialchars($item->item_code) . "</td><td>" . $item->expire_date . "</td><td>" . $item->stock . "</td></tr>";
			}
			$html .= "</table><br>";
		}

		$html .= "<p><a href='" . base_url('expired_items_report') . "' style='display:inline-block;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:5px;'>View Full Report</a></p>";
		$html .= "<hr><p style='color:#888;font-size:12px;'>Sent by MartPoint Retail at " . date('Y-m-d H:i:s') . "</p>";

		// Use Email_service instead of raw mail() so SMTP/Resend works
		$this->load->model('email_service');
		$result = $this->email_service->sendRaw(
			$settings->alert_email,
			$subject,
			$html,
			'',  // plain text version empty, Email_service handles fallback
			['template_key' => 'expiry_alert']
		);

		if($result['success']){
			echo "success";
		} else {
			echo "Failed: " . $result['message'];
		}
	}
}
