<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-bg: #F3F4F6; --mp-surface: #FFFFFF; --mp-border: #E5E7EB; --mp-ink: #111827; --mp-muted: #6B7280; --safe-bottom: env(safe-area-inset-bottom); }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: var(--mp-bg); color: var(--mp-ink); }
    #app { width: 100%; min-height: 100vh; padding: 0 0 90px; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; flex: 1; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: var(--mp-surface); border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; text-align: center; }
    .shift-badge { display: inline-flex; align-items: center; gap: 6px; background: #E0E7FF; color: var(--mp-primary); padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
    .video-wrap { position: relative; width: 100%; max-width: 360px; margin: 0 auto 14px; border-radius: 14px; overflow: hidden; background: #000; aspect-ratio: 4/3; }
    #video, #preview { width: 100%; height: 100%; object-fit: cover; }
    #preview { display: none; }
    .face-preview { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 3px solid var(--mp-success); display: none; margin: 0 auto 14px; }
    .status { font-size: 13px; color: var(--mp-muted); margin: 8px 0; min-height: 20px; }
    .location-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 20px; font-size: 12px; margin-top: 4px; }
    .location-ok { background: #D1FAE5; color: #065F46; }
    .location-bad { background: #FEE2E2; color: #991B1B; }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 10px; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-success { background: var(--mp-success); color: #fff; }
    .btn-danger { background: var(--mp-danger); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-ink); }
    .btn:disabled { opacity: .6; }
    .result { display: none; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="card">
        <div class="shift-badge"><i class="fa fa-clock-o"></i> <?= htmlspecialchars($shift->shift_name ?? 'On Duty'); ?></div>
        <div id="step1">
          <div class="video-wrap" id="videoWrap">
            <video id="video" autoplay playsinline muted></video>
            <img id="preview" alt="Captured">
          </div>
          <img id="facePreview" class="face-preview" alt="Captured face">
          <div class="status" id="status">Allow camera access to proceed</div>
          <div id="locationStatus"></div>

          <button id="btnCapture" class="btn btn-primary"><i class="fa fa-camera"></i> Capture Face</button>
          <button id="btnRetake" class="btn btn-secondary" style="display:none;"><i class="fa fa-refresh"></i> Retake</button>
          <button id="btnConfirm" class="btn <?= $clock_action == 'out' ? 'btn-danger' : 'btn-success'; ?>" style="display:none;"><i class="fa fa-<?= $clock_action == 'out' ? 'sign-out' : 'check'; ?>"></i> Confirm <?= $clock_action == 'out' ? 'Clock Out' : 'Clock In'; ?></button>
        </div>

        <div id="result" class="result">
          <div class="status" style="font-size:16px; font-weight:700; color:var(--mp-success);" id="resultMsg"></div>
          <a href="<?= base_url('mobile'); ?>" class="btn btn-primary" style="margin-top:10px;">Back to Dashboard</a>
        </div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
  </div>

  <?php if($this->session->flashdata('warning')): ?>
    <?php $flash_warning = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('warning')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_warning, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'warning'});</script>
  <?php endif; ?>
  <?php if($this->session->flashdata('success')): ?>
    <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
  <?php endif; ?>
  <?php if($this->session->flashdata('failed')): ?>
    <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
  <?php endif; ?>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
  (function(){
    var video = document.getElementById('video');
    var preview = document.getElementById('preview');
    var facePreview = document.getElementById('facePreview');
    var canvas = document.createElement('canvas');
    var stream = null;
    var capturedImage = null;
    var lat = null, lng = null;
    var action = '<?= $clock_action; ?>';
    var status = document.getElementById('status');
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

    function startCamera(){
      if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia){
        navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}}).then(function(s){
          stream = s;
          video.srcObject = s;
          video.play();
          status.textContent = 'Camera active. Click Capture Face.';
          getLocation();
        }).catch(function(err){
          status.textContent = 'Camera access denied. Please enable camera.';
        });
      } else {
        status.textContent = 'Camera not supported on this device.';
      }
    }

    function getLocation(callback){
      if(!navigator.geolocation){ if(callback) callback(); return; }
      navigator.geolocation.getCurrentPosition(function(pos){
        lat = pos.coords.latitude;
        lng = pos.coords.longitude;
        document.getElementById('locationStatus').innerHTML = '<span class="location-badge location-ok"><i class="fa fa-map-marker"></i> Location captured</span>';
        if(callback) callback();
      }, function(err){
        document.getElementById('locationStatus').innerHTML = '<span class="location-badge location-bad"><i class="fa fa-map-marker"></i> Location not available</span>';
        if(callback) callback();
      }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 });
    }

    startCamera();

    document.getElementById('btnCapture').addEventListener('click', function(){
      canvas.width = video.videoWidth || 640;
      canvas.height = video.videoHeight || 480;
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
      capturedImage = canvas.toDataURL('image/png');
      preview.src = capturedImage;
      preview.style.display = 'block';
      video.style.display = 'none';
      facePreview.src = capturedImage;
      facePreview.style.display = 'block';
      document.getElementById('videoWrap').style.display = 'none';
      document.getElementById('btnCapture').style.display = 'none';
      document.getElementById('btnRetake').style.display = 'inline-flex';
      document.getElementById('btnConfirm').style.display = 'inline-flex';
      status.textContent = 'Face captured. Review and confirm.';
    });

    document.getElementById('btnRetake').addEventListener('click', function(){
      capturedImage = null;
      preview.style.display = 'none';
      facePreview.style.display = 'none';
      document.getElementById('videoWrap').style.display = 'block';
      video.style.display = 'block';
      document.getElementById('btnCapture').style.display = 'inline-flex';
      document.getElementById('btnRetake').style.display = 'none';
      document.getElementById('btnConfirm').style.display = 'none';
      status.textContent = 'Camera active. Click Capture Face.';
    });

    function submitClock(btn){
      var url = action === 'out' ? '<?= base_url('attendance/clock_out'); ?>' : '<?= base_url('attendance/clock_in'); ?>';
      var data = 'face_image=' + encodeURIComponent(capturedImage) + '&' + csrfName + '=' + encodeURIComponent(csrfHash);
      if(lat !== null) data += '&lat=' + lat;
      if(lng !== null) data += '&lng=' + lng;

      var xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function(){
        if(xhr.readyState === 4){
          var res = {status:'error', message:'Network error. Please try again.'};
          try { res = JSON.parse(xhr.responseText); } catch(e){}
          if(res.status === 'success'){
            document.getElementById('step1').style.display = 'none';
            document.getElementById('result').style.display = 'block';
            document.getElementById('resultMsg').textContent = res.message;
          } else {
            if(btn) btn.disabled = false;
            if(btn) btn.innerHTML = '<i class="fa fa-<?= $clock_action == 'out' ? 'sign-out' : 'check'; ?>"></i> Confirm <?= $clock_action == 'out' ? 'Clock Out' : 'Clock In'; ?>';
            status.textContent = res.message;
            if(typeof mpAlert !== 'undefined'){ mpAlert(res.message, 'danger'); }
          }
        }
      };
      xhr.send(data);
    }

    document.getElementById('btnConfirm').addEventListener('click', function(){
      if(!capturedImage){ status.textContent = 'Please capture your face first.'; return; }
      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
      status.textContent = 'Getting location...';

      getLocation(function(){
        status.textContent = 'Submitting...';
        submitClock(btn);
      });
    });

    window.addEventListener('beforeunload', function(){
      if(stream) stream.getTracks().forEach(function(t){ t.stop(); });
    });
  })();
  </script>
</body>
</html>
