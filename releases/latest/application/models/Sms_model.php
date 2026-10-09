<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_model extends CI_Model {
	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}
	/**
	 * Upsert one row per store into a provider credential table.
	 * Returns false when the query fails so callers can roll back.
	 */
	private function _provider_upsert($table, array $data, $store_id){
		if(!$this->db->table_exists($table)){
			return false;
		}
		$exists = $this->db->select('id')->where('store_id', $store_id)->get($table)->num_rows();
		if($exists > 0){
			return (bool)$this->db->where('store_id', $store_id)->update($table, $data);
		}
		$data['store_id'] = $store_id;
		return (bool)$this->db->insert($table, $data);
	}

	//UPDATE SMS API
	public function api_update(){
		$hidden_rowcount = (int)$this->input->post('hidden_rowcount', TRUE);
		$store_id = get_current_store_id();
		$this->db->trans_begin();

		// ---- HTTP/URL provider rows (db_smsapi) ----
		if($hidden_rowcount>0){
			$this->db->query("delete from db_smsapi where store_id=".$store_id);
			for($i=1; $i<=$hidden_rowcount; $i++){
				if(isset($_POST['info_'.$i])){
					$info 	 	= $_POST['info_'.$i];
					$key 	 	= $_POST['key_'.$i];
					$key_value 	= $_POST['key_val_'.$i];

					$q1=$this->db->query("insert into db_smsapi(
								info,`key`,key_value,store_id)
								values(
								'$info',
								'$key',
								'$key_value',
								$store_id
							)");
					if(!$q1){
						$this->db->trans_rollback();
						return "failed";
					}

				}//if end()
			}//for end()
		}

		// ---- Active provider selector ----
		// Stored in the structured notification settings table; db_store.sms_status
		// is kept in sync as a fallback for installs that predate that table.
		$sms_status = (int)$this->input->post('sms_status', TRUE);
		if($sms_status < 0 || $sms_status > 6){ $sms_status = 0; }
		if($this->db->table_exists('db_store_notification_settings')){
			if(!mp_set_store_notification_setting($store_id, 'sms_status', $sms_status)){
				$this->db->trans_rollback();
				return "failed";
			}
		}
		if($this->db->field_exists('sms_status', 'db_store')){
			if(!$this->db->where('id', $store_id)->update('db_store', ['sms_status' => $sms_status])){
				$this->db->trans_rollback();
				return "failed";
			}
		}

		// ---- Twilio ----
		$ok = $this->_provider_upsert('db_twilio', [
			'account_sid'  => (string)$this->input->post('account_sid', TRUE),
			'auth_token'   => (string)$this->input->post('auth_token', TRUE),
			'twilio_phone' => (string)$this->input->post('twilio_phone', TRUE),
		], $store_id);
		if(!$ok){ $this->db->trans_rollback(); return "failed"; }

		// ---- FiveMojo WhatsApp ----
		$ok = $this->_provider_upsert('db_fivemojo', [
			'url'         => (string)($this->input->post('whatsAppUrl', TRUE) ?: 'https://app.fivemojo.com/api/send.php'),
			'token'       => (string)$this->input->post('whatsAppToken', TRUE),
			'instance_id' => (string)$this->input->post('whatsAppInstanceId', TRUE),
		], $store_id);
		if(!$ok){ $this->db->trans_rollback(); return "failed"; }

		// ---- Brevo ----
		$ok = $this->_provider_upsert('db_brevo', [
			'api_key'     => (string)$this->input->post('brevo_api_key', TRUE),
			'sender_name' => (string)$this->input->post('brevo_sender_name', TRUE),
		], $store_id);
		if(!$ok){ $this->db->trans_rollback(); return "failed"; }

		// ---- Sendchamp ----
		if($this->db->table_exists('db_sendchamp')){
			$ok = $this->_provider_upsert('db_sendchamp', [
				'api_key'   => (string)$this->input->post('sendchamp_api_key', TRUE),
				'sender_id' => (string)($this->input->post('sendchamp_sender_id', TRUE) ?: 'MartPoint'),
				'route'     => (string)($this->input->post('sendchamp_route', TRUE) ?: 'non_dnd_nigeria'),
			], $store_id);
			if(!$ok){ $this->db->trans_rollback(); return "failed"; }
		}

		// ---- BulkSMSNigeria ----
		if($this->db->table_exists('db_bulksmsng')){
			$ok = $this->_provider_upsert('db_bulksmsng', [
				'api_token' => (string)$this->input->post('bulksmsng_api_token', TRUE),
				'sender_id' => (string)($this->input->post('bulksmsng_sender_id', TRUE) ?: 'BulkSMS'),
				'base_url'  => rtrim((string)($this->input->post('bulksmsng_base_url', TRUE) ?: 'https://www.bulksmsnigeria.com/api'), '/'),
				'gateway'   => (string)$this->input->post('bulksmsng_gateway', TRUE),
			], $store_id);
			if(!$ok){ $this->db->trans_rollback(); return "failed"; }
		}

		$this->session->set_flashdata('success', 'Record Successfully Saved!!');
		$this->db->trans_commit();
		return "success";
	}
	//Send Messagr
	public function send_sms($mobile,$message){

		$store_id = get_current_store_id();

		// Use modular notification settings first; fallback to db_store for legacy installs
		$sms_status = mp_get_store_notification_setting($store_id, 'sms_status', 0);
		if (empty($sms_status) && isset($store_rec->sms_status)) {
			$sms_status = $store_rec->sms_status;
		}

		if($sms_status==0){
			return "Sorry! Can't Send.Please Enable SMS";
		}
		if($sms_status==1){
			$q1=$this->db->query("select * from db_smsapi where store_id=".$store_id);
			if($q1->num_rows()>0){
				$api=array();
				foreach($q1->result() as $res1){
					if($res1->info =='message'){
						$api = array_merge($api, [$res1->key => ($message)]);
					}
					else if($res1->info =='mobile'){
						$api = array_merge($api, [$res1->key => $mobile]);
					}
					else{
						$api = array_merge($api, [$res1->key => $res1->key_value]);
					}
				}
				/*For Special characters need to set unicode Ex: Currency Symbols*/
				$api = array_merge($api, ['unicode' => '1']);
				
				//print_r($api);exit();

				$ch = curl_init();
				$data = http_build_query($api);
				$getUrl = $api['weblink']."?".$data;
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_URL, $getUrl);
				curl_setopt($ch, CURLOPT_TIMEOUT, 80);
				 
				$response = curl_exec($ch);
				
				 
				if(curl_error($ch)){
					return 'failed';
				}
				else
				{
					return 'success';
				}
				 
				curl_close($ch);
				//return $output;
			}
			else{
				return "API Not Available";
			}
		}
		if($sms_status==2){
			//Twilio SMS API
			$this->load->model('twilio_model');
			return $this->twilio_model->index($mobile,$message);
		}
		if($sms_status==3){
			//fivemojo WhatsApp API
			$this->load->model('fivemojo_model');
			return $this->fivemojo_model->index($mobile,$message);
		}
		if($sms_status==4){
			//Brevo SMS API
			$this->load->model('brevo_model');
			return $this->brevo_model->index($mobile,$message);
		}
		if($sms_status==5){
			//Sendchamp SMS API
			$this->load->model('sendchamp_model');
			return $this->sendchamp_model->index($mobile, $message, $store_id);
		}
		if($sms_status==6){
			//BulkSMSNigeria SMS API
			$this->load->model('bulksmsng_model');
			return $this->bulksmsng_model->index($mobile, $message, $store_id);
		}
		

	}

	/**
	 * Send the same message to many recipients.
	 *
	 * Where the active provider supports native bulk (a single API call for
	 * an array of numbers) this uses it, so a campaign costs ONE round trip
	 * instead of one per recipient. Otherwise it degrades to a per-recipient
	 * loop and returns a per-recipient result set, matching the shape a
	 * caller expects from a bulk send.
	 *
	 * @return array{total:int,successful:int,failed:int,results:array}
	 */
	public function send_bulk(array $recipients, $message, $store_id = null){
		$store_id = $store_id ?: get_current_store_id();
		$out = ['total' => count($recipients), 'successful' => 0, 'failed' => 0, 'results' => []];
		if(empty($recipients)){ return $out; }

		$sms_status = mp_get_store_notification_setting($store_id, 'sms_status', 0);
		if(empty($sms_status) && $this->db->field_exists('sms_status','db_store')){
			$rec = $this->db->select('sms_status')->where('id',$store_id)->get('db_store')->row();
			if($rec){ $sms_status = $rec->sms_status; }
		}

		// BulkSMSNigeria accepts an array in `to` — one call for everyone.
		if((int)$sms_status === 6){
			$this->load->model('bulksmsng_model');
			$res = $this->bulksmsng_model->send_bulk($recipients, $message, $store_id);
			if($res['ok']){
				$out['successful'] = count($recipients);
				foreach($recipients as $r){
					$out['results'][] = ['recipient' => $r, 'status' => 'success', 'data' => $res['raw']];
				}
			} else {
				// Provider-level failure applies to every recipient.
				$out['failed'] = count($recipients);
				foreach($recipients as $r){
					$out['results'][] = ['recipient' => $r, 'status' => 'failed', 'error' => $res['error']];
				}
			}
			return $out;
		}

		foreach($recipients as $r){
			$resp = $this->send_sms($r, $message);
			$ok = is_string($resp)
				? (stripos($resp,'success') !== false || stripos($resp,'sent') !== false)
				: (bool)$resp;
			if($ok){
				$out['successful']++;
				$out['results'][] = ['recipient' => $r, 'status' => 'success', 'data' => $resp];
			} else {
				$out['failed']++;
				$out['results'][] = ['recipient' => $r, 'status' => 'failed', 'error' => is_string($resp) ? $resp : 'send failed'];
			}
		}
		return $out;
	}

}

/* End of file Sms_model.php */
/* Location: ./application/models/Sms_model.php */