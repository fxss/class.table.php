<?php

/*
 * @author Хобта Сергей Сергеевич <uxss@ya.ru>
 */

include 'class.table.php';
include '../config.php';

if (isset($_SESSION['class.table']))
	echo Table::saveToExcel($_SESSION['class.table']['array']);