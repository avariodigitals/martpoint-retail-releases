<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Nylon & Polythene Manufacturing Model
 *
 * Job-order driven production for factories that:
 *   (a) extrude film and convert it into bags   (nylon_extrusion + nylon_conversion)
 *   (b) buy film rolls and convert them          (nylon_conversion only)
 *   (c) trade film rolls without converting      (nylon_roll_trading only)
 *
 * Stock moves ride the existing db_stockadjustment engine (same pattern as
 * Production_batches_model::complete_batch) so every movement is a traceable
 * ledger row. Stage semantics:
 *
 *   material_allocation — reserves the input; records qty but posts no stock.
 *   extrusion           — consumes raw material, produces a film-roll item.
 *   printing            — consumes a film roll, returns it as printed
 *                         (same roll item; rejects net out of stock).
 *   cutting             — consumes film rolls; finished-good output is held
 *                         back — the product item is only credited by QC.
 *   packing             — same as cutting for the product item.
 *   qc                  — credits the job's product item, but ONLY when a
 *                         supervisor approves the log. This is what stops
 *                         unverified output inflating saleable stock.
 *
 * Reusable scrap is credited to a scrap item when one is provided; rejects and
 * waste are recorded but never add stock. Reversals post a counter-adjustment
 * and keep the original log for the audit trail.
 */
class Nylon_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        foreach (['db_nylon_item_specs','db_nylon_machines','db_nylon_jobs','db_nylon_job_stages','db_nylon_job_logs','db_nylon_job_costs','db_nylon_artworks'] as $t) {
            if (!$this->db->table_exists($t)) {
                log_message('error', 'Missing required table: ' . $t . '. Run the 4.0.9.53 migration via login.');
            }
        }
    }

    /* ============================ vocabularies ============================ */

    public static function item_classes() {
        return [
            'raw_material'  => 'Raw Material (resin, masterbatch, ink)',
            'film_roll'     => 'Film Roll (WIP / traded)',
            'finished_good' => 'Finished Good (bags, packed film)',
            'consumable'    => 'Consumable / Packaging',
        ];
    }

    public static function materials() {
        return ['LDPE'=>'LDPE','HDPE'=>'HDPE','LLDPE'=>'LLDPE','PP'=>'Polypropylene','Recycled'=>'Recycled Blend','Other'=>'Other'];
    }

    public static function product_forms() {
        return ['bag'=>'Bag','film_roll'=>'Film Roll','sheet'=>'Sheet','tubing'=>'Tubing','liners'=>'Liners'];
    }

    public static function bag_types() {
        return ['vest'=>'Vest / T-shirt','flat'=>'Flat / Counter','punch_handle'=>'Punch Handle','zip'=>'Zip / Resealable','garbage'=>'Garbage / Bin Liner','bread'=>'Bread Bag','other'=>'Other'];
    }

    public static function print_types() {
        return ['none'=>'No Printing','surface'=>'Surface Print','flexo'=>'Flexo Print','gravure'=>'Gravure Print','custom'=>'Custom / Other'];
    }

    public static function machine_types() {
        return ['extruder'=>'Extruder','printer'=>'Printer','cutter'=>'Cutting / Sealing','puncher'=>'Puncher','packer'=>'Packer','other'=>'Other'];
    }

    public static function stage_defs() {
        return [
            'material_allocation' => ['label'=>'Material Allocation','icon'=>'fa-cubes',        'machine_type'=>null],
            'extrusion'           => ['label'=>'Extrusion (Film Roll)','icon'=>'fa-industry',   'machine_type'=>'extruder'],
            'printing'            => ['label'=>'Printing','icon'=>'fa-print',                   'machine_type'=>'printer'],
            'cutting'             => ['label'=>'Cutting / Sealing / Punching','icon'=>'fa-cut', 'machine_type'=>'cutter'],
            'packing'             => ['label'=>'Packing','icon'=>'fa-archive',                  'machine_type'=>'packer'],
            'qc'                  => ['label'=>'Quality Check','icon'=>'fa-check-circle',       'machine_type'=>null],
        ];
    }

    public static function stage_label($key) {
        $defs = self::stage_defs();
        return isset($defs[$key]) ? $defs[$key]['label'] : ucfirst(str_replace('_',' ',$key));
    }

    public static function job_statuses() {
        return ['planned','in_progress','on_hold','completed','cancelled'];
    }

    public static function job_status_label($s) {
        $l = ['planned'=>'Planned','in_progress'=>'In Progress','on_hold'=>'On Hold','completed'=>'Completed','cancelled'=>'Cancelled'];
        return $l[$s] ?? ucfirst(str_replace('_',' ',$s));
    }

    public static function job_status_badge($s) {
        $b = ['planned'=>'default','in_progress'=>'primary','on_hold'=>'warning','completed'=>'success','cancelled'=>'danger'];
        return $b[$s] ?? 'default';
    }

    public static function stage_status_badge($s) {
        $b = ['pending'=>'default','in_progress'=>'info','done'=>'success','skipped'=>'muted'];
        return $b[$s] ?? 'default';
    }

    /* ============================ factory mode ============================ */

    /**
     * Capability flags for this store — configured via Business Profile
     * feature flags (nylon_extrusion / nylon_conversion / nylon_roll_trading).
     */
    public function mode_flags() {
        return [
            'extrusion'  => mp_feature_enabled('nylon_extrusion'),
            'conversion' => mp_feature_enabled('nylon_conversion'),
            'trading'    => mp_feature_enabled('nylon_roll_trading'),
        ];
    }

    public function mode_label() {
        $f = $this->mode_flags();
        if ($f['extrusion'] && $f['conversion']) return 'Extrudes film and converts to bags';
        if ($f['extrusion'] && $f['trading'])   return 'Extrudes and sells film rolls';
        if ($f['conversion'])                   return 'Buys film and converts to bags';
        if ($f['trading'])                      return 'Sells film rolls (no conversion)';
        if ($f['extrusion'])                    return 'Extrudes film rolls';
        return 'Nylon / polythene';
    }

    /* ============================ item specs ============================== */

    public function get_spec($item_id, $store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->where('store_id',$store_id)->where('item_id',$item_id)->get('db_nylon_item_specs')->row();
    }

    public function save_spec($item_id, $data, $store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $existing = $this->get_spec($item_id, $store_id);
        $data['item_id'] = $item_id;
        $data['store_id'] = $store_id;
        if ($existing) {
            return $this->db->where('id',$existing->id)->update('db_nylon_item_specs', $data);
        }
        return $this->db->insert('db_nylon_item_specs', $data);
    }

    /** Items that participate in the nylon pipeline, optionally by class. */
    public function get_spec_items($class = null, $store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('s.*, i.item_name, i.item_code, i.stock, i.sales_price, i.purchase_price, i.not_for_sale, u.unit_name')
            ->from('db_nylon_item_specs s')
            ->join('db_items i','i.id = s.item_id','left')
            ->join('db_units u','u.id = i.unit_id','left')
            ->where('s.store_id',$store_id)->where('s.status',1);
        if ($class) $this->db->where('s.item_class',$class);
        $this->db->order_by('s.item_class','asc')->order_by('i.item_name','asc');
        return $this->db->get()->result();
    }

    /* ============================ machines ================================ */

    public function get_machines($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->where('store_id',$store_id)->where('status',1)->order_by('machine_name','asc')->get('db_nylon_machines')->result();
    }

    public function save_machine($data, $id = null) {
        if ($id) {
            return $this->db->where('id',$id)->where('store_id',get_current_store_id())->update('db_nylon_machines',$data);
        }
        $data['store_id'] = get_current_store_id();
        return $this->db->insert('db_nylon_machines',$data);
    }

    public function delete_machine($id) {
        return $this->db->where('id',$id)->where('store_id',get_current_store_id())->update('db_nylon_machines',['status'=>0]);
    }

    /* ============================ units / conversion ====================== */

    public function get_units($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->where('store_id',$store_id)->where('status',1)->order_by('unit_name','asc')->get('db_units')->result();
    }

    /**
     * Convert a qty expressed in $unit_id into the item's base (stock) unit.
     * Uses the per-item selling-unit table first (explicit conversion), then
     * the unit family; returns NULL when no conversion is known — callers must
     * never fall back to a guessed bags-per-kg rate.
     */
    public function to_base_qty($item_id, $unit_id, $qty, $store_id = null) {
        $qty = (float)$qty;
        if (empty($store_id)) $store_id = get_current_store_id();
        $item = $this->db->select('unit_id')->where('id',$item_id)->get('db_items')->row();
        if (!$item) return null;
        if (empty($unit_id) || (int)$unit_id === (int)$item->unit_id) return $qty;

        if ($this->db->table_exists('db_item_selling_units')) {
            $su = $this->db->where('item_id',$item_id)->where('unit_id',$unit_id)
                ->where('store_id',$store_id)->where('status',1)->get('db_item_selling_units')->row();
            if ($su && (float)$su->conversion_factor > 0) {
                return $qty * (float)$su->conversion_factor;
            }
        }
        if ($item->unit_id && function_exists('get_unit_family')) {
            foreach (get_unit_family($item->unit_id, $store_id) as $u) {
                if ((int)$u->id === (int)$unit_id && (float)$u->equivalent_qty > 0) {
                    return $qty / (float)$u->equivalent_qty;
                }
            }
        }
        return null;
    }

    /* ============================ customer orders ========================= */

    /**
     * Nylon customer job order — stored in db_custom_orders so deposits,
     * history, status workflow and quotation/sale conversion keep working.
     */
    public function save_order($data, $id = null) {
        $this->load->model('custom_orders_model','custom_orders');
        $data['workflow_template_key'] = 'nylon';
        return $this->custom_orders->save($data, $id);
    }

    public function get_order($id) {
        $this->load->model('custom_orders_model','custom_orders');
        $order = $this->custom_orders->get($id);
        if ($order) {
            $order->specs = json_decode($order->specifications_json ?: '[]', true) ?: [];
            $order->unit_name = '';
            if (!empty($order->order_unit_id)) {
                $u = $this->db->select('unit_name')->where('id',$order->order_unit_id)->get('db_units')->row();
                $order->unit_name = $u ? $u->unit_name : '';
            }
            $order->artworks = $this->get_artworks($id);
            $order->artwork_approved = $this->artwork_ready($id);
        }
        return $order;
    }

    public function get_orders($store_id = null, $status = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('a.*, c.customer_name, c.mobile, b.item_name')
            ->from('db_custom_orders a')
            ->join('db_customers c','c.id = a.customer_id','left')
            ->join('db_items b','b.id = a.item_id','left')
            ->where('a.store_id',$store_id)
            ->where('a.workflow_template_key','nylon')
            ->order_by('a.created_at','desc');
        if ($status) $this->db->where('a.status',$status);
        return $this->db->get()->result();
    }

    /**
     * Clone an approved order's specification into a repeat order. Artwork
     * rows are copied too — an approved design stays approved on the repeat.
     */
    public function repeat_order($order_id, $order_date = null, $due_date = null) {
        $src = $this->get_order($order_id);
        if (!$src) return false;
        $data = [
            'store_id'      => $src->store_id,
            'customer_id'   => $src->customer_id,
            'item_id'       => $src->item_id,
            'item_name'     => $src->item_name,
            'specifications_json' => $src->specifications_json,
            'order_qty'     => $src->order_qty,
            'order_unit_id' => $src->order_unit_id,
            'artwork_required' => $src->artwork_required,
            'design_ref'    => $src->design_ref,
            'repeat_of_id'  => $src->id,
            'quoted_price'  => $src->quoted_price,
            'deposit_amount'=> $src->deposit_amount,
            'deposit_paid'  => 0,
            'total_amount'  => $src->total_amount,
            'balance_due'   => $src->total_amount,
            'status'        => 'new',
            'notes'         => 'Repeat of ' . $src->order_code,
            'order_date'    => $order_date ?: date('Y-m-d'),
            'due_date'      => $due_date,
        ];
        $new_id = $this->save_order($data);
        if ($new_id) {
            foreach ($src->artworks as $art) {
                $this->db->insert('db_nylon_artworks', [
                    'store_id' => $src->store_id,
                    'custom_order_id' => $new_id,
                    'file_name' => $art->file_name,
                    'file_path' => $art->file_path,
                    'version_no'=> $art->version_no,
                    'status'    => $art->status,
                    'note'      => 'Carried over from ' . $src->order_code,
                    'uploaded_by' => $art->uploaded_by,
                    'approved_by' => $art->approved_by,
                    'approved_at' => $art->approved_at,
                ]);
            }
        }
        return $new_id;
    }

    /* ============================ artwork ================================= */

    public function get_artworks($order_id) {
        return $this->db->where('custom_order_id',$order_id)->order_by('version_no','desc')->order_by('id','desc')->get('db_nylon_artworks')->result();
    }

    public function add_artwork($order_id, $file_name, $file_path, $note = '') {
        $order = $this->db->where('id',$order_id)->get('db_custom_orders')->row();
        if (!$order) return false;
        $last = $this->db->select_max('version_no')->where('custom_order_id',$order_id)->get('db_nylon_artworks')->row();
        $this->db->insert('db_nylon_artworks', [
            'store_id' => $order->store_id,
            'custom_order_id' => $order_id,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'version_no'=> (int)($last->version_no ?? 0) + 1,
            'status'    => 'pending',
            'note'      => $note,
            'uploaded_by' => $this->session->userdata('username') ?: 'System',
        ]);
        return $this->db->insert_id();
    }

    public function set_artwork_status($artwork_id, $status, $note = '') {
        if (!in_array($status, ['approved','rejected','pending'], true)) return false;
        $art = $this->db->where('id',$artwork_id)->get('db_nylon_artworks')->row();
        if (!$art) return false;
        $data = ['status'=>$status, 'note'=>$note ?: $art->note];
        if ($status === 'approved') {
            // Only one version may be approved at a time — others go back to pending.
            $this->db->where('custom_order_id',$art->custom_order_id)->where('status','approved')->update('db_nylon_artworks',['status'=>'pending','approved_at'=>null,'approved_by'=>null]);
            $data['approved_by'] = $this->session->userdata('username') ?: 'System';
            $data['approved_at'] = date('Y-m-d H:i:s');
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
        return $this->db->where('id',$artwork_id)->update('db_nylon_artworks',$data);
    }

    /** True when the order has an approved artwork (or never needed one). */
    public function artwork_ready($order_id) {
        $order = $this->db->where('id',$order_id)->get('db_custom_orders')->row();
        if (!$order) return false;
        if (empty($order->artwork_required)) return true;
        return $this->db->where('custom_order_id',$order_id)->where('status','approved')->count_all_results('db_nylon_artworks') > 0;
    }

    /* ============================ jobs ==================================== */

    public function get_job($id) {
        return $this->db->select('j.*, i.item_name, u.unit_name, o.order_code, o.customer_id, c.customer_name')
            ->from('db_nylon_jobs j')
            ->join('db_items i','i.id = j.product_item_id','left')
            ->join('db_units u','u.id = j.planned_unit_id','left')
            ->join('db_custom_orders o','o.id = j.custom_order_id','left')
            ->join('db_customers c','c.id = o.customer_id','left')
            ->where('j.id',$id)->get()->row();
    }

    public function get_jobs($store_id = null, $status = null, $warehouse_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('j.*, i.item_name, o.order_code, c.customer_name, w.warehouse_name')
            ->from('db_nylon_jobs j')
            ->join('db_items i','i.id = j.product_item_id','left')
            ->join('db_custom_orders o','o.id = j.custom_order_id','left')
            ->join('db_customers c','c.id = o.customer_id','left')
            ->join('db_warehouse w','w.id = j.warehouse_id','left')
            ->where('j.store_id',$store_id)
            ->order_by('j.due_date','asc')->order_by('j.id','desc');
        if ($status) $this->db->where('j.status',$status);
        if ($warehouse_id) $this->db->where('j.warehouse_id',$warehouse_id);
        return $this->db->get()->result();
    }

    public function get_stages($job_id) {
        return $this->db->where('job_id',$job_id)->order_by('seq','asc')->get('db_nylon_job_stages')->result();
    }

    public function get_logs($job_id) {
        return $this->db->select('l.*, s.stage_key, s.seq, m.machine_name, u.first_name, u.last_name')
            ->from('db_nylon_job_logs l')
            ->join('db_nylon_job_stages s','s.id = l.stage_id','left')
            ->join('db_nylon_machines m','m.id = l.machine_id','left')
            ->join('db_users u','u.id = l.operator_id','left')
            ->where('l.job_id',$job_id)->order_by('l.id','asc')->get()->result();
    }

    /**
     * Build the stage plan for a job. The pipeline is driven by the factory
     * mode flags and the product spec, snapshotted onto the job so later
     * flag changes cannot corrupt an in-flight job.
     *
     * @param array $cfg product_item_id, input_item_id (resin or purchased
     *                   roll), roll_item_id (the roll that extrusion outputs /
     *                   printing & cutting consume — defaults to input_item_id
     *                   when the input already is a roll), print (bool)
     * @return array ordered list of stage rows (without job_id)
     */
    public function build_stage_plan($cfg) {
        $flags   = $this->mode_flags();
        $store_id= get_current_store_id();
        $product = $this->db->where('id',$cfg['product_item_id'])->get('db_items')->row();
        $spec    = $product ? $this->get_spec($product->id, $store_id) : null;
        $is_roll_product = $spec && $spec->item_class === 'film_roll';
        $input   = (int)($cfg['input_item_id'] ?? 0);
        $roll    = (int)($cfg['roll_item_id'] ?? 0);
        $input_spec = $input ? $this->get_spec($input, $store_id) : null;
        $input_is_roll = $input_spec && $input_spec->item_class === 'film_roll';
        if ($input_is_roll && !$roll) $roll = $input;

        $print = !empty($cfg['print']) || ($spec && $spec->print_type && $spec->print_type !== 'none');
        $needs_conversion = $spec && $spec->item_class === 'finished_good' && ($spec->product_form === 'bag' || !$is_roll_product);

        $stages = [];
        $seq = 1;
        $stages[] = [
            'seq'=>$seq++,'stage_key'=>'material_allocation','input_item_id'=>$input ?: null,
            'output_item_id'=>null,'planned_input_qty'=>$cfg['planned_input_qty'] ?? null,
            'notes'=>'Reserve input material for the job',
        ];
        if ($flags['extrusion'] && !$input_is_roll) {
            $stages[] = [
                'seq'=>$seq++,'stage_key'=>'extrusion','input_item_id'=>$input ?: null,
                'output_item_id'=>$is_roll_product ? null : ($roll ?: null),
                'planned_output_qty'=>$is_roll_product ? ($cfg['planned_qty'] ?? null) : null,
                'notes'=>'Extrude film into rolls',
            ];
        }
        if ($print && !$is_roll_product) {
            $stages[] = [
                'seq'=>$seq++,'stage_key'=>'printing','input_item_id'=>$roll ?: null,
                'output_item_id'=>$roll ?: null,'requires_artwork'=>1,
                'notes'=>'Print artwork on the film',
            ];
        }
        if ($needs_conversion && $flags['conversion']) {
            $stages[] = [
                'seq'=>$seq++,'stage_key'=>'cutting','input_item_id'=>$roll ?: null,
                'output_item_id'=>$product ? $product->id : null,
                'planned_output_qty'=>$cfg['planned_qty'] ?? null,
                'notes'=>'Cut, seal and punch to size',
            ];
            $stages[] = [
                'seq'=>$seq++,'stage_key'=>'packing','input_item_id'=>$product ? $product->id : null,
                'output_item_id'=>$product ? $product->id : null,
                'notes'=>'Bundle / carton the finished bags',
            ];
        }
        $stages[] = [
            'seq'=>$seq++,'stage_key'=>'qc',
            'input_item_id'=>$product ? $product->id : null,
            'output_item_id'=>$product ? $product->id : null,
            'planned_output_qty'=>$cfg['planned_qty'] ?? null,
            'notes'=>'Verify output before stock is released',
        ];
        return $stages;
    }

    public function save_job($data, $stage_cfg = null, $id = null) {
        $this->db->trans_begin();
        try {
            if ($id) {
                $this->db->where('id',$id)->where('store_id',get_current_store_id())->update('db_nylon_jobs',$data);
                $job_id = $id;
            } else {
                $store_id = get_current_store_id();
                $prefix = 'JOB-' . date('Ymd') . '-';
                $last = $this->db->like('job_code',$prefix,'after')->where('store_id',$store_id)
                    ->order_by('id','DESC')->limit(1)->get('db_nylon_jobs')->row();
                $next = $last ? ((int)substr($last->job_code, strrpos($last->job_code,'-')+1) + 1) : 1;
                $data['job_code'] = $prefix . str_pad($next,3,'0',STR_PAD_LEFT);
                $data['store_id'] = $store_id;
                if (empty($data['warehouse_id'])) $data['warehouse_id'] = get_store_warehouse_id();
                $this->db->insert('db_nylon_jobs',$data);
                $job_id = $this->db->insert_id();
            }
            if (!$id && $stage_cfg) {
                $plan = $this->build_stage_plan($stage_cfg);
                $keys = [];
                foreach ($plan as $st) {
                    $st['job_id'] = $job_id;
                    $st['store_id'] = get_current_store_id();
                    $this->db->insert('db_nylon_job_stages',$st);
                    $keys[] = $st['stage_key'];
                }
                $this->db->where('id',$job_id)->update('db_nylon_jobs',['pipeline'=>implode(',',$keys)]);
            }
            $this->db->trans_commit();
            return $job_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error','Nylon save_job failed: '.$e->getMessage());
            return false;
        }
    }

    public function set_job_status($job_id, $status) {
        if (!in_array($status, self::job_statuses(), true)) return false;
        $data = ['status'=>$status];
        if ($status === 'in_progress' || $status === 'planned') {
            // nothing extra
        }
        return $this->db->where('id',$job_id)->where('store_id',get_current_store_id())->update('db_nylon_jobs',$data);
    }

    /* ============================ stock engine ============================ */

    /**
     * Post a set of signed stock moves through the shared stock-adjustment
     * engine. $moves = [ ['item_id'=>x,'qty'=>±n,'description'=>..], ... ].
     * Returns the adjustment id or false.
     */
    private function post_stock_moves($reference, array $moves, $note, $warehouse_id = null) {
        $moves = array_values(array_filter($moves, function($m){ return !empty($m['item_id']) && (float)$m['qty'] != 0.0; }));
        if (empty($moves)) return 0;
        $store_id = get_current_store_id();
        if (empty($warehouse_id)) $warehouse_id = get_store_warehouse_id();

        $adj = [
            'store_id'        => $store_id,
            'warehouse_id'    => $warehouse_id,
            'reference_no'    => $reference,
            'adjustment_date' => date('Y-m-d'),
            'adjustment_note' => $note,
            'created_date'    => date('Y-m-d'),
            'created_time'    => date('H:i:s'),
            'created_by'      => $this->session->userdata('username') ?: 'System',
            'system_ip'       => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'system_name'     => 'Nylon Production',
            'status'          => 1,
        ];
        if (!$this->db->insert('db_stockadjustment',$adj)) return false;
        $adjustment_id = $this->db->insert_id();
        if (!$adjustment_id) return false;

        $affected = [];
        foreach ($moves as $m) {
            $this->db->insert('db_stockadjustmentitems', [
                'store_id'       => $store_id,
                'warehouse_id'   => $warehouse_id,
                'adjustment_id'  => $adjustment_id,
                'item_id'        => $m['item_id'],
                'adjustment_qty' => $m['qty'],
                'description'    => $m['description'] ?? $note,
                'status'         => 1,
            ]);
            $affected[] = [$m['item_id']];
        }

        $this->load->model('pos_model');
        foreach ($affected as $row) {
            if (!$this->pos_model->update_items_quantity($row[0])) return false;
        }
        if (!update_warehouse_items($affected)) return false;
        return $adjustment_id;
    }

    /**
     * Does this stage log move real stock, and how?
     * Product item (job output) is never moved by intermediate stages — only
     * the QC stage credits it, on approval. This is the guard that keeps
     * unverified bags/film out of saleable stock and stops rejects from
     * inflating it.
     */
    private function _log_stock_moves($job, $stage, $log) {
        $moves = [];
        $in  = (int)$stage->input_item_id;
        $out = (int)$stage->output_item_id;
        $prod= (int)$job->product_item_id;
        $ref = $job->job_code . '/' . self::stage_label($stage->stage_key);

        if ($stage->stage_key === 'material_allocation') {
            return $moves; // reservation only — consumption posts at the working stage
        }
        if ($stage->stage_key === 'qc') {
            // Credit good output of the product item. qty_in is informational
            // (the bags/film were never stocked), so no input deduction here.
            if ($out && (float)$log->good_qty > 0) {
                $moves[] = ['item_id'=>$out,'qty'=>(float)$log->good_qty,'description'=>$ref.' — QC-approved output'];
            }
        } else {
            if ($in && $in !== $prod && (float)$log->qty_in > 0) {
                $moves[] = ['item_id'=>$in,'qty'=>-(float)$log->qty_in,'description'=>$ref.' — input consumed'];
            }
            if ($out && $out !== $prod && (float)$log->good_qty > 0) {
                $moves[] = ['item_id'=>$out,'qty'=>(float)$log->good_qty,'description'=>$ref.' — output'];
            }
        }
        if (!empty($log->scrap_item_id) && (float)$log->scrap_qty > 0) {
            $moves[] = ['item_id'=>(int)$log->scrap_item_id,'qty'=>(float)$log->scrap_qty,'description'=>$ref.' — reusable scrap recovered'];
        }
        return $moves;
    }

    /* ============================ reporting =============================== */

    /**
     * Submit a shift report against a stage. Operators report; stock posts
     * immediately for working stages, while QC logs wait for approval before
     * crediting the product item.
     * Returns [success,message,log_id].
     */
    public function report_log($job_id, $stage_id, $d) {
        $job   = $this->get_job($job_id);
        $stage = $this->db->where('id',$stage_id)->where('job_id',$job_id)->get('db_nylon_job_stages')->row();
        if (!$job || !$stage) return ['success'=>false,'message'=>'Job or stage not found.'];
        if ($job->status === 'completed' || $job->status === 'cancelled') {
            return ['success'=>false,'message'=>'Job is '.self::job_status_label($job->status).'.'];
        }
        if ($stage->status === 'done' || $stage->status === 'skipped') {
            return ['success'=>false,'message'=>'This stage is already closed.'];
        }
        if (!empty($stage->requires_artwork) && $job->custom_order_id && !$this->artwork_ready($job->custom_order_id)) {
            return ['success'=>false,'message'=>'Printing cannot start — the artwork on this order has not been approved.'];
        }
        $qty_in   = max(0,(float)($d['qty_in'] ?? 0));
        $good     = max(0,(float)($d['good_qty'] ?? 0));
        $reject   = max(0,(float)($d['reject_qty'] ?? 0));
        $scrap    = max(0,(float)($d['scrap_qty'] ?? 0));
        $waste    = max(0,(float)($d['waste_qty'] ?? 0));
        if ($qty_in <= 0 && $good <= 0 && $reject <= 0) {
            return ['success'=>false,'message'=>'Nothing to report — enter input, output or rejects.'];
        }

        $unit_cost = 0;
        // material_allocation is a reservation only — consumption (and its
        // cost) is recorded by the stage that actually uses the material.
        // Inputs produced by an earlier stage of THIS job (e.g. a roll made by
        // extrusion, then consumed by printing/cutting) carry no new material
        // cost — the resin was already costed upstream. Same no-double-count
        // rule as the stock ledger.
        if ($stage->stage_key !== 'material_allocation' && $stage->input_item_id
            && $stage->input_item_id != $job->product_item_id && $qty_in > 0) {
            $produced_in_job = $this->db->where('job_id',$job_id)
                ->where('output_item_id',$stage->input_item_id)
                ->where('id !=',$stage->id)
                ->count_all_results('db_nylon_job_stages') > 0;
            if (!$produced_in_job) {
                $in_item = $this->db->select('purchase_price')->where('id',$stage->input_item_id)->get('db_items')->row();
                $unit_cost = (float)($in_item->purchase_price ?? 0);
            }
        }

        $log = [
            'store_id'    => get_current_store_id(),
            'job_id'      => $job_id,
            'stage_id'    => $stage_id,
            'shift_label' => $d['shift_label'] ?? null,
            'machine_id'  => (int)($d['machine_id'] ?? 0) ?: null,
            'operator_id' => (int)($d['operator_id'] ?? 0) ?: null,
            'work_date'   => !empty($d['work_date']) ? $d['work_date'] : date('Y-m-d'),
            'qty_in'      => $qty_in,
            'good_qty'    => $good,
            'reject_qty'  => $reject,
            'scrap_qty'   => $scrap,
            'waste_qty'   => $waste,
            'scrap_item_id' => (int)($d['scrap_item_id'] ?? 0) ?: null,
            'unit_cost'   => $unit_cost,
            'material_cost' => round($qty_in * $unit_cost, 2),
            'notes'       => $d['notes'] ?? null,
            'status'      => 'submitted',
            'submitted_by'=> $this->session->userdata('username') ?: 'System',
        ];
        $this->db->insert('db_nylon_job_logs',$log);
        $log_id = $this->db->insert_id();
        $log['id'] = $log_id;

        if ($stage->status === 'pending') {
            $this->db->where('id',$stage_id)->update('db_nylon_job_stages',['status'=>'in_progress','started_at'=>date('Y-m-d H:i:s')]);
        }
        if ($job->status === 'planned') {
            $this->set_job_status($job_id,'in_progress');
        }

        // Working stages post now; the QC stage waits for supervisor approval.
        if ($stage->stage_key !== 'qc') {
            $adj = $this->post_stock_moves(
                $job->job_code.' [PROD]',
                $this->_log_stock_moves($job,$stage,(object)$log),
                'Nylon job '.$job->job_code.' — '.self::stage_label($stage->stage_key),
                $job->warehouse_id
            );
            if ($adj === false) {
                log_message('error','Nylon report_log: stock posting failed for log '.$log_id);
            } elseif ($adj > 0) {
                $this->db->where('id',$log_id)->update('db_nylon_job_logs',['adjustment_id'=>$adj]);
            }
        }
        $this->_recalc_job_material_cost($job_id);
        return ['success'=>true,'message'=>'Report saved.','log_id'=>$log_id];
    }

    /**
     * Supervisor approval. For QC logs this is what releases the finished
     * output into saleable stock.
     */
    public function approve_log($log_id) {
        $log = $this->db->where('id',$log_id)->get('db_nylon_job_logs')->row();
        if (!$log || $log->status !== 'submitted') return ['success'=>false,'message'=>'Nothing to approve.'];
        $job   = $this->get_job($log->job_id);
        $stage = $this->db->where('id',$log->stage_id)->get('db_nylon_job_stages')->row();
        if (!$job || !$stage) return ['success'=>false,'message'=>'Job or stage missing.'];

        if ($stage->stage_key === 'qc' && empty($log->adjustment_id)) {
            $adj = $this->post_stock_moves(
                $job->job_code.' [QC]',
                $this->_log_stock_moves($job,$stage,$log),
                'Nylon job '.$job->job_code.' — QC-approved release',
                $job->warehouse_id
            );
            if ($adj === false) return ['success'=>false,'message'=>'Stock posting failed — check the error log.'];
            if ($adj > 0) {
                $this->db->where('id',$log_id)->update('db_nylon_job_logs',['adjustment_id'=>$adj]);
            }
        }
        $this->db->where('id',$log_id)->update('db_nylon_job_logs',[
            'status'=>'approved','approved_by'=>$this->session->userdata('username') ?: 'System','approved_at'=>date('Y-m-d H:i:s'),
        ]);
        $this->_recalc_job_material_cost($log->job_id);
        return ['success'=>true,'message'=>'Report approved.'];
    }

    /**
     * Reverse a posted report — posts a counter-adjustment so the ledger keeps
     * both sides of the correction. Supervisor-only (controller enforces).
     */
    public function reverse_log($log_id, $reason = '') {
        $log = $this->db->where('id',$log_id)->get('db_nylon_job_logs')->row();
        if (!$log || $log->status === 'reversed') return ['success'=>false,'message'=>'Cannot reverse this report.'];
        $job   = $this->get_job($log->job_id);
        $stage = $this->db->where('id',$log->stage_id)->get('db_nylon_job_stages')->row();
        if (!$job || !$stage) return ['success'=>false,'message'=>'Job or stage missing.'];

        if (!empty($log->adjustment_id)) {
            $moves = $this->_log_stock_moves($job,$stage,$log);
            foreach ($moves as &$m) { $m['qty'] = -$m['qty']; $m['description'] = 'REVERSAL — '.$m['description']; }
            unset($m);
            $adj = $this->post_stock_moves(
                $job->job_code.' [REV]',
                $moves,
                'Reversal of report #'.$log_id.' on '.$job->job_code.($reason ? ': '.$reason : ''),
                $job->warehouse_id
            );
            if ($adj === false) return ['success'=>false,'message'=>'Reversal posting failed.'];
            if ($adj > 0) {
                $this->db->where('id',$log_id)->update('db_nylon_job_logs',['reversal_adjustment_id'=>$adj]);
            }
        }
        $this->db->where('id',$log_id)->update('db_nylon_job_logs',[
            'status'=>'reversed','reversed_by'=>$this->session->userdata('username') ?: 'System',
            'reversed_at'=>date('Y-m-d H:i:s'),'reversal_reason'=>$reason ?: null,
        ]);
        $this->_recalc_job_material_cost($log->job_id);
        return ['success'=>true,'message'=>'Report reversed and stock corrected.'];
    }

    /** Mark a stage done (supervisor) once operators have finished reporting. */
    public function complete_stage($stage_id) {
        $stage = $this->db->where('id',$stage_id)->get('db_nylon_job_stages')->row();
        if (!$stage || $stage->status === 'done') return false;
        $this->db->where('id',$stage_id)->update('db_nylon_job_stages',['status'=>'done','completed_at'=>date('Y-m-d H:i:s')]);
        return true;
    }

    public function skip_stage($stage_id) {
        return $this->db->where('id',$stage_id)->update('db_nylon_job_stages',['status'=>'skipped']);
    }

    /**
     * Complete a job: every non-skipped stage must be done and every QC log
     * approved. Marks the linked customer order ready for dispatch.
     */
    public function complete_job($job_id) {
        $job = $this->get_job($job_id);
        if (!$job || $job->status === 'completed') return ['success'=>false,'message'=>'Job not found or already completed.'];
        $stages = $this->get_stages($job_id);
        foreach ($stages as $s) {
            if (!in_array($s->status,['done','skipped'],true)) {
                return ['success'=>false,'message'=>'Stage "'.self::stage_label($s->stage_key).'" is not finished yet.'];
            }
        }
        $pending_qc = $this->db->select('l.id')->from('db_nylon_job_logs l')
            ->join('db_nylon_job_stages s','s.id = l.stage_id')
            ->where('l.job_id',$job_id)->where('s.stage_key','qc')->where('l.status','submitted')
            ->count_all_results();
        if ($pending_qc > 0) {
            return ['success'=>false,'message'=>$pending_qc.' QC report(s) still awaiting approval.'];
        }
        $this->db->where('id',$job_id)->update('db_nylon_jobs',[
            'status'=>'completed','completed_at'=>date('Y-m-d H:i:s'),
            'approved_by'=>$this->session->userdata('username') ?: 'System',
        ]);
        if ($job->custom_order_id) {
            $this->load->model('custom_orders_model','custom_orders');
            $order = $this->custom_orders->get($job->custom_order_id);
            if ($order && !in_array($order->status,['delivered','cancelled'],true)) {
                $this->custom_orders->save(['status'=>'ready'],$job->custom_order_id);
            }
        }
        $this->_recalc_job_material_cost($job_id);
        return ['success'=>true,'message'=>'Job completed.'];
    }

    private function _recalc_job_material_cost($job_id) {
        $mat = $this->db->select('COALESCE(SUM(material_cost),0) c')->from('db_nylon_job_logs')
            ->where('job_id',$job_id)->where('status !=','reversed')->get()->row()->c;
        $other = $this->db->select('COALESCE(SUM(amount),0) c')->from('db_nylon_job_costs')
            ->where('job_id',$job_id)->where('estimated',0)->get()->row()->c;
        $this->db->where('id',$job_id)->update('db_nylon_jobs',[
            'act_material_cost'=>round((float)$mat,2),'act_other_cost'=>round((float)$other,2),
        ]);
    }

    /* ============================ costs & reports ========================= */

    public function add_cost($job_id, $d) {
        $d['job_id'] = $job_id;
        $d['store_id'] = get_current_store_id();
        $d['created_by'] = $this->session->userdata('username') ?: 'System';
        $ok = $this->db->insert('db_nylon_job_costs',$d);
        if ($ok && empty($d['estimated'])) $this->_recalc_job_material_cost($job_id);
        return $ok;
    }

    public function get_costs($job_id) {
        return $this->db->where('job_id',$job_id)->order_by('id','asc')->get('db_nylon_job_costs')->result();
    }

    public function delete_cost($id) {
        $row = $this->db->where('id',$id)->where('store_id',get_current_store_id())->get('db_nylon_job_costs')->row();
        if (!$row) return false;
        $ok = $this->db->where('id',$id)->delete('db_nylon_job_costs');
        if ($ok) $this->_recalc_job_material_cost($row->job_id);
        return $ok;
    }

    /**
     * Per-job analysis: input vs output per stage, estimated vs actual cost,
     * yield, waste split and gross margin against the linked order value.
     */
    public function job_report($job_id) {
        $job    = $this->get_job($job_id);
        if (!$job) return null;
        $stages = $this->get_stages($job_id);
        $logs   = array_filter($this->get_logs($job_id), function($l){ return $l->status !== 'reversed'; });
        $costs  = $this->get_costs($job_id);

        $stage_totals = [];
        foreach ($stages as $s) {
            $stage_totals[$s->id] = ['stage'=>$s,'in'=>0,'good'=>0,'reject'=>0,'scrap'=>0,'waste'=>0,'pending_approval'=>0];
        }
        $material_cost = 0;
        foreach ($logs as $l) {
            if (isset($stage_totals[$l->stage_id])) {
                $t =& $stage_totals[$l->stage_id];
                $t['in'] += (float)$l->qty_in; $t['good'] += (float)$l->good_qty;
                $t['reject'] += (float)$l->reject_qty; $t['scrap'] += (float)$l->scrap_qty; $t['waste'] += (float)$l->waste_qty;
                if ($l->status === 'submitted') $t['pending_approval']++;
            }
            $material_cost += (float)$l->material_cost;
        }
        $est_other = 0; $act_other = 0;
        foreach ($costs as $c) { if ($c->estimated) $est_other += (float)$c->amount; else $act_other += (float)$c->amount; }

        $total_good = 0; $total_reject = 0; $total_scrap = 0; $total_waste = 0;
        foreach ($logs as $l) {
            if ($l->stage_key === 'qc') { $total_good += (float)$l->good_qty; }
            $total_reject += (float)$l->reject_qty; $total_scrap += (float)$l->scrap_qty; $total_waste += (float)$l->waste_qty;
        }
        $planned = (float)$job->planned_qty;
        $est_total  = (float)$job->est_material_cost + (float)$job->est_other_cost + $est_other;
        $act_total  = $material_cost + $act_other;
        $order      = $job->custom_order_id ? $this->db->where('id',$job->custom_order_id)->get('db_custom_orders')->row() : null;
        $revenue    = $order ? (float)$order->total_amount : 0;
        $margin     = $revenue - $act_total;

        return [
            'job'=>$job,'stages'=>$stage_totals,'logs'=>$logs,'costs'=>$costs,
            'material_cost'=>$material_cost,'est_other'=>$est_other,'act_other'=>$act_other,
            'est_total'=>$est_total,'act_total'=>$act_total,
            'planned_qty'=>$planned,'produced_qty'=>$total_good,
            'yield_pct'=> $planned > 0 ? round($total_good/$planned*100,1) : null,
            'reject_qty'=>$total_reject,'scrap_qty'=>$total_scrap,'waste_qty'=>$total_waste,
            'order'=>$order,'revenue'=>$revenue,'margin'=>$margin,
            'margin_pct'=> $revenue > 0 ? round($margin/$revenue*100,1) : null,
        ];
    }

    /* ============================ dashboard =============================== */

    public function dashboard_stats($store_id = null, $warehouse_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $open = ['planned','in_progress','on_hold'];
        $this->db->from('db_nylon_jobs')->where('store_id',$store_id)->where_in('status',$open);
        if ($warehouse_id) $this->db->where('warehouse_id',$warehouse_id);
        $open_jobs = $this->db->count_all_results();

        $this->db->from('db_nylon_jobs')->where('store_id',$store_id)->where_in('status',$open)
            ->where('due_date IS NOT NULL')->where('due_date <=',date('Y-m-d',strtotime('+7 days')));
        if ($warehouse_id) $this->db->where('warehouse_id',$warehouse_id);
        $due_soon = $this->db->count_all_results();

        $pending_qc = $this->db->select('l.id')->from('db_nylon_job_logs l')
            ->join('db_nylon_job_stages s','s.id = l.stage_id')->join('db_nylon_jobs j','j.id = l.job_id')
            ->where('l.store_id',$store_id)->where('s.stage_key','qc')->where('l.status','submitted')
            ->count_all_results();

        return ['open_jobs'=>$open_jobs,'due_soon'=>$due_soon,'pending_qc'=>$pending_qc];
    }

    /** Stock availability for raw materials and film rolls. */
    public function material_stock($store_id = null, $warehouse_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $wh = $warehouse_id ?: get_store_warehouse_id();
        $this->db->select('s.item_class, i.id, i.item_name, i.stock, u.unit_name, s.kg_per_roll')
            ->from('db_nylon_item_specs s')
            ->join('db_items i','i.id = s.item_id')
            ->join('db_units u','u.id = i.unit_id','left')
            ->where('s.store_id',$store_id)->where('s.status',1)
            ->where_in('s.item_class',['raw_material','film_roll'])
            ->order_by('s.item_class','asc')->order_by('i.item_name','asc');
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->available = $this->db->table_exists('db_warehouseitems')
                ? (float)$this->db->select('COALESCE(SUM(available_qty),0) q')->where('item_id',$r->id)->where('warehouse_id',$wh)->where('store_id',$store_id)->get('db_warehouseitems')->row()->q
                : (float)$r->stock;
        }
        return $rows;
    }

    /** Good output vs rejects grouped by machine (or unassigned). */
    public function output_by_machine($store_id = null, $days = 30) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->select("COALESCE(m.machine_name,'Unassigned') machine_name, s.stage_key,
                SUM(l.good_qty) good, SUM(l.reject_qty) rejects, SUM(l.scrap_qty) scrap, SUM(l.waste_qty) waste")
            ->from('db_nylon_job_logs l')
            ->join('db_nylon_job_stages s','s.id = l.stage_id')
            ->join('db_nylon_machines m','m.id = l.machine_id','left')
            ->where('l.store_id',$store_id)->where('l.status !=','reversed')
            ->where('l.work_date >=',date('Y-m-d',strtotime('-'.(int)$days.' days')))
            ->group_by(['machine_name','s.stage_key'])
            ->order_by('machine_name','asc')->get()->result();
    }

    /** Good vs rejects grouped by product item. */
    public function output_by_product($store_id = null, $days = 30) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->select("i.item_name, s.stage_key, SUM(l.good_qty) good, SUM(l.reject_qty) rejects")
            ->from('db_nylon_job_logs l')
            ->join('db_nylon_job_stages s','s.id = l.stage_id')
            ->join('db_nylon_jobs j','j.id = l.job_id')
            ->join('db_items i','i.id = j.product_item_id','left')
            ->where('l.store_id',$store_id)->where('l.status !=','reversed')
            ->where('l.work_date >=',date('Y-m-d',strtotime('-'.(int)$days.' days')))
            ->group_by(['i.item_name','s.stage_key'])->order_by('i.item_name','asc')->get()->result();
    }

    /** Open jobs with their order/due dates for the dashboard. */
    public function open_jobs($store_id = null, $warehouse_id = null, $limit = 10) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('j.*, i.item_name, o.order_code, c.customer_name')
            ->from('db_nylon_jobs j')
            ->join('db_items i','i.id = j.product_item_id','left')
            ->join('db_custom_orders o','o.id = j.custom_order_id','left')
            ->join('db_customers c','c.id = o.customer_id','left')
            ->where('j.store_id',$store_id)->where_in('j.status',['planned','in_progress','on_hold'])
            ->order_by('j.due_date IS NULL', 'asc', FALSE)->order_by('j.due_date','asc')->limit((int)$limit);
        if ($warehouse_id) $this->db->where('j.warehouse_id',$warehouse_id);
        return $this->db->get()->result();
    }

    /** Outstanding balances across nylon job orders. */
    public function outstanding_balances($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->select('o.id, o.order_code, o.total_amount, o.deposit_paid, o.balance_due, o.due_date, c.customer_name')
            ->from('db_custom_orders o')->join('db_customers c','c.id = o.customer_id','left')
            ->where('o.store_id',$store_id)->where('o.workflow_template_key','nylon')
            ->where('o.balance_due >',0)->where('o.status !=','cancelled')
            ->order_by('o.due_date','asc')->get()->result();
    }

    /** Profit per job for the reports screen. */
    public function profit_by_job($store_id = null, $warehouse_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $jobs = $this->get_jobs($store_id, null, $warehouse_id);
        $out = [];
        foreach ($jobs as $j) {
            $r = $this->job_report($j->id);
            $out[] = ['job'=>$j,'report'=>$r];
        }
        return $out;
    }

    /* ============================ demo seeder ============================= */

    /**
     * Seed realistic demo data covering the six acceptance scenarios and
     * return a structured report of what was created plus the resulting
     * inventory positions and job margins. Idempotent: refuses to run twice.
     */
    public function seed_demo() {
        $store_id = get_current_store_id();
        // Marker: the demo resin item. Anything else could collide with real jobs.
        if ($this->db->where('store_id',$store_id)->where('item_name','LDPE Resin (Virgin)')->count_all_results('db_items') > 0) {
            return ['seeded'=>false,'message'=>'Demo data already exists (item "LDPE Resin (Virgin)" found).'];
        }
        $this->load->model('pos_model');
        $wh = get_store_warehouse_id();
        $uid = get_current_user_id();

        $unit = function($name) use ($store_id) {
            $u = $this->db->where('store_id',$store_id)->where('unit_name',$name)->get('db_units')->row();
            if ($u) return $u->id;
            $this->db->insert('db_units',['store_id'=>$store_id,'unit_name'=>$name,'shortcode'=>strtoupper(substr($name,0,4)),'status'=>1,'conversion_factor'=>1,'is_default'=>0]);
            return $this->db->insert_id();
        };
        $u_kg = $unit('Kilogram'); $u_pcs = $unit('Piece'); $u_roll = $unit('Roll'); $u_bundle = $unit('Bundle'); $u_carton = $unit('Carton');

        $mk_item = function($name,$class,$unit_id,$price,$cost,$not_for_sale,$spec=[]) use ($store_id) {
            $this->db->insert('db_items',[
                'store_id'=>$store_id,'item_name'=>$name,'unit_id'=>$unit_id,
                'item_code'=>get_init_code('item'),'sales_price'=>$price,'purchase_price'=>$cost,
                'price'=>$cost,'stock'=>0,'not_for_sale'=>$not_for_sale,'status'=>1,
                'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>'Demo',
            ]);
            $id = $this->db->insert_id();
            $spec['item_class'] = $class;
            $this->save_spec($id,$spec,$store_id);
            return $id;
        };
        $stock_in = function($item_id,$qty,$note) use ($wh) {
            return $this->post_stock_moves('DEMO-IN', [['item_id'=>$item_id,'qty'=>$qty,'description'=>$note]], 'Demo opening stock', $wh);
        };

        // --- items -------------------------------------------------------------
        $resin  = $mk_item('LDPE Resin (Virgin)','raw_material',$u_kg,0,800,1,['material'=>'LDPE']);
        $mb     = $mk_item('White Masterbatch','raw_material',$u_kg,0,1200,1,['material'=>'Other']);
        $ink    = $mk_item('Flexo Ink - Red','consumable',$u_kg,0,2500,1,[]);
        $scrapI = $mk_item('LDPE Regrind (Scrap)','raw_material',$u_kg,0,300,1,['material'=>'Recycled']);
        $rollW  = $mk_item('LDPE Film Roll 30cm White 25kg','film_roll',$u_roll,26000,22000,0,['material'=>'LDPE','product_form'=>'film_roll','width_cm'=>30,'thickness_micron'=>40,'colour'=>'White','kg_per_roll'=>25]);
        $rollP  = $mk_item('HDPE Film Roll 45cm Natural (Purchased)','film_roll',$u_roll,24000,20000,0,['material'=>'HDPE','product_form'=>'film_roll','width_cm'=>45,'thickness_micron'=>30,'colour'=>'Natural','kg_per_roll'=>25]);
        $bagS   = $mk_item('Shoprite-style Printed Vest Bag 20x30','finished_good',$u_pcs,30,0,0,['material'=>'LDPE','product_form'=>'bag','bag_type'=>'vest','width_cm'=>20,'length_cm'=>30,'thickness_micron'=>40,'colour'=>'White','print_type'=>'flexo','design_ref'=>'DSN-SR-2026-014','kg_per_piece'=>0.025]);
        $bagM   = $mk_item('Plain Flat Bag 30x45 Clear','finished_good',$u_pcs,12,0,0,['material'=>'HDPE','product_form'=>'bag','bag_type'=>'flat','width_cm'=>30,'length_cm'=>45,'thickness_micron'=>30,'colour'=>'Clear','print_type'=>'none','kg_per_piece'=>0.02]);

        // selling units — explicit per-product conversions, never assumed
        $this->db->insert('db_item_selling_units',['store_id'=>$store_id,'item_id'=>$bagS,'unit_id'=>$u_kg,'unit_shortcode'=>'KG','conversion_factor'=>40,'selling_price'=>1200,'wholesale_price'=>1100,'is_default'=>0,'status'=>1]);
        $this->db->insert('db_item_selling_units',['store_id'=>$store_id,'item_id'=>$bagS,'unit_id'=>$u_bundle,'unit_shortcode'=>'BUNDLE','conversion_factor'=>100,'selling_price'=>2900,'is_default'=>0,'status'=>1]);
        $this->db->insert('db_item_selling_units',['store_id'=>$store_id,'item_id'=>$bagS,'unit_id'=>$u_carton,'unit_shortcode'=>'CTN','conversion_factor'=>1000,'selling_price'=>28000,'is_default'=>0,'status'=>1]);
        $this->db->insert('db_item_selling_units',['store_id'=>$store_id,'item_id'=>$bagM,'unit_id'=>$u_bundle,'unit_shortcode'=>'BUNDLE','conversion_factor'=>50,'selling_price'=>580,'is_default'=>0,'status'=>1]);

        $stock_in($resin, 500, 'Scenario 1&5 resin stock');   // 500 kg resin
        $stock_in($mb, 50, 'Masterbatch');
        $stock_in($rollP, 40, 'Scenario 2&3 purchased film rolls'); // 40 rolls

        // machines
        $ex = null; $pr = null; $ct = null;
        $this->save_machine(['machine_code'=>'EX-01','machine_name'=>'Extruder EX-01','machine_type'=>'extruder']); $ex = $this->db->insert_id();
        $this->save_machine(['machine_code'=>'PR-02','machine_name'=>'Flexo Printer PR-02','machine_type'=>'printer']); $pr = $this->db->insert_id();
        $this->save_machine(['machine_code'=>'CT-01','machine_name'=>'Bag Cutter CT-01','machine_type'=>'cutter']); $ct = $this->db->insert_id();

        // customer
        $cust = $this->db->where('store_id',$store_id)->where('customer_name','Demo Retail Chain Ltd')->get('db_customers')->row();
        if (!$cust) {
            $this->db->insert('db_customers',['store_id'=>$store_id,'customer_name'=>'Demo Retail Chain Ltd','customer_code'=>get_init_code('customer'),'mobile'=>'08000000001','created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>'Demo','status'=>1]);
            $cust_id = $this->db->insert_id();
        } else { $cust_id = $cust->id; }

        $report = ['seeded'=>true,'items'=>['resin'=>$resin,'masterbatch'=>$mb,'ink'=>$ink,'scrap'=>$scrapI,'roll_white'=>$rollW,'roll_purchased'=>$rollP,'bag_printed'=>$bagS,'bag_plain'=>$bagM],'jobs'=>[],'orders'=>[]];

        $mk_order = function($item_id,$qty,$unit_id,$total,$deposit,$artwork,$design,$status='deposit_paid',$specs=null) use ($store_id,$cust_id) {
            $item = $this->db->where('id',$item_id)->get('db_items')->row();
            return $this->save_order([
                'store_id'=>$store_id,'customer_id'=>$cust_id,'item_id'=>$item_id,
                'item_name'=>$item->item_name,'order_qty'=>$qty,'order_unit_id'=>$unit_id,
                'artwork_required'=>$artwork,'design_ref'=>$design,
                'specifications_json'=>json_encode($specs ?: ['Width'=>'20 cm','Length'=>'30 cm','Thickness'=>'40 micron','Colour'=>'White','Print'=>'Flexo 2-colour']),
                'quoted_price'=>$total,'deposit_amount'=>$deposit,'deposit_paid'=>$deposit,
                'total_amount'=>$total,'balance_due'=>$total-$deposit,'status'=>$status,
                'notes'=>'Demo order','order_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d',strtotime('+5 days')),
            ]);
        };

        // ---------- Scenario 1: resin → film → printed bag -----------------------
        $o1 = $mk_order($bagS, 10000, $u_pcs, 300000, 150000, 1, 'DSN-SR-2026-014');
        $this->add_artwork($o1,'sr-vest-bag-v3.ai','uploads/artwork/sr-vest-bag-v3.ai','Approved by customer 2026-09-20');
        $art = $this->db->where('custom_order_id',$o1)->order_by('id','desc')->limit(1)->get('db_nylon_artworks')->row();
        $this->set_artwork_status($art->id,'approved');
        $report['orders']['scenario_1'] = $o1;

        $j1 = $this->save_job([
            'job_kind'=>'order','custom_order_id'=>$o1,'product_item_id'=>$bagS,
            'planned_qty'=>10000,'planned_unit_id'=>$u_pcs,'due_date'=>date('Y-m-d',strtotime('+5 days')),
            'est_material_cost'=>185000,'est_other_cost'=>25000,'notes'=>'Resin → extrude → print → cut → pack → QC',
            'created_by'=>'Demo',
        ], ['product_item_id'=>$bagS,'input_item_id'=>$resin,'roll_item_id'=>$rollW,'planned_qty'=>10000,'planned_input_qty'=>260,'print'=>true]);
        // material allocation (records reservation, no stock move)
        $st = $this->get_stages($j1);
        $by = function($k) use ($st){ foreach($st as $s){ if($s->stage_key===$k) return $s; } return null; };
        $this->report_log($j1,$by('material_allocation')->id,['qty_in'=>262,'notes'=>'262 kg LDPE allocated']);
        // extrusion: 262 kg resin → 10 rolls (250 kg net) + 8 kg scrap + 4 kg waste
        $this->report_log($j1,$by('extrusion')->id,['qty_in'=>262,'good_qty'=>10,'reject_qty'=>0,'scrap_qty'=>8,'waste_qty'=>4,'scrap_item_id'=>$scrapI,'machine_id'=>$ex,'shift_label'=>'Morning']);
        // printing: 10 rolls in → 9.6 good rolls, 0.4 roll print rejects
        $this->report_log($j1,$by('printing')->id,['qty_in'=>10,'good_qty'=>9.6,'reject_qty'=>0.4,'machine_id'=>$pr,'shift_label'=>'Afternoon']);
        // cutting: 9.6 rolls → 9600 good bags + 300 rejects
        $this->report_log($j1,$by('cutting')->id,['qty_in'=>9.6,'good_qty'=>9600,'reject_qty'=>300,'machine_id'=>$ct,'shift_label'=>'Morning']);
        $this->report_log($j1,$by('packing')->id,['qty_in'=>9600,'good_qty'=>9600,'notes'=>'96 bundles of 100']);
        // qc: approve 9600 bags into stock
        $this->report_log($j1,$by('qc')->id,['qty_in'=>9600,'good_qty'=>9600,'reject_qty'=>0,'notes'=>'Drop & seal tests passed']);
        $qc_log = $this->db->where('job_id',$j1)->where('stage_id',$by('qc')->id)->order_by('id','desc')->limit(1)->get('db_nylon_job_logs')->row();
        $this->approve_log($qc_log->id);
        foreach (['material_allocation','extrusion','printing','cutting','packing','qc'] as $k) { $this->complete_stage($by($k)->id); }
        $this->complete_job($j1);
        $report['jobs']['scenario_1'] = $j1;

        // ---------- Scenario 2: purchased roll → plain bag -----------------------
        $o2 = $mk_order($bagM, 5000, $u_pcs, 130000, 65000, 0, null, 'deposit_paid',
            ['Width'=>'30 cm','Length'=>'45 cm','Thickness'=>'30 micron','Colour'=>'Clear','Print'=>'None']);
        $report['orders']['scenario_2'] = $o2;
        $j2 = $this->save_job([
            'job_kind'=>'order','custom_order_id'=>$o2,'product_item_id'=>$bagM,
            'planned_qty'=>5000,'planned_unit_id'=>$u_pcs,'due_date'=>date('Y-m-d',strtotime('+4 days')),
            'est_material_cost'=>20000,'est_other_cost'=>8000,'notes'=>'Purchased HDPE rolls → cut → pack → QC','created_by'=>'Demo',
        ], ['product_item_id'=>$bagM,'input_item_id'=>$rollP,'roll_item_id'=>$rollP,'planned_qty'=>5000,'print'=>false]);
        $st2 = $this->get_stages($j2);
        $by2 = function($k) use ($st2){ foreach($st2 as $s){ if($s->stage_key===$k) return $s; } return null; };
        $this->report_log($j2,$by2('material_allocation')->id,['qty_in'=>5,'notes'=>'5 purchased HDPE rolls allocated']);
        $this->report_log($j2,$by2('cutting')->id,['qty_in'=>5,'good_qty'=>5000,'reject_qty'=>120,'machine_id'=>$ct,'shift_label'=>'Morning']);
        $this->report_log($j2,$by2('packing')->id,['qty_in'=>5000,'good_qty'=>5000,'notes'=>'100 bundles of 50']);
        $this->report_log($j2,$by2('qc')->id,['qty_in'=>5000,'good_qty'=>5000]);
        $qc2 = $this->db->where('job_id',$j2)->where('stage_id',$by2('qc')->id)->order_by('id','desc')->limit(1)->get('db_nylon_job_logs')->row();
        $this->approve_log($qc2->id);
        foreach (['material_allocation','cutting','packing','qc'] as $k) { $this->complete_stage($by2($k)->id); }
        $this->complete_job($j2);
        $report['jobs']['scenario_2'] = $j2;

        // ---------- Scenario 3: film-roll-only sale (no conversion, no job) ------
        $o3 = $mk_order($rollP, 10, $u_roll, 240000, 240000, 0, null, 'delivered',
            ['Material'=>'HDPE','Width'=>'45 cm','Thickness'=>'30 micron','Colour'=>'Natural','Form'=>'Film roll — sold as-is']);
        $this->db->where('id',$o3)->update('db_custom_orders',['dispatched_qty'=>10,'delivery_date'=>date('Y-m-d')]);
        // the sale deducts the rolls from stock — posted through the same ledger
        $this->post_stock_moves('DEMO-SALE-'.$o3, [['item_id'=>$rollP,'qty'=>-10,'description'=>'Film-roll sale — 10 rolls dispatched']], 'Demo roll sale CO-…', $wh);
        $report['orders']['scenario_3'] = $o3;

        // ---------- Scenario 4: branded repeat order ------------------------------
        $o4 = $this->repeat_order($o1, date('Y-m-d'), date('Y-m-d',strtotime('+9 days')));
        $this->db->where('id',$o4)->update('db_custom_orders',['status'=>'deposit_paid','deposit_paid'=>150000,'balance_due'=>150000]);
        $report['orders']['scenario_4'] = $o4;

        // ---------- Scenario 5: partial production + dispatch --------------------
        $j5 = $this->save_job([
            'job_kind'=>'order','custom_order_id'=>$o4,'product_item_id'=>$bagS,
            'planned_qty'=>10000,'planned_unit_id'=>$u_pcs,'due_date'=>date('Y-m-d',strtotime('+9 days')),
            'est_material_cost'=>185000,'est_other_cost'=>25000,'notes'=>'Repeat run — partial across shifts','created_by'=>'Demo',
        ], ['product_item_id'=>$bagS,'input_item_id'=>$resin,'roll_item_id'=>$rollW,'planned_qty'=>10000,'planned_input_qty'=>120,'print'=>true]);
        $st5 = $this->get_stages($j5);
        $by5 = function($k) use ($st5){ foreach($st5 as $s){ if($s->stage_key===$k) return $s; } return null; };
        $this->report_log($j5,$by5('material_allocation')->id,['qty_in'=>130,'notes'=>'130 kg LDPE allocated']);
        $this->report_log($j5,$by5('extrusion')->id,['qty_in'=>130,'good_qty'=>5,'scrap_qty'=>3,'waste_qty'=>2,'scrap_item_id'=>$scrapI,'machine_id'=>$ex,'shift_label'=>'Night']);
        $this->report_log($j5,$by5('printing')->id,['qty_in'=>5,'good_qty'=>4.9,'reject_qty'=>0.1,'machine_id'=>$pr,'shift_label'=>'Morning']);
        $this->report_log($j5,$by5('cutting')->id,['qty_in'=>4.9,'good_qty'=>4900,'reject_qty'=>80,'machine_id'=>$ct,'shift_label'=>'Afternoon']);
        $this->report_log($j5,$by5('packing')->id,['qty_in'=>4900,'good_qty'=>4900,'notes'=>'49 bundles packed']);
        $this->report_log($j5,$by5('qc')->id,['qty_in'=>4900,'good_qty'=>4900]);
        $qc5 = $this->db->where('job_id',$j5)->where('stage_id',$by5('qc')->id)->order_by('id','desc')->limit(1)->get('db_nylon_job_logs')->row();
        $this->approve_log($qc5->id);
        // job stays in_progress — second shift still to run; record a partial dispatch
        $this->db->where('id',$o4)->update('db_custom_orders',['dispatched_qty'=>4000,'status'=>'in_production']);
        $report['jobs']['scenario_5'] = $j5;

        // ---------- Scenario 6: rejects must not inflate finished stock -----------
        $j6 = $this->save_job([
            'job_kind'=>'stock','custom_order_id'=>null,'product_item_id'=>$bagM,
            'planned_qty'=>2000,'planned_unit_id'=>$u_pcs,'due_date'=>date('Y-m-d',strtotime('+3 days')),
            'est_material_cost'=>8000,'est_other_cost'=>2500,'notes'=>'Stock replenishment run with high reject rate','created_by'=>'Demo',
        ], ['product_item_id'=>$bagM,'input_item_id'=>$rollP,'roll_item_id'=>$rollP,'planned_qty'=>2000,'print'=>false]);
        $st6 = $this->get_stages($j6);
        $by6 = function($k) use ($st6){ foreach($st6 as $s){ if($s->stage_key===$k) return $s; } return null; };
        $this->report_log($j6,$by6('material_allocation')->id,['qty_in'=>2]);
        $this->report_log($j6,$by6('cutting')->id,['qty_in'=>2,'good_qty'=>1400,'reject_qty'=>600,'waste_qty'=>10,'machine_id'=>$ct,'shift_label'=>'Morning','notes'=>'Slitter misalignment — 600 bags rejected']);
        $this->report_log($j6,$by6('packing')->id,['qty_in'=>1400,'good_qty'=>1400]);
        $this->report_log($j6,$by6('qc')->id,['qty_in'=>1400,'good_qty'=>1400,'reject_qty'=>0]);
        $qc6 = $this->db->where('job_id',$j6)->where('stage_id',$by6('qc')->id)->order_by('id','desc')->limit(1)->get('db_nylon_job_logs')->row();
        $this->approve_log($qc6->id);
        foreach (['material_allocation','cutting','packing','qc'] as $k) { $this->complete_stage($by6($k)->id); }
        $this->complete_job($j6);
        $report['jobs']['scenario_6'] = $j6;

        // scenario 3 (film-roll-only sale) needs no job — purchased rolls are
        // saleable stock. Compute the resulting inventory snapshot.
        $report['inventory'] = [];
        foreach ($report['items'] as $key => $item_id) {
            $it = $this->db->select('i.item_name, i.stock, u.unit_name')->from('db_items i')->join('db_units u','u.id=i.unit_id','left')->where('i.id',$item_id)->get()->row();
            $report['inventory'][$key] = ['name'=>$it->item_name,'stock'=>(float)$it->stock,'unit'=>$it->unit_name];
        }
        $report['margins'] = [];
        foreach ($report['jobs'] as $k => $jid) {
            $report['margins'][$k] = $this->job_report($jid);
        }
        return $report;
    }
}
