<?php
if (!defined('EVOBIZ'))
	exit;


if(defined("EVOBIZ_LOG") AND EVOBIZ_LOG)
{
	if(defined("EVOBIZ_ERROR_HANLDER") AND EVOBIZ_ERROR_HANLDER)
	{
		function evo_error_handler($errno, $errstr, $errfile, $errline)
		{
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		}

		function evo_exception_handler($e)
		{
			e("==== ".$e->getMessage()." ====");
			foreach($e->getTrace() as $k=>$v)
			{
				foreach(array("class","type","function","file","line") as $vv)
					if(!isset($v[$vv])) $v[$vv] = "";

				e("$k: ($v[class]$v[type]$v[function]) $v[file]:$v[line]");
			}
		}

		set_error_handler("evo_error_handler",E_ALL);
		set_exception_handler("evo_exception_handler");
	}


	function pr($data = "")
	{
		file_put_contents(EVOBIZ_LOG,print_r($data,true)."\n",FILE_APPEND);
	}

	function vd($data = "")
	{
		ob_start();
		var_dump($data);

		file_put_contents(EVOBIZ_LOG,ob_get_clean()."\n",FILE_APPEND);
	}

	function e($data = "")
	{
		file_put_contents(EVOBIZ_LOG,$data."\n",FILE_APPEND);
	}
}
else
{
	function pr($data = "")	{}

	function vd($data = "") {}

	function e($data = "")	{}
}

