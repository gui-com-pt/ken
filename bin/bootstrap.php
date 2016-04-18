#!/usr/bin/env hhvm
<?hh

require __DIR__ . '/../vendor/autoload.php';

if(!defined('WORK_DIR')) {
	define('WORK_DIR', __DIR__ .'/../');
}
$cli = new \Ken\Cli();
$cli->run($argv);