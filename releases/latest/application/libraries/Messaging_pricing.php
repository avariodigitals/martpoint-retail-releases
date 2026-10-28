<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** All monetary calculations use integer kobo. No provider credentials live here. */
class Messaging_pricing {
    const VERSION = '2026-10-27';

    public static function month() {
        return (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('Y-m');
    }

    public static function sms($text) {
        if (!is_string($text) || $text === '' || !preg_match('//u', $text)) {
            throw new InvalidArgumentException('Enter a valid SMS message.');
        }
        $basic = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
        $extended = "\f^{}\\[~]|€";
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $units = 0; $gsm = true; $widths = array();
        foreach ($chars as $char) {
            if (strpos($basic, $char) !== false) { $units++; $widths[]=1; }
            elseif (strpos($extended, $char) !== false) { $units += 2; $widths[]=2; }
            else { $gsm = false; break; }
        }
        if (!$gsm) {
            $widths=array(); $units=0;
            foreach($chars as $char) { $width=(int)(strlen(mb_convert_encoding($char,'UTF-16BE','UTF-8'))/2); $widths[]=$width; $units+=$width; }
        }
        $single = $gsm ? 160 : 70; $multi = $gsm ? 153 : 67;
        $segments=1;
        if($units>$single){
            $used=0;
            foreach($widths as $width){ if($used+$width>$multi){$segments++;$used=0;} $used+=$width; }
        }
        return array('encoding' => $gsm ? 'GSM-7' : 'UCS-2', 'units' => $units,
            'segments' => $segments);
    }

    public static function emailCost($category, $already, $recipients) {
        if (!in_array($category, array('transactional', 'promotional'), true)) throw new InvalidArgumentException('Invalid email category.');
        $already = max(0, (int)$already); $remaining = max(0, (int)$recipients); $cost = 0;
        $rates = $category === 'transactional' ? array(100, 85, 70) : array(200, 175, 150);
        foreach (array(10000, 50000, PHP_INT_MAX) as $band => $ceiling) {
            $qty = min($remaining, max(0, $ceiling - $already));
            $cost += $qty * $rates[$band]; $already += $qty; $remaining -= $qty;
            if (!$remaining) break;
        }
        return $cost;
    }

    public static function category(array $options) {
        $template = (string)($options['template_key'] ?? '');
        if (in_array($template, array('campaign', 'cart_recovery', 'back_in_stock', 'legacy_email', 'legacy_send_email'), true)
            || ($options['related_module'] ?? '') === 'campaign') return 'promotional';
        return ($options['message_category'] ?? '') === 'promotional' ? 'promotional' : 'transactional';
    }

    public static function platformTemplate($key) {
        return in_array($key, array('password_reset_otp', 'staff_invite', 'license_otp',
            'subscription_activated', 'subscription_renewed', 'subscription_expiring', 'subscription_renewal_reminder',
            'subscription_expired', 'subscription_suspended', 'subscription_resumed', 'support_request'), true);
    }

    public static function catalogue() {
        return array('currency' => 'NGN', 'pricing_version' => self::VERSION,
            'sms_segment_kobo' => 800, 'email_transactional_kobo' => array(100,85,70),
            'email_promotional_kobo' => array(200,175,150), 'email_bands' => array(10000,50000),
            'suggested_topups_naira' => array(5000,10000,25000,50000),
            'addons' => array(
                'followup_starter' => array('name'=>'Automatic follow-ups — Starter','monthly'=>6000,'annual'=>60000,'active_flows'=>10,'monthly_starts'=>5000,'available'=>false),
                'followup_growth' => array('name'=>'Automatic follow-ups — Growth','monthly'=>12000,'annual'=>120000,'active_flows'=>25,'monthly_starts'=>20000,'available'=>false),
                'followup_business' => array('name'=>'Automatic follow-ups — Business','monthly'=>25000,'annual'=>250000,'active_flows'=>75,'monthly_starts'=>75000,'available'=>false),
                'social_pair' => array('name'=>'Instagram business account + Facebook Page','monthly'=>3000,'annual'=>30000,'available'=>false),
                'social_extra_pair' => array('name'=>'Additional Instagram + Facebook pair','monthly'=>2000,'annual'=>20000,'available'=>false),
                'followup_setup' => array('name'=>'Set up up to five standard follow-ups','once'=>25000,'available'=>false),
                'social_setup' => array('name'=>'Connect and test one social account pair','once'=>15000,'available'=>false)),
            'whatsapp' => array('order_update_kobo'=>2500,'verification_kobo'=>2500,'promotion_kobo'=>12000,'available'=>false));
    }

    public static function signature($action, $timestamp, $url, $payload, $key) {
        return hash_hmac('sha256', $action."\n".$timestamp."\n".$url."\n".$payload, $key);
    }

    public static function money($naira) {
        if (!preg_match('/^\d{1,7}(?:\.\d{1,2})?$/', (string)$naira)) throw new InvalidArgumentException('Enter a valid naira amount with at most two decimal places.');
        $parts = explode('.', (string)$naira);
        return (int)$parts[0] * 100 + (int)str_pad($parts[1] ?? '', 2, '0');
    }
}
