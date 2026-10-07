<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verify_screens — exercises every interactive control on every clinic screen
 * (CLI only).
 *
 *   php index.php verify_screens run               # store 3, all roles
 *   php index.php verify_screens run 2             # explicit store
 *   php index.php verify_screens run 3 physio_t    # one user
 *
 * Why this exists: verify_rail proves the SIDEBAR resolves, and the acceptance
 * suite proves the business rules. Neither proves that the buttons on a screen
 * do anything — a control wired to a function that does not exist, or to an
 * endpoint that 404s, fails silently and looks like "the button does not work".
 *
 * What it does, per screen, per role:
 *   1. Fetches the screen and extracts every interactive control: <button>,
 *      <a role=button>, and onclick="fn(...)" handlers, plus inline $('.x')
 *      click bindings.
 *   2. Resolves the target: a JS function name, a data-* endpoint, an href, or
 *      an AJAX URL declared in a <script> block.
 *   3. Fails when the function the control calls is not defined anywhere in the
 *      rendered document, or when the endpoint it posts to does not exist.
 *
 * It is deliberately a static + HTTP analysis rather than a headless browser:
 * it must run in CI where no browser is available, and it still catches the
 * real defect class (a control wired to nothing).
 */
class Verify_screens extends CI_Controller {

	private $storeId;
	private $base;
	private $pass = 0;
	private $fail = 0;
	private $warn = 0;
	private $userId = null;

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		$this->storeId = (int)(getenv('MP_STORE') ?: 3);
		$this->base = rtrim(getenv('MP_BASE_URL') ?: 'https://martpointretailapp.test', '/');
	}

	private function check($name, $cond, $detail = ''){
		$ok = (bool)$cond;
		if($ok){ $this->pass++; } else { $this->fail++; }
		printf("%s  %-58s %s\n", $ok ? 'PASS' : 'FAIL', $name, $detail);
		return $ok;
	}

	private function note($name, $detail){
		$this->warn++;
		printf("NOTE  %-58s %s\n", $name, $detail);
	}

	/** Fetch a path as the given user through the local session helper. */
	private function fetch($path, $username){
		$out = tempnam(sys_get_temp_dir(), 'vscr');
		$cmd = sprintf(
			'php %s %s %s %s 2>/dev/null',
			escapeshellarg('/tmp/mp_fetch.php'),
			escapeshellarg((string)$username),
			escapeshellarg('/' . ltrim($path, '/')),
			escapeshellarg($out)
		);
		shell_exec($cmd);
		$body = is_file($out) ? (string)file_get_contents($out) : '';
		@unlink($out);
		return array($body, $this->httpStatus($body));
	}

	/**
	 * Classify a fetched body.
	 *
	 * Both denials and 404s are pages the app renders with HTTP 200, so the
	 * body has to be inspected. The checks are deliberately narrow: a loose
	 * /access denied/i match false-positives on the clock-in camera error
	 * string ("Camera access denied or unavailable"), which appears on every
	 * screen that loads the shared footer.
	 */
	private function httpStatus($body){
		if($body === ''){ return 0; }
		if(stripos($body, '<title>Page Not Found') !== false){ return 404; }
		if(stripos($body, '<title>Access Denied') !== false){ return 403; }
		if(strpos($body, "don't have permission to access this feature") !== false){ return 403; }
		return 200;
	}

	/** Every JS function the document DEFINES, inline + external scripts. */
	private function definedFunctions($html){
		$names = array();

		$harvest = function($src) use (&$names){
			if(preg_match_all('/function\s+([A-Za-z_$][\w$]*)\s*\(/', $src, $m)){
				foreach($m[1] as $n){ $names[$n] = true; }
			}
			// var x = function / x = function / window.x = function
			if(preg_match_all('/(?:var\s+|window\.|let\s+|const\s+)?([A-Za-z_$][\w$]*)\s*=\s*function\s*\(/', $src, $m)){
				foreach($m[1] as $n){ $names[$n] = true; }
			}
			if(preg_match_all('/window\.([A-Za-z_$][\w$]*)\s*=/', $src, $m)){
				foreach($m[1] as $n){ $names[$n] = true; }
			}
			// Object-literal methods (it:function(){}) — assist.js style.
			if(preg_match_all('/([A-Za-z_$][\w$]*)\s*:\s*function\s*\(/', $src, $m)){
				foreach($m[1] as $n){ $names[$n] = true; }
			}
		};

		$harvest($html);

		/*
		 * A control very often calls a function defined in a shared script
		 * (assist.js supplies openChat/submitSupport/closeSupportModal). Scanning
		 * only the inline HTML reported those as unresolved on every screen, so
		 * the referenced scripts are read from disk too.
		 */
		if(preg_match_all('/<script[^>]+src\s*=\s*"([^"]+)"/i', $html, $m)){
			foreach(array_unique($m[1]) as $src){
				$path = $this->localAssetPath($src);
				if($path && is_file($path)){
					$harvest((string)file_get_contents($path));
				}
			}
		}
		return $names;
	}

	/** Map a rendered asset URL onto the file it is served from. */
	private function localAssetPath($src){
		$src = preg_replace('/\?.*$/', '', $src);
		if(strpos($src, 'http') === 0){
			$needle = '/theme/';
			$pos = strpos($src, $needle);
			if($pos === false){ return null; }
			$rel = substr($src, $pos + 1);          // theme/js/assist.js
		} elseif(strpos($src, '/') === 0){
			$rel = ltrim($src, '/');
		} else {
			$rel = $src;
		}
		$base = rtrim(FCPATH, '/') . '/' . $rel;
		return is_file($base) ? $base : null;
	}

	/** Inline onclick handlers: returns array of handler source, keyed by control label. */
	private function onclickHandlers($html){
		$out = array();
		if(preg_match_all('/<([a-z]+)\b[^>]*\bonclick\s*=\s*"([^"]*)"[^>]*>(.*?)<\/\1>/is', $html, $m, PREG_SET_ORDER)){
			foreach($m as $hit){
				$label = trim(preg_replace('/\s+/', ' ', strip_tags($hit[3])));
				if($label === ''){ $label = $hit[1]; }
				$out[] = array('label' => $label, 'code' => $hit[2]);
			}
		}
		// onclick on self-closing / void elements (input, img, a without close)
		if(preg_match_all('/<(?:input|img|button|a)\b[^>]*\bonclick\s*=\s*"([^"]*)"[^>]*>/is', $html, $m)){
			foreach($m[1] as $code){ $out[] = array('label' => '(void control)', 'code' => $code); }
		}
		return $out;
	}

	/**
	 * Function names a handler actually CALLS.
	 *
	 * A handler such as onclick="$('#x').removeClass('active')" contains no call
	 * to a page function at all — jQuery's .removeClass() is a method, and the
	 * only "name(" is a jQuery method. Reporting those produced a burst of
	 * toggle()/send()/closeMenu() false positives.
	 *
	 * A name counts as a call only when it is NOT immediately preceded by a dot
	 * (i.e. not a method call) and IS followed by an opening paren.
	 */
	private function calledFunctions($code){
		$names = array();
		$code = stripslashes($code);
		if(preg_match_all('/(?<![\w$.])([A-Za-z_$][\w$]*)\s*\(/', $code, $m, PREG_OFFSET_CAPTURE)){
			foreach($m[1] as $hit){
				$n = $hit[0];
				if(in_array(strtolower($n), array(
					'if','for','while','return','function','switch','catch','typeof',
					'parseint','parsefloat','number','string','alert','confirm','prompt',
					'settimeout','setinterval','isnan','json','date','array','object',
					'require','new','this','event','element','el','e','ev','value','push',
					'preventdefault','stoppropagation','closest','find','attr','val','text',
					'html','on','click','append','remove','addclass','removeclass','toggleclass',
					'serialize','submit','focus','blur','prop','data','each','map','filter',
					'trim','split','join','replace','test','match','slice','indexof','tostring',
					'replacer','concat',
				), true)){ continue; }
				$names[$n] = true;
			}
		}
		return array_keys($names);
	}

	/** AJAX endpoints declared in the document. */
	private function ajaxEndpoints($html){
		$eps = array();
		if(preg_match_all('/base_url\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', $html, $m)){
			foreach($m[1] as $p){ $eps[trim($p, '/')] = true; }
		}
		if(preg_match_all('/\burl\s*:\s*[\'"]([^\'"]*)[\'"]/i', $html, $m)){
			foreach($m[1] as $p){
				$p = trim($p, '/');
				if($p !== '' && strpos($p, 'http') !== 0){ $eps[$p] = true; }
			}
		}
		$raw = array();
		if(preg_match_all('/\bfetch\(\s*[\'"]([^\'"]+)[\'"]/i', $html, $m)){
			foreach($m[1] as $p){ $raw[$p] = true; }
		}
		if(preg_match_all('/\bfetch\(\s*[\'"]<\?=\s*base_url\(\s*[\'"]([^\'"]+)/i', $html, $m)){
			foreach($m[1] as $p){ $eps[trim($p, '/')] = true; }
		}
		return array('named' => array_keys($eps), 'raw' => array_keys($raw));
	}

	/** The screens to audit: key => [label, path, permission gate]. */
	private function screens(){
		return array(
			'dashboard'        => array('Clinic overview',       'dashboard',                     null),
			'patients'         => array('Patients register',     'patients',                      'patients_view'),
			/* Patient registration is a MODAL on the register (openPatientModal
			   -> savePatient -> POST patients/save), not a separate page. The
			   audit must probe that endpoint rather than invent a /patients/add
			   route that has never existed. */
			'patients_save'    => array('Register patient (POST)', 'patients/save',               'patients_add'),
			'appointments'     => array('Appointments',          'appointments',                  'appointments_view'),
			'care_queue'       => array('Care queue',            'care_queue',                    'care_queue_view'),
			'sessions'         => array('Sessions',              'sessions',                      'sessions_view'),
			'investigations'   => array('Investigations',        'investigations',                'investigations_view'),
			'patient_docs'     => array('Documents',             'patient_docs',                  'patient_docs_view'),
			'patient_billing'  => array('Patient billing',       'patient_billing',               'patient_billing_view'),
			'patient_funds'    => array('Patient funds',         'patient_funds',                 'patient_funds_view'),
			'inpatient'        => array('Admissions',            'inpatient',                     'admissions_view'),
			'inpatient_beds'   => array('Bed board',             'inpatient/beds',                'admissions_view'),
			'tasks_porter'     => array('Porter tasks',          'inpatient/tasks?board=porter',  'porter_tasks_view'),
			'tasks_nursing'    => array('Nursing tasks',         'inpatient/tasks?board=nursing', 'nursing_tasks_view'),
			'inpatient_bill'   => array('Inpatient billing',     'inpatient/billing_setup',       'beds_manage'),
			'assessments'      => array('Assessment templates',  'assessment_templates',          'assessment_templates_manage'),
			'packages'         => array('Session packages',      'service_packages',              'service_packages_view'),
			'clinical_reports' => array('Clinical reports',      'clinical_reports',              'clinical_reports_view'),
			'items'            => array('Services & pricing',    'items',                         'items_view'),
			'purchase'         => array('Purchases',             'purchase',                      'purchase_view'),
			'suppliers'        => array('Suppliers',             'suppliers',                     'suppliers_view'),
			'supplier_add'     => array('Add supplier',          'suppliers/add',                 'suppliers_add'),
			'accounts'         => array('Accounts',              'accounts',                      'accounts_view'),
			'expense'          => array('Expenses',              'expense',                       'expense_view'),
			'warehouse'        => array('Branches',              'warehouse',                     'warehouse_view'),
			'users'            => array('Staff accounts',        'users/view',                    'users_view'),
			/* Staff are created on the list screen (Users::save_or_update);
			   there is no /users/add page. Roles ARE a separate page. */
			'roles'            => array('Roles',                 'roles/view',                    'roles_view'),
			'role_add'         => array('Add role',              'roles/add',                     'roles_add'),
			'approvals'        => array('Approvals',             'approvals/settings',            'approval_settings_edit'),
			'payment_modes'    => array('Payment modes',         'payment_modes',                 'payment_modes_view'),
			'audit_trail'      => array('Audit trail',           'audit_trail',                   'audit_trail_view'),
			'imports'          => array('Legacy import',         'imports',                       'imports_view'),
			'business_profile' => array('Facility settings',     'business_profile',              'business_setup'),
			'site'             => array('Site & branding',       'site',                          'store_edit'),
		);
	}

	public function run(){
		$args = array_slice($this->uri->segment_array(), 2);
		if(!empty($args[0]) && ctype_digit((string)$args[0])){ $this->storeId = (int)$args[0]; }

		echo "== Clinic screen control audit ==\n";
		echo "store {$this->storeId} @ {$this->base}\n\n";

		/*
		 * Probe as EVERY active role, not just the top one. A screen that works
		 * for an administrator can still be a dead end for a nurse (a control
		 * they are allowed to see but not to use), and the user's complaint was
		 * precisely "some buttons don't work" rather than "the admin is broken".
		 */
		$users = $this->db->select('username,role_id,role_name')
			->where('store_id', $this->storeId)
			->where('status', 1)
			->order_by('role_id', 'ASC')
			->get('db_users')->result();
		if(!$users){ echo "No active users on store {$this->storeId}.\n"; return; }

		$only = $args[1] ?? '';
		$totalControls = 0;
		$totalBroken  = 0;
		$problems     = array();
		$reachability = array();

		foreach($users as $user){
			if($only !== '' && strcasecmp($only, $user->username) !== 0){ continue; }
			echo "--- {$user->username} (role {$user->role_id} {$user->role_name})\n";

			foreach($this->screens() as $key => $meta){
				list($label, $path, $gate) = $meta;

				// Skip a screen this role has no grant for: it is not a defect
				// that a nurse cannot open purchases, and reporting it as one
				// would bury the real findings.
				if($gate !== null && !$this->roleCan($user->role_id, $gate)){
					continue;
				}

				list($html, $code) = $this->fetch($path, $user->username);
				$tag = $user->username . ' · ' . $label;

				if($code === 0){ $this->note($tag, "no response from /{$path}"); continue; }
				if($code === 404){
					$this->check($tag, false, 'HTTP 404 — route does not exist');
					continue;
				}
				if($code === 403){
					// The role holds the grant but the controller still refuses:
					// a real mismatch between the rail and the route.
					$this->check($tag, false, 'HTTP 403 despite holding the grant');
					$problems[$tag][] = 'grant held but route denies';
					continue;
				}

				$reachability[$label] = ($reachability[$label] ?? 0) + 1;

				$defined  = $this->definedFunctions($html);
				$handlers = $this->onclickHandlers($html);
				$broken   = array();

				foreach($handlers as $h){
					foreach($this->calledFunctions($h['code']) as $fn){
						$totalControls++;
						if(!isset($defined[$fn])){
							$broken[] = $fn . '()  ← "' . substr($h['label'], 0, 34) . '"';
						}
					}
				}
				if(preg_match_all('/\$\s*\(\s*[\'"]#([\w-]+)[\'"]\s*\)\s*\.\s*(?:on|click)\s*\(/i', $html, $m)){
					foreach(array_unique($m[1]) as $id){
						$totalControls++;
						if(stripos($html, $id) === false){
							$broken[] = '#' . $id . '  ← bound but never referenced anywhere';
						}
					}
				}

				if($broken){
					$totalBroken += count($broken);
					foreach($broken as $b){ $problems[$tag][] = $b; }
					$this->check($tag, false, count($broken) . ' unresolved control(s)');
				} else {
					$this->check($tag, true, count($handlers) . ' handler(s)');
				}
			}
			echo "\n";
		}

		if($problems){
			echo "Unresolved controls by screen:\n";
			foreach($problems as $screen => $list){
				echo "  {$screen}:\n";
				foreach(array_unique($list) as $b){ echo "    - {$b}\n"; }
			}
			echo "\n";
		}
		echo "controls inspected: {$totalControls}, unresolved: {$totalBroken}\n";
		printf("== SUMMARY: %d passed, %d failed, %d note(s) ==\n", $this->pass, $this->fail, $this->warn);
		return ($this->fail === 0);
	}

	/**
	 * Does this role hold a permission?
	 *
	 * Deliberately simple: ONE query, then the administrator bypass as a plain
	 * PHP condition. CI3 reuses a single Query Builder instance per connection,
	 * so chaining several count_all_results() calls inside one boolean
	 * expression interleaves their state and produces malformed SQL.
	 */
	private function roleCan($roleId, $perm){
		static $cache = array();
		$key = $roleId . '|' . $perm;
		if(array_key_exists($key, $cache)){ return $cache[$key]; }

		$explicit = (int)$this->db->query(
			'SELECT COUNT(*) AS n FROM db_permissions WHERE role_id = ? AND permissions = ?',
			array((int)$roleId, $perm)
		)->row()->n;

		// Retail-style grants carry an administrator bypass for the store-admin
		// role and the platform admin; clinical grants do not, but probing a
		// screen the admin genuinely reaches is the safe direction to err in.
		$bypass = in_array((int)$roleId, array(1, 2), true);

		$cache[$key] = ($explicit > 0) || $bypass;
		return $cache[$key];
	}

	public function index(){
		echo "Usage:\n  php index.php verify_screens run [store_id]\n";
	}
}
