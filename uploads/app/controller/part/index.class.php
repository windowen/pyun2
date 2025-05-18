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
class index_controller extends part_controller{
	function index_action(){
		$cacheM						=	$this->MODEL('cache');
		$cache						=	$cacheM->GetCache(array('city','part'));
		$this->yunset($cache);
		if($_GET['city']){//城市匹配
		    $city					=	explode("_",$_GET['city']);
		    $_GET['provinceid']		=	$city[0];
		    $_GET['cityid']			=	$city[1];
		    $_GET['three_cityid']	=	$city[2];
		}
		if($this->config['province']){
			$_GET['provinceid'] 	= 	$this->config['province'];
		}
		if($this->config['cityid']){
		    $_GET['cityid'] 		= 	$this->config['cityid'];
		}
		if($this->config['three_cityid']){
		    $_GET['three_cityid'] 	= 	$this->config['three_cityid'];
		}
		if($_GET['part_type']){
			$search[]				=	$cache['partclass_name'][$_GET['part_type']];
		}
		if($_GET['cycle']){
			$search[]				=	$cache['partclass_name'][$_GET['cycle']];
		}
		if($_GET['provinceid']){
			$search[]				=	$cache['city_name'][$_GET['provinceid']];
		}
		if($_GET['cityid']){
		    $search[]				=	$cache['city_name'][$_GET['cityid']];
		}
		if($_GET['three_cityid']){
		    $search[]				=	$cache['city_name'][$_GET['three_cityid']];
		}
		if($_GET['keyword']){
			$search[]				=	$_GET['keyword'];
		}
		if(!empty($search)){
			$data['seacrh_class']=@implode("-",$search);
			$this->data				=	$data;
		}
        //关键字显示
        include PLUS_PATH."keyword.cache.php";
        if(is_array($keyword)){
			foreach($keyword as $k=>$v){
				if($v['type']=='2' && $v['tuijian']=='1'){
					$partkeyword[]	=	$v;
				}
			}
        }
        $this->yunset("partkeyword",$partkeyword);
        //关键字显示end
		//设置用户类型
		if($this->uid &&  $this->usertype &&  $this->usertype != 2){
			$typename		=	array('1' => '求职者','3'=>'猎头账户');
			$this -> yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，账户属于'.$typename[$this->usertype].'。');
		}
		$this->seo("part_index");
		$this->yun_tpl(array('index'));
	}
	function show_action(){
			$partM		=	$this->MODEL("part");
		if($_GET['id']){
			$id			=	(int)$_GET['id'];
			$info		=	$partM->getInfo(array('id' => $id),array('cache'=>1,'com'=>1));
			
			$job		=	$info['info'];
			
			$this->yunset($info['cache']);
			
			if($job['uid']!=$this->uid && ($job['id']=='' || $job['state']==0 || $job['state']==3)){  
				$this->ACT_msg($this->config['sy_weburl'],"该兼职暂无法展示！"); 
			}
			
			$partM -> upInfo(array('hits'=>array('+',1)), array('id'=>$id));//更新浏览次数
			
			if($this->usertype==1){
				
				$apply		=	$partM->getPartSqInfo(array("uid"=>$this->uid,"jobid"=>$id));
				$this->yunset("apply",$apply);
				$collect	=	$partM->getPartCollectInfo(array("uid"=>$this->uid,"jobid"=>$id));
				$this->yunset("collect",$collect);
			}
			$this->yunset("job",$job);
			$this->yunset("ComInfo",$info['com']);
			
		}
		$data['part_name']	=	$job['name'];
		$this->data			=	$data;
		$this->seo("part_show");
		$this->yun_tpl(array('show'));
	}
	function partapply_action(){
		$partM 	=	$this->MODEL("part");
		
		$data	=	array(
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
			'jobid'		=>	(int)$_POST['jobid'],
			'port'		=>	'1'
		);
		$return	=	$partM -> addPartSq($data);
		echo json_encode($return);die;
	}
	function partcollect_action(){//收藏兼职
	
		$partM 	=	$this->MODEL("part");
		
		$data	=	array(
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
			'jobid'		=>	(int)$_POST['jobid'],
			'comid'		=>	(int)$_POST['comid'],
			
		);
		$return	=	$partM -> addPartCollect($data);
		echo json_encode($return);die;
	}
}
?>