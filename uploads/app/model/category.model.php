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
class category_model extends model{
	/*
	 * 添加管理员日志 
	 */
	function adminLog($content, $opera = '', $type = '', $opera_id=''){
		require_once('log.model.php');
		$logM	=	new log_model($this->db,$this->def);
		return	$logM	->	addAdminLog($content, $opera = '', $type = '', $opera_id='');
	}
	/*
	 * 后台缓存刷新
	 * $function	: 刷新缓存的对应方法名称
	 * $type		: 缓存文件开头名称
	 */
	function cache_action($function,$type){
		include(LIB_PATH."cache.class.php");
		$cacheclass	=	new cache("../data/plus/",$this);
		$makecache	=	$cacheclass	->	$function($type.".cache.php");
	}
	/*
	 * 获取自身及子类id集
	 * $table	: 查询的表格名字符串 
	 * $id		: 查询父类id，数组形式的id集合 
	 * $type	: 'onlyson' 只获取子类id合集
	 */
	function sonclass($table,$id=array(),$type='all'){
		$ids	=	array();
		if(count($id)){
			$class	=	$this	->	select_all($table,array('keyid'=>array('in',pylode(',',$id))),"id");
			if($class&&is_array($class)){
				foreach($class as $val){
					$ids[]	=	$val['id'];
				}
			}
			if($ids&&is_array($ids)){
			    $cl	=	$this	->	select_all($table,array('keyid'=>array('in',pylode(',',$ids))),"id");
			}
			if($cl){
				foreach($cl as $v){
					$ids[]	=	$v['id'];
				}
			}
			$ids	=	array_unique($ids);
			if($type!='onlyson'){
				$ids	=	array_merge($id,$ids);
			}
		}
		return $ids;
	}
	/////////////////////////////////////////////////////////个人会员分类//////////////////////////////////////////////////////////////
	/*
	 * 获取单个个人会员分类
	 * $whereData 	查询条件
	 * $field		自定义查询字段
	 */
	function getUserClass($whereData=array(),$field='*'){
		$one	=	array();
		$one	=	$this	->	select_once('userclass',$whereData,$field);
		return	$one;
	}
	/*
	 * 获取个人会员分类列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getUserClassList($whereData=array(),$field='*'){
		$list	=	array();
		$list	=	$this	->	select_all('userclass',$whereData,$field);
		return	$list;
	}

	/*
	 * 添加或更新个人会员分类
	 * $addData 	提交分类数据
	 * $whereData 	更新分类查询条件 
	 */
	function addUserClass($addData=array(),$whereData = array()){
		
		if(!empty($whereData)){
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
			$this	->	update_once('userclass',$addData,$whereData);
			$this 	->  cache_action('user_cache','user');
			$showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';
			$this	->	adminLog("个人会员分类".$showid.$type."修改成功");
		}else{
			$name				=	array();
			foreach ($addData['name'] as $val){
				if($val){
					$name[]		= 	$val;
				}
			}

			if($addData['keyid']){
				$where['keyid'] =	$addData['keyid'];
			}
			if(count($name)){
				$where['name']  =	array('in',"'".@implode("','",$name)."'");
			}
			//检查提交的类别名称是否有同名
			$userclass			=	$this	->	getUserClassList($where);
			$valueData			=	array();
			
			if(empty($userclass)){//没有同名类则正常添加
				if($addData['ctype'] == '1'){//添加的是一级分类
					foreach ($name as $key => $val){
			            foreach ($addData['variable'] as $k => $v){
			                if($k == $key){
			                	$valueData[$key]['name']		= $val;
			                	$valueData[$key]['variable']	= trim($v);
			                }
			            }
			        }
			    }else{//添加二级分类
					foreach ($name as $key => $val){
						$valueData[$key]['name'] 	= $val;
			            $valueData[$key]['keyid'] 	= intval($addData['keyid']);
			        }
			    }
				$ucid 			=	$this	->	DB_insert_multi('userclass',$valueData);
				$this 			->  cache_action('user_cache','user');
				$return['msg']	=	$ucid ? 2 : 3;
				$this 			->  adminLog("个人会员分类添加成功");
			}else{//有同名类，给出提示
				$return['msg']	=	1;
			}
		}
		return	$return;
	}
	/*
	 * 删除个人会员分类
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多选删除
	 * $whereData 	删除项查询条件(只需要当前选择删除的项，不需要包含子类查询) 
	 */
	function delUserClass($whereData=array(),$data=array()){
		$id					=	array();
		$uclass				=	$this	->	getUserClassList($whereData,'id');
		foreach ($uclass as $key => $value) {
			$id[]			=	$value['id'];
		}
		$ids				=	$this	->	sonclass('userclass',$id);//获取当前项及其所有子类的id集合
		if(count($ids)){
			$result	=	$this -> delete_all('userclass', array('id'=>array('in',pylode(',',$ids))), '');
		}
		$this 				->  cache_action('user_cache','user');
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '个人会员分类删除成功！' : '删除失败！';
		return	$return;
	}
	/////////////////////////////////////////////////////////城市管理分类//////////////////////////////////////////////////////////////
	/*
	 * 获取城市分类列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getCityClassList($whereData=array(),$field='*'){

		$list	=	$this	->	select_all('city_class',$whereData,$field);
		return	$list;
	}
	/*
	 * 添加城市
	 * $addData[$k][字段名]		添加多条数据
	 */
	function addCityClass($addData=array()){
		return $this	->	DB_insert_multi('city_class',$addData);
	}
	
	/*
	 * 更新城市
	 * $whereData 	查询条件
	 * $addData 	更新数据 
	 * $date		自定义数组 type: single 单个更新，multi 循环多次更新
	 */
	function upCityClass($whereData=array(),$addData=array(),$data=array()){
		
		if($addData['name']!=""){
			$this	->	update_once('city_class',$addData,$whereData);
			if($data['type']=='single'){
				$this	->	cache_action('city_cache','city');
				$this	->	adminLog("更新城市(ID:".$whereData['id'].")");
			}
			$return	=	'1';
		}else{
			$return	=	'2';
		}
		return	$return;
	}
	/*
	 * 删除城市
	 * $whereData 	查询条件
	 * $data 		自定义处理数组 
	 */
	function delCityClass($whereData=array(),$data=array()){
		
		$id_arr			=	array();
		$city_arr 		=	$this	->	getCityClassList($whereData,'id');
		foreach($city_arr as $key=>$value){
			$id_arr[]	=	$value['id'];
		}
		$ids			=	$this	->	sonclass('city_class',$id_arr);//获取当前项及其所有子类的id集合
		if(count($ids)){
			 
			$result	=	$this -> delete_all('city_class', array('id'=>array('in',pylode(',',$ids))),'');
		}
		$this -> adminLog("删除城市");
		if($result){
			$this -> cache_action('city_cache','city');	
			$return['error']=	'1';
		}else{
			$return['error']=	'2';
		}
		return	$return;
	}
	///////////////////////////////////////////////////////行业分类//////////////////////////////////////////////////////////////////
	/*
	 * 获取行业分类列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getIndustryClassList($whereData=array(),$field='*'){
		$list	=	array();
		$list	=	$this	->	select_all('industry',$whereData,$field);
		return	$list;
	}
	/*
	 * 添加行业分类
	 * $addData 	提交分类数据 'name':名称为数组形式封装
	 */
	function addIndustryClass($addData=array()){
		$valueData	=	array();
		
		$whereData['name']	=	array('in',"'".@implode("','", $addData['name'])."'");
		$industry			=	$this	->	getIndustryClassList($whereData);

		if(empty($industry)){
		    foreach ($addData['name'] as $key=>$val){
		        $valueData[$key]['name']=$val;
		    }
		    $result	=	$this	->	DB_insert_multi('industry',$valueData);
		    $this	->	cache_action('industry_cache','industry');
		    $return	=	$result	?	2	:	3;
		    $this	->	adminLog("行业类别添加成功！");
		}else{
			$return=1;
		}
		return	$return;
	}
	/*
	 * 更新行业分类
	 * $addData 	提交分类数据
	 * $whereData 	更新查询
	 */
	function upIndustryClass($addData=array(),$whereData=array()){
		
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
		$this	->	update_once('industry',$addData,$whereData);
		$showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';
		$this	->	cache_action('industry_cache','industry');
		$this	->	adminLog("行业分类".$showid.$type."修改成功");
	}
	/*
	 * 删除行业分类
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多个删除
	 * $whereData 	删除分类查询条件 
	 */
	function delIndustryClass($whereData=array(),$data=array()){
		if($data['type']=='one'){//单个删除
			$limit			=	'limit 1';
		}
		if($data['type']=='all'){//多个删除
			$limit			=	'';
		}
		 
		$result	=	$this -> delete_all('industry',$whereData,$limit);
		$this -> cache_action('industry_cache','industry');
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '行业分类删除成功！' : '删除失败！';
		return	$return;
	}
	
	/////////////////////////////////////////////////////////职位类别管理//////////////////////////////////////////////////////////////
	/*
	 * 获取单个职位分类
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getJobClass($whereData=array(), $field = '*'){
		$one	=	array();
		$one	=	$this	->	select_once('job_class',$whereData,$field);
		return	$one;
	}
	/*
	 * 获取职位类别列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段  
	 * $date 		自定义数组 默认为空，即自定义查询
	 *				type: 'oneall':根据指定一级分类获取所有二三级子类,此时的whereData查询条件为查询所有二级子类的查询条件
	 */
	function getJobClassList($whereData=array(), $field = '*',$data=array()){
		if(!count($data)){//自定义查询
			$list	=	$this	->	select_all('job_class',$whereData,$field);

			return	$list;
		}else{
			if($data['type']=='oneall'){//根据一级获取此一级下所有二三级子类，whereData查询条件应为查询所有二级子类的查询条件
				$twojob		=	array();
				$threejob	=	array();
				$twojob		=	$this	->	select_all('job_class',$whereData,'id,sort,name,e_name');
				if(is_array($twojob)){
					$val	=	array();
					foreach($twojob as $key=>$v){
						$val[]	=	$v['id'];
					}
					$root_arr	=	pylode(",",$val);
				}
				$whereThree['keyid']	=	array('in',$root_arr,'OR');	
				$whereThree['orderby']	=	'sort,asc';
				$tjobs=$this	->	select_all('job_class',$whereThree);
				if(is_array($tjobs)){
					foreach($tjobs as $key=>$v){
						$threejob[$v['keyid']][]	=	$v;//三级分类已他们的父类二级分类id分组
					}
				}
				$rdata['twojob']	=	$twojob;
				$rdata['threejob']	=	$threejob;
				return $rdata;
			}
		}
		
	}
	/*
	 * 添加或更新职位类别
	 * $data 	数据
	 */
	function addJobClass($data=array()){
		$addData			=	array();
		$where['name']		=	$data['name'];

		if($data['id']){
			$where['id']	=	array('<>',$data['id']);
		}
		if($data['nid'] || $data['keyid']){
			if($data['keyid']){
				$where['keyid']	=	$data['keyid'];
			}else{
				$where['keyid']	=	$data['nid'];
			}
		} 
		$info	=	$this	->	getJobClass($where); 
		
		if($info['id']){//检查同类别下是否重名
			$return['errcode']		=	'8';
			$return['msg']			=	'该类别已存在！';
			$return['url']			=	$_SERVER['HTTP_REFERER'];
		}else{//开始添加
			if($data['keyid']!=""){
				$addData['keyid']	=	$data['keyid'];
			}elseif($data['nid']!=""){
				$addData['keyid']	=	$data['nid'];
			}
			$addData['name']	=	$data['name'];
			$addData['e_name']	=	$data['e_name'];
			$addData['sort']	=	$data['sort'];
			$addData['content']	=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'','',''),$data['content']);
			if($data['id']){
				$nid	=	$this	->	update_once("job_class",$addData,array('id'=>$data['id']));
				$msg="更新";
			}else{
				$nid	=	$this	->	insert_into("job_class",$addData);
				$msg="添加";
			}
			$this	->	cache_action('job_cache','job');

			$return['errcode']		=	$nid	?	'9'	:	'8';
			$return['msg']			=	$msg."成功！";
			$return['url']			=	"index.php?m=admin_job";
			$return['type']			=	$nid	?	'1'	:	'0';
			
		}
		return $return;
	}
	/*
	 * 更新职位分类
	 * $addData 	提交分类数据
	 * $whereData 	更新查询
	 */
	function upJobClass($addData=array(),$whereData=array()){
		
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
		$result	=	$this	->	update_once('job_class',$addData,$whereData);
		$showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';
		$this	->	cache_action('job_cache','job');
		$this	->	adminLog("行业分类".$showid.$type."修改成功");
		
		return $result;
	}
	/*
	 * 删除行业分类
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多选删除
	 * $whereData 	删除分类查询条件 
	 */
	function delJobClass($whereData=array(),$data=array()){

		$id					=	array();
		$sclass				=	$this	->	getJobClassList($whereData,'id');
		foreach ($sclass as $key => $value) {
			$id[]			=	$value['id'];
		}
		$ids				=	$this	->	sonclass('job_class',$id);//获取当前项及其所有子类的id集合
		if(count($ids)){
			 
			$result	=	$this -> delete_all('job_class',array('id'=>array('in',pylode(',',$ids))), '');
		}
		$this 				->  cache_action('job_cache','job');
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '删除成功！' : '删除失败！';
		return	$return;
	}
	/////////////////////////////////////////////////////////企业会员分类//////////////////////////////////////////////////////////////
	/*
	 * 获取单个企业会员分类
	 * $whereData 	查询条件
	 * $field		自定义查询字段  
	 */
	function getComClass($whereData=array(),$field='*'){
		$one	=	array();
		$one	=	$this	->	select_once('comclass',$whereData,$field);
		return	$one;
	}
	/*
	 * 获取企业会员类别列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getComClassList($whereData=array(),$field='*'){
		$list	=	array();
		$list	=	$this	->	select_all('comclass',$whereData,$field);
		return	$list;
	}
	/*
	 * 添加或更新企业会员类别
	 * $addData 	提交分类数据
	 * $whereData 	更新分类查询条件 
	 */
	function addComClass($addData=array(),$whereData = array()){
		
		if(!empty($whereData)){
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
			$this	->	update_once('comclass',$addData,$whereData);
			$this 	->  cache_action('com_cache','com');
			$showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';
			$this	->	adminLog("企业会员分类".$showid.$type."修改成功");
		}else{
			$name				=	array();
			foreach ($addData['name'] as $val){
				if($val){
					$name[]		= 	$val;
				}
			}

			if($addData['keyid']){
				$where['keyid'] =	$addData['keyid'];
			}
			if(count($name)){
				$where['name']  =	array('in',"'".@implode("','",$name)."'");
			}
			//检查提交的类别名称是否有同名
			$class			=	$this	->	getComClassList($where);
			$valueData			=	array();
			
			if(empty($class)){//没有同名类则正常添加
				if($addData['ctype'] == '1'){//添加的是一级分类
					foreach ($name as $key => $val){
			            foreach ($addData['variable'] as $k => $v){
			                if($k == $key){
			                	$valueData[$key]['name']		= $val;
			                	$valueData[$key]['variable']	= trim($v);
			                }
			            }
			        }
			    }else{//添加二级分类
					foreach ($name as $key => $val){
						$valueData[$key]['name'] 	= $val;
			            $valueData[$key]['keyid'] 	= intval($addData['keyid']);
			        }
			    }
				$ucid 			=	$this	->	DB_insert_multi('comclass',$valueData);
				$this 			->  cache_action('com_cache','com');
				$return['msg']	=	$ucid ? 2 : 3;
				$this 			->  adminLog("企业会员分类添加成功");
			}else{//有同名类，给出提示
				$return['msg']	=	1;
			}
		}
		return	$return;
	}
	/*
	 * 删除企业会员分类
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多个删除
	 * $whereData 	删除分类查询条件 
	 */
	function delComClass($whereData=array(),$data=array()){

		$id					=	array();
		$sclass				=	$this	->	getComClassList($whereData,'id');
		foreach ($sclass as $key => $value) {
			$id[]			=	$value['id'];
		}
		$ids				=	$this	->	sonclass('comclass',$id);//获取当前项及其所有子类的id集合
		if(count($ids)){
			 
			$result	=	$this	->	delete_all('comclass',array('id'=>array('in',pylode(',',$ids))), '');
		}
		$this -> cache_action('com_cache','com');
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '企业会员分类删除成功！' : '删除失败！';
		return	$return;
	}
	/////////////////////////////////////////////////////////////兼职分类//////////////////////////////////////////////////////////////
	/*
	 * 获取单个兼职分类
	 * $whereData 	查询条件
	 * $field		自定义查询字段  
	 */
	function getPartClass($whereData=array(),$field='*'){
		$one	=	array();
		$one	=	$this	->	select_once('partclass',$whereData,$field);
		return	$one;
	}
	/*
	 * 获取兼职类别列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getPartClassList($whereData=array(),$field='*'){
		$list	=	array();
		$list	=	$this	->	select_all('partclass',$whereData,$field);
		return	$list;
	}
	/*
	 * 添加或更新兼职类别
	 * $addData 	提交分类数据
	 * $whereData 	更新分类查询条件 
	 */
	function addPartClass($addData=array(),$whereData = array()){
		
		if(!empty($whereData)){
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
			$this	->	update_once('partclass',$addData,$whereData);
			$this 	->  cache_action('part_cache','part');
			$showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';
			$this	->	adminLog("兼职分类".$showid.$type."修改成功");
		}else{
			$name				=	array();
			foreach ($addData['name'] as $val){
				if($val){
					$name[]		= 	$val;
				}
			}

			if($addData['keyid']){
				$where['keyid'] =	$addData['keyid'];
			}
			if(count($name)){
				$where['name']  =	array('in',"'".@implode("','",$name)."'");
			}
			//检查提交的类别名称是否有同名
			$class			=	$this	->	getPartClassList($where);
			$valueData			=	array();
			
			if(empty($class)){//没有同名类则正常添加
				if($addData['ctype'] == '1'){//添加的是一级分类
					foreach ($name as $key => $val){
			            foreach ($addData['variable'] as $k => $v){
			                if($k == $key){
			                	$valueData[$key]['name']		= $val;
			                	$valueData[$key]['variable']	= trim($v);
			                }
			            }
			        }
			    }else{//添加二级分类
					foreach ($name as $key => $val){
						$valueData[$key]['name'] 	= $val;
			            $valueData[$key]['keyid'] 	= intval($addData['keyid']);
			        }
			    }
				$ucid 			=	$this	->	DB_insert_multi('partclass',$valueData);
				$this 			->  cache_action('part_cache','part');
				$return['msg']	=	$ucid ? 2 : 3;
				$this 			->  adminLog("兼职分类添加成功");
			}else{//有同名类，给出提示
				$return['msg']	=	1;
			}
		}
		return	$return;
	}
	/*
	 * 删除兼职分类
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多个删除
	 * $whereData 	删除分类查询条件 
	 */
	function delPartClass($whereData=array(),$data=array()){

		$id					=	array();
		$sclass				=	$this	->	getPartClassList($whereData,'id');
		foreach ($sclass as $key => $value) {
			$id[]			=	$value['id'];
		}
		$ids				=	$this	->	sonclass('partclass',$id);//获取当前项及其所有子类的id集合
		if(count($ids)){
			 
			$result	=	$this -> delete_all('partclass', array('id'=>array('in',pylode(',',$ids))), '');
		}
		$this -> cache_action('part_cache','part');
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '兼职分类删除成功！' : '删除失败！';
		return	$return;
	}
	
	
	
	///////////////////////////////////////////////////////举报原因//////////////////////////////////////////////////////////////////
	/*
	 * 获取举报原因列表
	 * $whereData 	查询条件
	 * $field		自定义查询字段 
	 */
	function getReasonClassList($whereData=array(),$field='*'){
		$list	=	array();
		$list	=	$this	->	select_all('reason',$whereData,$field);
		return	$list;
	}
	/*
	 * 添加或修改举报原因
	 * $addData 	提交分类数据 'name':名称为数组形式封装
	 * $whereData 	更新查询
	 */
	function addReasonClass($addData=array(),$whereData=array()){
		
		
	    if(count($whereData)){
			$result	=	$this	->	update_once('reason',$addData,$whereData);
			$type	=	'修改';
	    }else{
	    	$result	=	$this	->	insert_into('reason',$addData);
	    	$type	=	'添加';
	    }
	    $status			=	$result	?	'成功'	:	'失败';
	    $return['msg']	=	'举报原因'.$type.$status;
	    $return['cod']	=	$result	?	9	:	8;
		$this	->	adminLog("举报原因".$type.$status);
		$this	->	cache_action('reason_cache','reason');
		return	$return;
	}
	
	/*
	 * 删除举报原因
	 * $data 		自定义数组数据
	 *				$data['type'] :one 单个删除 
	 *							  :all 多个删除
	 * $whereData 	删除分类查询条件 
	 */
	function delReasonClass($whereData=array(),$data=array()){
		if($data['type']=='one'){//单个删除
			$limit			=	'limit 1';
		}
		if($data['type']=='all'){//多个删除
			$limit			=	'';
		}
		 
		$result	=	$this -> delete_all('reason', $whereData, $limit);
		$this -> cache_action('reason_cache','reason');
		$return['errcode']	=	$result ? '9' :'8';
		$return['layertype']=	$data['type']=='all' ? 1 : 0;
		$return['msg']		=	$result ? '举报原因删除成功！' : '删除失败！';
		return	$return;
	}
	
	function getIntroduceClassList($whereData=array(),$field='*'){
	    $list	=	array();
	    $list	=	$this	->	select_all('introduce_class',$whereData,$field);
	    return	$list;
	}
	
	function addIntroduceClass($addData=array(),$whereData=array()){
		
		
	    if(count($whereData)){
			$result	=	$this	->	update_once('introduce_class',$addData,$whereData);
			$type	=	'修改';
	    }else{
	    	$result	=	$this	->	insert_into('introduce_class',$addData);
	    	$type	=	'添加';
	    }
	    $status			=	$result	?	'成功'	:	'失败';
	    $return['msg']	=	'自我介绍'.$type.$status;
	    $return['errcode']	=	$result	?	9	:	8;
		$this	->	adminLog("自我介绍".$type.$status);
		$this 	->  cache_action('introduce_cache','introduce');
		return	$return;
	}
	
	function getIntroduceClass($whereData=array(),$field='*'){
		$one	=	array();
		$one	=	$this	->	select_once('introduce_class',$whereData,$field);
		return	$one;
	}
	
	function delIntroduceClass($whereData=array(),$data=array()){
	    if(count($whereData)){
			 
	        $result	=	$this	->	delete_all('introduce_class', $whereData, '');
	    }
	    $return['errcode']	=	$result ? '9' :'8';
	    $return['layertype']=	$data['type']=='all' ? 1 : 0;
	    $return['msg']		=	$result ? '自我介绍删除成功！' : '删除失败！';
		$this 	->  cache_action('introduce_cache','introduce');
	    return	$return;
	}
	function setPinYin($where=array(),$data=array()){
		$field	=	$data['field']?$data['field']:'*';
		include LIB_PATH.'pin.php';
		$post	=	$data['post'];
		$page	=	$post['page'];
		$where['limit']	=	$post['pagesize'];
		if($data['type']=='city'){
			$list	=	$this -> getCityClassList($where,$field);
		}elseif($data['type']=='job'){
			$list	=	$this -> getJobClassList($where,$field);
		}
		if($list&&is_array($list)){
			foreach($list as $k=>$v){
				if($data['type']=='city'&&Pinyin($v['name'],'1')){
					$nid=$this -> update_once('city_class',array('e_name'=>Pinyin($v['name'],'1')),array('id'=>$v['id']));
				}elseif($data['type']=='job'&&Pinyin($v['name'],'1')){
					$v['name']	=	str_replace('/','',$v['name']);
					$nid=$this -> update_once('job_class',array('e_name'=>Pinyin($v['name'],'1')),array('id'=>$v['id']));
				}
			}
			if($nid){
				$return['msg']		=	'正在生成'.($page*$post['pagesize']+1).'-'.($page+1)*$post['pagesize'].'条数据！';
				$page++;
				$return['page']		=	$page;
				$return['pagesize']	=	$post['pagesize'];
				$return['errcode']	=	'8';
			}else{
				$return['msg']		=	'生成拼音成功！';
				$return['errcode']	=	'9';
			}
		}else{
			$return['msg']		=	'生成拼音成功！';
			$return['errcode']	=	'9';
		}
		return $return;
	}
	function setChaChong($where=array(),$data=array()){
		$field	=	$data['field']?$data['field']:'*';
		$rows	=	$this -> select_all($data['type'].'_class',$where,$field);
		if(count($rows)>($data['page']+1)*10){
			$page	=	$data['page']+1;
		}else{
			$page	=	0;
		}
		$where['limit']	=	array($data['page']*10,10);
		if($data['type']=='city'){
			$list	=	$this -> getCityClassList($where,$field);
		}elseif($data['type']=='job'){
			$list	=	$this -> getJobClassList($where,$field);
		}
		return array('list'=>$list,'page'=>$page);
	}

	function clearPinYin($table){
	
		$this -> update_once($table, array('e_name'=>''),array('id'=> array('>','0')));
	
	}
}
?>