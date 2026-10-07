<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Marketing model — Phase 3.
 *
 * Customer segmentation is read-only: preset segment keys resolve to
 * queries over db_sales/db_customers, and a saved segment is just a stored
 * preset key (+ optional JSON params). Nothing here mutates sales data.
 *
 * Campaigns dispatch through the store's configured Email_service or
 * Sms_model providers; db_campaign_sends keeps a per-recipient idempotent
 * record so a retry never re-sends to a delivered address.
 */
class Marketing_model extends CI_Model {

	/** Segment preset registry — key => [label, description]. */
	public static $SEGMENTS = [
		'all'           => ['All reachable customers', 'Every customer with an email or phone number'],
		'top_spenders'  => ['Top spenders', 'Top 25% of customers by lifetime spend'],
		'repeat'        => ['Repeat buyers', 'Customers with 2+ completed sales'],
		'one_time'      => ['One-time buyers', 'Customers with exactly one completed sale'],
		'lapsed_30'     => ['Lapsed 30+ days', 'No completed sale in the last 30 days'],
		'lapsed_60'     => ['Lapsed 60+ days', 'No completed sale in the last 60 days'],
		'lapsed_90'     => ['Lapsed 90+ days', 'No completed sale in the last 90 days'],
		'new_30'        => ['New this month', 'Created in the last 30 days'],
		'subscribers'   => ['Newsletter subscribers', 'Storefront newsletter signups (email)'],
	];

	/* ================= SEGMENTS ================= */

	public function listSegments($storeId){
		$out = [];
		foreach(self::$SEGMENTS as $key => $def){
			$out[] = ['key' => $key, 'name' => $def[0], 'desc' => $def[1], 'count' => $this->segmentCount($storeId, $key)];
		}
		return $out;
	}

	public function segmentCount($storeId, $key){
		return count($this->resolveSegment($storeId, $key, true));
	}

	/**
	 * Resolve a segment key to customer rows [id, name, email, phone].
	 * $countOnly returns minimal rows for counting.
	 */
	public function resolveSegment($storeId, $key, $countOnly = false){
		$storeId = (int)$storeId;
		$fields = $countOnly ? 'c.id' : 'c.id, c.customer_name AS name, c.email, c.mobile AS phone';
		switch($key){
			case 'subscribers':
				if(!$this->db->table_exists('db_newsletter_subscribers')) return [];
				return $this->db->select("id, '' AS name, email, '' AS phone")
					->where('store_id', $storeId)->where('status', 1)
					->get('db_newsletter_subscribers')->result();
			case 'new_30':
				return $this->db->select($fields)->from('db_customers c')
					->where('c.store_id', $storeId)
					->where("c.created_date >=", date('Y-m-d', strtotime('-30 days')))
					->group_start()->where('c.email IS NOT NULL')->where("c.email !=", '')->or_where("c.mobile IS NOT NULL")->where("c.mobile !=", '')->group_end()
					->get()->result();
			case 'repeat':
			case 'one_time':
				$op = $key === 'repeat' ? '>=' : '=';
				$n  = $key === 'repeat' ? 2 : 1;
				$sql = "SELECT " . ($countOnly ? 'c.id' : 'c.id, c.customer_name AS name, c.email, c.mobile AS phone') . "
					FROM db_customers c
					JOIN (SELECT customer_id, COUNT(*) n FROM db_sales WHERE store_id=? AND sales_status='Final' GROUP BY customer_id HAVING n {$op} {$n}) s ON s.customer_id=c.id
					WHERE c.store_id=? AND (c.email IS NOT NULL AND c.email != '' OR c.mobile IS NOT NULL AND c.mobile != '')";
				return $this->db->query($sql, [$storeId, $storeId])->result();
			case 'lapsed_30':
			case 'lapsed_60':
			case 'lapsed_90':
				$days = (int)substr($key, 7);
				$sql = "SELECT " . ($countOnly ? 'c.id' : 'c.id, c.customer_name AS name, c.email, c.mobile AS phone') . "
					FROM db_customers c
					WHERE c.store_id=? AND (c.email IS NOT NULL AND c.email != '' OR c.mobile IS NOT NULL AND c.mobile != '')
					AND c.id IN (SELECT customer_id FROM db_sales WHERE store_id=? AND sales_status='Final')
					AND c.id NOT IN (SELECT customer_id FROM db_sales WHERE store_id=? AND sales_status='Final' AND created_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY))";
				return $this->db->query($sql, [$storeId, $storeId, $storeId])->result();
			case 'top_spenders':
				$sql = "SELECT " . ($countOnly ? 'c.id' : 'c.id, c.customer_name AS name, c.email, c.mobile AS phone') . "
					FROM db_customers c
					JOIN (SELECT customer_id, SUM(grand_total) tv FROM db_sales WHERE store_id=? AND sales_status='Final' GROUP BY customer_id ORDER BY tv DESC) s ON s.customer_id=c.id
					WHERE c.store_id=? AND (c.email IS NOT NULL AND c.email != '' OR c.mobile IS NOT NULL AND c.mobile != '')
					ORDER BY s.tv DESC";
				$rows = $this->db->query($sql, [$storeId, $storeId])->result();
				return array_slice($rows, 0, max(1, (int)ceil(count($rows) / 4)));
			case 'all':
				return $this->db->select($fields)->from('db_customers c')
					->where('c.store_id', $storeId)
					->group_start()->where('c.email IS NOT NULL')->where("c.email !=", '')->or_where("c.mobile IS NOT NULL")->where("c.mobile !=", '')->group_end()
					->get()->result();
			default:
				return [];
		}
	}

	public function saveSegment($storeId, $name, $key, $definition = null, $id = null){
		if(!$this->db->table_exists('db_customer_segments')) return false;
		$row = ['store_id' => (int)$storeId, 'name' => substr(trim($name), 0, 120),
			'segment_key' => $key, 'definition_json' => $definition ? json_encode($definition) : null];
		if($id){
			$this->db->where('id', (int)$id)->where('store_id', (int)$storeId)->update('db_customer_segments', $row);
			return (int)$id;
		}
		$row['created_by'] = substr((string)($this->session->userdata('inv_username') ?: 'system'), 0, 60);
		$row['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('db_customer_segments', $row);
		return (int)$this->db->insert_id();
	}

	public function deleteSegment($id, $storeId){
		if(!$this->db->table_exists('db_customer_segments')) return false;
		return $this->db->where('id', (int)$id)->where('store_id', (int)$storeId)->delete('db_customer_segments');
	}

	public function getSegment($id, $storeId){
		if(!$this->db->table_exists('db_customer_segments')) return null;
		return $this->db->where('id', (int)$id)->where('store_id', (int)$storeId)->get('db_customer_segments')->row();
	}

	public function listSavedSegments($storeId){
		if(!$this->db->table_exists('db_customer_segments')) return [];
		return $this->db->where('store_id', (int)$storeId)->order_by('id', 'desc')->get('db_customer_segments')->result();
	}

	/* ================= CAMPAIGNS ================= */

	public function saveCampaign($storeId, array $data, $id = null){
		if(!$this->db->table_exists('db_campaigns')) return false;
		$row = [
			'store_id'    => (int)$storeId,
			'name'        => substr(trim((string)$data['name']), 0, 150),
			'segment_key' => $data['segment_key'] ?? null,
			'segment_id'  => !empty($data['segment_id']) ? (int)$data['segment_id'] : null,
			'channel'     => in_array($data['channel'] ?? '', ['email', 'sms'], true) ? $data['channel'] : 'email',
			'subject'     => substr((string)($data['subject'] ?? ''), 0, 255) ?: null,
			'message'     => (string)($data['message'] ?? ''),
		];
		if($id){
			$this->db->where('id', (int)$id)->where('store_id', (int)$storeId)->where('status', 'draft')->update('db_campaigns', $row);
			return $this->db->affected_rows() ? (int)$id : false;
		}
		$row['created_by'] = substr((string)($this->session->userdata('inv_username') ?: 'system'), 0, 60);
		$row['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('db_campaigns', $row);
		return (int)$this->db->insert_id();
	}

	public function getCampaign($id, $storeId){
		if(!$this->db->table_exists('db_campaigns')) return null;
		return $this->db->where('id', (int)$id)->where('store_id', (int)$storeId)->get('db_campaigns')->row();
	}

	public function listCampaigns($storeId, $limit = 100){
		if(!$this->db->table_exists('db_campaigns')) return [];
		return $this->db->where('store_id', (int)$storeId)->order_by('id', 'desc')->limit((int)$limit)->get('db_campaigns')->result();
	}

	/**
	 * Snapshot the campaign's audience into db_campaign_sends (pending rows).
	 * Unique (campaign_id, channel, recipient) keeps repeat dispatches
	 * idempotent — a retry only touches recipients still 'pending'.
	 * Note: the unique row prevents duplicate STAGING only; duplicate
	 * provider sends are prevented by claimSend()'s pending→sending gate.
	 */
	public function stageCampaignAudience($campaign, array $audience){
		if(!$this->db->table_exists('db_campaign_sends')) return 0;
		$hasToken = $this->db->field_exists('unsub_token', 'db_campaign_sends');
		$staged = 0;
		foreach($audience as $m){
			$recipient = $campaign->channel === 'sms' ? trim((string)($m->phone ?? '')) : trim((string)($m->email ?? ''));
			if($recipient === '') continue;
			if($campaign->channel === 'email' && !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
			$cols = '(store_id, campaign_id, channel, recipient, customer_name, status' . ($hasToken ? ', unsub_token' : '') . ', created_at)';
			$vals = [(int)$campaign->store_id, (int)$campaign->id, $campaign->channel, substr($recipient, 0, 191), substr((string)($m->name ?? ''), 0, 191), 'pending'];
			if($hasToken) $vals[] = bin2hex(random_bytes(16));
			// INSERT IGNORE — a retry never duplicates a row.
			$this->db->query('INSERT IGNORE INTO db_campaign_sends ' . $cols . ' VALUES (' . rtrim(str_repeat('?,', count($vals)), ',') . ',NOW())', $vals);
			if($this->db->affected_rows()) $staged++;
		}
		$audienceCount = $this->db->where('campaign_id', (int)$campaign->id)->count_all_results('db_campaign_sends');
		$this->db->where('id', (int)$campaign->id)->update('db_campaigns', ['audience_count' => $audienceCount]);
		return $staged;
	}

	public function getPendingSends($campaignId, $limit = 500){
		return $this->db->where('campaign_id', (int)$campaignId)->where('status', 'pending')
			->limit((int)$limit)->get('db_campaign_sends')->result();
	}

	/**
	 * Atomically claim a pending send for dispatch. Returns the row on
	 * success, null if another worker took it, it is not pending, or it
	 * exhausted its 5-attempt cap. This is what prevents a duplicate
	 * provider call when two dispatches race — the staging unique key
	 * alone cannot.
	 */
	public function claimSend($sendId){
		$hasClaimed = $this->db->field_exists('claimed_at', 'db_campaign_sends');
		if($hasClaimed){
			$this->db->query("UPDATE db_campaign_sends SET status='sending', claimed_at=NOW(), attempts=attempts+1 WHERE id=? AND status='pending' AND attempts < 5", [(int)$sendId]);
		} else {
			$this->db->where('id', (int)$sendId)->where('status', 'pending')
				->update('db_campaign_sends', ['status' => 'sending']);
		}
		if($this->db->affected_rows() !== 1) return null;
		return $this->db->where('id', (int)$sendId)->get('db_campaign_sends')->row();
	}

	/**
	 * Release sends stranded in 'sending' by an interrupted dispatch
	 * (fatal/timeout between claim and settle) back to pending — the
	 * attempt counter still applies, so a poisoned recipient gives up
	 * after 5 provider calls.
	 */
	public function releaseStuckSends($olderThanMinutes = 30){
		if(!$this->db->field_exists('claimed_at', 'db_campaign_sends')) return 0;
		$this->db->where('status', 'sending')
			->where('(claimed_at IS NULL OR claimed_at < DATE_SUB(NOW(), INTERVAL ' . (int)$olderThanMinutes . ' MINUTE))', null, false)
			->update('db_campaign_sends', ['status' => 'pending']);
		return $this->db->affected_rows();
	}

	/** Campaigns with unfinished work — for the background cron sweep. */
	public function getUnfinishedCampaigns(){
		if(!$this->db->table_exists('db_campaigns') || !$this->db->table_exists('db_campaign_sends')) return [];
		return $this->db->query("SELECT c.* FROM db_campaigns c WHERE c.status IN ('sending','partial') AND EXISTS (SELECT 1 FROM db_campaign_sends s WHERE s.campaign_id = c.id AND s.status = 'pending')")->result();
	}

	public function markSend($sendId, $status, $error = null){
		$status = in_array($status, ['sent', 'failed', 'suppressed'], true) ? $status : 'failed';
		$this->db->where('id', (int)$sendId)->update('db_campaign_sends', [
			'status' => $status,
			'error'  => $error ? substr((string)$error, 0, 255) : null,
			'sent_at'=> $status === 'sent' ? date('Y-m-d H:i:s') : null,
		]);
		// Refresh campaign counters. NOTE: counts must be evaluated into
		// variables before the update — count_all_results() resets the query
		// builder, so building them inside update()'s argument list would
		// strip the where() and hit every db_campaigns row.
		$send = $this->db->where('id', (int)$sendId)->get('db_campaign_sends')->row();
		if($send){
			$cid = (int)$send->campaign_id;
			$sent = $this->db->where('campaign_id', $cid)->where('status', 'sent')->count_all_results('db_campaign_sends');
			$failed = $this->db->where('campaign_id', $cid)->where('status', 'failed')->count_all_results('db_campaign_sends');
			$this->db->where('id', $cid)->update('db_campaigns', ['sent_count' => $sent, 'failed_count' => $failed]);
		}
	}

	/** Requeue failed sends that have attempts left — retry touches only failures. */
	public function requeueFailed($campaignId, $storeId){
		if(!$this->db->field_exists('attempts', 'db_campaign_sends')){
			$this->db->where('campaign_id', (int)$campaignId)->where('store_id', (int)$storeId)
				->where('status', 'failed')->update('db_campaign_sends', ['status' => 'pending']);
			return $this->db->affected_rows();
		}
		$this->db->where('campaign_id', (int)$campaignId)->where('store_id', (int)$storeId)
			->where('status', 'failed')->where('attempts <', 5)
			->update('db_campaign_sends', ['status' => 'pending', 'error' => null]);
		return $this->db->affected_rows();
	}

	public function campaignSendStats($campaignId){
		if(!$this->db->table_exists('db_campaign_sends')) return ['pending' => 0, 'sending' => 0, 'sent' => 0, 'failed' => 0, 'suppressed' => 0];
		$rows = $this->db->select('status, COUNT(*) n')->where('campaign_id', (int)$campaignId)->group_by('status')->get('db_campaign_sends')->result();
		$out = ['pending' => 0, 'sending' => 0, 'sent' => 0, 'failed' => 0, 'suppressed' => 0, 'unsubscribed' => 0];
		foreach($rows as $r) $out[$r->status] = (int)$r->n;
		return $out;
	}

	/* ================= SUPPRESSION ================= */

	/**
	 * Authoritative send-time check. A suppression row for the channel —
	 * or 'all' — means the recipient is never contacted again by
	 * campaigns, regardless of when the audience was staged.
	 */
	public function isSuppressed($storeId, $channel, $recipient){
		if(!$this->db->table_exists('db_marketing_suppressions')) return false;
		return $this->db->where('store_id', (int)$storeId)
			->where_in('channel', [$channel, 'all'])
			->where('recipient', substr((string)$recipient, 0, 191))
			->count_all_results('db_marketing_suppressions') > 0;
	}

	public function suppressRecipient($storeId, $channel, $recipient, $source = 'link'){
		if(!$this->db->table_exists('db_marketing_suppressions')) return false;
		$this->db->query('INSERT IGNORE INTO db_marketing_suppressions (store_id, channel, recipient, source, created_at) VALUES (?,?,?,?,NOW())',
			[(int)$storeId, $channel === 'sms' ? 'sms' : 'email', substr((string)$recipient, 0, 191), substr((string)$source, 0, 30)]);
		return $this->db->affected_rows() >= 0;
	}

	/** Public unsubscribe: send-row token → suppress + mark the send. */
	public function unsubscribeByToken($token){
		$token = preg_replace('/[^a-f0-9]/', '', (string)$token);
		if(strlen($token) < 32 || !$this->db->field_exists('unsub_token', 'db_campaign_sends')) return null;
		$send = $this->db->where('unsub_token', $token)->get('db_campaign_sends')->row();
		if(!$send) return null;
		$this->suppressRecipient($send->store_id, $send->channel, $send->recipient, 'link');
		if($send->status === 'pending' || $send->status === 'sending'){
			$this->db->where('id', $send->id)->update('db_campaign_sends', ['status' => 'suppressed', 'error' => 'unsubscribed']);
		}
		return $send;
	}

	public function getCampaignSends($campaignId, $storeId, $limit = 200){
		return $this->db->where('campaign_id', (int)$campaignId)->where('store_id', (int)$storeId)
			->order_by('id', 'desc')->limit((int)$limit)->get('db_campaign_sends')->result();
	}

	/* ================= BACK-IN-STOCK ================= */

	public function subscribeStockAlert($storeId, $itemId, $itemName, $email, $phone = null){
		if(!$this->db->table_exists('db_stock_alerts')) return false;
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
		$token = bin2hex(random_bytes(20));
		// INSERT ... ON DUPLICATE: re-subscribing refreshes rather than dupes.
		$this->db->query('INSERT INTO db_stock_alerts (store_id, item_id, item_name, email, phone, token, created_at) VALUES (?,?,?,?,?,?,NOW())
			ON DUPLICATE KEY UPDATE item_name=VALUES(item_name), phone=VALUES(phone), unsubscribed=0, notified=0',
			[(int)$storeId, (int)$itemId, substr((string)$itemName, 0, 191), substr($email, 0, 191), substr((string)$phone, 0, 50), $token]);
		return $this->db->affected_rows() >= 1;
	}

	public function unsubscribeStockAlert($token){
		if(!$this->db->table_exists('db_stock_alerts')) return false;
		$this->db->where('token', preg_replace('/[^a-f0-9]/', '', (string)$token))->update('db_stock_alerts', ['unsubscribed' => 1]);
		return $this->db->affected_rows() === 1;
	}

	/**
	 * Items with pending subscribers that are now in stock — for the cron.
	 * Effective stock mirrors the storefront's display semantics
	 * (Storefront_model::_decorateVariantParents): a Variants parent counts
	 * its own stock plus published, sellable children's — so an alert on a
	 * parent fires when any variant restocks, and an alert on a variant
	 * child fires only when that child restocks.
	 * NOTE on stock semantics: db_items.stock is the item's store-level
	 * sellable quantity — there is no per-branch column on db_items, so
	 * "in stock" here means the store's combined inventory figure, the same
	 * number the storefront shows and checkout enforces.
	 */
	public function getRestockableAlerts($limit = 200){
		if(!$this->db->table_exists('db_stock_alerts')) return [];
		// Child rows are matched by parent_id only — same as the
		// storefront decorator. Legacy variant children can carry
		// store_id NULL; filtering on store_id would under-count stock
		// that the storefront already shows as available.
		$effStock = "(i.stock + COALESCE(CASE WHEN i.item_group = 'Variants' THEN (SELECT SUM(c.stock) FROM db_items c"
			. " WHERE c.parent_id = i.id AND c.publish_online = 1"
			. " AND (c.status = 1 OR c.status IS NULL) AND (c.not_for_sale IS NULL OR c.not_for_sale = 0)) END, 0))";
		return $this->db->select("a.*, {$effStock} AS eff_stock, i.item_name AS live_name")
			->from('db_stock_alerts a')
			->join('db_items i', 'i.id = a.item_id AND i.store_id = a.store_id')
			->where('a.notified', 0)->where('a.unsubscribed', 0)
			->where('a.send_attempts <', 5)
			->where("{$effStock} >", 0, false)
			->order_by('a.id', 'asc')->limit((int)$limit)
			->get()->result();
	}

	public function markStockAlertNotified($id){
		$this->db->where('id', (int)$id)->update('db_stock_alerts', [
			'notified' => 1, 'notified_at' => date('Y-m-d H:i:s'),
		]);
		return $this->db->affected_rows() === 1;
	}

	public function listStockAlerts($storeId, $limit = 200){
		if(!$this->db->table_exists('db_stock_alerts')) return [];
		return $this->db->select('a.*, i.item_name AS live_name, i.stock')
			->from('db_stock_alerts a')
			->join('db_items i', 'i.id = a.item_id', 'left')
			->where('a.store_id', (int)$storeId)
			->order_by('a.id', 'desc')->limit((int)$limit)
			->get()->result();
	}
}
