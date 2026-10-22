<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Industry adapter over shared catalogue and stock engines. No parallel ledger. */
class Garden_model extends CI_Model {
    public function ready() {
        return $this->db->table_exists('db_garden_item_attributes') && $this->db->table_exists('db_garden_stock_events');
    }

    private function store_id() { return (int)get_current_store_id(); }

    /** Use existing branch assignments, including historical inactive branches. */
    public function report_branches() {
        $this->db->select('w.id,w.warehouse_name')->from('db_warehouse w')->where('w.store_id',$this->store_id());
        if (!is_admin() && !is_store_admin()) {
            $this->db->where('EXISTS (SELECT 1 FROM db_userswarehouses uw WHERE uw.warehouse_id=w.id AND uw.user_id='.(int)get_current_user_id().')',null,false);
        }
        return $this->db->order_by('w.warehouse_name')->get()->result();
    }

    /** Read-only projection of the shared invoice and receipt ledgers. */
    public function sales_collections($from,$to,array $branches,$invoicePage=1) {
        $ids=$branches ?: [0];$store=$this->store_id();$invoicePage=max(1,(int)$invoicePage);$invoicePerPage=20;
        $invoices=function() use($from,$to,$ids,$store) {
            $this->db->from('db_sales s')->where('s.store_id',$store)->where('s.sales_status','Final')
                ->where_in('s.warehouse_id',$ids)->where('s.sales_date >=',$from)->where('s.sales_date <=',$to);
            if (is_cashier() && !permissions('show_all_users_sales_invoices')) $this->db->where('s.created_by',$this->session->userdata('inv_username'));
        };
        $invoices();
        $summary=$this->db->select('COUNT(*) invoice_count,COALESCE(SUM(s.grand_total),0) invoiced,COALESCE(SUM(s.paid_amount),0) paid,COALESCE(SUM(GREATEST(COALESCE(s.grand_total,0)-COALESCE(s.paid_amount,0),0)),0) outstanding',false)->get()->row();
        $invoices();
        $rows=$this->db->select('s.id,s.sales_code,s.sales_date,s.grand_total,s.paid_amount,c.customer_name,w.warehouse_name')
            ->join('db_customers c','c.id=s.customer_id AND c.store_id=s.store_id','left')
            ->join('db_warehouse w','w.id=s.warehouse_id AND w.store_id=s.store_id','left')
            ->order_by('s.sales_date','desc')->order_by('s.id','desc')->limit($invoicePerPage,($invoicePage-1)*$invoicePerPage)->get()->result();
        $receipts=function() use($from,$to,$ids,$store) {
            $this->db->from('db_salespayments p')->join('db_sales s','s.id=p.sales_id AND s.store_id=p.store_id')
                ->where('p.store_id',$store)->where('s.sales_status','Final')->where_in('s.warehouse_id',$ids)
                ->where('p.payment_date >=',$from)->where('p.payment_date <=',$to);
            if (is_cashier() && !permissions('show_all_users_sales_invoices')) $this->db->where('s.created_by',$this->session->userdata('inv_username'));
        };
        $receipts();$payments=$this->db->select('COUNT(*) receipt_count,COALESCE(SUM(p.payment),0) collected',false)->get()->row();
        $receipts();$receiptRows=$this->db->select('p.id,p.payment_date,p.payment,p.payment_type,s.id sales_id,s.sales_code,c.customer_name,w.warehouse_name')
            ->join('db_customers c','c.id=s.customer_id AND c.store_id=s.store_id','left')
            ->join('db_warehouse w','w.id=s.warehouse_id AND w.store_id=s.store_id','left')
            ->order_by('p.payment_date','desc')->order_by('p.id','desc')->limit(250)->get()->result();
        return ['totals'=>$summary,'payments'=>$payments,'invoices'=>$rows,'receipts'=>$receiptRows,
            'invoice_page'=>$invoicePage,'invoice_per_page'=>$invoicePerPage,
            'invoice_pages'=>max(1,(int)ceil(((int)$summary->invoice_count)/$invoicePerPage))];
    }

    /** Read-only daily operations, from the same tenant-owned source records. */
    public function daily_workspace(array $permissions, array $profile) {
        $store=$this->store_id();$today=date('Y-m-d');
        $data=['plants'=>null,'low_plants'=>null,'projects'=>null,'visits'=>null,'unassigned'=>null,'jobs'=>[],'pipeline'=>[], 'watchlist'=>[]];
        if (!empty($permissions['items_view'])) {
            $data['plants']=$this->db->from('db_items i')->join('db_garden_item_attributes g','g.item_id=i.id AND g.store_id=i.store_id')
                ->where('i.store_id',$store)->where('i.status',1)->count_all_results();
            $data['low_plants']=$this->db->from('db_items i')->join('db_garden_item_attributes g','g.item_id=i.id AND g.store_id=i.store_id')
                ->where('i.store_id',$store)->where('i.status',1)->where('i.stock <= i.alert_qty',null,false)->count_all_results();
            $data['watchlist']=$this->db->select('i.id,i.item_name,i.stock,i.alert_qty,g.attributes_json')->from('db_items i')
                ->join('db_garden_item_attributes g','g.item_id=i.id AND g.store_id=i.store_id')->where('i.store_id',$store)->where('i.status',1)
                ->where('i.stock <= i.alert_qty',null,false)->order_by('i.stock','asc')->limit(5)->get()->result();
        }
        if (!empty($permissions['service_jobs_view']) && in_array('service_jobs',$profile['features'],true) && $this->db->table_exists('db_service_jobs')) {
            $data['visits']=$this->db->where('store_id',$store)->where('scheduled_date',$today)->where_not_in('status',['completed','cancelled'])->count_all_results('db_service_jobs');
            $data['unassigned']=$this->db->where('store_id',$store)->where_not_in('status',['completed','cancelled'])
                ->group_start()->where('assigned_user_id IS NULL',null,false)->or_where('assigned_user_id',0)->group_end()->count_all_results('db_service_jobs');
            $data['jobs']=$this->db->select('j.id,j.title,j.scheduled_date,j.status,c.customer_name,u.username AS engineer')->from('db_service_jobs j')
                ->join('db_customers c','c.id=j.customer_id AND c.store_id=j.store_id','left')->join('db_users u','u.id=j.assigned_user_id AND u.store_id=j.store_id','left')
                ->where('j.store_id',$store)->where_not_in('j.status',['completed','cancelled'])->group_start()->where('j.scheduled_date <=',$today)->or_where('j.scheduled_date IS NULL',null,false)->group_end()
                ->order_by('j.scheduled_date','asc')->limit(5)->get()->result();
            if (mp_garden_has_mode($profile,'landscaping') && $this->db->table_exists('db_service_job_context')) {
                $data['projects']=$this->db->where('store_id',$store)->where_not_in('stage',['closed','cancelled'])->count_all_results('db_service_job_context');
                $data['pipeline']=$this->db->select('stage,COUNT(*) AS total',false)->where('store_id',$store)->where_not_in('stage',['closed','cancelled'])->group_by('stage')->get('db_service_job_context')->result();
            }
        }
        return $data;
    }

    public function attributes($itemId, $storeId = null) {
        if (!$this->db->table_exists('db_garden_item_attributes')) return null;
        $row = $this->db->where('store_id', $storeId === null ? $this->store_id() : (int)$storeId)
            ->where('item_id', (int)$itemId)->get('db_garden_item_attributes')->row();
        return $row ? (json_decode($row->attributes_json, true) ?: []) : null;
    }

    public function catalogue($livingOnly = false) {
        $this->db->select('i.*, g.attributes_json')->from('db_items i')
            ->join('db_garden_item_attributes g', 'g.item_id=i.id AND g.store_id=i.store_id', 'left')
            ->where('i.store_id', $this->store_id())->where('i.status', 1);
        if ($livingOnly) $this->db->where('g.id IS NOT NULL', null, false);
        return $this->db->order_by('i.item_name')->limit(500)->get()->result();
    }

    /** Mobile-friendly catalogue projection: only one small page is rendered. */
    public function catalogue_page($page=1,$search='') {
        $store=$this->store_id();$page=max(1,(int)$page);$perPage=10;$search=trim((string)$search);
        $apply=function() use($store,$search) {
            $this->db->from('db_items i')->join('db_garden_item_attributes g','g.item_id=i.id AND g.store_id=i.store_id','left')
                ->where('i.store_id',$store)->where('i.status',1);
            if($search!=='') $this->db->group_start()->like('i.item_name',$search)->or_like('i.item_code',$search)->group_end();
        };
        $apply();
        $counts=$this->db->select("COUNT(*) total,SUM(CASE WHEN g.id IS NOT NULL THEN 1 ELSE 0 END) plant,SUM(CASE WHEN COALESCE(i.is_bundle,0)=1 THEN 1 ELSE 0 END) bundle,SUM(CASE WHEN COALESCE(i.service_bit,0)=1 THEN 1 ELSE 0 END) service",false)->get()->row_array();
        $total=(int)($counts['total'] ?? 0);$pages=max(1,(int)ceil($total/$perPage));if($page>$pages)$page=$pages;
        $apply();
        $items=$this->db->select('i.*,g.attributes_json')->order_by('i.item_name')->limit($perPage,($page-1)*$perPage)->get()->result();
        return ['items'=>$items,'page'=>$page,'pages'=>$pages,'total'=>$total,'counts'=>[
            'all'=>$total,'plant'=>(int)($counts['plant'] ?? 0),'bundle'=>(int)($counts['bundle'] ?? 0),
            'service'=>(int)($counts['service'] ?? 0),'supply'=>max(0,$total-(int)($counts['plant'] ?? 0)-(int)($counts['bundle'] ?? 0)-(int)($counts['service'] ?? 0)),
        ]];
    }

    public function save_attributes($itemId, array $input, $living) {
        $storeId = $this->store_id();
        $item = $this->db->where('id', (int)$itemId)->where('store_id', $storeId)->get('db_items')->row();
        if (!$item) throw new InvalidArgumentException('Product not found.');
        if ($living && ((int)$item->service_bit === 1 || !empty($item->is_bundle)
            || !in_array($item->product_type, ['physical','item',''], true))) {
            throw new InvalidArgumentException('Living products must be physical merchandise.');
        }
        $attributes = mp_garden_clean_attributes($input);
        if (!$living) return $this->db->where('store_id', $storeId)->where('item_id', (int)$itemId)->delete('db_garden_item_attributes');
        return $this->db->query('INSERT INTO db_garden_item_attributes (store_id,item_id,attributes_json,updated_by,updated_at)
            VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE attributes_json=VALUES(attributes_json),updated_by=VALUES(updated_by),updated_at=VALUES(updated_at)',
            [$storeId, (int)$itemId, json_encode($attributes), (int)$this->session->userdata('inv_userid'), date('Y-m-d H:i:s')]);
    }

    public function losses() {
        return $this->db->select('e.*,i.item_name,w.warehouse_name')->from('db_garden_stock_events e')
            ->join('db_items i','i.id=e.item_id AND i.store_id=e.store_id')
            ->join('db_warehouse w','w.id=e.warehouse_id AND w.store_id=e.store_id')
            ->where('e.store_id',$this->store_id())->order_by('e.id','DESC')->limit(250)->get()->result();
    }

    /** Transaction + tenant lock serialize replay and guard available stock. */
    public function record_loss(array $input) {
        $storeId = $this->store_id();
        $itemId = (int)($input['item_id'] ?? 0);
        $warehouseId = (int)($input['warehouse_id'] ?? 0);
        $key = (string)($input['request_key'] ?? '');
        $reason = (string)($input['reason'] ?? '');
        $rawQty = (string)($input['quantity'] ?? '');
        if (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $rawQty) || (float)$rawQty <= 0 || (float)$rawQty > 999999999) {
            throw new InvalidArgumentException('Enter a positive quantity with at most two decimal places.');
        }
        if (!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D', $key) || !isset(mp_garden_loss_reasons()[$reason])) {
            throw new InvalidArgumentException('Invalid loss reason or request reference.');
        }
        $note = trim((string)($input['note'] ?? ''));
        if (strlen($note) > 4000 || ($reason === 'other' && $note === '')) throw new InvalidArgumentException('Provide a valid loss explanation.');
        $qty = (float)$rawQty;
        $hash = hash('sha256', json_encode([$itemId,$warehouseId,number_format($qty,2,'.',''),$reason,$note]));
        $this->db->trans_begin();
        try {
            $store = $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE', [$storeId])->row();
            if (!$store) throw new InvalidArgumentException('Store not found.');
            $prior = $this->db->where('store_id',$storeId)->where('request_key',$key)->get('db_garden_stock_events')->row();
            if ($prior) {
                if (!hash_equals($prior->payload_hash,$hash)) throw new InvalidArgumentException('This reference was used for a different loss.');
                $this->db->trans_commit();
                return (int)$prior->adjustment_id;
            }
            $item = $this->db->query('SELECT * FROM db_items WHERE id=? AND store_id=? FOR UPDATE',[$itemId,$storeId])->row();
            if (!$item || (int)$item->service_bit === 1 || !empty($item->is_bundle) || $this->attributes($itemId) === null) {
                throw new InvalidArgumentException('Select a living product belonging to this store.');
            }
            $warehouse = $this->db->where('id',$warehouseId)->where('store_id',$storeId)->where('status',1)->get('db_warehouse')->row();
            if (!$warehouse) throw new InvalidArgumentException('Branch not found.');
            // stock is the shared available quantity, including held online orders;
            // warehouse availability additionally constrains the physical branch.
            $available = min((float)$item->stock, (float)total_available_qty_items_of_warehouse($warehouseId,$storeId,$itemId));
            if ($available < $qty) throw new InvalidArgumentException('Insufficient available stock in this branch.');
            $now = date('Y-m-d H:i:s');
            $ok = $this->db->insert('db_stockadjustment', [
                'store_id'=>$storeId,'warehouse_id'=>$warehouseId,'reference_no'=>'GL-'.substr(hash('sha256',$key),0,32),
                'adjustment_date'=>date('Y-m-d'),'adjustment_note'=>mp_garden_loss_reasons()[$reason].($note ? ': '.$note : ''),
                'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>$this->session->userdata('inv_username'),
                'system_ip'=>$this->input->ip_address(),'system_name'=>'Garden Centre','status'=>1,
            ]);
            if (!$ok) throw new RuntimeException('Could not create stock adjustment.');
            $adjustmentId = (int)$this->db->insert_id();
            if (!$this->db->insert('db_stockadjustmentitems', [
                'store_id'=>$storeId,'warehouse_id'=>$warehouseId,'adjustment_id'=>$adjustmentId,'item_id'=>$itemId,
                'adjustment_qty'=>-$qty,'description'=>mp_garden_loss_reasons()[$reason],'status'=>1,
            ])) throw new RuntimeException('Could not record stock movement.');
            if (!$this->db->insert('db_garden_stock_events',[
                'store_id'=>$storeId,'request_key'=>$key,'payload_hash'=>$hash,'item_id'=>$itemId,'warehouse_id'=>$warehouseId,
                'adjustment_id'=>$adjustmentId,'reason'=>$reason,'quantity'=>$qty,'unit_cost'=>(float)$item->purchase_price,
                'note'=>$note,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,
            ])) throw new RuntimeException('Could not record loss audit.');
            $this->load->model('pos_model');
            if (!$this->pos_model->update_items_quantity($itemId) || !update_warehouse_items([['item_id'=>$itemId]])) {
                throw new RuntimeException('Could not reconcile stock.');
            }
            if ($this->db->trans_status() === false) throw new RuntimeException('Could not save stock movement.');
            $this->db->trans_commit();
            return $adjustmentId;
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function batches() {
        return $this->db->select('b.*,i.item_name,p.purchase_date,p.supplier_id,s.supplier_name')->from('db_item_barcodes b')
            ->join('db_items i','i.id=b.item_id')->join('db_garden_item_attributes g','g.item_id=i.id AND g.store_id=i.store_id')
            ->join('db_purchase p','p.id=b.purchase_id AND p.store_id=i.store_id','left')
            ->join('db_suppliers s','s.id=p.supplier_id AND s.store_id=i.store_id','left')
            ->where('i.store_id',$this->store_id())->order_by('b.id','DESC')->limit(250)->get()->result();
    }

    /** Public requests enter the existing CRM; they are neither sales nor confirmed appointments. */
    public function submit_request($storeId, array $input, array $profile) {
        $kind = $input['request_kind'] ?? 'maintenance';
        if (!in_array($kind,['landscaping','maintenance'],true) || !mp_garden_has_mode($profile,$kind)) throw new InvalidArgumentException('This service is not available.');
        $name=trim((string)($input['name'] ?? '')); $phone=trim((string)($input['phone'] ?? '')); $email=trim((string)($input['email'] ?? ''));
        $location=trim((string)($input['site_location'] ?? '')); $description=trim((string)($input['description'] ?? ''));
        $date=trim((string)($input['preferred_date'] ?? '')); $budget=trim((string)($input['budget'] ?? ''));
        $key=(string)($input['submission_ref'] ?? ''); $serviceId=(int)($input['service_id'] ?? 0);
        $productId=(int)($input['product_id'] ?? 0);
        if (!$name || strlen($name)>150 || !$phone || strlen($phone)>30 || strlen($email)>255 || ($email && !filter_var($email,FILTER_VALIDATE_EMAIL))
            || !$location || strlen($location)>1000 || !$description || strlen($description)>4000) throw new InvalidArgumentException('Enter your name, phone, site location and a description. Check the length of your details.');
        if (!preg_match('/^garden_[a-f0-9]{40}$/D',$key)) throw new InvalidArgumentException('Please reload the request form.');
        if ($date) { $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$date); if (!$parsed || $parsed->format('Y-m-d')!==$date || $date<date('Y-m-d')) throw new InvalidArgumentException('Choose a valid future preferred date.'); }
        if ($budget && (!is_numeric($budget) || !is_finite((float)$budget) || (float)$budget<0 || (float)$budget>999999999999)) throw new InvalidArgumentException('Enter a valid optional budget.');
        $service = $serviceId ? $this->db->where('id',$serviceId)->where('store_id',(int)$storeId)->where('status',1)->where('available_online',1)->get('db_services')->row() : null;
        if ($serviceId && !$service) throw new InvalidArgumentException('Service not found.');
        $product=$productId ? $this->db->where('id',$productId)->where('store_id',(int)$storeId)->where('status',1)->where('publish_online',1)->group_start()->where('requires_quote',1)->or_where('product_type','service')->or_where('service_bit',1)->group_end()->get('db_items')->row() : null;
        if ($productId && !$product) throw new InvalidArgumentException('Service or quote offering not found.');
        $notes=json_encode(['request_kind'=>$kind,'site_location'=>$location,'description'=>$description,'preferred_date'=>$date ?: null,'budget'=>$budget ?: null,'service_id'=>$serviceId ?: null,'product_id'=>$productId ?: null],JSON_UNESCAPED_UNICODE);
        $this->db->trans_begin();
        try {
            if (!$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[(int)$storeId])->row()) throw new InvalidArgumentException('Store not found.');
            $prior=$this->db->where('store_id',(int)$storeId)->where('submission_ref',$key)->get('db_leads')->row();
            if ($prior) { $this->db->trans_commit(); return (int)$prior->id; }
            // Rate limit is tenant + IP, backed by the existing shared table.
            if (!$this->db->table_exists('db_rate_limits')) throw new RuntimeException('Missing shared intake rate limiter.');
            $bucket='garden:'.(int)$storeId.':'.hash('sha256',$this->input->ip_address());
            $window=date('Y-m-d H:00:00');
            $this->db->query('INSERT INTO db_rate_limits (bucket,window_start,hits) VALUES (?,?,1) ON DUPLICATE KEY UPDATE hits=hits+1',[$bucket,$window]);
            $limit=$this->db->where('bucket',$bucket)->where('window_start',$window)->get('db_rate_limits')->row();
            if (!$limit || $limit->hits>15) throw new InvalidArgumentException('Too many requests. Please contact the store directly.');
            $this->load->model('leads_model');
            $id=$this->leads_model->saveLead(['store_id'=>(int)$storeId,'name'=>$name,'phone'=>$phone,'email'=>$email ?: null,'source'=>'storefront','status'=>'new',
                'interest'=>ucfirst($kind).($service ? ': '.substr($service->service_name,0,180) : ''),'enquiry'=>$description,'notes'=>$notes,
                'preferred_date'=>$date ?: null,'submission_ref'=>$key,'created_at'=>date('Y-m-d H:i:s')]);
            if (!$id || $this->db->trans_status()===false) throw new RuntimeException('Could not create CRM request.');
            $this->db->trans_commit(); return (int)$id;
        } catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
    }
    /** Date-scoped operational activity; financial figures stay in Dashboard_model. */
    public function period_workspace($from,$to,array $allowed) {
        $store=$this->store_id();$out=[];
        if(!empty($allowed['stock_adjustment_view'])) {
            $out['plant_losses']=$this->db->select('COALESCE(SUM(quantity),0) AS units,COALESCE(SUM(quantity*unit_cost),0) AS cost',false)->where('store_id',$store)->where('created_at >=',$from.' 00:00:00')->where('created_at <=',$to.' 23:59:59')->get('db_garden_stock_events')->row();
        }
        if(!empty($allowed['service_jobs_view']) && $this->db->table_exists('db_service_job_context')) {
            $out['projects_created']=(int)$this->db->where('store_id',$store)->where('created_at >=',$from.' 00:00:00')->where('created_at <=',$to.' 23:59:59')->count_all_results('db_service_job_context');
            $out['projects_completed']=(int)$this->db->select('COUNT(DISTINCT job_id) AS n',false)->where('store_id',$store)->where('to_stage','completed')->where('created_at >=',$from.' 00:00:00')->where('created_at <=',$to.' 23:59:59')->get('db_service_job_events')->row()->n;
            $out['visits']=$this->db->select('j.id,j.title,j.scheduled_date,j.status,c.customer_name')->from('db_service_jobs j')->join('db_customers c','c.id=j.customer_id AND c.store_id=j.store_id','left')->where('j.store_id',$store)->where('j.scheduled_date >=',$from)->where('j.scheduled_date <=',$to)->order_by('j.scheduled_date')->limit(100)->get()->result();
            if($this->db->table_exists('db_service_project_records'))$out['inspections']=(int)$this->db->where('store_id',$store)->where('record_type','inspection')->where('created_at >=',$from.' 00:00:00')->where('created_at <=',$to.' 23:59:59')->count_all_results('db_service_project_records');
        }
        if(!empty($allowed['production_batches_view']) && $this->db->table_exists('db_garden_production_context')) {
            $out['production']=$this->db->select('g.batch_id,g.saleable_qty,g.completed_cost,g.unit_cost,g.completed_at,b.batch_name,i.item_name')->from('db_garden_production_context g')->join('db_production_batches b','b.id=g.batch_id AND b.store_id=g.store_id')->join('db_items i','i.id=g.product_item_id AND i.store_id=g.store_id')->where('g.store_id',$store)->where('g.completed_at >=',$from.' 00:00:00')->where('g.completed_at <=',$to.' 23:59:59')->order_by('g.completed_at','desc')->limit(100)->get()->result();
            $out['production_activity']=$this->db->select('kind,COUNT(*) AS entries,COALESCE(SUM(quantity),0) AS quantity,COALESCE(SUM(cost),0) AS cost',false)->where('store_id',$store)->where('created_at >=',$from.' 00:00:00')->where('created_at <=',$to.' 23:59:59')->group_by('kind')->get('db_garden_production_entries')->result();
        }
        return $out;
    }

}
