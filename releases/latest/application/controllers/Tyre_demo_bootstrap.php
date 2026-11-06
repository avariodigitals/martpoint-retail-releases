<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tyre_demo_bootstrap — creates a disposable local Tyre Shop demo store so the
 * tyre workspace can be walked through in a real browser.
 *
 *   php index.php tyre_demo_bootstrap run       # store 9995 + owner login + seed
 *   php index.php tyre_demo_bootstrap clean     # remove everything created here
 *
 * Login (local only): zonattyres / martpoint123
 */
class Tyre_demo_bootstrap extends CI_Controller {

	const STORE_ID = 9995;

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
	}

	public function run(){
		$sid = self::STORE_ID;

		$store = $this->db->where('id', $sid)->get('db_store')->row();
		if(!$store){
			$ngn = $this->db->select('id')->where('currency_code', 'NGN')->get('db_currency')->row();
			$cur_id = $ngn ? (int)$ngn->id : (int)($this->db->select('id')->where('status', 1)->limit(1)->get('db_currency')->row()->id ?? 0);
			$this->db->insert('db_store', ['id'=>$sid, 'store_name'=>'Zonat Tyres Demo', 'user_id'=>0, 'status'=>1, 'language_id'=>1, 'currency_id'=>$cur_id, 'currency_placement'=>'Left', 'date_format'=>'dd-mm-yyyy', 'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s')]);
			echo "created store $sid\n";
		} else {
			echo "store $sid exists\n";
		}

		$this->load->model('default_data_model', 'seeder');
		$this->seeder->seed_store_defaults($sid);
		echo "store defaults seeded\n";

		// The installer normally creates the HQ outlet; mirror it for this store.
		$hq = $this->db->where('store_id', $sid)->order_by('id', 'asc')->get('db_warehouse')->row();
		if(!$hq){
			$this->db->insert('db_warehouse', [
				'store_id' => $sid, 'warehouse_type' => 'System', 'warehouse_name' => 'Warehouse-A',
				'branch_type' => 'branch', 'is_distribution_center' => 0, 'status' => 1,
				'delivery_fee' => 0, 'pickup_enabled' => 1, 'public_enabled' => 1,
			]);
			$hq = $this->db->where('id', $this->db->insert_id())->get('db_warehouse')->row();
			echo "warehouse created id={$hq->id}\n";
		}

		// Owner login
		$role = $this->db->select('id, role_name')->where('store_id', $sid)
			->where('UPPER(role_name)', 'BUSINESS OWNER')->where('status', 1)
			->limit(1)->get('db_roles')->row();
		if(!$role){ show_error('Business Owner role missing for demo store', 500); }
		$user = $this->db->where('store_id', $sid)->where('username', 'zonattyres')->get('db_users')->row();
		if(!$user){
			$next = (int)$this->db->select('COALESCE(MAX(id),0)+1 n')->get('db_users')->row()->n;
			$hq = $this->db->where('store_id', $sid)->order_by('id','asc')->get('db_warehouse')->row();
			$this->db->insert('db_users', [
				'id'=>$next, 'store_id'=>$sid, 'username'=>'zonattyres',
				'first_name'=>'Zonat', 'last_name'=>'Tyres',
				'email'=>'zonattyres@demo.local',
				'password'=>password_hash('martpoint123', PASSWORD_BCRYPT),
				'role_id'=>(int)$role->id, 'role_name'=>$role->role_name,
				'default_warehouse_id'=>$hq ? (int)$hq->id : null,
				'status'=>1, 'created_date'=>date('Y-m-d'), 'created_time'=>date('H:i:s a'), 'created_by'=>'tyre_demo_bootstrap',
			]);
			$user = $this->db->where('id', $next)->get('db_users')->row();
			if($hq){
				$nid = (int)$this->db->select('COALESCE(MAX(id),0)+1 n')->get('db_userswarehouses')->row()->n;
				$this->db->insert('db_userswarehouses', ['id'=>$nid, 'user_id'=>$user->id, 'warehouse_id'=>$hq->id]);
			}
			echo "owner user created id={$user->id}\n";
		} else {
			echo "owner user exists id={$user->id}\n";
		}
		$this->db->where('id', $sid)->update('db_store', ['user_id'=>(int)$user->id]);

		// Apply the tyre_shop preset exactly as Business Profile save does
		$this->load->model('business_profile_model', 'bp_model');
		$presets = mp_get_business_presets();
		if(empty($presets['tyre_shop'])){ show_error('tyre_shop preset missing', 500); }
		$preset = $presets['tyre_shop'];
		$flags = [];
		foreach(mp_get_feature_flags() as $key=>$label){
			$flags[$key] = in_array($key, $preset['features'] ?? [], true) ? '1' : '0';
		}
		$this->bp_model->update_profile($sid, [
			'industry_type'=>'tyre_shop',
			'business_model'=>$preset['business_model'] ?? 'product_based',
			'workflow_template_key'=>$preset['workflow_template'] ?? 'retail_standard',
			'dashboard_template_key'=>$preset['dashboard_template'] ?? 'general_retail',
			'storefront_theme_key'=>$preset['theme_key'] ?? 'general_retail',
			'feature_flags_json'=>json_encode($flags),
			'label_overrides_json'=>!empty($preset['labels']) ? json_encode($preset['labels']) : null,
			'industry_settings_json'=>null,
		]);
		$this->seeder->seed_industry_defaults($sid, 'tyre_shop');
		echo "tyre_shop profile applied\n";

		// Rich demo content (renames store to the tyre blueprint name)
		$this->load->model('Demo_seeder', 'demo_seeder');
		$res = $this->demo_seeder->seed_full_demo($sid, 'tyre_shop');
		echo json_encode($res)."\n";

		// Link each demo tyre to its seeded brand and size variant so the
		// size alerts, seasonal chart and till brand/size search show truth.
		$variants = array();
		foreach ($this->db->where('store_id', $sid)->where('status', 1)->get('db_variants')->result() as $v) {
			$label = strtoupper(trim((string)($v->variant_name ?: $v->attribute_value)));
			if ($label !== '') $variants[$label] = (int)$v->id;
		}
		$brands = array();
		foreach ($this->db->where('store_id', $sid)->where('status', 1)->get('db_brands')->result() as $b) {
			$label = strtoupper(trim((string)$b->brand_name));
			if ($label !== '') $brands[$label] = (int)$b->id;
		}
		$linked = 0;
		foreach ($this->db->where('store_id', $sid)->where('status', 1)->get('db_items')->result() as $item) {
			$name = strtoupper((string)$item->item_name);
			$upd = array();
			foreach ($brands as $label => $bid) { if (strpos($name, $label) !== false) { $upd['brand_id'] = $bid; break; } }
			foreach ($variants as $label => $vid) { if (strpos($name, $label) !== false) { $upd['variant_id'] = $vid; break; } }
			if (!empty($upd)) {
				$this->db->where('id', $item->id)->update('db_items', $upd);
				$linked++;
			}
		}
		echo "linked items to brands/variants: $linked\n";

		// Give every demo item depot stock so the till can actually sell them.
		$stocked = 0;
		foreach ($this->db->where('store_id', $sid)->where('status', 1)->get('db_items')->result() as $item) {
			$exists = $this->db->where('store_id', $sid)->where('warehouse_id', $hq->id)->where('item_id', $item->id)->get('db_warehouseitems')->row();
			if ($exists) continue;
			$qty = (float)$item->stock > 0 ? (float)$item->stock : 20;
			$this->db->insert('db_warehouseitems', ['store_id'=>$sid, 'warehouse_id'=>$hq->id, 'item_id'=>(int)$item->id, 'available_qty'=>$qty]);
			$stocked++;
		}
		echo "depot stock rows added: $stocked\n";

		$profile = mp_get_store_profile($sid);
		echo "profile: industry=".$profile['industry_type']." dashboard=".$profile['dashboard_template']."\n";
		echo "LOGIN: zonattyres / martpoint123\n";
	}

	public function clean(){
		$sid = self::STORE_ID;
		// Introspective teardown: every tenant-scoped table for this scratch store only.
		$tables = $this->db->list_tables();
		$dropped = 0;
		foreach($tables as $t){
			if($t === 'db_store') continue;
			if(!$this->db->field_exists('store_id', $t)) continue;
			$this->db->where('store_id', $sid)->delete($t);
			$dropped++;
		}
		$users = $this->db->where('store_id', $sid)->get('db_users')->result();
		foreach($users as $u){
			$this->db->where('user_id', $u->id)->delete('db_userswarehouses');
			$this->db->where('id', $u->id)->delete('db_users');
		}
		$this->db->where('id', $sid)->delete('db_store');
		echo "cleaned store $sid from $dropped tenant tables\n";
	}
}
