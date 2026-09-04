<?php
$root = __DIR__;
$dir = $root . '/application';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$results = [];
foreach ($files as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) !== 'php') continue;
    $path = $f->getPathname();
    $content = file_get_contents($path);
    if (!preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $content)) continue;
    // Extract CREATE TABLE statements
    preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[^;]+;/is', $content, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $m) {
        $sql = $m[0];
        $rel = str_replace($root . '/', '', $path);
        $results[$rel][] = preg_replace('/\s+/', ' ', trim($sql));
    }
}
header('Content-Type: text/plain');
foreach ($results as $file => $sqls) {
    echo "\n=== $file ===\n";
    foreach ($sqls as $sql) {
        echo $sql . "\n";
    }
}
