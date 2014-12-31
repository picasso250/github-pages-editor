<?php

require 'lib.php';

spl_autoload_register(function($class){
	$f = __DIR__.'\vendor\php-markdown-lib/'.$class.'.php';
	if (is_file($f)) {
		require $f;
	}
});

use \Michelf\Markdown;

session_start();

$action = _get('action');
if (empty($action)) {
	$file_path = current_file();
	include 'index.html';
	exit;
}

$action();

// === logic ===

function get_file()
{
	$file_path = current_file();
	// D:\play-good\picasso250.github.com\_posts\2014-12-26-PHP-sugar.md
	if (is_file($file_path)) {
		readfile($file_path);
	}
}

function current_file()
{
	$session_key = 'se_fp';
	if (isset($_REQUEST['file_path']) && ($_REQUEST['file_path'])) {
		return $_SESSION[$session_key] = trim($_REQUEST['file_path']);
	}
	return isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : '';
}

function preview()
{
	$file_path = current_file();
	$text = _post('text');
	if ($text) {
		file_put_contents($file_path, "$text\n");
	}
	$text = preg_replace('/title: (.+)/u', '<h1>$1</h1>', $text);
	$text = preg_replace('/layout: (\w+)/', '', $text);
	$text = preg_replace('/<% highlight (\w+) %>/', '<pre><code class="$1">', $text);
	$text = preg_replace('/<% endhighlight %>/', '</code></pre>', $text);
	$my_html = Markdown::defaultTransform($text);
	echo $my_html;
}
