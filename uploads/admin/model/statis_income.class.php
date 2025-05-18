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
class statis_income_controller extends adminCommon{

	//图表类型 bar 柱形图，line折线图，pie饼形图
	// private $chartType = 'pie';

	//消费渠道
	private $typeMapping = array(	1 => '会员充值（购买会员）',2 => '积分充值',3 => '银行转帐',4 => '金额充值',
									5 => '购买增值包',7 => '购买小程序',
									10 => '职位置顶',11 => '职位紧急',12 => '职位推荐',
									13 => '自动刷新',14 => '简历置顶',16 => '刷新职位',
									17 => '刷新兼职',19 => '下载简历',20 => '发布职位',
									21 => '发布兼职',23 => '面试邀请',24 => '兼职推荐',
									25 => '店铺招聘'
	);

	//收支总计页面
	public function index_action(){

		$CompanyorderM					=			$this->MODEL('companyorder');

		$PackM							=			$this->MODEL('pack');

		$StatisM						=			$this->MODEL('statis');

		if(isset($_GET['radio_time'])){

			$this->yunset('radio_time', $_GET['radio_time']);
		
		}

		//查询报表数据
		$time_begin 					= 			isset($_GET['time_begin']) ? $_GET['time_begin'] : date('Y-m-d 00:00:00', strtotime('-30 day'));

		$time_end 						= 			isset($_GET['time_end']) ? $_GET['time_end'] :date('Y-m-d H:i:s');
		
		$this->yunset('defaultTimeBegin', $time_begin);
		
		$this->yunset('defaultTimeEnd', $time_end);

		$isAllTime						 = 			isset($_GET['isAllTime']) ? $_GET['isAllTime'] : 0; 

		//查询30条内的信息，展示到页面第一屏
		if($isAllTime != 1){

			$timeBegin 					= 			strtotime($time_begin);

		}else{

			$timeBegin 					= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
		
		}	

		$timeEnd 						= 			strtotime("now");
	
		list($in, $out, $net_income)	=			$StatisM->getStatisTotal($timeBegin,$timeEnd, '');

		$data [] 						= 			array('time' => '近30', 'in' => $in, 'out' => $out, 'net_income' => $net_income);
		
		$this->yunset('data', $data);

		//如果不是查询全部数据，组织查询的开始、结束时间
		if($isAllTime != 1){

			$timeBegin 					= 			strtotime($time_begin);

			$timeEnd 					 =	 		strtotime($time_end);

			$dateBegin 					 = 			date('Y-m-d', $timeBegin);

			$dateEnd 					 = 			date('Y-m-d', $timeEnd);
			
	    	$title 						 = 			"收益渠道统计 - {$dateBegin}~{$dateEnd}";
		
		}
		else{

			$title 						  =		 	"收益渠道统计 - 全部数据";

		}

		$names 							  = 			 array();//扇形每块的名称（收入渠道）

		$values							  = 			 array();//扇形每块的值
		
		$comwhere['order_state']		  =			2;

	
		if($isAllTime != 1){

			$comwhere['order_time'][]     =   		 array('>=', $timeBegin);
		
			$comwhere['order_time'][]     =          array('<=', $timeEnd,'AND');
			
		}

		$comwhere['groupby']			  =			'type';
		
		$comwhere['orderby']			  =			array('num,asc');
	
		$field 							  = 		'sum(order_price) as `num`, `type`';

		$row							  =			$CompanyorderM->getList($comwhere,array('field'=>$field));
		
		$total 							  = 		0;
		foreach($row as $r){

			if(isset($this->typeMapping[$r['type']])){

				$names [] 						= 		$this->typeMapping[$r['type']];
				
				$rr['value'] 					= 		$r['num'];

				$rr['name'] 					= 		$this->typeMapping[$r['type']];

				$values [] 						= 		$rr;

				$total 						   += 		$r['num'];
			}

		}

		$data = array('title' => $title,'names' => $names, 'values' => $values );

		$this->yunset($data);

		$this->yunset('total', $total);

		$this->yuntpl(array('admin/statis_income'));

	}

}
?>