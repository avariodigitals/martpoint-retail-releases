<?php
$path = '/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Users_model.php';
$content = file_get_contents($path);

// 1. Add approval_pin to creation (after password)
$content = str_replace(
    "'password' 				=> md5(\$pass), \n\t    \t\t\t\t\t'mobile'",
    "'password' 				=> md5(\$pass), \n\t    \t\t\t\t\t'approval_pin' 	\t\t=> !empty(\$approval_pin) ? md5(\$approval_pin) : null,\n\t    \t\t\t\t\t'mobile'",
    $content
);

// 2. Add approval_pin to update
$update_marker = "'email' \t\t\t\t\t\t=> \$email,\n\t    \t\t\t\t\t\n\t    \t\t\t\t);";
$update_replacement = "'email' \t\t\t\t\t\t=> \$email,\n\t    \t\t\t\t\t\n\t    \t\t\t\t);\n\t\tif(isset(\$approval_pin) && \$approval_pin !== ''){\n\t\t\t\$user_data['approval_pin'] = md5(\$approval_pin);\n\t\t}";
$content = str_replace($update_marker, $update_replacement, $content);

file_put_contents($path, $content);
echo "Patched Users_model.php\n";
?>
