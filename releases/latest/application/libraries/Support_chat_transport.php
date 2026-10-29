<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** PHP 7.4-compatible server-to-server chat transport. No credentials in logs. */
class Support_chat_transport {
    public static function baseUrl($configured){
        $base = rtrim(trim((string)$configured), '/');
        if($base === '') return 'https://www.martpoint.com.ng';
        $parts = parse_url($base);
        if($parts && in_array(strtolower($parts['host'] ?? ''), ['martpoint.com.ng','www.martpoint.com.ng'], true)
            && (!isset($parts['port']) || in_array((int)$parts['port'], [80,443], true))){
            return 'https://www.martpoint.com.ng' . rtrim($parts['path'] ?? '', '/');
        }
        return $base;
    }

    private static function failure($status, $code, $message){
        return ['status'=>$status,'body'=>json_encode(['success'=>false,'code'=>$code,'error'=>$message])];
    }

    public static function decodeResponse($raw, $status, $errno = 0){
        if($errno || $raw === false){
            return self::failure($errno === 28 ? 504 : 502, $errno === 28 ? 'bridge_timeout' : 'bridge_unreachable',
                'Support is taking longer than expected. Your message may not have been sent. Please check the conversation before trying again.');
        }
        // Do not forward shared bridge secrets or merchant identity to an
        // arbitrary redirect destination. Canonicalise the known host above.
        if($status >= 300 && $status < 400){
            return self::failure(502, 'bridge_redirect', 'The support connection needs an update. Please contact MartPoint Support.');
        }
        $data = json_decode((string)$raw, true);
        if($status < 200 || $status > 599 || !is_array($data) || !isset($data['success']) || !is_bool($data['success'])){
            return self::failure(502, 'bridge_invalid_response', 'We could not load support chat. Please try again shortly.');
        }
        if($status >= 400 && $data['success']){
            return self::failure(502, 'bridge_invalid_response', 'We could not load support chat. Please try again shortly.');
        }
        return ['status'=>$status,'body'=>json_encode($data)];
    }

    public static function request($url, array $headers, $body = null, $timeout = 30){
        if(!function_exists('curl_init')) return self::failure(502, 'bridge_unavailable', 'The support connection is not configured on this server.');
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>$headers,
            CURLOPT_CONNECTTIMEOUT=>8, CURLOPT_TIMEOUT=>$timeout, CURLOPT_FOLLOWLOCATION=>false]);
        if($body !== null){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);
        $response = self::decodeResponse($raw, $status, $errno);
        if($response['status'] >= 400 && function_exists('log_message')){
            // Status and errno only: never log tokens, headers or chat text.
            log_message('error', 'Support chat bridge failed (HTTP '.$status.', curl '.$errno.').');
        }
        return $response;
    }
}
