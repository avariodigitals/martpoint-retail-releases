<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verify_rail — proves every link the clinic shell renders actually resolves
 * (CLI only).
 *
 *   php index.php verify_rail run            # seed store 3
 *   php index.php verify_rail run 3          # explicit store
 *
 * A nav entry that 404s or 403s is a dead end for the user, and neither the
 * acceptance suite nor a syntax check will catch it. This renders the shell for
 * the store's most privileged role, extracts every sidebar and mobile-nav href,
 * requests each one as that user, and fails on 404/500 or on an access-denied
 * response.
 *
 * Requires a local base URL (MP_BASE_URL, default https://<APP_HOST>.test) and
 * an HTTP session for the probed user.
 */
class Verify_rail extends CI_Controller {

	private $storeId;
	private $pass = 0;
	private $fail = 0;
	private $base;

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		$this->storeId = (int)(getenv('MP_STORE') ?: 3);
		$this->base = rtrim(getenv('MP_BASE_URL') ?: 'https://martpointretailapp.test', '/');
	}

	private function check($name, $cond, $detail = ''){
		$ok = (bool)$cond;
		if($ok) $this->pass++; else $this->fail++;
		printf("%s  %-46s %s\n", $ok ? 'PASS' : 'FAIL', $name, $detail);
	}

	/** All active users for the store — every role's rail is verified. */
	private function probeUsers(){
		return $this->db->select('u.id, u.username, u.role_id, r.role_name')
			->from('db_users u')->join('db_roles r', 'r.id = u.role_id', 'left')
			->where('u.store_id', $this->storeId)->where('u.status', 1)
			->order_by('u.role_id')->get()->result();
	}

	private function httpSession($user){
		$sid = md5(uniqid('', true)) . substr(md5((string)mt_rand()), 0, 8);
		$data = array(
			'inv_username' => $user->username, 'logged_in' => 1, 'inv_userid' => (int)$user->id,
			'role_id' => (int)$user->role_id, 'store_id' => (string)$this->storeId,
			'display_name' => $user->username, 'login_attempts' => 0,
		);
		$payload = '';
		foreach($data as $k => $v){ $payload .= $k . '|' . serialize($v); }
		$this->db->insert('ci_sessions', array(
			'id' => $sid, 'ip_address' => '127.0.0.1', 'timestamp' => time(), 'data' => $payload,
		));
		return $sid;
	}

	private function fetch($url, $sid, &$code = null){
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_TIMEOUT        => 45,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_COOKIE         => 'v10_session=' . $sid,
			CURLOPT_USERAGENT      => 'MartPoint rail verifier',
		));
		$body = curl_exec($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return (string)$body;
	}

	public function run(){
		$users = $this->probeUsers();
		if(empty($users)){ fwrite(STDERR, "no active users for store {$this->storeId}\n"); exit(2); }

		echo "== Clinic rail link check ==\n";
		echo "store {$this->storeId} @ {$this->base}\n";
		echo 'active users to verify: ' . count($users) . "\n\n";

		$allBad = array();
		$checked = 0;

		foreach($users as $user){
			$sid = $this->httpSession($user);
			$code = 0;
			$dash = $this->fetch($this->base . '/dashboard', $sid, $code);
			$label = $user->username . ' (role ' . $user->role_id . ' ' . trim((string)$user->role_name ?: '—') . ')';

			if($code !== 200){
				$this->check($label . ' — dashboard renders', false, 'HTTP ' . $code);
				$allBad[] = $label . ' -> /dashboard (HTTP ' . $code . ')';
				$this->db->where('id', $sid)->delete('ci_sessions');
				continue;
			}

			// Every href the rail, mobile bar and KPI cards render for THIS user.
			// KPI cards are included deliberately: a figure linking to a route
			// the viewer cannot open is a dead end just like a nav link.
			preg_match_all('/<a[^>]+href="([^"]+)"/i', $dash, $m);
			$links = array();
			foreach($m[1] as $href){
				if(strpos($href, $this->base) !== 0) continue;
				$path = substr($href, strlen($this->base));
				if($path === '' || $path[0] === '#') continue;
				$links[$path] = true;
			}
			$links = array_keys($links);
			sort($links);

			$bad = array();
			foreach($links as $path){
				$c = 0;
				$body = $this->fetch($this->base . '/' . ltrim($path, '/'), $sid, $c);
				// Match the denied/locked page's own markup, not a phrase — the app
				// legitimately contains text like "Camera access denied".
				$denied = (strpos($body, 'mp-access-denied') !== false);

				/*
				 * A 3xx is a SUCCESS here, not a dead end: /logout answers 302 and
				 * then sends the browser to the login page, which is exactly what
				 * the user wants. Only an outright 4xx/5xx is a broken link.
				 */
				$reachable = ($c === 200) || ($c >= 300 && $c < 400);

				if(!$reachable || $denied){
					$bad[] = $path . ' (' . ($denied ? 'denied' : 'HTTP ' . $c) . ')';
				}
				$checked++;
			}
			$this->check($label . ' — ' . count($links) . ' link(s)', empty($bad), implode(', ', $bad));
			foreach($bad as $b){ $allBad[] = $label . ' -> ' . $b; }
			$this->db->where('id', $sid)->delete('ci_sessions');
		}

		echo "\n";
		$this->check('no dead ends on any role', empty($allBad), $checked . ' link(s) checked across ' . count($users) . ' role(s)');

		echo "\n== SUMMARY: {$this->pass} passed, {$this->fail} failed ==\n";
		exit($this->fail ? 1 : 0);
	}
}
