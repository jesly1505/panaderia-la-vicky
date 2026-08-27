<?php
header('Content-Type: text/plain; charset=utf-8');
$logPath = 'C:/xampp/apache/logs/error.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50);
    echo implode("", $lastLines);
} else {
    echo "Log file not found at " . $logPath;
}
