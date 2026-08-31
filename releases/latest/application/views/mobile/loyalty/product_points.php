<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Product Points</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-bg); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1px solid var(--mp-border); }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 18px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .section-head { padding: 16px; font-size: 16px; font-weight: 700; border-bottom: 1px solid var(--mp-border); }
    .section-body { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input[type="number"] { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; background: #fff; font-family: inherit; }
    .mp-select { position: relative; }
    .mp-select .trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: space-between; font-size: 14px; color: var(--mp-ink); }
    .mp-select .trigger i { color: var(--mp-muted); }
    .mp-select .options { display: none; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; max-height: 220px; overflow-y: auto; margin-top: 6px; z-index: 10; }
    .mp-select.open .options { display: block; }
    .mp-select .option { padding: 11px 14px; font-size: 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .mp-select .option:last-child { border-bottom: none; }
    .mp-select .option.selected { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    .mp-select .hidden-select { position: absolute; opacity: 0; pointer-events: none; }
    .save-btn { width: 100%; padding: 16px; border-radius: 14px; border: none; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .save-btn:disabled { opacity: 0.6; }
    .btn-link { display: inline-block; width: 100%; padding: 10px 14px; border-radius: 12px; text-decoration: none; font-size: 13px; font-weight: 600; background: var(--mp-bg); color: var(--mp-ink); text-align: center; margin-top: 10px; border: none; cursor: pointer; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .pp-card { background: var(--mp-bg); border-radius: 14px; padding: 14px; display: flex; justify-content: space-between; align-items: center; }
    .pp-card .name { font-weight: 700; font-size: 15px; }
    .pp-card .meta { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .icon-btn { border: none; background: transparent; color: var(--mp-primary); font-size: 15px; cursor: pointer; padding: 4px; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Product Points</h1>
        </div>
      </div>

      <div class="section">
        <div class="section-head">Add / Edit Product Points</div>
        <div class="section-body">
          <form id="ppForm" action="<?= base_url('loyalty/save_product_points'); ?>" method="post" onsubmit="return handleSave(this, 'Product points saved.');">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="id" id="pp_id" value="">
            <div class="field">
              <label>Product</label>
              <?= renderMpSelect('item_id', '', array_reduce($items, function($c, $i){ $c[$i->id] = $i->item_name; return $c; }, []), 'itemSelect'); ?>
            </div>
            <div class="field">
              <label>Bonus Points</label>
              <input type="number" step="0.01" name="bonus_points" id="bonus_points" value="0">
            </div>
            <div class="field">
              <label>Bonus Type</label>
              <?= renderMpSelect('bonus_type', 'fixed', ['fixed' => 'Fixed points', 'percentage' => 'Percentage of base'], 'btSelect'); ?>
            </div>
            <button type="submit" class="save-btn" id="ppSave"><i class="fa fa-save"></i> Save Product Points</button>
            <button type="button" class="btn-link" onclick="resetPpForm()">Cancel / New</button>
          </form>
        </div>
      </div>

      <div class="search-bar">
        <i class="fa fa-search"></i>
        <input type="text" id="ppSearch" placeholder="Search by product name">
      </div>

      <div class="section">
        <div class="section-head">Existing Product Points</div>
        <div class="section-body" id="ppList">
          <?php if(!empty($product_points)): foreach($product_points as $pp): ?>
            <div class="pp-card" data-name="<?= htmlspecialchars(strtolower($pp->item_name ?? '')); ?>">
              <div>
                <div class="name"><?= htmlspecialchars($pp->item_name ?? 'Item #'.$pp->item_id); ?></div>
                <div class="meta"><?= (float)($pp->bonus_points); ?> points &bull; <?= htmlspecialchars($pp->bonus_type ?? 'fixed'); ?></div>
              </div>
              <button type="button" class="icon-btn" onclick='editPp(<?= json_encode($pp); ?>)'><i class="fa fa-edit"></i></button>
            </div>
          <?php endforeach; else: ?>
            <div class="empty-state"><i class="fa fa-cube"></i><div>No product points set.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>

  <?php
  function renderMpSelect($name, $selected, $options, $id=null){
    $selId = $id ?: $name;
    $html = '<div class="mp-select" data-select="'.$selId.'">';
    $html .= '<select name="'.$name.'" id="'.$selId.'" class="hidden-select">';
    foreach($options as $k => $label){
      $html .= '<option value="'.htmlspecialchars($k).'" '.($k == $selected ? 'selected' : '').'>'.htmlspecialchars($label).'</option>';
    }
    $html .= '</select>';
    $html .= '<div class="trigger"><span class="selected-text">'.htmlspecialchars($options[$selected] ?? reset($options)).'</span><i class="fa fa-chevron-down"></i></div>';
    $html .= '<div class="options">';
    foreach($options as $k => $label){
      $html .= '<div class="option '.($k == $selected ? 'selected' : '').'" data-value="'.htmlspecialchars($k).'">'.htmlspecialchars($label).'</div>';
    }
    $html .= '</div></div>';
    return $html;
  }
  ?>

  <script>
    function initMpSelect(select){
      const trigger = select.querySelector('.trigger');
      const options = select.querySelector('.options');
      const realSelect = select.querySelector('.hidden-select');
      const selectedText = select.querySelector('.selected-text');

      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select').forEach(function(other){ if(other !== select) other.classList.remove('open'); });
        select.classList.toggle('open');
      });

      options.querySelectorAll('.option').forEach(function(opt){
        opt.addEventListener('click', function(){
          realSelect.value = this.dataset.value;
          selectedText.textContent = this.textContent;
          options.querySelectorAll('.option').forEach(function(o){ o.classList.remove('selected'); });
          this.classList.add('selected');
          select.classList.remove('open');
        });
      });
    }

    document.querySelectorAll('.mp-select').forEach(initMpSelect);
    document.addEventListener('click', function(e){
      document.querySelectorAll('.mp-select').forEach(function(select){ if(!select.contains(e.target)) select.classList.remove('open'); });
    });

    function setMpSelect(name, value){
      const select = document.querySelector('.mp-select[data-select="'+name+'"]');
      if(!select) return;
      const real = select.querySelector('.hidden-select');
      const text = select.querySelector('.selected-text');
      real.value = value;
      const opt = select.querySelector('.option[data-value="'+value.replace(/"/g,'\\"')+'"]');
      if(opt){
        text.textContent = opt.textContent;
        select.querySelectorAll('.option').forEach(function(o){ o.classList.remove('selected'); });
        opt.classList.add('selected');
      }
    }

    function handleSave(form, successMsg){
      const btn = form.querySelector('.save-btn');
      if(btn) btn.disabled = true;
      const formData = new FormData(form);
      fetch(form.action, {method: 'POST', body: formData})
        .then(r => r.text())
        .then(d => {
          if(btn) btn.disabled = false;
          if(d.trim().indexOf('success') === 0 || d.trim() === 'success'){
            mpSuccess(successMsg);
            form.reset();
            document.getElementById('pp_id').value = '';
          } else {
            mpError(d || 'Save failed.');
          }
        })
        .catch(() => { if(btn) btn.disabled = false; mpError('Save failed.'); });
      return false;
    }

    function editPp(pp){
      document.getElementById('pp_id').value = pp.id || '';
      document.getElementById('bonus_points').value = pp.bonus_points || 0;
      setMpSelect('itemSelect', String(pp.item_id || ''));
      setMpSelect('btSelect', (pp.bonus_type || 'fixed'));
      document.getElementById('bonus_points').focus();
    }

    function resetPpForm(){
      document.getElementById('ppForm').reset();
      document.getElementById('pp_id').value = '';
      setMpSelect('itemSelect', '');
      setMpSelect('btSelect', 'fixed');
    }

    const searchInput = document.getElementById('ppSearch');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#ppList .pp-card').forEach(function(el){
          const name = el.dataset.name || '';
          el.style.display = (name.indexOf(term) !== -1) ? 'flex' : 'none';
        });
      });
    }
  </script>
</body>
</html>
