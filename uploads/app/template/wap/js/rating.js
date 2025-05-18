function myFunction(_this) {
	_this.value = _this.value.replace(/[^0-9]/g, '');
}

/* 选择不同服务操作：会员、增值服务、单项购买 */
$("ul#rating_select").on("click", "li", function() {
	
	var id			=	$(this).attr('data-id');
	
	var meal		=	$(this).attr('data-meal');			//  1： 选择会员套餐服务，强制金额消费
	
	var price		=	$(this).attr('data-price');			//	单项购买金额
	var server		=	$(this).attr('data-server');		//	单项服务类型
	var single_id	=	$(this).attr('data-singleid');		//	单项购买金额
	
	var integral	=	$(this).attr('data-integral');		//	账户积分
	var	pro			=	$(this).attr('data-pro');			//	积分比例
		
	$(".rating_css_div").hide();	//	公用属性用来隐藏
	$("#rating_"+id).show();		//	根据id显示点击的服务
	
	$(".rating_css_div").find('ul li').removeClass("wap_buy_packageslist_cur");	//	会员套餐默认都是未选中
	
	//	增值服务默认第一个展开,其他的未展开								
	$(".detail_id").each(function(index,el){
		if(index > 0){
			$(el).hide();
		}else{
			$(el).show();
		}
	})
	$(".rating_css_div").find('ul li i').removeClass("wap_buy_p_i_s");			//	增值服务默认都是未选中
	
	
	$(".vip_pay_integral_div").hide();
	 
	
	$(this).addClass("wap_buy_packages_nav_cur"); 								//	点击li添加class
 	$(this).siblings('li').removeClass("wap_buy_packages_nav_cur"); 			//	删除兄弟li的class属性
	
	if(parseFloat(price) > 0){		//	单项购买点击 获取服务金额
		
		$("#order_price").html(price);
		$("#server_price").val(price);
	}else{
		
		$("#order_price").html(0);
		$("#server_price").val(0);
 	}
	
	if(server){						//	单项购买点击 获取服务类型
		$("#server").val(server);
		if(server == 'jobtop' || server == 'jobrec' || server == 'joburgent' || server == 'autojob' || server == 'partrec'){	//	职位推广服务
			if(parseFloat(price) > 0){
				
				$("#tg_price").val(price);
				$("#single_price").html(price);
				$("ul#job_tg").find('li').removeClass("wap_buy_packagescont_day_cur");
				$("ul#job_tg").find('.first').addClass("wap_buy_packagescont_day_cur");
				
			}
			$("#job_tg").show();
		}else{
			$("#job_tg").hide();
		}
		
		$(".integral_reduce").show();
	}else{
		
		$("#server").val('');
		$(".integral_reduce").hide();
	}
	
	if(single_id){
		
		$("#single_id").val(single_id);
	}else{
		
		$("#single_id").val('');
	}
	
	$("#single_integral").html(accMul(price, pro));
	
	if(integral && pro){	// 积分模式
		
		var integral_need	=	accMul(price, pro);
		
		$("#order_integral").html(accMul(price, pro));
		
		$("#xdays").val('');
		
		if(parseInt(integral) >= parseInt(integral_need)){	//	积分充足的情况
			
			$(".integral_buy_div").show();
			$(".integral_pay_div").hide();
			
			$(".pay_style_div").hide();
			
			$(".order_price_div").hide();
			$(".order_integral_div").show();
			
 		}else{
 			
 			var integral_cj	=	accSub(parseInt(integral_need), parseInt(integral));	//	还需充值积分
 			 			
 			if(parseInt(integral_min) < parseInt(integral_cj)){
 				
 				$('#integral_int').val(parseInt(integral_cj));
 				$('#order_price').html(accDiv(parseInt(integral_cj), pro));
 			}else{
 				
 				$('#integral_int').val(parseInt(integral_min));
 				$('#order_price').html(accDiv(parseInt(integral_min), pro));
 			}
 			
 			$(".integral_buy_div").hide();
			$(".integral_pay_div").show();
			
			$(".pay_style_div").show();
			
			$(".order_price_div").show();
			$(".order_integral_div").hide();
 			
 		}
		
	}else{
		
		if(document.getElementById("integral_switch_single")){	
	 		document.getElementById("integral_switch_single").classList.remove('mui-active');
	 	}
	 	
	 	$('#integral_dk_single_div').hide();
		
	 	$('#integral_dk').val('');
		
		$(".order_price_div").show();
		
		if(meal == 1 && id < 3){
			$(".integral_dk_div").hide();
		}else{
			$(".integral_dk_div").show();
		}
		
		$(".order_integral_div").hide();
		
		$(".pay_style_div").show();
		
	}
	
})

/* 选择不同会员等级操作 */
$("ul#vip_rating_1").on("tap", "li", function() {
	
	var id			=	$(this).attr('data-id');		//	会员等级ID
	var price		=	$(this).attr('data-price');		//	会员服务金额
	var server		=	$(this).attr('data-server');	//	服务类型
	
	var integral	=	$(this).attr('data-integral');	//	账号积分
	var pro			=	$(this).attr('data-pro');		//	积分比例

	var meal		=	$(this).attr('data-meal');		//  1： 选择会员套餐服务，强制金额消费
	
	$(this).addClass("wap_buy_packageslist_cur"); 		// 	点击li添加class
 	$(this).siblings('li').removeClass("wap_buy_packageslist_cur");		//	删除兄弟li的class属性
 	
 	$("#server").val(server);
 	$("#single_id").val(id);
 	$("#integral_dk").val('');
 	
 	if(integral && pro){	// 积分模式
 		
 		var integral_need	=	accMul(parseFloat(price), parseInt(pro));
 		
 		if(parseInt(integral) >= parseInt(integral_need)){
 			
 			$("#order_integral").html(integral_need);
			$(".pay_style_div").hide();
			$(".order_price_div").hide();
			$(".order_integral_div").show();
			
  		}else{
  			$(".pay_style_div").show();
  			$(".order_integral_div").hide();
  			$(".order_price_div").show();
  			
  			$(".integral_buy_div").hide();
 			$(".integral_pay_div").show();
 			
 			var	integral_need	=	 accMul(parseFloat(price), parseInt(pro));
 			
 			$("#single_integral").html(integral_need);
 			
 			var integral_cj		=	accSub(parseInt(integral_need), parseInt(integral));
 			
 			if(parseInt(integral_min) > parseInt(integral_cj)){
 				
 				$("#integral_int").val(integral_min);
 				$("#order_price").html(accDiv(parseInt(integral_min), parseInt(pro)));
 			}else{
 				
 				$("#integral_int").val(integral_cj);
 				$("#order_price").html(accDiv(parseInt(integral_cj), parseInt(pro)));
 			}
 		}
 		
 	}else{
 		
 		$("#order_price").html(price);
 		$("#server_price").val(price);
 		
 		$(".order_price_div").show();
		$(".order_integral_div").hide();

		if(meal == 1){
			$(".integral_dk_div").hide();
		}else{
			$(".integral_dk_div").show();
		}
		
		$(".pay_style_div").show();
 	}
})

$("ul#vip_rating_2").on("tap", "li", function() {
	
	var id			=	$(this).attr('data-id');		//	会员等级ID
	var price		=	$(this).attr('data-price');		//	会员服务金额
	var server		=	$(this).attr('data-server');	//	服务类型
	
	var integral	=	$(this).attr('data-integral');	//	账号积分
	var pro			=	$(this).attr('data-pro');		//	积分比例

	var meal		=	$(this).attr('data-meal');		//  1： 选择会员套餐服务，强制金额消费
	
	$(this).addClass("wap_buy_packageslist_cur"); 		// 	点击li添加class
 	$(this).siblings('li').removeClass("wap_buy_packageslist_cur");		//	删除兄弟li的class属性
 	
 	$("#server").val(server);
 	$("#single_id").val(id);
 	$("#integral_dk").val('');
	
 	if(integral && pro){	// 积分模式
 		
 		var integral_need	=	accMul(parseFloat(price), parseInt(pro));
 		
 		if(parseInt(integral) >= parseInt(integral_need)){
 			
 			$("#order_integral").html(integral_need);
			
			$(".pay_style_div").hide();
			
			$(".order_price_div").hide();
			$(".order_integral_div").show();
			
  		}else{
  			$(".pay_style_div").show();
  			$(".order_integral_div").hide();
  			$(".order_price_div").show();
  			
  			$(".integral_buy_div").hide();
 			$(".integral_pay_div").show();
 			
 			var	integral_need	=	 accMul(parseFloat(price), parseInt(pro));
 			
 			$("#single_integral").html(integral_need);
 			
 			var integral_cj		=	accSub(parseInt(integral_need), parseInt(integral));
 			
 			if(parseInt(integral_min) > parseInt(integral_cj)){
 				
 				$("#integral_int").val(integral_min);
 				$("#order_price").html(accDiv(parseInt(integral_min), parseInt(pro)));
 			}else{
 				
 				$("#integral_int").val(integral_cj);
 				$("#order_price").html(accDiv(parseInt(integral_cj), parseInt(pro)));
 			}
 		}
 		
 	}else{
 		
 		$("#order_price").html(price);
 		$("#server_price").val(price);
 		
 		$(".order_price_div").show();
		$(".order_integral_div").hide();

		if(meal == 1){
			$(".integral_dk_div").hide();
		}else{
			$(".integral_dk_div").show();
		}
		
		$(".pay_style_div").show();
 	}
})

/* 选择不同增值服务操作 */
$("ul#vip_add").on("tap", "li", function() {

	$(this).find('.detail_id').show(); // 点击li展示详情
 	$(this).siblings('li').find('.detail_id').hide(); // 兄弟li的隐藏详情
 	$(this).siblings('li').find('.detail_id').find('li i').removeClass("wap_buy_p_i_s");
 	
})

/* 选择不同增值服务内容详情操作 */
$("ul.detail_id").on("tap", "li", function() {

	$(this).find('i').addClass("wap_buy_p_i_s");
	$(this).siblings('li').find('i').removeClass("wap_buy_p_i_s"); // 兄弟li的隐藏详情
	
	var id		=	$(this).attr('data-id');
	var server	=	$(this).attr('data-server');
	var price	=	$(this).attr('data-price');
	
	var integral=	$(this).attr('data-integral');
	var pro		=	$(this).attr('data-pro');

	$("#server").val(server);
 	$("#single_id").val(id);
 	$("#integral_dk").val('');
	
 	if(integral && pro){	// 积分模式
 		
 		var integral_need	=	accMul(parseFloat(price), parseInt(pro));
 		
 		if(parseInt(integral) >= parseInt(integral_need)){
 			
 			$("#order_integral").html(parseInt(integral_need));
			
			$(".pay_style_div").hide();
			
			$(".order_price_div").hide();
			$(".order_integral_div").show();
			
  		}else{
  			$(".pay_style_div").show();
  			$(".order_integral_div").hide();
  			$(".order_price_div").show();
  			
  			$(".integral_buy_div").hide();
 			$(".integral_pay_div").show();
 			
 			var	integral_need	=	 accMul(parseFloat(price), parseInt(pro));
 			
 			$("#single_integral").html(parseInt(integral_need));
 			
 			var integral_cj		=	accSub(parseInt(integral_need), parseInt(integral));
 			
 			if(parseInt(integral_min) > parseInt(integral_cj)){
 				
 				$("#integral_int").val(integral_min);
 				$("#order_price").html(accDiv(parseInt(integral_min), parseInt(pro)));
 			}else{
 				
 				$("#integral_int").val(integral_cj);
 				$("#order_price").html(accDiv(parseInt(integral_cj), parseInt(pro)));
 			}
 		}
 		
 	}else{
 		
 		$("#order_price").html(price);
 		$("#server_price").val(price);
 		
 		$(".order_price_div").show();
		$(".order_integral_div").hide();
		
		$(".pay_style_div").show();
 	}
})
 
/* 选择不同的推广天数 */
$("ul#job_tg").on("tap", "li", function() {

	var integral		=	$("ul#job_tg").attr('data-integral');
	var pro				=	$("ul#job_tg").attr('data-pro');

	$(this).siblings('li').removeClass("wap_buy_packagescont_day_cur"); //	删除兄弟li的class属性
 	$(this).addClass("wap_buy_packagescont_day_cur");					//	删除兄弟li的class属性
	
 	var days			=	$(this).attr('data-days');
	var tg_price		=	$("#tg_price").val();
	var order_price		=	accMul(parseFloat(tg_price), parseInt(days));	//	选择天数下单金额

	if(days != '0'){
		$("#xdays").val('');
		$("#days").val(days);
	}else{
		$("#days").val(0);
	}
	
	if(integral && pro){
		
		var integral_need	=	accMul(parseFloat(order_price), parseInt(pro));
		
		if(parseInt(integral_need) > parseInt(integral)){
			
			$("#single_integral").html(integral_need);
			
			$(".integral_buy_div").hide();
			
			$(".integral_pay_div").show();
			
			$(".pay_style_div").show();
			 
			$(".order_integral_div").hide();
		
			$(".order_price_div").show();
			 
			var integral_cj	=	accSub(parseInt(integral_need), parseInt(integral));
			
			$('#single_integral').html(integral_need);
			
			if(parseInt(integral_min) < parseInt(integral_cj)){
 				
 				$('#integral_int').val(parseInt(integral_cj));
 				
 				$('#order_price').html(accDiv(parseInt(integral_cj), pro));
 				
 			}else{
 				
 				$('#integral_int').val(parseInt(integral_min));
 				
 				$('#order_price').html(accDiv(parseInt(integral_min), pro));
 			}
			 
			
		}else{
			
			$(".integral_buy_div").show();
			$(".integral_pay_div").hide();
			
			$(".pay_style_div").hide();
			
			$(".order_integral_div").show();
			$(".order_price_div").hide();
			
			
			$("#single_integral").html(integral_need);
			$("#order_integral").html(integral_need);
		}
		
	}else{
	
		$("#order_price").html(order_price);
		$("#server_price").val(order_price);
		$("#single_price").html(order_price);
		$("#integral_dk").val('');
		
		$(".order_price_div").show();
		$(".order_integral_div").hide();
		
		$(".pay_style_div").show();
	}
	
})
 
/* 自定义天数 */
function checkDays(){
	
	var integral	=	$("ul#job_tg").attr('data-integral');
	var pro			=	$("ul#job_tg").attr('data-pro');
	
	var xdays		=	$("#xdays").val();
	var tg_price	=	$("#tg_price").val();
	
	if(parseInt(xdays) > 0){
		
		var order_price	=	accMul(parseFloat(tg_price), parseInt(xdays));	//	自定义天数下单金额
		
		if(integral && pro){
			
			var integral_need	=	accMul(parseFloat(order_price), parseInt(pro));
			
			if(parseInt(integral_need) > parseInt(integral)){
				
				$("#single_integral").html(integral_need);
				
				$(".integral_buy_div").hide();
				
				$(".integral_pay_div").show();
				
				$(".pay_style_div").show();
				 
				$(".order_integral_div").hide();
			
				$(".order_price_div").show();
				 
				var integral_cj	=	accSub(parseInt(integral_need), parseInt(integral));
				
				$('#single_integral').html(integral_need);
				
				if(parseInt(integral_min) < parseInt(integral_cj)){
	 				
	 				$('#integral_int').val(parseInt(integral_cj));
	 				
	 				$('#order_price').html(accDiv(parseInt(integral_cj), pro));
	 				
	 			}else{
	 				
	 				$('#integral_int').val(parseInt(integral_min));
	 				
	 				$('#order_price').html(accDiv(parseInt(integral_min), pro));
	 			}
				 
				
			}else{
				
				$(".integral_buy_div").show();
				$(".integral_pay_div").hide();
				
				$(".pay_style_div").hide();
				
				$(".order_integral_div").show();
				$(".order_price_div").hide();
				
				
				$("#single_integral").html(integral_need);
				$("#order_integral").html(integral_need);
			}
			
		}else{
			
			$("#order_price").html(order_price);
			$("#server_price").val(order_price);
			$("#single_price").html(order_price);
		}
		
		$("#days").val(xdays);
		
	}else{
		
		$("#order_price").html(0);
		$("#server_price").val(0);
		$("#single_price").html(0);
		$("#days").val(0);
		
		layermsg('请填写推广天数');
		return false;
	}
}

if(document.getElementById('integral_switch_single')) {
	document.getElementById('integral_switch_single').addEventListener('toggle', function(event) {
		
		var server_price	=	$("#server_price").val();
		
		if(event.detail.isActive) {
			
			document.getElementById('integral_dk_single_div').style.display = 'block';
			
		} else {
			
			if(document.getElementById('integral_dk_single_div')) { // 兑换积分部分隐藏
				
				document.getElementById('integral_dk_single_div').style.display = 'none';
				document.getElementById('integral_dk').value = '';
				
				$(".order_price_div").show();
				$(".order_integral_div").hide();
				$("#order_price").html(server_price);
				$(".pay_style_div").show();
				
			}
		}
	});
}

/* 积分充值 */
function checkIntegralPay(integral, integralMin, integralPro){
	
	var single_integral	=	$('#single_integral').html(); 	//	单项购买所需积分
	var integral		=	integral;						//	目前拥有积分

	var integral_need	=	accSub(parseInt(single_integral), parseInt(integral));	//	单选购买减去账号积分，还需要积分数量
	var integral_min	=	integralMin;	//	站点最低充值积分
	var integral_pro	=	integralPro;	//	站点积分比例
	
	if(parseInt(integral_need) < parseInt(integral_min)){
		
		var	minJifen	=	integral_min;
	}else{
		
		var	minJifen	=	integral_need;
	}
	
	var integral_int	=	$('#integral_int').val();
	
	if(parseInt(integral_int) < parseInt(minJifen)){
		
		$('#integral_int').val(minJifen);
		
		$('#order_price').html(accDiv(minJifen, integral_pro));
		
	}else if(integral_int != ''){
		
		$('#order_price').html(accDiv(parseInt(integral_int), integral_pro));
		
	} else{
		$('#integral_int').val(minJifen);
		$('#order_price').html(accDiv(minJifen, integral_pro));
	}
	
}

/* 抵扣积分输入操作 */
function checkIntegralDK(integral, pro){
	var integral, pro;
	
	var integral_dk		=	$("#integral_dk").val();		// 	抵扣输入积分
	var server_price	=	$("#server_price").val();		//	所选服务金额 
	 
 	if(server_price == '' || parseFloat(server_price) == 0){
		
		$("#integral_dk").val('');
		layermsg('请先选择购买服务！');
		return false;
	}
	var server_price		=	server_price;	// 	下单金额
	var order_price			=	server_price;
	var integral_need		=	accMul(parseFloat(server_price), parseInt(pro));	// 	套餐金额转积分
	
	if(parseInt(integral_need) == 0){
    	
    	var integral_need	=	1;
    	
    }else if(integral_need > parseInt(integral_need)){
    	
    	var integral_need 	= 	accAdd(parseInt(integral_need), 1);
    }
	
	if(integral_dk){
 		
		if(parseInt(integral_dk) > 0){
    		$("#integral_dk").val(parseInt(integral_dk));
    	}
		
	    if(parseInt(integral) >= parseInt(integral_need)) { 					// 	拥有积分充足
	    	
	        if(parseInt(integral_dk) >= parseInt(integral_need)) { 				// 	输入抵扣积分超过购买积分
	        	
	            $("#integral_dk").val(parseInt(integral_need));					//	抵扣积分变更最大所需积分
	            
	            order_price	=	0 ;	// 	抵扣积分后所需金额
	            
	        } else {
	        	
	            order_price	= 	accSub(parseFloat(server_price), accDiv(parseInt(integral_dk), parseInt(pro)));	//	抵扣积分后所需金额
	            
	        }	
	    
	    } else {																//	拥有积分不充足
	    	
	        if(parseInt(integral_dk) > parseInt(integral)) {					//	抵扣所有积分
	        	
	            $("#integral_dk").val(parseInt(integral));
	            
	            order_price	= 	accSub(parseFloat(server_price), accDiv(integral, parseInt(pro)));		//	抵扣积分后所需金额
	        } else {
	        	
	            order_price	= 	accSub(parseFloat(server_price), accDiv(integral_dk, parseInt(pro)));	//	抵扣积分后所需金额
	        }
	    }
    }
	
	
	/* 根据抵扣后金额，判断支付方式 */
	if(order_price > 0){
		$(".order_price_div").show();
		$(".order_integral_div").hide();
		$(".pay_style_div").show();
		$("#order_price").html(order_price);
	}else{
		$(".order_integral_div").show();
		$(".order_price_div").hide();
		$(".pay_style_div").hide();
		$("#order_integral").html(parseInt(integral_need));
	}

}

/* 支付方式选择  */
function paycheck(type){
	
	var type;
	
	$("#paytype").val(type);
	$(".wap_buy_packages_fk_xz").removeClass("wap_buy_packages_fk_xz_cur");
	
	if(type == 'wxpay'){
		
		$(".wxpay").addClass("wap_buy_packages_fk_xz_cur");
	}else if(type == 'alipay'){
		
		$(".alipay").addClass("wap_buy_packages_fk_xz_cur");
	}
	
}

/* 积分支付购买 */
function integralBuy(){
	layer_load();
	var server 		= 	$("#server").val();
	var single_id	= 	$("#single_id").val();
	
	var pData = {server: server};
	if(server == 'issuejob'){
		
		var rurl  	= 	wapurl + 'member/index.php?c=jobcolumn';
		
	}else if(server == 'jobtop'){
		
		var days	=	$("#days").val();
		var rurl  	= 	wapurl + 'member/index.php?c=job';
		pData.zdjobid = single_id;
		pData.days = days;
		
	}else if(server == 'jobrec'){
		
		var days	=	$("#days").val();
		var rurl  	= 	wapurl + 'member/index.php?c=job';
		pData.recjobid = single_id;
		pData.days = days;
		
	}else if(server == 'joburgent'){
		
		var days	=	$("#days").val();
		var rurl  	= 	wapurl + 'member/index.php?c=job';
		pData.ujobid = single_id;
		pData.days = days;
		
	}else if(server == 'sxjob'){
		
		var rurl  	= 	wapurl + 'member/index.php?c=job';
		pData.sxjobid = single_id;
		
	}else if(server == 'sxpart'){
		
		var rurl  	= 	wapurl + 'member/index.php?c=part';
		pData.sxpartid = single_id;
		
	}else if(server == 'partrec'){
		
		var days	=	$("#days").val();
		var rurl  	= 	wapurl + 'member/index.php?c=part';
		pData.recpartid = single_id;
		pData.days = days;
		
	}else if(server == 'downresume'){
		
		var rurl  	= 	wapurl + 'index.php?c=resume&a=show&id=' + single_id + '&down=1';
		pData.eid = single_id;
		
	}else if(server == 'invite'){
		
		var rurl  	=	wapurl + 'index.php?c=resume&a=invite&uid=' + single_id + '&invite=1';
		
	}else if(server == 'zph'){
		
		var bid   	= 	$("#bid").val();
		var rurl  	=	wapurl + 'index.php?c=zph&a=reserve&id=' + single_id + '&zph=1';
		pData.zid = single_id;
		pData.bid = bid;
	}else if(server == 'vip'){
		
		var rurl  	=	wapurl + 'member/index.php?c=com';
		pData.ratingid = single_id;
	}else if(server == 'pack'){
		
		var rurl  	=	wapurl + 'member/index.php?c=com';
		pData.tcid = single_id;
	}else if(server == 'autojob'){
		
		var days	=	$("#days").val();
		var rurl  	= 	wapurl + 'member/index.php?c=job';
		pData.jobautoids = single_id;
		pData.days = days;
		
	}
	var ajaxUrl		= 	'index.php?c=dkzf';
	$.post(ajaxUrl, pData, function(data){
		layer.closeAll('loading');
		var data = eval('(' + data + ')');
		
		if(data.error == '0') { // 成功
			
			layermsg(data.msg, 2, function() {
				location.href = rurl;
			});
			
			return false;
		} else {
			
			layermsg(data.msg, 2, function() {
				location.reload(true);
			});
			
			return false;
		}
	});
}

/* 金额支付购买 */
function orderBuy(){
	
	var server 		= $("#server").val();
	var paytype 	= $("#paytype").val();
	
	if(paytype == ''){
		
		layermsg('请选择支付方式！');
		return false;
	}
	if(server == ''){
		
		layermsg('请选择购买服务！');
		return false;
	}
	var single_id = $("#single_id").val()

	var integral_dk	= $("#integral_dk").val();
	
	var integral_int = $("#integral_int").val();
		
	var ajaxUrl = 'index.php?c=getOrder';
	
	var pData 	= 	{paytype: paytype, server: server, price_int: integral_int,dkjf: integral_dk};
	
	if(server == 'issuejob' || server == 'invite'){
		
		
	}else if(server == 'sxjob'){
		
		pData.sxjobid = single_id;
		
	}else if(server == 'jobtop'){
		
		var days	=	$("#days").val();
		pData.days = days;
		pData.zdjobid = single_id;
		
	}else if(server == 'jobrec'){
		
		var days	=	$("#days").val();
		pData.days = days;
		pData.recjobid = single_id;
		
	}else if(server == 'joburgent'){
		
		var days	=	$("#days").val();
		pData.days = days;
		pData.ujobid = single_id;
		
	}else if(server == 'sxpart'){
		
		pData.sxpartid = single_id;
		
	}else if(server == 'partrec'){
		
		var days	=	$("#days").val();
		pData.days = days;
		pData.recpartid = single_id;
		
	}else if(server == 'downresume'){
		
		pData.eid = single_id;
		
	}else if(server == 'zph'){
		var bid		=	$('#bid').val();
		pData.zid = single_id;
		pData.bid = bid;
		
	}else if(server == 'vip'){
		
		pData.ratingid = single_id;
		
	}else if(server == 'pack'){
		
		pData.tcid = single_id;
		
	}else if(server == 'autojob'){
		
		var days	=	$("#days").val();
		pData.days = days;
		pData.jobautoids = single_id;
		
	}
	$.post(ajaxUrl, pData, function(data){
		
		var data = eval('(' + data + ')');
		
		if(data.error == '0') { // 下单成功
			
			layermsg(data.msg, 2, function() {
				location.href = data.url;
			});
			return false;
		} else {
			
			layermsg(data.msg, 2, function() {
				location.reload(true);
			});
			return false;
		}
	});
}