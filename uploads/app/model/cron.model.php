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
class cron_model extends model{
	
	/**
	* @desc 计划任务列表
	* $whereData		查询条件
	* $data			自定义处理数组
	*/
	public function getList($whereData = array(), $data = array()){
		
		$data['field']	=	empty($data['field']) ? '*' : $data['field'];
		
		$List			=	$this -> select_all('cron', $whereData, $data['field']);
		
		return	$List;
	}
	/**
	* @desc 单条计划任务列表
	* $whereData		查询条件
	* $data			自定义处理数组
	*/
	public function getInfo($whereData, $data = array()){
		
		$data['field']	=	empty($data['field']) ? '*' : $data['field'];
		
		$List			=	$this -> select_once('cron', $whereData, $data['field']);
		
		return	$List;
	}
	/**
	* @desc 添加修改计划任务列表
	* $whereData		查询条件
	* $data			自定义处理数组
	*/
	public function upaddInfo($data = array()){
		if(!empty($data)){
			$id	=	$data["id"];
			
			unset($data["id"]);
			
			unset($data["msgconfig"]);
			
			$data['nexttime']  =	strtotime($this->nextexe($data));
			
			if($data['dir'])
			{
				$dirArr = explode('.',$data['dir']);
				
				if($dirArr[0]=='' || strpos($dirArr[0],'/')!==false || end($dirArr)!='php'){
					return array('msg'=>'无效的执行文件！','errcode'=>'8');
				}else{
					$data['dir']	=	$dirArr[0].'.php';	
				}
			}else{
				return array('msg'=>'请填写计划任务执行文件！','errcode'=>'8');
			}
			
			if($data['type'] == 4){
				if(trim($data['second']) == ''){
					return array('msg'=>'请填写执行任务的间隔秒数！','errcode'=>'8');
				}
				$data['minute'] =	intval($data['second']);
			}
			if(!$id){
				$data["ctime"]	=	time();
				
				$nbid			=	$this->insert_into("cron",$data);
				
				$return['msg']	=	"计划任务(id:".$nbid.")添加";
			}else{
				
				$nbid			=	$this->update_once("cron",$data,array('id'=>$id));
				
				$return['msg']	=	"计划任务(ID:".$id.")修改";
			}
			
			$return['errcode']	=	$nbid?'9':'8';
			
			$return['msg']		=	$nbid?$return['msg'].'成功！':$return['msg'].'失败！';
		
		}
		
		return	$return;
	}
	/**
	* @desc 删除计划任务列表
	* $whereData		查询条件
	* $data			自定义处理数组
	*/
	public function delInfo($delId){
		if(!empty($delId)){
			$nbid				=	$this->delete_all("cron",array('id'=>$delId),'');
				
			$return['msg']		=	"计划任务(ID:".$delId.")";
			
			$return['errcode']	=	$nbid?'9':'8';
			
			$return['msg']		=	$nbid?$return['msg'].'删除成功！':$return['msg'].'删除失败！';
			
		}
		return	$return;
	}
	function excron($cron,$id=''){

       if(is_array($cron) && !empty($cron)){
				
			foreach($cron as $key=>$value){
				if($id){
					if($value['id']==$id){
						$timestamp[$value['nexttime']] = $value;
						$timestamp[$value['nexttime']]['cronkey'] = $key;
						break;
					}
				}else{
					
					if($value['nexttime']<=time()){
						$timestamp[$value['nexttime']] = $value;
						$timestamp[$value['nexttime']]['cronkey'] = $key;
					}
				}
			}
			if($timestamp){
				
				$data['cron'] = ArrayToString($cron);
				$data['start'] = '1';
				made_web_array(PLUS_PATH.'cron.cache.php',$data);

				
				krsort($timestamp);
				$croncache = current($timestamp);
				
				set_time_limit(600);
					
				if(file_exists(LIB_PATH.'cron/'.$croncache['dir'])){
					include(LIB_PATH.'cron/'.$croncache['dir']);
				}
				
				$nexttime = $this->nextexe($croncache);

				$this->update_once("cron",array('nowtime'=>time(),'nexttime'=>strtotime($nexttime)),array('id'=>$croncache['id']));

				$cron[$croncache['cronkey']]['nexttime'] = strtotime($nexttime);
				$data['cron'] = ArrayToString($cron);
				
				$data['start'] = '0';
				made_web_array(PLUS_PATH.'cron.cache.php',$data);
			}
		}
		return true;
    }

	function nextexe($value){
		
		if($value["type"]=='1' && $value["week"]>0){
			$W   = date("w",time());   
			if($value["week"]>=$W){
				$Day = date("Ymd", strtotime("+".($value["week"]-$W)." days", time()));
			}else{
				$Day = date("Ymd", strtotime("+".($value["week"]-$W+7)." days", time()));
			}
		}elseif($value['type']=='2' && $value["month"]>0){
			if($value["month"]<10){
				$Day  = date('Ym')."0".$value["month"];
			}else{
				$Day  = date('Ym')."".$value["month"];
			}
		}elseif($value['type']=='3'){
			$Day = date('Ymd');
		}
		else{
			return date('YmdHis', time() + $value['minute']);
		}

		$Date = $Day;
		
		if($value["hour"]>0){
			if($value["hour"]<10){
				$Date .= "0".$value["hour"];
			}else{
				$Date .= $value["hour"];
			}
		}else{
			$Date .= '00';
		}
		if($value["minute"]>0){
			if($value["minute"]<10){
				$Date .= "0".$value["minute"];
			}else{
				$Date .= $value["minute"];
			}
		}else{
			$Date .= '00';
		}

		
		if($Date<=date('YmdHi')){
			if($value["type"]=='1' && $value["week"]>0){
				$Dates = date('Y-m-d',strtotime("+1 week",$Date));

			}elseif($value['type']=='2' && $value["month"]>0){
				$nextmonth = $this->GetMonth();
				if($value["month"]<10){
					$Dates  = $nextmonth.'-0'.$value["month"];
				}else{
					$Dates  = $nextmonth.'-'.$value["month"];
				}
			}else{
				$Dates = date('Y-m-d',strtotime("+1 days",strtotime($Day)));
			}

			
			if($value["hour"]>0){
				if($value["hour"]<10){
					$Dates  .= ' 0'.$value["hour"];
				}else{
					$Dates  .= ' '.$value["hour"];
				}
			}else{
				$Dates  .= " 00";
			}
			
			if($value["minute"]>0){
				if($value["minute"]<10){
					$Dates  .= ':0'.$value["minute"];
				}else{
					$Dates  .= ':'.$value["minute"];
				}
			}else{
				$Dates  .= ":00";
			}
			return 	$Dates;
		}else{
			return 	$Date;
		}
	}
	function GetMonth(){
		
		$tmp_date=date("Ym");

		
		$tmp_year=substr($tmp_date,0,4);
		
		$tmp_mon =substr($tmp_date,4,2);
		$tmp_nextmonth=mktime(0,0,0,$tmp_mon+1,1,$tmp_year);
		$tmp_forwardmonth=mktime(0,0,0,$tmp_mon-1,1,$tmp_year);

		
		return $fm_next_month=date("Y-m",$tmp_nextmonth);
	}
}
?>