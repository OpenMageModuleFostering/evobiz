<?php
if (!defined('EVOBIZ'))
	exit;

function sql_escape_string($str)
{
	return str_replace
	(
		array("\\",		"\0",	"\n",	"\r",	"\x1a",	"'",	'"'),
		array("\\\\",	"\\0",	"\\n",	"\\r",	"\Z",	"\'",	'\"'),
		stripslashes($str)
	);
}


function accent_clean($string)
{
	return strtr($string, array(
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'a'=>'a', 'a'=>'a', 'a'=>'a', 'ç'=>'c', 'c'=>'c', 'c'=>'c', 'c'=>'c', 'c'=>'c', 'd'=>'d', 'd'=>'d', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'g'=>'g', 'g'=>'g', 'g'=>'g', 'h'=>'h', 'h'=>'h', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', '?'=>'i', 'j'=>'j', 'k'=>'k', '?'=>'k', 'l'=>'l', 'l'=>'l', 'l'=>'l', '?'=>'l', 'l'=>'l', 'ñ'=>'n', 'n'=>'n', 'n'=>'n', 'n'=>'n', '?'=>'n', '?'=>'n', 'ð'=>'o', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'o'=>'o', 'o'=>'o', 'o'=>'o', 'œ'=>'o', 'ø'=>'o', 'r'=>'r', 'r'=>'r', 's'=>'s', 's'=>'s', 's'=>'s', 'š'=>'s', '?'=>'s', 't'=>'t', 't'=>'t', 't'=>'t', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'w'=>'w', 'ý'=>'y', 'ÿ'=>'y', 'y'=>'y', 'z'=>'z', 'z'=>'z', 'ž'=>'z'
	));
}


function char_clean($string)
{
	$string = str_replace("&nbsp;"	," ",$string);
	$string = str_replace("|"		," ",$string);
	$string = str_replace("&#39;"	,"'",$string);
	$string = str_replace("&#150;"	,"-",$string);
	$string = str_replace(chr(9)	," ",$string);
	$string = str_replace(chr(10)	," ",$string);
	$string = str_replace(chr(13)	," ",$string);
	$string = preg_replace("%(  *)%"," ",$string);

	return $string;
}


function from_camel_case($str)
{
	$str[0] = strtolower($str[0]);

	$func = create_function('$c', 'return "_" . strtolower($c[1]);');

	return preg_replace_callback('/([A-Z])/', $func, $str);
}


function to_camel_case($str, $capitalise_first_char = false)
{
	if($capitalise_first_char)
	{
			$str[0] = strtoupper($str[0]);
	}

	$func = create_function('$c', 'return strtoupper($c[1]);');

	return preg_replace_callback('/_([a-z])/', $func, $str);
}
