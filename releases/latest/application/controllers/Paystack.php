<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paystack extends MY_Controller {
	public function __construct(){
		parent::__construct();
		// The webhook endpoint receives signed POSTs from Paystack — there is
		// no merchant session, so it must skip the dashboard auth gate.
		// Authenticity is enforced by verify_webhook_signature() inside.
		$publicMethods = array('webhook','callback','invoice','invoice_checkout');
		if(!in_array(strtolower($this->router->fetch_method()), $publicMethods, true)){
			$this->load_global();
		}
		$this->load->model('paystack_model','paystack');
	}

	/** Public invoice page reached from an issued invoice email. */
	public function invoice($token = '')
	{
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->load->helper(array('url','custom','currency'));
		$invoice = $this->_publicInvoice($token);
		if(!$invoice){ show_404(); return; }
		$data = $this->_invoicePageData($invoice, $token);
		$this->load->view('paystack_invoice', $data);
	}

	/** Start checkout only for the current outstanding balance on this invoice. */
	public function invoice_checkout($token = '')
	{
		$this->load->helper(array('url','custom','currency'));
		if(!$this->input->post()) { redirect('invoice/pay/'.rawurlencode($token)); return; }
		$invoice = $this->_publicInvoice($token);
		if(!$invoice){ show_404(); return; }
		$storeId = (int)$invoice->store_id;
		if(!$this->paystack->is_enabled($storeId)){
			$this->session->set_flashdata('invoice_payment_error', 'MartPoint Pay is not available for this store yet.');
			redirect('invoice/pay/'.rawurlencode($token)); return;
		}

		$this->db->trans_begin();
		try {
			$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE', array($storeId));
			$locked = $this->db->query('SELECT * FROM db_sales WHERE id=? AND store_id=? AND invoice_payment_token=? FOR UPDATE', array((int)$invoice->id, $storeId, $token))->row();
			if(!$locked || $locked->sales_status !== 'Final') throw new RuntimeException('This invoice is not available for payment.');
			$paidRow = $this->db->query('SELECT COALESCE(SUM(payment),0) AS paid FROM db_salespayments WHERE sales_id=? AND status=1 AND COALESCE(confirmation_status,1)=1', array((int)$locked->id))->row();
			$due = round((float)$locked->grand_total - (float)($paidRow->paid ?? 0), 2);
			if($due <= 0){ $this->db->trans_commit(); redirect('invoice/pay/'.rawurlencode($token)); return; }

			// Reuse an open invoice checkout so repeated clicks cannot create parallel
			// charges against the same balance.
			$pending = $this->db->where('store_id',$storeId)->where('sales_id',(int)$locked->id)->where('payment_status','pending')->get('db_paystack_payments')->result();
			foreach($pending as $pendingPayment){
				$meta = json_decode((string)($pendingPayment->meta_data ?? ''), true);
				if(is_array($meta) && hash_equals($token, (string)($meta['invoice_payment_token'] ?? ''))
					&& abs((float)$pendingPayment->amount - $due) < 0.01 && !empty($pendingPayment->paystack_authorization_url)){
					$this->db->trans_commit(); redirect($pendingPayment->paystack_authorization_url); return;
				}
				if(is_array($meta) && !empty($meta['invoice_payment_token'])){
					throw new RuntimeException('A checkout is already open for this invoice. Use the existing checkout link or contact the store before retrying.');
				}
			}
			$otherPending = $this->db->where('sales_id',(int)$locked->id)->where('payment_type','Paystack')
				->where('status',0)->where('confirmation_status',0)->count_all_results('db_salespayments');
			if($otherPending > 0) throw new RuntimeException('A payment for this invoice is awaiting confirmation. Contact the store before starting another checkout.');

			$split = $this->paystack->get_split_config($due, $storeId);
			$payout = $split['payout_account'] ?? null;
			if(empty($split['status']) || !$payout || empty($payout->provider_subaccount_code) || (int)$payout->enabled !== 1){
				throw new RuntimeException('Online invoice payments are not active for this store yet.');
			}
			if(empty($locked->customer_id) || (int)$locked->customer_id === 1){
				throw new RuntimeException('This invoice has no customer email address for online payment. Contact the store.');
			}
			$customer = $this->db->where('id',(int)$locked->customer_id)->get('db_customers')->row();
			$email = filter_var((string)($customer->email ?? ''), FILTER_VALIDATE_EMAIL);
			if(!$email) throw new RuntimeException('The customer email on this invoice is missing or invalid. Contact the store.');

			$metadata = array(
				'purpose'=>'fleet_invoice_payment', 'sales_id'=>(int)$locked->id,
				'customer_id'=>(int)$locked->customer_id, 'store_id'=>$storeId,
				'invoice_payment_token'=>$token
			);
			$result = $this->paystack->generate_payment_link($due, $email, $metadata, $storeId);
			if(empty($result['status'])) throw new RuntimeException($result['message'] ?? 'Paystack could not start checkout.');
			$paymentLinked = $this->db->where('paystack_reference',$result['reference'])->update('db_paystack_payments', array(
				'sales_id'=>(int)$locked->id, 'customer_id'=>(int)$locked->customer_id
			));
			if(!$paymentLinked) throw new RuntimeException('Paystack started checkout, but the invoice payment could not be linked. Contact the store before paying again.');

			$this->load->helper('date');
			$paymentCode = function_exists('get_init_code') ? get_init_code('sales_payment',$storeId) : ('MP-'.strtoupper(substr($result['reference'],-12)));
			$paymentRow = array(
				'store_id'=>$storeId, 'payment_code'=>$paymentCode,
				'count_id'=>function_exists('get_count_id') ? get_count_id('db_salespayments',$storeId) : 0,
				'sales_id'=>(int)$locked->id, 'payment_date'=>date('Y-m-d'),
				'payment_type'=>'Paystack', 'payment_mode_id'=>null, 'payment'=>$due,
				'payment_note'=>'Customer invoice payment via MartPoint Pay',
				'payment_reference'=>$result['reference'], 'confirmation_status'=>0,
				'created_date'=>date('Y-m-d'), 'created_time'=>date('h:i:s a'),
				'created_by'=>'MartPoint Pay', 'system_ip'=>$this->input->ip_address(),
				'system_name'=>gethostname() ?: 'MartPoint', 'status'=>0,
				'account_id'=>null, 'customer_id'=>(int)$locked->customer_id,
				'advance_adjusted'=>0, 'cheque_number'=>null, 'cheque_period'=>null,
				'cheque_status'=>'Pending'
			);
			foreach(array_keys($paymentRow) as $column){ if(!$this->db->field_exists($column,'db_salespayments')) unset($paymentRow[$column]); }
			if(!$this->db->insert('db_salespayments',$paymentRow)) throw new RuntimeException('The checkout was created but its invoice record could not be prepared. Contact the store before paying again.');
			if(!$this->db->trans_status()) throw new RuntimeException('Invoice checkout could not be saved.');
			$this->db->trans_commit();
			redirect($result['authorization_url']);
		} catch(Throwable $e){
			$this->db->trans_rollback();
			log_message('error','Invoice Paystack checkout failed: '.$e->getMessage());
			$this->session->set_flashdata('invoice_payment_error',$e->getMessage());
			redirect('invoice/pay/'.rawurlencode($token));
		}
	}

	private function _publicInvoice($token)
	{
		$token = strtolower(trim((string)$token));
		if(!preg_match('/^[a-f0-9]{64}$/D',$token) || !$this->db->field_exists('invoice_payment_token','db_sales')) return null;
		return $this->db->select('s.id,s.store_id,s.sales_code,s.sales_date,s.sales_status,s.grand_total,s.customer_id,s.invoice_status,c.customer_name,c.email')
			->from('db_sales s')->join('db_customers c','c.id=s.customer_id','left')
			->where('s.invoice_payment_token',$token)->where('s.status',1)->get()->row();
	}

	private function _invoicePageData($invoice, $token)
	{
		$paid = $this->db->query('SELECT COALESCE(SUM(payment),0) AS paid FROM db_salespayments WHERE sales_id=? AND status=1 AND COALESCE(confirmation_status,1)=1',array((int)$invoice->id))->row();
		$items = $this->db->select("COALESCE(NULLIF(TRIM(i.item_name),''),NULLIF(TRIM(si.description),''),'Item') AS item_name, si.sales_qty, si.unit_total_cost, si.total_cost")
			->from('db_salesitems si')->join('db_items i','i.id=si.item_id','left')->where('si.sales_id',(int)$invoice->id)->get()->result();
		$store = function_exists('get_store_details') ? get_store_details((int)$invoice->store_id) : null;
		return array(
			'invoice'=>$invoice, 'store_name'=>(string)($store->store_name ?? 'MartPoint Store'),
			'items'=>$items,
			'paid'=>(float)($paid->paid ?? 0), 'due'=>max(0,round((float)$invoice->grand_total-(float)($paid->paid ?? 0),2)),
			'paystack_active'=>$this->paystack->is_enabled((int)$invoice->store_id),
			'payment_result'=>(string)$this->input->get('payment', TRUE),
			'error'=>$this->session->flashdata('invoice_payment_error'),
			'csrf_name'=>$this->security->get_csrf_token_name(), 'csrf_hash'=>$this->security->get_csrf_hash(),
			'checkout_url'=>site_url('invoice/pay/'.rawurlencode($token).'/checkout')
		);
	}

	// Settings page
	public function settings(){
		$this->permission_check('paystack_settings');
		// Users may keep Central open during a release. Run registered migrations
		// here too, so this screen never depends on manually applying SQL.
		if(function_exists('mp_is_central') && mp_is_central()){
			$this->update_db();
		}
		$data=$this->data;
		$data['page_title']='MartPoint Pay';
		$data['settings']=$this->paystack->get_settings();
		$data['payout_account']=$this->paystack->refresh_payout_account_status((int)get_current_store_id());
		$data['central_managed'] = !function_exists('mp_is_central') || !mp_is_central();
		$data['payout_edit_policy_available'] = true;
		$data['allow_payout_account_edit'] = true;
		if($data['central_managed']){
			$this->load->library('messaging_client');
			$policy = $this->messaging_client->payoutAccountEditPolicy((int)get_current_store_id());
			$data['payout_edit_policy_available'] = !empty($policy['success']) && array_key_exists('allow_payout_account_edit', $policy);
			$data['allow_payout_account_edit'] = $data['payout_edit_policy_available'] && !empty($policy['allow_payout_account_edit']);
		}
		if(!$data['central_managed']){
			$billing_base = rtrim(trim((string)getenv('CENTRAL_BILLING_API_URL')), '/');
			$billing_host = $billing_base !== '' ? parse_url($billing_base, PHP_URL_HOST) : false;
			$billing_scheme = $billing_base !== '' ? parse_url($billing_base, PHP_URL_SCHEME) : false;
			if(!$billing_host || $billing_scheme !== 'https'){
				$billing_base = 'https://martpoint.com.ng';
			}
			$data['paystack_shared_webhook_url'] = $billing_base.'/api/payments/webhooks/paystack';
		}
		$data['paystack_terms_accepted'] = $this->paystack->has_current_paystack_terms_acceptance((int)get_current_store_id());
		$data['content'] = $this->load->view('paystack_settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// Save settings
	public function save_settings(){
		$this->permission_check('paystack_settings');
		if(!function_exists('mp_is_central') || !mp_is_central()){
			echo 'central_managed';
			return;
		}
		// Catch up the Central schema before saving provider configuration.
		$this->update_db();
		$secret_key = $this->input->post('secret_key', TRUE);
		$public_key = $this->input->post('public_key', TRUE);
		$enabled = $this->input->post('enabled', TRUE);
		$test_mode = $this->input->post('test_mode', TRUE);
		$webhook_secret = $this->input->post('webhook_secret', TRUE);
		$test_mode = (string)($test_mode ?? '1') === '1' ? 1 : 0;
		$expected_secret_prefix = $test_mode ? 'sk_test_' : 'sk_live_';
		$expected_public_prefix = $test_mode ? 'pk_test_' : 'pk_live_';
		if(!is_string($secret_key) || strpos(trim($secret_key), $expected_secret_prefix) !== 0
			|| !is_string($public_key) || strpos(trim($public_key), $expected_public_prefix) !== 0){
			http_response_code(422);
			echo 'The selected mode does not match the Paystack key pair. Test mode needs sk_test_ and pk_test_ keys; live mode needs sk_live_ and pk_live_ keys.';
			return;
		}
		$secret_key = trim($secret_key);
		$public_key = trim($public_key);
		$platform_fee_percent = min(100, max(0, (float)$this->input->post('platform_fee_percent')));
		$platform_fee_fixed = max(0, (float)$this->input->post('platform_fee_fixed'));
		$fee_bearer = $this->input->post('fee_bearer', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];

		$data = array(
			'secret_key' => $secret_key,
			'public_key' => $public_key,
			'enabled' => $enabled ?? 0,
			'test_mode' => $test_mode,
			'webhook_secret' => $webhook_secret ?? '',
			// Keep the legacy fields in sync for older integrations while the
			// transaction split uses the combined percentage + fixed fee below.
			'platform_fee_type' => $platform_fee_percent > 0 ? 'percentage' : ($platform_fee_fixed > 0 ? 'flat' : 'none'),
			'platform_fee_value' => $platform_fee_percent > 0 ? $platform_fee_percent : $platform_fee_fixed,
			'platform_fee_percent' => $platform_fee_percent,
			'platform_fee_fixed' => $platform_fee_fixed,
			'fee_bearer' => $fee_bearer === 'merchant' ? 'merchant' : 'platform',
			'callback_url' => base_url('paystack/callback'),
			'created_date' => $CUR_DATE,
			'created_time' => $CUR_TIME,
			'created_by' => $CUR_USERNAME
		);

		if($this->paystack->save_settings($data)){
			$saved = $this->paystack->get_settings();
			if($saved && hash_equals((string)($saved->secret_key ?? ''), $secret_key)
				&& hash_equals((string)($saved->public_key ?? ''), $public_key)
				&& (int)($saved->test_mode ?? -1) === $test_mode){
				$table = $this->db->table_exists('db_payment_gateway_accounts')
					? 'db_payment_gateway_accounts' : 'db_paystack_settings';
				$fee_schema_ready = $this->db->field_exists('platform_fee_percent', $table)
					&& $this->db->field_exists('platform_fee_fixed', $table);
				if(!$fee_schema_ready && ($platform_fee_percent > 0 || $platform_fee_fixed > 0)){
					echo 'credentials_saved_fee_schema_missing';
					return;
				}
				echo "success";
				return;
			}
			http_response_code(500);
			echo 'The database write returned success, but the saved Paystack settings could not be confirmed. Check the Central database migration and provider row.';
		} else {
			$db_error = $this->db->error();
			http_response_code(500);
			$message = trim((string)($db_error['message'] ?? ''));
			echo $message !== ''
				? 'Paystack settings were not saved: '.$message
				: 'Paystack settings were not saved. Check that the Central Paystack migration has been applied.';
		}
	}

	// Generate payment link (AJAX)
	public function generate_link(){
		if(!$this->paystack->is_enabled()){
			echo json_encode(array('status'=>false, 'message'=>'MartPoint Pay is not enabled'));
			return;
		}

		$amount = floatval($this->input->post('amount'));
		$email = $this->input->post('email');
		$sales_id = intval($this->input->post('sales_id'));
		$customer_id = intval($this->input->post('customer_id'));
		$phone = $this->input->post('phone');
		$store_id = (int)get_current_store_id();

		if($amount <= 0){
			echo json_encode(array('status'=>false, 'message'=>'Invalid amount'));
			return;
		}
		if(empty($email)){
			echo json_encode(array('status'=>false, 'message'=>'Customer email is required'));
			return;
		}
		if($sales_id <= 0 || $store_id <= 0){
			echo json_encode(array('status'=>false, 'message'=>'Save the sale first so the payment can be applied automatically.'));
			return;
		}
		$sale = $this->db->where('id',$sales_id)->where('store_id',$store_id)->where('sales_status','Final')->get('db_sales')->row();
		if(!$sale){
			echo json_encode(array('status'=>false, 'message'=>'The sale could not be found for this store.'));
			return;
		}
		$paid = $this->db->query('SELECT COALESCE(SUM(payment),0) AS amount FROM db_salespayments WHERE sales_id=? AND status=1 AND COALESCE(confirmation_status,1)=1', array($sales_id))->row();
		$due = round((float)$sale->grand_total - (float)($paid->amount ?? 0), 2);
		if($due <= 0 || round($amount,2) > $due){
			echo json_encode(array('status'=>false, 'message'=>'The link amount exceeds this sale’s outstanding balance.'));
			return;
		}
		$customer_id = (int)$sale->customer_id;
		$openLinks = $this->db->where('store_id',$store_id)->where('sales_id',$sales_id)
			->where('payment_status','pending')->get('db_paystack_payments')->result();
		foreach($openLinks as $openLink){
			$openMeta = json_decode((string)($openLink->meta_data ?? ''), true);
			if(!is_array($openMeta) || ($openMeta['purpose'] ?? '') !== 'pos_sale_payment') continue;
			if(abs((float)$openLink->amount - $amount) < 0.01 && !empty($openLink->paystack_authorization_url)){
				echo json_encode(array('status'=>true, 'reference'=>$openLink->paystack_reference,
					'authorization_url'=>$openLink->paystack_authorization_url,
					'access_code'=>$openLink->paystack_access_code ?? '', 'platform_fee'=>$openLink->platform_fee ?? 0));
				return;
			}
			echo json_encode(array('status'=>false, 'message'=>'A MartPoint Pay link is already awaiting payment for this sale. Use that link before creating another.'));
			return;
		}

		$metadata = array(
			'sales_id' => $sales_id,
			'customer_id' => $customer_id,
			'store_id' => $store_id,
			'purpose' => 'pos_sale_payment',
			'phone' => $phone
		);

		$result = $this->paystack->generate_payment_link($amount, $email, $metadata, $store_id);
		if($result['status']){
			$this->db->trans_begin();
			$linked = $this->db->where('paystack_reference', $result['reference'])->where('store_id',$store_id)->update('db_paystack_payments', array(
				'sales_id' => $sales_id, 'customer_id' => $customer_id, 'customer_phone' => $phone
			));
			if($linked){
				// Reuse the pending Paystack row saved with the POS sale when possible.
				$pending = $this->db->where('store_id',$store_id)->where('sales_id',$sales_id)
					->where('payment_type','Paystack')->where('confirmation_status',0)
					->where('payment_reference','')->where('status',0)->where('payment',round($amount,2))
					->order_by('id','ASC')->get('db_salespayments')->row();
				if($pending){
					$linked = $this->db->where('id',(int)$pending->id)->update('db_salespayments', array('payment_reference'=>$result['reference']));
				} else {
					$entry = array(
						'store_id'=>$store_id, 'sales_id'=>$sales_id, 'customer_id'=>$customer_id,
						'payment_code'=>function_exists('get_init_code') ? get_init_code('sales_payment',$store_id) : ('MP-'.$result['reference']),
						'count_id'=>function_exists('get_count_id') ? get_count_id('db_salespayments',$store_id) : 0,
						'payment_date'=>date('Y-m-d'), 'payment_type'=>'Paystack', 'payment_mode_id'=>null,
						'payment'=>round($amount,2), 'payment_note'=>'Customer payment via MartPoint Pay',
						'payment_reference'=>$result['reference'], 'confirmation_status'=>0,
						'created_date'=>date('Y-m-d'), 'created_time'=>date('h:i:s a'),
						'created_by'=>'MartPoint Pay', 'system_ip'=>$this->input->ip_address(),
						'system_name'=>gethostname() ?: 'MartPoint', 'status'=>0,
						'account_id'=>null, 'advance_adjusted'=>0
					);
					foreach(array_keys($entry) as $column){ if(!$this->db->field_exists($column,'db_salespayments')) unset($entry[$column]); }
					$linked = $this->db->insert('db_salespayments',$entry);
				}
			}
			if(!$linked || !$this->db->trans_status()){
				$this->db->trans_rollback();
				log_message('error','Paystack payment link '.$result['reference'].' could not be linked to sale '.$sales_id.'.');
				echo json_encode(array('status'=>false, 'message'=>'Paystack created the link but MartPoint could not attach it to the sale. Do not share this link; contact support.'));
				return;
			}
			$this->db->trans_commit();
		}
		echo json_encode($result);
	}

	// Customer-facing callback (browser redirect after payment)
	public function callback(){
		$reference = $this->input->get('reference');
		$trxref = $this->input->get('trxref');
		$ref = $reference ?: $trxref;

		if(empty($ref)){
			show_error('No transaction reference found');
			return;
		}

		// Verify the transaction
		$payment = $this->paystack->get_payment_by_reference($ref);
		$store_id = $payment ? $payment->store_id : null;
		$verify = $this->paystack->verify_transaction($ref, $store_id);
		if($verify['status'] && $verify['payment_status'] == 'success'
			&& $this->paystack->payment_matches($ref, $verify['amount'], $verify['currency'])){
			// Update paystack record
			$this->paystack->update_payment_status($ref, 'success', $verify);
			// Confirm the sales payment
			$ledgerConfirmed = $this->paystack->confirm_sales_payment($ref);
			$meta = json_decode((string)($payment->meta_data ?? ''), true);
			if(is_array($meta) && !empty($meta['invoice_payment_token'])
				&& preg_match('/^[a-f0-9]{64}$/D', (string)$meta['invoice_payment_token'])){
				redirect('invoice/pay/'.rawurlencode($meta['invoice_payment_token']).'?payment='.($ledgerConfirmed ? 'success' : 'review'));
				return;
			}

			$data['message'] = $ledgerConfirmed
				? 'Payment successful and applied to the sale automatically.'
				: 'Payment successful at Paystack, but MartPoint could not match it to the sale balance. Please contact support; do not request another payment.';
			$data['reference'] = $ref;
			$this->load->view('paystack_callback', $data);
		} else {
			$meta = is_object($payment) ? json_decode((string)($payment->meta_data ?? ''), true) : null;
			if(is_array($meta) && !empty($meta['invoice_payment_token'])
				&& preg_match('/^[a-f0-9]{64}$/D', (string)$meta['invoice_payment_token'])){
				$providerStatus = strtolower((string)($verify['payment_status'] ?? ''));
				if(!empty($verify['status']) && in_array($providerStatus,array('failed','abandoned'),true)){
					$this->paystack->update_payment_status($ref,$providerStatus,$verify);
					$this->paystack->release_failed_invoice_payment($ref);
				}
				redirect('invoice/pay/'.rawurlencode($meta['invoice_payment_token']).'?payment='.($providerStatus === 'success' ? 'review' : 'failed'));
				return;
			}
			$data['message'] = 'Payment was not successful. Please try again or contact support.';
			$data['reference'] = $ref;
			$this->load->view('paystack_callback', $data);
		}
	}

	// Webhook endpoint for async payment confirmation
	public function webhook(){
		// Get the raw input
		$input = @file_get_contents('php://input');
		if(empty($input)){
			http_response_code(400);
			echo 'No data received';
			return;
		}

		$event = json_decode($input, true);
		if(!$event || !isset($event['event']) || empty($event['data']['reference'])){
			http_response_code(400);
			echo 'Invalid payload';
			return;
		}
		$reference = $event['data']['reference'];
        if(mp_is_central() && $this->db->table_exists('db_messaging_purchases') && is_array($event)){
            $this->load->library('messaging_payments');
            $handled=$this->messaging_payments->webhook('paystack',$input,$event);
            if($handled!==null){
                $this->output->set_status_header($handled['http']??200)->set_content_type('application/json')->set_output(json_encode($handled));
                return;
            }
        }


		// Resolve the owning store from the payment target — webhooks carry no
		// session, so get_current_store_id() would pick the wrong keys.
		$this->load->model('storefront_model');
		$order = $this->storefront_model->getOrderByPaymentReference($reference);

		if($order){
			// Online-store order: verify signature with THAT store's keys.
			$settings = $this->paystack->get_settings($order->store_id);
		} else {
			// POS / sales payment or unmatched reference. Webhooks carry no
			// session so get_current_store_id() is empty — resolve the store
			// from the local payment link first, else from the signature.
			$known = $this->paystack->get_payment_by_reference($reference);
			$settings = ($known && !empty($known->store_id))
				? $this->paystack->get_settings($known->store_id)
				: $this->paystack->resolve_webhook_settings($input);
		}
		if(!$settings || empty($settings->secret_key)){
			http_response_code(500);
			echo 'MartPoint Pay is not configured';
			return;
		}

		// Verify webhook signature (optional but recommended)
		if($settings->test_mode == 0){
			$valid = !empty($settings->webhook_secret)
				&& $this->paystack->verify_webhook_signature($input, $settings->webhook_secret);
			if(!$valid){
				http_response_code(401);
				echo 'Invalid signature';
				return;
			}
		}

		// Handle charge.success event
		if($event['event'] == 'charge.success'){
			$data = $event['data'];
			$amount = $data['amount'] / 100;
			$channel = $data['channel'];
			$paid_at = $data['paid_at'];
			$status = $data['status']; // success

			if($order){
				// Server-side verify then atomically claim the paid transition;
				// a replayed/racing webhook cannot fulfil twice.
				$verify = $this->paystack->verify_transaction($reference, $order->store_id);
				$moneyIsReal = !empty($verify['status'])
					&& (string)($verify['payment_status'] ?? '') === 'success'
					&& (string)($verify['reference'] ?? '') === (string)$order->order_code;
				$exact = $moneyIsReal && abs((float)$verify['amount'] - (float)$order->grand_total) < 0.01;
				// Whatever the amount, money the provider confirms for this order is
				// recorded against it. Before this, an in-full charge that arrived
				// here while the browser callback had not run was the only way an
				// order got paid, and anything short of the invoice was dropped.
				$this->load->model('online_payments_model', 'onlinepay');
				if(!$moneyIsReal){
					// The provider confirmed a charge that does not carry this
					// order's invoice number. It is not this order's money, so it
					// must not sit in the order's ledger waiting for somebody to
					// confirm another payment and settle the whole lot.
					$this->load->model('payment_reconcile_model','recon');
					$this->recon->queue((int)$order->store_id, array(
						'exception_type'  => 'unmatched',
						'provider'        => 'paystack',
						'reference'       => (string)$reference,
						'order_id'        => (int)$order->id,
						'amount'          => $verify['amount'] ?? $amount,
						'expected_amount' => (float)$order->grand_total,
						'currency'        => $verify['currency'] ?? ($data['currency'] ?? null),
						'detail'          => 'A charge came in against reference ' . $reference
							. ', which is not the invoice number of order ' . $order->order_code
							. ' (' . $order->order_status . '/' . $order->payment_status . '). Nothing was recorded on the order.',
						'detected_by'     => 'webhook',
						'payload'         => $data,
					));
				} else {
					$ledger = $this->onlinepay->recordGatewayMoney($order, [
						'provider'       => 'paystack',
						'reference'      => $reference,
						'amount'         => $verify['amount'] ?? 0,
						'currency'       => $verify['currency'] ?? null,
						'payment_status' => $verify['payment_status'] ?? '',
					], 'the payment provider');
					if(!$exact && ($ledger['recorded'] || $ledger['duplicate'])){
						$settle = $this->onlinepay->settleOrder((int)$order->store_id, (int)$order->id, null, 'card payment');
						if(($settle['state'] ?? '') === 'paid'){
							$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_webhook');
							$this->storefront_model->fulfilPaidOrder($order->id);
						}
					} elseif($exact){
						$claimed = $this->storefront_model->claimPaidOrder($order->id, [
							'paystack_reference' => $reference,
							'paystack_amount' => $verify['amount'],
							'order_status' => 'paid'
						]);
						if($claimed){
							$this->storefront_model->adjustStock($order->id);
							$salesId = (int)$this->storefront_model->ensureSalesLedger($order->id);
							$this->onlinepay->postVerifiedLines((int)$order->store_id, (int)$order->id, $salesId);
							$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_webhook');
						} else {
							// Payment verified but the order is no longer claimable
							// (cancelled / reservation released / already paid).
							// A replay of a charge this order has already been given
							// credit for is not news — the browser callback won the
							// claim a moment ago. Anything else is real money that
							// arrived where it cannot be applied, so surface it.
							if(empty($ledger['duplicate'])){
								$this->load->model('payment_reconcile_model','recon');
								$this->recon->queue_late_payment($order->store_id, array(
									'provider'        => 'paystack',
									'reference'       => $reference,
									'order_id'        => $order->id,
									'amount'          => $verify['amount'],
									'expected_amount' => $order->grand_total,
									'currency'        => $verify['currency'] ?? null,
									'detail'          => 'Verified payment received for order ' . $order->order_code
										. ' which is already ' . $order->order_status . '/' . $order->payment_status
										. ' (stock ' . ($order->stock_state ?? 'n/a') . '). Refund the customer or reinstate the order.',
									'detected_by'     => 'webhook',
									'payload'         => $data,
								));
							}
						}
						// Idempotent, self-healing: runs for the claim winner and
						// retries on later webhooks if fulfilment died mid-flight.
						$this->storefront_model->fulfilPaidOrder($order->id);
					}
				}
			} else {
				// Update paystack payment record
				$known = $this->paystack->get_payment_by_reference($reference);
				$this->paystack->update_payment_status($reference, $status, array(
					'channel' => $channel,
					'paid_at' => $paid_at
				));

				// Confirm only an exact local amount/currency match. A webhook is
				// not allowed to turn an under/over-payment into a paid sale.
				$ledgerConfirmed = false;
				if($known && $this->paystack->payment_matches($reference, $amount, $data['currency'] ?? 'NGN')){
					$ledgerConfirmed = $this->paystack->confirm_sales_payment($reference);
				} elseif($known && $status === 'failed'){
					$this->paystack->release_failed_invoice_payment($reference);
				}

				// Reconciliation: a verified charge matching no local payment
				// link or order is an unmatched exception; a settled amount
				// that disagrees with the initiated amount is a discrepancy.
				if($status === 'success'){
					$this->load->model('payment_reconcile_model','recon');
					if(!$known){
						$this->recon->queue(isset($settings->store_id) ? (int)$settings->store_id : get_current_store_id(), array(
							'exception_type' => 'unmatched',
							'provider'       => 'paystack',
							'reference'      => $reference,
							'amount'         => $amount,
							'currency'       => $data['currency'] ?? null,
							'detail'         => 'Charge succeeded at provider but matches no local order or payment link',
							'detected_by'    => 'webhook',
							'payload'        => $data,
						));
					} else {
						$currency_mismatch = !empty($data['currency']) && !empty($known->currency)
							&& strtoupper($data['currency']) !== strtoupper($known->currency);
						if(abs((float)$amount - (float)$known->amount) > 0.01 || $currency_mismatch){
							$this->recon->queue((int)$known->store_id, array(
								'exception_type'  => 'discrepancy',
								'provider'        => 'paystack',
								'reference'       => $reference,
								'sales_id'        => $known->sales_id,
								'amount'          => $amount,
								'expected_amount' => $known->amount,
								'currency'        => $data['currency'] ?? $known->currency,
								'detail'          => $currency_mismatch
									? 'Settled currency differs from initiated currency'
									: 'Settled amount differs from initiated amount',
								'detected_by'     => 'webhook',
							));
						} elseif(!$ledgerConfirmed) {
							$this->recon->queue((int)$known->store_id, array(
								'exception_type'=>!empty($known->sales_id) ? 'discrepancy' : 'unmatched', 'provider'=>'paystack',
								'reference'=>$reference, 'sales_id'=>!empty($known->sales_id) ? (int)$known->sales_id : null,
								'amount'=>$amount, 'expected_amount'=>$known->amount,
								'currency'=>$data['currency'] ?? $known->currency,
								'detail'=>'Paystack confirmed the charge but MartPoint could not apply it to the linked sale automatically.',
								'detected_by'=>'webhook', 'payload'=>$data
							));
						}
					}
				}
			}
		}

		// Chargeback/dispute events → dispute exception on the matching record.
		if(strpos($event['event'], 'charge.dispute') === 0){
			$this->load->model('payment_reconcile_model','recon');
			$known = $this->paystack->get_payment_by_reference($reference);
			$exc_store = $known ? (int)$known->store_id : ($order ? (int)$order->store_id : get_current_store_id());
			$this->recon->queue($exc_store, array(
				'exception_type' => 'dispute',
				'provider'       => 'paystack',
				'reference'      => $reference,
				'sales_id'       => $known->sales_id ?? null,
				'order_id'       => $order->id ?? null,
				'amount'         => isset($event['data']['amount']) ? $event['data']['amount'] / 100 : null,
				'detail'         => 'Provider dispute event: '.$event['event'],
				'detected_by'    => 'webhook',
				'payload'        => $event['data'],
			));
		}

		http_response_code(200);
		echo 'OK';
	}

	// Check if Paystack is enabled for this store (API)
	public function is_enabled(){
		echo json_encode(array('enabled' => $this->paystack->is_enabled()));
	}

	// Get public key for frontend JS
	public function get_public_key(){
		$settings = $this->paystack->get_settings();
		echo json_encode(array(
			'public_key' => $settings ? $settings->public_key : '',
			'test_mode' => $settings ? $settings->test_mode : 1
		));
	}

	public function banks(){
		$this->permission_check_with_msg('paystack_settings');
		echo json_encode($this->paystack->get_banks());
	}

	public function resolve_payout_account(){
		$this->permission_check_with_msg('paystack_settings');
		if(!$this->fleet_payout_account_edit_allowed()){
			echo json_encode(['status'=>false, 'message'=>'Central has disabled payout account viewing and editing for this fleet.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		$accountNumber = preg_replace('/\D+/', '', (string)$this->input->post('account_number', TRUE));
		$bankCode = trim((string)$this->input->post('bank_code', TRUE));
		if(strlen($accountNumber) !== 10 || $bankCode === ''){
			echo json_encode(['status'=>false, 'message'=>'Select a bank and enter a 10-digit account number.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		$result = $this->paystack->resolve_payout_account($accountNumber, $bankCode, (int)get_current_store_id());
		if(!$result['status']){
			echo json_encode(['status'=>false, 'message'=>$result['message'] ?? 'The account could not be verified. Check the bank and account number.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		echo json_encode(['status'=>true,
			'account_number'=>(string)($result['data']['account_number'] ?? $accountNumber),
			'account_name'=>(string)($result['data']['account_name'] ?? ''),
			'csrf_hash'=>$this->security->get_csrf_hash()]);
	}

	public function save_payout_account(){
		$this->permission_check_with_msg('paystack_settings');
		if(!$this->fleet_payout_account_edit_allowed()){
			echo json_encode(['status'=>false, 'message'=>'Central has disabled payout account editing for this fleet.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		$store_id = (int)get_current_store_id();
		$payoutEmail = trim((string)$this->input->post('payout_email', TRUE));
		if(!filter_var($payoutEmail, FILTER_VALIDATE_EMAIL)){
			$store = $this->db->select('email')->where('id', $store_id)->get('db_store')->row();
			$payoutEmail = trim((string)($store->email ?? ''));
		}
		if(!filter_var($payoutEmail, FILTER_VALIDATE_EMAIL)){
			$user = $this->db->select('email')->where('id', (int)($this->data['CUR_USERID'] ?? 0))->get('db_users')->row();
			$payoutEmail = trim((string)($user->email ?? ''));
		}
		$result = $this->paystack->save_payout_account($store_id, array(
			'account_number' => $this->input->post('payout_account_number', TRUE),
			'bank_code' => $this->input->post('payout_bank_code', TRUE),
			'bank_name' => $this->input->post('payout_bank_name', TRUE),
			'email' => $payoutEmail,
			'phone' => $this->input->post('payout_phone', TRUE),
			'terms_accepted' => (int)$this->input->post('terms_accepted', TRUE) === 1,
			'accepted_by' => (string)($this->data['CUR_USERNAME'] ?? ''),
			'accepted_by_user_id' => (int)($this->data['CUR_USERID'] ?? 0),
			'enabled' => function_exists('mp_is_central') && mp_is_central()
				? (int)$this->input->post('payout_enabled', TRUE) : 1,
		));
		$result['csrf_hash'] = $this->security->get_csrf_hash();
		echo json_encode($result);
	}

	private function fleet_payout_account_edit_allowed(){
		if(function_exists('mp_is_central') && mp_is_central()) return true;
		$this->load->library('messaging_client');
		$policy = $this->messaging_client->payoutAccountEditPolicy((int)get_current_store_id());
		return !empty($policy['success']) && !empty($policy['allow_payout_account_edit']);
	}
}
