<?php
// Patch script to add approval_pin support to Users_model.php
$file = __DIR__ . '/application/models/Users_model.php';
$content = file_get_contents($file);

// 1. Add approval_pin to creation info array
$search1 = "'password' \t\t\t\t\t=> md5(\$pass), \n\t\t\t\t\t\t'mobile' \t\t\t\t\t=> \$mobile,";
$replace1 = "'password' \t\t\t\t\t=> md5(\$pass), \n\t\t\t\t\t\t'approval_pin' \t\t\t=> !empty(\$approval_pin) ? md5(\$approval_pin) : null,\n\t\t\t\t\t\t'mobile' \t\t\t\t\t=> \$mobile,";

if(strpos($content, $search1) !== false){
    $content = str_replace($search1, $replace1, $content);
    echo "Patched creation info array.\n";
} else {
    echo "Creation pattern not found - may already be patched or format differs.\n";
}

// 2. Add approval_pin to update info array (after last_name, before mobile)
$search2 = "'last_name' \t\t\t=> \$last_name, \n\t\t\t\t\t'mobile' \t\t\t\t=> \$mobile,";
$replace2 = "'last_name' \t\t\t=> \$last_name, \n\t\t\t\t\t'mobile' \t\t\t\t=> \$mobile,";

// Try a different pattern for update section
$search2b = "\$user_data = array(\n\t\t\t\t\t'username' \t\t\t\t=> \$username, \n\t\t\t\t\t'first_name' \t\t\t=> \$new_user, \n\t\t\t\t\t'last_name' \t\t\t=> \$last_name, \n\t\t\t\t\t'mobile' \t\t\t\t=> \$mobile,";
$replace2b = "\$user_data = array(\n\t\t\t\t\t'username' \t\t\t\t=> \$username, \n\t\t\t\t\t'first_name' \t\t\t=> \$new_user, \n\t\t\t\t\t'last_name' \t\t\t=> \$last_name, \n\t\t\t\t\t'approval_pin' \t\t\t=> isset(\$approval_pin) && \$approval_pin !== '' ? md5(\$approval_pin) : null,\n\t\t\t\t\t'mobile' \t\t\t\t=> \$mobile,";

if(strpos($content, $search2b) !== false){
    $content = str_replace($search2b, $replace2b, $content);
    echo "Patched update info array.\n";
} else {
    echo "Update pattern not found - may already be patched or format differs.\n";
}

file_put_contents($file, $content);
echo "Done.\n";
