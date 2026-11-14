<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Communication_contacts {
    public function parse($raw,$channel){
        if(!in_array($channel,array('email','sms'),true))throw new InvalidArgumentException('Choose SMS or email.');
        if(strlen($raw)>1048576)throw new InvalidArgumentException('Import at most 1 MB at a time.');
        require_once APPPATH.'libraries/Communication_audience.php';
        $raw=preg_replace('/^\xEF\xBB\xBF/','',trim($raw));$contacts=array();$invalid=0;
        if(stripos($raw,'BEGIN:VCARD')===0)$rows=$this->vcards($raw);
        else{
            $stream=fopen('php://temp','r+');fwrite($stream,$raw);rewind($stream);$rows=array();$headers=null;
            while(($row=fgetcsv($stream))!==false){
                if(count($row)===1 && trim((string)$row[0])==='')continue;
                $row=array_map('trim',$row);
                if(!$rows && $headers===null){$lower=array_map('strtolower',$row);if(array_intersect($lower,array('email','phone','mobile','name','given name','phone 1 - value'))){$headers=array_flip($lower);continue;}}
                if($headers!==null)$rows[]=array('name'=>$row[$headers['name']??$headers['given name']??-1]??'','email'=>$row[$headers['email']??$headers['e-mail 1 - value']??-1]??'','phone'=>$row[$headers['phone']??$headers['mobile']??$headers['phone 1 - value']??-1]??'');
                else{$value=$row[count($row)-1];$rows[]=array('name'=>count($row)>1?$row[0]:'','email'=>$channel==='email'?$value:'','phone'=>$channel==='sms'?$value:'');}
                if(count($rows)>10000){fclose($stream);throw new InvalidArgumentException('Import at most 10,000 contacts at a time.');}
            }fclose($stream);
        }
        foreach($rows as $row){
            $email=Communication_audience::recipient('email',$row['email']);$phone=Communication_audience::recipient('sms',$row['phone']);$key=$channel==='email'?$email:$phone;
            if(!$key){$invalid++;continue;}
            $contacts[$key]=array('name'=>mb_substr($row['name'],0,190),'email'=>$email?:'','phone'=>$phone?:'');
        }
        if(!$contacts)throw new InvalidArgumentException('No valid contacts found. Upload CSV/vCard or paste one recipient per line.');
        return array('contacts'=>array_values($contacts),'invalid'=>$invalid);
    }
    private function vcards($raw){
        $lines=preg_split('/\r\n|\n|\r/',$raw);$unfolded=array();
        foreach($lines as $line){$last=count($unfolded)-1;if($last>=0 && preg_match('/^[ \t]/',$line))$unfolded[$last].=substr($line,1);elseif($last>=0 && stripos($unfolded[$last],'ENCODING=QUOTED-PRINTABLE')!==false && substr($unfolded[$last],-1)==='=')$unfolded[$last]=substr($unfolded[$last],0,-1).$line;else $unfolded[]=$line;}
        $rows=array();$card=null;
        foreach($unfolded as $line){
            if(strcasecmp($line,'BEGIN:VCARD')===0){$card=array('name'=>'','email'=>array(),'phone'=>array());continue;}
            if(strcasecmp($line,'END:VCARD')===0 && $card!==null){
                foreach($card['phone']?:array('') as $phone)$rows[]=array('name'=>$card['name'],'email'=>$card['email'][0]??'','phone'=>$phone);
                foreach(array_slice($card['email'],1) as $email)$rows[]=array('name'=>$card['name'],'email'=>$email,'phone'=>'');
                if(count($rows)>10000)throw new InvalidArgumentException('Import at most 10,000 contact details at a time.');$card=null;continue;
            }
            if($card===null || strpos($line,':')===false)continue;
            list($meta,$value)=explode(':',$line,2);$key=strtoupper(preg_replace('/^.*\./','',explode(';',$meta)[0]));
            if(stripos($meta,'ENCODING=QUOTED-PRINTABLE')!==false)$value=quoted_printable_decode($value);
            if(preg_match('/CHARSET=([^;:]+)/i',$meta,$match) && in_array(strtoupper($match[1]),array('ISO-8859-1','WINDOWS-1252'),true))$value=mb_convert_encoding($value,'UTF-8',$match[1]);
            $value=str_replace(array('\\n','\\N','\\,','\\;','\\\\'),array(' ',' ',',',';','\\'),$value);
            if($key==='FN')$card['name']=$value;
            elseif($key==='N' && !$card['name'])$card['name']=trim(implode(' ',array_reverse(array_slice(explode(';',$value),0,2))));
            elseif($key==='TEL')$card['phone'][]=preg_replace('/^tel:/i','',$value);
            elseif($key==='EMAIL')$card['email'][]=$value;
        }
        if($card!==null)throw new InvalidArgumentException('The vCard file is incomplete. Export it again from Contacts.');return $rows;
    }
}
