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
class admin_yqmb_controller extends adminCommon{
    
    
     
    function index_action(){
 		
	    $comM      =   $this -> MODEL('company');
	    $yqmbM     =   $this -> MODEL('yqmb');
		$typeStr    =   intval($_GET['type']);
		$keywordStr =   trim($_GET['keyword']);
    	if (!empty($keywordStr)) {
			if ($typeStr    == 1) {
				$com    =   $comM -> getList(array('name'=>array('like', $keywordStr)), array('field'=>'`uid`'));
                $cuids = array();
                if (is_array($com['list'])) {
                	foreach ($com['list'] as $v) {  
                        $cuids[] = $v['uid'];  
                    }
                }
                $where['uid']    =   array('in', pylode(',', $cuids));
             
            } elseif ($typeStr == 2) {
				$where['uid']       =   intval($keywordStr);
            
             }
            $urlarr['type']             =   $typeStr;
            $urlarr['keyword']          =   $keywordStr;
            
        }
		
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('yqmb',$where,$pageurl,$_GET['page']);
		
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
		    
		    //limit order 只有在列表查询时才需要
		    if($_GET['order']){
		        
		        $where['orderby']		=	$_GET['t'].','.$_GET['order'];
		        $urlarr['order']		=	$_GET['order'];
		        $urlarr['t']			=	$_GET['t'];
		        
		    }else{
		        $where['orderby']		=	array('id,desc'); 
		    }
		    
		    $where['limit']				=	$pages['limit'];
		    
		    $mbList    =   $yqmbM -> getList($where,array('utype'=>'admin'));
		    
		    $this -> yunset('rows', $mbList);
		    
		}
 		$this->yuntpl(array('admin/admin_yqmb'));
	}
    
	
	
	/**
	 * @desc 删除分享职位
	 */
	function delYqmb_action(){
	    
	    if($_GET['del'])
		{
			$this->check_token();
			
			$yqmbM	=	$this->MODEL('yqmb');
			
			$return		=	$yqmbM->delYqmb($_GET['del']);
			
			$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('请选择要删除的内容！',8);
		}
	     
	}

	function save_action(){
		$yqmbM		=	$this->MODEL('yqmb');
		
		if($_POST['id']){
			
			$where['id']=	$_POST['id'];
			
		}
		$data = array(
			'uid'		=>	$_POST['uid']
		);
		$setdata = array(
			'name' 		=> $_POST['name'],
			'linkman' 	=> $_POST['linkman'],
			'linktel' 	=> $_POST['linktel'],
			'content' 	=> $_POST['content'],
			'intertime' => $_POST['intertime'],
			'address' 	=> $_POST['address'],
		);
		$return			=	$yqmbM->addInfo($setdata,$data,$where);
		
		if($return['errcode']=='9'){
			
			$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
		}else{
			
			$this->ACT_layer_msg($return['msg'],$return['errcode']);
		}
	}

}
