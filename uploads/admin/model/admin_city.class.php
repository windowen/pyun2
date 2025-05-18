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
class admin_city_controller extends adminCommon{

	function index_action(){
		$city_ABC				=	array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$categoryM				=	$this -> MODEL('category');
		$whereData['keyid']		=	'0';
		$whereData['orderby']	=	'sort,asc';	
		$city	=	$categoryM	->	getCityClassList($whereData);
		$this->yunset("letter",$city_ABC);
		$this->yunset("city",$city);
		$this->yuntpl(array('admin/admin_city'));
	}
	
	function upp_action(){
		$categoryM	=	$this -> MODEL('category');

		if($_POST['id']!="" || $_POST['addcityname_0']){
			$delid	=	$_POST['id'];
			if($_POST['updateall']){
				if($_POST['addcityname_0']){
					$_POST['id']	=	"0,".$_POST['id'];
				}
				$id_arr			=	@explode(",",$_POST['id']);
				$ck				=	0;
				foreach($id_arr as $key=>$value){
					if($_POST["cityname_".$value]!=""){//更新城市
						$upData['name']		=	$_POST["cityname_".$value];
						$upData['e_name']	=	$_POST["citye_name_".$value];
						$upData['sort']		=	$_POST["citysort_".$value];
						$upData['letter']	=	$_POST["letter_".$value];
						$upData['display']	=	$_POST["display_".$value];
						$upData['sitetype']	=	$_POST["sitetype_".$value];
						$upWhere['id']		=	$value;
						$categoryM	->	upCityClass($upWhere,$upData,array('type'=>'multi'));
					}
					if(is_array($_POST["addcityname_".$value])){//添加的城市
						foreach($_POST["addcityname_".$value] as $k=>$v){
							if($v!=""){
								$addData[$ck]['keyid']		=	$value;
								$addData[$ck]['name']		=	$v;
								$addData[$ck]['letter']		=	$_POST["addletter_".$value][$k];
								$addData[$ck]['display']	=	$_POST["adddisplay_".$value][$k];
								$addData[$ck]['sitetype']	=	$_POST["addsitetype_".$value[$k]];
								$addData[$ck]['e_name']		=	$_POST["addcitye_name_".$value][$k];
								$ck++;
							}
						}
					}
				}
				if(is_array($addData)){
					$categoryM	->	addCityClass($addData);
				}
				
				$categoryM	->	cache_action('city_cache','city');
				$this		->	ACT_layer_msg("区域修改成功！",9,$_SERVER['HTTP_REFERER'],2,1);
			}
			if($delid){//删除多选城市
				$whereData['id']	=	array('in',$delid);
				$categoryM	->	delCityClass($whereData);
				$this		->	layer_msg( "区域(ID:".$delid.")删除成功！",9,1,$_SERVER['HTTP_REFERER']);
			}
		}else{
			$this	->	ACT_layer_msg("请选择需要修改或增加子类的城市！",8,$_SERVER['HTTP_REFERER'],2,1);
			
		}
	}
	function AddCity_action(){
		if( $_POST['kid']){
			$categoryM				=	$this -> MODEL('category');
			$whereData['keyid']		=	$_POST['kid'];
			$whereData['orderby']	=	'sort,asc';
			$adcity					=	$categoryM	->	getCityClassList($whereData);


			echo json_encode($adcity);die;
		}
	}

	function del_action(){
		if((int)$_POST['delid']){
			$categoryM			=	$this -> MODEL('category');
			$whereData['id']	=	array('in',$_POST['delid']);
			$return				=	$categoryM	->	delCityClass($whereData);
			echo $return['error'];
		}
		die;
	}

	function Single_action(){
		$addData			=	array();
		$whereData['id']	=	$_POST['id'];
		$_POST['sort']		=	$_POST['c_sort'];
		unset($_POST['id']);
		unset($_POST['c_sort']);
		$addData			=	$_POST;

		$categoryM	=	$this -> MODEL('category');
		$return		=	$categoryM	->	upCityClass($whereData,$addData,array('type'=>'single'));
		echo $return;
		die;
	}
	function ajax_action(){
		$categoryM			=	$this	->	MODEL('category');
		$whereData['id']	=	$_POST['id'];
		$addData['name']	=	$_POST['name'];
		$addData['e_name']	=	$_POST['e_name'];
		$categoryM	->	upCityClass($whereData,$addData);
		echo '1';die;
	}
	function ajaxpinyin_action(){
		$where['e_name'][]	=	array('isnull');
		$where['e_name'][]	=	array('=','','OR');
		$where['orderby']	=	'sort,desc';
		$data['field']	=	'`id`,`name`,`e_name`';
		$data['type']	=	'city';
		$data['post']	=	$_POST;
		$categoryM	=	$this -> MODEL('category');
		$return		=	$categoryM -> setPinYin($where,$data);
		echo json_encode($return);die;
	}
	function clearpinyin_action(){
		
		$categoryM	=	$this -> MODEL('category');
		
		$categoryM -> clearPinYin('city_class');
		
	}
	function ajaxchachong_action(){
		$where['e_name']	=	array('<>','');
		$where['groupby']	=	'e_name';
		$where['having']	=	array('enum'=>array('>','1'));
		$data	=	array(
			'field'	=>	'*,count(*) as enum',
			'page'	=>	$_POST['page'],
			'type'	=>	'city'
		);
		$categoryM	=	$this -> MODEL('category');
		$list		=	$categoryM -> setChaChong($where,$data);
		echo json_encode($list);die;
	}

}
?>