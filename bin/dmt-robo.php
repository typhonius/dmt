<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Robo\Config\Config;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Loader\YamlConfigLoader;
use DrupalModuleTracker\DrupalModuleTracker;

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

$config = new Config();
$loader = new YamlConfigLoader();
$processor = new ConfigProcessor();

$processor->extend($loader->load(dirname(__DIR__) . '/config/config.yml'));

$config->import($processor->export());

$input = new ArgvInput($argv);
$output = new ConsoleOutput();
$app = new DrupalModuleTracker($config, $input, $output);
$statusCode = $app->run($input, $output);
exit($statusCode);
