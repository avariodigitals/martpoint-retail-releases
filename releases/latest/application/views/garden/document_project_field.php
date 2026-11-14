<?php
$ci =& get_instance();
if ((mp_get_store_profile()['industry_type'] ?? '')!=='garden_centre_landscaping' || !$ci->permissions('service_jobs_edit') || !mp_feature_enabled('service_jobs')) return;
if (!empty($garden_document_edit)) return;
$this->load->helper('garden');$this->load->model('service_project_model');
if (!$this->db->table_exists('db_service_job_context')) return;
$projectOptions=$ci->service_project_model->listing();$selectedProject=(int)$this->input->get('garden_project_id');
?>
<div class="garden-document-link" style="border:1px solid #dce6d2;background:#f6f9f0;border-radius:10px;padding:16px;margin:12px 0 20px">
<label for="garden-document-project" style="display:block;font-weight:600;margin-bottom:8px">Link to landscaping project <small style="font-weight:400">(optional)</small></label>
<select name="garden_project_id" id="garden-document-project" hidden><option value="">No project</option>
<?php foreach ($projectOptions as $option): if (in_array($option->stage,['closed','cancelled'],true)) continue;
$context=$option;if ($garden_document_kind==='invoice' && (!$context->quotation_id || !in_array($option->stage,['approved','deposit','execution','completed','paid'],true))) continue; ?>
<option value="<?= (int)$option->id; ?>" data-customer="<?= (int)$option->customer_id; ?>" data-name="<?= htmlspecialchars($option->customer_name); ?>" data-quote="<?= (int)$context->quotation_id; ?>" <?= $selectedProject===(int)$option->id ? 'selected' : ''; ?>><?= htmlspecialchars($option->title.' — '.$option->customer_name); ?></option>
<?php endforeach; ?></select><div id="garden-document-picker"></div>
<p style="font-size:11px;color:#7a896e;margin:9px 0 0"><?= $garden_document_kind==='invoice' ? 'Selecting a project starts the invoice from its linked quotation.' : 'The saved record will appear on the project automatically. Customer documents must use the project customer.'; ?></p>
</div>
<script>
(function(){function init(){var select=document.getElementById('garden-document-project'),host=document.getElementById('garden-document-picker');if(!select||!host)return;
var trigger=document.createElement('button'),list=document.createElement('div');trigger.type='button';trigger.style.cssText='padding:10px 12px;border:1px solid #d3dfc7;background:white;border-radius:6px;width:100%;text-align:left';trigger.setAttribute('aria-expanded','false');list.hidden=true;list.style.cssText='max-height:220px;overflow-y:auto;border:1px solid #d3dfc7;margin-top:5px;background:white';
function label(){trigger.textContent=select.options[select.selectedIndex].text;}Array.from(select.options).forEach(function(option){var button=document.createElement('button');button.type='button';button.textContent=option.text;button.style.cssText='display:block;width:100%;border:0;border-bottom:1px solid #eee;padding:11px;text-align:left;background:white';button.onclick=function(){select.value=option.value;list.hidden=true;trigger.setAttribute('aria-expanded','false');label();apply();};list.appendChild(button);});trigger.onclick=function(){list.hidden=!list.hidden;trigger.setAttribute('aria-expanded',String(!list.hidden));};host.append(trigger,list);label();
function apply(){var opt=select.options[select.selectedIndex];if(!select.value)return;
<?php if ($garden_document_kind==='invoice'): ?>
var existing=document.querySelector('input[name="quotation_id"]');if(opt.dataset.quote && (!existing || existing.value!==opt.dataset.quote)){var destination=<?= json_encode(base_url('sales/quotation/')); ?>+opt.dataset.quote+'?garden_project_id='+select.value;if(window.matchMedia('(max-width:768px), (max-width:1024px) and (orientation:portrait)').matches)destination+='&mobile=1';location.href=destination;return;}
<?php endif; ?>
var customer=document.getElementById('customer_id');if(customer&&opt.dataset.customer){var found=Array.from(customer.options).some(function(o){return o.value===opt.dataset.customer;});if(!found)customer.add(new Option(opt.dataset.name,opt.dataset.customer));customer.value=opt.dataset.customer;if(window.jQuery)window.jQuery(customer).trigger('change');else customer.dispatchEvent(new Event('change',{bubbles:true}));}}
setTimeout(apply,0);host.addEventListener('keydown',function(event){if(event.key==='Escape'){list.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.focus();}});
}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();})();
</script>
