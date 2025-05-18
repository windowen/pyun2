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
class admin_evaluate_controller extends adminCommon{
	//测评试卷列表
	function index_action(){
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		$group		=	$EvaluateM->getList(array('keyid'=>'0'));
		
		if($group){
			
			foreach($group as $val){
				
				$arr[$val['id']]	=	$val['name'];
			
			}
			
			$search_list[]	=	array("param"=>"keyid","name"=>'试卷类别',"value"=>$arr);
			
			$this -> yunset("arr",$arr);
			
			$this -> yunset("search_list",$search_list);
		
		}
		
		$where['keyid']		=	array('<>',0);
		
		if($_GET['evaluate_search']&&trim($_GET['keyword'])){
			
			$where['name']				=	array('like',trim($_GET['keyword']));
			
			$urlarr['evaluate_search']	=	$_GET['evaluate_search'];
			
			$urlarr['keyword']			=	trim($_GET['keyword']);
		
		}
		
		if((int)$_GET['keyid']){
			
			$where['keyid']		=	intval($_GET['keyid']);
			
			$urlarr['keyid']	=	intval($_GET['keyid']);
		
		}
		
		$urlarr['page']		=	"{{page}}";
		
		$pageurl			=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM				=	$this -> MODEL('page');
		
		$pages				=	$pageM -> pageList('evaluate_group',$where,$pageurl,$_GET['page'],$limit);
		
		if($pages['total'] > 0){	
			
			if($_GET['order']){	
				
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				
				$urlarr['order']		=	$_GET['order'];  
				
				$urlarr['t']			=	$_GET['t'];          
		    
			}else{                  
				
				$where['orderby']		=	'id';                  
			
			}             
			
			$where['limit']				=	$pages['limit'];                  
			
			$rows	=	$EvaluateM -> getList($where);           
		
		}
		
		$this -> yunset("rows",$rows);  
		
		$this -> yunset("get_type", $_GET);  
		
		$this -> yuntpl(array('admin/admin_evaluate_list'));  
	
	}
	
	//添加修改测评试卷
	function examup_action(){
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		$group_all	=	$EvaluateM -> getList(array('keyid'=>0));
		
		$this -> yunset("group_all",$group_all);

		$fullscore	=	0;

		if($_GET['id']){
		
			$where['id']		=	(int)$_GET['id'];
			
			$info				=	$EvaluateM -> getInfo($where,array('pic'=>1));
			
			$info['fromscore'] 	= 	mb_unserialize($info['fromscore']);
			
			$info['toscore'] 	=	mb_unserialize($info['toscore']);
			
			$info['comment'] 	= 	mb_unserialize($info['comment']);
			
			$info['describe']	= 	explode(",",$info['describe']);
			
			// 问题、选项、试卷总分
			$ask				=	$EvaluateM -> getEvaluateList(array('gid'=>(int)$_GET['id'],'orderby'=>'id,asc'));
			
			
			
			foreach($ask as $key => $val){ 
				
				$tempscore	=	intval($ask[$key]['score'][0]); 
				
				foreach($ask[$key]['score'] as $v){
					
					if($v>$tempscore){
						
						$tempscore	=	$v;
					
					}
				}
				
				$fullscore+=$tempscore;
			
			}  
		
			$this -> yunset("info",$info);
			
			$this -> yunset(array("ask"=>$ask, 'anum' => count($ask)));
			
			if($_GET['type'] == '1'){

				$this -> yunset('type', '1');
			}
		}

		$this -> yunset("fullscore",$fullscore);
		$this -> yuntpl(array('admin/admin_evaluate_examup'));
	}
	
	//保存添加修改测评试卷
	function examupsave_action(){

		$EvaluateM	=	$this->MODEL('evaluate');
		
		$examid 	= 	(int)$_POST['examid'];
		
		if(trim($_POST['examtitle'])==''){
		    
			$this -> ACT_layer_msg("请填写测评名称！",8);
		}

		if($_FILES['file']['tmp_name']){

			$upArr    =  array(
				'file'  	=>  $_FILES['file'],
				'dir'   	=>  'evaluate'
			);
			//缩率图参数

			$uploadM  =  $this->MODEL('upload');

			$pic      =  $uploadM->newUpload($upArr);
			
			if (!empty($pic['msg'])){

				$this->ACT_layer_msg($pic['msg'],8);

			}elseif (!empty($pic['picurl'])){

				$pictures 	=  	$pic['picurl'];
			}

		}

		$data	=	array(
		
			'examtitle'		=>	$_POST['examtitle'],
			'selectgroup'	=>	$_POST['selectgroup'],
			'sort'			=>	$_POST['sort'],
			'hot'			=>	$_POST['hot'],
			'description'	=>	$_POST['description'],
			'fromscore'		=>	$_POST['fromscore'],
			'toscore'		=>	$_POST['toscore'],
			'comment'		=>	$_POST['comment']
		);

		if(isset($pictures)){

			$data['pic']	=	$pictures;
		}
		 
		if($examid){
			
			$scale	=	$EvaluateM -> upInfo(array('id'=>intval($_POST['examid'])),$data);

			$nid	=	$examid;
			
		}else{
			
			$scale	=	$EvaluateM -> addInfo($data);

			$nid	=	$scale;
		}
		
		$scale?$this->ACT_layer_msg("操作成功！",9,"index.php?m=admin_evaluate&c=examup&id=".$nid."&type=1"):$this->ACT_layer_msg("操作失败！",8,$_SERVER['HTTP_REFERER']);
	
	}

	//添加,更新问题
	function ajaxsave_action(){
		 
		$EvaluateM	=	$this->MODEL('evaluate');
		
		$questid 	= 	$_POST['questid']; 
		
		$status 	= 	$_POST['status'];
		
		if($status=="up"){
			
			$scale	=	$EvaluateM->upEvaQuestion(array('id'=>$questid),$_POST);
			
			if($scale){
				
				$this -> MODEL('log')->addAdminLog("测评问题(ID:".$questid.")修改成功");
				
				echo '1';die;
			}

		}elseif($status=="new"){
			
			$option		=	$_POST['option'];
			
			for($i=0; $i<count($option); $i++){
				
				$option[$i]		=	$option[$i];
			}
			
			$option		=	serialize($option);
			
			$_POST['option']	=	$option;

			$scale	=	$EvaluateM -> addEvaQuestion($_POST);
			
			if($scale){
				
				$this -> MODEL('log') -> addAdminLog("测评问题(ID:".$scale.")添加成功"); 
				
				echo '1';die;
			}
		}
	}
	
	//删除测评试卷
	function delevaluate_action(){
		
		$this -> check_token();
		
		$EvaluateM	=	$this -> MODEL('evaluate'); 
		
		if($_GET['del']){
			
			$del	=	$_GET['del'];
			
			if(is_array($del)){
				
				$this -> delevagroup($del); 
				
				$this -> layer_msg('测评试卷(ID:'.@implode(',',$del).')删除成功！',9,1,$_SERVER['HTTP_REFERER']);
			
			}else{
				
				$this -> layer_msg('请选择您要删除的测评试卷！',8,1,$_SERVER['HTTP_REFERER']);
			
			}
		
		}
		
		if(isset($_GET['id'])){
			
			$id		=	intval($_GET['id']);
			
			$result	=	$EvaluateM -> delevaluate($id);
			
			isset($result)?$this->layer_msg('测评试卷(ID:'.$_GET['id'].')删除成功！',9):$this->layer_msg('删除失败！',8);
		
		}else{
			
			$this->ACT_layer_msg( "非法操作！",8,$_SERVER['HTTP_REFERER']);
		
		}
	
	}
	
	//删除问题
	function delquestion_action(){
		
		$this -> check_token();
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		if($_GET['qid']){
			
			$qid	=	$_GET['qid'];
			
			$scale 	= 	$EvaluateM->delEvaQuestion($qid);
			
			isset($scale)?$this->layer_msg('测评问题(ID:'.$qid.')删除成功！',9):$this->layer_msg('删除失败！',8);
		
		}
	
	}
	
	function delevagroup($ids){
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		$id			=	pylode(',',$ids);
		
		$EvaluateM  ->  delEvaluateGroups($id);
	
	}
	   
	//测评类别列表
	function group_action(){
		
		$EvaluateM	=	$this->MODEL('evaluate');              
		
		$where=array();
		
		$evaluate_group   =  $EvaluateM -> getList($where,array('orderby'=>"sort,desc"));  
		
		if(is_array($evaluate_group)){
			
			$rootid	=	array();
			
			foreach($evaluate_group as $key=>$value){
				
				if($value['keyid']!=0){
					
					$rootid[$value['keyid']][]  = $value['id'];
				
				}else{
					
					$rootid[$value['id']][] 	= $value['id'];
				
				}
			
			}
		
		}
		
		if(is_array($rootid)&&$rootid){
			
			foreach($rootid as $k=>$v){
				
				$root_arr 	= 	@implode(",",$v);
				
				$count	=	$EvaluateM -> getEvaluateGroupNum(array('keyid'=>$k),array('keyid'=>array('in',$root_arr,'OR')));
				
				foreach($evaluate_group as $key=>$value){
					
					if($value['id']==$k){
						
						$evaluate_group[$key]['count'] = $count;
						
						$evaluate_group[$key]['roots'] = count($v)-1;
					
					}
				
				}
			
			}
		
		}
		
		$this->yunset("evaluate_group",$evaluate_group);
		
		$this->yuntpl(array('admin/admin_evaluate_group'));
	
	}
	
	//添加测评类别管理
	function addgroup_action(){
		
		$EvaluateM	=	$this->MODEL('evaluate'); 
		
		if($_POST['sub']){
			
			if($_POST['classname']!=""){
				
				$where['name']	=	$_POST['classname'];
				
				$row	=	$EvaluateM -> getInfo($where);
				
				if(!is_array($row)){
					
					$nid	=	$EvaluateM -> addEvaluateGroupInfo($_POST);
					
					$nid?$this->ACT_layer_msg( "测评类别添加成功！",9,"index.php?m=admin_evaluate&c=group",2,1):$this->ACT_layer_msg( "测评类别添加失败！",8,"index.php?m=admin_evaluate&c=group",2,1);
				
				}else{
					
					$this->ACT_layer_msg( "已经存在此类别！",8,$_SERVER['HTTP_REFERER']);
				
				}
			
			}else{
				
				$this->ACT_layer_msg( "请正确填写你的类别！",8,$_SERVER['HTTP_REFERER']);
			
			}
		
		}
	
	}
	
	//点击修改 类别名称、排序
	function ajax_action(){
		
		$EvaluateM	=	$this -> MODEL('evaluate'); 
		
		$whereData['id']	=	$_POST['id'];
		
		$addData['name']	=	$_POST['name'];
		
		$addData['sort']	=	$_POST['sort'];
		
		$EvaluateM -> upEvaluateGroupInfo($addData,$whereData);
	
	}
	
	//删除测评类别
	function delgroup_action(){
		
		$this -> check_token();
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		// 删除分组   删除该分组下的所有试卷。
		// 该分组下所有的试卷的id
		$id	=	intval($_GET['id']);
		
		if(isset($_GET['id'])) {	
			
			$titleid	=	$EvaluateM -> getList(array('keyid'=>$id));
			
			$ids=array();
			
			foreach($titleid as $val){
				
				$ids[]	=	$val['id'];
			
			}
			
			$this -> delevagroup($ids);
			
			$result	=	$EvaluateM->delEvaluateGroup($id);
			
			isset($result)?$this->layer_msg('测评类别(ID:'.$_GET['id'].')删除成功！',9):$this->layer_msg('删除失败！',8);
		
		}
	
	}
	
	//测评留言管理列表
	function message_action(){
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		if(trim($_GET['keyword'])!=""){
			
			if($_GET['type']=='1'){
				
				$info	=	$EvaluateM -> getMemberList(array('username'=>array('like',trim($_GET['keyword']))));
				
				if($info&&is_array($info)){
					
					foreach($info as $v){
						
						$uids[]		=	$v['uid'];
					
					}
				
				}
				
				$where['uid']		=	array('in',pylode(',',$uids));
			
			}else{
				
				$where['message']	=	array('like',trim($_GET['keyword']));
			
			}
			
			$urlarr['type']		=	$_GET['type'];
			
			$urlarr['keyword']	=	$_GET['keyword'];
		
		}
		
		$urlarr['c']	=	$_GET['c'];
		
		$urlarr['page']	=	"{{page}}";
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM	=	$this -> MODEL('page');
		
		$pages	=	$pageM -> pageList('evaluate_leave_message',$where,$pageurl,$_GET['page'],$limit);
		
		if($pages['total'] > 0){	             
			
			$where['orderby']		=	'id';                            
			
			$where['limit']			=	$pages['limit'];                  
			
			$rows	=	$EvaluateM -> getMessageList($where);           
		
		}
		
		$this -> yunset("rows",$rows); 
		
		$this -> yuntpl(array('admin/admin_evaluate_message'));
	
	}
	
	//留言内容显示更多
	function show_action(){
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		if($_POST['id']){
			
			$where['id']	=	intval($_POST['id']);
			
			$info	=	$EvaluateM -> getMessageInfo($where,array('field'=>'message'));
			
			$data['message']	=	$info['message'];
			
			echo json_encode($data);die;
		
		}
	
	}
	
	//删除测评留言管理
	function delmsg_action(){
		
		$this -> check_token();
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		$delID	=	$_GET['id'] ? intval($_GET['id']) : $_GET['del'];
		
		$return	=	$EvaluateM -> delMessage($delID);
		
		$this -> layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	}
	
	//测评用户记录列表
	function record_action(){
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		$group	=	$EvaluateM -> getList(array('keyid'=>array('=',0)));
		
		if($group){
			
			foreach($group as $val){
				
				$arr[$val['id']]	=	$val['name'];
			
			}
			
			$this -> yunset("arr",$arr);
		
		}
		
		if(trim($_GET['keyword'])!=""){
			
			if($_GET['type']=='1' || $_GET['type']==''){
				
				$info	=	$EvaluateM -> getMemberList(array('username'=>array('like',trim($_GET['keyword']))));
				
				if($info&&is_array($info)){
					
					foreach($info as $v){
						
						$uids[]		=	$v['uid'];
					
					}
				
				}
				
				$where['uid']	=	array('in',pylode(',',$uids));
			
			}else{
				
				$exameInfo	=	$EvaluateM -> getList(array('name'=>array('like',trim($_GET['keyword']))));
				
				if($exameInfo&&is_array($exameInfo)){
					
					foreach($exameInfo as $v){
						
						$examids[]	=	$v['id'];
					
					}
				
				}
				
				$where['examid']	=	array('in',pylode(',',$examids));
			
			}
			
			$urlarr['type']		=	$_GET['type'];
			
			$urlarr['keyword']	=	$_GET['keyword'];
		
		}
		
		$urlarr['c']	=	$_GET['c'];
		
		$urlarr['page']	=	"{{page}}";
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM			=	$this -> MODEL('page');
		
		$pages			=	$pageM -> pageList('evaluate_log',$where,$pageurl,$_GET['page'],$limit);
		
		if($pages['total'] > 0){	
			
			if($_GET['order']){	
				
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				
				$urlarr['order']		=	$_GET['order'];  
				
				$urlarr['t']			=	$_GET['t'];          
		    
			}else{                  
				
				$where['orderby']		=	'id';                  
			
			}             
			
			$where['limit']				=	$pages['limit'];                  
			
			$rows   =	$EvaluateM -> getEvaluateLogList($where);           
		
		}
		
		$this -> yunset("rows",$rows); 
		
		$this -> yunset("get_type", $_GET);
		
		$this -> yuntpl(array('admin/admin_evaluate_record'));
	
	}
	
	//删除测评用户记录
	function delevaluatelog_action(){
		
		$this -> check_token();
		
		$EvaluateM	=	$this -> MODEL('evaluate');
		
		$delID		=	$_GET['id'] ? intval($_GET['id']) : $_GET['del'];
		
		$return		=	$EvaluateM -> delEvaluateLog($delID);
		
		$this -> layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	
	}
	 
}
?>