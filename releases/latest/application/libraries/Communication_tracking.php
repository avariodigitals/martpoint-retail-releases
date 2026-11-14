<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Campaign engagement links: the open pixel and the click redirect.
 *
 * Every staged campaign email row already carries a private per-recipient token
 * (unsub_token). It is reused here as the capability that identifies the
 * recipient, and as the key that binds a click redirect to the exact link that
 * was delivered, so our own domain can never be turned into an open redirect.
 */
class Communication_tracking {
    const MAX_URL = 900;          // longer links stay direct rather than overflow the redirect
    const TOKEN = '/^[a-f0-9]{32,64}$/D';
    const SIGNATURE = '/^[a-f0-9]{40}$/D';
    /** Image prefetchers and link scanners are not people reading mail. */
    const NOISE = '/bot|crawler|spider|scan|safelinks?|predproxy|proxy\.cache|googleimageproxy|yahoo!\sscreensproxy|outlooksecure|microsoft\surl\s|linkpreview|slurp|curl|wget|python|java\/|okhttp|phantomjs|headless/i';

    /** The per-recipient token, or '' when the row cannot be tracked. */
    public static function token($send) {
        $token = strtolower(trim((string)(is_object($send) ? ($send->unsub_token ?? '') : ($send['unsub_token'] ?? ''))));
        return preg_match(self::TOKEN, $token) ? $token : '';
    }

    public static function openUrl($token) { return base_url('storefront/campaign_open/' . $token); }

    /** Signed redirect: the token identifies the reader, the signature the exact link. */
    public static function clickUrl($token, $url) {
        $url = trim((string)$url);
        if (!self::trackable($token, $url)) return $url;
        return base_url('storefront/campaign_click/' . $token . '/' . self::signature($token, $url) . '/' . self::encode($url));
    }

    public static function signature($token, $url) { return substr(hash_hmac('sha256', 'click|' . self::normalize($url), $token), 0, 40); }

    /** A delivered link can only be replayed through the same recipient token it was wrapped with. */
    public static function verifyClick($token, $encoded, $signature) {
        $url = self::decode($encoded);
        if (!self::trackable($token, $url) || !preg_match(self::SIGNATURE, (string)$signature)) return '';
        return hash_equals(self::signature($token, $url), strtolower((string)$signature)) ? $url : '';
    }

    /** http(s) links of sane length only; our own tracking and unsubscribe links are never re-wrapped. */
    public static function trackable($token, $url) {
        $url = trim((string)$url);
        if (!preg_match(self::TOKEN, (string)$token) || $url === '' || strlen($url) > self::MAX_URL) return false;
        if (!preg_match('#^https?://[^\s/<>@]+[^\s<>"\']*$#i', $url)) return false;
        if (stripos($url, 'campaign_open/') !== false || stripos($url, 'campaign_click/') !== false || stripos($url, 'campaign_unsubscribe/') !== false) return false;
        return true;
    }

    public static function encode($url) { return rtrim(strtr(base64_encode((string)$url), '+/', '-_'), '='); }

    public static function decode($encoded) {
        $encoded = (string)$encoded;
        if (!preg_match('#^[A-Za-z0-9_\-]{1,1600}$#D', $encoded)) return '';
        $url = base64_decode(strtr($encoded, '-_', '+/') . str_repeat('=', (4 - strlen($encoded) % 4) % 4), true);
        return $url === false ? '' : $url;
    }

    /** Trailing tracking junk the recipient already carries is stripped before counting a link. */
    public static function normalize($url) {
        $url = trim((string)$url);
        $url = preg_replace('/[?&](utm_[a-z]+|mc_[a-z]+|ref|fbclid|gclid|yclid|msclkid)=[^&#]*/i', '', $url);
        return strtolower(rtrim((string)$url, '?&'));
    }

    public static function linkHash($url) { return substr(md5(self::normalize($url)), 0, 32); }

    /** Mail scanners and image prefetchers must not be counted as readers. */
    public static function isAutomation($userAgent) {
        $userAgent = (string)$userAgent;
        return $userAgent === '' || preg_match(self::NOISE, $userAgent);
    }

    /** A short, human label for the report table. */
    public static function clientName($userAgent) {
        if (self::isAutomation($userAgent)) return 'Automatic check';
        $agent = (string)$userAgent;
        foreach (array('Gmail' => 'Gmail', 'YahooMail|yahoo.*mail' => 'Yahoo mail', 'Outlook|MSIPOCE|OfficeLync' => 'Outlook',
            'Apple Mail|CFNetwork' => 'Apple Mail', 'Thunderbird' => 'Thunderbird', 'Airmail' => 'Airmail',
            'iPhone|iPad' => 'iPhone or iPad', 'Android' => 'Android', 'Edg' => 'Edge', 'Chrome' => 'Chrome',
            'Firefox' => 'Firefox', 'Safari' => 'Safari') as $pattern => $label) {
            if (preg_match('/' . $pattern . '/i', $agent)) return $label;
        }
        return 'Email app';
    }

    public static function pixel($token) {
        return '<img src="' . htmlspecialchars(self::openUrl($token), ENT_QUOTES, 'UTF-8') . '" width="1" height="1" alt="" '
            . 'style="display:block;border:0;height:1px;width:1px;max-width:0;color:transparent" aria-hidden="true">';
    }

    /**
     * Rewrite delivered HTML: wrap campaign links and add the open pixel. Text parts are
     * left untouched on purpose, so billing (which counts text pages) never changes.
     */
    public static function wrap($html, $token) {
        $html = (string)$html;
        if ($html === '' || !preg_match(self::TOKEN, (string)$token)) return $html;
        $html = preg_replace_callback('/(<a\b[^>]*?\shref=["\'])(https?:\/\/[^"\']+)(["\'])/i',
            function ($match) use ($token) { return $match[1] . htmlspecialchars(self::clickUrl($token, html_entity_decode($match[2], ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8') . $match[3]; },
            $html);
        $pixel = self::pixel($token);
        $body = preg_match('/<\/body>/i', $html);
        return $body ? preg_replace('/<\/body>/i', $pixel . '</body>', $html, 1) : $html . $pixel;
    }
}
