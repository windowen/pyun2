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
class admin_once_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
      $search_list[]	=	array("param"=>"status","name"=>'审核状态',"value"=>array("1"=>"已审核","3"=>"未审核","2"=>"已过期"));
      $search_list[]	=	array("param"=>"time","name"=>'发布时间',"value"=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
      $this->yunset("search_list",$search_list);
	}
	function index_action(){
  
		$this->set_search();
   
		$OnceM  		=  	$this -> MODEL('once');
   
		if($_GET['keyword']){
		  
			$keytype  	=   intval($_GET['type']);
			$keyword  	=   trim($_GET['keyword']);
			
			if($keytype   ==  2){
				
				$where['title'] 		= 	array('like',$keyword);
			}elseif($keytype  ==  3){
			  
				$where['phone'] 		= 	array('like',$keyword);
			}elseif($keytype  ==  4){
			  
				$where['linkman']		= 	array('like',$keyword);
			}elseif($keytype  ==  5){
			  
				$where['companyname']	= 	array('like',$keyword);
			}
			$urlarr['keytype']			=  $keytype;
			$urlarr['keyword']			=  $keyword;
			
		}
		if($_GET['status']){
        
			$status   =   intval($_GET['status']);
          
			if($status  ==  1){
            
				$where['status']  		= 	$status;
				$where['edate']   		= 	array(">",time());
				
			}elseif($status   ==   3){
            
				$where['status']  		= 	0;
				$where['edate']   		=  	array(">",time());
            
			}elseif($status   ==   2){
           
				$where['edate']   		= 	array("<",time());
				        
			}
      $urlarr['status'] 		= 	$status;
		}
       
		if($_GET['time']){
		    
		    if($_GET['time']=='1'){
		        
		        $where['ctime']  		=  	array('>=',strtotime('today'));
		    }else{
		        
		       $where['ctime']  		=  	array('>=',strtotime('-'.intval($_GET['time']).' day'));
		    }
		    $urlarr['time']				=	$_GET['time'];
		}
	
		$urlarr['page'] = '{{page}}';
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('once_job',$where,$pageurl,$_GET['page']);
		if($pages['total']  > 0){
		   //limit order 只有在列表查询时才需要
			if($_GET['order']){
			 
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']		=	$_GET['order'];
				$urlarr['t']			=	$_GET['t'];
			 
			}else{
			  
			  $where['orderby']			=	array('status,asc','id,desc');
			}
		}
		$where['limit']   				=   $pages['limit'];
	 
		$List   		=   $OnceM -> getOnceList($where);
	  
		$this -> yunset(array('rows'=>$List));
    
    	$cacheM					         =	$this -> MODEL('cache');
    
	  	$domain					         =	$cacheM	-> GetCache('domain');
	    
	 	$this -> yunset('Dname', $domain['Dname']);
    
		$this -> yuntpl(array('admin/admin_once'));
	}
  
  //批量延期
	function ctime_action(){
    
		$OnceM  	=  	$this -> MODEL('once');
		
		$return   	=   $OnceM -> setOnceCtime($_POST['onceids'],array('endtime'=>$_POST['endtime']));
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	}
  
	/*
	*获得职位详情
	*
	*/
	function show_action(){

		if($_GET['id']){

			$OnceM	=  	$this -> MODEL('once');

			$id		=   intval($_GET['id']);

			$show 	=	$OnceM ->  getOnceInfo(array('id'=>$id));

			$this->yunset("show",$show);

		}

		$this->yuntpl(array('admin/admin_once_show'));

	}
  
  //审核功能
	function status_action(){
    
		$OnceM  	=   $this ->  MODEL('once');

		$status		=	intval($_POST['status']);

		$return		=	$OnceM -> setOnceStatus($_POST['allid'],array('status'=>$status));

		if($return){

			echo $return['status'];die;

		}
		
	} 
  //店铺展示
	function ajax_action(){
		
		$onceM  =   $this -> MODEL('once');
		$id   	=   intval($_GET['id']);
		$info 	= 	$onceM -> getOnceInfo(array('id'=>$id));
		
		echo json_encode($info);
	}
  
  //查询修改信息
	function edit_action(){
		
		if($_GET['id']){
			$OnceM  =   $this->MODEL('once');
			$id   	=   intval($_GET['id']);
			$row 	=   $OnceM -> getOnceInfo(array('id'=>$id));
			$this ->  yunset('row',$row);
		}
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city'));
        $this->yunset($CacheList);
		$this->yuntpl(array('admin/admin_once_add'));
	}
  
  //保存修改信息
    function save_action(){
      
		$onceM  	= 	$this ->  MODEL("once");

		$edate		=	strtotime("+".(int)$_POST['edate']." days");
		
		$post		=   array(
			'title' 		=>  $_POST['title'],
			'companyname'	=>  $_POST['companyname'],
			'linkman'		=>	$_POST['linkman'],
			'phone' 		=>  $_POST['phone'],
			'provinceid' 	=>  $_POST['provinceid'],
			'cityid' 		=>  $_POST['cityid'],
			'three_cityid'	=>  $_POST['three_cityid'],
			'address' 		=>  $_POST['address'],
			'require' 		=>  $_POST['require'],
			'edate'			=>	$edate,
			'salary'		=>	$_POST['salary'],
			'password'		=>	$_POST['password'],
			'file'			=>	$_FILES['file'],
			'status' 		=>  1
		);

		$data		=	array(
			'id'     =>	 (int)$_POST['id'],
			'post'   =>	 $post,
			'type'   =>  'admin'
		);
		$return		= 	$onceM  ->  addOnceInfo($data,'admin');
		
		if($return['errcode']==9){
			$this->ACT_layer_msg($return['msg'],$return['errcode'],'index.php?m=admin_once');
		}else{
			$this->ACT_layer_msg($return['msg'],$return['errcode']);
		}
	}  
  
  //删除功能
	function del_action(){
		$this->check_token();

		$OnceM	=	$this -> Model('once');
		$id		=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
		
		$return	=	$OnceM -> delOnce($id);
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	
	function set_action(){
		$this->yuntpl(array('admin/admin_onceset'));
	}
	
	function onceset_action(){
    
		$configM	=   $this -> MODEL("config");
		
		foreach($_POST as $key=>$v){
      
	        $where['name']	=	$key;
			$config			=	$configM -> getNum($where);
          
			if($config==false){
              
                $data      	=  	array(
					'name'    =>  $key,
					'config'  =>  $v,
				);
				$msg   		=   $configM -> addInfo($data);
		  	}else{
				$where['name']	=   $key;
				$data			=   array(
					'config'    =>  $v,
				);
				$msg		=	$configM -> upInfo($where,$data);
			}
			
			if(!$msg){
				$this->web_config();
				$this->ACT_layer_msg('操作失败！',8);die;
			}
		}
		$this->web_config();
		$this->ACT_layer_msg('操作成功！',9);
	}

	function onceNum_action(){
		$MsgNum	= $this->MODEL('msgNum');
		echo $MsgNum->onceNum();
	}
   /**
	 * @desc  店铺招聘分站  分站设置
	 */
	function checksitedid_action(){
	    
	    $id		 =	trim($_POST['uid']);
	    $did		 =	intval($_POST['did']);
	    
	    if(empty($id)){
	        
	        $this -> ACT_layer_msg('参数不全请重试！', 8);
	    }
	    
	    $ids		 =	@explode(',',$_POST['uid']);
	    $id 		 =	pylode(',',$ids);
	    
	    if(empty($id)){
	        
	        $this -> ACT_layer_msg('请正确选择需分配用户！', 8);
	    }
	    
	    $siteM       =  $this->MODEL('site');
	    
	    $didData	 =    array('did' => $did);
	   
	    $siteM -> updDid(array('once_job'),array('id'=>array('in', $id)),$didData);
	    
	    $this->ACT_layer_msg('店铺招聘(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER'],2,1);
	    
	}
  
}
?>