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
class datacall_controller extends adminCommon{
	function index_action(){
		$dataM			=	$this -> MODEL('data');
		
		$limit			=	$this->config["sy_listnum"];//定义显示条数
		
		$urlarr["page"]	=	"{{page}}";
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('outside',$where,$pageurl,$_GET['page'],$limit);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			//limit order 只有在列表查询时才需要
			if($_GET['order'])
			{
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']		=	$_GET['order'];
				$urlarr['t']			=	$_GET['t'];
			}else{
				$where['orderby']		=	'id';
			}
			$where['limit']	=	$pages['limit'];
			
			$List	=	$dataM -> getList($where);
			
			$this->yunset("rows" , $List['list']);
		}
		
		include CONFIG_PATH."/db.data.php";
		
		$this->yunset("datacall",$arr_data['datacall']);
		
		$this->yuntpl(array('admin/admin_datacall'));
	}
	function add_action(){
		$dataM	=	$this -> MODEL('data');
		extract($_POST);
		include CONFIG_PATH."/db.data.php";
		if($_GET[id]){
			
			$info = $dataM->getInfo(array('id'=>$_GET[id]));
			
			$w=@explode(",",$info[where]);
			if(is_array($w)){
				foreach($w as $key=>$va){
					$arr=@explode("_",$va);
					$t[$arr[0]]=$arr[1];
				}
			}
			$this->yunset("info",$info);
			$this->yunset("where",$t);
		}
		if($submit){
			if($name==''){
				$this->ACT_layer_msg("请填写调用名称！",8);
			}
			if(empty($row)){
				
				$postdata	=	$_POST;
				
				if(is_array($arr_data["datacall"][$type]["where"])){
					foreach($arr_data["datacall"][$type]["where"] as $key=>$va){
						$cont[]=$key."_".$$key;
					}
					$postdata['where']	=	implode(',',$cont);
				}
				include LIB_PATH."/datacall.class.php";
				$call= new datacall("../data/plus/data/",$this->obj);
				if($id){
					
					unset($postdata['id']);
					
					$return = $dataM -> upInfo($postdata,array('id'=>$id));
					
					$call->editcache($id);//生成缓存
					
					$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=datacall",2,1);
				}else{
					
					$return = $dataM->addInfo($postdata);
					
					$call->editcache($id);//生成缓存
					
					$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=datacall",2,1);
				}
			}else{
				$this->ACT_layer_msg("调用名称重复，请重新输入！",8,$_SERVER['HTTP_REFERER']);
			}
		}
		$this->yunset("datacall",$arr_data['datacall'][$_GET['type']]);
		$this->yuntpl(array('admin/admin_datacall_add'));
	}
	function preview_action(){
		$src = $this->config["sy_weburl"]."/data/plus/outside.php?id=".$_GET["name"];
		$src = str_replace(" ","",$src);
		$this->yunset("src",$src);
		$this->yuntpl(array('admin/admin_datacall_preview'));
	}
	function del_action(){
		$this->check_token();
		if($_GET[id]){
			$_GET[id] = intval($_GET[id]);
			@unlink(PLUS_PATH."/data/".$_GET["id"].".php");
			$return=$this -> MODEL('data')->delData($_GET["id"]);
			$return['id']?$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],"index.php?m=datacall"):$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],"index.php?m=datacall");
		}
	}
	function cache_action(){
		$dataM=$this -> MODEL('data');
		if($_GET["aid"]==1){
			
			$where['orderby']	=	'id,asc';
			
			$one = $dataM -> getInfo($where);
			
			$wheretwo['id']		=	array('>',$one["id"]);
			$wheretwo['orderby']=	'id,asc';
			
			$two = $dataM -> getInfo("outside",$wheretwo);
			
			$this->update_cache($one["id"],$two);
		}
		if($_GET["id"]){
			$where['id']		=	array('>',$_GET["id"]);
			
			$where['orderby']	=	'id,asc';
			
			$next = $dataM -> getInfo($where);
			
			$this->update_cache($_GET["id"],$next);
		}else{
			$this->ACT_layer_msg("缓存更新成功！",9,"index.php?m=datacall",2,1);
		}
	}
	function make_action(){
		extract($_GET);
		$this->update_cache($id,'');
	}
	function update_cache($one,$two){ 
		include LIB_PATH."datacall.class.php"; 
		$call= new datacall("../data/plus/data/",$this->obj); 
		
		$row=$call->editcache($one);//生成缓存 
		
		if($two[id]!=""){
			echo "正在更新".$row[name]."...请稍后！";

		}
		if($two['id']){
			echo "<script>location.href='index.php?m=datacall&c=cache&id=".$two[id]."'</script>";die;
		}else{
			$this->layer_msg('缓存更新成功！',9,0,"index.php?m=datacall");
		}
	}
}
?>