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
class admin_comrating_controller extends adminCommon{	 
    
    /**
     * @desc 增值套餐服务 -- 套餐列表
     */
	function index_action(){
	    
	    $ratingM       =   $this -> MODEL('rating');

        $where['category']      =   '1';
		
        if($_GET['rating']){
		    
            $where['id']        =   intval($_GET['rating']);
		    
            $urlarr['rating']   =   $_GET['rating'];
            
		}
		
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'], $urlarr, 'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('company_rating', $where, $pageurl, $_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
		    
            $where['orderby']   =   array('type,asc','sort,desc');
		    
            $where['limit']     =	$pages['limit'];
		    
            $list               =   $ratingM -> getList($where, array('utype'=>'admin'));
		    
		    $this -> yunset('list', $list);
		    
		}
		 
 		$this->yuntpl(array('admin/admin_company_rating'));
	}
	
    /**
     * @desc 增值套餐服务  --  设置/修改  套餐
     */
	function rating_action(){
	    
	    $ratingM    =   $this -> MODEL('rating');
	    
	    
		if($_GET['id']){
		    
            $row    =   $ratingM -> getInfo(array('id'=>intval($_GET['id'])));
            
            $this   ->  yunset("row", $row);
            
		}   
        

        $this       ->  yuntpl(array('admin/admin_comclass_add'));
	}

	/**
	 * @desc   会员-企业-增值套餐服务
	 * @desc   新增/修改 会员套餐 -- 保存数据
	 */
	function saveclass_action(){
	    
	    
        $id         =   intval($_POST['id']);
        
        $ratingM    =   $this -> MODEL('rating');
        
        if (!empty($id)) {
            
            $row        =   $ratingM -> getInfo($id);
            
        }
        
        $_POST['file']  =  $_FILES['file'];
        $postData       =   $_POST;
        
        if (!empty($id)) {
            
            $err    =   $ratingM -> upRating($id, $postData);
            
        }else{
            
            $err    =   $ratingM -> addRating($postData);
            
        }
        
        $this -> cache_rating();
        
        $this -> ACT_layer_msg($err['msg'], $err['errcode'], 'index.php?m=admin_comrating', 2, 1);
	}

	/**
	 * @desc 删除会员套餐
	 */
	function delrating_action(){
	    
		if($_POST['del']){
 			
            $id         =   $_POST['del'];
			
		}else if($_GET['id']){
		    
            $this -> check_token();
			
            $id         =   $_GET['id'];
			
		}
		
        $ratingM        =	$this -> MODEL('rating');
		 
        $err            =	$ratingM -> delRating($id, array('category'=>'1'));
		    
        $this -> layer_msg($err['msg'], $err['errcode'], $err['layertype'], $_SERVER['HTTP_REFERER']);
		
	}

	/**
	 * @desc 企业会员增值套餐服务    --  套餐详情    --  删除会员等级图标
	 */
	function delpic_action(){
	    
		if($_GET['id']){
			$this->check_token();
			
			$ratingM     =   $this -> MODEL('rating');
			
			$ratingM	->	upRating(intval($_GET['id']),array('com_pic'=>''));
			
			$this -> cache_rating();
			
			$this -> layer_msg('企业会员等级（ID：(ID:'.$_GET['id'].')图标删除成功！',9,0,$_SERVER['HTTP_REFERER']);
		}
		
	}

	function cache_rating(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->comrating_cache("comrating.cache.php");
	}
 
	/**
	 * @desc 后台--会员--企业--增值套餐服务-- 增值服务列表
     */
	function server_action(){
	    
	    $ratingM   =   $this -> MODEL('rating');
	    
	    $list      =   $ratingM -> getComServiceList(array('orderby'=>'sort,desc'));
		
	    $this -> yunset("list", $list);
		
	    $this -> yuntpl(array('admin/admin_com_rating'));
		
	}
	
	/**
	 * @desc 后台--会员--企业--增值套餐服务-- 设置/修改 增值类型
	 */
	function srating_action(){
	    
	    if ($_GET['id']){
	        
	        $where['id']        =   intval($_GET['id']);
	        
	        $ratingM   =   $this -> MODEL('rating');
	        
	        $row       =   $ratingM -> getComServiceInfo($where);

	        $this -> yunset("row", $row);
	    }
	    
		$this->yuntpl(array('admin/admin_comrating_add'));
		
	}
    
	/**
	 * @desc 后台--会员--企业--增值套餐服务-- 保存增值类型
	 */
	function save_action(){
	    
	    $ratingM   =   $this -> MODEL('rating');
        
	    $postData  =   $_POST;
	    
	    $err       =   $ratingM -> upComService($postData);
	    
	    $this -> ACT_layer_msg($err['msg'], $err['errcode'], $err['url'],2 ,1);
	    
     }
     
     /**
      * @desc 后台--会员--企业--增值套餐服务-- 增值服务列表
      *  ajax 修改类型名称
      */
     function ajax_action(){
         
         $id        =   intval($_POST['id']);
         $name      =   trim($_POST['name']);
         $ratingM   =   $this -> MODEL('rating');
         
         if(!empty($name) && !empty($id)){
            
             $serviceNum    =   $ratingM -> getComServiceNum(array('name'=>$name, 'id'=>array('<>', $id)));
         
             if($serviceNum){
                 
                 echo 2;die;
                 
             }
             
             $ratingM -> setComService(array('name' => $name), array('id'=>$id));
             
             $this -> MODEL('log') -> addAdminLog("企业增值包(ID:".$_POST['id'].")名称修改成功");
             
         }
         
         echo '1';die;
     }
     
    /**
     * @desc 删除增值服务类型 
     */     
     function delserver_action(){
         
        if($_POST['del']){
             
            $id     =   $_POST['del'];
            
        }else if($_GET['id']){
             
            $this   ->  check_token();
            
            $id     =   $_GET['id'];
            
        }
        
        $ratingM    =   $this -> MODEL('rating');
         
        $err        =   $ratingM -> delComService($id, array('category'=>'3'));
        
        $this       -> layer_msg($err['msg'], $err['errcode'], $err['layertype'], $_SERVER['HTTP_REFERER']);
        
     }
     
     
     /**
      * @desc 企业增值服务详情列表查询
      */
     function list_action(){
         
         $ratingM   =   $this -> MODEL('rating');
         
         $zzlist    =   $ratingM -> getComServiceList(array('orderby'=>'sort,desc'));
         
         $this      ->  yunset("zzlist", $zzlist);
         
         $id        =   intval($_GET['id']);
         
         if (!empty($id)) {
             
             $row   =   $ratingM -> getComServiceInfo(array('id'=>$id), array('field'=>'name'));
             
             $this  ->  yunset('row', $row);
             
             $list  =   $ratingM -> getComSerDetailList(array('type'=>$id, 'orderby'=>'sort,desc'));
            
             $this  ->  yunset("list", $list);
             
         }
         
         $this->yuntpl(array('admin/admin_comservice_list'));
         
     }
     
     /**
      * @desc 设置增值服务状态
      */
     function opera_action(){
         
         $id        =   intval($_POST['id']);
         $display   =   intval($_POST['display']);
         
         if (!empty($id) && !empty($display)){
             
             $nid   =   $this -> MODEL('rating') -> setComService(array("display"=>$display), array("id"=> $id));
             
             if ($nid){
                 echo 1;die;
             }else{
                 echo 2;die;
             }
         }
         
     }
	
	/**
	 * @desc 企业增值服务套餐  -- 添加 / 修改  增值服务套餐详情
	 */
	function edittc_action(){
	    
        $ratingM    =   $this -> MODEL('rating');
	    
        $zzlist     =   $ratingM -> getComServiceList(array('orderby'=>'sort,desc'));
	    
        $this       ->  yunset("zzlist", $zzlist);
	    
        $id         =   intval($_GET['id']);
        
        $tid        =   intval($_GET['tid']);
    
        
        if (!empty($id)) {
            
            $row    =   $ratingM -> getComServiceInfo(array('id'=>$id));
            
            $this   ->  yunset('row', $row);
            
            $list   =   $ratingM -> getComSerDetailList(array('type'=>$id, 'orderby'=>'id,asc'));
            
            $this   ->  yunset('list', $list);
            
        }
	    
	    if(!empty($tid)){
	        
	        $listinfo  =   $ratingM -> getComSerDetailInfo($tid);
	        
	        $this  ->  yunset("listinfo", $listinfo);
	    }
	    
	    
	    
	    $this->yuntpl(array('admin/admin_comservice_add'));
	    
	}
	
	
	/**
	 * @desc  后台企业增值服务套餐  
	 *        添加 / 修改  增值服务套餐详情  保存
	 */
	function saves_action(){
	    
	    $ratingM   =   $this -> MODEL('rating');
	    
	    $postData  =   $_POST;
	    
	    $err       =   $ratingM -> upComSerDetail($postData);
	    
	    $this -> ACT_layer_msg($err['msg'], $err['errcode'], $err['url'],2 ,1);
	    
	}
	
	
	/**
	 * @desc 删除增值服务套餐详情
	 */
	
	function del_action(){
	    
 		if($_POST['del']){
			
            $id     =   $_POST['del'];
			
		}else if($_GET['id']){
		    
            $this   ->  check_token();
			
            $id     =   $_GET['id'];
 		}
 		
        $ratingM    =   $this -> MODEL('rating');
        
        $err        =   $ratingM -> delComSerDetail($id);
        
        $this       -> layer_msg($err['msg'], $err['errcode'], $err['layertype'], $_SERVER['HTTP_REFERER']);
        
	}

}
?>