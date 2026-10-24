<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dormant client-side primitives for MartPoint AI Growth.
 *
 * This model is intentionally narrower than the future Growth module. It
 * keeps merchant research/campaign records tenant-scoped and provides the
 * wallet's idempotent append-only operations. It does not call providers,
 * verify payments, or enable itself when a migration is present.
 */
class Ai_growth_model extends CI_Model {

	private $cfg = array();

	public function __construct(){
		parent::__construct();
		$this->load->database();
		$this->config->load('ai_growth', true);
		$this->cfg = (array)$this->config->item('ai_growth', 'ai_growth');
	}

	public function isPlatformEnabled(){
		return !empty($this->cfg['enabled']);
	}

	/**
	 * Fail closed when a pre-Growth install has not received the migration.
	 */
	public function getSettings($storeId){
		if(!$this->_ready() || !$this->db->table_exists('db_ai_growth_settings')) return null;
		return $this->db->where('store_id', (int)$storeId)->get('db_ai_growth_settings')->row();
	}

	/**
	 * An operation is allowed only when both the platform and the merchant
	 * entitlement are active. An expired subscription is inactive immediately.
	 */
	public function isEnabled($storeId){
		if(!$this->isPlatformEnabled()) return false;
		$settings = $this->getSettings($storeId);
		if(!$settings || (int)$settings->enabled !== 1) return false;
		if(!in_array((string)$settings->entitlement_status, array('trial','active'), true)) return false;
		if(!empty($settings->subscription_ends_at) && strtotime($settings->subscription_ends_at) < time()) return false;
		return true;
	}

	/**
	 * Wallet availability is derived from the ledger; no mutable balance column
	 * is trusted. Reserved funds are exposed separately from available funds.
	 */
	public function walletSnapshot($storeId){
		$empty = array(
			'available_minor' => 0,
			'credited_minor' => 0,
			'reserved_minor' => 0,
			'captured_minor' => 0,
			'currency' => $this->cfg['ledger_currency'] ?? 'NGN',
		);
		if(!$this->_ready() || !$this->db->table_exists('db_ai_growth_wallet_ledger') || !$this->db->table_exists('db_ai_growth_wallet_reservations')) return $empty;

		$ledger = $this->db->select('COALESCE(SUM(amount_minor),0) AS available_minor', false)
			->select("COALESCE(SUM(CASE WHEN entry_type IN ('credit','refund','reversal','adjustment') AND amount_minor > 0 THEN amount_minor ELSE 0 END),0) AS credited_minor", false)
			->where('store_id', (int)$storeId)->get('db_ai_growth_wallet_ledger')->row();
		$reserved = $this->db->select('COALESCE(SUM(estimated_charge_minor - actual_charge_minor - released_minor),0) AS reserved_minor', false)
			->where('store_id', (int)$storeId)->where_in('status', array('reserved', 'uncertain'))
			->get('db_ai_growth_wallet_reservations')->row();
		$captured = $this->db->select('COALESCE(SUM(actual_charge_minor),0) AS captured_minor', false)
			->where('store_id', (int)$storeId)->where_in('status', array('settled','released'))
			->get('db_ai_growth_wallet_reservations')->row();

		$empty['available_minor'] = max(0, (int)($ledger->available_minor ?? 0));
		$empty['credited_minor'] = max(0, (int)($ledger->credited_minor ?? 0));
		$empty['reserved_minor'] = max(0, (int)($reserved->reserved_minor ?? 0));
		$empty['captured_minor'] = max(0, (int)($captured->captured_minor ?? 0));
		return $empty;
	}

	/**
	 * Credit after a separately verified payment. Funding remains disabled by
	 * default and this method refuses to run until both gates are enabled.
	 */
	public function creditWallet($storeId, $amountMinor, $idempotencyKey, $referenceType = 'payment', $referenceId = null, array $metadata = array()){
		if(!$this->_walletFundingAllowed($storeId)) return array('ok' => false, 'error' => 'Growth wallet funding is not enabled');
		$amountMinor = (int)$amountMinor;
		if($amountMinor <= 0 || trim((string)$idempotencyKey) === '') return array('ok' => false, 'error' => 'Invalid wallet credit');

		$this->db->trans_start();
		$existing = $this->_ledgerByKey($storeId, $idempotencyKey, true);
		if($existing){
			$this->db->trans_complete();
			return array('ok' => true, 'ledger_id' => (int)$existing->id, 'idempotent' => true);
		}
		$ok = $this->_insertLedger($storeId, 'credit', $amountMinor, $idempotencyKey, $referenceType, $referenceId, null, 0, $amountMinor, $metadata);
		$this->db->trans_complete();
		return array('ok' => (bool)$ok, 'ledger_id' => $ok ? (int)$this->db->insert_id() : null);
	}

	/**
	 * Reserve before an external provider call. The row lock and unique key
	 * make retries safe and prevent a negative available balance.
	 */
	public function reserveWallet($storeId, $amountMinor, $reservationKey, $referenceType, $referenceId = null, array $metadata = array()){
		if(!$this->_walletOperationsAllowed($storeId)) return array('ok' => false, 'error' => 'Growth wallet operations are not enabled');
		$amountMinor = (int)$amountMinor;
		$reservationKey = trim((string)$reservationKey);
		if($amountMinor <= 0 || $reservationKey === '') return array('ok' => false, 'error' => 'Invalid wallet reservation');

		$this->db->trans_start();
		$existing = $this->db->where('store_id', (int)$storeId)->where('reservation_key', $reservationKey)
			->get('db_ai_growth_wallet_reservations')->row();
		if($existing){
			$this->db->trans_complete();
			return array('ok' => true, 'reservation_id' => (int)$existing->id, 'status' => $existing->status, 'idempotent' => true);
		}

		// Lock the store's Growth settings row for the balance check. If no row
		// exists, the entitlement is not provisioned and the operation fails.
		$settings = $this->db->query('SELECT store_id FROM db_ai_growth_settings WHERE store_id = ? FOR UPDATE', array((int)$storeId))->row();
		$balance = $this->db->select('COALESCE(SUM(amount_minor),0) AS available_minor', false)
			->where('store_id', (int)$storeId)->get('db_ai_growth_wallet_ledger')->row();
		if(!$settings || (int)($balance->available_minor ?? 0) < $amountMinor){
			$this->db->trans_complete();
			return array('ok' => false, 'error' => 'Insufficient Growth wallet balance');
		}

		$now = date('Y-m-d H:i:s');
		$this->db->insert('db_ai_growth_wallet_reservations', array(
			'store_id' => (int)$storeId,
			'reservation_key' => $reservationKey,
			'reference_type' => substr((string)$referenceType, 0, 50),
			'reference_id' => $referenceId === null ? null : substr((string)$referenceId, 0, 80),
			'estimated_charge_minor' => $amountMinor,
			'currency' => $this->cfg['ledger_currency'] ?? 'NGN',
			'status' => 'reserved',
			'metadata_json' => $metadata ? json_encode($metadata) : null,
			'created_at' => $now,
			'updated_at' => $now,
		));
		$reservationId = (int)$this->db->insert_id();
		$ledgerKey = 'reserve:' . $reservationKey;
		$ok = $reservationId > 0 && $this->_insertLedger($storeId, 'reserve', -$amountMinor, $ledgerKey, $referenceType, $referenceId, $reservationId, 0, $amountMinor, $metadata);
		$this->db->trans_complete();
		return array('ok' => (bool)$ok, 'reservation_id' => $reservationId ?: null, 'status' => $ok ? 'reserved' : null);
	}

	/**
	 * Settle actual usage and release the unused hold. Actual usage may not
	 * exceed the authorized amount; callers must reserve again if it does.
	 */
	public function settleWallet($storeId, $reservationId, $actualMinor, $providerReference = null, array $metadata = array()){
		if(!$this->_walletOperationsAllowed($storeId)) return array('ok' => false, 'error' => 'Growth wallet operations are not enabled');
		$actualMinor = (int)$actualMinor;
		if($actualMinor < 0) return array('ok' => false, 'error' => 'Invalid actual usage');

		$this->db->trans_start();
		$reservation = $this->db->query('SELECT * FROM db_ai_growth_wallet_reservations WHERE id = ? AND store_id = ? FOR UPDATE', array((int)$reservationId, (int)$storeId))->row();
		if(!$reservation){
			$this->db->trans_complete();
			return array('ok' => false, 'error' => 'Wallet reservation not found');
		}
		if(!in_array($reservation->status, array('reserved', 'uncertain'), true)){
			$this->db->trans_complete();
			return array('ok' => in_array($reservation->status, array('settled','released'), true), 'reservation_id' => (int)$reservation->id, 'status' => $reservation->status, 'idempotent' => true);
		}
		$estimated = (int)$reservation->estimated_charge_minor;
		if($actualMinor > $estimated){
			$this->db->trans_complete();
			return array('ok' => false, 'error' => 'Actual usage exceeds the wallet reservation');
		}

		$release = $estimated - $actualMinor;
		$baseKey = 'settle:' . (int)$reservation->id;
		$ok = $this->_insertLedger($storeId, 'capture', 0, $baseKey . ':capture', $reservation->reference_type, $reservation->reference_id, $reservation->id, 0, $actualMinor, array_merge($metadata, array('provider_reference' => $providerReference)));
		if($ok && $release > 0){
			$ok = $this->_insertLedger($storeId, 'release', $release, $baseKey . ':release', $reservation->reference_type, $reservation->reference_id, $reservation->id, 0, 0, array_merge($metadata, array('provider_reference' => $providerReference)));
		}
		if($ok){
			$this->db->where('id', (int)$reservation->id)->where('store_id', (int)$storeId)->update('db_ai_growth_wallet_reservations', array(
				'actual_charge_minor' => $actualMinor,
				'released_minor' => $release,
				'status' => 'settled',
				'provider_reference' => $providerReference === null ? null : substr((string)$providerReference, 0, 191),
				'updated_at' => date('Y-m-d H:i:s'),
			));
		}
		$this->db->trans_complete();
		return array('ok' => (bool)$ok, 'reservation_id' => (int)$reservation->id, 'actual_minor' => $actualMinor, 'released_minor' => $release);
	}

	/** Mark an uncertain provider outcome; no funds are released automatically. */
	public function markReservationUncertain($storeId, $reservationId, $providerReference = null){
		if(!$this->_walletOperationsAllowed($storeId)) return false;
		return (bool)$this->db->where('id', (int)$reservationId)->where('store_id', (int)$storeId)->where('status', 'reserved')
			->update('db_ai_growth_wallet_reservations', array('status' => 'uncertain', 'provider_reference' => $providerReference, 'updated_at' => date('Y-m-d H:i:s')));
	}

	private function _ready(){
		return $this->db && $this->db->conn_id;
	}

	private function _walletOperationsAllowed($storeId){
		if(!$this->isEnabled($storeId) || empty($this->cfg['wallet_operations_enabled'])) return false;
		$settings = $this->getSettings($storeId);
		return $settings && (int)$settings->wallet_operations_enabled === 1;
	}

	private function _walletFundingAllowed($storeId){
		if(!$this->_walletOperationsAllowed($storeId) || empty($this->cfg['wallet_funding_enabled'])) return false;
		$settings = $this->getSettings($storeId);
		return $settings && (int)$settings->wallet_funding_enabled === 1;
	}

	private function _ledgerByKey($storeId, $key, $forUpdate = false){
		$sql = 'SELECT * FROM db_ai_growth_wallet_ledger WHERE store_id = ? AND idempotency_key = ?';
		if($forUpdate) $sql .= ' FOR UPDATE';
		return $this->db->query($sql, array((int)$storeId, (string)$key))->row();
	}

	private function _insertLedger($storeId, $type, $amountMinor, $idempotencyKey, $referenceType = null, $referenceId = null, $reservationId = null, $providerCostMinor = 0, $martpointChargeMinor = 0, array $metadata = array()){
		return $this->db->insert('db_ai_growth_wallet_ledger', array(
			'store_id' => (int)$storeId,
			'entry_type' => substr((string)$type, 0, 20),
			'amount_minor' => (int)$amountMinor,
			'currency' => $this->cfg['ledger_currency'] ?? 'NGN',
			'idempotency_key' => substr((string)$idempotencyKey, 0, 191),
			'reference_type' => $referenceType === null ? null : substr((string)$referenceType, 0, 50),
			'reference_id' => $referenceId === null ? null : substr((string)$referenceId, 0, 80),
			'reservation_id' => $reservationId ? (int)$reservationId : null,
			'provider_cost_minor' => max(0, (int)$providerCostMinor),
			'martpoint_charge_minor' => max(0, (int)$martpointChargeMinor),
			'metadata_json' => $metadata ? json_encode($metadata) : null,
			'created_by' => $this->session->userdata('inv_userid') ?: null,
			'created_at' => date('Y-m-d H:i:s'),
		));
	}
}

