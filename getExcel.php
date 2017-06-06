<?php

/*
 * @author Хобта Сергей Сергеевич <uxss@ya.ru>
 */

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

//echo ini_get('max_execution_time')."\n";
ini_set("max_execution_time","0");
//echo ini_get('max_execution_time')."\n";
//echo ini_get('memory_limit')."\n";
//ini_set("memory_limit","256M");
//echo ini_get("memory_limit")."\n";

include 'class.table.php';
include '../config.php';

if (isset($_SESSION['class.table']['array']))
{
	$data = $_SESSION['class.table']['array'];
	
	if (!$_GET['group'])
		$data = Table::ungroupArray($data);
	
	echo "###".Table::saveToExcel($data);
}
else
	echo "Время хранения таблицы истекло. Необходимо запросить таблицу заново.\nТаблица хранится 5 минут.###";