<?php
$filepath = __DIR__ . "/application/models/Customers_model.php";
$content = file_get_contents($filepath);

// 1. verify_and_save info array
$search1 = "                'credit_limit'         => \$credit_limit,\n              );";
$replace1 = "                'credit_limit'         => \$credit_limit,\n                'nin_bvn'              => \$nin_bvn,\n                'nin_verified'         => (!empty(\$nin_verified)) ? 1 : 0,\n              );";
$content = str_replace($search1, $replace1, $content, $count1);
echo "Replace 1 count: $count1\n";

// 2. get_details
$search2 = "\t\t\$data['email']=\$query->email;\n\t\t\n\t\t\$data['gstin']=\$query->gstin;";
$replace2 = "\t\t\$data['email']=\$query->email;\n\t\t\$data['nin_bvn']=\$query->nin_bvn;\n\t\t\$data['nin_verified']=\$query->nin_verified;\n\t\t\$data['nin_verified_at']=\$query->nin_verified_at;\n\t\t\$data['nin_waived']=\$query->nin_waived;\n\t\t\n\t\t\$data['gstin']=\$query->gstin;";
$content = str_replace($search2, $replace2, $content, $count2);
echo "Replace 2 count: $count2\n";

// 3. update_customers inputs (second occurrence of gstin after nin added)
$search3 = "\t\t\$gstin = \$this->input->post('gstin', TRUE);\n\t\t\$shipping_country = \$this->input->post('shipping_country', TRUE);";
$replace3 = "\t\t\$gstin = \$this->input->post('gstin', TRUE);\n\t\t\$nin_bvn = \$this->input->post('nin_bvn', TRUE);\n\t\t\$nin_verified = \$this->input->post('nin_verified', TRUE);\n\t\t\$shipping_country = \$this->input->post('shipping_country', TRUE);";
$offset = strpos($content, $search3);
if ($offset !== false) {
    $offset2 = strpos($content, $search3, $offset + strlen($search3));
    if ($offset2 !== false) {
        $content = substr_replace($content, $replace3, $offset2, strlen($search3));
        echo "Replace 3 count: 1\n";
    } else {
        echo "Replace 3 count: 0 (only one found)\n";
    }
} else {
    echo "Replace 3 count: 0\n";
}

// 4. update_customers info array
$search4 = "                'credit_limit'         => empty(\$credit_limit) ? null : \$credit_limit,\n              );";
$replace4 = "                'credit_limit'         => empty(\$credit_limit) ? null : \$credit_limit,\n                'nin_bvn'              => \$nin_bvn,\n                'nin_verified'         => (!empty(\$nin_verified)) ? 1 : 0,\n              );";
$offset = strpos($content, $search4);
if ($offset !== false) {
    $offset2 = strpos($content, $search4, $offset + strlen($search4));
    if ($offset2 !== false) {
        $content = substr_replace($content, $replace4, $offset2, strlen($search4));
        echo "Replace 4 count: 1\n";
    } else {
        echo "Replace 4 count: 0 (only one found)\n";
    }
} else {
    echo "Replace 4 count: 0\n";
}

file_put_contents($filepath, $content);
echo "Done\n";
