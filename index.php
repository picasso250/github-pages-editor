<?php

require __DIR__.'/php-tiny-frame/autoload.php';

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
	$root = current_root();
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
		// $content = file_get_contents($file_path);
		// echo text2html($content);
		readfile($file_path);
	}
}
function get_file_list()
{
	$root = current_root();
	if (is_dir($root)) {
		echo json(file_list($root));
	} else {
		echo json(1, 'not dir');
	}
}
function file_list($root) {
	return array_map(function ($file_name) {
		return [$file_name, basename($file_name)];
	}, glob("$root/*.md"));
}
function html2text($html)
{
	$html = preg_replace('/<br>\n?/', "\n", $html);
	$html = preg_replace('/&nbsp;/', ' ', $html);
	return strip_tags($html);
}

function text2html($text)
{
	$text = str_replace(' ', '&nbsp;', $text);
	return nl2br($text);
}

function current_file()
{
	$session_key = 'se_fp';
	if (isset($_REQUEST['file_path']) && ($_REQUEST['file_path'])) {
		return $_SESSION[$session_key] = trim($_REQUEST['file_path']);
	}
	return isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : '';
}
function current_root()
{
	$session_key = 'se_r';
	if (isset($_REQUEST['root']) && ($_REQUEST['root'])) {
		return $_SESSION[$session_key] = trim($_REQUEST['root']);
	}
	return isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : '';
}

function preview()
{
	$file_path = current_file();
	$text = _post('text');
	$text = html2text($text);
	if ($text) {
		// file_put_contents($file_path, html2text($text));
	}
	$text = preg_replace('/title: (.+)/u', '<h1>$1</h1>', $text);
	$text = preg_replace('/layout: (\w+)/', '', $text);
	$text = preg_replace('/<% highlight (\w+) %>/', '<pre><code class="$1">', $text);
	$text = preg_replace('/<% endhighlight %>/', '</code></pre>', $text);
	$my_html = Markdown::defaultTransform($text);
	echo $my_html;
}
