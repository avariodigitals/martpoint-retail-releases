<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Presentation for system messages, never merchant names, content, URLs or identifiers. */
class Communication_copy {
    public static function message($value){
        $text=(string)$value;
        if(preg_match('/SQLSTATE|SQL syntax|mysqli|result_array|Call to (?:a member|undefined)|Uncaught|Stack trace|db_[a-z_]+|\bHMAC\b|\bmigration\b|\/application\/|\.php on line/i',$text))return 'This feature is temporarily unavailable. Please contact your Administrator.';
        $text=preg_replace('/\bMartPoint Central\b/i','your Administrator',$text);
        $text=preg_replace('/\bCentral\b/i','Administrator',$text);
        $text=preg_replace('/\bthis (?:Fleet|installation)\b/i','this account',$text);
        $text=preg_replace('/\byour Fleet\b/i','your account',$text);
        $text=preg_replace('/\bFleet account\b/i','Account',$text);
        return $text;
    }
}
