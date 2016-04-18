<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/../vendor/autoload.php');

spl_autoload_register(function ($class_name) {
	if(file_exists($class_name . '.php')) {
		require $class_name . '.php';
		return true;
	}
    return false;
});

date_default_timezone_set('Europe/Lisbon');