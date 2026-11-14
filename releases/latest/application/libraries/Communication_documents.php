<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Evidence stays in Central storage; no public upload URL is created. */
class Communication_documents {
    const MAX_BYTES=1048576;
    public static function validate(array $p){
        $encoded=$p['data']??'';
        if(!is_string($encoded) || strlen($encoded)>1398104)throw new InvalidArgumentException('Each document must be at most 1 MB.');
        $bytes=base64_decode($encoded,true);
        if($bytes===false || $bytes==='' || strlen($bytes)>self::MAX_BYTES)throw new InvalidArgumentException('Upload a PDF, PNG or JPEG document up to 1 MB.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);$extensions=array('application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg');
        if(!isset($extensions[$mime]) || ($mime==='application/pdf' && strpos($bytes,'%PDF-')!==0) || ($mime!=='application/pdf' && @getimagesizefromstring($bytes)===false))throw new InvalidArgumentException('Upload a genuine PDF, PNG or JPEG document.');
        $name=preg_replace('/[^a-zA-Z0-9._ -]/','_',basename((string)($p['name']??'document')));$name=mb_substr(pathinfo($name,PATHINFO_FILENAME),0,170).'.'.$extensions[$mime];
        return array('filename'=>$name,'mime'=>$mime,'sha256'=>hash('sha256',$bytes),'content'=>$bytes);
    }
    public static function upload($field){
        $f=$_FILES[$field]??null;
        if(!$f || $f['error']!==UPLOAD_ERR_OK || $f['size']>self::MAX_BYTES || !is_uploaded_file($f['tmp_name']))throw new InvalidArgumentException('Upload '.$field.' as a PDF, PNG or JPEG up to 1 MB.');
        $p=array('name'=>$f['name'],'data'=>base64_encode(file_get_contents($f['tmp_name'])));self::validate($p);return $p;
    }
    public function store($account,$kind,$reference,$slot,array $document){
        $ci =& get_instance();$data=self::validate($document);
        $old=$ci->db->where('account_id',$account)->where('kind',$kind)->where('reference_id',$reference)->where('slot',$slot)->get('db_communication_documents')->row_array();
        if($old){if(!hash_equals($old['sha256'],$data['sha256']))throw new RuntimeException('This request already has different evidence. Create a new request after Administrator resolves it.');return (int)$old['id'];}
        $data+=array('account_id'=>$account,'kind'=>$kind,'reference_id'=>$reference,'slot'=>$slot,'created_at'=>gmdate('Y-m-d H:i:s'));
        if(!$ci->db->insert('db_communication_documents',$data))throw new RuntimeException('Document could not be saved.');return (int)$ci->db->insert_id();
    }
    public function get($account,$id){$ci =& get_instance();$r=$ci->db->where('account_id',$account)->where('id',(int)$id)->get('db_communication_documents')->row_array();if(!$r)throw new RuntimeException('Document not found for this account.');return $r;}
    public static function download(array $r){$ci =& get_instance();$ci->output->set_content_type($r['mime'])->set_header('Cache-Control: private, no-store')->set_header('X-Content-Type-Options: nosniff')->set_header("Content-Security-Policy: sandbox")->set_header('Content-Disposition: attachment; filename="'.$r['filename'].'"')->set_output($r['content']);}
}
