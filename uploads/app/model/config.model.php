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
class config_model extends model{
	
	/*
	 * 获取配置列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getList($whereData = array(),$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('admin_config',$whereData,"`name`,`config`");
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}
	
	/*
	* 获取配置详情
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	

	function getInfo($whereData = array(), $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('admin_config',$whereData,$data['field']);
		}

		return $List;
	
	}

	/*
	* 创建配置
	* $setData 	自定义处理数组
	*
	*/
	

	function addInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('admin_config',$setData);
			
		}

		return $nid;
	
	}

	/*
	* 更新配置
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/

	function upInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('admin_config',$data,$whereData);
			
		}

		return $nid;
	
	}
	/*
	* 查询数量
	* $whereData 	查询条件
	*
	*/

	function getNum($whereData){

		if(!empty($whereData)){
			
			$num	=	$this -> select_num('admin_config',$whereData);
			
		}

		return $num;
	
	}
	
	//congfig参数设置
	function setConfig($data)
	{
	    $config  =  $this -> select_all('admin_config',array(),'`name`');
	    
	    foreach($config as $v){
			
			$allList[]	=	$v['name'];
		
		}
		
		foreach($data as $key=>$v){
			
			if(in_array($key,$allList)){
			    
				$this->upInfo(array('name'=>$key),array('config'=>$v));
				
			}else{
			    
				$this->addInfo(array('name'=>$key,'config'=>$v));
			
			}
		}
	}
	/**
	 *  生成四位JS，css识别码 
	 */
	public function cacheCode(){
	    
	    $config     =  $this -> select_once('admin_config', array('name'=>'cachecode'));
	    
	    $cachecode  =  rand(1000, 9999);
	    
	    if (empty($config)) {
	        
	        $this->insert_into('admin_config',array('name'=>'cachecode','config'=>$cachecode));
	        
	    } else {
	        
	        $this->update_once('admin_config',array('config'=>$cachecode),array('name'=>'cachecode'));
	    }
	}
	/**
	 * 获取银行卡列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getBankList($whereData=array(),$data=array('field'=>'*')){
		$list  =  $this -> select_all('bank',$whereData,$data['field']);
		return	$list;
	}
	/**
	 * 获取银行卡详细信息
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getBankInfo($whereData=array(),$data=array('field'=>'*')){
		$BankInfo  =  $this -> select_once('bank',$whereData,$data['field']);
		return	$BankInfo;
	}
	/**
	 * 添加银行卡
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function addBank($addData=array(),$data=array()){
		$return  =  $this -> insert_into('bank',$addData);
		return	$return;
	}
	/**
	 * 修改银行卡
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function upBank($whereData=array(),$data=array()){
		$return  =  $this -> update_once('bank',$data,$whereData);
		return	$return;
	}
	/**
	 * 删除银行卡
	 * $whereData 	删除条件
	 * $data		自定义
	 */
	function delBank($whereData=array(),$data=array()){

		$result	=  $this -> delete_all('bank',$whereData,'');		
		return	$result;
	}
	/**
	 * 获取预警设置列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	public function getWarningList($whereData, $data=array())
    {
        if ($whereData) {

            $data['field']  =   empty($data['field']) ? '*' : $data['field'];

            $List   =   $this -> select_all('warning', $whereData, $data['field']);
            
            if (is_array($List)) {
                
                $uid    =   array();
                
                foreach ($List as $v) {
            
                    $uid[]  =   $v['uid'];
                }
                
                $member =   $this->select_all('member', array('uid' => array('in', pylode(',', $uid))), '`uid`,`username`');
                
                foreach ($List as $k => $v) {
                    foreach ($member as $val) {
                        if ($v['uid'] == $val['uid']) {
                            $List[$k]['username'] = $val['username'];
                        }
                    }
                }
            }
        }
        return $List;
    }
	/**
    * @desc    删除预警设置列表
    */
	public function delWarning($delId)
    {
	    
        if (empty($delId)) {
            
            return array(
                'errcode'   => 8,
                'msg'       => '请选择要删除的数据！'
            );
            
        } else {
            $return     =   array();
            
            if (is_array($delId)) {
                
                $delId  =   pylode(',', $delId);
                $return['layertype']    =   1;
            } else {
            
                $return['layertype']    =   0;
            }

            $nid    =   $this -> delete_all('warning', array('id' => array('in', $delId)), '');
            
            if ($nid) {
            
                $return['msg']      =   '预警信息(ID:' . $delId . ')';
                $return['errcode']  =   $nid ? '9' : '8';
                $return['msg']      =   $nid ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
            }
        }
        
        return $return;
    }
}
?>