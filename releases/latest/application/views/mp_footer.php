<?php $CI =& get_instance(); ?>
  </main><!-- /.mp-main -->
</div><!-- /.mp-shell -->

<!-- Footer -->
<footer class="copyright">
  &copy; <?= date('Y'); ?> <?= isset($SITE_TITLE) ? htmlspecialchars($SITE_TITLE) : 'MartPoint Retail' ?>. Powered by MartPoint Retail v<?= isset($VERSION) ? $VERSION : app_version(); ?>.
</footer>

<!-- Clock In Modal (App-wide) -->
<div class="modal" id="appClockInModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-clock-o"></i> <span id="appClockTitle">Clock In</span></h4>
      </div>
      <div class="modal-body text-center">
        <video id="appClockVideo" autoplay playsinline muted style="width:100%;max-width:300px;border-radius:8px;"></video>
        <canvas id="appClockCanvas" style="display:none;"></canvas>
        <p id="appClockStatus" class="text-muted">Click the button below to capture your face.</p>
        <button id="appCaptureBtn" class="btn btn-primary btn-block"><i class="fa fa-camera"></i> Capture Face</button>
        <button id="appConfirmClockBtn" class="btn btn-success btn-block" style="display:none;"><i class="fa fa-check"></i> Confirm Clock In</button>
      </div>
    </div>
  </div>
</div>

<?php if(!isset($skip_shared_assets)): ?>
<!-- SOUND CODE -->
<?php include "comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include "comman/code_js.php"; ?>
<?php endif; ?>

<!-- Wrap every DataTable in a scrollable div so the info/pagination footer stays below the scroll area -->
<script>
if(typeof jQuery !== 'undefined'){
  $(document).on('init.dt', function(e, settings){
    if(!$.fn.dataTable || !settings) return;
    var api = new $.fn.dataTable.Api(settings);
    var tbl = $(api.table().node());
    if(tbl.parent().hasClass('mp-dt-scroll')) return;
    tbl.wrap('<div class="mp-dt-scroll"></div>');
  });
}
</script>

<!-- MartPoint Assist — always loaded on every desktop page (CSS is in mp_header) -->
<?php if(!defined('MP_ASSIST_LOADED')): ?>
<?php define('MP_ASSIST_LOADED', true); ?>
<script src="<?php echo $theme_link; ?>js/assist.js?v=14"></script>
<?php $this->load->view('assist/panel'); ?>
<?php endif; ?>

<!-- ChartJS 1.0.1 -->
<script src="<?php echo $theme_link; ?>plugins/chartjs/Chart.min.js"></script>
<script>
'use strict';
window.chartColors = {
  red: 'rgb(255, 50, 10)', orange: 'rgb(255, 102, 64)', yellow: 'rgb(230, 184, 0)',
  green: 'rgb(0, 179, 0)', blue: 'rgb(0, 0, 230)', purple: 'rgb(134, 0, 179)', grey: 'rgb(117, 117, 163)'
};
(function(global) {
  var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var COLORS = ['#4dc9f6','#f67019','#f53794','#537bc4','#acc236','#166a8f','#00a950','#58595b','#8549ba'];
  var Samples = global.Samples || (global.Samples = {});
  var Color = global.Color;
  Samples.utils = {
    srand: function(seed) { this._seed = seed; },
    rand: function(min, max) { var seed = this._seed; min = min === undefined ? 0 : min; max = max === undefined ? 1 : max; this._seed = (seed * 9301 + 49297) % 233280; return min + (this._seed / 233280) * (max - min); },
    numbers: function(config) { var cfg = config || {}; var min = cfg.min || 0, max = cfg.max || 1; var from = cfg.from || [], count = cfg.count || 8; var decimals = cfg.decimals || 8; var dfactor = Math.pow(10, decimals) || 0; var data = []; for (var i = 0; i < count; ++i) { var value = (from[i] || 0) + this.rand(min, max); if (this.rand() <= (cfg.continuity || 1)) { data.push(Math.round(dfactor * value) / dfactor); } else { data.push(null); } } return data; },
    labels: function(config) { var cfg = config || {}; var min = cfg.min || 0, max = cfg.max || 100; var count = cfg.count || 8; var step = (max - min) / count; var decimals = cfg.decimals || 8; var dfactor = Math.pow(10, decimals) || 0; var values = []; for (var i = min; i < max; i += step) { values.push('' + Math.round(dfactor * i) / dfactor); } return values; },
    months: function(config) { var cfg = config || {}; var count = cfg.count || 12; var section = cfg.section; var values = []; for (var i = 0; i < count; ++i) { var value = MONTHS[Math.ceil(i) % 12]; values.push(value.substring(0, section)); } return values; },
    color: function(index) { return COLORS[index % COLORS.length]; },
    transparentize: function(color, opacity) { var alpha = opacity === undefined ? 0.5 : 1 - opacity; return Color(color).alpha(alpha).rgbString(); }
  };
  window.randomScalingFactor = function() { return Math.round(Samples.utils.rand(-100, 100)); };
  Samples.utils.srand(Date.now());
}(this));

<?php if(is_user() && isset($sub_month) && isset($tot_subscribes)){ ?>
  function createConfig(position) {
    return {
      type: 'line',
      data: {
        labels: ["<?=$sub_month[6].'-'.$sub_year[6]?>","<?=$sub_month[5].'-'.$sub_year[5]?>","<?=$sub_month[4].'-'.$sub_year[4]?>","<?=$sub_month[3].'-'.$sub_year[3]?>","<?=$sub_month[2].'-'.$sub_year[2]?>","<?=$sub_month[1].'-'.$sub_year[1]?>","<?=$sub_month[0].'-'.$sub_year[0]?>"],
        datasets: [{ label: 'Total Subscriptions', borderColor: window.chartColors.red, backgroundColor: window.chartColors.red, data: ["<?=$tot_subscribes[6]?>","<?=$tot_subscribes[5]?>","<?=$tot_subscribes[4]?>","<?=$tot_subscribes[3]?>","<?=$tot_subscribes[2]?>","<?=$tot_subscribes[1]?>","<?=$tot_subscribes[0]?>"], fill: false }]
      },
      options: { responsive: true, title: { display: true, text: 'Tooltip Position: ' + position }, tooltips: { position: position, mode: 'index', intersect: false } }
    };
  }
  window.onload = function() {
    var container = document.querySelector('.subscription_chart');
    ['average'].forEach(function(position) {
      var div = document.createElement('div'); div.classList.add('chart-container');
      var canvas = document.createElement('canvas'); div.appendChild(canvas); container.appendChild(div);
      var ctx = canvas.getContext('2d'); var config = createConfig(position); new Chart(ctx, config);
    });
  };
<?php } ?>

<?php if($CI->permissions('dashboard_view') && isset($tranding_item)){ ?>
$(function(){
  // Sales vs Expenses chart is now a server-rendered SVG (#salesTrendChart)
});

var doughnutCanvas = document.getElementById("doughnut-chart");
if(doughnutCanvas){
new Chart(doughnutCanvas, {
  type: 'doughnut',
  data: {
    labels: [<?php if($tranding_item['tot_rec'] > 0){?><?php for($i=$tranding_item['tot_rec']; $i>0; $i--){ ?>'<?= $tranding_item[$i]['name'] ?>',<?php } ?><?php } ?>],
    datasets: [{ label: "Top Items", backgroundColor: ["#2563EB","#F97316","#16A34A","#F59E0B","#DC2626","#8B5CF6"], borderWidth: 0, hoverOffset: 4, data: [<?php if($tranding_item['tot_rec'] > 0){?><?php for($i=$tranding_item['tot_rec']; $i>0; $i--){ ?>'<?= $tranding_item[$i]['sales_qty'] ?>',<?php } ?><?php } ?>] }]
  },
  options: { cutoutPercentage: 60, legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, fontColor: '#64748B', fontSize: 12, fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif" } }, title: { display: false }, tooltips: { backgroundColor: '#0F172A', titleFontSize: 13, bodyFontSize: 13, cornerRadius: 8, xPadding: 12, yPadding: 12, displayColors: true, boxWidth: 10, boxHeight: 10 } }
});
}
<?php } ?>
</script>

<script type="text/javascript">
  var base_url='<?= base_url(); ?>';
  <?php if(is_admin() && store_module()){ ?> $("#stores_details").DataTable(); <?php } ?>
</script>

<!-- Clock In Script -->
<script>
(function(){
  var clockStream = null;
  var clockImage = null;
  var isClockedIn = false;

  function updateClockButton(clockedIn){
    isClockedIn = clockedIn;
    $('#appClockInBtn .clock-label').text(isClockedIn ? 'Clock Out' : 'Clock In');
    $('#appClockInBtn i').attr('class', isClockedIn ? 'fa fa-sign-out text-red' : 'fa fa-clock-o');
    $('#appClockInBtn').attr('title', isClockedIn ? 'Clock Out' : 'Clock In');
    $('#appClockTitle').text(isClockedIn ? 'Clock Out' : 'Clock In');
    $('#appConfirmClockBtn').html(isClockedIn ? '<i class="fa fa-check"></i> Confirm Clock Out' : '<i class="fa fa-check"></i> Confirm Clock In');
  }

  function checkStatus(){
    $.getJSON('<?php echo base_url('attendance/status_ajax'); ?>', function(res){
      updateClockButton(res.clocked_in);
    });
  }

  checkStatus();
  setInterval(checkStatus, 30000);

  $('#appClockInBtn').click(function(e){
    e.preventDefault();
    $('#appCaptureBtn').show();
    $('#appConfirmClockBtn').hide();
    $('#appClockStatus').text('Click the button below to capture your face.');
    clockImage = null;
    $('#appClockInModal').modal('show');
    setTimeout(startCamera, 100);
  });

  function startCamera(){
    var video = document.getElementById('appClockVideo');
    if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia){
      navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}, audio:false}).then(function(stream){
        clockStream = stream;
        video.srcObject = stream;
        video.muted = true;
        video.play();
      }).catch(function(err){
        $('#appClockStatus').text('Camera access denied or unavailable. Please allow camera access or check your device.');
        console.error('Clock-in camera error:', err);
      });
    } else {
      $('#appClockStatus').text('Camera is not supported on this browser/device.');
    }
  }

  function stopCamera(){
    if(clockStream){ clockStream.getTracks().forEach(function(t){ t.stop(); }); clockStream = null; }
  }

  $('#appCaptureBtn').click(function(){
    var video = document.getElementById('appClockVideo');
    var canvas = document.getElementById('appClockCanvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    clockImage = canvas.toDataURL('image/png');
    $('#appClockStatus').text('Face captured. Click Confirm to proceed.');
    $('#appCaptureBtn').hide();
    $('#appConfirmClockBtn').show();
  });

  $('#appConfirmClockBtn').click(function(){
    if(!clockImage){ alert('Please capture your face first.'); return; }
    $('#appClockStatus').text('Getting location...');
    var payload = {face_image: clockImage};
    if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(function(pos){
        payload.lat = pos.coords.latitude;
        payload.lng = pos.coords.longitude;
        sendClock(payload);
      }, function(){
        sendClock(payload);
      });
    } else {
      sendClock(payload);
    }
  });

  function sendClock(payload){
    var wasClockedIn = isClockedIn;
    var url = isClockedIn ? '<?php echo base_url('attendance/clock_out'); ?>' : '<?php echo base_url('attendance/clock_in'); ?>';
    $.post(url, payload, function(res){
      if(res.status === 'success'){
        if(typeof toastr !== 'undefined') toastr['success'](res.message);
        $('#appClockInModal').modal('hide');
        updateClockButton(!wasClockedIn);
        var dashCard = $('#dashClockStatusCard');
        if(dashCard.length){
          if(!wasClockedIn){
            dashCard.closest('.mp-section').slideUp(200);
          } else {
            dashCard.closest('.mp-section').show();
          }
        }
        setTimeout(checkStatus, 500);
      } else {
        if(typeof toastr !== 'undefined') toastr['error'](res.message || 'Clock action failed');
      }
    }, 'json');
  }

  $('#appClockInModal').on('hidden.bs.modal', function(){ stopCamera(); });
})();
</script>

<!-- Logout enforcement -->
<script>
$(function(){
  var logoutLink = $('a[href$="logout"]');
  if(!logoutLink.length) return;

  logoutLink.on('click', function(e){
    var href = $(this).attr('href');
    if($(this).data('processing')) return false;

    e.preventDefault();
    var $self = $(this);
    $self.data('processing', true);

    $.post('<?php echo base_url('attendance/needs_clock_out_ajax'); ?>', {
      '<?php echo $this->security->get_csrf_token_name(); ?>':'<?php echo $this->security->get_csrf_hash(); ?>'
    }, function(res){
      $self.data('processing', false);
      if(res.needs_clock_out){
        if(typeof swal === 'undefined'){
          if(!confirm('You have not clocked out today. Clock out now before logging out?')){
            return;
          }
          doClockOutThenLogout(href);
          return;
        }
        swal({
          title: "Clock Out Required",
          text: "You haven't clocked out for today. Please clock out before signing out.",
          icon: "warning",
          buttons: { cancel: "Stay Logged In", clockout: { text: "Clock Out & Logout", value: "clockout", closeModal: false } }
        }).then(function(value){
          if(value === 'clockout'){ doClockOutThenLogout(href); }
        });
      } else {
        window.location.href = href;
      }
    }, 'json').fail(function(){
      $self.data('processing', false);
      window.location.href = href;
    });
  });

  function doClockOutThenLogout(href){
    $.post('<?php echo base_url('attendance/clock_out_ajax'); ?>', {
      user_id: <?php echo (int)$this->session->userdata('inv_userid'); ?>,
      '<?php echo $this->security->get_csrf_token_name(); ?>':'<?php echo $this->security->get_csrf_hash(); ?>'
    }, function(res){
      if(res.status === 'success'){
        toastr['success']('Clocked out successfully. Signing out...');
        setTimeout(function(){ window.location.href = href; }, 800);
      } else {
        toastr['error'](res.message || 'Failed to clock out');
        if(typeof swal !== 'undefined'){
          swal({ title: "Clock Out Failed", text: (res.message || 'Failed to clock out') + ". Please try again or contact admin.", icon: "error" });
        }
      }
    }, 'json').fail(function(){
      toastr['error']('Network error. Please try again.');
    });
  }
});
</script>

<!-- Offline Support -->
<script src="<?php echo base_url('theme/js/mp-offline-db.js'); ?>?v=4"></script>
<script>
(function(){
  function updateNetworkStatus(){
    var pill = document.getElementById('mpConnectionStatus');
    if (!pill) return;
    var dot = pill.querySelector('.mp-status-dot');
    var text = pill.querySelector('.mp-status-text');
    if (navigator.onLine) {
      pill.classList.remove('offline');
      if(text) text.textContent = 'Online';
    } else {
      pill.classList.add('offline');
      if(text) text.textContent = 'Offline';
    }
  }
  window.addEventListener('online', updateNetworkStatus);
  window.addEventListener('offline', updateNetworkStatus);
  updateNetworkStatus();

  function updatePendingSalesBadge(){
    if (typeof MPOfflineDB === 'undefined') return;
    Promise.all([MPOfflineDB.countPendingSales(), MPOfflineDB.countPendingPurchases()]).then(function(results){
      var count = results[0] + results[1];
      var badge = document.getElementById('pendingSalesBadge');
      if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'inline-block' : 'none'; }
    }).catch(function(){});
  }
  function checkCacheAge(){
    if (typeof MPOfflineDB === 'undefined') return;
    MPOfflineDB.getMeta('lastSync').then(function(ts){
      if (ts) {
        var d = new Date(ts);
        var hoursAgo = Math.round((Date.now() - d) / 3600000);
        var label = hoursAgo < 1 ? 'just now' : hoursAgo + 'h ago';
        $('#syncOfflineBtn').attr('title', 'Last synced: ' + d.toLocaleString() + ' (' + label + ')');
        if (hoursAgo >= 24) {
          mpShowToast('Cache Stale', 'Your offline cache is ' + hoursAgo + ' hours old. Please sync for latest data.', 'warning');
          $('#syncOfflineBtn').find('i').addClass('text-red');
        }
      } else {
        $('#syncOfflineBtn').attr('title', 'Never synced — click to cache offline data');
      }
    }).catch(function(){});
  }

  $(document).ready(function(){
    setTimeout(updatePendingSalesBadge, 1000);
    checkCacheAge();

    $('#syncOfflineBtn').on('click', function(e){
      e.preventDefault();
      if (!navigator.onLine) { mpShowToast('Cannot sync while offline. Please connect to network first.', '', 'warning'); return; }
      if (typeof MPOfflineDB === 'undefined') { mpShowToast('Offline database not available.', '', 'danger'); return; }
      var $btn = $(this);
      var originalHtml = $btn.html();
      $btn.html('<i class="fa fa-refresh fa-spin"></i><span class="hidden-xs"> Syncing...</span>');
      mpShowToast('Offline Sync', 'Syncing data for offline use...', 'info');
      var currentStoreId = <?= json_encode((int)$this->session->userdata('store_id') ?: 1); ?>;
      // Wrap jQuery AJAX in native Promises so .catch() and .finally() work
      function ajx(url){
        return new Promise(function(resolve, reject){
          $.ajax({ url: url, method: 'GET', dataType: 'json', data: { store_id: currentStoreId } })
            .done(function(res){ resolve(res); })
            .fail(function(jqXHR){ reject(jqXHR); });
        });
      }
      var itemCount = 0, custCount = 0, supCount = 0;
      ajx('<?= base_url('items/sync_items_for_offline'); ?>').then(function(itemRes){
        itemCount = Array.isArray(itemRes) ? itemRes.length : 0;
        return MPOfflineDB.saveItems(itemRes);
      }).then(function(){
        mpShowToast('Offline Sync', itemCount + ' items cached. Syncing customers...', 'info');
        return ajx('<?= base_url('customers/sync_customers_for_offline'); ?>');
      }).then(function(custRes){
        custCount = Array.isArray(custRes) ? custRes.length : 0;
        return MPOfflineDB.saveCustomers(custRes);
      }).then(function(){
        mpShowToast('Offline Sync', custCount + ' customers cached. Syncing suppliers...', 'info');
        return ajx('<?= base_url('suppliers/sync_suppliers_for_offline'); ?>');
      }).then(function(supRes){
        supCount = Array.isArray(supRes) ? supRes.length : 0;
        return MPOfflineDB.saveSuppliers(supRes);
      }).then(function(){
        var now = new Date();
        MPOfflineDB.setMeta('lastSync', now.toISOString()).catch(function(){});
        mpShowToast('Sync Complete', 'Offline sync complete. ' + itemCount + ' items, ' + custCount + ' customers, ' + supCount + ' suppliers cached.', 'success');
        $('#syncOfflineBtn').attr('title', 'Last synced: ' + now.toLocaleTimeString()).find('i').removeClass('text-red');
      }).catch(function(err){
        mpShowToast('Sync Failed', 'Sync failed: ' + (err && err.statusText ? err.statusText : 'unknown error'), 'danger');
      }).finally(function(){
        $btn.html(originalHtml);
      });
    });
  });
})();
</script>

<div class="mp-toast-container" id="mpToastContainer"></div>

<script>
const mpToastIcons = {
  success: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  danger: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
  warning: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
};
function mpShowToast(title, message, type){
  const container = document.getElementById('mpToastContainer');
  if(!container) return;
  const toast = document.createElement('div');
  toast.className = 'mp-toast ' + (type || 'success');
  toast.innerHTML = '<div class="mp-toast-icon">' + (mpToastIcons[type] || mpToastIcons.success) + '</div>' +
    '<div class="mp-toast-content"><div class="mp-toast-title">' + (title || '') + '</div><div class="mp-toast-message">' + (message || '') + '</div></div>' +
    '<button class="mp-toast-close" onclick="this.parentElement.remove()">&times;</button>';
  container.appendChild(toast);
  requestAnimationFrame(function(){ toast.classList.add('show'); });
  setTimeout(function(){
    toast.classList.add('hide');
    setTimeout(function(){ toast.remove(); }, 300);
  }, 4500);
}
</script>

<!-- Close dropdowns on outside click -->
<script>
$(document).on('click', function(e){
  if(!$(e.target).closest('.mp-user-menu').length){ $('#mpUserDropdown').removeClass('open'); }
});
</script>

<?php if(!empty($extra_js_files) && is_array($extra_js_files) && empty($GLOBALS['__mp_extra_js_loaded'])): ?>
<?php foreach($extra_js_files as $js): ?><script src="<?php echo $theme_link . $js; ?>"></script><?php endforeach; ?>
<?php endif; ?>

<script>
if(typeof jQuery !== 'undefined'){
  $(function(){
    // Keep any sidebar section open when one of its child links is active
    $('.mp-nav-submenu .mp-nav-item.active').each(function(){
      $(this).closest('.mp-nav-group').addClass('open');
    });
  });
}
</script>

</body>
</html>
