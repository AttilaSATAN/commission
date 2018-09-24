<?php declare(strict_types=1);

$_eall = E_ALL;

ini_set('error_reporting', "$_eall");
ini_set('log_errors','1'); 
ini_set('display_errors','0'); 

require_once "vendor/autoload.php";

set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
