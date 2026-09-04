<?php
/**
 * One-time script to replace all native confirm() dialogs with SweetAlert swal()
 * across theme/js/ files.
 * Visit http://localhost:8888/fix_confirms.php to run.
 */

set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$baseDir = __DIR__ . '/theme/js';
$fixedFiles = [];
$totalFixes = 0;

function findMatchingBrace($content, $openIdx) {
    $depth = 1;
    $len = strlen($content);
    $inString = false;
    $stringChar = null;
    for ($i = $openIdx + 1; $i < $len; $i++) {
        $c = $content[$i];
        if ($inString) {
            if ($c === '\\') {
                $i++;
                continue;
            }
            if ($c === $stringChar) {
                $inString = false;
            }
        } else {
            if ($c === '"' || $c === "'" || $c === '`') {
                $inString = true;
                $stringChar = $c;
            } elseif ($c === '{') {
                $depth++;
            } elseif ($c === '}') {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }
    }
    return -1;
}

function processFile($filepath) {
    global $fixedFiles, $totalFixes;
    $content = file_get_contents($filepath);
    $original = $content;
    $changes = 0;

    // Pattern: if(confirm("...")){ ... }
    while (true) {
        preg_match('/if\s*\(\s*confirm\s*\(\s*"([^"]*)"\s*\)\s*\)\s*\{/', $content, $match, PREG_OFFSET_CAPTURE);
        if (!$match) {
            break;
        }
        $confirmMsg = $match[1][0];
        $matchStart = $match[0][1];
        $matchLen = strlen($match[0][0]);
        $openBrace = $matchStart + $matchLen - 1;
        $closeBrace = findMatchingBrace($content, $openBrace);
        if ($closeBrace === -1) {
            break;
        }

        $innerBlock = substr($content, $openBrace + 1, $closeBrace - $openBrace - 1);

        if (stripos($confirmMsg, 'Delete') !== false) {
            $swalText = 'You want to delete this record?';
            $btnText = 'Yes, Delete';
            $danger = 'true';
        } elseif (stripos($confirmMsg, 'Save') !== false || stripos($confirmMsg, 'Update') !== false) {
            $swalText = 'Do you want to save this record?';
            $btnText = 'Yes, Save';
            $danger = 'false';
        } else {
            $swalText = 'Are you sure?';
            $btnText = 'Yes, Confirm';
            $danger = 'false';
        }

        $replacement = "swal({\n" .
            '  title: "Are you sure?",' . "\n" .
            '  text: "' . $swalText . '",' . "\n" .
            '  icon: "warning",' . "\n" .
            '  buttons: ["Cancel", "' . $btnText . '"],' . "\n" .
            '  dangerMode: ' . $danger . ',' . "\n" .
            "}).then(function(willDo){\n" .
            "  if(willDo){\n" .
            $innerBlock .
            "  }\n" .
            "});";

        $content = substr($content, 0, $matchStart) . $replacement . substr($content, $closeBrace + 1);
        $changes++;
    }

    // Also handle single-quoted confirm messages
    while (true) {
        preg_match("/if\s*\(\s*confirm\s*\(\s*'([^']*)'\s*\)\s*\)\s*\{/", $content, $match, PREG_OFFSET_CAPTURE);
        if (!$match) {
            break;
        }
        $confirmMsg = $match[1][0];
        $matchStart = $match[0][1];
        $matchLen = strlen($match[0][0]);
        $openBrace = $matchStart + $matchLen - 1;
        $closeBrace = findMatchingBrace($content, $openBrace);
        if ($closeBrace === -1) {
            break;
        }

        $innerBlock = substr($content, $openBrace + 1, $closeBrace - $openBrace - 1);

        if (stripos($confirmMsg, 'Delete') !== false) {
            $swalText = 'You want to delete this record?';
            $btnText = 'Yes, Delete';
            $danger = 'true';
        } elseif (stripos($confirmMsg, 'Save') !== false || stripos($confirmMsg, 'Update') !== false) {
            $swalText = 'Do you want to save this record?';
            $btnText = 'Yes, Save';
            $danger = 'false';
        } else {
            $swalText = 'Are you sure?';
            $btnText = 'Yes, Confirm';
            $danger = 'false';
        }

        $replacement = "swal({\n" .
            '  title: "Are you sure?",' . "\n" .
            '  text: "' . $swalText . '",' . "\n" .
            '  icon: "warning",' . "\n" .
            '  buttons: ["Cancel", "' . $btnText . '"],' . "\n" .
            '  dangerMode: ' . $danger . ',' . "\n" .
            "}).then(function(willDo){\n" .
            "  if(willDo){\n" .
            $innerBlock .
            "  }\n" .
            "});";

        $content = substr($content, 0, $matchStart) . $replacement . substr($content, $closeBrace + 1);
        $changes++;
    }

    // Handle dynamic messages like confirm("Do You Wants to "+this_id+" Record ?")
    while (true) {
        preg_match('/if\s*\(\s*confirm\s*\(\s*"([^"]*\+[^"]*)"\s*\)\s*\)\s*\{/', $content, $match, PREG_OFFSET_CAPTURE);
        if (!$match) {
            break;
        }
        $matchStart = $match[0][1];
        $matchLen = strlen($match[0][0]);
        $openBrace = $matchStart + $matchLen - 1;
        $closeBrace = findMatchingBrace($content, $openBrace);
        if ($closeBrace === -1) {
            break;
        }

        $innerBlock = substr($content, $openBrace + 1, $closeBrace - $openBrace - 1);

        $replacement = "swal({\n" .
            '  title: "Confirm",' . "\n" .
            '  text: "Do you want to proceed?",' . "\n" .
            '  icon: "warning",' . "\n" .
            '  buttons: ["Cancel", "Yes, Confirm"],' . "\n" .
            '  dangerMode: false,' . "\n" .
            "}).then(function(willDo){\n" .
            "  if(willDo){\n" .
            $innerBlock .
            "  }\n" .
            "});";

        $content = substr($content, 0, $matchStart) . $replacement . substr($content, $closeBrace + 1);
        $changes++;
    }

    if ($changes > 0) {
        file_put_contents($filepath, $content);
        $fixedFiles[] = $filepath;
        $totalFixes += $changes;
    }
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'js') {
        processFile($file->getPathname());
    }
}

echo "<h1>Done!</h1>";
echo "<p>Fixed {$totalFixes} confirm() calls in " . count($fixedFiles) . " files.</p>";
echo "<ul>";
foreach ($fixedFiles as $f) {
    echo "<li>" . htmlspecialchars(str_replace(__DIR__, '', $f)) . "</li>";
}
echo "</ul>";

// Delete self for security
unlink(__FILE__);
echo "<p><em>This script has self-deleted for security.</em></p>";
