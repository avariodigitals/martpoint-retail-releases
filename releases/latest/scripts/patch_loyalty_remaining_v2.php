<?php
/**
 * Patch script for remaining Loyalty module edits
 * Run from project root: php patch_loyalty_remaining_v2.php
 */

echo "=== Loyalty Patch Script v2 ===\n\n";

$patchCount = 0;

// 1. Patch Roles_model.php
echo "1. Patching Roles_model.php...\n";
$rolesFile = __DIR__ . '/application/models/Roles_model.php';
$rolesContent = file_get_contents($rolesFile);

if (strpos($rolesContent, "'loyalty_view'") !== false) {
    echo "   Already patched.\n";
} else {
    // Find 'nin_logs' line and insert after it
    $pattern = "/(\s+'nin_logs',\n)(\s*\n)/";
    $replacement = "\$1\$2                    'loyalty_view',\n                    'loyalty_add',\n                    'loyalty_edit',\n                    'loyalty_delete',\n                    'gift_cards_view',\n                    'gift_cards_add',\n                    'gift_cards_edit',\n                    'gift_cards_delete',\n                    'store_credit_view',\n                    'store_credit_add',\n                    'store_credit_edit',\n                    'store_credit_delete',\n\n";
    $newContent = preg_replace($pattern, $replacement, $rolesContent, 1, $count);
    if ($count > 0) {
        file_put_contents($rolesFile, $newContent);
        echo "   Patched successfully.\n";
        $patchCount++;
    } else {
        echo "   WARNING: Could not find insertion point.\n";
    }
}

// 2. Patch Customers_model.php - verify_and_save birthday variable
echo "\n2. Patching verify_and_save birthday variable...\n";
$custFile = __DIR__ . '/application/models/Customers_model.php';
$custContent = file_get_contents($custFile);

if (strpos($custContent, "\$birthday = \$this->input->post('birthday', TRUE);") !== false) {
    echo "   Birthday variable already exists.\n";
} else {
    $pattern = "/(\\\$shipping_location_link = \\\$this->input->post\('shipping_location_link', TRUE\);\n)(\\\$CUR_DATE = \\\$this->data\['CUR_DATE'\];)/";
    $replacement = "\$1\$birthday = \$this->input->post('birthday', TRUE);\n\$2";
    $newContent = preg_replace($pattern, $replacement, $custContent, 1, $count);
    if ($count > 0) {
        file_put_contents($custFile, $newContent);
        echo "   Birthday variable added.\n";
        $patchCount++;
    } else {
        echo "   WARNING: Could not find insertion point for birthday variable.\n";
    }
}

// 3. Patch Customers_model.php - verify_and_save info array birthday
echo "\n3. Patching verify_and_save info array...\n";
$custContent = file_get_contents($custFile);

if (strpos($custContent, "'birthday'") !== false) {
    echo "   Birthday in info array already exists.\n";
} else {
    $pattern = "/('location_link'\s+=> \\\$location_link,\n\s+'credit_limit'\s+=> \\\$credit_limit,)\n(\s+\);)/";
    $replacement = "\$1\n                'birthday'             => (!empty(\$birthday)) ? \$birthday : NULL,\n\$2";
    $newContent = preg_replace($pattern, $replacement, $custContent, 1, $count);
    if ($count > 0) {
        file_put_contents($custFile, $newContent);
        echo "   Birthday added to info array.\n";
        $patchCount++;
    } else {
        echo "   WARNING: Could not find info array insertion point.\n";
    }
}

// 4. Patch getCustomersArray JSON fields
echo "\n4. Patching getCustomersArray JSON fields...\n";
$custContent = file_get_contents($custFile);

if (preg_match('/"loyalty_points".*getCustomersArray/', $custContent)) {
    echo "   Already patched.\n";
} else {
    $pattern = "/(\$json_arr\[\"delete_bit\"\].*?= \\\$res->delete_bit;)\n(\s+array_push\(\$display_json, \\\$json_arr\);)/";
    $replacement = "\$1\n\t\t  \t\$json_arr[\"loyalty_points\"] \t\t\t = \$res->loyalty_points;\n\t\t  \t\$json_arr[\"loyalty_tier\"] \t\t\t = \$res->loyalty_tier;\n\t\t  \t\$json_arr[\"store_credit_balance\"] \t = \$res->store_credit_balance;\n\t\t  \t\$json_arr[\"gift_card_balance\"] \t\t = \$res->gift_card_balance;\n\$2";
    $newContent = preg_replace($pattern, $replacement, $custContent, 1, $count);
    if ($count > 0) {
        file_put_contents($custFile, $newContent);
        echo "   JSON fields patched.\n";
        $patchCount++;
    } else {
        echo "   WARNING: Could not find getCustomersArray insertion point.\n";
    }
}

// 5. Patch update_customers birthday
echo "\n5. Patching update_customers birthday...\n";
$custContent = file_get_contents($custFile);

if (preg_match('/update_customers[\s\S]*?\$birthday =/', $custContent)) {
    echo "   Already patched.\n";
} else {
    $pattern = "/(\\\$shipping_location_link = \\\$this->input->post\('shipping_location_link', TRUE\);\n)(\s*if\(\\\$q_id==1\)\{)/";
    $replacement = "\$1\$birthday = \$this->input->post('birthday', TRUE);\n\n\$2";
    $newContent = preg_replace($pattern, $replacement, $custContent, 1, $count);
    if ($count > 0) {
        file_put_contents($custFile, $newContent);
        echo "   update_customers patched.\n";
        $patchCount++;
    } else {
        echo "   WARNING: Could not find update_customers insertion point.\n";
    }
}

// 6. Patch update_customers info array
echo "\n6. Patching update_customers info array...\n";
$custContent = file_get_contents($custFile);

$pattern = "/(update_customers[\s\S]*?'location_link'\s+=> \\\$location_link,\n\s+'credit_limit'\s+=>\s+empty\(\\\$credit_limit\) \? null : \\\$credit_limit,)\n(\s+\);)/";
$replacement = "\$1\n\t\t\t\t'birthday'             => (!empty(\$birthday)) ? \$birthday : NULL,\n\$2";
$newContent = preg_replace($pattern, $replacement, $custContent, 1, $count);
if ($count > 0) {
    file_put_contents($custFile, $newContent);
    echo "   update_customers info array patched.\n";
    $patchCount++;
} else {
    echo "   (May already be patched or pattern not found)\n";
}

echo "\n=== Done. $patchCount patch(es) applied. ===\n";
