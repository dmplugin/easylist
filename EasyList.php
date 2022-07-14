<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHP ActiveRecord requires PHP 5.3 or higher');

define('PHP_EASYLIST_VERSION_ID','1.0');

if (!defined('PHP_EASYLIST_AUTOLOAD_PREPEND'))
	define('PHP_EASYLIST_AUTOLOAD_PREPEND',true);

require __DIR__.'/src/ListConnection.php';
require __DIR__.'/src/Listing.php';
require __DIR__.'/src/ListFilter.php';
require __DIR__.'/src/ListTable.php';
require __DIR__.'/src/Exceptions/EasyListException.php';
