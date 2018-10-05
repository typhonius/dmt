<?php

if (strpos(basename(__FILE__), 'phar')) {
    $root = __DIR__;
    require_once 'phar://dmt.phar/vendor/autoload.php';
} else {
    if (file_exists(dirname(__DIR__).'/vendor/autoload.php')) {
        $root = dirname(__DIR__);
        require_once dirname(__DIR__) . '/vendor/autoload.php';
    } elseif (file_exists(dirname(__DIR__) . '/../../autoload.php')) {
        $root = dirname(__DIR__) . '/../../..';
        require_once dirname(__DIR__) . '/../../autoload.php';
    } else {
        $root = __DIR__;
        require_once 'phar://dmt.phar/vendor/autoload.php';
    }
}
