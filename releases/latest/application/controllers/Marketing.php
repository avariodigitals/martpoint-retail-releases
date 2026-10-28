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
		$this->load->library('messaging_client');
		$messaging=$this->messaging_client->request('snapshot',$storeId);
		$data = array_merge($this->data, [
			'page_title'  => 'Campaigns',
			'campaigns'   => $this->marketing_m->listCampaigns($storeId),
			'segments'    => Marketing_model::$SEGMENTS,
			'saved'       => $this->marketing_m->listSavedSegments($storeId),
			'email_ready' => !empty($messaging['channels']['email']),
            'messaging' => $messaging,
			'sms_ready'   => !empty($messaging['channels']['sms']),
			'can_email'   => $this->_can_send_channel('email'),
			'can_sms'     => $this->_can_send_channel('sms'),
			'store_name'  => get_store_name(),
		]);
		if($this->input->get('screen') === 'mobile' || is_mobile()){
			$data['SITE_TITLE'] = $data['store_name'] ?: 'MartPoint';
			$this->load->view('mobile/marketing_campaigns', $data);
			return;
		}
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
        if(trim((string)$this->input->post('name'))==='' || trim((string)$this->input->post('message'))==='' || ($channel==='email' && trim((string)$this->input->post('subject'))==='')){
            echo json_encode(array('status'=>'error','message'=>'Enter a campaign name, message and an email subject when sending email.')); return;
        }

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

        $quote=$this->_campaignQuote($campaign,$testOnly);
        if(empty($quote['success']) || empty($quote['ready']) || (int)$this->input->post('approved_cost_kobo')!==(int)($quote['total_kobo']??-1)){
            echo json_encode(array('status'=>'error','message'=>$quote['message']??'Review and confirm the current messaging cost before sending.')); return;
        }
        if(!$testOnly){
            $approved=$this->messaging_client->request('authorize_campaign',$storeId,array('campaign_id'=>(string)$cid,'budget_kobo'=>(int)$quote['total_kobo']));
            if(empty($approved['success'])){ echo json_encode(array('status'=>'error','message'=>$approved['message']??'Campaign spend could not be approved.')); return; }
            $this->db->query('INSERT INTO db_messaging_campaign_approvals (store_id,campaign_id,approved_at) VALUES (?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE approved_at=VALUES(approved_at)',array($storeId,$cid));
        }

		if($testOnly){
			// Controlled test: send to the store owner only, touch nothing.
			$store = get_store_details($storeId);
			$to = $campaign->channel === 'email' ? ($store->email ?? '') : ($store->phone ?? $store->mobile ?? '');
			if($to === ''){ echo json_encode(['status'=>'error','message'=>'No store owner ' . $campaign->channel . ' configured']); return; }
			$fakeSend = (object)['recipient' => $to, 'customer_name' => 'Store owner (test)', 'unsub_token' => null];
			$res = $this->_dispatchCampaignMessage($campaign, $fakeSend, $storeId);
			echo json_encode(['status' => $res['ok'] ? 'success' : 'error', 'message' => $res['ok'] ? 'Test accepted for ' . $to : $res['error']]);
			return;
		}

		// Audience was frozen when the cost was estimated.
		// Recover rows stranded in 'sending' by an earlier interrupted run.
		$this->marketing_m->releaseStuckSends();
		$this->db->where('id', $cid)->update('db_campaigns', ['status' => 'sending']);

		// Batch: at most 5 provider calls per request — the cron sweep
		// finishes the rest. A rate-limit error pauses the batch, leaves
		// the send pending, and the campaign resumes on the next run.
		$rateLimited = false; $dispatchStarted=microtime(true);
		foreach($this->marketing_m->getPendingSends($cid, 5) as $send){
            if(microtime(true)-$dispatchStarted>12) break;
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
				$this->db->where('id', $send->id)->update('db_campaign_sends', ['status' => 'pending', 'error' => $res['error'] ?? 'Messaging paused']);
				$this->db->query('UPDATE db_campaign_sends SET attempts=GREATEST(0,attempts-1) WHERE id=?',array($send->id));
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
		if($rateLimited) $msg .= ' — messaging paused; queued sends resume when credit, limits and provider confirmation allow';
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
        if(!$this->_can_send_channel($campaign->channel)){echo json_encode(array('status'=>'error','message'=>'No permission to spend on this channel.'));return;}
        if($this->db->table_exists('db_messaging_campaign_approvals')) $this->db->where('store_id',$storeId)->where('campaign_id',$cid)->delete('db_messaging_campaign_approvals');
		$n = $this->marketing_m->requeueFailed($cid, $storeId);
		if($n > 0) $this->db->where('id', $cid)->where('store_id', $storeId)->update('db_campaigns', ['status' => 'partial']);
		echo json_encode(['status'=>'success','message'=>"{$n} failed send(s) requeued — confirm the remaining cost using Resume. Accepted rows are never re-contacted"]);
	}

	/**
	 * One provider call. Returns ok/error plus rate_limited when the
	 * provider reports 429-style throttling — the caller requeues rather
	 * than fails the send. Every recipient gets a working unsubscribe
	 * path: footer link for email, opt-out link appended to SMS.
	 */
    private function _dispatchCampaignMessage($campaign,$send,$storeId){
        $this->load->library('campaign_messaging');
        return $this->campaign_messaging->send($campaign,$send,empty($send->id));
    }

    private function _campaignQuote($campaign,$test){
        $this->load->library('campaign_messaging');
        if($test){
            $store=get_store_details($campaign->store_id);
            $to=$campaign->channel==='email'?($store->email??''):($store->phone??$store->mobile??'');
            if(!$to) return array('success'=>false,'message'=>'Configure the store owner email/phone before a test send.');
            $sends=array((object)array('recipient'=>$to,'customer_name'=>'Store owner (test)','unsub_token'=>null));
        }else{
            $key=$campaign->segment_key;
            if($campaign->segment_id){
                $saved=$this->marketing_m->getSegment($campaign->segment_id,$campaign->store_id);
                if(!$saved) return array('success'=>false,'message'=>'Saved audience no longer exists.');
                $key=$saved->segment_key;
            }
            if(!isset(Marketing_model::$SEGMENTS[$key])) return array('success'=>false,'message'=>'Choose a valid audience.');
            // First estimate freezes the audience. Editing a draft clears it.
            if(!$this->db->where('campaign_id',$campaign->id)->count_all_results('db_campaign_sends')){
                $audience=$this->marketing_m->resolveSegment($campaign->store_id,$key);
                $this->marketing_m->stageCampaignAudience($campaign,$audience);
            }
            $sends=$this->marketing_m->getPendingSends($campaign->id,10000);
        }
        return $this->campaign_messaging->estimate($campaign,$sends,$test);
    }

    public function estimate_campaign(){
        if(!$this->_can_send()){ echo json_encode(array('success'=>false,'message'=>'Access denied')); return; }
        $this->load->model('Marketing_model','marketing_m');
        $campaign=$this->marketing_m->getCampaign((int)$this->input->post('id'),get_current_store_id());
        if(!$campaign || !$this->_can_send_channel($campaign->channel)){ echo json_encode(array('success'=>false,'message'=>'Campaign access denied')); return; }
        $this->marketing_m->releaseStuckSends();
        echo json_encode($this->_campaignQuote($campaign,(bool)$this->input->post('test')));
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
