<?PHP
if (!defined('EVOBIZ'))
	exit;


class evo
{
	static $o_singleton;

	static $level = 1;

	static function &XML()
	{
		if(static::$o_singleton === null)
		{
			$className = get_called_class();

			static::$o_singleton = new $className();
		}

		return static::$o_singleton;
	}

	static function indent($string)
	{
		$a_string = explode("\r\n",$string);

		$return = "";

		$count = count($a_string);

		foreach($a_string as $k => $line)
		{
			if($line == "") continue;

			if (preg_match("`<\!\[CDATA\[(.*)]]>`", $line, $tmp_res) == 1)
			{
				$line = str_repeat("\t", static::$level).$line;
			}
			else // Balise auto-fermante
			if (preg_match("`<[^/]* ?[^>]*\/>`", $line) == 1)
			{
				$line = str_repeat("\t", static::$level).$line;
			}
			else // Balise fermante
			if (preg_match("`</.* ?[^>]*>`", $line) == 1)
			{
 				static::$level--;
				$line = str_repeat("\t", static::$level).$line;
			}
			else // Balise ouvrante
			if (preg_match("`<[^/]* ?[^>]*>`", $line) == 1)
			{
				$line = str_repeat("\t", static::$level).$line;
 				static::$level++;
			}

			$return .= "$line\r\n";
		}

 		return $return;
	}

	function __call($tag,$a_arg)
	{
		$nr = "\r\n";

		$count = count($a_arg);

		$tag = to_camel_case(str_replace(" ","_",accent_clean($tag)));

		if(preg_match("%D$%",$tag))
		{
			$tag = preg_replace("%D$%","",$tag);

			if($count == 1 AND empty($a_arg[0]))
			{
				$count = 0;
			}

			if($count == 0)
			{
				$return = "<$tag/>$nr";
			}
			else
			if($count == 1)
			{
				$return = ("<$tag>$nr<![CDATA[".str_replace(array("\r","\n","\t"),"",(html_entity_decode(char_clean($a_arg[0]),ENT_COMPAT,"UTF-8")))."]]>$nr</$tag>$nr");
			}
			else
			if($count == 2)
			{
				$return = ("<$tag $a_arg[0]>$nr<![CDATA[".str_replace(array("\r","\n","\t"),(html_entity_decode(char_clean($a_arg[1]),ENT_COMPAT,"UTF-8")))."]]>$nr</$tag>$nr");
			}
		}
		else
		if($count == 0)
		{
			$return = "<$tag/>$nr";
		}
		else
		if($count == 1)
		{
			$return = ("<$tag>$nr$a_arg[0]$nr</$tag>$nr");
		}
		else
		if($count == 2)
		{
			$return = ("<$tag $a_arg[0]>$nr$a_arg[1]$nr</$tag>$nr");
		}

		return $return;
	}
}
