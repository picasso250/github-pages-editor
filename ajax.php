<?php

require 'lib.php';

spl_autoload_register(function($class){
	$f = __DIR__.'\vendor\php-markdown-lib/'.$class.'.php';
	if (is_file($f)) {
		require $f;
	}
});

use \Michelf\Markdown;

$action = _get('action');
if (empty($action)) {
	die('no action');
}

$action();

// === logic ===

function get_file()
{
	$file_path = _get('file_path');
	// D:\play-good\picasso250.github.com\_posts\2014-12-26-PHP-sugar.md
	if (empty($file_path)) {
		die('no file_path');
	}
	if (is_file($file_path)) {
		readfile($file_path);
	}
}

function preview()
{
	$text = _post('text');
	$my_html = Markdown::defaultTransform($text);
	echo $my_html;
}
