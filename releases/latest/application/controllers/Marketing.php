<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MartPoint Retail — Marketing landing controller (desktop).
 *
 * Renders the desktop Marketing landing page inside the AdminLTE shell.
 * The item list, ordering and permission gating come from the shared
 * marketing_menu_items() helper (application/helpers/marketing_helper.php),
 * which is also used by Mobile::marketing() — so mobile and desktop share
 * one source of truth for the menu logic. Each item carries url_desktop
 * so clicks stay inside desktop controllers.
 */
class Marketing extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function index()
	{
		// Show the landing page if the user holds any marketing permission.
		if(!(
			$this->permissions('discountCouponView') ||
			$this->permissions('customerCouponView') ||
			$this->permissions('discountCouponAdd') ||
			$this->permissions('customerCouponAdd') ||
			$this->permissions('loyalty_view') ||
			$this->permissions('gift_cards_view') ||
			$this->permissions('store_credit_view') ||
			$this->permissions('customers_view') ||
			$this->permissions('send_email') ||
			$this->permissions('send_sms')
		)){
			$this->show_access_denied_page();
		}

		$data = $this->data;
		$data['page_title'] = 'Marketing';
		$data['marketing_items'] = function_exists('marketing_menu_items') ? marketing_menu_items() : [];
		$data['content'] = $this->load->view('marketing/desktop/overview', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/* ================= PHASE 3: SEGMENTS ================= */

	/** Viewing a segment's membership needs only customers_view. */
	private function _can_segment(){
		return $this->permissions('customers_view') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}
	/** Creating/deleting saved segments mutates shared config — edit perm. */
	private function _can_segment_manage(){
		return $this->permissions('customers_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}
	private function _can_send(){
		return $this->permissions('send_email') || $this->permissions('send_sms') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}
	/**
	 * Channel-specific gate: email campaigns need send_email, SMS needs
	 * send_sms — spending credit on a channel requires that channel's
	 * permission, not just "any send" or customers_view.
	 */
	private function _can_send_channel($channel){
		$perm = $channel === 'sms' ? 'send_sms' : 'send_email';
		return $this->permissions($perm) || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	public function segments(){
		if(!$this->_can_segment()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$this->load->model('Marketing_model','marketing_m');
		$data = array_merge($this->data, [
			'page_title' => 'Customer Segments',
			'segments' => $this->marketing_m->listSegments($storeId),
			'saved'    => $this->marketing_m->listSavedSegments($storeId),
		]);
		$data['content'] = $this->load->view('marketing/desktop/segments', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** JSON: preview a segment's audience (first 50 + total count). */
	public function segment_preview(){
		if(!$this->_can_segment()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$key = preg_replace('/[^a-z0-9_]/', '', (string)$this->input->post('segment_key'));
		$segmentId = (int)$this->input->post('segment_id');
		$this->load->model('Marketing_model','marketing_m');
		if($segmentId){
			$saved = $this->marketing_m->getSegment($segmentId, $storeId);
			if(!$saved){ echo json_encode(['status'=>'error','message'=>'Segment not found']); return; }
			$key = $saved->segment_key;
		}
		$audience = $this->marketing_m->resolveSegment($storeId, $key);
		$out = [];
		foreach(array_slice($audience, 0, 50) as $m){
			$out[] = ['name' => $m->name ?: '—', 'email' => $m->email ?: '', 'phone' => $m->phone ?: ''];
		}
		echo json_encode(['status'=>'success','count'=>count($audience),'sample'=>$out]);
	}

	public function save_segment(){
		if(!$this->_can_segment_manage()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$this->load->model('Marketing_model','marketing_m');
		$storeId = get_current_store_id();
		$key = preg_replace('/[^a-z0-9_]/', '', (string)$this->input->post('segment_key'));
		if(!isset(Marketing_model::$SEGMENTS[$key])){ echo json_encode(['status'=>'error','message'=>'Unknown segment']); return; }
		$name = trim((string)$this->input->post('name')) ?: Marketing_model::$SEGMENTS[$key][0];
		$id = $this->marketing_m->saveSegment($storeId, $name, $key);
		echo json_encode(['status' => $id ? 'success' : 'error', 'message' => $id ? 'Segment saved' : 'Save failed']);
	}

	public function delete_segment(){
		if(!$this->_can_segment_manage()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$this->load->model('Marketing_model','marketing_m');
		$ok = $this->marketing_m->deleteSegment((int)$this->input->post('id'), get_current_store_id());
		echo json_encode(['status' => $ok ? 'success' : 'error']);
	}

	/* ================= PHASE 3: CAMPAIGNS ================= */

	public function campaigns(){
		if(!$this->_can_send()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$this->load->model('Marketing_model','marketing_m');
		// Provider status for cost/config disclosure in the UI.
		$this->load->model('email_settings_model');
		$emailCfg = $this->email_settings_model->getSettings($storeId);
		$data = array_merge($this->data, [
			'page_title'  => 'Campaigns',
			'campaigns'   => $this->marketing_m->listCampaigns($storeId),
			'segments'    => Marketing_model::$SEGMENTS,
			'saved'       => $this->marketing_m->listSavedSegments($storeId),
			'email_ready' => !empty($emailCfg['provider']),
			'sms_ready'   => (bool)mp_get_store_notification_setting($storeId, 'sms_status', 0),
		]);
		$data['content'] = $this->load->view('marketing/desktop/campaigns', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_campaign(){
		if(!$this->_can_send()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$this->load->model('Marketing_model','marketing_m');
		$storeId = get_current_store_id();
		$segKey = preg_replace('/[^a-z0-9_]/', '', (string)$this->input->post('segment_key'));
		$segId  = (int)$this->input->post('segment_id') ?: null;
		if($segId){
			if(!$this->marketing_m->getSegment($segId, $storeId)){ echo json_encode(['status'=>'error','message'=>'Saved segment not found']); return; }
		} elseif(!isset(Marketing_model::$SEGMENTS[$segKey])){
			echo json_encode(['status'=>'error','message'=>'Choose an audience']); return;
		}
		$channel = $this->input->post('channel') === 'sms' ? 'sms' : 'email';
		if(!$this->_can_send_channel($channel)){ echo json_encode(['status'=>'error','message'=>'You do not have permission to create ' . strtoupper($channel) . ' campaigns']); return; }
		$id = $this->marketing_m->saveCampaign($storeId, [
			'name'        => $this->input->post('name'),
			'segment_key' => $segId ? null : $segKey,
			'segment_id'  => $segId,
			'channel'     => $this->input->post('channel'),
			'subject'     => $this->input->post('subject'),
			'message'     => $this->input->post('message'),
		], (int)$this->input->post('id') ?: null);
		echo json_encode(['status' => $id ? 'success' : 'error', 'id' => $id, 'message' => $id ? 'Campaign saved' : 'Save failed (sent campaigns are immutable)']);
	}

	/** Dispatch a draft campaign — staged sends then per-recipient send. */
	public function send_campaign(){
		if(!$this->_can_send()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$this->load->model('Marketing_model','marketing_m');
		$cid = (int)$this->input->post('id');
		$testOnly = (bool)$this->input->post('test');
		$campaign = $this->marketing_m->getCampaign($cid, $storeId);
		if(!$campaign){ echo json_encode(['status'=>'error','message'=>'Campaign not found']); return; }
		// Channel permission is enforced per send — send_email does not
		// authorise SMS spend and vice versa.
		if(!$this->_can_send_channel($campaign->channel)){ echo json_encode(['status'=>'error','message'=>'No ' . strtoupper($campaign->channel) . ' send permission']); return; }
		// draft starts, partial/sending resume — 'sent' is terminal.
		if($campaign->status === 'sent' && !$testOnly){ echo json_encode(['status'=>'error','message'=>'Campaign already fully dispatched']); return; }

		// Resolve audience — saved segment wins, else the preset key. Never
		// fall back to 'all' when a saved segment was deleted or the key is
		// unknown: fail loudly rather than mail every customer.
		$key = $campaign->segment_key;
		if($campaign->segment_id){
			$saved = $this->marketing_m->getSegment($campaign->segment_id, $storeId);
			if(!$saved){ echo json_encode(['status'=>'error','message'=>'Saved segment no longer exists — edit the campaign audience']); return; }
			$key = $saved->segment_key;
		}
		if(!isset(Marketing_model::$SEGMENTS[$key])){ echo json_encode(['status'=>'error','message'=>'Campaign has no valid audience']); return; }
		$audience = $this->marketing_m->resolveSegment($storeId, $key);

		if($testOnly){
			// Controlled test: send to the store owner only, touch nothing.
			$store = get_store_details($storeId);
			$to = $campaign->channel === 'email' ? ($store->email ?? '') : ($store->phone ?? '');
			if($to === ''){ echo json_encode(['status'=>'error','message'=>'No store owner ' . $campaign->channel . ' configured']); return; }
			$fakeSend = (object)['recipient' => $to, 'customer_name' => 'Store owner (test)', 'unsub_token' => null];
			$res = $this->_dispatchCampaignMessage($campaign, $fakeSend, $storeId);
			echo json_encode(['status' => $res['ok'] ? 'success' : 'error', 'message' => $res['ok'] ? 'Test accepted for ' . $to : $res['error']]);
			return;
		}

		$this->marketing_m->stageCampaignAudience($campaign, $audience);
		// Recover rows stranded in 'sending' by an earlier interrupted run.
		$this->marketing_m->releaseStuckSends();
		$this->db->where('id', $cid)->update('db_campaigns', ['status' => 'sending']);

		// Batch: at most 60 provider calls per request — the cron sweep
		// finishes the rest. A rate-limit error pauses the batch, leaves
		// the send pending, and the campaign resumes on the next run.
		$rateLimited = false;
		foreach($this->marketing_m->getPendingSends($cid, 60) as $send){
			// Atomic claim — a parallel dispatch can never take this row.
			$claimed = $this->marketing_m->claimSend($send->id);
			if(!$claimed) continue;
			// Send-time eligibility: suppression wins even when the
			// opt-out happened AFTER the audience was staged.
			if($this->marketing_m->isSuppressed($storeId, $campaign->channel, $send->recipient)){
				$this->marketing_m->markSend($send->id, 'suppressed', 'recipient opted out');
				continue;
			}
			$res = $this->_dispatchCampaignMessage($campaign, $send, $storeId);
			if(!empty($res['rate_limited'])){
				// Back to pending — will retry on the next dispatch run.
				$this->db->where('id', $send->id)->update('db_campaign_sends', ['status' => 'pending', 'error' => 'rate limited']);
				$rateLimited = true;
				break;
			}
			$this->marketing_m->markSend($send->id, $res['ok'] ? 'sent' : 'failed', $res['error'] ?? null);
		}
		$stats = $this->marketing_m->campaignSendStats($cid);
		$remaining = $stats['pending'] + $stats['sending'];
		$status = $remaining > 0 ? 'partial' : 'sent';
		$this->db->where('id', $cid)->update('db_campaigns', ['status' => $status, 'sent_at' => date('Y-m-d H:i:s')]);
		$msg = "{$stats['sent']} accepted, {$stats['failed']} failed, {$stats['suppressed']} opted out" . ($remaining ? ", {$remaining} still queued" : '');
		if($rateLimited) $msg .= ' — provider rate limit hit, queued sends resume automatically';
		echo json_encode(['status'=>'success','message'=>$msg,'sent'=>$stats['sent'],'failed'=>$stats['failed'],'suppressed'=>$stats['suppressed'],'queued'=>$remaining,'rate_limited'=>$rateLimited]);
	}

	/** Requeue this campaign's failed sends (attempts<5) and resume. */
	public function retry_campaign(){
		if(!$this->_can_send()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$this->load->model('Marketing_model','marketing_m');
		$cid = (int)$this->input->post('id');
		$campaign = $this->marketing_m->getCampaign($cid, $storeId);
		if(!$campaign){ echo json_encode(['status'=>'error','message'=>'Campaign not found']); return; }
		$n = $this->marketing_m->requeueFailed($cid, $storeId);
		if($n > 0) $this->db->where('id', $cid)->where('store_id', $storeId)->update('db_campaigns', ['status' => 'partial']);
		echo json_encode(['status'=>'success','message'=>"{$n} failed send(s) requeued — sent/accepted rows are never re-contacted"]);
	}

	/**
	 * One provider call. Returns ok/error plus rate_limited when the
	 * provider reports 429-style throttling — the caller requeues rather
	 * than fails the send. Every recipient gets a working unsubscribe
	 * path: footer link for email, opt-out link appended to SMS.
	 */
	private function _dispatchCampaignMessage($campaign, $send, $storeId){
		$store = get_store_details($storeId);
		$storeName = $store->store_name ?? 'Our store';
		$recipient = $send->recipient;
		$name = $send->customer_name ?? '';
		$text = str_replace('{name}', $name ?: 'there', (string)$campaign->message);
		$unsubUrl = !empty($send->unsub_token) ? base_url('storefront/campaign_unsubscribe/' . $send->unsub_token) : null;
		if($campaign->channel === 'sms'){
			if($unsubUrl) $text .= ' Opt out: ' . $unsubUrl;
			$this->load->model('sms_model');
			$resp = $this->sms_model->send_sms($recipient, $text);
			$ok = is_string($resp) ? (stripos($resp, 'success') !== false || stripos($resp, 'sent') !== false) : (bool)$resp;
			$err = $ok ? null : (is_string($resp) ? $resp : 'SMS send failed');
			return ['ok' => $ok, 'error' => $err, 'rate_limited' => $err && preg_match('/rate.?limit|429|too many|throttl/i', $err)];
		}
		$this->load->model('email_service');
		$this->email_service->setStoreId($storeId);
		$html = '<p>Hi ' . htmlspecialchars($name ?: 'there') . ',</p><p>' . nl2br(htmlspecialchars($text)) . '</p>'
			. '<p style="color:#888;font-size:12px;">— ' . htmlspecialchars($storeName) . '</p>'
			. ($unsubUrl ? '<p style="color:#888;font-size:11px;"><a href="' . htmlspecialchars($unsubUrl) . '">Unsubscribe from these emails</a></p>' : '');
		$plain = $text . ($unsubUrl ? "\n\nUnsubscribe: {$unsubUrl}" : '');
		$res = $this->email_service->sendRaw($recipient, $campaign->subject ?: ('A message from ' . $storeName), $html, $plain, ['template_key' => 'campaign', 'related_module' => 'campaign', 'related_record_id' => $campaign->id]);
		$err = empty($res['success']) ? ($res['message'] ?? 'send failed') : null;
		return ['ok' => !empty($res['success']), 'error' => $err, 'rate_limited' => $err && preg_match('/rate.?limit|429|too many|throttl/i', $err)];
	}

	public function campaign_detail($id = 0){
		if(!$this->_can_send()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$this->load->model('Marketing_model','marketing_m');
		$campaign = $this->marketing_m->getCampaign((int)$id, $storeId);
		if(!$campaign){ show_404(); return; }
		$data = array_merge($this->data, [
			'page_title' => 'Campaign — ' . $campaign->name,
			'campaign'   => $campaign,
			'sends'      => $this->marketing_m->getCampaignSends($campaign->id, $storeId),
			'stats'      => $this->marketing_m->campaignSendStats($campaign->id),
		]);
		$data['content'] = $this->load->view('marketing/desktop/campaign_detail', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/* ================= PHASE 3: BACK-IN-STOCK ================= */

	public function stock_alerts(){
		if(!$this->_can_segment()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$this->load->model('Marketing_model','marketing_m');
		$data = array_merge($this->data, [
			'page_title' => 'Back-in-Stock Alerts',
			'alerts' => $this->marketing_m->listStockAlerts($storeId),
		]);
		$data['content'] = $this->load->view('marketing/desktop/stock_alerts', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
}
