<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** Editorial content only. Orders, products, payments and CRM stay in their existing modules. */
class Storefront_content_model extends CI_Model {
    public function __construct(){
        parent::__construct();
        if(!$this->db->table_exists('db_storefront_posts')){
            $this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_posts (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,store_id INT NOT NULL,slug VARCHAR(180) NOT NULL,title VARCHAR(255) NOT NULL,excerpt TEXT NULL,body MEDIUMTEXT NOT NULL,image_url VARCHAR(1500) NULL,status VARCHAR(20) NOT NULL DEFAULT 'draft',published_at DATETIME NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,UNIQUE KEY store_slug(store_id,slug),KEY public_posts(store_id,status,published_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }
    public function defaults(){return ['contact_enabled'=>1,'contact_heading'=>'Let’s talk.','contact_intro'=>'Questions about a product, a service or your next project? Send our team a message.','blog_enabled'=>1,'blog_label'=>'Journal','blog_heading'=>'Ideas, stories & inspiration.','blog_intro'=>'Notes and useful advice from our team.','buy_now'=>1,'whatsapp_order'=>1,'product_sharing'=>1,'pickup_enabled'=>0,'pickup_ready_text'=>'','privacy_heading'=>'Privacy Policy','privacy_body'=>"Information you provide\nWhen you place an order, make a booking, sign in or send an enquiry, the store uses the information you provide to respond, process your request and support your purchases.\n\nOrders and services\nRelevant details may be shared with payment, delivery and service providers where needed to handle your request. MartPoint supplies the store software; the merchant manages its products, services and customer enquiries.\n\nCookies and local storage\nThe storefront uses browser storage for functions such as the shopping bag and sign-in. Where optional tracking is enabled, available tracking choices can be managed from the storefront.\n\nYour questions and requests\nContact this store using the details below for questions about your information, corrections, retention or marketing preferences."];}
    public function settings($storeId){
        $this->load->model('storefront_model');$s=$this->storefront_model->getSettings($storeId);$all=json_decode($s->theme_content_json ?? '',true);
        return array_merge($this->defaults(),is_array($all['shared_storefront'] ?? null)?array_intersect_key($all['shared_storefront'],$this->defaults()):[]);
    }
    public function save_settings($storeId,array $input){
        $values=$this->settings($storeId);
        foreach($this->defaults() as $key=>$default){
            if(is_int($default)){$values[$key]=empty($input[$key])?0:1;continue;}
            if(!array_key_exists($key,$input))continue;
            if(!is_string($input[$key]))throw new InvalidArgumentException('Enter text for '.str_replace('_',' ',$key));
            $value=trim($input[$key]);if(mb_strlen($value)>($key==='privacy_body'?30000:1500))throw new InvalidArgumentException('Please shorten '.str_replace('_',' ',$key));$values[$key]=$value===''&&$default!==''?$default:$value;
        }
        $s=$this->storefront_model->getSettings($storeId);$all=json_decode($s->theme_content_json ?? '',true);if(!is_array($all))$all=[];$all['shared_storefront']=$values;
        return $this->storefront_model->saveSettings($storeId,['theme_content_json'=>json_encode($all,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
    }
    public function posts($storeId,$public=false,$page=1,$kind=null){
        $this->db->where('store_id',(int)$storeId);
        if($public)$this->db->where('status','published')->where('published_at <=',gmdate('Y-m-d H:i:s'));
        if($kind!==null)$this->db->where('kind',$this->cleanKind($kind));
        return $this->db->order_by('published_at','desc')->order_by('id','desc')->limit($public?13:200,$public?(max(1,(int)$page)-1)*12:0)->get('db_storefront_posts')->result();
    }

    /** Entries for one public page (insight, project or application). */
    public function entries($storeId,$kind,$limit=12){
        if(!$this->db->field_exists('kind','db_storefront_posts')) return [];
        return $this->db->where('store_id',(int)$storeId)->where('kind',$this->cleanKind($kind))
            ->where('status','published')->where('published_at <=',gmdate('Y-m-d H:i:s'))
            ->order_by('published_at','desc')->order_by('id','desc')->limit(max(1,(int)$limit))
            ->get('db_storefront_posts')->result();
    }

    public function cleanKind($kind){
        return in_array($kind,['insight','project','application'],true) ? $kind : 'insight';
    }

    public function postMeta($post){
        $meta=json_decode((string)($post->meta_json ?? ''),true);
        return is_array($meta)?$meta:[];
    }

    public function post($storeId,$id,$public=false){
        $this->db->where('store_id',(int)$storeId)->where($public?'slug':'id',$id);
        if($public)$this->db->where('status','published')->where('published_at <=',gmdate('Y-m-d H:i:s'));
        return $this->db->get('db_storefront_posts')->row();
    }
    public function save_post($storeId,array $input,$image=null){
        $id=filter_var($input['id']??0,FILTER_VALIDATE_INT);if($id===false||$id<0)throw new InvalidArgumentException('Article not found.');if($id&&!$this->post($storeId,$id))throw new InvalidArgumentException('Article not found.');
        $row=[];foreach(['title','slug','excerpt','body','image_url','status'] as $key){if(isset($input[$key])&&!is_string($input[$key]))throw new InvalidArgumentException('Enter text for '.$key);$row[$key]=trim($input[$key]??'');}
        if($image!==null)$row['image_url']=$image;
        if(!$row['title']||!$row['body']||mb_strlen($row['title'])>255||mb_strlen($row['body'])>100000||mb_strlen($row['excerpt'])>3000)throw new InvalidArgumentException('Enter an article title and body within the allowed lengths.');
        $row['slug']=strtolower($row['slug']?:url_title($row['title'],'-',true));if(!preg_match('/^[a-z0-9][a-z0-9-]{0,179}$/D',$row['slug']))throw new InvalidArgumentException('Use a short slug containing letters, numbers and hyphens.');
        if(!in_array($row['status'],['draft','published','archived'],true))throw new InvalidArgumentException('Choose a valid article status.');
        if($row['image_url']&&(!filter_var($row['image_url'],FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($row['image_url'],PHP_URL_SCHEME)),['http','https'],true)||strlen($row['image_url'])>1500))throw new InvalidArgumentException('Enter an http or https image URL.');
        if($this->db->where('store_id',$storeId)->where('slug',$row['slug'])->where('id !=',$id)->count_all_results('db_storefront_posts'))throw new InvalidArgumentException('That article address is already used.');
        if($this->db->field_exists('kind','db_storefront_posts')){
            $row['kind']=$this->cleanKind((string)($input['kind']??'insight'));
            $meta=[];
            foreach(['location','sector','systems','year'] as $key){
                $value=trim((string)($input[$key]??''));
                if($value!=='')$meta[$key]=mb_substr($value,0,180);
            }
            $row['meta_json']=$meta?json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null;
        }
        if(isset($input['published_at'])&&!is_string($input['published_at']))throw new InvalidArgumentException('Choose a valid publishing date.');
        $date=trim((string)($input['published_at']??''));$store=$this->db->select('timezone')->where('id',(int)$storeId)->get('db_store')->row();$zone=in_array($store->timezone??'',DateTimeZone::listIdentifiers(),true)?$store->timezone:'UTC';$parsed=$date?DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$date,new DateTimeZone($zone)):null;
        if($date&&(!$parsed||$parsed->format('Y-m-d\TH:i')!==$date))throw new InvalidArgumentException('Choose a valid publishing date.');
        $row['published_at']=$parsed?$parsed->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'):gmdate('Y-m-d H:i:s');$row['updated_at']=gmdate('Y-m-d H:i:s');
        if($id){$ok=$this->db->where('store_id',$storeId)->where('id',$id)->update('db_storefront_posts',$row);}else{$row['store_id']=$storeId;$row['created_at']=$row['updated_at'];$ok=$this->db->insert('db_storefront_posts',$row);$id=(int)$this->db->insert_id();}
        if(!$ok)throw new RuntimeException('Could not save the article.');return $id;
    }
    public function submit_contact($storeId,array $input,array $files=[]){
        $v=[];foreach(['name','phone','email','subject','message','submission_ref','company','enquiry_type','project_location'] as $key){if(isset($input[$key])&&!is_string($input[$key]))throw new InvalidArgumentException('Please check your details.');$v[$key]=trim($input[$key]??'');}
        $b2b=$v['enquiry_type']!==''||!empty($input['consent']);
        if($b2b&&empty($input['consent']))throw new InvalidArgumentException('Please confirm that the store may contact you about this enquiry.');
        if(mb_strlen($v['company'])>180||mb_strlen($v['enquiry_type'])>60||mb_strlen($v['project_location'])>180)throw new InvalidArgumentException('Please shorten your company or project details.');
        if(!$v['name']||mb_strlen($v['name'])>150||(!$v['phone']&&!$v['email'])||strlen($v['phone'])>30||strlen($v['email'])>255||($v['email']&&!filter_var($v['email'],FILTER_VALIDATE_EMAIL))||!$v['message']||mb_strlen($v['message'])>4000||mb_strlen($v['subject'])>180||!preg_match('/^contact_[a-f0-9]{40}$/D',$v['submission_ref']))throw new InvalidArgumentException('Enter your name, a valid phone or email and your message (up to 4,000 characters).');
        $productId=(int)($input['item_id']??$input['product_id']??0);if($productId&&!$this->storefront_model->getOnlineProduct($productId,$storeId))throw new InvalidArgumentException('Product not found.');
        // A service request stays a lead, never an order. The id is kept in the
        // notes so the CRM can see which service page started the conversation.
        $serviceId=(int)($input['service_id']??0);
        if($serviceId&&!$this->storefront_model->getOnlineService($serviceId,$storeId))$serviceId=0;
        $attachments=[];$moved=[];
        $this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[(int)$storeId]);
            $prior=$this->db->where('store_id',$storeId)->where('submission_ref',$v['submission_ref'])->get('db_leads')->row();if($prior){$this->db->trans_commit();return $prior->id;}
            $bucket='contact:'.(int)$storeId.':'.substr(hash('sha256',$this->input->ip_address()),0,48);$hour=date('Y-m-d H:00:00');
            $this->db->query('INSERT INTO db_rate_limits(bucket,window_start,hits) VALUES(?,?,1) ON DUPLICATE KEY UPDATE hits=hits+1',[$bucket,$hour]);
            $limit=$this->db->where('bucket',$bucket)->where('window_start',$hour)->get('db_rate_limits')->row();if(!$limit||$limit->hits>15)throw new InvalidArgumentException('Too many messages. Please contact the store directly.');
            $attachments=$this->storeEnquiryFiles($storeId,$files,$moved);
            $this->load->model('leads_model');
            $lead=[
                'store_id'=>$storeId,'name'=>$v['name'],'phone'=>$v['phone']?:null,'email'=>$v['email']?:null,
                'source'=>'storefront','status'=>'new',
                'interest'=>$v['subject']?:($v['enquiry_type']?:'Contact enquiry'),
                'enquiry'=>$v['message'],
                'notes'=>json_encode([
                    'form'=>$b2b?'b2b_enquiry':'contact',
                    'product_id'=>$productId?:null,
                    'service_id'=>$serviceId?:null,
                    'company'=>$v['company']?:null,
                    'enquiry_type'=>$v['enquiry_type']?:null,
                    'project_location'=>$v['project_location']?:null,
                    'attachments'=>$attachments,
                ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                'submission_ref'=>$v['submission_ref'],'created_at'=>date('Y-m-d H:i:s')
            ];
            foreach(['company','enquiry_type','project_location'] as $column){
                if($v[$column]!==''&&$this->db->field_exists($column,'db_leads'))$lead[$column]=$v[$column];
            }
            if($productId&&$this->db->field_exists('item_id','db_leads'))$lead['item_id']=$productId;
            $id=$this->leads_model->saveLead($lead);
            if(!$id||!$this->db->trans_status())throw new RuntimeException('Could not send your message.');$this->db->trans_commit();return $id;
        }catch(Throwable $e){
            $this->db->trans_rollback();
            foreach($moved as $path){if(is_file(FCPATH.$path))@unlink(FCPATH.$path);}
            throw $e;
        }
    }

    /**
     * Validate and store enquiry attachments outside the public web root.
     * Returns metadata for the CRM; files are streamed back only through an
     * authenticated leads endpoint.
     */
    private function storeEnquiryFiles($storeId,array $files,array &$moved){
        $list=[];
        if(isset($files['name'])){
            if(is_array($files['name'])){
                foreach($files['name'] as $i=>$name){$list[]=['name'=>$name,'tmp_name'=>$files['tmp_name'][$i]??'','error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$i]??0];}
            }else{
                $list[]=['name'=>$files['name'],'tmp_name'=>$files['tmp_name']??'','error'=>$files['error']??UPLOAD_ERR_NO_FILE,'size'=>$files['size']??0];
            }
        }
        $allowed=[
            'pdf'=>['application/pdf'],
            'jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'png'=>['image/png'],'webp'=>['image/webp'],
            'dwg'=>['image/vnd.dwg','application/acad','application/octet-stream'],
            'dxf'=>['image/vnd.dxf','application/dxf','application/octet-stream'],
            'doc'=>['application/msword','application/octet-stream'],
            'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/octet-stream'],
        ];
        $dir='uploads/private/store-'.(int)$storeId.'/enquiries/';
        $root=mp_private_storage_root();
        $absoluteDir=rtrim($root,'/').'/'.$dir;
        if(!is_dir($absoluteDir)&&!mkdir($absoluteDir,0700,true))throw new InvalidArgumentException('Attachments are temporarily unavailable. Please contact the store directly.');
        $count=0;$total=0;$attachments=[];
        foreach($list as $file){
            if((int)$file['error']===UPLOAD_ERR_NO_FILE)continue;
            if(++$count>3)throw new InvalidArgumentException('Attach up to three documents.');
            if((int)$file['error']!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']))throw new InvalidArgumentException('One of the attachments could not be uploaded.');
            if($file['size']<=0||$file['size']>8*1024*1024)throw new InvalidArgumentException('Each attachment must be 8 MB or smaller.');
            $total+=(int)$file['size'];if($total>20*1024*1024)throw new InvalidArgumentException('Attachments must total 20 MB or less.');
            $ext=strtolower(pathinfo((string)$file['name'],PATHINFO_EXTENSION));
            if(!isset($allowed[$ext]))throw new InvalidArgumentException('Attachments must be PDF, Word, DWG, DXF, JPG, PNG or WebP.');
            $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
            if(!in_array($mime,$allowed[$ext],true))throw new InvalidArgumentException('The attachment "'.mb_substr((string)$file['name'],0,80).'" is not a valid '.strtoupper($ext).' file.');
            $safe=bin2hex(random_bytes(16)).'.'.$ext;
            if(!move_uploaded_file($file['tmp_name'],$absoluteDir.$safe))throw new InvalidArgumentException('One of the attachments could not be saved.');
            $path=$dir.$safe;$moved[]=$path;
            $attachments[]=['name'=>mb_substr(basename((string)$file['name']),0,180),'path'=>$path,'size'=>(int)$file['size'],'type'=>$ext];
        }
        return $attachments;
    }

    // ============== TECHNICAL SPECIFICATIONS & DOCUMENTS ==============

    public function productSpecs($itemId){
        if(!$this->db->field_exists('specs_json','db_items')) return [];
        $row=$this->db->select('specs_json')->where('id',(int)$itemId)->get('db_items')->row();
        $rows=json_decode((string)($row->specs_json ?? ''),true);
        return is_array($rows)?$rows:[];
    }

    public function saveProductSpecs($storeId,$itemId,array $rows){
        if(!$this->db->field_exists('specs_json','db_items')) throw new InvalidArgumentException('This store has not been updated to support specification tables yet.');
        $item=$this->db->where('id',(int)$itemId)->where('store_id',(int)$storeId)->get('db_items')->row();
        if(!$item) throw new InvalidArgumentException('Product not found.');
        $clean=[];
        foreach(array_slice($rows,0,40) as $row){
            if(!is_array($row)) continue;
            $label=trim((string)($row['label'] ?? ''));$value=trim((string)($row['value'] ?? ''));
            if($label===''&&$value==='') continue;
            if(mb_strlen($label)>120||mb_strlen($value)>500) throw new InvalidArgumentException('Keep each specification label under 120 and each value under 500 characters.');
            if($label==='') throw new InvalidArgumentException('Every specification value needs a label.');
            $clean[]=['label'=>$label,'value'=>$value];
        }
        return $this->db->where('id',(int)$itemId)->where('store_id',(int)$storeId)->update('db_items',['specs_json'=>$clean?json_encode($clean,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null]);
    }

    /**
     * Store a technical document outside the public web root. Public files are
     * streamed through Storefront::document(); private ones only through the
     * signed-in admin. The original filename is kept for display, never used
     * as the path.
     */
    public function storeDocumentFile($storeId,$itemId,array $file){
        $allowed=[
            'pdf'=>['application/pdf'],
            'jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'png'=>['image/png'],'webp'=>['image/webp'],
            'dwg'=>['image/vnd.dwg','application/acad','application/octet-stream'],
            'dxf'=>['image/vnd.dxf','application/dxf','application/octet-stream'],
            'doc'=>['application/msword','application/octet-stream'],
            'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/octet-stream'],
        ];
        if((int)($file['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||empty($file['tmp_name'])||!is_uploaded_file($file['tmp_name'])) throw new InvalidArgumentException('The document could not be uploaded.');
        if((int)($file['size'] ?? 0)<=0||(int)$file['size']>12*1024*1024) throw new InvalidArgumentException('Documents must be 12 MB or smaller.');
        $ext=strtolower(pathinfo((string)($file['name'] ?? ''),PATHINFO_EXTENSION));
        if(!isset($allowed[$ext])) throw new InvalidArgumentException('Documents must be PDF, Word, DWG, DXF, JPG, PNG or WebP.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
        if(!in_array($mime,$allowed[$ext],true)) throw new InvalidArgumentException('That file is not a valid '.strtoupper($ext).' document.');
        $dir='uploads/private/store-'.(int)$storeId.'/documents/'.(int)$itemId.'/';
        $root=mp_private_storage_root();
        $absoluteDir=rtrim($root,'/').'/'.$dir;
        if(!is_dir($absoluteDir)&&!mkdir($absoluteDir,0700,true)) throw new InvalidArgumentException('Documents are temporarily unavailable.');
        $safe=bin2hex(random_bytes(16)).'.'.$ext;
        if(!move_uploaded_file($file['tmp_name'],$absoluteDir.$safe)) throw new InvalidArgumentException('The document could not be saved.');
        return ['path'=>$dir.$safe,'name'=>mb_substr(basename((string)$file['name']),0,180),'size'=>(int)$file['size']];
    }

    public function documents($storeId,$itemId,$publicOnly=true){
        if(!$this->db->table_exists('db_storefront_documents')) return [];
        $this->db->where('store_id',(int)$storeId)->where('item_id',(int)$itemId);
        if($publicOnly)$this->db->where('visibility','public');
        return $this->db->order_by('sort_order','asc')->order_by('id','asc')->get('db_storefront_documents')->result();
    }

    public function document($storeId,$itemId,$id,$publicOnly=true){
        if(!$this->db->table_exists('db_storefront_documents')) return null;
        $this->db->where('id',(int)$id)->where('store_id',(int)$storeId)->where('item_id',(int)$itemId);
        if($publicOnly)$this->db->where('visibility','public');
        return $this->db->get('db_storefront_documents')->row();
    }

    public function saveDocument($storeId,$itemId,array $input,$uploaded=null){
        if(!$this->db->table_exists('db_storefront_documents')) throw new InvalidArgumentException('This store has not been updated to support documents yet.');
        $item=$this->db->where('id',(int)$itemId)->where('store_id',(int)$storeId)->get('db_items')->row();
        if(!$item) throw new InvalidArgumentException('Product not found.');
        $id=(int)($input['id'] ?? 0);
        $title=trim((string)($input['title'] ?? ''));$type=trim((string)($input['doc_type'] ?? 'datasheet'));
        $visibility=(($input['visibility'] ?? '')==='public')?'public':'private';
        if($title===''||mb_strlen($title)>180) throw new InvalidArgumentException('Enter a document title up to 180 characters.');
        if(!in_array($type,['datasheet','certificate','drawing','manual','brochure','other'],true))$type='other';
        $row=['store_id'=>(int)$storeId,'item_id'=>(int)$itemId,'title'=>$title,'doc_type'=>$type,'visibility'=>$visibility,'sort_order'=>(int)($input['sort_order'] ?? 0)];
        if($uploaded){
            $row['file_path']=$uploaded['path'];$row['file_name']=$uploaded['name'];$row['file_size']=(int)$uploaded['size'];
        }elseif(!$id){
            throw new InvalidArgumentException('Choose a file to upload.');
        }
        if($id){
            $existing=$this->document($storeId,$itemId,$id,false);
            if(!$existing) throw new InvalidArgumentException('Document not found.');
            $ok=$this->db->where('id',$id)->where('store_id',(int)$storeId)->update('db_storefront_documents',$row);
            if($ok&&$uploaded&&!empty($existing->file_path)&&$existing->file_path!==$uploaded['path']&&is_file(FCPATH.$existing->file_path))@unlink(FCPATH.$existing->file_path);
            return $ok;
        }
        $row['created_at']=date('Y-m-d H:i:s');
        return $this->db->insert('db_storefront_documents',$row);
    }

    public function deleteDocument($storeId,$itemId,$id){
        if(!$this->db->table_exists('db_storefront_documents')) return false;
        $doc=$this->document($storeId,$itemId,$id,false);
        if(!$doc) return false;
        $this->db->where('id',(int)$id)->where('store_id',(int)$storeId)->delete('db_storefront_documents');
        if(!empty($doc->file_path)&&is_file(FCPATH.$doc->file_path))@unlink(FCPATH.$doc->file_path);
        return true;
    }
}
