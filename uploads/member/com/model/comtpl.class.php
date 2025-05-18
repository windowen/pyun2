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
class comtpl_controller extends company{
	function index_action() {
		$comM	=	$this -> MODEL('company');
		
		$tplM	=	$this -> MODEL('tpl');
		
		$statis	=	$this -> company_satic();
		
		$where['status']			=	'1' ;
		
		$where['PHPYUNBTWSTART_A']	=	'' ;

		$where['service_uid'][]		=	array('findin', $this->uid) ;
		
		$where['service_uid'][]		=	array('=', '0', 'OR') ;
		
		$where['PHPYUNBTWEND_A']	=	'' ;
		
		$where['orderby']			=	'id,desc' ;
		
		$list		=	$tplM -> getComtplList($where);
		
		$bannernum	=	$comM -> getBannerNum(array('uid'=>$this->uid));
		
		$this -> public_action();
		$this -> yunset("list", $list);
		if(!empty($statis['comtpl_all'])){
			$buytpl	=	@explode(",", $statis['comtpl_all']);
			foreach($buytpl as $key=>$val){
				$buytpl_arr[]	=	trim($val);
			}
			
		}else{
			$buytpl_arr	=	array();
		}
		$this -> yunset("buytpl", $buytpl_arr);
		$this -> yunset("bannernum", $bannernum);
		$this -> com_tpl('comtpl');
	}
	function settpl_action(){
		$IntegralM	=	$this -> MODEL('integral');
		
		$statisM	=	$this -> MODEL('statis');
		
		$logM		=	$this -> MODEL('log');
		
		$tplM		=	$this -> MODEL('tpl');
		
		$comM		=	$this -> MODEL('company');
		
		$bannernum	=	$comM -> getBannerNum(array('uid'=>$this->uid));
		
		if($_POST['savetpl']){
		    
		    $where    =   array();
		    
			$where['status']			=	'1' ;
			
			$where['PHPYUNBTWSTART_A']	=	'' ;

			$where['service_uid'][]		=	array('findin', $this->uid) ;
			
			$where['service_uid'][]		=	array('=', '0', 'OR') ;
			
			$where['PHPYUNBTWEND_A']	=	'' ;
			
			$where['orderby']			=	'id,desc' ;
			
			$list	=	$tplM -> getComtplList($where);
			
			foreach($list as $v){
				
				$tplid[]=$v['id'];
				
			}
			
			$statis		=	$this -> company_satic();
			
			if(in_array($_POST['tpl'],$tplid)){
				
				$row	=	$tplM -> getComtpl(array('id'=>(int)$_POST['tpl']));
				
				if(strstr($statis['comtpl_all'],$row['url'])==false){
					
					if($row['type']==1){
						
						if($statis['integral']<$row['price']){
							$this->ACT_layer_msg("您的".$this->config['integral_pricename']."不足，请先充值！",8,"index.php?c=pay");
						}
						
						$nid		=	$IntegralM->company_invtal($this->uid,$this -> usertype, $row['price'],false,"购买企业模板",true,2,'integral',15);
					}
					else{
						if($statis['integral']<$row['price']){
							$this	->	ACT_layer_msg("您的余额不够购买，请先充值！",8,"index.php?c=pay");
						}
						$nid		=	$IntegralM->company_invtal($this->uid,$this -> usertype, $row['price'],false,"购买企业模板",true,2,"integral",15);//积分操作记录
					
					}
					if($statis['comtpl_all']==''){
						$statisM->upInfo(array("comtpl_all"=>$row['url']),array("uid"=>$this->uid,'usertype'=>2));
					}else{
						$statisM->upInfo(array("comtpl_all"=>array('concat',$row['url'])),array("uid"=>$this->uid,'usertype'=>2));
					}
				}
				
				$oid	=	$statisM->upInfo(array("comtpl"=>$row['url']),array("uid"=>$this->uid,'usertype'=>2));
				if($oid){
					if($bannernum==0){
						$this->ACT_layer_msg("设置成功！",9,"index.php?c=comtpl");
					}else{
						$this->ACT_layer_msg("恭喜您设置成功,您还可以上传横幅广告！",9,"index.php?c=comtpl");
					}
					$logM->addMemberLog($this->uid,$this->usertype,"设置企业模版",16,1);//会员日志
				}else{
					$this->ACT_layer_msg("设置失败，请稍后再试！",8,$_SERVER['HTTP_REFERER']);
				}
			}else{
 				$this->ACT_layer_msg("请正确选择模版！",8,"index.php?c=comtpl");
			}
		}
	}
}
?>