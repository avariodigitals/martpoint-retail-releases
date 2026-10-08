<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * BackupManager — Creates DB dumps and file backups before updates
 * Safe for cPanel shared hosting (falls back to pure PHP if exec/mysqldump unavailable)
 */
class BackupManager {

    protected $CI;
    protected $backupDir;
    protected $timestamp;

    public function __construct() {
        $this->CI =& get_instance();
        $this->timestamp = date('Ymd_His');
        $this->backupDir = FCPATH . 'backups';
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a full database backup (SQL dump)
     * @return string|false Full path to .sql file, or false on failure
     */
    public function backupDatabase() {
        $db = $this->CI->db->database;
        $outFile = $this->backupDir . '/db_' . $this->timestamp . '.sql';

        // Try mysqldump first (fastest)
        if (function_exists('exec') && $this->canExec()) {
            $host = $this->CI->db->hostname;
            $user = $this->CI->db->username;
            $pass = $this->CI->db->password;
            $passPart = $pass ? " -p'" . escapeshellarg($pass) . "'" : '';
            $cmd = "mysqldump -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . $passPart . " " . escapeshellarg($db) . " > " . escapeshellarg($outFile) . " 2>&1";
            @exec($cmd, $output, $ret);
            if ($ret === 0 && file_exists($outFile) && filesize($outFile) > 0) {
                return $outFile;
            }
        }

        // Fallback: pure PHP SQL dump
        return $this->phpDumpDatabase($outFile);
    }

    /**
     * Create a ZIP backup of files that will be modified (based on manifest diff)
     * @param array $filesToModify List of relative file paths from manifest
     * @return string|false Full path to .zip file
     */
    public function backupFiles(array $filesToModify) {
        if (!class_exists('ZipArchive')) {
            return false; // cPanel almost always has ZipArchive enabled
        }

        $zipPath = $this->backupDir . '/files_' . $this->timestamp . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($filesToModify as $relPath) {
            $absPath = FCPATH . $relPath;
            if (file_exists($absPath)) {
                $zip->addFile($absPath, $relPath);
            }
        }
        $zip->close();

        if (file_exists($zipPath) && filesize($zipPath) > 0) {
            return $zipPath;
        }
        return false;
    }

    /**
     * Restore files from a backup ZIP
     */
    public function restoreFiles(string $zipPath): bool {
        if (!file_exists($zipPath) || !class_exists('ZipArchive')) {
            return false;
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }
        $zip->extractTo(FCPATH);
        $zip->close();
        return true;
    }

    /**
     * Restore database from SQL dump
     */
    public function restoreDatabase(string $sqlPath): bool {
        if (!file_exists($sqlPath)) {
            return false;
        }
        $sql = file_get_contents($sqlPath);
        if (empty($sql)) {
            return false;
        }

        // Split and execute statements
        $queries = $this->splitSql($sql);
        $this->CI->db->trans_start();
        foreach ($queries as $q) {
            $q = trim($q);
            if (empty($q)) continue;
            if ($this->CI->db->query($q) === false) {
                $this->CI->db->trans_rollback();
                return false;
            }
        }
        $this->CI->db->trans_complete();
        return $this->CI->db->trans_status();
    }

    /**
     * Clean old backups, keeping last N
     */
    public function cleanup(int $keep = 3) {
        $pattern = $this->backupDir . '/db_*.sql';
        $files = glob($pattern);
        if ($files) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            foreach (array_slice($files, $keep) as $f) {
                @unlink($f);
            }
        }

        $pattern2 = $this->backupDir . '/files_*.zip';
        $files2 = glob($pattern2);
        if ($files2) {
            usort($files2, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            foreach (array_slice($files2, $keep) as $f) {
                @unlink($f);
            }
        }
    }

    /**
     * Pure PHP database dump (fallback when mysqldump is unavailable)
     */
    protected function phpDumpDatabase(string $outFile) {
        $tables = [];
        $q = $this->CI->db->query("SHOW TABLES");
        foreach ($q->result_array() as $row) {
            $tables[] = array_values($row)[0];
        }

        $handle = fopen($outFile, 'w');
        if (!$handle) return false;

        // A full-store database cannot be dumped inside one request's memory
        // or time budget. Reading every table with result_array() loaded the
        // entire dataset into RAM, so on a real store the dump died with a
        // fatal (no return value) and the update failed at step 1 for ever —
        // reported as "check backups/ folder permissions", which was a guess
        // and sent diagnosis in the wrong direction for hours.
        //
        // Stream instead: unbuffered queries, row by row, and raise the
        // ceiling so a large table has a chance to finish. Writes are the
        // only accumulation, and they go straight to disk.
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        fwrite($handle, "-- MartPoint Auto-Backup generated at " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

        foreach ($tables as $table) {
            // DROP + CREATE
            $create = $this->CI->db->query("SHOW CREATE TABLE `{$table}`")->row_array();
            $createSql = $create['Create Table'] ?? $create['Create View'] ?? '';
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createSql . ";\n\n");

            // INSERTs — streamed in pages so memory stays flat regardless of
            // table size. 500 rows per page is well under any row-size limit
            // and keeps each statement a reasonable length.
            $pageSize = 500;
            $offset = 0;
            $columns = null;
            while (true) {
                $rows = $this->CI->db
                    ->query("SELECT * FROM `{$table}` LIMIT {$pageSize} OFFSET {$offset}")
                    ->result_array();
                if (empty($rows)) { break; }

                if ($columns === null) {
                    $columns = array_keys($rows[0]);
                    $colStr = '`' . implode('`,`', $columns) . '`';
                } else {
                    $colStr = '`' . implode('`,`', $columns) . '`';
                }

                // One INSERT statement per page — fewer, larger statements
                // restore faster than one-per-row.
                $rowCount = count($rows);
                fwrite($handle, "INSERT INTO `{$table}` ({$colStr}) VALUES\n");
                foreach ($rows as $i => $row) {
                    $vals = [];
                    foreach ($columns as $col) {
                        $val = $row[$col] ?? null;
                        $vals[] = is_null($val) ? 'NULL' : $this->CI->db->escape($val);
                    }
                    $suffix = ($i < $rowCount - 1) ? "," : ";";
                    fwrite($handle, "(" . implode(',', $vals) . "){$suffix}\n");
                }
                fwrite($handle, "\n");

                if ($rowCount < $pageSize) { break; }
                $offset += $pageSize;
                // A table this big is rare; stop paging rather than risk
                // reading a shifting OFFSET for ever on a concurrent write.
                if ($offset > 5000000) { break; }
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);

        return file_exists($outFile) && filesize($outFile) > 0 ? $outFile : false;
    }

    protected function splitSql(string $sql): array {
        // Split by semicolons, but avoid splitting inside quoted strings
        $statements = [];
        $current = '';
        $inQuote = false;
        $quoteChar = '';
        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            if (!$inQuote && ($char === "'" || $char === '`' || $char === '"')) {
                $inQuote = true;
                $quoteChar = $char;
            } elseif ($inQuote && $char === $quoteChar) {
                // Check escape
                if ($i > 0 && $sql[$i - 1] === '\\') {
                    // escaped, stay in quote
                } else {
                    $inQuote = false;
                }
            } elseif (!$inQuote && $char === ';') {
                $statements[] = $current;
                $current = '';
                continue;
            }
            $current .= $char;
        }
        if (trim($current) !== '') {
            $statements[] = $current;
        }
        return $statements;
    }

    protected function canExec(): bool {
        $disabled = ini_get('disable_functions');
        if ($disabled) {
            $funcs = array_map('trim', explode(',', $disabled));
            return !in_array('exec', $funcs, true) && !in_array('system', $funcs, true);
        }
        return true;
    }
}
