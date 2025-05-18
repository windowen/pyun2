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
class index_controller extends ask_controller{
	function index_action(){
		//统计问答数量
		
		$M			=	$this -> MODEL('ask');
		
        $answer		=	$M -> getQuestionNum(array('is_recom'=>1,'state'=>1));
		
		$where['add_time']	=	array('>',strtotime("-30 day"));
		
		$where['groupby']	=	'uid';
		
		$where['orderby']	=	'num';
		
		$where['limit']		=	'6';
		
		$hotuser	=	$M -> getAnswersList($where,array("field"=>"uid,count(id) as num,sum(support) as support,nickname,pic"));
		 		
		$this->atnask($M);
		
		$this->hotclass();
		
		$this->seo('ask_index');
		
        $this->yunset("answer",$answer);
		
		$this->yunset("hotuser",$hotuser);
		
		$this->ask_tpl('index');
	}

	function getcomment_action(){
		$M		=	$this->MODEL('ask');
		
		$aid	=	(int)$_POST['aid'];
		
		$comment=	$M -> getCommentsList(array('aid'=>$aid,'orderby'=>'add_time,asc'));
		
		if(is_array($comment)){
			
			foreach($comment as $k=>$v){
				
				$comment[$k]['pic']			=	checkpic($v['pic'],$this->config['sy_friend_icon']);

				$comment[$k]['errorpic']	=	checkpic('',$this->config['sy_friend_icon']);
				
				$comment[$k]['nickname']	=	urlencode($v['nickname']);
				
				$comment[$k]['content']		=	urlencode($v['content']);
				
				$comment[$k]['date']		=	date("Y-m-d H:i",$v['add_time']);
				
				if($v['uid']==$this->uid){
					
					$comment[$k]['myself']='1';
				}
			}
			$comment_json = $this->JSON($comment);
			
			echo urldecode($comment_json);die;
		}
	}
	/**************************************************************
	 *
	 *  使用特定function对数组中所有元素做处理
	 *  @param  string  &$array     要处理的字符串
	 *  @param  string  $function   要执行的函数
	 *  @return boolean $apply_to_keys_also     是否也应用到key上
	 *  @access public
	 *
	 *************************************************************/
	function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
	  static $recursive_counter = 0;
	  if (++$recursive_counter > 1000) {
	    die('possible deep recursion attack');
	  }
	  foreach ($array as $key => $value) {
	    if (is_array($value)) {
	      $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
	    } else {
	      $array[$key] = $function($value);
	    }
	  
	    if ($apply_to_keys_also && is_string($key)) {
	      $new_key = $function($key);
	      if ($new_key != $key) {
	        $array[$new_key] = $array[$key];
	        unset($array[$key]);
	      }
	    }
	  }
	  $recursive_counter--;
	}

	/**************************************************************
	 *
	 *  将数组转换为JSON字符串（兼容中文）
	 *  @param  array   $array      要转换的数组
	 *  @return string      转换得到的json字符串
	 *  @access public
	 *
	 *************************************************************/
	function JSON($array) {
	    $this->arrayRecursive($array, 'urlencode', true);
	    $json = json_encode($array);
	    return urldecode($json);
	}
	function qrepost_action(){
		$M			=	$this -> MODEL('ask');
		
		$logM		=	$this -> MODEL('log');
		
		$reportM	=	$this -> MODEL('report');
			
        $userinfoM	=	$this -> MODEL('userinfo');
		
		$this->is_login();
		
		$eid	=	(int)$_POST['eid'];
		
		$reason	=	$_POST['reason'];
		
		$is_set	=	$reportM -> getReportOne(array('type'=>1,'r_type'=>1,'eid'=>$eid),array('field'=>'`p_uid`'));
		
		if(empty($is_set)){
			
            $question	=	$M -> getInfo($eid,array('field'=>'`uid`')); 
			
            $Userinfo	=	$userinfoM -> getInfo(array('uid'=>$question['uid']),array('field'=>'`username`'));
			
            $my_nickname=	$userinfoM -> getInfo(array('uid'=>$this->uid),array('field'=>'`username`'));
			
			$data['did']		=	$this->userdid;
			
			$data['p_uid']		=	$this->uid;
			
			$data['c_uid']		=	$question['uid'];
			
			$data['eid']		=	(int)$_POST['eid'];
			
			$data['usertype']	=	$this->usertype;
			
			$data['inputtime']	=	time();
			
			$data['username']	=	$my_nickname['username'];
			
			$data['r_name']		=	$Userinfo['username'];
			
			$data['r_reason']	=	$_POST['reason'];
			
			$data['type']		=	1;
			
			$data['r_type']		=	1;
			
			$new_id	=	$reportM -> addAskReport($data);
			
			if($new_id){
				
			    $logM -> addMemberLog($this->uid,$this->usertype,'举报问答问题',23,1);
				
				echo '1';
			}else{
				echo '0';
			}
		}else{
			if($is_set['p_uid']==$this->uid){
				echo '2';
			}else{
				echo '3';
			}
		}
	}
	function forcomment_action(){
		$M		=	$this -> MODEL('ask');
		
		$this->is_login();
		
		$data	=	array(
			'uid'		=>	$this->uid,
			
			'usertype'	=>	$this->usertype,
			
			'aid'		=>	(int)$_POST['aid'],
			
			'qid'		=>	(int)$_POST['qid'],
			
			'content'	=>	$_POST['content']
		);
		
		echo $M -> addReview($data);
		
	}
	function forsupport_action(){
		$M		=	$this -> MODEL('ask');
		
		$data	=	array(
			'uid'		=>	$this->uid,
			
			'usertype'	=>	$this->usertype,
			
			'aid'		=>	$_POST['aid']
		);
		
		echo $M -> upSupportInfo($data);die;
	}
	//问答回答问题
	function answer_action(){
		$M		=	$this -> MODEL('ask');
		
		if($this->uid==''||$this->username==''){
			
			$this->ACT_layer_msg( "请先登录！",8,$_SERVER['HTTP_REFERER']);
		}
		
		$data	=	array(
			
			'uid'		=>	$this->uid,
				
			'username'	=>	$this->username,
			
			'usertype'	=>	$this->usertype,
				
			'id'		=>	(int)$_POST['id'],
				
			'content'	=>	$_POST["content"]
		);
		$return	=	$M -> addAnswerInfo($data);
		
		if($return['id']){
			
			$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
			
		}else{
			
			$this->ACT_layer_msg($return['msg'],$return['errcode']);
			
		}
	}
	function addquestion_action(){
		if($this->uid==''||$this->username==''){
			
			header('Location:'.Url("login"));
		}
		$this->seo('ask_add_question');
		
		$this->ask_tpl('addquestion');
	}
	function qclass_action(){
		$CacheM	=	$this -> MODEL('cache');
		
        $info	=	$CacheM -> GetCache(array('ask'));
		
		$rows	=	array();
		
		$id		=	(int)$_POST['id'];
		
		foreach($info['ask_type'][$id] as $v){
			
			$rows[$v]=urlencode($info['ask_name'][$v]);
		}
		$rows	=	json_encode($rows);
		
		echo urldecode($rows);die;
	}
	function save_action(){
		$cid=(int)$_POST['cid'];
		
		if($_POST['title']&&$cid){
			
			$M=$this->MODEL('ask');
			
			if($this->uid==''){
				
				$this->ACT_layer_msg( "请先登录！", 8);
			}
			$data	=	array(
				'uid'		=>	$this->uid,
				
				'usertype'	=>	$this->usertype,

				'username'	=>	$this->username,
				
				'cid'		=>	(int)$_POST['cid'],
				
				'title'		=>	trim($_POST['title']),
				
				'authcode'	=>	$_POST['authcode'],
				
				'content'	=>	$_POST["content"]
			);
			$return=$M->addAskInfo($data);
			
			if($return['id']){
				
				$this->ACT_layer_msg($return['msg'],$return['errcode'],Url("ask",array("c"=>"index")));
				
			}else{
				
				$this->ACT_layer_msg($return['msg'],$return['errcode']);
				
			}
		}
	}

	function hotweek_action(){
		$M		=	$this -> MODEL('ask');

		$recom	=	$M -> getList(array('is_recom'=>'1','orderby'=>'add_time','limit'=>'8'), array('field'=>'uid,nickname,pic', 'utype' => 'hot'));
		$this->yunset('recom',$recom['recUser']);
		
		$this->atnask($M);
		
		$this->yunset("navtype",'hotweek');
		
		$this->seo('ask_hot_week');
		
		$this->ask_tpl('hotweek');
	}

	function savecode_action(){
	    if(strpos($this->config['code_web'],'职场提问')!==false){
	        session_start();
	        if ($this->config['code_kind']==1){
	            if(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
	                echo 1;die;
	                unset($_SESSION['authcode']);
	            }
	        }elseif ($this->config['code_kind']>2){
	             if (md5(strtolower($_POST['unlock']))!=$_SESSION['unlock'] || empty($_SESSION['unlock'])){
			            unset($_SESSION['unlock']);
			            echo 3;die;
			     }
	        }
	    }		
	}
}
?>