<?php
if (!defined('EVOBIZ'))
	exit;

if(!defined("EVOBIZ_ROOT"))
define("EVOBIZ_ROOT",realpath(dirname(__FILE__)."/../"));


abstract class exportEvo
{
	public $pageNumber;
	public $pageSize;

	public $fileFlag = false;

	public function __construct()
	{

		if(isset($_GET["output"]))
		{
			$this->fileFlag = (trim(sql_escape_string($_GET["output"])) === "file");
		}


		if(isset($_GET["version"]))
		{
			die(EVOBIZ_VERSION."\n");
		}
		else
		if(isset($_GET["count"]))
		{
			die($this->getCountProducts()."\n");
		}
		else
		if(isset($_GET["filename"]))
		{
			echo EVOBIZ_EXPORT_FILE."\n";

			exit;
		}
		else
		if(isset($_GET["header"]))
		{
			$this->exportHeader();

			exit;
		}
		else
		if(isset($_GET["footer"]))
		{
			$this->exportFooter();

			exit;
		}
		else
		if(isset($_GET["window"]))
		{
			$window = trim(sql_escape_string($_GET["window"]));

			if(!empty($window) AND preg_match("%^[0-9]*$%",$window))
			{
				die(ceil($this->getCountProducts()/$window)."\n");
			}
			else
			{
				die("^[0-9]*$\n");
			}
		}
		else
		if(isset($_GET["pageSize"]) AND isset($_GET["pageNumber"]))
		{
			$pageSize 	= trim(sql_escape_string($_GET["pageSize"]));
			$pageNumber = trim(sql_escape_string($_GET["pageNumber"]));

			if(!empty($pageSize) AND preg_match("%^[0-9]*$%",$pageSize) AND !empty($pageNumber) AND preg_match("%^[0-9]*$%",$pageNumber))
			{

				$this->pageSize 	= $pageSize;
				$this->pageNumber 	= $pageNumber;

				e($this->pageSize);
				e($this->pageNumber);

				$this->export();
				exit;
			}
			else
			{
				die("^[0-9]*$ ^[0-9]*$\n");
			}

			exit;
		}

 		if(isset($_SERVER["REMOTE_ADDR"]) AND !$this->fileFlag)
 		{
			header('Content-Description: File Transfer');
			header('Content-Type: text/xml');
			header('Content-Disposition: attachment; filename="'.EVOBIZ_EXPORT_FILE.'"');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}

		$this->exportHeader();


		$nb_total = ceil($this->getCountProducts()/EVOBIZ_WINDOW);

		e("Nb Products");
		e($this->getCountProducts());

		e('$this->getCountProducts()/EVOBIZ_WINDOW');
		e($nb_total);

		for($w=1;$w<$nb_total+1;$w++)
		{
			e("Page number $w ".EVOBIZ_WINDOW);

			$this->pageSize 	= EVOBIZ_WINDOW;
			$this->pageNumber 	= $w;

  			$this->export();
		}


		$this->exportFooter();
		exit;
	}


	public function exportHeader()
	{
		$data  = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$data .= "<products>\n";

		$this->out($data,false);
	}


	public function exportFooter()
	{
		$data  = "</products>\n";

		$this->out($data);
	}


	public function out($data,$append = true)
	{
 		if($this->fileFlag)
 		{
			if(!is_dir(EVOBIZ_EXPORT_DIR)) mkdir(EVOBIZ_EXPORT_DIR);

			file_put_contents(EVOBIZ_EXPORT_PATH,$data,$append ? FILE_APPEND : null);
		}
		else
		{
			echo $data;
		}
	}
}
