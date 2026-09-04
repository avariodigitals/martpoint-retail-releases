<?php
$prefix = 'stress_';
$sql = "CREATE TABLE IF NOT EXISTS db_installment_payments (\n    id INT NOT NULL AUTO_INCREMENT,\n    plan_id INT NOT NULL\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

function prefixSchemaSql($sql, $prefix) {
    $sql = preg_replace('/CREATE TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`(?!' . preg_quote($prefix, '/') . ')([^`]+)`/i', 'CREATE TABLE $1`' . $prefix . '$2`', $sql);
    $sql = preg_replace('/CREATE TABLE\s+(IF\s+NOT\s+EXISTS\s+)?(?!' . preg_quote($prefix, '/') . ')(\b\w+\b)/i', 'CREATE TABLE $1`' . $prefix . '$2`', $sql);
    return $sql;
}

echo "Input:\n" . $sql . "\n\nOutput:\n" . prefixSchemaSql($sql, $prefix);
