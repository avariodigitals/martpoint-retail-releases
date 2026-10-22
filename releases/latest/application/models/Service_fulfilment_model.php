<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Idempotent adapter from shared commerce lines to shared pending service jobs. */
class Service_fulfilment_model extends CI_Model {
    private function enabled($storeId) {
        if (!$this->db->table_exists('db_service_fulfilment_sources')) return false;
        $profile=mp_get_store_profile($storeId);
        return ($profile['industry_type'] ?? '')==='garden_centre_landscaping' && in_array('service_jobs',$profile['features'],true);
    }

    public function snapshot_online($orderId,$lineId,$storeId,$component = null) {
        if (!$this->enabled($storeId)) return true;
        $order=$this->db->where('id',(int)$orderId)->where('store_id',(int)$storeId)->get('db_online_orders')->row();
        $line=$this->db->where('id',(int)$lineId)->where('order_id',(int)$orderId)->get('db_online_order_items')->row();
        if (!$order || !$line) throw new InvalidArgumentException('Order line not found.');
        // Product-only purchases retain the existing guest checkout behaviour.
        // A customer relationship is required only when creating service work.
        if ($component) {
            if ((int)$component->service_bit!==1 || ($component->product_type ?? '')!=='service') return true;
        } elseif ($line->item_type!=='service') return true;
        if (!$order->customer_id) throw new InvalidArgumentException('Service order identity is missing.');
        $serviceId=null;$posId=null;$componentId=0;
        if ($component) {
            $posId=(int)$component->component_item_id;$componentId=$posId;
            $item=$this->db->where('id',$posId)->where('store_id',(int)$storeId)->where('service_bit',1)->get('db_items')->row();
            if (!$item) throw new InvalidArgumentException('Bundle service not found.');
            $name=$item->item_name;$qty=(float)$component->qty*(float)$line->qty;
        } else {
            $service=$this->db->where('id',(int)$line->item_id)->where('store_id',(int)$storeId)->get('db_services')->row();
            if (!$service) throw new InvalidArgumentException('Online service not found.');
            $this->load->helper('offering_policy');$posId=mp_service_pos_item_id($service) ?: null;$serviceId=(int)$service->id;
            $name=$line->item_name;$qty=(float)$line->qty;
        }
        return $this->snapshot(['store_id'=>(int)$storeId,'source_type'=>'online','source_parent_id'=>(int)$orderId,'source_line_id'=>(int)$lineId,
            'component_item_id'=>$componentId,'customer_id'=>(int)$order->customer_id,'pos_item_id'=>$posId,'online_service_id'=>$serviceId,
            'service_name'=>$name,'quantity'=>$qty]);
    }

    private function snapshot(array $data) {
        $prior=$this->db->where('store_id',$data['store_id'])->where('source_type',$data['source_type'])->where('source_line_id',$data['source_line_id'])
            ->where('component_item_id',$data['component_item_id'])->get('db_service_fulfilment_sources')->row();
        if ($prior) return (int)$prior->id;
        $data['created_at']=date('Y-m-d H:i:s');
        if (!$this->db->insert('db_service_fulfilment_sources',$data)) throw new RuntimeException('Could not record service fulfilment snapshot.');
        return (int)$this->db->insert_id();
    }

    private function materialise($storeId,$type,$parentId) {
        $this->load->model('equipment_model');
        $rows=$this->db->where('store_id',(int)$storeId)->where('source_type',$type)->where('source_parent_id',(int)$parentId)->get('db_service_fulfilment_sources')->result();
        foreach ($rows as $row) {
            if ($row->job_id) continue;
            $id=$this->equipment_model->save_job_data(['customer_id'=>$row->customer_id,'title'=>$row->service_name,'job_type'=>'maintenance','status'=>'open',
                'sales_id'=>$type==='sale' ? (int)$parentId : null,
                'description'=>'Pending fulfilment of '.$row->quantity.' unit(s). Source: '.$type.' #'.$parentId.', line #'.$row->source_line_id.'.'],0,(int)$storeId);
            if (!$this->db->where('id',$row->id)->where('store_id',(int)$storeId)->update('db_service_fulfilment_sources',['job_id'=>$id])) throw new RuntimeException('Could not link service job.');
        }
        return true;
    }

    public function fulfil_sale($saleId,$storeId) {
        if (!$this->enabled($storeId)) return true;
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[(int)$storeId]);
            $sale=$this->db->where('id',(int)$saleId)->where('store_id',(int)$storeId)->where('sales_status','Final')->get('db_sales')->row();
            if (!$sale) throw new InvalidArgumentException('Final sale not found.');
            if ($this->db->where('store_id',(int)$storeId)->where('source_type','sale')->where('source_parent_id',(int)$saleId)->count_all_results('db_service_fulfilment_sources')) {
                $this->materialise($storeId,'sale',$saleId);$this->db->trans_commit();return true;
            }
            $lines=$this->db->select('si.*,i.item_name,i.product_type,i.service_bit,i.is_bundle')->from('db_salesitems si')
                ->join('db_items i','i.id=si.item_id AND i.store_id=si.store_id')->where('si.sales_id',(int)$saleId)->where('si.store_id',(int)$storeId)->get()->result();
            $this->load->model('extras_model');
            foreach ($lines as $line) {
                $components=!empty($line->is_bundle) ? $this->extras_model->getBundleComponents($line->item_id,$storeId) : [(object)['component_item_id'=>0,'service_bit'=>$line->service_bit,'product_type'=>$line->product_type,'item_name'=>$line->item_name,'qty'=>1]];
                foreach ($components as $component) {
                    if ((int)$component->service_bit!==1 || $component->product_type!=='service') continue;
                    $this->snapshot(['store_id'=>(int)$storeId,'source_type'=>'sale','source_parent_id'=>(int)$saleId,'source_line_id'=>(int)$line->id,
                        'component_item_id'=>(int)$component->component_item_id,'customer_id'=>(int)$sale->customer_id,'pos_item_id'=>(int)$component->component_item_id ?: (int)$line->item_id,
                        'online_service_id'=>null,'service_name'=>$component->item_name,'quantity'=>(float)$line->sales_qty*(float)$component->qty]);
                }
            }
            $this->materialise($storeId,'sale',$saleId);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not create sale service work.');
            $this->db->trans_commit();return true;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function fulfil_online($orderId) {
        $order=$this->db->where('id',(int)$orderId)->get('db_online_orders')->row();
        if (!$order || !$this->enabled($order->store_id)) return true;
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[(int)$order->store_id]);
            $order=$this->db->where('id',(int)$orderId)->get('db_online_orders')->row();
            if ($order->payment_status!=='paid' || in_array($order->order_status,['cancelled','refunded'],true)) throw new InvalidArgumentException('Paid active order required for service work.');
            $this->materialise($order->store_id,'online',$orderId);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not create paid service work.');
            $this->db->trans_commit();return true;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function online_complete($orderId,$storeId) {
        $sources=$this->db->select('s.job_id,j.status')->from('db_service_fulfilment_sources s')
            ->join('db_service_jobs j','j.id=s.job_id AND j.store_id=s.store_id','left')->where('s.store_id',(int)$storeId)
            ->where('s.source_type','online')->where('s.source_parent_id',(int)$orderId)->get()->result();
        foreach ($sources as $source) if (!$source->job_id || $source->status!=='completed') return false;
        return true;
    }

    public function cancel_online_work($orderId,$storeId) {
        if (!$this->enabled($storeId)) return true;
        $sources=$this->db->where('store_id',(int)$storeId)->where('source_type','online')->where('source_parent_id',(int)$orderId)->get('db_service_fulfilment_sources')->result();
        foreach ($sources as $source) {
            if (!$source->job_id) continue;
            if (!$this->db->where('id',(int)$source->job_id)->where('store_id',(int)$storeId)->where_not_in('status',['completed','cancelled'])
                ->update('db_service_jobs',['status'=>'cancelled','resolution_notes'=>'Source online order cancelled or refunded. Existing material issues retain their audit history.'])) return false;
        }
        return true;
    }
}
