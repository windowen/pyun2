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
class link_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
		$lo_time		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		if($this->config["sy_web_site"]=='1'){
			$cacheM  	=	$this -> MODEL('cache');
			$domains	=	$cacheM -> GetCache('domain',$Options=array('needreturn'=>true,'needassign'=>true,'needall'=>true));
			
		    $domain=array();
		    foreach($domains['site_domain'] as $val){
		        $domain[$val['id']]	=	$val['cityname'];
		    }
		    $search_list[]	=	array("param"=>"did","name"=>'显示站点',"value"=>$domain);
		}
		$search_list[]	=	array("param"=>"link","name"=>'发布时间',"value"=>$lo_time);	
		$search_list[]	=	array("param"=>"type","name"=>'类型',"value"=>array("1"=>"文字链接","2"=>"图片链接"));
		$search_list[]	=	array("param"=>"state","name"=>'审核状态',"value"=>array("1"=>"已审核","2"=>"未审核"));
		
		$this->yunset("search_list",$search_list);
	}
	function index_action(){
		$this->set_search();
		if($_GET['state']=='1'){
			
			$where['link_state']	=	1;
			$urlarr['state']		=	1;
			
		}elseif($_GET['state']=='2'){
			
			$where['link_state']	=	0;
			$urlarr['state']		=	2;
			
		}
		if($_GET['type']){
			
			$where['link_type']		=	$_GET['type'];
			$urlarr['type']			=	1;
			
		}
		if($_GET['did']){
			
			$where['did']			=	$_GET['did'];
			$urlarr['did']			=	$_GET['did'];
		}
		if($_GET['link']){
			if($_GET['link']=='1'){
				$where['link_time']		=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			}else{
				$where['link_time']		=	array('>',strtotime('-'.intval($_GET['link']).' day'));
			}
			$urlarr['link']				=	$_GET['link'];
		}
		if($_GET['news_search']!=''){
			if ($_GET['type']=='1'){
				
				$where['link_name']		=	array('like',trim($_GET['keyword']));
				$where['link_type']		=	1;
				
			}elseif ($_GET['type']=='2'){
				
				$where['link_name']		=	array('like',trim($_GET['keyword']));
				$where['link_type']		=	2;
				
			}else{
				
				$where['link_name']		=	array('like',trim($_GET['keyword']));
			}
			$urlarr['type']				=	$_GET['type'];
			$urlarr['keyword']			=	$_GET['keyword'];
			$urlarr['news_search']		=	$_GET['news_search'];
		}
		
		$urlarr['page']	=	"{{page}}";
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('admin_link',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){
			
	        if($_GET['order']){
	            $where['orderby']	=	$_GET['t'].','.$_GET['order'];

	            $urlarr['order']	=	$_GET['order'];
	            $urlarr['t']		=	$_GET['t'];
	        }else{
	            $where['orderby']	=	array('link_state,asc','link_time,desc');
	        }
	        $where['limit']			=	$pages['limit'];
			
			$linkM					=	$this  -> MODEL('link');
	        $rows    				=   $linkM -> getList($where);
			$this -> yunset("linkrows",$rows);
	    }
		
		//提取分站内容
	    $cacheM  =	$this -> MODEL('cache');
	    $domain  =	$cacheM -> GetCache('domain',$Options=array('needreturn'=>true,'needassign'=>true,'needall'=>true));
	    
	    $this -> yunset('Dname', $domain['Dname']);
		/***分站******/
		
		$this->yuntpl(array('admin/admin_link_list'));
	}

	function add_action(){
		//提取分站内容
	    $cacheM  =	$this -> MODEL('cache');
	    $domain  =	$cacheM -> GetCache('domain',$Options=array('needreturn'=>true,'needassign'=>true,'needall'=>true));
	    
	    $this -> yunset('Dname', $domain['Dname']);
		
		if($_GET['id']){
			$linkM	=	$this  -> MODEL('link');
			$info	=	$linkM -> getInfo(array('id'=>$_GET['id'])); 
			$this->yunset("info",$info);
			
			$this->yunset("lasturl",$_SERVER['HTTP_REFERER']);
		}
		$this->yuntpl(array('admin/admin_link_add'));
	}
	//删除链接
	function del_action(){
		
		if(is_array($_POST['del'])){
			$id	=	$_POST['del'];
		}else{
			$id	=	$_GET['id'];
		}
		
		$linkM	=	$this  -> MODEL('link');
		
		$return	=	$linkM -> delInfo($id);
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	//审核链接
	function status_action(){
			
		$id		=	$_POST['yesid'];
		$linkM	=	$this  -> MODEL('link');
		
		$return	=	$linkM -> setLinkStatus($id,array('status'=>$_POST['status']));
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	}
	//保存信息
	function save_action(){
		
		$linkM			=	$this  -> MODEL('link');
		if($_POST['phototype']==1){
			
			if($_FILES['file']['tmp_name']){
		 		$upArr    =  array(
					'file'  =>  $_FILES['file'],
					'dir'   =>  'link'
				);

				$uploadM  =  $this->MODEL('upload');

				$pic      =  $uploadM->newUpload($upArr);
				
				if (!empty($pic['msg'])){

					$this->ACT_layer_msg($pic['msg'],8);

				}elseif (!empty($pic['picurl'])){

					$pictures 	=  	$pic['picurl'];
				}
		 	}

			
		}else{
			$pictures		=	$_POST['uplocadpic'];
		}
		
		$post	=	array(
			'did'			=>	$_POST['did'],
			'link_name'		=>	trim($_POST['title']),
			'link_url'		=>	$_POST['url'],
			'link_type'		=>	$_POST['type'],
			'tem_type'		=>	$_POST['tem_type'],
			'img_type'		=>	$_POST['phototype'],
			'link_sorting'	=>	$_POST['sorting'],
			'link_state'	=>	1,
		);

		if(isset($pictures)){

			$post['pic']	=	$pictures;

		}

		$data	=	array(
			'post'	=>	$post,
			'id'	=>	$_POST['id'],
			'utype'	=>	'admin'
		);
		
		$return	=	$linkM -> addInfo($data);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=link");
	}
	function checksitedid_action(){
		$linkM	=	$this  -> MODEL('link');
		$data	=	array(
			'uid'=>$_POST['uid'],
			'did'=>$_POST['did']
		);
		
		$return	=	$linkM -> setLinkSite($data);
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	}
}

?>