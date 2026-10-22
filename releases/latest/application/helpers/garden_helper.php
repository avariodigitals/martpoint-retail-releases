<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Garden policies; records continue to use the shared MartPoint engines. */
function mp_garden_modes($modes) {
    $available = mp_get_business_presets()['garden_centre_landscaping']['operating_modes'];
    if (!is_array($modes) || !$modes || array_diff($modes, array_keys($available))) {
        throw new InvalidArgumentException('Choose at least one valid operating mode.');
    }
    return in_array('combined', $modes, true) ? ['combined'] : array_values(array_unique($modes));
}

function mp_garden_project_stages() {
    return ['enquiry'=>'Enquiry','inspection'=>'Site inspection','quoted'=>'Quotation','approved'=>'Approved','deposit'=>'Deposit received',
        'execution'=>'In progress','completed'=>'Work completed','paid'=>'Paid','closed'=>'Closed','cancelled'=>'Cancelled'];
}

function mp_garden_inspection_fields() {
    return [
        'visit_date'=>['Visit date','date'], 'site_type'=>['Site use',['residential'=>'Residential','commercial'=>'Commercial','public'=>'Public / community','event'=>'Event','other'=>'Other']],
        'length_m'=>['Approx. length (m)','number'],'width_m'=>['Approx. width (m)','number'],'soil_ph'=>['Measured soil pH (optional)','ph'],
        'soil'=>['Soil assessment',['not_assessed'=>'Not assessed','sandy'=>'Sandy','clay'=>'Clay','loam'=>'Loam','mixed'=>'Mixed','other'=>'Other']],
        'sunlight'=>['Light exposure',['not_assessed'=>'Not assessed','full_sun'=>'Full sun','partial'=>'Partial sun / shade','shade'=>'Shade','mixed'=>'Mixed']],
        'drainage'=>['Drainage',['not_assessed'=>'Not assessed','good'=>'Good','moderate'=>'Moderate','poor'=>'Poor']],
        'terrain'=>['Terrain',['not_assessed'=>'Not assessed','level'=>'Level','sloped'=>'Sloped','uneven'=>'Uneven']],
        'water_access'=>['Water access',['not_assessed'=>'Not assessed','available'=>'Available','limited'=>'Limited','none'=>'None']],
        'access'=>['Site access',['not_assessed'=>'Not assessed','clear'=>'Clear access','restricted'=>'Restricted access','equipment'=>'Equipment access needed']],
        'risks'=>['Observed concerns',['none'=>'None observed','pests'=>'Pests','disease'=>'Plant disease','waterlogging'=>'Waterlogging','erosion'=>'Erosion','utilities'=>'Underground utilities','safety'=>'Safety / access hazard'],'checks'],
        'scope'=>['Proposed scope',['planting'=>'Planting','soil_prep'=>'Soil preparation','turf'=>'Turf / lawn','irrigation'=>'Irrigation','hardscape'=>'Hard landscaping','decor'=>'Décor / containers','maintenance'=>'Maintenance'],'checks'],
        'existing_plants'=>['Existing plants / condition','text'],'client_brief'=>['Customer brief / preferences','text'],
        'recommendations'=>['Horticulturist recommendations','text'],'follow_up'=>['Follow-up date (optional)','date'],
    ];
}

function mp_garden_clean_inspection(array $input) {
    $out=[];
    foreach (mp_garden_inspection_fields() as $key=>$definition) {
        $value=$input[$key] ?? ''; $type=$definition[1];
        if (($definition[2] ?? '')==='checks') {
            if ($value==='') $value=[];
            if (!is_array($value)) throw new InvalidArgumentException('Choose valid '.$definition[0].'.');
            foreach ($value as $choice) if (!is_string($choice) || !isset($type[$choice])) throw new InvalidArgumentException('Choose valid '.$definition[0].'.');
            $out[$key]=array_values(array_unique($value));
        } elseif (is_array($type)) {
            if (!is_string($value) || ($value!=='' && !isset($type[$value]))) throw new InvalidArgumentException('Choose valid '.$definition[0].'.');
            $out[$key]=$value;
        } else {
            if (!is_scalar($value)) throw new InvalidArgumentException('Invalid '.$definition[0].'.');
            $value=trim((string)$value);
            if (strlen($value)>6000) throw new InvalidArgumentException($definition[0].' is too long.');
            if ($value!=='' && $type==='date') { $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value); if (!$date || $date->format('Y-m-d')!==$value) throw new InvalidArgumentException('Invalid '.$definition[0].'.'); }
            if ($value!=='' && in_array($type,['number','ph'],true) && (!is_numeric($value) || !is_finite((float)$value) || (float)$value<0 || (float)$value>($type==='ph' ? 14 : 100000))) throw new InvalidArgumentException('Invalid '.$definition[0].'.');
            $out[$key]=$value;
        }
    }
    if (empty($out['visit_date']) || empty($out['recommendations'])) throw new InvalidArgumentException('Record a visit date and recommendations.');
    if (in_array('none',$out['risks'],true) && count($out['risks'])>1) throw new InvalidArgumentException('None observed cannot be combined with a concern.');
    return $out;
}

/** Called inside the shared document save transaction; returns an error, or null. */
function mp_garden_link_created_document($kind,$id) {
    $ci =& get_instance();$project=$ci->input->post('garden_project_id');$automatic=false;
    if (($project===null || $project==='') && $kind==='invoice' && (mp_get_store_profile()['industry_type'] ?? '')==='garden_centre_landscaping' && $ci->db->table_exists('db_service_job_context')) {
        $invoice=$ci->db->where('id',(int)$id)->where('store_id',get_current_store_id())->get('db_sales')->row();
        if ($invoice && $invoice->quotation_id) {
            $context=$ci->db->where('store_id',get_current_store_id())->where('quotation_id',(int)$invoice->quotation_id)->get('db_service_job_context')->row();
            if ($context) { $project=(string)$context->job_id;$automatic=true; }
        }
    }
    if ($project===null || $project==='') return null;
    try {
        if (!is_scalar($project) || !ctype_digit((string)$project) || (int)$project<1 || !mp_garden_has_mode(mp_get_store_profile(),'landscaping') || !mp_feature_enabled('service_jobs') || !($automatic ? $ci->permissions('sales_add') : $ci->permissions('service_jobs_edit'))) throw new InvalidArgumentException('You cannot link this project.');
        $ci->load->model('service_project_model');$ci->service_project_model->attach_document((int)$project,$kind,(int)$id);
        return null;
    } catch (Throwable $e) { return $e->getMessage(); }
}

function mp_garden_has_mode($profile, $mode) {
    $modes = $profile['operating_modes'] ?? mp_garden_modes($profile['settings']['garden_operating_modes'] ?? ['combined']);
    return ($profile['industry_type'] ?? '') === 'garden_centre_landscaping'
        && (in_array('combined', $modes, true) || in_array($mode, $modes, true));
}

function mp_garden_mode_features(array $features, array $modes) {
    if (in_array('combined', $modes, true)) return $features;
    $requirements = [
        'pos_retail_button'=>['retail','nursery'], 'pos_wholesale_button'=>['retail','nursery'],
        'batch_tracking'=>['nursery'], 'custom_orders'=>['landscaping'],
        'production_workflow'=>['retail','nursery'],'recipe_tracking'=>['retail','nursery'],
        'service_jobs'=>['landscaping','maintenance'], 'staff_assignment'=>['landscaping','maintenance'],
        'service_workflow'=>['landscaping','maintenance'], 'leads'=>['landscaping','maintenance'],
    ];
    return array_values(array_filter($features, function($feature) use ($requirements, $modes) {
        return !isset($requirements[$feature]) || array_intersect($requirements[$feature], $modes);
    }));
}

function mp_garden_attributes() {
    return [
        'common_name'=>'Common Name', 'botanical_name'=>'Botanical Name', 'plant_family'=>'Plant Family',
        'variety'=>'Variety', 'environment'=>'Indoor / Outdoor', 'sunlight'=>'Sunlight Requirement',
        'watering'=>'Watering Frequency', 'growth_stage'=>'Growth Stage', 'pot_size'=>'Pot Size',
        'height'=>'Height', 'plant_age'=>'Plant Age', 'care_instructions'=>'Care Instructions',
        'pet_safety'=>'Pet Safety / Toxicity Information',
    ];
}

function mp_garden_clean_attributes($input) {
    $result = [];
    foreach (mp_garden_attributes() as $key => $label) {
        $value = $input[$key] ?? '';
        if (!is_scalar($value)) throw new InvalidArgumentException($label.' must be text.');
        $value = trim((string)$value);
        if (strlen($value) > 4000) throw new InvalidArgumentException($label.' is too long.');
        if ($value !== '') $result[$key] = $value;
    }
    return $result;
}

function mp_garden_loss_reasons() {
    return ['plant_death'=>'Plant Death', 'disease'=>'Disease Loss', 'damage'=>'Damaged Plants',
        'weather'=>'Weather Loss', 'other'=>'Other Approved Loss'];
}

/** Anchor monthly visits to their original day (Jan 31 -> Feb 28 -> Mar 31). */
function mp_garden_occurrence($start, $frequency, $index, $customDays = 0) {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$start);
    if (!$date || $date->format('Y-m-d') !== $start || $index < 0) throw new InvalidArgumentException('Invalid schedule date.');
    if ($frequency === 'monthly') {
        $month = $date->modify('first day of this month')->modify('+'.(int)$index.' months');
        return $month->setDate((int)$month->format('Y'), (int)$month->format('m'), min((int)$date->format('d'), (int)$month->format('t')))->format('Y-m-d');
    }
    $intervals = ['once'=>0, 'weekly'=>7, 'biweekly'=>14, 'custom'=>(int)$customDays];
    if (!isset($intervals[$frequency]) || ($frequency === 'custom' && ($customDays < 1 || $customDays > 366))
        || ($frequency === 'once' && $index !== 0)) throw new InvalidArgumentException('Invalid recurrence.');
    return $date->modify('+'.($intervals[$frequency] * (int)$index).' days')->format('Y-m-d');
}

/** Shared navigation definition; secondary tools must not repeat these routes. */
function mp_garden_navigation_groups() {
    return [
 'Overview'=>[
  ['dashboard_view','garden','Overview','fa-th-large',null,null],
 ],
 'Catalogue & stock'=>[
  ['items_view','garden/catalogue','Plants & products','fa-pagelines',null,null],
  ['stock_adjustment_view','garden/nursery','Nursery & plant care','fa-tint','nursery',null],
  ['production_batches_view','garden/production','Production batches','fa-cubes',null,'production_workflow'],
  ['recipes_view','garden/materials','Materials & costing','fa-list-alt',null,'recipe_tracking'],
 ],
 'Projects & services'=>[
  ['service_jobs_view','garden/projects','Landscaping projects','fa-map-o','landscaping','service_jobs'],
  ['service_jobs_view','garden/work','Team jobs & visits','fa-calendar-check-o',null,'service_jobs'],
  ['service_jobs_view','garden/maintenance','Maintenance plans','fa-repeat','maintenance','service_jobs'],
 ],
 'Sales & customers'=>[
  ['leads_view','leads','Enquiries','fa-comment-o',null,'leads'],
  ['quotation_view','quotation','Quotations','fa-file-text-o','landscaping',null],
  ['sales_view','sales','Sales & invoices','fa-file-o',null,null],
  ['customers_view','customers','Customers & sites','fa-users',null,null],
  ['online_store_view','online_store/orders','Online orders','fa-shopping-basket',null,'online_store'],
 ],
 'Operations'=>[
  ['purchase_view','purchase','Purchases & suppliers','fa-truck',null,null],
  ['store_view','operations/delivery_scheduling','Deliveries & collections','fa-truck',null,'delivery_scheduling'],
  ['warehouse_view','warehouse','Branches','fa-map-marker',null,null],
  ['expense_view','expense','Expenses','fa-credit-card',null,null],
 ],
 'Reports'=>[
  ['dashboard_view','dashboard/daily_summary','Daily garden summary','fa-calendar-o',null,null],
  ['sales_report','garden/sales_report','Sales & Collections','fa-line-chart',null,null],
  ['profit_report','reports/profit_loss','Profit & loss','fa-bar-chart',null,null],
  ['cash_flow_report','reports/cash_flow','Cash flow','fa-exchange',null,null],
  ['receivables_aging_report','reports/receivables_aging','Receivables ageing','fa-clock-o',null,null],
  ['stock_report','reports/stock','Stock report','fa-cubes',null,null],
  ['expense_report','reports/expense','Expense report','fa-credit-card',null,null],
 ],
 'Online store'=>[
  ['online_store_view','online_store','Manage store','fa-globe',null,'online_store'],
  ['online_store_view','online_store/subscribers','Email subscribers','fa-envelope-o',null,'online_store'],
 ],
 'Settings'=>[
  ['business_profile','garden/settings','Workspace settings','fa-sliders',null,null],
 ],
 'More'=>[
  [null,'garden/business_tools','More','fa-ellipsis-h',null,null],
 ],
];
}

function mp_garden_navigation_visible($link,array $profile) {
    $ci =& get_instance();
    return (empty($link[0]) || $ci->permissions($link[0])) && (!$link[4] || mp_garden_has_mode($profile,$link[4])) && (!$link[5] || mp_feature_enabled($link[5]));
}

/** Choose one destination, including shared create/edit screens and detail routes. */
function mp_garden_navigation_current($path) {
    $path=trim($path,'/');
    $aliases=['garden/project/'=>'garden/projects','garden/plant/'=>'garden/catalogue','garden/production_batch/'=>'garden/production','garden/production_template/'=>'garden/materials','sales/quotation/'=>'quotation','items/'=>'garden/catalogue'];
    foreach($aliases as $prefix=>$route)if(strpos($path,$prefix)===0)return $route;
    if($path==='items')return 'garden/catalogue';
    $best='garden/business_tools';$length=0;
    foreach(mp_garden_navigation_groups() as $links)foreach($links as $link){$route=$link[1];if($path===$route||($route!=='garden'&&strpos($path,$route.'/')===0)){if(strlen($route)>$length){$best=$route;$length=strlen($route);}}}
    return $best;
}

/** Convert the existing permission-filtered shared rail into a tool directory. */
function mp_garden_business_tool_groups($html) {
    $doc=new DOMDocument('1.0','UTF-8');$before=libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>');libxml_clear_errors();libxml_use_internal_errors($before);
    $xp=new DOMXPath($doc);$groups=[];
    foreach($xp->query('//a[@href]') as $a){
        $group=$xp->query('ancestor::*[contains(concat(" ",normalize-space(@class)," ")," mp-nav-group ")][1]',$a)->item(0);
        $heading=$group?$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," mp-nav-group-toggle ")]',$group)->item(0):null;
        $title=$heading?trim(preg_replace('/\s+/u',' ',$heading->textContent)):'Other tools';
        $label=trim(preg_replace('/\s+/u',' ',$a->textContent));if($label==='')continue;
        $groups[$title][]=['label'=>$label,'url'=>$a->getAttribute('href')];
    }
    return $groups;
}

/** Keep additional shared tools, excluding destinations already on the Garden rail. */
function mp_garden_secondary_menu($html, array $profile) {
    $ci =& get_instance();
    $normalise=function($url) {
        $path=trim((string)parse_url(html_entity_decode($url,ENT_QUOTES,'UTF-8'),PHP_URL_PATH),'/');
        $base=trim((string)parse_url(base_url(),PHP_URL_PATH),'/');
        if ($base && strpos($path,$base.'/')===0) $path=substr($path,strlen($base)+1);
        $path=preg_replace('#^index\.php/#','',$path);
        return preg_replace('#/index$#','',$path);
    };
    $seen=['dashboard'=>true,'operations/service_jobs'=>true,'operations/production_schedule'=>true,'operations/production_batch'=>true,'operations/recipes'=>true,'operations/recipe'=>true];
    foreach (mp_garden_navigation_groups() as $links) foreach ($links as $link) {
        if (mp_garden_navigation_visible($link,$profile)) $seen[$normalise($link[1])]=true;
    }
    if (isset($seen['garden/sales_report'])) $seen['reports/sales_and_payments']=true;
    if (isset($seen['garden/catalogue'])) $seen['items']=true;
    $remove=function($href) use (&$seen,$normalise) {
        $path=$normalise($href);
        if ($path==='subscription_license' || strpos($path,'subscription_license/')===0 || isset($seen[$path])) return true;
        $seen[$path]=true;return false;
    };
    if (!class_exists('DOMDocument')) {
        return preg_replace_callback('#<a\b[^>]*href="([^"]*)"[^>]*>.*?</a>#si',function($m) use ($remove) { return $remove($m[1]) ? '' : $m[0]; },$html);
    }
    $doc=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="garden-secondary">'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();libxml_use_internal_errors($previous);
    $xp=new DOMXPath($doc);
    foreach (iterator_to_array($doc->getElementsByTagName('a')) as $a) if ($remove($a->getAttribute('href'))) $a->parentNode->removeChild($a);
    foreach (['mp-nav-group','mp-nav-section'] as $class) {
        foreach (iterator_to_array($xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," '.$class.' ")]')) as $node) {
            if (!$node->getElementsByTagName('a')->length && $node->parentNode) $node->parentNode->removeChild($node);
        }
    }
    $labels=['Sales'=>'Sales tools','Catalog'=>'Catalogue setup','Purchases & Suppliers'=>'Purchasing tools','Inventory'=>'Stock tools','Clients'=>'Customer tools','Finance'=>'Finance tools','Reports'=>'More reports','Online Store'=>'Store setup','Operations'=>'Other operations'];
    foreach ($xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," mp-nav-group-toggle ")]') as $toggle) {
        foreach ($toggle->childNodes as $text) if ($text->nodeType===XML_TEXT_NODE && isset($labels[trim($text->nodeValue)])) $text->nodeValue=' '.$labels[trim($text->nodeValue)].' ';
    }
    $root=$doc->getElementById('garden-secondary');$result='';
    if ($root) foreach ($root->childNodes as $child) $result.=$doc->saveHTML($child);
    return $result;
}

/** Mobile destinations for shared tools opened from the Garden workspace. */
function mp_garden_mobile_url($url) {
    $base = rtrim(base_url(), '/').'/';
    if (strpos($url, $base) !== 0) return $url;
    $parts = parse_url(substr($url, strlen($base)));
    $path = $parts['path'] ?? '';
    if ($path === '') return $url;
    $query = [];parse_str($parts['query'] ?? '', $query);
    $routes = ['garden/business_tools'=>'mobile/more','operations/delivery_scheduling'=>'mobile/deliveries','dashboard/daily_summary'=>'garden/daily_summary','dashboard'=>'garden','pos'=>'mobile/pos','sales'=>'mobile/sales_list','sales/add'=>'mobile/sale','quotation'=>'mobile/quotations','quotation/add'=>'mobile/quotation_form','customers'=>'mobile/customers','customers/add'=>'mobile/add_customer','leads'=>'mobile/leads','items'=>'mobile/catalogue','items/add'=>'mobile/product','purchase'=>'mobile/purchase','purchase/add'=>'mobile/purchase_form','expense'=>'mobile/finance/expenses','expense/add'=>'mobile/finance/expenses/form','stock_adjustment'=>'mobile/stock_adjustments','stock_transfer'=>'mobile/stock_transfers','stock_transfer/add'=>'mobile/stock_transfer_form'];
    if (isset($routes[$path])) $path = $routes[$path];
    elseif (preg_match('#^reports/(profit_loss|sales_and_payments|cash_flow|receivables_aging|stock|expense|sales)$#D', $path, $m)) $path = 'mobile/report/'.$m[1];
    elseif (preg_match('#^sales/invoice/(\d+)$#D', $path, $m)) $path = 'mobile/sales_invoice/'.$m[1];
    elseif (preg_match('#^quotation/invoice/(\d+)$#D', $path, $m)) $path = 'mobile/quotation_view/'.$m[1];
    elseif (preg_match('#^quotation/update/(\d+)$#D', $path, $m)) $path = 'mobile/quotation_form/'.$m[1];
    elseif (preg_match('#^expense/update/(\d+)$#D', $path, $m)) $path = 'mobile/finance/expenses/form/'.$m[1];
    elseif (preg_match('#^items/update/(\d+)$#D', $path, $m)) $path = 'mobile/product/'.$m[1];
    elseif (preg_match('#^purchase/invoice/(\d+)$#D', $path, $m)) $path = 'mobile/purchase_view/'.$m[1];
    elseif (preg_match('#^customers/update/(\d+)$#D', $path, $m)) $path = 'mobile/edit_customer/'.$m[1];
    elseif (preg_match('#^operations/delivery_schedule_view/(\d+)$#D', $path, $m)) $path='mobile/delivery_view/'.$m[1];
    elseif (preg_match('#^online_store/(?:content_settings|journal(?:/\d+)?)$#D',$path)) $query['screen']='mobile';
    elseif (preg_match('#^online_store(?:/(.*))?$#D', $path, $m)) $path = 'mobile/online_store'.(!empty($m[1])?'/'.$m[1]:'');
    if ($path === 'garden' || strpos($path,'garden/')===0 || $path === 'warehouse' || strpos($path,'warehouse/')===0) $query['screen']='mobile';
    if (preg_match('#^sales/quotation/\d+$#D', $path)) $query['mobile']='1';
    return base_url($path).($query?'?'.http_build_query($query):'').(isset($parts['fragment'])?'#'.$parts['fragment']:'');
}

/** Rewrite links before rendering: correct destinations even before JS loads. */
function mp_garden_mobile_links($html) {
    return preg_replace_callback('/\bhref="([^"]*)"/i',function($match){
        $url=html_entity_decode($match[1],ENT_QUOTES,'UTF-8');
        return 'href="'.htmlspecialchars(mp_garden_mobile_url($url),ENT_QUOTES,'UTF-8').'"';
    },$html);
}
