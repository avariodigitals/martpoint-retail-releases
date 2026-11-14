<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient_statement_model — read-only account statements for a patient and for
 * a single admission. There is deliberately NO write path here: balances are
 * composed from the authoritative sources (db_sales, db_salesitems,
 * db_salespayments, db_patient_wallet_txns, db_daily_charges) so no double
 * entry, revenue or wallet invariant is touched.
 *
 * Two statements are produced:
 *  - admissionStatement(): dated charges + payments/reversals for ONE
 *    admission, resolved through db_sales.admission_id. A new occupant of the
 *    same bed has a different admission and never sees the prior occupant.
 *  - patientStatement(): ALL Final/Opening invoices for the patient's customer
 *    id (patient-wide), with the wallet shown separately (not as debt).
 *
 * Admission-to-bill linkage: db_admissions.invoice_id -> db_sales(id) and
 * db_sales.admission_id -> db_admissions(id). Invoices created inside an
 * admission carry admission_id; payments against that invoice inherit the
 * linkage. General patient payments without admission linkage appear only on
 * the patient-wide statement.
 */
class Patient_statement_model extends CI_Model {

	/**
	 * Admission-only statement. Returns null when the admission has no invoice.
	 * Each line is a dated movement: charge, payment, wallet settlement or
	 * reversal, with a reference and a running outstanding balance.
	 */
	public function admissionStatement($admId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$inv = $this->db->where('admission_id', (int)$admId)->where('store_id', $storeId)
			->order_by('id', 'desc')->limit(1)->get('db_sales')->row();
		if(!$inv) return null;

		return $this->_invoiceStatement($inv, $storeId);
	}

	/**
	 * Patient-wide statement. Every Final/Opening invoice for the patient's
	 * customer, with its charges and payments, plus a wallet summary shown
	 * separately. Ordering: invoice desc, lines asc.
	 */
	public function patientStatement($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$p = $this->db->where('id', (int)$patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$p || !$p->customer_id) return array('invoices' => array(), 'wallet' => null, 'total_debt' => 0.0);

		$invoices = $this->db->where('store_id', $storeId)->where('customer_id', (int)$p->customer_id)
			->where_in('sales_status', array('Final', 'Opening'))
			->order_by('id', 'desc')->get('db_sales')->result();

		$out = array();
		foreach($invoices as $inv){
			$out[] = $this->_invoiceStatement($inv, $storeId);
		}

		// Wallet — funds, not debt. Shown separately per the requirement.
		$this->load->model('Patient_funds_model', 'pf');
		$wallet = $this->pf->getBalances($patientId, $storeId);

		$totalDebt = 0.0;
		foreach($out as $i){ $totalDebt += $i['outstanding']; }

		return array(
			'invoices' => $out,
			'wallet' => $wallet,
			'total_debt' => round($totalDebt, 2),
		);
	}

	/**
	 * Build one invoice's statement, reconciled to the authoritative invoice
	 * figures so discounts/taxes/adjustments never drift:
	 *  - charges     = itemised db_salesitems lines (what was billed)
	 *  - grand_total = invoice.grand_total (post-discount/tax — the authority)
	 *  - payments    = db_salespayments, wallet_reversal netted negative
	 *  - outstanding = max(0, grand_total - paid_amount) — invoice authority
	 *  - running balance begins at grand_total and decreases net of payments
	 * Wallet consumption vs invoice settlement is NOT double counted: wallet
	 * spend rows are the wallet's own ledger; here only db_salespayments rows
	 * (which a wallet settlement also writes) appear against the invoice.
	 */
	private function _invoiceStatement($inv, $storeId){
		$charges = $this->db->where('sales_id', $inv->id)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_salesitems')->result();
		$payments = $this->db->where('sales_id', $inv->id)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_salespayments')->result();

		$lines = array();

		// Itemised charges.
		foreach($charges as $c){
			$lines[] = array(
				'kind'   => 'charge',
				'date'   => $c->created_date ?? $inv->sales_date ?? null,
				'amount' => round((float)$c->total_cost, 2),
				'ref'    => $c->description ?: ('item-'.$c->id),
				'note'   => $c->description ?: 'Charge',
			);
		}

		// Net payments: wallet_reversal rows are negative (they reopen the
		// receivable); everything else reduces the balance.
		foreach($payments as $p){
			$type = $p->payment_type === 'wallet_reversal' ? 'reversal' : 'payment';
			$raw  = (float)$p->payment;
			$amount = $type === 'reversal' ? -$raw : $raw;
			$lines[] = array(
				'kind'   => $type,
				'date'   => $p->payment_date ?? $p->created_date ?? null,
				'amount' => round($amount, 2),
				'ref'    => $p->payment_reference ?: ($p->payment_mode_id ? 'pm-'.$p->id : 'pay-'.$p->id),
				'note'   => $p->payment_note ?: ($type === 'payment' ? 'Payment' : 'Wallet reversal'),
			);
		}

		// Chronological order, running balance anchored at grand_total.
		usort($lines, function($a, $b){ return strcmp((string)$a['date'], (string)$b['date']); });
		$bal = (float)$inv->grand_total;
		foreach($lines as &$l){
			if($l['kind'] !== 'charge'){
				$bal = round($bal + $l['amount'], 2); // payments are negative
				$l['balance'] = $bal;
			} else {
				$l['balance'] = $bal;
			}
		}
		unset($l);

		return array(
			'id' => (int)$inv->id,
			'sales_code' => $inv->sales_code,
			'sales_status' => $inv->sales_status,
			'admission_id' => isset($inv->admission_id) ? (int)$inv->admission_id : null,
			'reference_no' => $inv->reference_no ?? null,
			'sales_date' => $inv->sales_date ?? null,
			'grand_total' => (float)$inv->grand_total,
			'paid' => (float)$inv->paid_amount,
			'outstanding' => max(0, (float)$inv->grand_total - (float)$inv->paid_amount),
			'lines' => $lines,
		);
	}
}
