<?php
$quickKind=$lookup_kind ?? 'category';
$quickPermission=$quickKind==='brand'?'brand_add':'items_category_add';
$quickTarget=$lookup_target ?? ($quickKind==='brand'?'m_brand_id':'m_category_id');
$quickController =& get_instance();
if($quickController->permissions($quickPermission)):
?>
<details class="mp-quick-lookup" data-kind="<?= $quickKind; ?>" data-csrf-name="<?= htmlspecialchars($this->security->get_csrf_token_name(),ENT_QUOTES,'UTF-8'); ?>" data-csrf-hash="<?= htmlspecialchars($this->security->get_csrf_hash(),ENT_QUOTES,'UTF-8'); ?>" data-target="<?= htmlspecialchars($quickTarget,ENT_QUOTES,'UTF-8'); ?>" data-url="<?= htmlspecialchars(base_url('catalogue_lookup/create'),ENT_QUOTES,'UTF-8'); ?>" style="margin:7px 0;max-width:100%;">
  <summary style="cursor:pointer;color:var(--mp-primary,#0057ff);font-size:13px;">+ New <?= $quickKind; ?></summary>
  <div style="display:flex;gap:7px;flex-wrap:wrap;padding-top:8px;">
    <input type="text" class="mp-lookup-name form-control" maxlength="100" aria-label="New <?= $quickKind; ?> name" placeholder="<?= ucfirst($quickKind); ?> name" style="flex:1 1 140px;min-width:0;">
    <button type="button" class="mp-lookup-save btn btn-primary">Save <?= $quickKind; ?></button>
  </div>
  <p class="mp-lookup-feedback" role="status" aria-live="polite" style="font-size:12px;overflow-wrap:anywhere;"></p>
</details>
<?php endif; ?>
<?php if(empty($GLOBALS['mp_quick_catalogue_script'])): $GLOBALS['mp_quick_catalogue_script']=true; ?>
<script src="<?= htmlspecialchars(base_url('theme/js/modals/catalogue_quick_create.js?v='.app_version()),ENT_QUOTES,'UTF-8'); ?>"></script>
<?php endif; ?>
