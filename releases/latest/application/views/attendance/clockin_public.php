<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Clock In</title>
  <link rel="stylesheet" href="<?= base_url('theme/bootstrap/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css') ?>">
  <style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 420px; width: 100%; padding: 30px; text-align: center; }
    .card h2 { margin-bottom: 20px; font-weight: 700; }
    #preview { width: 100%; border-radius: 12px; margin-bottom: 15px; background: #f5f5f5; }
    .btn-lg { border-radius: 30px; padding: 12px 30px; font-size: 16px; }
    .status { margin-top: 10px; font-size: 14px; }
    .face-preview { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #28a745; display: none; margin: 0 auto 15px; }
    .location-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-top: 8px; }
    .location-ok { background: #d4edda; color: #155724; }
    .location-bad { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
  <div class="card">
    <h2><i class="fa fa-clock-o text-primary"></i> Clock In</h2>
    <p class="text-muted"><?= htmlspecialchars($store_name ?? 'Store') ?></p>

    <?php if(!$logged_in): ?>
      <div class="alert alert-warning">
        <p>You must be logged in to clock in.</p>
        <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg">Login</a>
      </div>
    <?php else: ?>
      <div id="step1">
        <video id="video" autoplay playsinline style="width:100%;border-radius:12px;margin-bottom:15px;"></video>
        <img id="facePreview" class="face-preview" alt="Captured face">
        <div id="controls">
          <button id="btnCapture" class="btn btn-primary btn-lg btn-block"><i class="fa fa-camera"></i> Capture Face</button>
          <button id="btnRetake" class="btn btn-default btn-block" style="display:none;"><i class="fa fa-refresh"></i> Retake</button>
          <button id="btnConfirm" class="btn btn-success btn-lg btn-block" style="display:none;margin-top:10px;"><i class="fa fa-check"></i> Confirm Clock In</button>
        </div>
        <div id="status" class="status text-muted">Allow camera access to proceed</div>
        <div id="locationStatus"></div>
      </div>
      <div id="result" style="display:none;">
        <div class="alert alert-success"><h4><i class="fa fa-check-circle"></i> Success!</h4><p id="resultMsg"></p></div>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-default">Go to Dashboard</a>
      </div>
    <?php endif; ?>
  </div>

<script>
(function(){
  var video = document.getElementById('video');
  var canvas = document.createElement('canvas');
  var stream = null;
  var capturedImage = null;
  var lat = null, lng = null;

  function startCamera(){
    if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia){
      navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}}).then(function(s){
        stream = s;
        video.srcObject = s;
        video.play();
        document.getElementById('status').textContent = 'Camera active. Click Capture Face.';
        getLocation();
      }).catch(function(err){
        document.getElementById('status').textContent = 'Camera access denied. Please enable camera.';
      });
    } else {
      document.getElementById('status').textContent = 'Camera not supported on this device.';
    }
  }

  function getLocation(){
    if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(function(pos){
        lat = pos.coords.latitude;
        lng = pos.coords.longitude;
        document.getElementById('locationStatus').innerHTML = '<span class="location-badge location-ok"><i class="fa fa-map-marker"></i> Location captured</span>';
      }, function(){
        document.getElementById('locationStatus').innerHTML = '<span class="location-badge location-bad"><i class="fa fa-map-marker"></i> Location not available</span>';
      });
    }
  }

  startCamera();

  document.getElementById('btnCapture').addEventListener('click', function(){
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    capturedImage = canvas.toDataURL('image/png');
    document.getElementById('facePreview').src = capturedImage;
    document.getElementById('facePreview').style.display = 'block';
    video.style.display = 'none';
    document.getElementById('btnCapture').style.display = 'none';
    document.getElementById('btnRetake').style.display = 'block';
    document.getElementById('btnConfirm').style.display = 'block';
    document.getElementById('status').textContent = 'Face captured. Review and confirm.';
  });

  document.getElementById('btnRetake').addEventListener('click', function(){
    capturedImage = null;
    document.getElementById('facePreview').style.display = 'none';
    video.style.display = 'block';
    document.getElementById('btnCapture').style.display = 'block';
    document.getElementById('btnRetake').style.display = 'none';
    document.getElementById('btnConfirm').style.display = 'none';
    document.getElementById('status').textContent = 'Camera active. Click Capture Face.';
  });

  document.getElementById('btnConfirm').addEventListener('click', function(){
    if(!capturedImage){ alert('Please capture your face first.'); return; }
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    document.getElementById('status').textContent = 'Submitting...';

    var payload = {face_image: capturedImage};
    if(lat !== null && lng !== null){
      payload.lat = lat;
      payload.lng = lng;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= base_url('attendance/clock_in') ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function(){
      if(xhr.readyState === 4){
        var res = JSON.parse(xhr.responseText);
        if(res.status === 'success'){
          document.getElementById('step1').style.display = 'none';
          document.getElementById('result').style.display = 'block';
          document.getElementById('resultMsg').textContent = res.message;
        } else {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa fa-check"></i> Confirm Clock In';
          document.getElementById('status').textContent = res.message;
          alert(res.message);
        }
      }
    };
    var data = 'face_image=' + encodeURIComponent(capturedImage);
    data += '&<?= $this->security->get_csrf_token_name() ?>=' + encodeURIComponent('<?= $this->security->get_csrf_hash() ?>');
    if(lat !== null) data += '&lat=' + lat;
    if(lng !== null) data += '&lng=' + lng;
    xhr.send(data);
  });

  window.addEventListener('beforeunload', function(){
    if(stream) stream.getTracks().forEach(function(t){ t.stop(); });
  });
})();
</script>
</body>
</html>
