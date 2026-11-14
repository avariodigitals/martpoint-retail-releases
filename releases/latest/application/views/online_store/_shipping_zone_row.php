<?php
$zone = is_array($zone ?? null) ? $zone : [];
$states = array_values((array)($zone['states'] ?? []));
$cities = array_values((array)($zone['cities'] ?? []));
$selectedCountry = (string)($zone['country'] ?? '');
$countryOptions = '';
foreach ((array)($shipping_countries ?? []) as $countryRow) {
    $countryName = (string)($countryRow['country'] ?? '');
    if ($countryName === '') continue;
    $countryOptions .= '<option value="'.htmlspecialchars($countryName, ENT_QUOTES, 'UTF-8').'"'.(strcasecmp($countryName, $selectedCountry)===0?' selected':'').'>'.htmlspecialchars($countryName, ENT_QUOTES, 'UTF-8').'</option>';
}
$stateLegacy = count($states) === 1 ? (string)$states[0] : '';
$cityLegacy = count($cities) === 1 ? (string)($cities[0]['name'] ?? '') : '';
?>
<div class="city-zone-row mp-zone-row" data-zone-row
  data-selected-states="<?= htmlspecialchars(json_encode($states), ENT_QUOTES, 'UTF-8'); ?>"
  data-selected-cities="<?= htmlspecialchars(json_encode($cities), ENT_QUOTES, 'UTF-8'); ?>"
  data-wildcard="<?= !empty($zone['wildcard']) ? '1' : '0'; ?>">
  <div class="mp-zone-fields">
    <label>Zone name<input type="text" class="os-ship-name cz-name" name="cz_name[]" value="<?= htmlspecialchars((string)($zone['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. Lagos Metro"></label>
    <label>Country<select class="cz-country mp-zone-country" name="cz_country[]"><option value="">Select country</option><?= $countryOptions; ?></select></label>
    <div class="mp-zone-location mp-zone-state">
      <span class="mp-zone-label">States / regions <small>(optional)</small></span>
      <input type="hidden" class="cz-state" name="cz_state[]" value="<?= htmlspecialchars($stateLegacy, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" class="cz-states-json" name="cz_states_json[]" value="<?= htmlspecialchars(json_encode([]), ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" class="mp-zone-manual-state" value="<?= htmlspecialchars(implode(', ',$states), ENT_QUOTES, 'UTF-8'); ?>" placeholder="State / region (optional)">
      <button type="button" class="mp-zone-trigger" data-zone-toggle="states" aria-expanded="false">Select states</button>
      <div class="mp-zone-chips" data-zone-state-chips></div>
      <div class="mp-zone-panel" data-zone-states-panel hidden><input type="search" placeholder="Search states" data-zone-state-search><div class="mp-zone-options" data-zone-states role="listbox" aria-label="States"></div></div>
    </div>
    <div class="mp-zone-location mp-zone-city">
      <span class="mp-zone-label">Cities / towns <small>(optional)</small></span>
      <input type="hidden" class="cz-city" name="cz_city[]" value="<?= htmlspecialchars($cityLegacy, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" class="cz-cities-json" name="cz_cities_json[]" value="<?= htmlspecialchars(json_encode([]), ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" class="mp-zone-manual-city" value="<?= htmlspecialchars($cityLegacy, ENT_QUOTES, 'UTF-8'); ?>" placeholder="City / area (optional; * for other areas)">
      <input type="search" class="mp-zone-city-search" placeholder="Choose state(s) first, then search cities" data-zone-city-search disabled>
      <div class="mp-zone-panel" data-zone-city-results hidden><div class="mp-zone-options" data-zone-cities role="listbox" aria-label="Matching cities"></div></div>
      <div class="mp-zone-chips" data-zone-city-chips></div>
    </div>
    <label>Delivery fee<input type="number" step="0.01" min="0" class="os-ship-fee cz-fee" name="cz_fee[]" value="<?= htmlspecialchars((string)($zone['fee'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Fee"></label>
    <label>Minimum order<input type="number" step="0.01" min="0" class="os-ship-fee cz-minorder" name="cz_min_order[]" value="<?= htmlspecialchars((string)($zone['min_order'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Optional"></label>
    <label>Free over<input type="number" step="0.01" min="0" class="os-ship-fee cz-freeover" name="cz_free_over[]" value="<?= htmlspecialchars((string)($zone['free_over'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Optional"></label>
  </div>
  <div class="mp-zone-footer">
    <label class="mp-zone-other"><input type="checkbox" value="1" data-zone-wildcard <?= !empty($zone['wildcard']) ? 'checked' : ''; ?>> Include other cities in the selected state(s)</label>
    <button type="button" class="mp-zone-remove cz-remove" onclick="removeCityZone(this)" aria-label="Remove shipping zone" title="Remove shipping zone"><i class="fa fa-trash" aria-hidden="true"></i><span>Remove zone</span></button>
  </div>
</div>
