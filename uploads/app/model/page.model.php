<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class page_model extends model{
	

	function pageList($tableName,$whereData,$pageUrl,$pageNow,$limit=''){
		
		$rows		=	array();
		
		$limit		=	$limit	== '' ? $this->config['sy_listnum'] : intval($limit);
		
		$page		=	$pageNow <	1 ? 1 : intval($pageNow);
		
		if ($tableName == 'ad' && $this->config['did']) {
		    $whereData['did'] =   $this->config['did'];
		}
		 
		$num		=	$this->select_num($tableName,$whereData);
		 
        $pagenav	=	Page($page,$num,$limit,$pageUrl,$notpl=false,$this->tpl);

		return array('total'=>$num,'pagenav'=>$pagenav,'limit'=>array(($page-1)*$limit,$limit));
	
	}
	
	
	
}
?>