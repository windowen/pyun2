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
class resumeshare_controller extends resume_controller{
	/**
	 * 简历详情
	 * 分享简历
	 * 2019-06-15
	 */
	function index_action(){
		if(empty($this -> uid)){
			header('Location: '.Url('login'));die;
		}
		$resumeM			=	$this -> MODEL('resume');
		$eid				=	intval($_GET['id']);
		if(!empty($eid)){
			$user			=	$resumeM -> getInfoByEid(array(
				'eid' 		=>	$eid,
				'uid' 		=>	$this -> uid,
				'usertype' 	=>	$this -> usertype
			));

			$JobM			=	$this -> MODEL('job');
			
			$time			=	strtotime("-14 day");
			$allnum			=	$JobM -> getYqmsNum(array(
				'uid' 		=>	$user['uid'],
				'eid' 		=>	$eid,
				'datetime' 	=>	array('>', $time)
			));
			$replynum		=	$JobM -> getYqmsNum(array(
				'uid' 		=>	$user['uid'],
				'eid' 		=>	$eid,
				'datetime' 	=>	array('>', $time),
				'is_browse' =>	array('>', 1)
			));

			$pre			=	round(($replynum/$allnum)*100);
			
			$cData['uid']				=	$this->uid;
			$cData['usertype']			=	$this->usertype;
			$cData['eid']				=	$eid;
			$cData['ruid']				=	$user['uid'];
			$resumeCkeck				=	$resumeM->openResumeCheck($cData);
			
			$this -> yunset('resumeCkeck',$resumeCkeck);
			$this -> yunset('pre', $pre);
			$this -> yunset('Info', $user);
			$data['resume_username']	=	$user['username_n'];//简历人姓名
			$data['resume_city']		=	$user['city_one'].",".$user['city_two'];//城市
			$data['resume_job']			=	$user['hy'];//行业
			$this -> data = $data;
		}

		$_POST				=	$this -> post_trim($_POST);
		if(!empty($_POST)){

			$_POST['id']	=	intval($_POST['id']);
			//参数判断
			if(empty($_POST['femail']) || empty($_POST['authcode'])){
				echo '请完整填写信息！';die;
			}
			session_start();
			if(md5(strtolower($_POST['authcode'])) != $_SESSION['authcode'] || empty($_SESSION['authcode'])){
				echo '验证码不正确！';die;
			}
			unset($_SESSION['authcode']);
			if($this->config['sy_email_set'] != 1){
				echo '网站邮件服务器不可用!';die;
			}
			if(CheckRegEmail(trim($_POST['femail'])) == false){
				echo '邮箱格式错误！';die;
			}

			$recomM						=	$this -> MODEL('recommend');
			//判断当天推荐职位、简历数是否超过最大次数
			if(isset($this->config['sy_recommend_day_num'])	&& $this->config['sy_recommend_day_num'] > 0){
				$num					=	$recomM -> getRecommendNum(array('uid'=>$this->uid));
				if($num >= $this->config['sy_recommend_day_num']){
					echo "每天最多推荐{$this->config['sy_recommend_day_num']}次职位/简历！";
					exit;
				}
			}

			//判断上一次推荐的时间间隔
			if(isset($this->config['sy_recommend_interval']) && $this->config['sy_recommend_interval'] > 0){
				$row 					=	$recomM -> getInfo(array('uid' => $this -> uid, 'orderby' => 'addtime'));
				if(!empty($row['addtime']) && (time() - $row['addtime']) < $this->config['sy_recommend_interval']){
					$needTime = $this->config['sy_recommend_interval'] - (time() - $row['addtime']);
					if($needTime > 60){
						$h 				=	floor(($needTime % (3600*24)) / 3600);
						$m				=	floor((($needTime % (3600*24)) % 3600) / 60);
						$s				=	floor((($needTime % (3600*24)) % 3600 % 60));
						if($h != 0){
							$needTime	=	$h.'时';
						}else if($m != 0){
							$needTime	=	$m.'分';
						} 
					}else{
						$needTime		=	$needTime.'秒';
					}

					$recs 				=	$this->config['sy_recommend_interval'];
					if($recs>60){
						$h 				=	floor(($recs % (3600*24)) / 3600);
						$m				=	floor((($recs % (3600*24)) % 3600) / 60);
						$s				= 	floor((($recs % (3600*24)) % 3600 % 60));
						if($h != 0){
							$recs		=	$h.'时';
						}else if($m != 0){
							$recs		=	$m.'分';
						} 
					}else{
						$recs			=	$recs.'秒';
					}
					echo "推荐职位、简历间隔不得少于{$recs}，请{$needTime}后再推荐";
					exit;
				}
			}
			$Info       =   $resumeM -> getInfoByEid(array('eid' => $_POST['id']));
			
			global $phpyun;
			$phpyun -> assign('Info',$Info);
			
			$contents	=	$phpyun -> fetch(TPL_PATH.'resume/sendresume.htm',time());
			
			$userinfoM					=	$this -> MODEL('userinfo');
			//查询用户的昵称（个人用户 resume表，企业用户company表）
			$nickname					=	'';
			$whereData					=	array('uid' => $this -> uid);
		    if($this->usertype == 3){
				$fieData				=	array('usertype' => 3, 'field' => '`realname`');
				$row 					= 	$userinfoM -> getUserInfo($whereData, $fieData);
		    	if($row['realname']){
		    		$nickname			=	$row['realname'];
		    	}
		    }else{
				$fieData				=	array('usertype' => $this->usertype, 'field' => '`name`');
				$row 					= 	$userinfoM -> getUserInfo($whereData, $fieData);
		    	if($row['name']){
		    		$nickname			=	$row['name'];
		    	}
		    }
			$myemail					=	$this -> stringfilter($nickname);

			//发送邮件并记录入库
			$emailData['email']			=	$_POST['femail'];
			$emailData['subject']		=	'您的好友'.$myemail.'向您推荐了简历！';
			$emailData['content'] 		=	$contents;
			//入库字段
			$emailData['uid']			=	'';
			$emailData['name']			=	$_POST['femail'];
			$emailData['cuid']			=	$this -> uid;
			$emailData['cname']			=	$myemail;
      		$notice						=	$this -> MODEL('notice');
			$sendid						=	$notice -> sendEmail($emailData);

			if($sendid['status'] != -1){
				echo 1;
			}else{
				echo '邮件发送错误 原因：'. $sendid['msg'];die;
			}

			//保存推荐记录到数据表recommend表
			$recommend 					=	array(
				'uid' 					=>	$this->uid,
				'rec_type'				=>	2,
				'rec_id'				=>	$_POST['id'],
				'email'					=>	$_POST['femail'],
				'addtime'				=>	time()
			);
			$result						=	$recomM -> addRecommendInfo($recommend);

			die;
		}
		
		$this -> seo('resume_share');
		$this -> yuntpl(array('resume/resume_share'));
    }
}
?>