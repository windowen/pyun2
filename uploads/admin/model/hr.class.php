<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class hr_controller extends adminCommon{
	
	//设置高级搜索功能
	
	function set_search(){
		
		$search_list[]	=	array("param"=>"status", "name"=>'前台显示', "value"=>array("1"=>"显示", "0"=>"不显示"));
		
		$ad_time		=	array('1'=>'今天', '3'=>'最近三天', '7'=>'最近七天', '15'=>'最近半月', '30'=>'最近一个月');
		
		$search_list[]	=	array("param"=>"end", "name"=>'上传日期', "value"=>$ad_time);
		
		$this->yunset("search_list",$search_list);
	}
	
	function index_action(){
		
		$this->set_search();
		
		$HrM		=	$this->MODEL('hr');
		
		if($_GET["type"]!=""){
			
			if($_GET["type"]=="1"){
				
				$where['name']	=	array('like',trim($_GET['keyword']));
			
			}elseif($_GET['type']=="2"){
				
				$hrclass 	=	$HrM -> getClassList(array('name'=>array('like',trim($_GET['keyword']))),'id');
				
				if ($hrclass){
		            
		            foreach($hrclass as $v){
						
		                $cids[]		=	$v['id'];
		            }
		            
		            $where['cid']   =	array('in',pylode(',', $cids));
		        }
			}
			
			$urlarr["keyword"]=$_GET["keyword"];
			
			$urlarr["type"]=$_GET["type"];
		}
		 
		if($_GET['status']=="0"){
			
			$where['is_show']	=	$_GET['status'];
			
			$urlarr['status']	=	$_GET['status'];
		
		}elseif($_GET['status']=="1"){
			
			$where['is_show']	=   $_GET['status'];
			
			$urlarr['status']	=	$_GET['status'];
		
		}	
	    
		if($_GET['end']){
			
			if($_GET['end'] == 1){
				
				$where['add_time']	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			
			}else{
				
				$where['add_time']	=	array('>=',strtotime('-'.intval($_GET['end']).' day'));
			}
			
			$urlarr['end']	=	$_GET['end'];
			
		}
		
		$urlarr['page']		=	"{{page}}";
		
		$pageurl			=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM				=	$this  -> MODEL('page');
		
		$pages				=	$pageM -> pageList('toolbox_doc',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			if($_GET['order']){
				
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
                
				$urlarr['order']		=	$_GET['order'];
                
				$urlarr['t']			=	$_GET['t'];
           
		    }else{   
                
				$where['orderby']		=	'id';        
            
			}    
            
			$where['limit']				=	$pages['limit'];         
            
			$ListHr   	=	$HrM -> getList($where);           
			
			$t_class	=	$HrM -> getClassList();
			
			if(is_array($ListHr )){
				
				foreach($ListHr  as $key=>$val){
					
					foreach($t_class as $value){
						
						if($val['cid']==$value['id']){
							
							$ListHr[$key]['cname']=$value['name'];
						
						}
					
					}
				
				}
			
			}
		
		}
		
		$this->yunset("rows" , $ListHr); 
			
		$this->yunset("get_type", $_GET);
			
		$this->yuntpl(array('admin/admin_hr_toolbox'));
		
	}
	
	function add_action(){
		
		$HrM	=	$this->MODEL('hr');
		
		if($_GET['id']){
			
			$id			=		intval($_GET['id']);
			
			$info		=		$HrM   ->  getInfo($id);
			
			$this 		->  yunset("row",  $info);
			
			$this 		->  yunset('lasturl',   $_SERVER['HTTP_REFERER']);
		
		}
		
		$ListHr		=	$HrM -> getClassList();
		
		$this->yunset("t_class", $ListHr);
		
		$this->yuntpl(array('admin/admin_hr_adddoc'));
	}
	function save_action(){
		
		$HrM	=	$this->MODEL('hr');
		
		if($_POST['submit']){
			
			if($_POST['name'] == ''){
				
				$this->ACT_layer_msg("文档名称不能为空！",8,$_SERVER['HTTP_REFERER']);
				
			}else if($_POST['cid'] == ''){
				
				$this->ACT_layer_msg("请选择文档分类！",8,$_SERVER['HTTP_REFERER']);
			}
			
			if($_FILES['file']['name']==''){
				
				if($_POST['url']==''){
					
					$this->ACT_layer_msg("请选择上传文档！",8,$_SERVER['HTTP_REFERER']);
				
				}else{
					
					$_POST['url']	=	str_replace($this->config['sy_weburl'],"",$_POST['url']);
					$post 			= 	array(
						'name'		=>	$_POST['name'],
						'cid'		=>	$_POST['cid'],
						'is_show'	=>	$_POST['is_show'],
						'url'		=>	$_POST['url'],
						'add_time'	=>	time(),
					);
					$nid	=	$HrM->upHrInfo(array('id'=>intval($_POST['id'])),array('post'=>$post));
					
					$nid?$this->ACT_layer_msg( "文档更新(ID:".$_POST['id'].")成功！",9,"index.php?m=hr",2,1):$this->ACT_layer_msg( "文档更新(ID:".$_POST['id'].")失败！",8,"index.php?m=hr",2,1);
				
				}
			
			}else{
				
				
				$upArr  =  array(
				    'file'  =>  $_FILES['file'],
				    'dir'   =>  'hrdoc'
				);
				
				$uploadM  =  $this->MODEL('upload');
				
				$result   =  $uploadM -> uploadDoc($upArr);
				
				if ($result['msg']){
				    
				    $this->ACT_layer_msg($result['msg'],8);
				    
				}else{
				    $_POST['url']  =  $result['docurl'];
				    
				    if($_POST['id']){
				        
				        $post	= 	array(
				            'name'		=>	$_POST['name'],
				            'cid'		=>	$_POST['cid'],
				            'is_show'	=>	$_POST['is_show'],
				            'url'		=>	$_POST['url'],
				            'add_time'	=>	time(),
				        );
				        $nid	=	$HrM->upHrInfo(array('id'=>intval($_POST['id'])),array('post'=>$post));
				        $msg="更新";
				    }else{
				        
				        $nid=$HrM -> addHrInfo($_POST);
				        $msg="添加";
				    }
				    
				    $nid?$this->ACT_layer_msg( "文档(ID:".$_POST['id'].")".$msg."成功！",9,"index.php?m=hr",2,1):$this->ACT_layer_msg( "文档(ID:".$_POST['id'].")".$msg."失败！",8,"index.php?m=hr",2,1);
				}
			}
		}
	}
	
	function del_action(){
		
		$this->check_token();
		
		$HrM	=	$this -> Model('hr');
		
		$delID	=	$_GET['id'] ? intval($_GET['id']) : $_GET['del'];
		
		$addArr	=	$HrM -> delHr($delID);
		
		$this   ->  layer_msg( $addArr['msg'],$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
		
	}
   
   function show_action(){
		$HrM	=	$this -> Model('hr');
		$nid	=	$HrM-> upHrInfo(array('id' => array('=', $_GET['id'])),array('post'=>array(''.$_GET['type'].''=>$_GET['rec'])));	
		echo $nid?1:0;die;
	
	}
}

?>