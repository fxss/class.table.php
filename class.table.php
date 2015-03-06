<?php
//test
class Table
{
	public	static	$info = false;
	
	public static function write($data = false, $tableInfo = false)
	{
		self::writeTable($data, $tableInfo);
	}
	
	public static function writeTable($data = false, $tableInfo = false)
	{
		if($data)
		{
			$args = "";
			$colKeys = false;
			$titles = false;
			
			if(!$tableInfo) $tableInfo = $data['tableInfo'];
			elseif($data['tableInfo']) $tableInfo = array_replace_recursive($data['tableInfo'], $tableInfo);
			unset($data['tableInfo']);
			
			if($tableInfo)
			{
				self::$info = $tableInfo;
				unset($tableInfo);
				
				if(self::$info['cols'])
				{
					$colKeys	=	array_filter(array_map(function($v){return $v[key];}, self::$info['cols']));
					$titles		=	array_filter(array_map(function($v){return $v[title];}, self::$info['cols']));
					unset(self::$info['cols']);
				}
				
				$args = self::convertRulesToHtml(self::$info);
			}
			if(!isset(self::$info['rowspan'])) self::$info['rowspan'] = true;
			
			echo "\n	<table{$args}>";
					
			if(is_array($titles) && sizeof($titles))
			{
				echo "\n		<tr>";
				foreach($titles as $title)
				{
					echo "\n			<th>{$title}</th>";
				}
				echo "\n		</tr>";
			}
			
			foreach($data as $info)
			{
				self::writeRow($info, $colKeys);
			}
			
			echo "\n	</table>";
			
			self::$info = false;
		}
	}
	
	private static function writeRow($data, $colKeys)
	{
		if(is_array($data))
		{
			$rowRules = false;
			$cellsRules = false;
			//====================
			$rowspan = true && self::$info['rowspan'];
			$countRows = false;
			$args = "";
			//====================
			$subRow = false;
			$subKeys = false;
			
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

				$colKeys = self::modifyKeys($colKeys, $rowRules['keys']);
				
				$args = self::convertRulesToHtml($rowRules);
			}
			
			foreach($data as $k => $item)
				if(is_array($item)) $subRow = $item;
			
			if(!$colKeys) $colKeys = array_keys(array_filter($data, function($v){return !is_array($v);}));
			
			echo "\n		<tr{$args}>";
			foreach($colKeys as $key)
			{
				if($data[$key] && $subRow && ($rowspan || (is_bool($cellsRules[$key]['rowspan']) && $cellsRules[$key]['rowspan'])))
				{
					if(!$countRows) $countRows = self::countRows($data);
					if(!is_int($cellsRules[$key]['rowspan'])) $cellsRules[$key]['rowspan'] = $countRows;
				}
				self::writeCell($data[$key], $cellsRules[$key]);
			}
			echo "\n		</tr>";
			
			if($subRow)
				foreach($subRow as $row)
					self::writeRow($row, array_diff($colKeys, array_keys($data)));
		}
	}
	
	private static function writeCell($data, $rules)
	{
		$args = self::convertRulesToHtml($rules);
		echo "\n			<td{$args}>{$data}</td>";
	}
	
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

}

?>
