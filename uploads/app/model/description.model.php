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
class description_model extends model{
	/*
	 * 添加管理员日志 
	 */
	function adminLog($content, $opera = '', $type = '', $opera_id=''){
		require_once('log.model.php');
		$logM	=	new log_model($this->db,$this->def);
		return	$logM	->	addAdminLog($content, $opera = '', $type = '', $opera_id='');
	}
	/*
	 * 获取单页面类别列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getDesClassList($whereData=array(),$field='*'){
		$list	=	array();
		$list	=	$this	->	select_all('desc_class',$whereData,$field);
		return	$list;
	}
	/*
	 * 添加单页面类别
	 * $addData 	提交分类数据 'name':名称为数组形式封装
	 */
	function addDesClass($addData=array()){
		$valueData	=	array();
		
		$whereData['name']	=	array('in',"'".@implode("','", $addData['name'])."'");
		$industry			=	$this	->	getDesClassList($whereData);

		if(empty($industry)){
		    foreach ($addData['name'] as $key=>$val){
		        $valueData[$key]['name']=$val;
		    }
		    $result	=	$this	->	DB_insert_multi('desc_class',$valueData);
		    $return	=	$result	?	2	:	3;
		    $this	->	AdminLog("单页面类别添加成功！");
		}else{
			$return=1;
		}
		return	$return;
	}
	/*
	 * 更新单页面类别
	 * $addData 	提交分类数据
	 * $whereData 	更新查询
	 */
	function upDesClass($addData=array(),$whereData=array()){
		
		if($addData['name']){//修改名称
			$type	=	'名称';
		}else{
			unset($addData['name']);
		}
		if($addData['sort']){//修改排序
			$type	=	'排序';
		}else{
			unset($addData['sort']);
		}
		$this	->	update_once('desc_class',$addData,$whereData);
		$showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';
		$this	->	adminLog("单页面类别".$showid.$type."修改成功");
	}
	/*
	 * 删除单页面类别
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多个删除
	 * $whereData 	删除分类查询条件 
	 */
	function delDesClass($whereData=array(),$data=array()){
		if($data['type']=='one'){//单个删除
			$limit			=	'limit 1';
		}
		if($data['type']=='all'){//多个删除
			$limit			=	'';
		}
		 
		$result				=	$this	->	delete_all('desc_class',$whereData,$limit);
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '单页面类别删除成功！' : '删除失败！';
		return	$return;
	}
	/*
	 * 获取单页面列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getDesList($whereData=array(),$data=array('field'=>'*')){
		$list	=	$this	->	select_all('description',$whereData,$data['field']);
		return	$list;
	}
	/*
	 * 获取单个单页面
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getDes($whereData=array(),$data=array('field'=>'*')){
		$one	=	$this	->	select_once('description',$whereData,$data['field']);
		
		if(!empty($one)){
		    
		    $one['content']=str_replace(array("&nbsp;","&"),array(" ","&amp;"),$one['content']);
		    
		    preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$one['content'],$res);
		    
		    if(!empty($res[3])){
		        foreach($res[3] as $v){
		            if(strpos($v,'http:')===false && strpos($v,'https:')===false){
		                
		                $one['content'] = str_replace($v,$this->config['sy_ossurl'].$v,$one['content']);
		            }
		        }
		    }
		}
		
		return	$one;
	}
	/*
	 * 添加单页面
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function addDes($addData=array(),$data=array()){
		$return	=	$this	->	insert_into('description',$addData);
		return	$return;
	}
	/*
	 * 更新单页面
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function upDes($addData=array(),$whereData=array()){
		$return	=	$this	->	update_once('description',$addData,$whereData);
		return	$return;
	}
	/*
	 * 删除单页面
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多个删除
	 * $id 			单页面id，可以使数组 
	 */
	function delDes($id,$data=array()){

		if(is_array($id)){
			$where['id']	=	array('in',pylode(',',$id));
			$limit			=	'';
		}else{
			$where['id']	=	array('in',$id);
			$limit			=	' limit 1';
		}
		$des	=	$this	->	getDesList($where);
		foreach($des as $dk=>$dv){
			if(file_exists($dv['url'])){
				@unlink($dv['url']);
			}
		}
		 
		$result				=	$this	->	delete_all('description',$where,$limit);
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '单页面删除成功！' : '删除失败！';
		return	$return;
	}
	
}
?>