<?php

/**
 * Table
 * 
 * Automatic creation of html tables from the data array
 * 
 * @author 0xSS <uxss@ya.ru>
 * @version 1.0
 */
class Table
{
	/**
	 * @var array $info Should contain a common table info and settings
	 */
	public static $info	= false;
	
	/**
	 * Synonym of Table::writeTable()
	 * Write table from data array
	 * 
	 * @see writeTable()
	 * @param array $data array of data
	 * @param array $tableInfo array of common table info and settings
	 * @return void
	 */
	public static function write($data = false, $tableInfo = false)
	{
		self::writeTable($data, $tableInfo);
	}
	
	/**
	 * Write table from data array
	 * 
	 * @param array $data array of data
	 * @param array $tableInfo array of common table info and settings
	 * @return void
	 */
	public static function writeTable($data = false, $tableInfo = false)
	{
		if($data)
		{
			$args		= "";
			$colKeys	= false;
			$titles		= false;
			
			//Search and collect information about the table in $tableInfo
			//High priority is given to $tableInfo(second parameter)
			if(!$tableInfo) $tableInfo = $data['tableInfo'];
			elseif($data['tableInfo']) $tableInfo = array_replace_recursive($data['tableInfo'], $tableInfo);
			unset($data['tableInfo']);
			
			if($tableInfo)
			{
				//Following work will be happening with global variable $info
				self::$info = $tableInfo;
				unset($tableInfo);
				
				//Collecting information about columns(titles and column keys)
				if(self::$info['cols'])
				{
					$colKeys	=	self::getColKeys(self::$info['cols']);
					$titles		=	self::getTitles(self::$info['cols']);
					unset(self::$info['cols']);
				}
				
				//Collecting HTML parameters for table
				$args = self::convertRulesToHtml(self::$info);
			}
			//Height of each row will be calculated automatically by the default
			if(!isset(self::$info['rowspan'])) self::$info['rowspan'] = true;
			
			echo "\n	<table{$args}>";
			
			//If title was set then write	
			if(is_array($titles) && sizeof($titles))
			{
				echo "\n		<tr>";
				foreach($titles as $title)
				{
					echo "\n			<th>{$title}</th>";
				}
				echo "\n		</tr>";
			}
			
			//Write the rows
			foreach($data as $info)
			{
				self::writeRow($info, $colKeys);
			}
			
			echo "\n	</table>";
			
			//Clearing of global variables for the next use a static class
			self::$info = false;
		}
	}
	
	/**
	 * Write row of table from data array
	 * 
	 * @param array $data array of data
	 * @param array $colKeys array of column keys. Using for manage output order
	 * @return void
	 */
	private static function writeRow($data, $colKeys)
	{
		if(is_array($data))
		{
			$rowRules	= false;
			$cellsRules	= false;
			//====================
			$rowspan	= self::$info['rowspan'];
			$countRows	= false;
			$args		= "";
			//====================
			$subRow		= false;
			$subKeys	= false;
			
			if($data['tableInfo'])
			{
				$rowRules = $data['tableInfo'];
				unset($data['tableInfo']);
				
				if($rowRules['cells'])
				{
					$cellsRules = $rowRules['cells'];
					unset($rowRules['cells']);
				}
				
				if($rowRules['rowspan'])
				{
					$rowspan = $rowRules['rowspan'];
					unset($rowRules['rowspan']);
				}
				
				if($rowRules['cols']) $colKeys = self::getColKeys($rowRules['cols']);
				$colKeys = self::modifyKeys($colKeys, $rowRules['keys']);
				
				$args = self::convertRulesToHtml($rowRules);
			}
			
			foreach($data as $k => $item)
				if(is_array($item)) $subRow = $item;
			
			//If do not specify column keys then it is filled with all the elements of the array, except for sub-arrays(all cells)
			if(!$colKeys) $colKeys = array_keys(array_filter($data, function($v){return !is_array($v);}));
			
			echo "\n		<tr{$args}>";
			foreach($colKeys as $i => $key)
			{
				if(
					isset($data[$key]) && $subRow &&
					(
						(!isset($cellsRules[$key]['rowspan']) && $rowspan)
						|| (is_bool($cellsRules[$key]['rowspan']) && $cellsRules[$key]['rowspan'])
					)
				  )
				{
					if(!$countRows) $countRows = self::countRows($data);
					if(!is_int($cellsRules[$key]['rowspan'])) $cellsRules[$key]['rowspan'] = $countRows;
				
					unset($colKeys[$i]);
				}
				self::writeCell($data[$key], $cellsRules[$key]);
			}
			echo "\n		</tr>";
			
			if($subRow)
				foreach($subRow as $row)
					self::writeRow($row, $colKeys);
		}
	}
	
	/**
	 * Write cell of table from data string
	 * 
	 * @param string $data cell content
	 * @param array $rules parameters of cell
	 * @return void
	 */
	private static function writeCell($data, $rules)
	{
		$args = self::convertRulesToHtml($rules);
		echo "\n			<td{$args}>{$data}</td>";
	}
	
	/**
	 * Count the number of subrows
	 * 
	 * @param array $data array of subrows
	 * @return int
	 */
	private static function countRows($data)
	{
		$rows = 0;
		
		if(is_array($data))
		{
			unset($data['tableInfo']);
			$rows = 1;
			
			foreach($data as $item)
				if(is_array($item))
					foreach($item as $a)
						$rows += self::countRows($a);
		}
		
		return $rows;
	}
	
	/**
	 * Convert array of parameters to html string
	 * 
	 * @param array $info array of table/row/cell parameters
	 * @return string
	 */
	private static function convertRulesToHtml($info)
	{
		$args = "";
		
		if(is_int($info['colspan']))	$args .= " colspan='{$info['colspan']}'";
		if(is_int($info['rowspan']))	$args .= " rowspan='{$info['rowspan']}'";
		if($info['id'])					$args .= " id='{$info['id']}'";
		if($info['class'])				$args .= " class='{$info['class']}'";
		if($info['style'])				$args .= " style='{$info['style']}'";
		if($info['args'])				$args .= " {$info['args']}";
		
		return $args;
	}
	
	/**
	 * Modify column keys by rules
	 * 
	 * @param array $colKeys array of column keys
	 * @param array $rules array of modification rules
	 * @return array
	 */
	private static function modifyKeys($colKeys, $rules)
	{
		if($rules)
		{
			if($rules['delete'] == 'all') $colKeys = array();
			else
				foreach($rules['delete'] as $v)
					if(array_search($v, $colKeys))
						unset($colKeys[array_search($v, $colKeys)]);
			
			foreach($rules['add'] as $v)
				$colKeys[] = $v;
			
			foreach($rules['forwarding'] as $v)
				if(array_search($v['src'], $colKeys))
					$colKeys[array_search($v['src'], $colKeys)] = $v['dst'];
		}
		
		return $colKeys;
	}
	
	/**
	 * Return array of column keys
	 * 
	 * @param array $cols array of column information
	 * @return array
	 */
	private static function getColKeys($cols)
	{
		return array_filter(array_map(function($v){return $v['key'];}, $cols), 'strlen');
	}
	
	/**
	 * Return array of column headers
	 * 
	 * @param array $cols array of column information
	 * @return array
	 */
	private static function getTitles($cols)
	{
		return array_filter(array_map(function($v){return $v['title'];}, $cols));
	}

}

?>
