<?php

$candidates = [
    dirname(__FILE__, 4) . '/vendor/autoload.php',
    dirname(__FILE__, 5) . '/autoload.php',
];
// Cover root case and library case
foreach ($candidates as $candidate) {
    if (file_exists($candidate)) {
        echo $candidate;
        require_once $candidate;
        break;
    }
}
