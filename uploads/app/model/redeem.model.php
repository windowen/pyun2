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
class redeem_model extends model
{
	/**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type=''); 
    }
	//查询单条商品
    function getInfo($where=array(),$data=array()){
		
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
		$info	=	$this -> select_once('reward',$where,$data['field']);
		if(!empty($info)){
			if($info['pic']){
				$info['pic']	= checkpic($info['pic']);
			}
			if($info['content']){
				$content=htmlspecialchars_decode($info['content']);
				$info['content_n'] = $content;
				preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$content,$res);
				if(!empty($res[3])){
					foreach($res[3] as $v){
						if(strpos($v,'http:')===false && strpos($v,'https:')===false){
							$ossv  			   = checkpic($v);
							$info['content_n'] = str_replace($v,$ossv,$content);
						}
					}
				}
			}
		}
		return $info;
    }

	//查询商品列表
    function getList($whereData,$data=array()){
		
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
		$List			=	$this->select_all("reward",$whereData,$data['field']);
		
		if(!empty( $List )){
			
			foreach($List as $key=>$val){
				$nids[] 		= 	$val['nid'];
				$tnids[]	 	= 	$val['tnid'];
			}
			
			$classWhere['id']	=	array('>',0);
			$class				=	$this->select_all("redeem_class",$classWhere);
			
			$classname='';
			foreach($List as $k=>$v){
        		foreach($class as $val){
        			if($v['nid']==$val['id']){
        				$classname			=	$val['name'];
        			}
					
					if($v['tnid']==$val['id']){
						$classname			=	$List[$k]['classname'].'-'.$val['name'];
					}
					
					$List[$k]['classname']	=	$classname;
        		}
        	}
		}
		
		return $List;
	}
	
	function upInfo($data = array(),$whereData)
	{
		if(!empty($whereData)){
			$nid	=	$this -> update_once('reward',$data,$whereData);
		}
		return $nid;
	}

	function delReward($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit	=	'limit 1';
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit	=	'';
		}
		
		$result	=	$this	->	delete_all('reward',$whereData,$limit);
		
		return	$result;
		
	}

	function addInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('reward',$setData);
			
		}

		return $nid;
	}
	function getChangeList($whereData,$data=array()){
		$ListNew		=	array();
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('change',$whereData,$data['field']);
		
		if(!empty( $List )){
		    
		    $gid  =   array();
		    
			foreach($List as $key=>$val){
			
			    $gid[]   =   $val['gid'];
			}
			
			require_once ('redeem.model.php');
			$redeemM     =   new redeem_model($this->db, $this->def);
			$gift        =   $redeemM->getList(array('id'=>array('in',pylode(',', $gid))),array('field'=>'id,pic'));
			
			$dh = $sh = $wtg =0;
			foreach($List as $key=>$val){
        foreach ($gift as $v){
						if($val['gid']==$v['id']){
              				$List[$key]['pic']	=	checkpic($v['pic'],$this->config['sy_imgsc_mr']);
						}
					}
				
				if($data['utype']=='wap'){
				    
					
					if($val['status']==0){
						$sh   =   $sh + 1;
					}
					
					if($val['status']==2){
						$wtg  =   $wtg + 1;
					}
					if($val['status']==1){
						$dh   =   $dh + 1;
					}		
				}
			} 
			$ListNew['dh']		=	$dh;
			$ListNew['wtg']		=	$wtg;
			$ListNew['sh']		=	$sh;
			$ListNew['list']	=	$List;
			
		}

		return	$ListNew;
	}
	
	function getChangeInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('change',$whereData,$data['field']);
		}

		return $List;
	
	}
	function AddChange($data = array()){
		$num	=	(int)$data['num'];
		$id		=	(int)$data['id'];
		
		$info		=	$this->select_once('member',array("uid"=>$data['uid']),'`password`,`salt`');
		
		require_once ('statis.model.php');
		$statisM 	= 	new statis_model($this->db, $this->def);
		$statis		=	$statisM->getInfo($data['uid'],array("usertype"=>$data['usertype'],"field"=>"`integral`"));
		
		$gift		=	$this->getInfo(array("id"=>(int)$data['id']));
		
		$nums		=	$this->select_num('change',array("gid"=>$gift['id'],"uid"=>$data['uid']));
		
		$integral	=	$gift['integral']*$num;
		
		if(!$data['uid'] && !$data['username']){
			$return['msg']		=	'您还没有登录，请先登录！';
			$return['errcode']		=	'8';
		}elseif(!$data['linkman'] || !$data['linktel'] ){
			$return['msg']		=	'收件人和手机号码不能为空！';
			$return['errcode']		=	'8';
		}elseif($data['linktel']&&CheckMoblie($data['linktel'])==false){
			$return['msg']		=	'手机格式错误！';
			$return['errcode']		=	'8';
		}elseif(!$data['password']){
			$return['msg']		=	'密码不能为空！';
			$return['errcode']		=	'8';
		}elseif(!passCheck($data['password'],$info['salt'],$info['password'])){
			$return['msg']		=	'密码不正确！';
			$return['errcode']		=	'8';
		}elseif($num<1){
			$return['msg']		=	'请填写正确的数量！';
			$return['errcode']		=	'8';
		}elseif($num>$gift['stock']){
			$return['msg']		=	'已超出库存数量！';
			$return['errcode']		=	'8';
		}elseif($gift['restriction']!='0' && $nums+$num>$gift['restriction']){
			$return['msg']		=	'已超出限购数量！';
			$return['errcode']		=	'8';
		}elseif($statis['integral']<$integral){
			$return['msg']		=	'您的'.$this->config['integral_pricename'].'不足！';
			$return['errcode']		=	'8';
		}else{
			require_once ('integral.model.php');
			$integralM = new integral_model($this->db, $this->def);
			//积分操作记录
			$integralM->company_invtal($data['uid'],$data['usertype'],$integral,false,"".$this->config['integral_pricename']."兑换",true,2,'integral',24);
      if($data['bodyt']){
         $data['body']=$data['bodyt'];
      }else{
        $data['body']='收货地址：'.$data['provinceid'].' '.$data['cityid'].' '.$data['three_cityid'];
        if($data['address']){
          $data['body'].=' '.$data['address'];
        }
        if($data['other']){
          $data['body'].=' 用户留言：'.$data['other'];
        }
      }


			$post=array(
				'uid'		=>	$data['uid'],
				'username'	=>	$data['username'],
				'usertype'	=>	$data['usertype'],
				'name'		=>	$gift['name'],
				'gid'		=>	$gift['id'],
				'linkman'	=>	$data['linkman'],
				'linktel'	=>	$data['linktel'],
				'body'		=>	$data['body'],
				'integral'	=>	$integral,
				'num'		=>	$num,
				'ctime'		=>	time()
			);
			$this->insert_into('change',$post);
			
			$this->update_once('reward',array('num'=>array('+',$num),'stock'=>array('-',$num)),array("id"=>$data['id']));
			
			$this -> addMemberLog($data['uid'], $data['usertype'],$this->config['integral_pricename']."兑换，商品ID：".$gift['id'],17,1);
			$return['msg']='兑换成功，请等待管理员审核！';
			$return['errcode']='9';
			if($data['utype']=='pc'){
				$return['url']=Url('redeem',array('c'=>'show','id'=>$id));
			}
			if($data['utype']=='wap'){
				$return['url']=Url('wap',array('c'=>'redeem','a'=>'show','id'=>$id));
			}
		}
		return $return;
	}

	function getChangeNum($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_num('change',$whereData,$data['field']);
		}

		return $List;
	
	}

	function addChangeInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('change',$setData);
			
		}

		return $nid;
	
	}

	function upChangeInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('change',$data,$whereData);
			
		}

		return $nid;
	
	}
	
	 function delChange($whereData,$data)
	{
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';	
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
		}
		
		/**$data['member']	:会员中心执行删除，member为user表示个人会员，com表示企业会员
		*  $data['uid']	  	:用户uid
		*  $data['usertype']:用户usertype
		*  $data['id']	  	:change表id
		*/

		if($data['member']){
			if($data['uid']==''
				||($data['member']=='com'&&$data['usertype']!='2')
				||($data['member']=='user'&&$data['usertype']!='1')){

				$result['msg']	=	'登录超时';
				$result['cod']	=	8;
			}else{
				$rows	=	$this	->	getChangeInfo(array('uid'=>$data['uid'],'id'=>$data['id']));
				if($rows['id']){
					require_once('integral.model.php');
					require_once('reward.model.php');
					$IntegralM	=	new integral_model($this->db,$this->def);
					
					$this	->	update_once('reward',array('num'=>array('-',$rows['num']),'stock'=>array('+',$rows['num'])),array('id'=>$rows['gid']));

					$IntegralM	->	company_invtal($data['uid'],$data['usertype'],$rows['integral'],true,"取消商品兑换",true,2,'integral',24);
					$this		->	delete_all('change',array('uid'=>$data['uid'],'id'=>$data['id']),$limit);
				}
				$this	->	addMemberLog($data['uid'],$data['usertype'],"取消兑换",17,3);//会员日志
				$result['msg']	=	'取消成功！';
				$result['cod']	=	9;
			}
		}else{
			$result	=	$this -> delete_all('change',$whereData,$limit);
		}
		
		return	$result;
		
	}
	//************************end************************
	

	//*********************redeem_class******************
	//查询商品分类
    function GetRewardClass($whereData,$data=array()){
		$ListNew		=	array();
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('redeem_class',$whereData,$data['field']);
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
		
    }
	
	function getRedeemClassInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('redeem_class',$whereData,$data['field']);
		}

		return $List;
	
	}

	function addRedeemClassInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('redeem_class',$setData);
			
		}

		return $nid;
	
	}

	function upRedeemClassInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('redeem_class',$data,$whereData);
			
		}

		return $nid;
	
	}
	
	
	function delRedeemClass($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('redeem_class',$whereData,$limit);
		
		return	$result;
		
	}
	//************************end************************
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>