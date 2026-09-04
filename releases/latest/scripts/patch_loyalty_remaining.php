<?php
/**
 * Patch script for remaining Loyalty module edits
 * Run: php patch_loyalty_remaining.php
 */

echo "=== Loyalty Patch Script ===\n";

// 1. Patch Roles_model.php
echo "\n1. Patching Roles_model.php...\n";
$rolesFile = __DIR__ . '/application/models/Roles_model.php';
$rolesContent = file_get_contents($rolesFile);

$search = "                    'nin_logs',\n\n";
$replace = "                    'nin_logs',\n\n                    'loyalty_view',\n                    'loyalty_add',\n                    'loyalty_edit',\n                    'loyalty_delete',\n                    'gift_cards_view',\n                    'gift_cards_add',\n                    'gift_cards_edit',\n                    'gift_cards_delete',\n                    'store_credit_view',\n                    'store_credit_add',\n                    'store_credit_edit',\n                    'store_credit_delete',\n\n";

if (strpos($rolesContent, "'loyalty_view'") !== false) {
    echo "   Roles_model.php already patched.\n";
} elseif (strpos($rolesContent, "                    'nin_logs',\n\n") !== false) {
    $rolesContent = str_replace($search, $replace, $rolesContent);
    file_put_contents($rolesFile, $rolesContent);
    echo "   Roles_model.php patched successfully.\n";
} else {
    echo "   WARNING: Could not find insertion point in Roles_model.php.\n";
}

// 2. Patch Customers_model.php - verify_and_save birthday
echo "\n2. Patching Customers_model.php verify_and_save...\n";
$custFile = __DIR__ . '/application/models/Customers_model.php';
$custContent = file_get_contents($custFile);

// Add birthday variable
$search2 = "\t\t$shipping_address = \$this->input->post('shipping_address', TRUE);\n\t\t$shipping_location_link = \$this->input->post('shipping_location_link', TRUE);\n\t\t$CUR_DATE = \$this->data['CUR_DATE'];";
$replace2 = "\t\t$shipping_address = \$this->input->post('shipping_address', TRUE);\n\t\t$shipping_location_link = \$this->input->post('shipping_location_link', TRUE);\n\t\t$birthday = \$this->input->post('birthday', TRUE);\n\t\t$CUR_DATE = \$this->data['CUR_DATE'];";

if (strpos($custContent, "\$birthday =") !== false) {
    echo "   Birthday variable already exists.\n";
} elseif (strpos($custContent, $search2) !== false) {
    $custContent = str_replace($search2, $replace2, $custContent);
    file_put_contents($custFile, $custContent);
    echo "   Birthday variable added.\n";
} else {
    echo "   WARNING: Could not find birthday insertion point.\n";
}

// Add birthday to info array
$custContent = file_get_contents($custFile);
$search3 = "                'location_link'         => \$location_link,\n                'credit_limit'         => \$credit_limit,\n              );";
$replace3 = "                'location_link'         => \$location_link,\n                'credit_limit'         => \$credit_limit,\n                'birthday'             => (!empty(\$birthday)) ? \$birthday : NULL,\n              );";

if (strpos($custContent, "'birthday'") !== false) {
    echo "   Birthday in info array already exists.\n";
} elseif (strpos($custContent, $search3) !== false) {
    $custContent = str_replace($search3, $replace3, $custContent);
    file_put_contents($custFile, $custContent);
    echo "   Birthday added to info array.\n";
} else {
    echo "   WARNING: Could not find info array insertion point.\n";
}

// 3. Patch getCustomersArray JSON fields
echo "\n3. Patching getCustomersArray JSON fields...\n";
$custContent = file_get_contents($custFile);
$search4 = "\t\t\t\t$json_arr[\"delete_bit\"] \t\t\t\t = \$res->delete_bit;\n\t\t  \t\n\t\t  \tarray_push(\$display_json, \$json_arr);";
$replace4 = "\t\t\t\t$json_arr[\"delete_bit\"] \t\t\t\t = \$res->delete_bit;\n\t\t  \t\$json_arr[\"loyalty_points\"] \t\t\t = \$res->loyalty_points;\n\t\t  \t\$json_arr[\"loyalty_tier\"] \t\t\t = \$res->loyalty_tier;\n\t\t  \t\$json_arr[\"store_credit_balance\"] \t = \$res->store_credit_balance;\n\t\t  \t\$json_arr[\"gift_card_balance\"] \t\t = \$res->gift_card_balance;\n\t\t  \t\n\t\t  \tarray_push(\$display_json, \$json_arr);";

if (strpos($custContent, "\"loyalty_points\"") !== false && strpos($custContent, "getCustomersArray") !== false) {
    echo "   getCustomersArray JSON fields already patched.\n";
} elseif (strpos($custContent, $search4) !== false) {
    $custContent = str_replace($search4, $replace4, $custContent);
    file_put_contents($custFile, $custContent);
    echo "   getCustomersArray JSON fields patched.\n";
} else {
    echo "   WARNING: Could not find getCustomersArray insertion point.\n";
}

// 4. Patch update_customers birthday
echo "\n4. Patching update_customers birthday...\n";
$custContent = file_get_contents($custFile);
$search5 = "\t\t$shipping_address = \$this->input->post('shipping_address', TRUE);\n\t\t$shipping_location_link = \$this->input->post('shipping_location_link', TRUE);\n\n\t\tif(\$q_id==1){";
$replace5 = "\t\t$shipping_address = \$this->input->post('shipping_address', TRUE);\n\t\t$shipping_location_link = \$this->input->post('shipping_location_link', TRUE);\n\t\t$birthday = \$this->input->post('birthday', TRUE);\n\n\t\tif(\$q_id==1){";

if (strpos($custContent, "update_customers") !== false) {
    if (strpos($custContent, "\$birthday = \$this->input->post('birthday', TRUE);") !== false) {
        echo "   update_customers birthday already patched.\n";
    } elseif (strpos($custContent, $search5) !== false) {
        $custContent = str_replace($search5, $replace5, $custContent);
        file_put_contents($custFile, $custContent);
        echo "   update_customers birthday patched.\n";
    } else {
        echo "   WARNING: Could not find update_customers insertion point.\n";
    }
} else {
    echo "   update_customers method not found.\n";
}

echo "\n=== Patch complete ===\n";
