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
class admin_domain_controller extends adminCommon{
    function index_action(){
		$this->yunset('config',$this->config);
		
		$this->yuntpl(array('admin/admin_domain_config'));
	}
	function savecf_action(){
 		if($_POST['config']){
			unset($_POST['config']);
			$_POST  =  $this->post_trim($_POST);
			// 处理默认域名，防止未携带http头
			if (!empty($_POST['sy_indexdomain'])){
			    
			    if (stripos($_POST['sy_indexdomain'], 'http') === false){
			        
			        if (stripos($this->config['sy_weburl'], 'https://') !== false){
			            $protocol = 'https://';
			        }else{
			            $protocol = 'http://';
			        }
			        $_POST['sy_indexdomain']  =  $protocol.$_POST['sy_indexdomain'];
			    }
			}
			
			$this -> MODEL('config') -> setConfig($_POST);
			
			$this->web_config();
			
			$this->layer_msg('网站配置设置成功！',9,1);
		 }
	}
	function alllist_action(){
		//查询
		$this->yunset($this -> MODEL('cache') -> GetCache(array('city','hy')));
		
		$siteM			=	$this -> MODEL('site');
		
		$urlarr['c']	=	$_GET['c'];
		
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('domain',array(),$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		
		if($pages['total'] > 0){
			
			if($_GET['order'])
			{
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				
				$urlarr['order']	=	$_GET['order'];
				
				$urlarr['t']		=	$_GET['t'];
			}else{
				
				$where['orderby']	=	'id';
			}
			
			$where['limit']	=	$pages['limit'];
		
			$List			=	$siteM -> getList($where);
			
		}
		
		$this->yunset('domain',$List);
		
		$this->yuntpl(array('admin/admin_domain'));
	}
	function AddDomain_action(){
		
		$this->yunset($this->MODEL('cache')->GetCache(array('city','hy')));
		
		include_once('model/model/style_class.php');//引用操作文件
		
		$style	=	new style($this->obj);
		
		$list	=	$style->model_list_action();
		
		$this->yunset('list',$list);
		
		$this->yuntpl(array('admin/admin_adddomain'));
	}
	function save_action(){
		$siteM	=	$this->MODEL('site');
		
	    if ($this->config['sy_web_site']!=1){
	        $this->layer_msg('请先开启分站！',8,1);
	    }
		if($_POST['domain']){$domain = @str_replace(array('http://','https://'),'',$_POST['domain']);}
		if($_POST['fz_type']=='1'){//如果是地区分站，行业类别为空
			$_POST['hy']='';
		}else{				//如果是地区分站，行业类别为空
			$_POST['cityid']='';
		}
		if ($_FILES['file']['tmp_name']!=''){
		    
		    $upArr    =  array(
		        'file'  =>  $_FILES['file'],
		        'dir'   =>  'logo'
		    );
		    
		    $uploadM  =  $this->MODEL('upload');
		    
		    $pic      =  $uploadM->newUpload($upArr);
		    if (!empty($pic['msg'])){
		        
		        $this->ACT_layer_msg($pic['msg'],8);
		        
		    }elseif (!empty($pic['picurl'])){
		        
		        $_POST['weblogo']  =  $pic['picurl'];
		    }
		}

		if($_POST['id']){
		    if(($domain!='' || $_POST['indexdir']!='') && $_POST['title']!='' ){
				
				$whereData['id'] = array('<>',$_POST['id']);

				$whereData['PHPYUNBTWSTART1']  =  'AND';
				if($domain!=''){
					
					$whereData['domain']             =  array('=',$domain);
					if($_POST['indexdir']!=''){
					
						$whereData['indexdir']       =  array('=',$_POST['indexdir'],'OR');
						
					}
				}else{
				
					$whereData['indexdir']           =  array('=',$_POST['indexdir']);
				}
                $whereData['PHPYUNBTWEND1']    =  '';

		        $domain_list	=	$siteM -> getInfo($whereData);
		        if(is_array($domain_list)){
		            $this->ACT_layer_msg('该域名已经被绑定！',8);
		        }else{
		        	
		            $siteM -> addInfo($_POST,array('id'=>$_POST['id']));
					
		            $this->DomainArr();
					
		            $this->ACT_layer_msg('分站(ID:'.$_POST['id'].')修改成功！',9,'index.php?m=admin_domain&c=alllist',2,1);
		        }
		    }else{
		        $this->ACT_layer_msg('信息填写不完整！',8);
		    }
		}else{
		    
		    if(($domain!='' || $_POST['indexdir']!='')  && $_POST['title']!='' ){

				if($domain!=''){
					
					$whereData['domain']             =  array('=',$domain);
					if($_POST['indexdir']!=''){
					
						$whereData['indexdir']             =  array('=',$_POST['indexdir'],'OR');
						
					}
				}else{
				
					$whereData['indexdir']             =  array('=',$_POST['indexdir']);
				}

				

		        $domain_list = $siteM -> getInfo($whereData);
		        if(is_array($domain_list)){
		            $this->ACT_layer_msg('该域名已经被绑定！',8);
		        }else{
					
		            $id	=	$siteM -> addInfo($_POST);
					
		            $this->DomainArr();
					
		            $this->ACT_layer_msg('分站(ID'.$id.')创建成功！',9,'index.php?m=admin_domain&c=alllist',2,1);
		        }
		    }else{
		        $this->ACT_layer_msg('信息填写不完整！',8);
		    }
		}
	}

	function Modify_action(){
		if($_GET['siteid']){
			$this->yunset($this->MODEL('cache')->GetCache(array('city','hy')));
			
			include_once('model/model/style_class.php');//引用操作文件
			
			$style	=	new style($this->obj);
			
			$list	=	$style->model_list_action();
			
			$this->yunset('list',$list);
			
			$site	=	$this -> MODEL('site') -> getInfo(array('id'=>$_GET['siteid']));
			
			$this->yunset('site',$site);
		}
		$this->yuntpl(array('admin/admin_adddomain'));
	}
	function AjaxCity_action(){
		if($_GET['keyid']){
			
			$city=$this->MODEL('category')->getCityClassList(array('keyid'=>$_GET['keyid']));
			
			$html='';
			if(is_array($city)){
				
				foreach($city as $key=>$value){
					
					$html.='<option value="'.$value['id'].'">'.$value['name'].'</option>';
				}
			}
			echo $html;die;
		}
	}
	function DelDomain_action(){
		$this->check_token();
		if($_GET['delid']){
			
			$return	=	$this -> MODEL('site') -> delDomain($_GET['delid']);
			
			$this->DomainArr();
			
			$this->layer_msg($return['msg'],$return['errcode'],0,'index.php?m=admin_domain&c=alllist');
		}
	}
	function allDelDomain_action(){
		$this->check_token();
	    if($_GET['del']){
			
	    	$del=$_GET['del'];
			
	    	if(is_array($del)){
				
				$return	=	$this -> MODEL('site') -> delDomain($del);
				
 				$this->layer_msg($return['msg'],$return['errcode'],1,$_SERVER['HTTP_REFERER']);
	    	}else{
				$this->layer_msg('请选择您要删除的分站！',8,1,$_SERVER['HTTP_REFERER']);
	    	}
	    }
	}
	function DomainArr(){
		include(LIB_PATH.'cache.class.php');
		
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		
		$makecache=$cacheclass->domain_cache('domain_cache.php');
	}

	function checkType_action(){
		if($_POST['id'] && $_POST['type']){
			
			$this -> MODEL('site') -> upInfo(array('type'=>$_POST['type']),array('id'=>$_POST['id']));
		}
		echo 1;
	}

}
?>