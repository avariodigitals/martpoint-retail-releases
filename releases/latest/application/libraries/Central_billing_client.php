<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Authenticated Central-to-Admin billing calls. No credentials are stored in source. */
class Central_billing_client {
    private $base;
    private $secret;
    public function __construct() {
        $this->base = rtrim((string)getenv('CENTRAL_BILLING_API_URL'), '/');
        $this->secret = (string)getenv('CENTRAL_BILLING_API_SECRET');
    }
    public function configuration_error() {
        $issues = array();
        $scheme = $this->base !== '' ? parse_url($this->base, PHP_URL_SCHEME) : false;
        $host = $this->base !== '' ? parse_url($this->base, PHP_URL_HOST) : false;
        if ($this->base === '') $issues[] = 'CENTRAL_BILLING_API_URL is empty in this PHP request';
        elseif ($scheme !== 'https' || !$host) $issues[] = 'CENTRAL_BILLING_API_URL must be a valid HTTPS base URL';
        if (strlen($this->secret) < 32) $issues[] = 'CENTRAL_BILLING_API_SECRET is missing or shorter than 32 characters in this PHP request';
        if (!function_exists('curl_init')) $issues[] = 'PHP cURL is unavailable';
        return $issues ? implode('; ', $issues) : '';
    }
    public function ready() { return $this->configuration_error() === ''; }
    public function post($path, array $payload) {
        if (!$this->ready()) throw new RuntimeException('Central billing is not configured.');
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) throw new RuntimeException('Could not encode Central billing request.');
        $time = (string)time();
        $requestPath='/api/internal/central-billing/'.ltrim($path,'/');
        $signature = hash_hmac('sha256', $time."\n".$requestPath."\n".$body, $this->secret);
        $ch = curl_init($this->base.$requestPath);
        curl_setopt_array($ch, array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>array('Content-Type: application/json',
            'X-Central-Timestamp: '.$time,'X-Central-Signature: '.$signature)));
        $raw = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
        if ($raw === false) throw new RuntimeException('Could not reach Next Admin billing API over HTTPS'.($err ? ': '.$err : '.'));
        if ($status < 200 || $status >= 300) {
            $response = json_decode($raw, true);
            $detail = is_array($response) ? ($response['error'] ?? $response['message'] ?? '') : '';
            $detail = is_string($detail) ? trim(strip_tags($detail)) : '';
            throw new RuntimeException('Next Admin billing API returned HTTP '.$status.($detail !== '' ? ': '.mb_substr($detail, 0, 240) : '.'));
        }
        $result = json_decode($raw, true);
        if (!is_array($result) || empty($result['success'])) throw new RuntimeException('Central billing response could not be confirmed.');
        return $result;
    }
}
