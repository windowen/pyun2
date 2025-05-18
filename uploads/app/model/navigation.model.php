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
class navigation_model extends model{
	
	/**
	 * 获取导航列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getNavList($whereData=array(),$data=array('field'=>'*')){
		$list  =  $this -> select_all('navigation',$whereData,$data['field']);
		return	$list;
	}
	
	/**
	 * 获取单个导航
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getNav($whereData=array(),$data=array('field'=>'*')){
		$one  =  $this -> select_once('navigation',$whereData,$data['field']);
		if(!empty($one)){
			$one['pic_n']	=	checkpic($one['pic']);
		}
		return	$one;
	}
	
	/**
	 * 添加导航
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function addNav($addData=array(),$data=array()){
		// 处理上传缩略图
		
		if($addData['file']['tmp_name']){
			
			include_once('upload.model.php');
			
			$uploadM  =  new upload_model($this->db, $this->def);
			
			$upArr    =  array(
				'file'  =>  $addData['file'],
				'dir'   =>  'nav',
				'type'	=>	'nav'
			);
			
			$pic  =  $uploadM->newUpload($upArr);
			
			if (!empty($pic['msg'])){
				
				$res['msg']  =  $pic['msg'];
				
				return $res;
				
			}elseif (!empty($pic['picurl'])){
				
				$pictures 	=  	$pic['picurl'];
				
				$thumburl 	=  	$pic['thumburl'];
			}
			unset($addData['file']);
		}
		//处理缩略图
		if(isset($pictures)){
			
			$addData['pic']  =  $pictures;
			
		}
		$return  =  $this -> insert_into('navigation',$addData);
		return	$return;
	}
	
	/**
	 * 更新导航
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function upNav($addData, $whereData){
		// 处理上传缩略图
		
		if($addData['file']['tmp_name']){
			
			include_once('upload.model.php');
			
			$uploadM  =  new upload_model($this->db, $this->def);
			
			$upArr    =  array(
				'file'  =>  $addData['file'],
				'dir'   =>  'nav',
				'type'	=>	'nav'
			);
			
			$pic  =  $uploadM->newUpload($upArr);
			
			if (!empty($pic['msg'])){
				
				$res['msg']  =  $pic['msg'];
				
				return $res;
				
			}elseif (!empty($pic['picurl'])){
				
				$pictures 	=  	$pic['picurl'];
				
				$thumburl 	=  	$pic['thumburl'];
			}
			unset($addData['file']);
		}
		//处理缩略图
		if(isset($pictures)){
			
		    $addData['pic']  =  $pictures;
			
		}
		$return  =  $this -> update_once('navigation',$addData,$whereData);
		return	$return;
	}
	
	/**
	 * 删除导航
	 * $id 			导航id，可以是数组 
	 * $data		自定义
	 */
	function delNav($whereData=array(),$data=array()){
		$result	=	$this -> delete_all('navigation',$whereData,'');		
		return	$result;
	}
	
	/**
	 * 获取导航类型列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getNavTypeList($whereData=array(), $data=array('field'=>'*')){
		$list	=	$this	->	select_all('navigation_type', $whereData, $data['field']);
		return	$list;
	}
	/**
	 * 获取单个导航类型
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getNavType($whereData=array(),$data=array('field'=>'*')){
		$one  	=  $this -> select_once('navigation_type', $whereData, $data['field']);
		
		return	$one;
	}
	/**
	 * 添加导航类别
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function addNavType($addData=array(),$data=array()){
		$return  =  $this -> insert_into('navigation_type',$addData);
		return	$return;
	}
	/**
	 * 更新导航类别
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function upNavType($whereData=array(),$data=array()){
		$return  =  $this -> update_once('navigation_type',$data,$whereData);
		return	$return;
	}

	/**
	 * 删除导航类别
	 *$whereData   删除条件
	 */
	public function delNavType($whereData=array()){
		if($whereData){
			$nid	=	$this -> delete_all('navigation_type',$whereData,'');
		}
		return $nid;
	}
	/**
	 * 后台导航查询处理
	 * @param array $whereData
	 * @param array $data
	 */
	public function getAdminNavList($whereData=array(),$data=array('utype'=>null)){
	    //导航配置
	    $menurows  =  $this -> select_all('admin_navigation',$whereData);
	    
	    $i = $j = $a = $b = 0;
	    $navigation = $menu = $one_menu = $two_menu = array();
	    
	    if(!empty($menurows) && $data['utype'] == 'power'){
	        foreach($menurows as $key=>$v){
	            //根据登录管理员权限，获取后台导航
	            if($v['keyid']==0){
					$navId[]					  =  $v['id'];
	                $navigation[$i]['id']         =  $v['id'];
	                $navigation[$i]['name']       =  $v['name'];
	                $navigation[$i]['classname']  =  $v['classname'];
	                $navigation[$i]['sort']       =  $v['sort'];
	                $i++;
	            }
	            if($v['menu'] == 2){
	                $menu[$j]['id']         =  $v['id'];
	                $menu[$j]['keyid']      =  $v['keyid'];
	                $menu[$j]['name']       =  $v['name'];
	                $menu[$j]['classname']  =  $v['classname'];
	                $menu[$j]['url']        =  $v['url'];
	                $j++;
	            }
				$menuAll[$v['id']] = $v;
	        }
			
			if(!empty($menuAll)){
				//二级分类
				foreach($menuAll as $key => $va)
				{
					if(in_array($va['keyid'],$navId))
					{
						$one_menu[$va['keyid']][]	=  $va;
						$oneNavId[]			=	$va['id'];
						unset($menuAll[$key]);
					}
				}

				//三级分类
				foreach($menuAll as $key => $va)
				{
					if(in_array($va['keyid'],$oneNavId))
					{
						$two_menu[$va['keyid']][]	=	$va;
						$twoNavId[]			=	$va['id'];
						unset($menuAll[$key]);
					}
				}
				//四级分类
				foreach($menuAll as $key => $va)
				{
					if(in_array($va['keyid'],$twoNavId))
					{
						$three_menu[$va['keyid']][]	=	$va;
					}
				}
			}
		
	    }
	    if ($data['utype'] == 'power'){
	        $return  = array(
	            'navigation'  =>  $navigation,
	            'menu'        =>  $menu,
	            'one_menu'    =>  $one_menu,
	            'two_menu'    =>  $two_menu,
				'three_menu'    =>  $three_menu
	        );
	    }else{
	        $return['list']  =  $menurows;
	    }
	    
	    return $return;
	}
	/**
	 * 获取单个后台导航
	 * $whereData 	查询条件
	 * $data		
	 */
	function getAdminNav($whereData=array(),$data=array()){
	    
	    $nav  =  $this -> select_once('admin_navigation',$whereData);
	    
	    return	$nav;
	}
	/**
	 * 添加后台导航
	 * @param array $addData
	 * @param array $data
	 */
	public function addAdminNav($addData=array(),$data=array()){
	    
	    $return  =  $this -> insert_into('admin_navigation',$addData);
	    
	    return	$return;
	}
	/**
	 * 修改后台导航
	 * @param array $addData
	 * @param array $whereData
	 * @param array $data
	 */
	public function upAdminNav($upData=array(),$whereData=array(),$data=array()){
	    
	    $return  =  $this -> update_once('admin_navigation',$upData,$whereData);
	    
	    return	$return;
	}
	/**
	 * 删除后台导航
	 * @param array $whereData
	 * @param array $data
	 */
	public function delAdminNav($whereData=array(),$data=array()){
	    
	    $return  =  $this  ->  delete_all('admin_navigation',$whereData, '');
	    
	    return	$return;
	}
}
?>