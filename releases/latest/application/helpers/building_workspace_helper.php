<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** A permission-filtered directory of tools relevant to this workspace. */
function bm_workspace_tools() {
    $ci =& get_instance();
    $messagePermission=$ci->permissions('send_email')?'send_email':'send_sms';
    $catalog = [
        'Messaging & customer engagement' => [
            [$messagePermission,'messaging?screen=mobile','Messaging balance & packages','Add MartPoint credit, view usage and control spending'],
            [$messagePermission,'marketing/campaigns?screen=mobile','SMS & email campaigns','Review costs and send customer campaigns through MartPoint'],
        ],
        'Commercial & customers' => [
            ['customers_view','customers','Customers & sites','Customer contacts, organisations and delivery addresses'],
            ['leads_view','leads','CRM & opportunities','Website enquiries and commercial follow-up','leads'],
            ['quotation_view','quotation','Quotations & proposals','Technical scope, revisions and customer approval'],
            ['sales_view','sales','Invoice register','Invoices, deposits, payments and balances'],
            ['service_jobs_view','building_projects','Projects','Coordinate scope, surveys and execution'],
        ],
        'B2B website' => [
            ['online_store_view','online_store','B2B website & enquiries','Website inbox, CRM follow-up and operational handoffs','online_store'],
            ['online_store_edit','online_store/appearance','Appearance & theme','Framework Atelier and Vector Systems','online_store'],
            ['online_store_edit','online_store/settings','Website settings','Contact information, enquiry settings and publishing','online_store'],
            ['online_store_edit','online_store/theme_content','Website content','Hero, services, applications and section copy','online_store'],
            ['online_store_edit','online_store/journal','Applications, projects & insights','Case studies and editorial website content','online_store'],
            ['online_store_edit','online_store/product_b2b','Specifications & documents','Product specifications and public technical documents','online_store'],
            ['online_store_edit','online_store/homepage_builder','Homepage sections','Arrange and configure website sections','online_store'],
            ['online_store_edit','online_store/domains','Website domains','Custom domain configuration','online_store'],
        ],
        'Materials & purchasing' => [
            ['items_view','items','Products & inventory','Materials, availability and item details'],
            ['suppliers_view','suppliers','Suppliers','Supplier contacts and procurement partners'],
            ['purchase_view','purchase','Procurement','Purchase orders and material receipts'],
            ['stock_transfer_view','stock_transfer/view','Depot transfers','Move stock between branches','warehouse'],
            ['stock_adjustment_view','stock_adjustment','Stock adjustments','Reconcile physical material quantities'],
        ],
        'Field operations & after-sales' => [
            ['service_jobs_view','building_workspace/surveys','Site surveys','Measurements, site conditions and engineer notes','service_jobs'],
            ['service_jobs_view','building_workspace/field','Field teams & installation','Engineer work orders and visit schedules','service_jobs'],
            ['service_jobs_view','building_workspace/services','Service requests','Standalone repair and maintenance visits','service_jobs'],
            ['service_jobs_view','building_workspace/services?type=maintenance','Maintenance visits','Scheduled maintenance work orders','service_jobs'],
            ['equipment_view','operations/equipment_register','Installed assets','Customer equipment, installation and service history','equipment_register'],
            ['store_view','operations/warranty_lookup','Warranty lookup','Serial numbers, invoices and warranty coverage','warranty_tracking'],
            ['store_view','operations/delivery_scheduling','Deliveries & logistics','Dispatch schedules and proof of delivery','delivery_scheduling'],
        ],
        'Finance & reports' => [
            ['expense_view','expense','Expenses','Operating and project expenditure'],
            ['accounts_view','accounts','Accounts','Bank, cash and ledger accounts','accounts'],
            ['profit_report','reports/profit_loss','Profit & loss','Recorded income, costs and profitability'],
            ['sales_report','reports/sales_and_payments','Invoice & payment report','Billing and collections'],
            ['receivables_aging_report','reports/receivables_aging','Receivables ageing','Unpaid invoices and overdue customer balances'],
            ['purchase_report','reports/purchase','Procurement spend','Supplier purchasing report'],
            ['stock_report','reports/stock','Materials position','Stock value and material availability'],
            ['expense_report','reports/expense','Expense report','Recorded business expenses'],
        ],
        'Teams & settings' => [
            ['', 'attendance/workspace','My attendance','Clock in or out and review your own attendance'],
            ['users_view','users/view','Teams & users','Staff accounts and access'],
            ['roles_view','roles/view','Roles & permissions','Control what each role can access'],
            ['attendance_edit','attendance/assign_shifts','Assign attendance shifts','Manage team shift assignments'],
            ['attendance_edit','attendance/shifts','Attendance shifts','Configure team shift rosters'],
            ['attendance_view','attendance/daily','Daily attendance','See team attendance for a date'],
            ['attendance_view','attendance/report','Attendance report','Review team clock-in and clock-out history'],
            ['business_profile','business_profile','Industry settings','Building Materials operating configuration'],
            ['store_profile','store_profile/update/'.(int)get_current_store_id(),'Business settings','Business identity, document and payment configuration'],
            ['audit_trail_view','audit_trail','Audit trail','Review recorded business changes'],
        ],
    ];
    $groups=[]; $permissions=[];
    foreach ($catalog as $name=>$tools) foreach ($tools as $tool) {
        if (!array_key_exists($tool[0],$permissions)) $permissions[$tool[0]]=$tool[0] === '' || $ci->permissions($tool[0]);
        if (!$permissions[$tool[0]] || (!empty($tool[4]) && !mp_feature_enabled($tool[4]))) continue;
        $groups[$name][]=['label'=>$tool[2],'url'=>base_url($tool[1]),'description'=>$tool[3]];
    }
    return $groups;
}

/** The shared content has independent desktop and mobile documents. */
function bm_workspace_render(array $data, $view) {
    $ci =& get_instance();
    $mobile=$ci->input->get('screen')==='mobile';
    $data['building_mobile']=$mobile;
    if ($mobile) $data['SITE_TITLE']=$data['store_name'] ?? $ci->session->userdata('store_name') ?: 'MartPoint';
    $data['content']=$ci->load->view($view,$data,true);
    if ($mobile) $ci->load->view('mobile/building_workspace',$data);
    else {
        $data['content'].=$ci->load->view('building_materials/scripts',['building_mobile'=>false],true);
        $ci->load->view('mp_layout',$data);
    }
}
