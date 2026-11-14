<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * The daily expiry list.
 *
 * Wave 3 gave the shop a report that answers "what is running out of date"
 * when somebody opens it. This sends the same answer to them once a day, on
 * the channels the Expiry Settings screen already owns: the email address in
 * alert_email and, when the shop ticks it, the WhatsApp number in alert_phone.
 *
 * Three promises hold this together:
 *  1. the list is the report. Every batch here comes out of
 *     Pharmacy_batch_report_model::expiring(), so a message can never disagree
 *     with /batch_reports the way the old item-level alert did;
 *  2. one list per shop per day. last_alert_slot stamps the day a digest
 *     actually reached the shop, so a re-run — or a cron that fires twice —
 *     sends nothing a second time;
 *  3. nothing is invented and nothing is hidden. The message says which
 *     batches are already past, which run out inside the shop's own window,
 *     how many had to be left off for space, and how many batches on the
 *     shelf carry no date at all.
 */
class Pharmacy_expiry_alert_model extends CI_Model {

	/** How many batches fit in the email before the rest is summarised. */
	const LIST_ROWS = 30;
	/** WhatsApp has to stay short — the full list lives on the report screen. */
	const SMS_ROWS = 3;

	public $last_errors = array();

	/**
	 * Can the alert run at all? A server that has not applied the expiry-alert
	 * migration has nowhere to keep the shop's WhatsApp number or the day stamp,
	 * and a server with no batch ledger has no list to send — either way the
	 * sweep must say so instead of messaging an empty table.
	 */
	public function ready() {
		return $this->db->table_exists('db_expiry_settings')
			&& $this->db->table_exists('db_item_barcodes')
			&& $this->db->field_exists('alert_phone', 'db_expiry_settings');
	}

	/** The store's own settings, with the alert columns safe on older schemas. */
	public function settings($storeId) {
		$this->load->model('expiry_settings_model');
		$settings = $this->expiry_settings_model->get_settings((int)$storeId);
		$settings->whatsapp_alerts_enabled = (int)($settings->whatsapp_alerts_enabled ?? 0);
		$settings->alert_phone = trim((string)($settings->alert_phone ?? ''));
		$settings->last_alert_slot = trim((string)($settings->last_alert_slot ?? ''));
		return $settings;
	}

	/**
	 * Is this shop's expiry warning the batch-ledger one? The digest lists
	 * batches and links to /batch_reports, which is the pharmacy workspace's
	 * screen, so the daily list is a pharmacy behaviour. A bakery or a cosmetics
	 * counter keeps the item-level alert it has always had rather than being
	 * pointed at a report it cannot open.
	 */
	public function available($storeId) {
		$storeId = (int)$storeId;
		if (!$this->ready()) return false;
		if (!function_exists('mp_pharmacy_industry')) return false;
		$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile($storeId) : array();
		if (!mp_pharmacy_industry($profile['industry_type'] ?? '')) return false;
		if (function_exists('mp_feature_enabled_for_store') && !mp_feature_enabled_for_store('expiry_tracking', $storeId)) return false;
		return true;
	}

	/**
	 * Shops that asked to hear about expiry: a channel switched on, and their
	 * own flag and industry — each read per store, so one shop's preset can
	 * never decide another's.
	 */
	public function alertable_stores() {
		if (!$this->ready()) return array();
		$rows = $this->db->where('(email_alerts_enabled = 1 OR whatsapp_alerts_enabled = 1)', null, false)
			->order_by('store_id', 'asc')->get('db_expiry_settings')->result();
		$out = array();
		foreach ($rows as $row) {
			$storeId = (int)$row->store_id;
			if ($storeId <= 0) continue;
			if (!$this->available($storeId)) continue;
			$out[] = $storeId;
		}
		return $out;
	}

	/** The batches the digest is made of: the report's own running-out-of-date tab. */
	private function _report($storeId) {
		$this->load->model('pharmacy_batch_report_model', 'batch_reports');
		$horizon = $this->batch_reports->horizonDays((int)$storeId);
		$filters = $this->batch_reports->filters($storeId, array('horizon' => $horizon));
		$filters['per_page'] = self::LIST_ROWS;
		$filters['offset'] = 0;
		return $this->_statsOf($this->batch_reports->expiring($storeId, $filters), $horizon);
	}

	private function _statsOf($report, $horizon) {
		$bands = $report['bands'] ?? array();
		$totals = $report['totals'] ?? array();
		$rows = $report['rows'] ?? array();
		$total = (int)($report['total'] ?? 0);
		return array(
			'batches' => (int)($totals['batch_rows'] ?? $total),
			'units' => round((float)($bands['expired_qty'] ?? 0) + (float)($bands['soon_qty'] ?? 0), 4),
			'expired_units' => round((float)($bands['expired_qty'] ?? 0), 4),
			'soon_units' => round((float)($bands['soon_qty'] ?? 0), 4),
			'value' => round((float)($totals['cost_value'] ?? 0), 2),
			'listed' => count($rows),
			'total' => $total,
			'omitted' => max(0, $total - count($rows)),
			'no_expiry_rows' => (int)($totals['no_expiry_rows'] ?? 0),
			'horizon' => $horizon,
			'until' => (string)($bands['soon_until'] ?? date('Y-m-d')),
			'sellable_units' => round((float)($totals['sellable_qty'] ?? 0), 4),
			'held_units' => round((float)($totals['held_qty'] ?? 0), 4),
			'rows' => $rows,
		);
	}

	/**
	 * What today's list would contain, for the settings screen — the same call
	 * the digest makes, so the screen can never show one number and the message
	 * another.
	 */
	public function stats($storeId) {
		if (!$this->available($storeId)) {
			return array('available' => false, 'stats' => $this->_statsOf(array(), 0), 'today' => date('Y-m-d'));
		}
		return array('available' => true, 'stats' => $this->_report($storeId), 'today' => date('Y-m-d'));
	}


	/**
	 * Build the day's list for one shop. Returns the message bodies plus a
	 * `skip` reason whenever the shop should not be messaged — an unconfigured
	 * address, nothing actually expiring, or a shelf the ledger cannot see.
	 */
	public function compose($storeId, $today = null) {
		$storeId = (int)$storeId;
		$today = $today ?: date('Y-m-d');
		$out = array(
			'store_id' => $storeId,
			'date' => $today,
			'skip' => '',
			'channels' => array(),
			'subject' => '',
			'html' => '',
			'text' => '',
			'sms' => '',
			'stats' => array('batches' => 0, 'units' => 0.0, 'expired_units' => 0.0, 'soon_units' => 0.0,
				'value' => 0.0, 'listed' => 0, 'omitted' => 0, 'no_expiry_rows' => 0, 'horizon' => 30,
				'until' => $today, 'sellable_units' => 0.0, 'held_units' => 0.0),
		);
		if (!$this->available($storeId)) {
			$out['skip'] = 'The expiry list needs the batch ledger and the pharmacy expiry settings; this server has not installed them yet.';
			return $out;
		}

		$settings = $this->settings($storeId);
		$email = trim((string)($settings->alert_email ?? ''));
		$phone = trim((string)($settings->alert_phone ?? ''));
		$wantsEmail = (int)($settings->email_alerts_enabled ?? 0) === 1 && $email !== '';
		$wantsWhatsapp = (int)$settings->whatsapp_alerts_enabled === 1 && $phone !== '';
		if (!$wantsEmail && !$wantsWhatsapp) {
			$out['skip'] = 'No expiry alert channel is switched on with an address to reach — set one under Expiry Settings.';
			return $out;
		}
		if ($wantsEmail && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$out['skip'] = 'The expiry alert email address is not a valid address — correct it under Expiry Settings.';
			return $out;
		}
		$out['channels'] = array();
		if ($wantsEmail) $out['channels']['email'] = $email;
		if ($wantsWhatsapp) $out['channels']['whatsapp'] = $phone;

		$this->load->model('pharmacy_batch_report_model', 'batch_reports');
		$horizon = $this->batch_reports->horizonDays($storeId);
		$stats = $this->_report($storeId);
		$out['stats'] = $stats;
		$rows = $stats['rows'];
		$total = $stats['total'];

		if ($total === 0) {
			$out['skip'] = 'Nothing on the batch ledger is past its date or runs out inside the warning window.';
			return $out;
		}

		$storeName = function_exists('get_store_name') ? (string)get_store_name($storeId) : 'MartPoint';
		$reportUrl = (function_exists('base_url') ? base_url('batch_reports') : 'batch_reports') . '?report=expiring';
		$blocksExpired = false;
		$rotation = '';
		if (method_exists($this->batch_reports, 'rotationMethod')) {
			$rotation = strtoupper((string)$this->batch_reports->rotationMethod($storeId));
		}
		$this->load->model('inventory_rotation_model');
		$blocksExpired = (bool)$this->inventory_rotation_model->blocks_expired_sales($storeId);

		$qty = function ($value) { return function_exists('format_qty') ? format_qty((float)$value) : number_format((float)$value, 2); };
		$money = function ($value) { return function_exists('store_number_format') ? store_number_format((float)$value) : number_format((float)$value, 2); };
		$day = function ($date) { $stamp = strtotime((string)$date); return $stamp === false ? (string)$date : date('d M Y', $stamp); };

		$stats = $out['stats'];
		$headline = $stats['expired_units'] > 0
			? $qty($stats['expired_units']) . ' unit(s) already past their date and ' . $qty($stats['soon_units']) . ' more out of date by ' . $day($stats['until'])
			: $qty($stats['soon_units']) . ' unit(s) run out of date by ' . $day($stats['until']);
		$out['subject'] = $storeName . ' — expiry list for ' . $day($today) . ' (' . $stats['batches'] . ' batches)';

		$rowsHtml = '';
		$textLines = array();
		$smsLines = array();
		foreach ($rows as $row) {
			$held = $this->batch_reports->held_reason($row, $blocksExpired, $today);
			$state = ($held === '') ? 'Can still be sold' : $held;
			$expires = trim((string)($row->expire_date ?? ''));
			$when = $expires === '' || $expires === '0000-00-00' ? 'No date recorded'
				: ($expires < $today ? 'Expired ' . $day($expires) : 'Expires ' . $day($expires));
			$lot = trim((string)($row->batch_lot ?? '')) !== '' ? trim((string)$row->batch_lot) : 'Lot not recorded';
			$where = trim((string)($row->warehouse_name ?? '')) !== '' ? trim((string)$row->warehouse_name) : 'Location not set';
			$rowsHtml .= '<tr>'
				. '<td style="padding:6px 8px;border-bottom:1px solid #e6e6e6;">' . htmlspecialchars($row->item_name . ' (' . $row->item_code . ')') . '</td>'
				. '<td style="padding:6px 8px;border-bottom:1px solid #e6e6e6;">' . htmlspecialchars($lot) . '</td>'
				. '<td style="padding:6px 8px;border-bottom:1px solid #e6e6e6;">' . htmlspecialchars($where) . '</td>'
				. '<td style="padding:6px 8px;border-bottom:1px solid #e6e6e6;white-space:nowrap;">' . htmlspecialchars($when) . '</td>'
				. '<td style="padding:6px 8px;border-bottom:1px solid #e6e6e6;text-align:right;">' . htmlspecialchars($qty($row->qty)) . '</td>'
				. '<td style="padding:6px 8px;border-bottom:1px solid #e6e6e6;">' . htmlspecialchars($state) . '</td>'
				. '</tr>';
			$textLines[] = '- ' . $row->item_name . ' [' . $lot . '] at ' . $where . ': ' . $qty($row->qty) . ' left, ' . strtolower($when) . ', ' . strtolower($state);
			if (count($smsLines) < self::SMS_ROWS) $smsLines[] = $row->item_name . ' ' . $lot . ' (' . $qty($row->qty) . ', ' . strtolower($when) . ')';
		}

		$omittedNote = $stats['omitted'] > 0
			? 'and ' . $stats['omitted'] . ' more batch(es) on the expiry report'
			: '';
		$out['text'] = $storeName . ' — expiry list for ' . $day($today) . "\n"
			. $stats['batches'] . ' batch(es) affected: ' . $headline . '. Value at cost: ' . $money($stats['value']) . ".\n"
			. ($rotation === 'MANUAL' ? 'Batch rotation is on Manual — the clerk picks the batch, so check dates before selling.' : 'Batch rotation: ' . $rotation . '.') . "\n"
			. implode("\n", $textLines) . "\n"
			. ($omittedNote !== '' ? '… ' . $omittedNote . ".\n" : '')
			. ($stats['no_expiry_rows'] > 0 ? 'Note: ' . $stats['no_expiry_rows'] . ' batch(es) on the shelf carry no expiry date at all, so they cannot appear on this list.\n' : '')
			. 'Full report: ' . $reportUrl . "\n";

		$out['html'] = '<div style="font-family:Arial,Helvetica,sans-serif;color:#222;">'
			. '<h2 style="margin:0 0 4px;">Expiry list — ' . htmlspecialchars($storeName) . '</h2>'
			. '<p style="margin:0 0 14px;color:#666;">' . htmlspecialchars($day($today)) . ' · warning window ' . (int)$stats['horizon'] . ' day(s)</p>'
			. '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:760px;margin-bottom:14px;">'
			. '<tr>'
			. '<td style="padding:10px 12px;background:rgba(220,38,38,.08);border:1px solid #e6e6e6;border-radius:8px;"><strong style="color:#dc2626;">' . htmlspecialchars($qty($stats['expired_units'])) . '</strong><br>units already past their date</td>'
			. '<td style="width:14px;"></td>'
			. '<td style="padding:10px 12px;background:rgba(245,158,11,.08);border:1px solid #e6e6e6;border-radius:8px;"><strong style="color:#b45309;">' . htmlspecialchars($qty($stats['soon_units'])) . '</strong><br>more out of date by ' . htmlspecialchars($day($stats['until'])) . '</td>'
			. '</tr></table>'
			. '<p style="margin:0 0 12px;">' . (int)$stats['batches'] . ' batch(es) on the ledger are affected. Cost value of the list: ' . htmlspecialchars($money($stats['value'])) . '. '
			. htmlspecialchars($qty($stats['held_units'])) . ' of those units the counter will not sell today.</p>'
			. '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:760px;font-size:14px;">'
			. '<tr style="background:#f5f5f5;">'
			. '<th style="padding:8px;border:1px solid #e6e6e6;text-align:left;">Medicine</th>'
			. '<th style="padding:8px;border:1px solid #e6e6e6;text-align:left;">Batch</th>'
			. '<th style="padding:8px;border:1px solid #e6e6e6;text-align:left;">Location</th>'
			. '<th style="padding:8px;border:1px solid #e6e6e6;text-align:left;">Date</th>'
			. '<th style="padding:8px;border:1px solid #e6e6e6;text-align:right;">Left</th>'
			. '<th style="padding:8px;border:1px solid #e6e6e6;text-align:left;">Can it be sold?</th>'
			. '</tr>' . $rowsHtml . '</table>'
			. ($omittedNote !== '' ? '<p style="margin:10px 0;color:#666;">… ' . htmlspecialchars($omittedNote) . '.</p>' : '')
			. ($rotation === 'MANUAL' ? '<p style="margin:10px 0;">Batch rotation is on <strong>Manual</strong> — the clerk picks the batch, so check these dates before selling.</p>' : '')
			. ($stats['no_expiry_rows'] > 0 ? '<p style="margin:10px 0;color:#b45309;">' . (int)$stats['no_expiry_rows'] . ' batch(es) on the shelf carry no expiry date at all, so they cannot appear on this list — the report screen counts them too.</p>' : '')
			. '<p style="margin:16px 0 0;"><a href="' . htmlspecialchars($reportUrl) . '" style="display:inline-block;padding:10px 18px;background:#007bff;color:#fff;text-decoration:none;border-radius:5px;">Open the expiry report</a></p>'
			. '<p style="color:#888;font-size:12px;margin:16px 0 0;">Sent by MartPoint as part of your expiry alerts. Change the window, the address or the channel under Inventory → Expiry Settings.</p>'
			. '</div>';

		$out['sms'] = $storeName . ' expiry list ' . $day($today) . ': ' . (int)$stats['batches'] . ' batches, ' . $qty($stats['units']) . ' units'
			. ($stats['expired_units'] > 0 ? ' (' . $qty($stats['expired_units']) . ' already past date)' : '')
			. '. ' . implode('; ', $smsLines)
			. ($stats['omitted'] > 0 ? ' +' . (int)$stats['omitted'] . ' more' : '')
			. '. Details: ' . $reportUrl;

		return $out;
	}

	/**
	 * Hand the composed list to the channels the shop switched on. Sending goes
	 * through the same email/SMS services the rest of the app uses, so a paused
	 * fleet account or a store with no WhatsApp connected fails here rather than
	 * pretending a message went out.
	 */
	public function deliver($storeId, array $composed) {
		$storeId = (int)$storeId;
		$result = array('status' => 'skipped', 'sent' => 0, 'failed' => 0, 'messages' => array());
		$this->last_errors = array();
		if (!empty($composed['skip'])) {
			$result['messages'][] = $composed['skip'];
			return $result;
		}
		$channels = $composed['channels'];
		$today = (string)$composed['date'];

		if (isset($channels['email'])) {
			$this->load->model('email_service');
			$this->email_service->setStoreId($storeId);
			$send = $this->email_service->sendRaw($channels['email'], $composed['subject'], $composed['html'], $composed['text'], array(
				'template_key' => 'expiry_alert',
				'messaging_key' => 'expiry-alert:' . $storeId . ':' . $today,
				'related_module' => 'expiry_alert',
				'related_record_id' => $today,
			));
			$ok = !empty($send['success']) && strpos((string)($send['message'] ?? ''), 'Skipped.') === false;
			if ($ok) { $result['sent']++; }
			else {
				$result['failed']++;
				$why = 'email: ' . (string)($send['message'] ?? 'no answer');
				$result['messages'][] = $why;
				$this->last_errors[] = $why;
			}
		}

		if (isset($channels['whatsapp'])) {
			$this->load->model('sms_model');
			$send = $this->sms_model->send_sms($channels['whatsapp'], $composed['sms'], array(
				'store_id' => $storeId,
				'channel' => 'whatsapp',
				'message_category' => 'transactional',
				'messaging_key' => 'expiry-alert-whatsapp:' . $storeId . ':' . $today,
			));
			if ($send === 'success') { $result['sent']++; }
			else {
				$result['failed']++;
				$why = 'whatsapp: ' . (string)$send;
				$result['messages'][] = $why;
				$this->last_errors[] = $why;
			}
		}

		$result['status'] = $result['sent'] > 0 ? ($result['failed'] > 0 ? 'partial' : 'success') : ($result['failed'] > 0 ? 'error' : 'skipped');
		return $result;
	}

	/** The day a digest reached the shop — the whole point of "one list a day". */
	public function mark_sent($storeId, $date) {
		if (!$this->ready()) return false;
		return $this->db->where('store_id', (int)$storeId)
			->update('db_expiry_settings', array('last_alert_slot' => $date));
	}

	/**
	 * The nightly run: every shop that asked, once a day, skipping quietly when
	 * there is nothing to report. A store that had nothing to send is not
	 * stamped, so a later run the same day can still reach it if stock moved.
	 */
	public function sweep($today = null) {
		$today = $today ?: date('Y-m-d');
		$result = array('stores' => 0, 'sent' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => array());
		foreach ($this->alertable_stores() as $storeId) {
			$result['stores']++;
			$settings = $this->settings($storeId);
			if ((string)$settings->last_alert_slot === $today) { $result['skipped']++; continue; }
			$composed = $this->compose($storeId, $today);
			if (!empty($composed['skip'])) { $result['skipped']++; continue; }
			$delivered = $this->deliver($storeId, $composed);
			if ($delivered['sent'] > 0) {
				$this->mark_sent($storeId, $today);
				$result['sent']++;
			} else {
				$result['failed']++;
			}
			foreach ($delivered['messages'] as $message) {
				$result['errors'][] = 'store ' . $storeId . ' ' . $message;
			}
		}
		return $result;
	}
}
