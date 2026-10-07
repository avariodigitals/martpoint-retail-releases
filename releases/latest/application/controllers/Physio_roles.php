<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Physio_roles — additive permission sync for physiotherapy role presets (CLI).
 *
 *   php index.php physio_roles check          # preview the diff, write nothing
 *   php index.php physio_roles sync           # insert only the missing keys
 *   php index.php physio_roles sync 2 3       # limit to specific store ids
 *
 * Why this exists: create_physio_roles() only creates roles that are missing,
 * and reseed_missing_permissions() skips any role that already holds
 * permissions so admin edits are never clobbered. A store seeded from an
 * earlier preset therefore keeps a partial permission set and its clinical
 * screens 403 for roles that should have access.
 *
 * Safety: this is strictly additive. It inserts only the preset keys the role
 * does not already hold. It never deletes, never overwrites, and never touches
 * non-physio roles. Running it twice changes nothing.
 *
 * Environment:
 *   MP_DB   destination database (default: app DB)
 */
class Physio_roles extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		$this->load->model('default_data_model', 'default_data');
	}

	public function index(){
		echo "Usage:\n"
			. "  php index.php physio_roles check [store_id ...]   preview only\n"
			. "  php index.php physio_roles sync  [store_id ...]   apply changes\n";
	}

	/** Physio-enabled stores (from industry settings, the canonical source). */
	private function physioStores(){
		if(!$this->db->table_exists('db_store_industry_settings')){
			return array();
		}
		$rows = $this->db->select('store_id')->where('industry_type', 'physiotherapy_rehabilitation')
			->get('db_store_industry_settings')->result();
		return array_map('intval', array_column($rows, 'store_id'));
	}

	private function execute($apply){
		$ids = array_slice($this->uri->segment_array(), 2);
		$targets = array();
		foreach($ids as $id){
			if(ctype_digit((string)$id)){ $targets[] = (int)$id; }
		}
		if(empty($targets)){
			$targets = $this->physioStores();
		}
		if(empty($targets)){
			echo "No physiotherapy stores found" . ($ids ? " matching the given ids" : "") . ".\n";
			return;
		}

		echo ($apply ? "== SYNC" : "== CHECK (no writes)")
			. " — db {$this->db->database}, stores: " . implode(', ', $targets) . " ==\n\n";

		$grandAdded = 0; $grandMissing = 0; $adminAdded = 0;

		// The vendor/install-level admin roles (db_store 1) are not tied to a
		// physio store, so they are topped up once for the whole run.
		$installReport = $this->default_data->sync_install_admin_permissions($apply);
		foreach($installReport as $role => $r){
			$adminAdded += $r['added'];
			$grandAdded += $r['added'];
			$grandMissing += count($r['missing']);
			if(!empty($r['missing']) || $r['added']){
				echo "install-level {$role} (role {$r['role_id']}): "
					. ($apply ? "added " . $r['added'] : "missing " . count($r['missing']))
					. " clinical permission(s)\n";
			}
		}
		if(!empty($installReport)){ echo "\n"; }

		foreach($targets as $storeId){
			$storeAdded = 0; $storeMissing = 0;
			// 1. Create any preset role that is entirely absent (idempotent —
			//    existing roles are skipped by name).
			if($apply){
				$created = $this->default_data->create_physio_roles($storeId);
				if(!empty($created['count'])){
					echo "store {$storeId}: created {$created['count']} missing role(s)\n";
				}
				if(!empty($created['errors'])){
					foreach($created['errors'] as $e){ echo "   ! {$e}\n"; }
				}
			}
			// 2. Align the store's stored feature flags with its industry
			//    preset. A store converted from retail keeps that preset's
			//    flags, and mp_feature_enabled() consults the stored flag
			//    FIRST — so the clinical rail renders empty even though the
			//    role holds every grant. Strictly additive: only preset flags
			//    that are currently off are turned on.
			$flagReport = $this->default_data->sync_industry_feature_flags($storeId, $apply);
			$flagAdded = count($flagReport['changed']);
			$flagMsg = $flagAdded
					? ($apply ? "enabled {$flagAdded}" : "would enable {$flagAdded}")
					: "already aligned";
			echo "   - {$flagReport['industry']} [feature flags]: {$flagMsg}";
			if($flagAdded){
				echo " — " . implode(', ', array_slice(array_keys($flagReport['changed']), 0, 8))
						. ($flagAdded > 8 ? ' …' : '');
			}
			echo "\n";

			// 3. Give the store's full-control roles (Admin / Store Admin /
			//    Business Owner) the clinical grant set. Without this the
			//    administrator's role holds no clinical key at all and their
			//    clinic rail renders with no clinical screens.
			$adminReport = $this->default_data->sync_clinical_admin_permissions($storeId, $apply);
			$adminStoreAdded = 0;
			foreach($adminReport as $r){ $adminStoreAdded += $r['added']; }
			$adminAdded += $adminStoreAdded;
			// 4. Give the store's OWNER role the owner-administration grants
			//    (staff & permissions, business setup, payment modes, email,
			//    approvals). is_store_admin() recognises the owner role by name
			//    within the store, so the screens appear once the gate passes —
			//    but the actions inside them need these keys. A store whose
			//    owner role was seeded without them (or which has no role 2 at
			//    all, like a clinic) would otherwise reach the screen and be
			//    unable to save.
			$ownerReport = $this->default_data->sync_store_owner_permissions($storeId, $apply);
			$ownerStoreAdded = 0;
			foreach($ownerReport as $r){ $ownerStoreAdded += $r['added']; }
			// 5. Additively close the permission gap on every preset role.
			$report = $this->default_data->sync_physio_role_permissions($storeId, $apply);
			$storeAdded = $adminStoreAdded + $ownerStoreAdded; $storeMissing = 0;
			foreach($report as $r){ $storeAdded += $r['added']; $storeMissing += count($r['missing']); }
			foreach($adminReport as $r){ $storeMissing += count($r['missing']); }
			foreach($ownerReport as $r){ $storeMissing += count($r['missing']); }
			$grandAdded += $storeAdded; $grandMissing += $storeMissing;
			echo "store {$storeId}: " . ($apply
					? "{$storeAdded} permission(s) added"
					: "{$storeMissing} permission(s) missing")
				. "\n";
			if(empty($ownerReport)){
				echo "   - (no owner role found for this store — owner screens stay\n"
					. "      unreachable until one of '" . implode("', '", store_owner_role_names())
					. "' exists in db_roles for store {$storeId})\n";
			}
			foreach($ownerReport as $role => $r){
				if(empty($r['missing'])){ continue; }
				echo "   - {$role} (role {$r['role_id']}) [owner admin]: "
					. ($apply ? "added " . $r['added'] : "missing " . count($r['missing']))
					. " permission(s)\n";
			}
			foreach($adminReport as $role => $r){
				if($r['role_id'] === null){ continue; }
				if(empty($r['missing'])){ continue; }
				echo "   - {$role} (role {$r['role_id']}) [full control]: "
					. ($apply ? "added " . $r['added'] : "missing " . count($r['missing']))
					. " clinical permission(s)\n";
			}
			foreach($report as $role => $r){
				if($r['role_id'] === null){
					echo "   - {$role}: " . $r['note'] . "\n";
					continue;
				}
				if(empty($r['missing'])){ continue; }
				echo "   - {$role} (role {$r['role_id']}): "
					. ($apply ? "added " . $r['added'] : "missing " . count($r['missing']))
					. " — " . implode(', ', array_slice($r['missing'], 0, 6))
					. (count($r['missing']) > 6 ? ' …' : '') . "\n";
			}
			echo "\n";
		}

		if($apply){
			echo "Total inserted: {$grandAdded} permission row(s).\n";
			if($adminAdded > 0){
				echo "  of which {$adminAdded} were clinical grants for the store's full-control role(s).\n";
			}
			echo "Re-run 'physio_roles check' to confirm nothing is left missing.\n";
		} else {
			echo "Total missing: {$grandMissing} permission row(s). "
				. "Run 'physio_roles sync' to apply.\n";
		}
	}

	public function check(){ $this->execute(false); }
	public function sync(){  $this->execute(true);  }
}
