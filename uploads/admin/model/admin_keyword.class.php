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
class admin_keyword_controller extends adminCommon{
	
	function index_action(){
		$hotM 						 	=  		$this -> MODEL('hotkey');
		$keywordarr						=		array("1"=>"店铺招聘","2"=>"兼职","3"=>"职位","4"=>"公司","5"=>"简历","8"=>"微信搜索",'12'=>'问答','13'=>'普工简历');
		$search_list[]					=		array("param"=>"type","name"=>'数据类型',"value"=>$keywordarr);
		$search_list[]					=		array("param"=>"rec","name"=>'推荐',"value"=>array("1"=>"已推荐","2"=>"未推荐"));
		$search_list[]					=		array("param"=>"check","name"=>'审核状态',"value"=>array("1"=>"已审核","2"=>"未审核"));
					
		$type							=		(int)$_GET['type'];
					
		if($type){			
			$where['type']				=		$type;
			$urlarr['type']				=		$_GET['type'];
		}
		if ($_GET['rec']=='1'){
			$where['tuijian']			=		1;
			$urlarr['rec']				=		$_GET['rec'];
		}elseif ($_GET['rec']=='2'){
			$where['tuijian']			=		0;
			$urlarr['rec']				=		$_GET['rec'];
		}
		if ($_GET['bold']=='1'){
			$where['bold']				=		1;
			$urlarr['bold']				=		$_GET['bold'];
		}elseif ($_GET['bold']=='2'){
			$where['bold']				=		0;
			$urlarr['bold']				=		$_GET['bold'];
		}
		if(trim($_GET['keyword'])){
			$where['key_name']			=		array('like',trim($_GET['keyword']));
			
			if ($_GET['cate']!=''){
				$where['type']			=		$_GET['cate'];
			}
			
			$urlarr['cate']				=		$_GET['cate'];
			$urlarr['keyword']			=		$_GET['keyword'];
			$urlarr['news_search']		=		$_GET['news_search'];
		}
		if($_GET["check"]==1){
			$where['check']				=		$_GET['check'];
			$urlarr['check']			=		$_GET['check'];
		}elseif($_GET['check']=="2"){
			$where['check']				=		array('<>',1);
			$urlarr['check']			=		$_GET['check'];
		}
		
	    $urlarr['page']	 				=  		'{{page}}';
	    $pageurl		 				=		Url($_GET['m'],$urlarr,'admin');
	    $pageM			 				=		$this  -> MODEL('page');
	    $pages			 				=		$pageM -> pageList('hot_key',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        if($_GET['order']){
	            $where['orderby']  		=  		$_GET['t'].','.$_GET['order'];
	            $urlarr['order']   		=  		$_GET['order'];
	            $urlarr['t']	   		=  		$_GET['t'];
	        }else{				
	            $where['orderby']  		=  		'id,desc';
	        }	
			
	        $where['limit']		   		=  		$pages['limit'];
					
	        $List  						= 	 	$hotM -> getList($where);
            
	        $this -> yunset(array('rows'=>$List));
	    }

		
		$this->yunset("keywordarr",$keywordarr);
		$this->yunset("search_list",$search_list);
        $this->yunset("get_type", $_GET);
		$this->yuntpl(array('admin/admin_keyword'));
	}
	function info_action(){
		$hotM 						 	=  		$this -> MODEL('hotkey');
		$hot_key						=		$hotM->getHotkeyOne(array('id'=>$_POST['id']));
		echo  json_encode($hot_key);die;
	}
	function save_action(){
		if(trim($_POST['key_name'])==''){
			$this->ACT_layer_msg("关键字名称不能为空！",8,$_SERVER['HTTP_REFERER']);
		}else{
			$hotM 						=  		$this -> MODEL('hotkey');
			$value['key_name']			=		trim($_POST['key_name']);
			$value['num']				=		trim($_POST['num']);
			$value['type']				=		trim($_POST['type']);
			$value['size']				=		trim($_POST['size']);
			$value['bold']				=		trim($_POST['bold']);
			$value['color']				=		trim($_POST['color']);
			$value['tuijian']			=		trim($_POST['tuijian']);
			$value['check']				=		'1';
			
			if($_POST['id']){
				$id						=		$_POST['id'];
				$oid					=		$hotM->upHotkey(array('id'=>$_POST['id']),$value);
			}else{
				$oid					=		$hotM->addInfo($value);
				$id						=		$oid;
			}
			$this->get_cache();
			$oid?$this->ACT_layer_msg("关键字(ID:".$id.")保存成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("保存失败，请销后再试！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	function del_action(){
		$hotM 							=  		$this -> MODEL('hotkey');
		if(is_array($_POST['del'])){
			$where['id']				=		array('in',pylode(',',$_POST['del']));
		}else{
			$this->check_token();
			$where['id']				=		$_GET['id'];
		}
		
		$return 						=		$hotM->delHotkey($where);
		
		$this->get_cache();
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		
	}
	
	function recup_action(){
		$this->check_token();
		$hotM 							=  		$this -> MODEL('hotkey');
		$data['type']					=		$_GET['type'];
		$data['id']						=		$_GET['id'];
		$data['rec']					=		$_GET['rec'];
		$return							=		$hotM->recupHotkey($data);
		$this->get_cache();
		echo $return ? 1 : 0;die;
	}
	function get_cache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->keyword_cache("keyword.cache.php");
	}
	function status_action(){
    
    $hotM 							=  		$this -> MODEL('hotkey');
		 if($_POST['pid']){
      
			$aid 						= 		$_POST['pid'];
			
			$data['size']				=		$_POST['size'];
			$data['check']				=		$_POST['status'];
			$data['tuijian']			=		$_POST['tuijian'];
			$data['bold']				=		$_POST['bold'];
			if($_POST['type']){
				$data['type']			=		$_POST['type'];
			}	
			$data['color']				=		$_POST['color'];	
			$id							=		$hotM->upHotkey(array('id'=>array('in',$aid)) , $data);
			$this->get_cache();
			$id?$this->ACT_layer_msg("关键字(ID:".$aid.")设置成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("设置失败！",8,$_SERVER['HTTP_REFERER']);
		}else{
			$this->ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	function state_action(){
		if($_POST['sid']){
			$aid 			= 	$_POST['sid'];
			$data['check']	=	$_POST['status'];
			$hotM 			=  	$this -> MODEL('hotkey');
			$id				=	$hotM->upHotkey(array('id'=>array('in',$aid)) , $data);
			$this->get_cache();
			$id?$this->ACT_layer_msg("关键字(ID:".$aid.")批量审核成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("批量审核失败！",8,$_SERVER['HTTP_REFERER']);
		}else{
			$this->ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>