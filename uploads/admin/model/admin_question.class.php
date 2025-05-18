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
class admin_question_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
		
		$search_list[]	=	array ("param" => "is_recom", "name" => '是否推荐', "value" => array("1" => "已推荐", "2" => "未推荐"));
		
		$search_list[]	=	array ("param" => "status", "name" => '审核状态', "value" => array ("0" => "未审核", "1" => "已审核", "2" => "未通过"));
		
		$ad_time		=	array ('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
		
		$search_list[]	=	array("param" => "end", "name" => '提问时间', "value" => $ad_time);
		
		$this -> yunset("search_list",$search_list);
	
	}
	
	function index_action(){
		
		$this -> set_search();
		
		$AskM	=	$this -> MODEL('ask');
		
		if($_GET['id']){//举报页传递参数
			
			$where['id']	=	$_GET['id'];
			
			$urlarr['id']	=	$_GET['id'];
		
		}
		
		if($_GET['is_recom']){
			
			if($_GET['is_recom'] == 2){
				
				$where['is_recom']	=	0;
			
			}elseif($_GET['is_recom'] ==1 ){
				
				$where['is_recom']	=	1;
			
			}
			
			$urlarr['is_recom']	=	$_GET['is_recom'];
		
		}
		
		if($_GET['status']>-1){
			
			$where['state']		=	$_GET['status'];
			
			$urlarr['status']	=	$_GET['status'];
	
		}
		
		if($_GET['end']){
			
			if($_GET['end']==1){
				
				$where['add_time']	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			
			}else{
				
				$where['add_time']	=	array('>=',strtotime('-'.(int)$_GET['end'].'day'));
			
			}
			
			$urlarr['end']	=	$_GET['end'];
		
		}
		
		if(trim($_GET['keyword'])){
			
			if ($_GET['type']=='1'){
				
				$where['title']		=	array('like',trim($_GET['keyword']));
			
			}elseif($_GET['type'] == "2"){
				$userinfoM=$this->MODEL('userinfo');
				$members	=	$userinfoM -> getList(array('username'=>array('like',trim($_GET['keyword']))),array('field'=>'uid'));
				
				if($members){
					
					foreach($members as $v){
						
						$uids[]		=	$v['uid'];
					
					}
					
					$where['uid']   =	array('in',pylode(',', $uids));
				
				}
			
			}
			
			$urlarr["keyword"]	=	$_GET["keyword"];
			
			$urlarr["type"]		=	$_GET["type"];
		
		}
		
		$urlarr['page']="{{page}}";
		
		$pageurl=Url($_GET['m'],$urlarr,'admin');
		
		$pageM	=	$this  -> MODEL('page');
		
		$pages	=	$pageM -> pageList('question',$where,$pageurl,$_GET['page'],$limit);
		
		if($pages['total'] > 0){
			
			if($_GET['order']){
				
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
                
				$urlarr['order']		=	$_GET['order'];
                
				$urlarr['t']			=	$_GET['t'];
           
		    }else{   
                
				$where['orderby']		=	'id';        
            
			}    
            
			$where['limit']				=	$pages['limit'];         
            
			$question   =  $AskM -> getList($where, array('utype'=>'admin'));           

		}
		
		$this -> yunset("get_type", $_GET);
		
		$this -> yunset("question",$question);
		
		$this -> yuntpl(array('admin/admin_question_list'));
	
	}

	// 是否推荐
	function recommend_action(){
		
		$AskM	=	$this -> Model('ask');
		
		$nid	=	$AskM -> upRecommend(array('id'=>$_GET['id']),array(''.$_GET['type'].''=>$_GET['rec']));
		
		echo $nid?1:0;die;
	
	}
	
	function add_action(){
		
		$AskM	=	$this -> Model('ask');
		
		if($_GET['id']){
			
			$id		= 	intval($_GET['id']);
			
			$question_show		=		$AskM -> getInfo($id);
			
			$this -> yunset("question_show",$question_show);
		
		}
		
		$all_class	=	$AskM -> getQclassList(array('orderby'=>'sort,desc'),'id,name,pid');
		
		foreach($all_class as $k=>$v){
			
			if($v['id'] == $question_show['cid']){
				
				$pid	=	$v['pid'];
				
				$all_class[$k]['is_select']		=	'1';
			
			}
			
			if($v['pid'] == '0'){
				
				$p_class[]	=	$v;
			
			}
		
		}
		
		$this -> yunset("class_list",$p_class);
		
		$this -> yunset("pid",$pid);
		
		$s_class	=	$AskM -> getQclassList(array('pid'=>$pid,'orderby'=>'sort,desc'),'id,name,pid');
		
		$this -> yunset("s_class",$s_class);
		
		$this -> yuntpl(array('admin/admin_question_add'));
	
	}
	
	function get_class_action(){
		
		$AskM	=	$this -> Model('ask');
		
		if($_POST['pid']){
			
			$q_class	=	$AskM -> getQclassList(array('pid'=>$pid,'orderby'=>'sort,desc'),'id,name,pid');
			
			if($q_class[0]){
				
				$html	=	'';
				
				foreach($q_class as $v){
					
					$html.= 	'<option value="'.$v['id'].'">'.$v['name'].'</option>';
				
				}
				
				echo $html;
			
			}else{
				
				echo $html	=	"<div class=\"yun_admin_select_box_list\">该分类下暂无子类！</div>";
			
			}
		
		}
	
	}
	
	function save_action(){
		
		$AskM	=	$this -> Model('ask');
		
		if($_POST['update']){
			
			$_POST['content']	=	str_replace("&amp;","&",$_POST['content']);
			
			$nbid	=	$AskM -> upAskInfo(array('id'=>intval($_POST['id'])),$_POST);
			
			if($_POST['back_url']){
				
				$url	=	"index.php?m=report&type=1";
			
			}else{
				
				$url	=	"index.php?m=admin_question";
			
			}
			
			isset($nbid)?$this->ACT_layer_msg("问答(ID:".$_POST['id'].")更新成功！",9,$url,2,1):$this->ACT_layer_msg("更新失败！",8,$url);
		
		}
		
	}
	
	function del_action(){ 
		
		$this -> check_token();
		
		if($_GET['del']){ 
	    	
			$del	=	$_GET['del'];
	    	
			if($del){
			
				$this -> delquestion($del,array('utype'=>'admin')); 
	    		
				$this -> layer_msg( "问答(ID:".pylode(',',$del).")删除成功！",9,1,$_SERVER['HTTP_REFERER']);
	    	
			}else{
				
				$this -> layer_msg( "请选择您要删除的信息！",8,1,$_SERVER['HTTP_REFERER']);
	    	
			}
	   
	    }
	    
		if(isset($_GET['id'])){
			
			$this -> delquestion(array($_GET['id'])); 
			
			$this -> layer_msg('问答(ID:'.$_GET['id'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']);
		
		}else{
			
			$this -> layer_msg('非法操作！',8,0,$_SERVER['HTTP_REFERER']);
		
		}
	
	}
	
	function delquestion($ids = array()){
		
		$AskM	=	$this -> MODEL('ask');
		
		$id		=	pylode(',',$ids);
		
		$AskM  ->  delquestion($id,array('utype'=>'admin'));
		
		
	}
	
	//获取该提问的所有回答
	function getanswer_action(){
		
		$AskM	=	$this->MODEL('ask');
		
		$id		=	intval($_GET['id']);
		
		if($id){
			
			$ques	=	$AskM -> getInfo($id);
			
			$this -> yunset("ques",$ques);
		
		}
		
		if($_GET['aid']){
			
			$all_answer		=	$AskM -> getAnswersList(array( 'id' => $_GET['aid'], 'orderby' => 'add_time,desc' ));
		
		}else{
			
			$all_answer		=	$AskM -> getAnswersList(array( 'qid' => $id, 'orderby' => 'add_time,desc' ));
		
		}
		
		$this -> yunset("all_answer",$all_answer);
		
		$this -> yunset("qid",$_GET['id']);
		
		$this -> yuntpl(array('admin/admin_answer_list'));
	
	}
	
	//更新 回答
	function save_answer_action(){
		
		$AskM=$this->MODEL('ask');
		
		$url	=	"index.php?m=admin_question&c=getanswer&id=".$_POST['qid'];
		
		if($_POST['update']){
			
			$_POST['content']	=	str_replace("&amp;","&",$_POST['content']);
			
			$a_id	=	$AskM -> upAnswerInfo(array('id'=>intval($_POST['id'])),$_POST);
			
			isset($a_id)?$this->ACT_layer_msg("回答(ID:".$_POST['id'].")更新成功！",9,$url):$this->ACT_layer_msg("更新失败！",8,$url);
		
		}
	
	}
	function delanswer_action(){//删除用户回答

		$this->check_token();
		
		$AskM	=	$this->MODEL('ask');
		
		if($_GET['del']){
			
	    	$a_del=$_GET['del'];
			
	    	if($a_del){
				
				$result	=	$AskM -> delAnswer('',$a_del);
				
	    		$AskM -> upStatusInfo($_GET['qid'],'',array('answer_num'=>array('-',count($a_del))));
				
	    		$this->layer_msg($result['msg'],$result['errcode'],$result['layertype'],$_SERVER['HTTP_REFERER']);
	    	}else{
				$this->layer_msg( "请选择您要删除的信息！",8,1,$_SERVER['HTTP_REFERER']);
	    	}
	    }
		// 删除 回答
	    if(isset($_GET["id"])){
			$result	=	$AskM -> delAnswer('',$_GET['id']);
			
			$AskM -> upStatusInfo($_GET['qid'],'',array('answer_num'=>array('-','1')));
			
			$this->layer_msg($result['msg'],$result['errcode'],$result['layertype'],$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('非法操作！',3,0,$_SERVER['HTTP_REFERER']);
		}
	}
	//获得 针对某一提问回答的评论
	function getcomment_action(){
		
		$AskM	=	$this->MODEL('ask');
		
		if($_GET['aid']){
			
			$rows	=	$AskM -> getCommentsList(array('id'=>intval($_GET['aid']),'orderby'=>'id,desc'));
		
		}else{
			
			$rows	=	$AskM -> getCommentsList(array('aid'=>intval($_GET['id']),'orderby'=>'id,desc'));
		
		}
		
		$this -> yunset("answer_review",$rows);
		
		if($_GET['id']){
			
			$aid	=	intval($_GET['id']);
			
			$qid	=	$AskM -> getCommentBackQuestion($aid);
			
			$this -> yunset("qid",$qid);
		
		}
		
		$this -> yuntpl(array('admin/admin_answer_review'));
	
	}

	function save_review_action(){
		
		$AskM	=	$this -> MODEL('ask');
		
		$url	=	"index.php?m=admin_question&c=getcomment&id=".$_POST['aid'];
		
		if($_POST['update']){
			
			$r_id	=	$AskM -> upReview(array('id' => intval($_POST['id'])),$_POST);
			
			isset($r_id)?$this -> ACT_layer_msg("问答评论(ID:".$_POST['id'].")更新成功！",9,$url,2,1):$this->ACT_layer_msg("更新失败！",8,$url);
		
		}
	
	}
	
	//删除针对某回答的评论
	
	function delreview_action(){
	
		$this -> check_token();
		
		$AskM	=	$this -> MODEL('ask');
		
		$delID	=	$_GET['id']?intval($_GET['id']):$_GET['del'];
		
		$return	=	$AskM->delReview($delID);
		
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	
	}

	//修改审核状态
	function status_action(){
		
		extract($_POST);
		
		$AskM	=	$this->MODEL('ask');
		
		$id		=	@explode(",",$pid);
		
		if(is_array($id)){
			
			$data['state']		=	$_POST['status'];
			
			$data['lastupdate']	=	time();
			
			$nid	=	$AskM -> upStatusInfo($id, $where = array(), $data);
			
			$List	=	$AskM ->getList(array('id'=>array('in',pylode(',',$id))) , array('field'=>'`id`,`uid`,`title`'));
			
			/* 消息前缀 */		
			$tagName  				=	'问答';

			if(!empty($List)){

				foreach($List as $v){
					
					 $uids[]  =  $v['uid'];
								
					/* 处理审核信息 */
					if ($_POST['status'] == 2){
						
						$statusInfo  =  $tagName.':<a href="answertpl,'.$v['id'].'">'.$v['title'].'</a>,审核未通过';
						
						if($_POST['statusbody']){
							
							$statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
							
						}
						
						$msg[$v['uid']][]  =  $statusInfo;
						
					}elseif($_POST['status'] == 1){
						
						$msg[$v['uid']][]  =  $tagName.':<a href="answertpl,'.$v['id'].'">'.$v['title'].'</a>,已审核通过';
						
					}
				}
				//发送系统通知
				if(!empty($_POST['status'])){
					$sysmsgM	=	$this->MODEL('sysmsg');
				
					$sysmsgM -> addInfo(array('uid'=>$uids,'content'=>$msg));
				}

			}
			
			$nid?$this -> ACT_layer_msg("问答审核(ID:".$pid.")设置成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this -> ACT_layer_msg("设置失败！",8,$_SERVER['HTTP_REFERER']);    
		
		}else{
			
			$this -> ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		
		}
	
	}
	
	//问答设置
	function config_action(){
		
		$configM	=	$this->MODEL('config');
		
		if($_POST['config']){
			
			unset($_POST["config"]);
			
			foreach($_POST as $key => $v){
				
				$config		=	$configM -> getNum(array('name' => $key));
				
				if($config == false){
					
					$configM -> addInfo(array('name' => $key,'config' => $v));
				
				}else{
					
					$configM -> upInfo(array('name' => $key),array('config' => $v));
				
				}
			
			}
			
			
			$this -> web_config();
			
			$this -> ACT_layer_msg("配置修改成功！",9,1,2,1);
		
		}
		
		$this -> yuntpl(array('admin/admin_question_config'));
	
	}
	
	 
}
?>