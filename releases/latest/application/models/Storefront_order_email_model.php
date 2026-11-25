<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Order-only recovery for failed storefront owner/customer notifications. */
class Storefront_order_email_model extends CI_Model {
	private $orderTemplates = array('online_order_owner','online_order_customer');

	private function isCreditFailure($row) {
		$error = strtolower((string)($row->error_message ?? ''));
		if (strpos($error, 'accepted before this pause') !== false) return false;
		return (strpos($error, 'balance is too low') !== false)
			|| (strpos($error, 'balance') !== false && (strpos($error, 'low') !== false || strpos($error, 'empty') !== false))
			|| (strpos($error, 'insufficient') !== false && strpos($error, 'credit') !== false)
			|| (strpos($error, 'credit') !== false && (strpos($error, 'low') !== false || strpos($error, 'empty') !== false || strpos($error, 'paused') !== false));
	}

	public function recoverableFailures($storeId, $limit = 25) {
		if (!$this->db->table_exists('db_email_logs')) return array();
		$rows = $this->db->where('store_id',(int)$storeId)->where('status','failed')
			->where('related_module','storefront')->where_in('email_type',$this->orderTemplates)
			->order_by('id','ASC')->limit(max(1,min(500,(int)$limit*20)))->get('db_email_logs')->result();
		return array_values(array_filter($rows,function($row){return $this->isCreditFailure($row);}));
	}

	public function recoverableFailureCount($storeId) {
		if (!$this->db->table_exists('db_email_logs')) return 0;
		$rows=$this->db->where('store_id',(int)$storeId)->where('status','failed')
			->where('related_module','storefront')->where_in('email_type',$this->orderTemplates)
			->get('db_email_logs')->result();
		$total=0; foreach($rows as $row) if($this->isCreditFailure($row))$total++;
		return $total;
	}

	public function retryFailedLog($logId, $storeId) {
		if (!$this->db->table_exists('db_email_logs')) return array('success'=>false,'message'=>'Email history is unavailable.');
		$log=$this->db->where('id',(int)$logId)->where('store_id',(int)$storeId)->where('status','failed')
			->where('related_module','storefront')->where_in('email_type',$this->orderTemplates)->get('db_email_logs')->row();
		if (!$log || !$this->isCreditFailure($log)) return array('success'=>false,'message'=>'Only order emails that failed because email credit was unavailable can be resent here.');
		if (!ctype_digit((string)$log->related_record_id)) return array('success'=>false,'message'=>'This email is not linked to a valid order.');

		$already=$this->db->where('store_id',(int)$storeId)->where('related_module','storefront')
			->where('related_record_id',(string)$log->related_record_id)->where('email_type',$log->email_type)
			->where('status','sent')->count_all_results('db_email_logs');
		if ($already) {
			$this->db->where('id',(int)$log->id)->update('db_email_logs',array('status'=>'failed','error_message'=>'Skipped because a successful send already exists for this order notification.'));
			return array('success'=>true,'message'=>'Skipped; this order notification already has a successful send record.');
		}

		$this->db->where('id',(int)$log->id)->where('status','failed')->update('db_email_logs',array('status'=>'retrying'));
		if ($this->db->affected_rows()!==1) return array('success'=>false,'message'=>'This email is already being processed or has changed. Refresh the logs and try again.');
		try {
			$this->load->model('storefront_model');
			$order=$this->storefront_model->getOrder((int)$log->related_record_id);
			if (!$order || (int)$order->store_id!==(int)$storeId) throw new RuntimeException('The linked order was not found for this store.');
			$this->load->model('email_settings_model');
			if (!$this->email_settings_model->isReady((int)$storeId)) throw new RuntimeException('The email provider is not configured for this store.');
			$items=$this->storefront_model->getOrderItems((int)$order->id);
			$result=$this->sendOrderNotification($order,$items,$log->email_type);
			if (empty($result['success']) || stripos((string)($result['message']??''),'skipped')!==false) throw new RuntimeException($result['message']??'Order email could not be sent.');
			$this->db->where('id',(int)$log->id)->update('db_email_logs',array('status'=>'sent','error_message'=>'Resent from the saved order after the original credit-related failure.'));
			return array('success'=>true,'message'=>'Order email resent to the current saved recipient.');
		} catch (Throwable $e) {
			$this->db->where('id',(int)$log->id)->update('db_email_logs',array('status'=>'failed','error_message'=>mb_substr($e->getMessage(),0,1000)));
			return array('success'=>false,'message'=>$e->getMessage());
		}
	}

	private function sendOrderNotification($order, $items, $templateKey) {
		$storeId=(int)$order->store_id;
		$this->load->model('email_service');
		$this->load->model('email_template_model');
		$this->load->library('storefront_currency');
		$this->load->helper('storefront_url');
		$this->email_service->setStoreId($storeId);
		$this->email_template_model->seedDefaults($storeId);
		$this->load->model('storefront_model');
		$settings=$this->storefront_model->getSettings($storeId);
		$store=get_store_details($storeId);
		if (!$store) throw new RuntimeException('Store details are unavailable for this order.');

		$currencyRow=$this->db->query("SELECT a.currency AS symbol,b.currency_placement AS placement FROM db_currency a,db_store b WHERE a.id=b.currency_id AND b.id=? LIMIT 1",array($storeId))->row();
		$currencyCode=strtoupper((string)($order->transaction_currency??$order->base_currency??$this->storeCurrencyCode($storeId)??'NGN'));
		$symbols=array('NGN'=>'₦','CAD'=>'$','USD'=>'$','EUR'=>'€','GBP'=>'£');
		$currency=$currencyCode.' '.($symbols[$currencyCode]??($currencyRow->symbol??''));
		$rate=(float)($order->exchange_rate??1); if($rate<=0)$rate=1;
		$emailItems=array();
		foreach((array)$items as $item){
			$item=(array)$item;
			$item['item_name']=$this->orderItemName($item,$storeId);
			$item['qty']=(float)($item['qty']??1);
			$hasTransactionPrice=isset($item['transaction_unit_price'])&&$item['transaction_unit_price']!==null;
			$unit=(float)($item['transaction_unit_price']??$item['base_unit_price']??$item['price']??$item['unit_price']??0);
			if(!$hasTransactionPrice && $currencyCode!==strtoupper((string)($order->base_currency??$currencyCode)))$unit=$this->storefront_currency->convertBaseToTransaction($unit,$rate);
			$item['unit_price']=$unit;
			$storedTotal=$item['transaction_total_price']??$item['base_total_price']??$item['total_price']??null;
			$item['total_price']=($storedTotal!==null && ($hasTransactionPrice||$currencyCode===strtoupper((string)($order->base_currency??$currencyCode))))
				?(float)$storedTotal:round($unit*$item['qty'],2,PHP_ROUND_HALF_UP);
			$emailItems[]=$item;
		}
		$sub=(float)($order->transaction_subtotal??$order->subtotal);
		$shipping=(float)($order->transaction_delivery_fee??$order->delivery_fee);
		$tax=(float)($order->transaction_tax_amount??$order->tax_amount??0);
		$total=(float)($order->transaction_grand_total??$order->grand_total);
		$brand=$this->brandData($store,$settings);
		$tplData=array_merge($brand,array(
			'order_code'=>$order->order_code,'customer_name'=>htmlspecialchars((string)$order->customer_name),
			'customer_phone'=>htmlspecialchars((string)$order->customer_phone),'customer_email'=>$order->customer_email?:'N/A',
			'customer_address'=>$order->customer_address?:'N/A','payment_method'=>ucfirst(str_replace('_',' ',$order->payment_method)),
			'shipping_method'=>$order->shipping_method?:'N/A','order_items'=>$this->itemsHtml($emailItems,$currency),
			'order_items_text'=>implode('',array_map(function($item)use($currency){return '- '.$item['item_name'].' x'.$item['qty'].' = '.$currency.' '.number_format((float)$item['total_price'],2)."\n";},$emailItems)),
			'subtotal'=>$currency.' '.number_format($sub,2),'tax_amount'=>$tax>0?$currency.' '.number_format($tax,2):'None',
			'delivery_fee'=>$shipping>0?$currency.' '.number_format($shipping,2):'Free','grand_total'=>$currency.' '.number_format($total,2),
			'order_link'=>mp_storefront_url($settings->store_slug??'','order_received/'.$order->order_code),
			'dashboard_link'=>base_url('online_store/order_detail/'.(int)$order->id),'fulfilment_branch'=>'Store team',
			'prescription_link'=>!empty($order->prescription_file)?base_url($order->prescription_file):'Not attached'
		));
		$options=array('related_module'=>'storefront','related_record_id'=>$order->id,'message_category'=>'order_notification',
			'messaging_key'=>'order-mail:'.$storeId.':'.(int)$order->id.':'.($templateKey==='online_order_owner'?'owner':'customer'));

		if($templateKey==='online_order_customer'){
			$to=trim((string)($order->customer_email??''));
			if(!filter_var($to,FILTER_VALIDATE_EMAIL))throw new RuntimeException('The order has no valid customer email address.');
			return $this->email_service->sendTemplate($templateKey,$to,$tplData,$options);
		}
		$to=array();
		$storeEmail=!empty($settings->store_email)?$settings->store_email:($store->email??'');
		if(filter_var($storeEmail,FILTER_VALIDATE_EMAIL))$to[]=$storeEmail;
		if($this->db->field_exists('warehouse_id','db_online_orders')&&!empty($order->warehouse_id)&&$this->db->table_exists('db_warehouse')&&$this->db->field_exists('email','db_warehouse')){
			$branch=$this->db->select('warehouse_name,email')->where('id',(int)$order->warehouse_id)->where('store_id',$storeId)->where('status',1)->get('db_warehouse')->row();
			if($branch&&filter_var($branch->email??'',FILTER_VALIDATE_EMAIL))$to[]=$branch->email;
			if($branch&&!empty($branch->warehouse_name))$tplData['fulfilment_branch']=htmlspecialchars($branch->warehouse_name);
		}
		$to=array_values(array_unique($to));
		if(!$to)throw new RuntimeException('The store and fulfilment branch do not have a valid notification email.');
		return $this->email_service->sendTemplate($templateKey,count($to)===1?$to[0]:$to,$tplData,$options);
	}

	private function brandData($store,$settings) {
		$logo='';
		if(!empty($settings->store_logo)&&file_exists($settings->store_logo))$logo=base_url($settings->store_logo);
		elseif(!empty($store->store_logo)&&file_exists($store->store_logo))$logo=base_url($store->store_logo);
		else $logo=base_url('theme/dist/img/logo1.png');
		$name=htmlspecialchars($store->store_name??($settings->store_name??'Store'));
		$email=htmlspecialchars(!empty($settings->store_email)?$settings->store_email:($store->email??''));
		$phone=htmlspecialchars($settings->store_phone??($store->mobile??''));
		$address=htmlspecialchars($settings->store_address??($store->address??''));
		$contact=array(); if($address!=='')$contact[]='<p>'.$address.'</p>';
		$reach=array(); if($phone!=='')$reach[]='<a href="tel:'.preg_replace('/[^0-9+]/','',$phone).'">'.$phone.'</a>';
		if($email!=='')$reach[]='<a href="mailto:'.$email.'">'.$email.'</a>';
		if($reach)$contact[]='<p>'.implode(' &nbsp;·&nbsp; ',$reach).'</p>';
		$text=trim(implode("\n",array_filter(array($address,trim($phone.($phone!==''&&$email!==''?' · ':'').$email)))));
		return array('store_name'=>$name,'store_email'=>$email,'store_logo_url'=>$logo,'store_contact_html'=>implode('',$contact),'store_contact_text'=>$text);
	}

	private function itemsHtml(array $items,$currency) {
		$html='';
		foreach($items as $item){
			$name="<td class='name' style='padding:12px;font-size:14px;color:#334155;border-bottom:1px solid #F1F5F9;'>".htmlspecialchars((string)$item['item_name']).'</td>';
			$qty="<td class='ctr' style='padding:12px;font-size:14px;color:#334155;border-bottom:1px solid #F1F5F9;text-align:center;'>".(int)$item['qty'].'</td>';
			$html.='<tr>'.$name.$qty."<td class='num' style='padding:12px;font-size:14px;color:#334155;border-bottom:1px solid #F1F5F9;text-align:right;'>".$currency.' '.number_format((float)$item['total_price'],2).'</td></tr>';
		}
		return $html;
	}

	private function orderItemName(array $item,$storeId) {
		if(trim((string)($item['item_name']??''))!=='')return (string)$item['item_name'];
		$id=(int)($item['item_id']??0);
		if($id>0 && ($item['item_type']??'product')==='service' && $this->db->table_exists('db_services')){
			$name=$this->db->select('service_name')->where('id',$id)->where('store_id',(int)$storeId)->get('db_services')->row();
			if($name&&!empty($name->service_name))return (string)$name->service_name;
		}
		if($id>0 && $this->db->table_exists('db_items')){
			$name=$this->db->select('item_name')->where('id',$id)->where('store_id',(int)$storeId)->get('db_items')->row();
			if($name&&!empty($name->item_name))return (string)$name->item_name;
		}
		return 'Item #'.$id;
	}

	private function storeCurrencyCode($storeId) {
		$row=$this->db->query('SELECT c.currency_code,c.currency_name,c.currency FROM db_currency c JOIN db_store s ON s.currency_id=c.id WHERE s.id=? LIMIT 1',array($storeId))->row();
		if(!$row)return null;
		if(!empty($row->currency_code)&&preg_match('/^[A-Za-z]{3}$/',trim($row->currency_code)))return strtoupper(trim($row->currency_code));
		if(preg_match('/\(([A-Za-z]{3})\)/',$row->currency_name??'',$match))return strtoupper($match[1]);
		if(preg_match('/^[A-Za-z]{3}$/',trim($row->currency??'')))return strtoupper(trim($row->currency));
		return null;
	}
}
