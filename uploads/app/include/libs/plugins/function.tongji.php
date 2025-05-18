<?php
function smarty_function_tongji($paramer,&$smarty){
		global $config,$views;
		$sy_tongji = '';
		
		if($config['sy_webtongji']){
			
			$patterns=array("Ｓleep"," an d "," Ｏr ","xＯr","Ｓelect","Ｕpdate","Ｃount","Ｃhr","Ｔruncate","Ｕnion","Ｄelete","Ｉnsert","- -","（","）","OOOOOOOO","Ox","×","Ob");

			$replacements=array("sleep"," and "," or ","xor","select","update","count","chr","truncate","union","delete","insert","--","(",")","00000000","0x","*","0b");

			

			$sy_tongji = str_replace($patterns,$replacements,$config['sy_webtongji']);
		}
		
		return $sy_tongji;
		
	}
?>