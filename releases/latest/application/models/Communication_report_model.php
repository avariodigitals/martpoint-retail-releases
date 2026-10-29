<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Campaign results: what the app already knows about each recipient (delivered,
 * opened, clicked, unsubscribed) plus the open/click events themselves.
 *
 * Every read is store-scoped except the token lookups, which are capabilities
 * carried inside the delivered message itself.
 */
class Communication_report_model extends CI_Model {
    const PAGE = 25;
    const FILTERS = array('all', 'opened', 'clicked', 'failed', 'waiting');
    /** A mailbox that reloads a message is not a new read. */
    const DEDUPE_MINUTES = 60;

    public function ready() {
        return $this->db->table_exists('db_campaign_sends') && $this->db->field_exists('open_count', 'db_campaign_sends')
            && $this->db->table_exists('db_communication_email_events');
    }

    public function campaign($storeId, $campaignId) {
        if (!(int)$campaignId || !$this->db->table_exists('db_campaigns')) return null;
        return $this->db->where('id', (int)$campaignId)->where('store_id', (int)$storeId)->get('db_campaigns')->row_array();
    }

    /** The staged row a tracking link belongs to. */
    public function sendByToken($token) {
        $this->load->library('communication_tracking');
        $token = strtolower(trim((string)$token));
        if (!preg_match(Communication_tracking::TOKEN, $token) || !$this->db->field_exists('unsub_token', 'db_campaign_sends')) return null;
        return $this->db->where('unsub_token', $token)->get('db_campaign_sends')->row();
    }

    /**
     * One counted open per recipient per hour. Scanners and image prefetchers are
     * ignored entirely, so the reported rate stays close to real readers.
     */
    public function recordOpen($send, $userAgent) {
        if (!$send || !$this->ready() || Communication_tracking::isAutomation($userAgent)) return false;
        if ($this->countedRecently((int)$send->id, 'open')) return false;
        $this->logEvent($send, 'open', '', $userAgent);
        $first = empty($send->opened_at);
        $this->db->where('id', (int)$send->id)->update('db_campaign_sends',
            array('open_count' => (int)$send->open_count + 1, 'opened_at' => $first ? date('Y-m-d H:i:s') : $send->opened_at));
        return true;
    }

    public function recordClick($send, $url, $userAgent) {
        if (!$send || !$this->ready()) return false;
        $this->logEvent($send, 'click', $url, $userAgent);
        $this->db->where('id', (int)$send->id)->update('db_campaign_sends',
            array('click_count' => (int)$send->click_count + 1, 'clicked_at' => empty($send->clicked_at) ? date('Y-m-d H:i:s') : $send->clicked_at));
        return true;
    }

    private function logEvent($send, $event, $url, $userAgent) {
        $this->load->library('communication_tracking');
        $url = substr(trim((string)$url), 0, 1000);
        $this->db->insert('db_communication_email_events', array(
            'store_id' => (int)$send->store_id, 'campaign_id' => (int)$send->campaign_id, 'send_id' => (int)$send->id,
            'event' => $event, 'url' => $url, 'link_hash' => $url !== '' ? Communication_tracking::linkHash($url) : '',
            'ua_client' => substr(Communication_tracking::clientName($userAgent), 0, 60), 'created_at' => date('Y-m-d H:i:s')));
    }

    private function countedRecently($sendId, $event) {
        $row = $this->db->select('id')->from('db_communication_email_events')
            ->where('send_id', $sendId)->where('event', $event)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - self::DEDUPE_MINUTES * 60))->limit(1)->get()->row_array();
        return !empty($row);
    }

    /** Headline figures for one campaign. Rates are measured against delivered mail. */
    public function summary($storeId, $campaignId) {
        $out = array('recipients' => 0, 'delivered' => 0, 'failed' => 0, 'waiting' => 0, 'suppressed' => 0,
            'unsubscribed' => 0, 'opened' => 0, 'open_hits' => 0, 'clicked' => 0, 'click_hits' => 0,
            'open_rate' => 0.0, 'click_rate' => 0.0, 'click_to_open' => 0.0, 'trackable' => $this->ready());
        if (!$this->ready() || !(int)$campaignId) return $out;
        $row = $this->db->select("COUNT(*) AS recipients,"
            . "SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) AS delivered,"
            . "SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) AS failed,"
            . "SUM(CASE WHEN status IN ('pending','sending') THEN 1 ELSE 0 END) AS waiting,"
            . "SUM(CASE WHEN status='suppressed' THEN 1 ELSE 0 END) AS suppressed,"
            . "SUM(CASE WHEN status='suppressed' AND error='unsubscribed' THEN 1 ELSE 0 END) AS unsubscribed,"
            . "SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) AS opened,"
            . "COALESCE(SUM(open_count),0) AS open_hits,"
            . "SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) AS clicked,"
            . "COALESCE(SUM(click_count),0) AS click_hits")->
            where('store_id', (int)$storeId)->where('campaign_id', (int)$campaignId)->get('db_campaign_sends')->row_array();
        foreach ($out as $key => $value) if (isset($row[$key])) $out[$key] = (int)$row[$key];
        $out['open_rate'] = $out['delivered'] > 0 ? round($out['opened'] * 100 / $out['delivered'], 1) : 0.0;
        $out['click_rate'] = $out['delivered'] > 0 ? round($out['clicked'] * 100 / $out['delivered'], 1) : 0.0;
        $out['click_to_open'] = $out['opened'] > 0 ? round($out['clicked'] * 100 / $out['opened'], 1) : 0.0;
        return $out;
    }

    /** Daily bars for the last few days, oldest first, with empty days filled in. */
    public function timeline($storeId, $campaignId, $days = 14) {
        $days = max(2, min(30, (int)$days));
        $rows = array();
        if ($this->ready()) {
            $since = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
            $found = $this->db->select("DATE(created_at) AS day,"
                . "SUM(CASE WHEN event='open' THEN 1 ELSE 0 END) AS opened,"
                . "SUM(CASE WHEN event='click' THEN 1 ELSE 0 END) AS clicked")
                ->from('db_communication_email_events')
                ->where('store_id', (int)$storeId)->where('campaign_id', (int)$campaignId)
                ->where('created_at >=', $since)->group_by('day', false)->order_by('day', 'asc')->limit($days)->get()->result_array();
            foreach ($found as $row) $rows[$row['day']] = array('opened' => (int)$row['opened'], 'clicked' => (int)$row['clicked']);
        }
        $out = array();
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $hit = isset($rows[$day]) ? $rows[$day] : array('opened' => 0, 'clicked' => 0);
            $out[] = array('day' => $day, 'label' => date('d M', strtotime($day)), 'opened' => $hit['opened'], 'clicked' => $hit['clicked']);
        }
        return $out;
    }

    /** Which links actually pulled readers through, busiest first. */
    public function links($storeId, $campaignId, $limit = 8) {
        if (!$this->ready()) return array();
        $rows = $this->db->select("MAX(url) AS url, COUNT(*) AS clicks, COUNT(DISTINCT send_id) AS people")
            ->where('store_id', (int)$storeId)->where('campaign_id', (int)$campaignId)->where('event', 'click')
            ->where('url <>', '')->group_by('link_hash')->order_by('clicks', 'desc')->limit((int)$limit)->get('db_communication_email_events')->result_array();
        foreach ($rows as $i => $row) $rows[$i]['label'] = $this->describe((string)$row['url']);
        return $rows;
    }

    /** One page of recipients with what each of them did. */
    public function recipients($storeId, $campaignId, $filter = 'all', $page = 1) {
        $out = array('rows' => array(), 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => self::PAGE, 'filter' => 'all');
        if (!$this->ready()) return $out;
        $filter = in_array($filter, self::FILTERS, true) ? $filter : 'all';
        $total = $this->scope($storeId, $campaignId, $filter)->count_all_results('db_campaign_sends');
        $pages = max(1, (int)ceil($total / self::PAGE));
        $page = min(max(1, (int)$page), $pages);
        $rows = $this->scope($storeId, $campaignId, $filter)->select('id,channel,recipient,customer_name,status,error,opened_at,open_count,clicked_at,click_count,sent_at')
            ->order_by('id', 'asc')->limit(self::PAGE, ($page - 1) * self::PAGE)->get('db_campaign_sends')->result_array();
        foreach ($rows as $i => $row) {
            $rows[$i]['display'] = $row['customer_name'] !== '' ? $row['customer_name'] : $row['recipient'];
            $rows[$i]['outcome'] = $this->outcome($row);
        }
        $out['rows'] = $rows; $out['total'] = $total; $out['page'] = $page; $out['pages'] = $pages; $out['filter'] = $filter;
        return $out;
    }

    /** A recipient filter, applied identically to the count and to the page. */
    private function scope($storeId, $campaignId, $filter) {
        $this->db->where('store_id', (int)$storeId)->where('campaign_id', (int)$campaignId);
        if ($filter === 'opened') $this->db->where('opened_at IS NOT NULL', null, false);
        elseif ($filter === 'clicked') $this->db->where('clicked_at IS NOT NULL', null, false);
        elseif ($filter === 'failed') $this->db->group_start()->where('status', 'failed')->or_where('status', 'suppressed')->group_end();
        elseif ($filter === 'waiting') $this->db->where_in('status', array('pending', 'sending'));
        return $this->db;
    }

    private function outcome(array $row) {
        if ($row['status'] === 'failed') return 'Not delivered';
        if ($row['status'] === 'suppressed') return 'Opted out';
        if ($row['status'] !== 'sent') return 'Waiting to send';
        // Only email carries a read receipt, so a text is reported as sent.
        if (($row['channel'] ?? '') !== 'email') return 'Sent';
        if (!empty($row['clicked_at'])) return 'Clicked a link';
        if (!empty($row['opened_at'])) return 'Opened';
        return 'Delivered, not opened';
    }

    /** A readable link label: the page they landed on, never a query string of tokens. */
    private function describe($url) {
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) return mb_substr($url, 0, 70);
        $path = rtrim((string)($parts['path'] ?? ''), '/');
        $label = str_replace('-', ' ', $path === '' ? '' : basename($path));
        return mb_substr($parts['host'] . ($label !== '' ? ' / ' . $label : ''), 0, 70);
    }
}
