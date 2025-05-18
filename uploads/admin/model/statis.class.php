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
class statis_controller extends adminCommon{

	//图表类型 bar 柱形图，line折线图，pie饼形图
	private $chartType = 'line';

	//收支总计页面
	public function index_action(){

		$StatisM								=			$this->MODEL('statis');

		$CompanyorderM							=			$this->MODEL('companyorder');


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
			$timeBegin							 = 			strtotime($time_begin);
		}else{
			$timeBegin 							 = 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
		}	
		$timeEnd 								 = 			strtotime($time_end);

		list($in, $out, $net_income) 			 = 			$StatisM->getStatisTotal($timeBegin, $timeEnd, '');
		
		$data [] 								 = 			array('time' => '近30', 'in' => $in, 'out' => $out, 'net_income' => $net_income);
		
		$this->yunset('data', $data);

		//如果不是查询全部数据，组织查询的开始、结束时间
		if($isAllTime != 1){

			$timeBegin 							= 			strtotime($time_begin);

			$timeEnd 							= 			strtotime($time_end);
			
			$dateBegin 							= 			date('Y-m-d', $timeBegin);

			$dateEnd 							= 			date('Y-m-d', $timeEnd);

			$title 								=			"收支统计 - {$dateBegin}~{$dateEnd}";
		
		}else{
			$title 								= 			"收支统计 - 全部数据";	
		}

		//收支总计 查询：in毛收入，out支出/提现，net_income净收入
		
		$names 									= 			array();//横坐标名称（时间日期）
		
		$values 								= 			array();//纵坐标的值
		
		$dataGroupNames 						= 			array();//柱行代表数据的名称
    		
		//毛收入
		$comwhere['order_state']				=			2;

		if($isAllTime != 1){

			$comwhere['order_time'][]         	=   		array('>=', $timeBegin);
			
			$comwhere['order_time'][]         	=           array('<=', $timeEnd,'AND');

		}

		$comwhere['groupby']					=			'date';

		$comwhere['orderby']					=			array('date,asc');

		$comfield 								=			"sum(order_price) as `num`, from_unixtime(order_time,'%Y-%m-%d') as `date`";
		
		$row									=			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
		
		$totalIn 								= 			0;//收入总计
		if(count($row) < 1){

			$in 								= 			array('names' => array(), 'values' => array());
		
		}else{

			$inNames 							= 			array();

			$inValues 							= 			array();
			foreach($row as $r){

				$inNames [] 					= 			$r['date'];

				$inValues [] 					= 			$r['num'];

				$totalIn 						+= 			$r['num'];
			}

			$in 								 = 			array('names' => $inNames, 'values' => $inValues);
		
		}

		//支出/提现
		$memwhere['order_state']		=			1;
	
		if($isAllTime != 1){

			$memwhere['time'][]         =   		array('>=', $timeBegin);
			
			$memwhere['time'][]         =           array('<=', $timeEnd,'AND');

		}

		$memwhere['groupby']			=			'date';

		$memwhere['orderby']			=			'date';


		$memfield						= 			"sum(order_price) as num, from_unixtime(`time`,'%Y-%m-%d') as `date`";
		
	
		$totalOut 						= 			0;

		$out 						= 			array('names' => array(), 'values' => array());
		
		
		//净收入 综合names、values
		$names 							= 		 array_merge($in['names'], $out['names']);
		
		$names 							= 		 array_unique($names);
		
		usort($names,'t_sort');

		$inValues 						= 		  array();

		$outValues 					    = 		  array();
		
		$inK 							= 		  0;

		$outK 							= 		  0;

		foreach($names as $n){
			if(in_array($n, $in['names'])){

				$inValues [] 			=		 $in['values'][$inK];

				$inK ++;

			}else{

				$inValues [] 			= 		0;
			
			}

			if(in_array($n, $out['names'])){

				$outValues [] 			= 		$out['values'][$outK];

				$outK ++;
			
			}else{
				$outValues [] 			= 		0;
			}

			$netIncomeValues [] 		= 		end($inValues) - end($outValues);
		}

		$in['values'] 				    = 		$inValues;

		$out['values'] 					= 		$outValues;
		
		$dataGroupNames [] 				= 		'毛收入';
		
		$dataGroupNames [] 				= 		'支出';
		
		$dataGroupNames [] 				= 		'净收入';

		$values[] 						= 		array('name' => '毛收入','type' => $this->chartType,'data' => $in['values']);
		
		$values[] 						= 		array('name' => '支出','type' => $this->chartType,'data' => $out['values']);

		$values[] 						=   	array('name' => '净收入','type' => $this->chartType,'data' => $netIncomeValues);

		$data 							= 		array('title' => $title,'names' => $names, 'values' => $values, 'dataGroupNames' => $dataGroupNames);
		
		$this->yunset($data);

		$this->yunset(array('totalIn'=> $totalIn, 'totalOut' => $totalOut, 'totalNetIncome' => $totalIn - $totalOut));

		$this->yuntpl(array('admin/statis'));
		
	}

}
?>