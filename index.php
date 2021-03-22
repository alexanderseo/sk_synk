<?php
$wordpress = array();

function debug($_) {
    echo '<pre>';
    var_dump($_);
    echo '</pre>';
}

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'database.php';
require_once 'wordpress.php';

require_once 'bootstrap.php';