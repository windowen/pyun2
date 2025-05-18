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
class statis_city_controller extends adminCommon{

	//图表类型 bar 柱形图，line折线图，pie饼形图
	private $chartType = 'pie';

	//消费最多行业统计
	public function index_action(){

		$CompanyorderM							=			$this->MODEL('companyorder');

		$CompanyM								=			$this->MODEL('company');

		$StatisM								=			$this->MODEL('statis');

		$this->yunset('user', '地区');

		if(isset($_GET['radio_time'])){

			$this->yunset('radio_time', $_GET['radio_time']);

		}

		//查询报表数据
		$time_begin 							= 			isset($_GET['time_begin']) ? $_GET['time_begin'] : date('Y-m-d 00:00:00', strtotime('-30 day'));
		
		$time_end 								= 			isset($_GET['time_end']) ? $_GET['time_end'] :date('Y-m-d H:i:s');
		
		$this->yunset('defaultTimeBegin', $time_begin);
		
		$this->yunset('defaultTimeEnd', $time_end);

		$isAllTime 								= 			isset($_GET['isAllTime']) ? $_GET['isAllTime'] : 0; 

		//查询30条内的信息，展示到页面第一屏
		if($isAllTime != 1){

			$timeBegin 							= 			strtotime($time_begin);

		}else{

			$timeBegin 							= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
		}	
		$timeEnd 								= 			strtotime("now");

		list($in, $out, $net_income)			=			$StatisM->getStatisTotal($timeBegin, $timeEnd, '');

		$data [] = array('time' => '近30', 'in' => $in, 'out' => $out, 'net_income' => $net_income);
		
		$this->yunset('data', $data);

		//如果不是查询全部数据，组织查询的开始、结束时间
		if($isAllTime != 1){

			$timeBegin 							= 			strtotime($time_begin);

			$timeEnd 							= 			strtotime($time_end);

			$dateBegin 							= 			date('Y-m-d', $timeBegin);

			$dateEnd 							= 			date('Y-m-d', $timeEnd);
				
	    	$title 								= 			"消费最多地区统计 - {$dateBegin}~{$dateEnd}";
		}

		else{

			$title 								= 			"消费最多地区统计 - 全部数据";

		}

		$names 									= 			array();//扇形每块的名称（收入渠道）
		
		$values 								= 			array();//扇形每块的值
		
		$topNum 								= 			10;//统计消费最多10个地区

		$orwhere['order_state']					=			2;

		if($isAllTime != 1){

			$orwhere['order_time'][]     		=   		array('>=', $timeBegin);
		
			$orwhere['order_time'][]     		=           array('<=', $timeEnd,'AND');

		}
		$limit 									= 			$topNum * 10;

		$orwhere['groupby']						=			'uid';

		$orwhere['orderby']						=			array('num,asc');

		$orwhere['limit']						=			$limit;
	
		$field 									= 			'sum(order_price) as `num`, `uid`';
		
		$row									=			$CompanyorderM->getList($orwhere,array('field'=>$field));

		$uidArr 								= 			array();

		$uidValue								= 			array();

		foreach($row as $r){

			$uidArr [] 							= 			$r['uid'];

			$uidValue[$r['uid']] 				= 			$r['num'];

		}
		$uidStr 								= 			implode(',', $uidArr);

		$comwhere['uid']						=			array('in',pylode(',',$uidArr));

		$comwhere['cityid']						=			array('>',0);
		
		$data									=			$CompanyM->getChCompanyList($comwhere,array('field'=>'`uid`,`cityid`,`provinceid`'));

		$total 									= 			0;

		foreach($data as $r){

			if(array_key_exists($r['cityid'], $values) ){

				$values [$r['cityid']]['value'] 		+= 			$uidValue[$r['uid']];

			}
			else{
				
				$values [$r['cityid']]['value'] 		= 			$uidValue[$r['uid']];
				
				$values [$r['cityid']]['pid'] 			= 			$r['provinceid'];
				
				$values [$r['cityid']]['cid'] 			= 			$r['cityid'];
			
			}
		}

		usort($values,'my_sort');

		$city 											= 			$this->MODEL('cache')->GetCache(array('city'));

		$arr 											= 			array();

		$i 												= 			0;
		foreach($values as $r){

			$names [] 									=			 $city['city_name'][$r['pid']] . '-' . $city['city_name'][$r['cid']];
			
			$rr['value'] 								=			 $r['value'];
			
			$rr['name'] 								= 			 $city['city_name'][$r['pid']] . '-' . $city['city_name'][$r['cid']];

			$arr [] 									= 			 $rr;

			$total 										+= 			 $r['value'];

			$i ++;

			if($i == $topNum){

				break;

			}

		}
		$values	 		= 			$arr;

		$this->yunset('total', $total);
		
		$this->yunset( array('title' => $title,'names' => $names, 'values' => $values ) );
		
		$c 				= 			isset($_GET['c']) ? $_GET['c'] : '';
		
		$this->yunset('gourl', "index.php?m={$_GET['m']}&c={$c}");

		$this->yuntpl(array('admin/statis_user'));
	
	}

}
?>