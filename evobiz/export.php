<?php
umask(0);
ini_set('display_errors', 1);

define("EVOBIZ_ROOT",dirname(__FILE__));
define("MAGE_ROOT",realpath(dirname(__FILE__)."/.."));

if(file_exists(EVOBIZ_ROOT."/debug"))
{
 	define("EVOBIZ_ERROR_HANLDER",true);
	define("EVOBIZ_LOG",EVOBIZ_ROOT."/evo.log");
}

define('EVOBIZ',true);


include(EVOBIZ_ROOT.'/includes/debug.php');
include(EVOBIZ_ROOT.'/includes/functions.php');
include(EVOBIZ_ROOT.'/includes/config.php');
include(EVOBIZ_ROOT.'/classes/evo.php');
include(EVOBIZ_ROOT.'/classes/exportEvo.php');
include(EVOBIZ_ROOT.'/classes/exportEvo17.php');


e("==== ".date("Y-m-d H:i:s")." ====");


include_once MAGE_ROOT.'/app/Mage.php';

// Mage::setIsDeveloperMode(true);

Mage::app();

new exportEvo17();

