<?php
header('Content-Type: application/json');

$limits = [
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'max_input_time' => ini_get('max_input_time'),
    'max_input_vars' => ini_get('max_input_vars')
];

echo json_encode($limits, JSON_PRETTY_PRINT);
?> 