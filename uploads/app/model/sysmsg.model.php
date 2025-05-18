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
class sysmsg_model extends model{
   
  	/**
     * @desc    获取私信详细信息
     */
	function getSysmsgInfo($whereData,$data=array()){
		
		$field	=	empty($data['field']) ? '*' : $data['field'];
		
		$SysmsgInfo 	=	$this -> select_once('sysmsg', $whereData, $field);
		
		if(!empty($SysmsgInfo)){
			$patten		=	array("\r\n", "\n", "\r"); 
			$patea      =	"/<a[^>]*>(.*?)<\/a>/is";
			$content	=	str_replace($patten, "<br/>", $SysmsgInfo['content']);
			$str		=	htmlspecialchars_decode($content);
			$SysmsgInfo['content_n']	=	preg_replace($patea,"$1", $str);
			$SysmsgInfo['content']		=	strip_tags($SysmsgInfo['content']);
			$SysmsgInfo['ctime_n']		=	date('Y-m-d H:i',$SysmsgInfo['ctime']);
		}
		
		return $SysmsgInfo;
		
	}  
	/**
	 * @desc 获取系统消息列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	public function getList($whereData = array(), $data = array())
    {
        
        $field	=	empty($data['field']) ? '*' : $data['field'];
        $List   =   $this->select_all('sysmsg', $whereData, $field);
        
        if (is_array($List)) {
            $patten = array("\r\n","\n","\r");
            $patea  = "/<a[^>]*>(.*?)<\/a>/is"; // 匹配a标签
            $patet  = "/href=\"(.*)\"/"; // 取a标签的值
            $pateh  = '/<a .*?href="(.*?)".*?>/is';
			
            foreach ($List as $key => $value) {
                $arr = $str = '';
                $url_wx	=	array();
                $List[$key]['ctime_n'] = date('Y-m-d H:i', $value['ctime']);
                // 去掉相关的链接<a>标签
                $content = str_replace($patten, "<br/>", $value['content']);
                $wxappStr = htmlspecialchars_decode($content);
                $List[$key]['content_all'] = preg_replace($patea, "$1", $wxappStr);
				

                // 对a标签进行替换处理 注：当前消息没有相关a标签链接，不影响显示相关的值
                preg_match_all($pateh, $value['content'], $str); // 匹配a标签里面相关的值
				
				if(!empty($str[1])){
					foreach($str[1] as $k=>$v){
					
						$arr = explode(',', $v); // 进行数组遍历
				
                                                  // arr[0] :针对类型 $arr[1]:id的值
                                                  // 个人类型 : usertpl 根据个人uid查找默认简历
						if ($arr[0] == "usertpl") {
							// 判断是否为手机端 重新编写新链接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "resume",
									'a' => 'show',
									"uid" => $arr[1]
								));
							} else {
								$url = Url('resume', array(
									"c" => "showuid",
									"uid" => $arr[1]
								));
							}
							$url_wx[] = "/pages/resume/show?uid=" . $arr[1];
						}
						// 简历类型 :resumetpl 根据简历id查找简历
						if ($arr[0] == "resumetpl") {
							// 判断是否为手机端 重新编写新链接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "resume",
									'a' => 'show',
									"id" => $arr[1]
								));
							} else {
								$url = Url('resume', array(
									"c" => "show",
									"id" => $arr[1]
								));
							}
							$url_wx[] = "/pages/resume/show?id=" . $arr[1];
						}
						// 企业类型 comtpl
						if ($arr[0] == "comtpl") {
							// 判断是否为手机端 重新编写新链接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "company",
									'a' => 'show',
									"id" => $arr[1]
								));
							} else {
								$url = Url('company', array(
									"c" => "show",
									"id" => $arr[1]
								));
							}
							$url_wx[] = "/pages/company/show?id=" . $arr[1];
						}
						// 企业职位 :comjobtpl
						if ($arr[0] == "comjobtpl") {
							// 判断是否为手机端 重新编写新连接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "job",
									'a' => 'comapply',
									"id" => $arr[1]
								));
							} else {
								$url = Url('job', array(
									"c" => "comapply",
									"id" => $arr[1]
								));
							}
							$url_wx[] = "/pages/job/show?id=" . $arr[1];
						}
						
						// 招聘会类型:zphtpl
						if ($arr[0] == "zphtpl") {
							// 判断是否为手机端
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "zph",
									'a' => 'show',
									"id" => $arr[1]
								));
							} else {
								$url = Url('zph', array(
									"c" => "show",
									"id" => $arr[1]
								));
							}
							$url_wx[]	= "/pages/zph/show?id=".$arr[1];
						}
						// 兼职类型:parttpl
						if ($arr[0] == "parttpl") {
							// 判断是否为手机端 重新编写新链接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "part",
									'a' => 'show',
									"id" => $arr[1]
								));
							} else {
								$url = Url('part', array(
									"c" => "show",
									"id" => $arr[1]
								));
							}
							$url_wx[] = "/pages/part/show?id=" . $arr[1];
						}
						// 兑换商品记录:rewardtpl
						if ($arr[0] == "rewardtpl") {
							// 判断是否为手机端 重新编写新链接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "redeem",
									'a' => 'show',
									"id" => $arr[1]
								));
							} else {
								$url = Url('redeem', array(
									"c" => "show",
									"id" => $arr[1]
								));
							}
						}
						// 问答:answertpl
						if ($arr[0] == 'answertpl') {
							// 判断是否为手机端 重新编写新链接
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "ask",
									'a' => 'content',
									"id" => $arr[1]
								));
							} else {
								$url = Url('ask', array(
									"c" => "content",
									"id" => $arr[1]
								));
							}
						}
						// 网络招聘会类型:zphnettpl
						if ($arr[0] == "zphnettpl") {
							// 判断是否为手机端
							if (isMobileUser()) {
								$url = Url('wap', array(
									"c" => "zphnet",
									'a' => 'show',
									"id" => $arr[1]
								));
							} else {
								$url = Url('zphnet', array(
									"c" => "show",
									"id" => $arr[1]
								));
							}
							$url_wx[]	= "/pages/zphnet/show?id=".$arr[1];
						}
						if (! empty($url_wx)) {
							$List[$key]['url_wx'] = $url_wx;
						}
						
						$List[$key]['content'] = str_replace($v, $url, $List[$key]['content']); // 替换原来标签

					}
				}
                
				



                if ($value['remind_status'] == 1) {
                    $List[$key]['status_n'] = '已查看';
                } else {
                    $List[$key]['status_n'] = '未查看';
                }
            }

        }
        if ($data['from'] == 'wap_member') {
            
            $uid    =   intval($whereData['fa_uid']);
            $usertype   =   intval($whereData['usertype']);
            
            $this->upInfo(array('fa_uid' => $uid, 'usertype' => $usertype, 'remind_status' => '0'), array('remind_status' => '1'));
        }
        
        return $List;
    }
	
	public function upInfo($whereData,$data = array()){
	    if (!empty($whereData)) {
            
            $nid    =   $this -> update_once('sysmsg', $data, $whereData);
            
            return $nid;
        }
	    
	}
	/**
	 * 添加系统消息
	 * @param array $data
	 */
	public function addInfo($data = array()){
	    
	    if (!empty($data['uid'])){
	        
	        $list     =  array();
	        
	        $time     =  time();
	        
	        $content  =  $data['content'];
	        
	        $usertype =  $data['usertype'];
	        
	        $member   =  $this -> select_all('member',array('uid'=>array('in',pylode(',', $data['uid']))),'uid,username,usertype');
	        
	        foreach ($member as $k=>$v){
	            
	            $list[$k]['fa_uid']    =  $v['uid'];
              
	            $list[$k]['username']  =  $v['username'];
	            
	            if (is_array($usertype) && empty($usertype)) {
	                
	                $list[$k]['usertype']  =  $usertype[$v['uid']];
	            }else{
	                
	                $list[$k]['usertype']  =  $data['usertype'] ? $data['usertype'] : $v['usertype'];
	            }
                
	            
	            $list[$k]['ctime']     =  $time;
	            
	            //要区分内容，批量添加的内容不相同时，需要先处理后用数组传入
	            
	            if (is_array($content)){
	                
	                $list[$k]['content']   =  $content[$v['uid']];
	                
	                if(is_array($list[$k]['content'])){
	                    
	                    $list[$k]['content'] = @implode('，', $list[$k]['content']);
	                }
	                 
	            }else{
	                
	                $list[$k]['content']   =  $content;
	            }
	        }
			
 	        if (!empty($list)){
	            
	            $this -> DB_insert_multi('sysmsg',$list);
	        }
	    }
	}
	/**
	 * 删除系统消息
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	public function delInfo($id,$data){
		
	    $limit                =  'limit 1';
	    $return['layertype']  =	 0;
	    
	    if (!empty($id)){
	        
	        if(is_array($id)){
	            
	            $id  =  pylode(',', $id);
	            $return['layertype']  =  1;
	            $limit                =  '';
	        }
	        if($data['fa_uid']){

	            $nid	=	$this->delete_all('sysmsg',array('id'=>array('in',$id),'fa_uid'=>$data['fa_uid']),$limit);

			}else{

				$nid	=	$this->delete_all('sysmsg',array('id'=>array('in',$id)),$limit);
			}
	       
	        
	        if ($nid){
	            
	            $return['msg']      =  '系统消息(ID:'.$id.')删除成功';
	            $return['errcode']  =  '9';
	        }else{

	            $return['msg']      =  '系统消息(ID:'.$id.')删除成功';
	            $return['errcode']  =  '8';
	        }
	    }else{

	        $return['msg']      =  '请选择您要删除的系统消息';
	        $return['errcode']  =  '8';
	    }
	    return $return;
	}
	/**
     * @desc    企业系统消息设置是否查看
     */
	public function upSysmsg($whereData,$data = array()){
		
		if(!empty($whereData)) {
			
			$PostData	=	array(
				
				'remind_status'		=>	$data['remind_status'],
			
			);
			
			$nid  =  $this -> update_once('sysmsg', $PostData, $whereData);
			
		}
		
		return $nid;
	
	}
	/**
     * @desc    删除企业会员中心系统消息
     */
	function delSysmsg($id = null , $data = array()) {
        
        if(!empty($id)){
            
            if(is_array($id)){
                
                $ids    =	$id;
                
                $return['layertype']	=	1;
                
            }else{
                
                $ids    =   @explode(',', $id);
				
				$return['layertype']	=	0;
                
            }
            
            $id	=	pylode(',', $ids);
			 

            $return['id']		=	$this -> delete_all('sysmsg',array('id' => array('in',$id),'fa_uid'=>$data['fa_uid']),'');
            
            $return['msg']		=	'系统消息';
            
            $return['errcode']	=	$return['id'] ? '9' :'8';

            
		    $return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
       
	    }else{
            

			$return['msg']		=	'请选择您要删除的数据！';
            
			$return['errcode']	=	8;
        
		}
        
        return	$return;
    
	}

	/**
     * @desc    获取私信数目
     */
	function getSysmsgNum($WhereData = array()){
		
		$Sysmsgnum	=	$this->select_num('sysmsg', $WhereData);
		
		return $Sysmsgnum;
	}
	
	
}
?>