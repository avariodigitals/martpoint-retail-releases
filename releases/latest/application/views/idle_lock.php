<?php
/**
 * Idle Lock — inactivity warning dialog + snooze (screen lock) overlay.
 *
 * Loaded once per page (desktop via mp_footer.php, mobile via
 * mobile/bottom_nav.php). Configuration is read from db_store_settings
 * (group 'idle_lock') and is fully overridable from Store Settings.
 */
if(!defined('MP_IDLE_LOCK_LOADED')):
define('MP_IDLE_LOCK_LOADED', true);

$__il_store_id = function_exists('get_current_store_id') ? (int)get_current_store_id() : 0;
$__il_cfg = array(
	'idle_enabled'         => 0,
	'idle_timeout_minutes' => 15,
	'idle_warning_seconds' => 60,
	'snooze_enabled'  => 0,
	'snooze_title'    => '',
	'snooze_message'  => '',
	'snooze_image'    => '',
);
if($__il_store_id && $this->db->table_exists('db_store_settings')){
	foreach($__il_cfg as $k => $v){
		$__il_cfg[$k] = mp_get_store_setting($__il_store_id, 'idle_lock', $k, $v);
	}
}
$__il_cfg['idle_timeout_minutes'] = max(1, (int)$__il_cfg['idle_timeout_minutes']);
$__il_cfg['idle_warning_seconds'] = max(10, (int)$__il_cfg['idle_warning_seconds']);

if((int)$__il_cfg['idle_enabled'] === 1):
	$__il_store    = function_exists('get_store_details') ? get_store_details($__il_store_id) : null;
	$__il_store_nm = $__il_store && !empty($__il_store->store_name) ? $__il_store->store_name : ($SITE_TITLE ?? 'MartPoint');
	$__il_title    = trim((string)$__il_cfg['snooze_title'])    !== '' ? $__il_cfg['snooze_title']    : 'Great deals are always in store';
	$__il_msg      = trim((string)$__il_cfg['snooze_message'])  !== '' ? $__il_cfg['snooze_message']  : 'We\'ll be right back — your session is safely paused while you\'re away.';
	$__il_img      = trim((string)$__il_cfg['snooze_image']);
	$__il_img_url  = ($__il_img !== '' && file_exists(FCPATH . $__il_img)) ? base_url($__il_img) : '';
	$__il_user     = $this->session->userdata('display_name') ?: $this->session->userdata('inv_username') ?: 'User';
	// Store logo: db_store.store_logo → db_logos (latest active) → store monogram
	$__il_logo     = '';
	$__il_theme_logo = function_exists('mp_get_store_theme_setting') ? mp_get_store_theme_setting($__il_store_id, 'store_logo', '') : '';
	if(!empty($__il_theme_logo) && file_exists(FCPATH . $__il_theme_logo)){
		$__il_logo = base_url($__il_theme_logo);
	} elseif($__il_store && !empty($__il_store->store_logo) && file_exists(FCPATH . $__il_store->store_logo)){
		$__il_logo = base_url($__il_store->store_logo);
	} elseif($this->db->table_exists('db_logos')){
		$__il_lg = $this->db->where('store_id', $__il_store_id)->where('status', 1)->order_by('id', 'desc')->get('db_logos')->row();
		if($__il_lg && !empty($__il_lg->logo) && file_exists(FCPATH . $__il_lg->logo)){
			$__il_logo = base_url($__il_lg->logo);
		}
	}
	// Store monogram fallback (first letters of the store name)
	$__il_words    = preg_split('/\s+/', trim($__il_store_nm));
	$__il_initials = strtoupper(substr($__il_words[0], 0, 1) . (count($__il_words) > 1 ? substr(end($__il_words), 0, 1) : ''));
	// Current user's avatar — same helper the topbar uses (falls back to default avatar image)
	$__il_avatar   = function_exists('get_profile_picture') ? get_profile_picture() : '';
?>
<style>
#mpIdleWarnOverlay,#mpSnoozeOverlay{position:fixed;inset:0;z-index:10050;display:none;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;}
#mpIdleWarnOverlay.open,#mpSnoozeOverlay.open{display:flex;align-items:center;justify-content:center;}
#mpIdleWarnOverlay{background:rgba(15,23,42,.45);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);padding:16px;}
.mp-il-dialog{width:100%;max-width:420px;background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(15,23,42,.28);padding:28px 24px 22px;text-align:center;animation:mpIlPop .28s cubic-bezier(.2,.9,.3,1.2);}
@keyframes mpIlPop{from{opacity:0;transform:translateY(14px) scale(.96);}to{opacity:1;transform:none;}}
.mp-il-iconwrap{width:64px;height:64px;margin:0 auto 14px;border-radius:50%;background:linear-gradient(135deg,#EEF4FF,#E0ECFF);display:flex;align-items:center;justify-content:center;color:#2563EB;font-size:26px;}
.mp-il-dialog h2{margin:0 0 6px;font-size:20px;font-weight:700;color:#0F172A;letter-spacing:-.01em;}
.mp-il-dialog p{margin:0 0 14px;font-size:14px;line-height:1.55;color:#64748B;}
.mp-il-dialog p strong{color:#0F172A;}
.mp-il-count{display:inline-flex;align-items:center;justify-content:center;min-width:52px;padding:4px 12px;border-radius:999px;background:#FEF3C7;color:#B45309;font-weight:700;font-size:15px;font-variant-numeric:tabular-nums;}
.mp-il-bar{height:6px;border-radius:999px;background:#F1F5F9;overflow:hidden;margin:14px 0 20px;}
.mp-il-bar > i{display:block;height:100%;width:100%;border-radius:999px;background:linear-gradient(90deg,#2563EB,#60A5FA);transition:width 1s linear;}
.mp-il-actions{display:flex;gap:10px;}
.mp-il-btn{flex:1;padding:12px 14px;border-radius:12px;font-size:14px;font-weight:600;border:1px solid transparent;cursor:pointer;transition:transform .12s ease,box-shadow .12s ease,background .12s ease;}
.mp-il-btn:active{transform:scale(.97);}
.mp-il-btn.primary{background:#2563EB;color:#fff;box-shadow:0 6px 16px rgba(37,99,235,.3);}
.mp-il-btn.primary:hover{background:#1D4ED8;}
.mp-il-btn.ghost{background:#fff;color:#475569;border-color:#E2E8F0;}
.mp-il-btn.ghost:hover{background:#F8FAFC;color:#0F172A;}

/* ---- Snooze / lock screen — full-screen flyer ---- */
#mpSnoozeOverlay{background:#0C111B;overflow:hidden;cursor:pointer;}
#mpSnoozeOverlay::before{content:'';position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);background-size:28px 28px;pointer-events:none;}
.mp-sn-bg{position:absolute;inset:0;background-size:cover;background-position:center;transform:scale(1.02);}
.mp-sn-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(9,13,23,.72) 0%,rgba(9,13,23,.55) 45%,rgba(9,13,23,.85) 100%);}
.mp-sn-glow{position:absolute;inset:0;background:radial-gradient(110% 70% at 50% -10%,rgba(37,99,235,.14),transparent 60%);pointer-events:none;}
.mp-sn-stage{position:relative;z-index:1;width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:24px 20px 84px;overflow-y:auto;color:#E5E7EB;animation:mpIlPop .4s cubic-bezier(.2,.9,.3,1.1);}
.mp-sn-logo-tile{width:92px;height:92px;border-radius:24px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 18px 44px rgba(0,0,0,.45);margin-bottom:16px;}
.mp-sn-logo-tile img{max-width:80%;max-height:80%;object-fit:contain;}
.mp-sn-logo-tile .mono{font-size:34px;font-weight:800;letter-spacing:-.02em;color:#1E3A8A;}
.mp-sn-storename{font-size:clamp(15px,2.4vw,19px);font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#F8FAFC;}
.mp-sn-lockpill{display:inline-flex;align-items:center;gap:8px;margin-top:14px;padding:6px 16px;border-radius:999px;background:rgba(251,191,36,.10);border:1px solid rgba(251,191,36,.28);color:#FCD34D;font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;}
.mp-sn-lockpill .dot{width:7px;height:7px;border-radius:50%;background:#FBBF24;animation:mpIlPulse 2s ease-in-out infinite;}
.mp-sn-campaign{margin-top:26px;max-width:560px;}
.mp-sn-campaign h2{margin:0 0 10px;font-size:clamp(24px,5vw,38px);font-weight:800;letter-spacing:-.02em;color:#fff;line-height:1.15;}
.mp-sn-campaign p{margin:0 auto;font-size:clamp(13px,2vw,15px);line-height:1.7;color:#B8C0CE;max-width:46ch;}
.mp-sn-meta{margin-top:30px;display:inline-flex;align-items:center;gap:16px;padding:10px 22px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);font-size:12.5px;color:#A5ADBB;}
.mp-sn-meta i{color:#7C8494;}
.mp-sn-meta .sep{width:1px;height:14px;background:rgba(255,255,255,.14);}
.mp-sn-hint{position:relative;margin-top:34px;font-size:12.5px;color:#7FB3F5;display:flex;align-items:center;gap:8px;animation:mpIlPulse 2.2s ease-in-out infinite;}
#mpSnoozeOverlay.reveal .mp-sn-hint{animation:none;opacity:.35;}
.mp-sn-powered{position:absolute;left:0;right:0;bottom:0;z-index:2;padding:14px 16px calc(14px + env(safe-area-inset-bottom,0px));text-align:center;background:rgba(8,12,20,.65);border-top:1px solid rgba(255,255,255,.08);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);font-size:11.5px;letter-spacing:.06em;color:#7C8494;}
.mp-sn-powered strong{color:#93C5FD;font-weight:700;}
@keyframes mpIlPulse{0%,100%{opacity:.55;}50%{opacity:1;}}

/* Unlock card */
.mp-sn-unlock{position:absolute;inset:0;z-index:5;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(9,13,23,.88);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);opacity:0;pointer-events:none;transition:opacity .25s ease;}
.mp-sn-stage,.mp-sn-powered{transition:opacity .25s ease,filter .25s ease;}
#mpSnoozeOverlay.reveal .mp-sn-unlock{opacity:1;pointer-events:auto;}
#mpSnoozeOverlay.reveal .mp-sn-stage{opacity:.12;filter:blur(8px);pointer-events:none;}
#mpSnoozeOverlay.reveal .mp-sn-powered{opacity:0;}
#mpSnoozeOverlay.reveal .mp-sn-hint{animation:none;}
.mp-un-card{width:100%;max-width:360px;background:#fff;border-radius:20px;box-shadow:0 30px 80px rgba(0,0,0,.5);padding:26px 24px 22px;text-align:center;transform:translateY(10px);transition:transform .25s ease;}
#mpSnoozeOverlay.reveal .mp-un-card{transform:none;}
.mp-un-avatar{width:64px;height:64px;margin:0 auto 12px;border-radius:50%;background:#E0E7FF;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;overflow:hidden;box-shadow:0 0 0 3px #fff,0 0 0 5px #E2E8F0;}
.mp-un-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
.mp-un-card h3{margin:0 0 2px;font-size:17px;font-weight:700;color:#0F172A;}
.mp-un-card .sub{margin:0 0 16px;font-size:12.5px;color:#64748B;}
.mp-un-field{position:relative;margin-bottom:12px;}
.mp-un-field input{width:100%;padding:13px 44px 13px 14px;border:1.5px solid #E2E8F0;border-radius:12px;font-size:15px;outline:none;transition:border-color .15s ease,box-shadow .15s ease;box-sizing:border-box;}
.mp-un-field input:focus{border-color:#2563EB;box-shadow:0 0 0 4px rgba(37,99,235,.12);}
.mp-un-field .peek{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#94A3B8;cursor:pointer;font-size:15px;padding:6px;}
.mp-un-err{display:none;margin:0 0 10px;font-size:12.5px;color:#DC2626;font-weight:600;}
.mp-un-card.shake{animation:mpIlShake .4s ease;}
@keyframes mpIlShake{0%,100%{transform:translateX(0);}20%{transform:translateX(-8px);}40%{transform:translateX(8px);}60%{transform:translateX(-5px);}80%{transform:translateX(5px);}}
.mp-un-actions{display:flex;flex-direction:column;gap:10px;}
.mp-un-actions .mp-il-btn{width:100%;}
.mp-un-switch{margin-top:14px;font-size:12px;color:#64748B;}
.mp-un-switch a{color:#2563EB;font-weight:600;text-decoration:none;}
.mp-un-switch a:hover{text-decoration:underline;}
@media (max-width:480px){
	.mp-il-dialog{padding:24px 18px 18px;border-radius:18px;}
	.mp-il-actions{flex-direction:column-reverse;}
	.mp-sn-logo-tile{width:72px;height:72px;border-radius:18px;}
	.mp-sn-logo-tile .mono{font-size:26px;}
	.mp-sn-stage{padding:20px 16px 76px;}
	.mp-sn-campaign{margin-top:20px;}
	.mp-sn-meta{gap:10px;padding:8px 16px;font-size:11.5px;}
	.mp-sn-hint{margin-top:24px;}
}
</style>

<!-- Idle warning dialog -->
<div id="mpIdleWarnOverlay" role="dialog" aria-modal="true" aria-labelledby="mpIdleWarnTitle">
	<div class="mp-il-dialog">
		<div class="mp-il-iconwrap"><i class="fa fa-clock-o"></i></div>
		<h2 id="mpIdleWarnTitle">Still there?</h2>
		<p>System has been idle for <strong id="mpIdleFor"></strong>.<br>Do you want to remain logged in?</p>
		<div class="mp-il-count"><span id="mpIdleCountdown"><?= (int)$__il_cfg['idle_warning_seconds']; ?></span>s</div>
		<div class="mp-il-bar"><i id="mpIdleBar"></i></div>
		<div class="mp-il-actions">
			<button type="button" class="mp-il-btn ghost" id="mpIdleCancel">Cancel</button>
			<button type="button" class="mp-il-btn primary" id="mpIdleStay"><i class="fa fa-check"></i> Stay Logged In</button>
		</div>
	</div>
</div>

<!-- Snooze / lock screen — full-screen flyer -->
<div id="mpSnoozeOverlay" aria-hidden="true">
	<?php if($__il_img_url): ?>
		<div class="mp-sn-bg" style="background-image:url('<?= htmlspecialchars($__il_img_url); ?>')"></div>
	<?php endif; ?>
	<div class="mp-sn-glow"></div>
	<div class="mp-sn-stage">
		<div class="mp-sn-logo-tile">
			<?php if($__il_logo): ?>
				<img src="<?= htmlspecialchars($__il_logo); ?>" alt="<?= htmlspecialchars($__il_store_nm); ?>" onerror="this.parentNode.innerHTML='<span class=\'mono\'><?= htmlspecialchars($__il_initials); ?></span>'">
			<?php else: ?>
				<span class="mono"><?= htmlspecialchars($__il_initials); ?></span>
			<?php endif; ?>
		</div>
		<div class="mp-sn-storename"><?= htmlspecialchars($__il_store_nm); ?></div>
		<div class="mp-sn-lockpill"><span class="dot"></span> Screen Locked</div>
		<div class="mp-sn-campaign">
			<h2><?= htmlspecialchars($__il_title); ?></h2>
			<p><?= htmlspecialchars($__il_msg); ?></p>
		</div>
		<div class="mp-sn-meta">
			<span><i class="fa fa-user-circle"></i> <?= htmlspecialchars($__il_user); ?></span>
			<span class="sep"></span>
			<span><i class="fa fa-clock-o"></i> <span id="mpSnoozeClock"></span></span>
		</div>
		<div class="mp-sn-hint"><i class="fa fa-hand-pointer-o"></i> Move, tap or press any key to unlock</div>
	</div>
	<div class="mp-sn-powered">Business Powered by <strong>MartPoint</strong></div>
	<div class="mp-sn-unlock">
		<div class="mp-un-card" id="mpUnlockCard">
			<div class="mp-un-avatar">
				<?php if(!empty($__il_avatar)): ?>
					<img src="<?= htmlspecialchars($__il_avatar); ?>" alt="<?= htmlspecialchars($__il_user); ?>" onerror="this.parentNode.textContent='<?= strtoupper(substr($__il_user, 0, 1)); ?>'">
				<?php else: ?>
					<?= strtoupper(substr($__il_user, 0, 1)); ?>
				<?php endif; ?>
			</div>
			<h3><?= htmlspecialchars($__il_user); ?></h3>
			<p class="sub">Enter your password or PIN to resume your session.</p>
			<div class="mp-un-field">
				<input type="password" id="mpUnlockSecret" autocomplete="current-password" placeholder="Password or PIN" aria-label="Password or PIN">
				<button type="button" class="peek" id="mpUnlockPeek" aria-label="Show password"><i class="fa fa-eye"></i></button>
			</div>
			<p class="mp-un-err" id="mpUnlockErr"></p>
			<div class="mp-un-actions">
				<button type="button" class="mp-il-btn primary" id="mpUnlockBtn"><i class="fa fa-unlock"></i> Unlock Session</button>
			</div>
			<div class="mp-un-switch">Not you? <a href="<?= base_url('logout'); ?>">Log in with a different account</a></div>
		</div>
	</div>
</div>

<script>
(function(){
	'use strict';
	var CFG = {
		timeoutMs: <?= (int)$__il_cfg['idle_timeout_minutes'] * 60000; ?>,
		warningSeconds: <?= (int)$__il_cfg['idle_warning_seconds']; ?>,
		snooze: <?= (int)$__il_cfg['snooze_enabled'] === 1 ? 'true' : 'false'; ?>,
		verifyUrl: '<?= base_url('session_lock/verify'); ?>',
		pingUrl: '<?= base_url('session_lock/ping'); ?>',
		logoutUrl: '<?= base_url('logout'); ?>',
		userId: <?= (int)$this->session->userdata('inv_userid'); ?>,
		csrfName: '<?= $this->security->get_csrf_token_name(); ?>',
		csrfHash: '<?= $this->security->get_csrf_hash(); ?>'
	};
	var idleStart = Date.now(), warnTimer = null, countTimer = null, clockTimer = null, pingTimer = null;
	var countdown = CFG.warningSeconds, warningOpen = false, snoozed = false;
	var LOCK_KEY = 'mp_idle_locked';
	function lockSet(){ try { localStorage.setItem(LOCK_KEY, String(CFG.userId)); } catch(e){} }
	function lockClear(){ try { localStorage.removeItem(LOCK_KEY); } catch(e){} }
	function lockGet(){ try { return localStorage.getItem(LOCK_KEY); } catch(e){ return null; } }

	var warnEl = document.getElementById('mpIdleWarnOverlay');
	var snzEl  = document.getElementById('mpSnoozeOverlay');

	function fmtIdle(ms){
		var s = Math.floor(ms / 1000), m = Math.floor(s / 60);
		if(m < 1) return s + ' second' + (s === 1 ? '' : 's');
		return m + ' min ' + (s % 60) + ' sec';
	}

	function resetIdle(){
		if(snoozed || warningOpen) return;
		idleStart = Date.now();
		clearTimeout(warnTimer);
		warnTimer = setTimeout(showWarning, CFG.timeoutMs);
	}

	function showWarning(){
		warningOpen = true;
		countdown = CFG.warningSeconds;
		document.getElementById('mpIdleFor').textContent = fmtIdle(Date.now() - idleStart);
		document.getElementById('mpIdleCountdown').textContent = countdown;
		var bar = document.getElementById('mpIdleBar');
		bar.style.transition = 'none'; bar.style.width = '100%';
		warnEl.classList.add('open');
		// force reflow then animate the bar
		void bar.offsetWidth;
		bar.style.transition = 'width ' + CFG.warningSeconds + 's linear';
		bar.style.width = '0%';
		countTimer = setInterval(function(){
			countdown--;
			document.getElementById('mpIdleFor').textContent = fmtIdle(Date.now() - idleStart);
			document.getElementById('mpIdleCountdown').textContent = Math.max(0, countdown);
			if(countdown <= 0){ expireWarning(); }
		}, 1000);
	}

	function hideWarning(){
		warningOpen = false;
		clearInterval(countTimer);
		warnEl.classList.remove('open');
	}

	function expireWarning(){
		hideWarning();
		if(CFG.snooze){ showSnooze(); } else { doLogout(); }
	}

	function doLogout(){
		lockClear();
		window.location.href = CFG.logoutUrl;
	}

	function showSnooze(){
		snoozed = true;
		lockSet();
		snzEl.classList.add('open');
		snzEl.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		var clk = document.getElementById('mpSnoozeClock');
		function tick(){ var d = new Date(); clk.textContent = d.toLocaleDateString() + ' ' + d.toLocaleTimeString(); }
		tick(); clockTimer = setInterval(tick, 1000);
		pingTimer = setInterval(function(){ fetch(CFG.pingUrl, {credentials:'same-origin'}).catch(function(){}); }, 5 * 60000);
	}

	function hideSnooze(){
		snoozed = false;
		lockClear();
		snzEl.classList.remove('open', 'reveal');
		snzEl.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
		clearInterval(clockTimer); clearInterval(pingTimer);
		var inp = document.getElementById('mpUnlockSecret');
		inp.value = ''; setErr('');
	}

	function revealUnlock(){
		if(!snoozed) return;
		snzEl.classList.add('reveal');
		setTimeout(function(){ document.getElementById('mpUnlockSecret').focus(); }, 250);
	}

	function setErr(msg){
		var e = document.getElementById('mpUnlockErr');
		e.textContent = msg || '';
		e.style.display = msg ? 'block' : 'none';
	}

	function submitUnlock(){
		var inp = document.getElementById('mpUnlockSecret');
		var btn = document.getElementById('mpUnlockBtn');
		var val = inp.value.trim();
		if(!val){ setErr('Please enter your password or PIN.'); inp.focus(); return; }
		btn.disabled = true;
		btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Verifying…';
		setErr('');
		var body = new URLSearchParams();
		body.append('secret', val);
		body.append(CFG.csrfName, CFG.csrfHash);
		fetch(CFG.verifyUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest'},
			body: body.toString()
		}).then(function(r){
			if(r.redirected){ doLogout(); return null; }
			return r.text().then(function(t){
				try { return JSON.parse(t); } catch(e){ return {status:'error', message:'Server error (' + r.status + '). Please try again.'}; }
			});
		}).then(function(res){
			if(res === null) return;
			if(res && res.status === 'success'){
				hideSnooze();
				idleStart = Date.now();
				resetIdle();
			} else if(res && res.expired){
				doLogout();
			} else {
				setErr((res && res.message) || 'Incorrect password or PIN.');
				var card = document.getElementById('mpUnlockCard');
				card.classList.remove('shake'); void card.offsetWidth; card.classList.add('shake');
				inp.select(); inp.focus();
			}
		}).catch(function(){
			setErr('Network error — please try again.');
		}).then(function(){
			btn.disabled = false;
			btn.innerHTML = '<i class="fa fa-unlock"></i> Unlock Session';
		});
	}

	// ---- events ----
	var throttle = 0;
	['mousemove','mousedown','keydown','scroll','touchstart','click','wheel'].forEach(function(ev){
		document.addEventListener(ev, function(){
			var now = Date.now();
			if(now - throttle < 1000) return;
			throttle = now;
			if(snoozed){ revealUnlock(); return; }
			if(!warningOpen){ idleStart = now; clearTimeout(warnTimer); warnTimer = setTimeout(showWarning, CFG.timeoutMs); }
		}, {passive: true});
	});

	document.addEventListener('visibilitychange', function(){
		if(document.hidden || snoozed || warningOpen) return;
		// Recalculate after a background tab returns
		if(Date.now() - idleStart >= CFG.timeoutMs){ showWarning(); }
	});

	document.getElementById('mpIdleStay').addEventListener('click', function(){
		hideWarning(); idleStart = Date.now(); resetIdle();
	});
	document.getElementById('mpIdleCancel').addEventListener('click', doLogout);
	document.getElementById('mpUnlockBtn').addEventListener('click', submitUnlock);
	document.getElementById('mpUnlockSecret').addEventListener('keydown', function(e){
		if(e.key === 'Enter'){ e.preventDefault(); submitUnlock(); }
	});
	document.getElementById('mpUnlockPeek').addEventListener('click', function(){
		var inp = document.getElementById('mpUnlockSecret');
		var show = inp.type === 'password';
		inp.type = show ? 'text' : 'password';
		this.innerHTML = '<i class="fa ' + (show ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
	});

	// While locked, swallow refresh/navigation shortcuts (F5, Cmd/Ctrl+R) so the
	// lock cannot be bypassed by reloading. Even if a reload slips through, the
	// lock flag below re-locks the page on load.
	document.addEventListener('keydown', function(e){
		if(!snoozed) return;
		var k = e.key;
		if(k === 'F5' || ((e.metaKey || e.ctrlKey) && (k === 'r' || k === 'R'))){
			e.preventDefault();
			e.stopPropagation();
			revealUnlock();
		}
	}, true);
	// Warn if the browser tries to leave the page while locked
	window.addEventListener('beforeunload', function(e){
		if(!snoozed) return;
		e.preventDefault();
		e.returnValue = '';
	});

	// If the screen was locked before a refresh/navigation/new tab, stay locked
	// — but only for the same user account that locked it.
	var lockOwner = lockGet();
	var wasLocked = lockOwner === String(CFG.userId);
	if(lockOwner !== null && !wasLocked){ lockClear(); } // stale flag from another user
	if(wasLocked && CFG.snooze){
		showSnooze();
		revealUnlock();
	} else {
		warnTimer = setTimeout(showWarning, CFG.timeoutMs);
	}
})();
</script>
<?php endif; endif; ?>
