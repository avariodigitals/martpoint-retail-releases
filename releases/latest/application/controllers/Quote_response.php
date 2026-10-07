<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Public, unguessable quotation response links. No customer account required. */
class Quote_response extends CI_Controller {
	private function store_for_quote($store_id) {
		$s = $this->db->select('store_name,currency_id')->where('id',(int)$store_id)->get('db_store')->row();
		if ($s && !empty($s->currency_id)) {
			$c = $this->db->select('currency_code,currency_name')->where('id',(int)$s->currency_id)->get('db_currency')->row();
			$s->currency = $c->currency_code ?? ($c->currency_name ?? '');
		} else { if ($s) $s->currency = ''; }
		return $s;
	}
	/**
	 * Look up a quotation by its raw response token.
	 *
	 * Returns an array with:
	 *   - 'quote'   : the hydrated quotation row, or null when nothing matched
	 *   - 'state'   : 'ok' | 'expired' | 'used' | 'invalid'
	 *   - 'reason'  : human-readable explanation for non-'ok' states
	 *
	 * 'used' covers links already answered/accepted/declined/cancelled;
	 * 'expired' covers a link that lapsed on time. Both are distinct from
	 * 'invalid' (unknown/forged token), which still deserves a 404.
	 */
	private function resolve_quote($token) {
		if (!preg_match('/^[a-f0-9]{64}$/', (string)$token)) {
			return ['quote'=>null,'state'=>'invalid','reason'=>''];
		}
		$q = $this->db->where('response_token_hash', hash('sha256', $token))->get('db_quotation')->row();
		if (!$q) {
			// No live hash. The customer may be re-opening an already-answered link,
			// so match the historical token fingerprint recorded on the lifecycle log.
			$q = $this->historic_quote($token);
			if ($q) {
				return ['quote'=>null,'state'=>'used','reason'=>'This quotation link has already been used for a response. Choose an option above to view the recorded outcome.','quote_hint'=>$q];
			}
			return ['quote'=>null,'state'=>'invalid','reason'=>''];
		}
		if (!empty($q->response_token_expires_at) && strtotime($q->response_token_expires_at) < time()) {
			return ['quote'=>null,'state'=>'expired','reason'=>'This response link expired on '.date('j M Y',strtotime($q->response_token_expires_at)).'. Please contact the business for a current quotation.','quote_hint'=>$q];
		}
		return ['quote'=>$this->hydrate_quote($q),'state'=>'ok','reason'=>''];
	}

	/** Find a quotation previously answered via this token (hash already cleared). */
	private function historic_quote($token) {
		if (!$this->db->table_exists('db_quotation_lifecycle_log')) return null;
		$fp = hash('sha256', $token);
		$qid = null;
		if ($this->db->field_exists('response_token_fingerprint','db_quotation_lifecycle_log')) {
			$row = $this->db->select('quotation_id')->where('response_token_fingerprint',$fp)->order_by('id','DESC')->get('db_quotation_lifecycle_log')->row();
			if ($row) $qid = (int)$row->quotation_id;
		}
		if (!$qid) return null;
		return $this->db->where('id',$qid)->get('db_quotation')->row();
	}

	private function hydrate_quote($q) {
		$q->customer = $this->db->select('customer_name')->where('id',(int)$q->customer_id)->where('store_id',(int)$q->store_id)->get('db_customers')->row();
		$q->store = $this->store_for_quote($q->store_id);
		$q->items = $this->db->select('i.item_name,qi.description,qi.quotation_qty,qi.price_per_unit,qi.total_cost')
			->from('db_quotationitems qi')->join('db_items i','i.id=qi.item_id','left')
			->where('qi.quotation_id',(int)$q->id)->get()->result();
		return $q;
	}

	/**
	 * Render the read-only "this link is no longer actionable" page.
	 * Reuses public_response.php with the token cleared so no form renders.
	 */
	private function render_closed($state, $reason, $hint = null) {
		$this->output->set_status_header(200);
		if ($hint) { $hint = $this->hydrate_quote($hint); $hint->items = []; }
		$this->load->view('quotation/public_response', [
			'q' => $hint, 'token' => '', 'error' => null, 'notice' => null,
			'closed_state' => $state, 'closed_reason' => $reason,
		]);
	}

	/** Back-compat shim for any internal caller expecting the old signature. */
	private function find_quote($token) {
		$r = $this->resolve_quote($token);
		return $r['state'] === 'ok' ? $r['quote'] : null;
	}

	public function index($token = '') {
		$r = $this->resolve_quote($token);
		if ($r['state'] === 'invalid') { show_404(); return; }
		if ($r['state'] !== 'ok') { $this->render_closed($r['state'], $r['reason'], $r['quote_hint'] ?? null); return; }
		$this->load->view('quotation/public_response', ['q'=>$r['quote'],'token'=>$token,'error'=>null,'notice'=>null]);
	}

	public function respond($token = '') {
		$r = $this->resolve_quote($token);
		if ($r['state'] === 'invalid') { show_404(); return; }
		if ($r['state'] !== 'ok') { $this->render_closed($r['state'], $r['reason'], $r['quote_hint'] ?? null); return; }
		$q = $r['quote'];
		$action = $this->input->post('decision', TRUE);
		$note = trim((string)$this->input->post('note', TRUE));
		if (!in_array($action,['accepted','declined'],true)) { show_404(); return; }
		if (in_array($q->lifecycle_status,['accepted','declined','cancelled','expired'],true)) {
			$this->load->view('quotation/public_response',['q'=>$q,'token'=>$token,'error'=>null,'notice'=>'A response has already been recorded for this quotation.']); return;
		}
		if ($action === 'declined' && $note === '') {
			$this->load->view('quotation/public_response',['q'=>$q,'token'=>$token,'error'=>'Please add a short note explaining the decline.','notice'=>null]); return;
		}
		$note = substr($note,0,255);
		$fp = hash('sha256', $token);
		$log = function($toStatus) use ($q, $note, $fp) {
			$row = ['store_id'=>(int)$q->store_id,'quotation_id'=>(int)$q->id,'from_status'=>$q->lifecycle_status,'to_status'=>$toStatus,'reason'=>$note,'actor'=>$q->customer->customer_name ?? 'Customer','channel'=>'email'];
			if ($this->db->field_exists('response_token_fingerprint','db_quotation_lifecycle_log')) $row['response_token_fingerprint'] = $fp;
			return $row;
		};
		$job = $this->db->select('id')->where('quotation_id',(int)$q->id)->where('store_id',(int)$q->store_id)->get('db_print_jobs')->row();
		$this->db->trans_start();
		if ($job && $action === 'accepted') {
			$this->load->model('printing_model','print');
			$res = $this->print->accept_quotation_revision((int)$job->id,$note);
			if (empty($res['success'])) { $this->db->trans_rollback(); $this->load->view('quotation/public_response',['q'=>$q,'token'=>$token,'error'=>$res['message'] ?? 'Could not accept quotation.','notice'=>null]); return; }
			$this->db->where('id',(int)$q->id)->update('db_quotation',['lifecycle_status'=>'accepted','responded_at'=>date('Y-m-d H:i:s'),'response_reason'=>$note,'response_token_hash'=>null]);
			$this->db->insert('db_quotation_lifecycle_log', $log('accepted') + ['job_id'=>(int)$job->id]);
		} elseif ($job) {
			$this->load->model('printing_model','print');
			$res = $this->print->decline_quotation((int)$job->id,$note,'email');
			if (empty($res['success'])) { $this->db->trans_rollback(); $this->load->view('quotation/public_response',['q'=>$q,'token'=>$token,'error'=>$res['message'] ?? 'Could not decline quotation.','notice'=>null]); return; }
			$this->db->where('id',(int)$q->id)->update('db_quotation',['response_token_hash'=>null]);
			$this->db->insert('db_quotation_lifecycle_log', $log('declined') + ['job_id'=>(int)$job->id]);
		} else {
			$this->db->where('id',(int)$q->id)->update('db_quotation',['lifecycle_status'=>$action,'responded_at'=>date('Y-m-d H:i:s'),'response_reason'=>$note,'response_token_hash'=>null]);
			$this->db->insert('db_quotation_lifecycle_log', $log($action) + ['job_id'=>null]);
		}
		$this->db->trans_complete();
		$q = $this->db->where('id',(int)$q->id)->get('db_quotation')->row();
		$q->customer = $this->db->select('customer_name')->where('id',(int)$q->customer_id)->where('store_id',(int)$q->store_id)->get('db_customers')->row();
		$q->store = $this->store_for_quote($q->store_id);
		$q->items = [];
		$this->load->view('quotation/public_response',['q'=>$q,'token'=>'','error'=>null,'notice'=>$action==='accepted'?'Thank you. Your acceptance has been recorded.':'Your response has been recorded. The business will follow up with you.']);
	}
}
