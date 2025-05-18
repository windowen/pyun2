/* 选择不同会员/增值包/单次购买  */
$(function(){
	$("ul#rating_type").on("click", "li", function() {
        $(this).addClass("buymeal_tit_cur");
        $(this).siblings('li').removeClass("buymeal_tit_cur");

        var rating = $(this).attr("rating");
			
        if(rating) {
        	$(".rating_type").hide();
        	$("#rating_"+rating).show();
        } 
	})
})

/* 会员套餐滚动 */
layui.use('carousel', function() {
	var carousel = layui.carousel;
	
	// 套餐会员
	carousel.render({
		elem : '#vip_1',
		width : '748px',// 设置容器宽度
		arrow : 'hover' // 悬停显示箭头
		,autoplay:true // 自动切换关闭
	});
	// 时间会员
	carousel.render({
		elem : '#vip_2',
		width : '748px',	
		autoplay:true // 自动切换关闭
 	});
});

//加减乘除
function accAdd(arg1,arg2){
	if (isNaN(arg1)) {
		arg1 = 0;
	}
	if (isNaN(arg2)) {
		arg2 = 0;
	}
	arg1 = Number(arg1);
	arg2 = Number(arg2);
	var r1, r2, m, c;
	try {
		r1 = arg1.toString().split(".")[1].length;
	}
	catch (e) {
		r1 = 0;
	}
	try {
		r2 = arg2.toString().split(".")[1].length;
	}
	catch (e) {
		r2 = 0;
	}
	c = Math.abs(r1 - r2);
	m = Math.pow(10, Math.max(r1, r2));
	if (c > 0) {
		var cm = Math.pow(10, c);
		if (r1 > r2) {
			arg1 = Number(arg1.toString().replace(".", ""));
			arg2 = Number(arg2.toString().replace(".", "")) * cm;
		} else {
			arg1 = Number(arg1.toString().replace(".", "")) * cm;
			arg2 = Number(arg2.toString().replace(".", ""));
		}
	} else {
		arg1 = Number(arg1.toString().replace(".", ""));
		arg2 = Number(arg2.toString().replace(".", ""));
	}
	return (arg1 + arg2) / m;
}
function accSub(arg1,arg2){
	arg1 = $.trim(arg1);
	arg2 = $.trim(arg2);
	return accAdd(arg1,-arg2); 
} 
function accMul(arg1, arg2) {
	arg1 = $.trim(arg1);
	arg2 = $.trim(arg2);
	var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
	try { m += s1.split(".")[1].length } catch (e) { }
	try { m += s2.split(".")[1].length } catch (e) { }
	return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
}
function accDiv(arg1,arg2){ 
	arg1 = $.trim(arg1);
	arg2 = $.trim(arg2);
	var t1=0,t2=0,r1,r2;    
	try{t1=arg1.toString().split(".")[1].length}catch(e){}    
	try{t2=arg2.toString().split(".")[1].length}catch(e){}    
	with(Math){        
		r1=Number(arg1.toString().replace(".",""));        
		r2=Number(arg2.toString().replace(".",""));        
		return (r1/r2)*pow(10,t2-t1);    
	}
}

function myFunction(_this) {
    _this.value = _this.value.replace(/[^0-9]/g, '');
}

/* 购买会员套餐 */
function buyVip(id){
	
	$.post(weburl + '/index.php?m=ajax&c=getVipPrice',{id : id}, function (data){
		
		if(data){
			
			var data	= 	eval('(' + data + ')');
			var style	=	data.style;
			
			$('#pay_server').val('vip');
			$("#ratingid").val(data.id);
			$("#service_name_"+style).html(data.name);
			
			if(data.yh_price){
				var price = data.yh_price;
				$("#service_price_"+style).html(data.yh_price);
				$("#service_origin_price_"+style).html(data.service_price);
				$("#vip_order_price_"+style).html(data.price);
				$("#integral_int_vip").val(data.yh_price);
 			}else{
				var price = data.service_price;
				$('#origin_tag_'+style).hide();
				$("#service_price_"+style).html(data.service_price);
				$("#vip_order_price_"+style).html(data.price);
				$("#integral_int_vip").val(data.service_price);

			}
			// 积分充值购买,不能使用优惠券
			var online = $("#integral_online").val();
			if(online != '3'){
				
			}else{
				// 积分充值购买有最低充值限制
				var minJf = $("#integral_min").val(),
					pro = $("#integral_pro").val();
				if (parseInt(minJf) > parseInt(price)){
					$("#integral_int_vip").val(minJf);
					var pro = $("#integral_pro").val();
					$("#vip_order_price_"+style).text(accDiv(minJf, pro));
				}
			}
			
			$("#integral_dk_vip").val('');
			
			
			$(".price_1").show();
			
			$("#integral_dk_div").show();
			
 			layer.open({
				type: 1,
				title: '购买会员套餐服务',
				closeBtn: 1,
				border: [10, 0.3, '#000', true],
				area: ['auto', 'auto'],
				content: $("#buyVipDiv_"+style)
			});
		}
	});
}

/* 购买增值服务 */
function buyPack(id){
	
	$.post(weburl + '/index.php?m=ajax&c=getPackPrice',{packid : id}, function (data){
		
		if(data){
			
			var data	= 	eval('(' + data + ')');
			var style	=	data.style;
			
			$('#pay_server').val('pack');
 			$("#comserviceid").val(data.tid);
			$("#service_name_"+style).text(data.name);
			
			if(data.yh_price){
				var price = data.yh_price;
				$("#service_price_"+style).text(data.yh_price);
				$("#service_origin_price_"+style).text(data.service_price);
				$("#vip_order_price_"+style).text(data.price);
				$("#integral_int_vip").val(data.yh_price);
 			}else{
				var price = data.service_price;
				$('#origin_tag_'+style).hide();
				$("#service_price_"+style).text(data.service_price);
				$("#vip_order_price_"+style).text(data.price);
				$("#integral_int_vip").val(data.service_price);
			}
			
			if(style == '1'){
				$("#subject_"+style).val('购买增值服务');
			}else if(style == '3'){
				$("#subject_"+style).val('充值'+pricename+'购买增值服务');
			}
			
			// 积分充值购买,不能使用优惠券
			var online = $("#integral_online").val();
			var meal = $("#member_meal").val();
			if(online != '3' || meal!='0'){
				
			}else{
				// 积分充值购买有最低充值限制
				var minJf = $("#integral_min").val();

				if (parseInt(minJf) > parseInt(price)){
					$("#integral_int_vip").val(minJf);
					var pro = $("#integral_pro").val();
					$("#vip_order_price_"+style).text(accDiv(minJf, pro));
				}
			}
			
			$("#integral_dk_vip").val('');
			
			
			$(".price_1").show();
			
			$("#integral_dk_div").show();
			
 			layer.open({
				type: 1,
				title: '购买会员增值服务',
				closeBtn: 1,
				border: [10, 0.3, '#000', true],
				area: ['auto', 'auto'],
				content: $("#buyVipDiv_"+style)
			});
		}
	});
}

/* 积分购买*/
function integralBuy() {
	
	var index = layer.load('执行中，请稍候...', 0);
	var server = $('#pay_server').val();
	var pData =	{server: server};
	
	if(server == 'issuejob' || server == 'createson' || server == 'invite'){
		
	}else if(server == 'sxjob'){
		pData.sxjobid = $("#jobids").val();
	}else  if(server == 'sxpart'){
		pData.sxpartid = $("#partids").val();
	}else if(server == 'sxltjob'){
		pData.sxltjobid = $("#jobids").val();
	}else if(server == 'downresume'){
    	pData.eid = $("#eid").val();
    }else if(server == 'zph'){
    	pData.zid = $("#zid").val();
    	pData.bid = $("#bid").val();
		var jobid = '';
		$("input[name=checkbox_job]:checked").each(function(){
			if(jobid == ""){
				jobid = $(this).val();
			}else{
				jobid = jobid+","+$(this).val();
			}
		});
		pData.jobid = jobid;
    }else if(server == 'vip'){
		pData.ratingid = $('#ratingid').val(); // 会员套餐ID
	} else if (server == 'pack'){
		pData.tcid  =  $('#comserviceid').val(); // 增值包id
	}else if(server == 'jobtop' || server == 'jobrec' || server == 'partrec' || server == 'joburgent' || server == 'autojob'){
		var jobid 	= 	$("#promoteid").val();
	   	var days	=	$("input[name='days']:checked").val(); // 选中的天数
		var xdays	=	$('#xdays').val(); //自定义天数
		pData.days = days;
		pData.xdays = xdays;
		if(server == 'jobtop'){
			pData.zdjobid = jobid;
		} else if(server == 'jobrec'){
			pData.recjobid = jobid;
	    } else if(server == 'partrec'){
	    	pData.recpartid = jobid;
	    } else if(server == 'joburgent'){
	    	pData.ujobid = jobid;
	    } else if(server == 'autojob'){
			var	jobautoids	=	$('#jobautoids').val();
			pData.jobautoids = jobautoids;
	    }
    }

    var url = weburl + "/index.php?m=ajax&c=integralBuy";
	
    $.post(url, pData, function(data) {
        if(data) {
            layer.closeAll();
            data = eval('(' + data + ')');
            if(data.error == 1) {
                if(data.url) {
                    layer.msg(data.msg, 2, 8, function() {
                        window.location.href = data.url;
                    });
                } else {
                    layer.msg(data.msg, 2, 8);
                }
            } else if(data.error == 0) {
				
				layer.msg(data.msg, 2, 9, function() {
				    window.location.reload();
				});
				
            }
        }
    })
}
/* 金额付款操作 单项购买*/
function payVip(paytype, formId, type){
	
	var index	= 	layer.load('执行中，请稍候...', 0);
	if(type == 'all'){
		$('#pay_type_vip').val(paytype);
		var integral_dk		=	$('#integral_dk_vip').val();	// 	抵扣积分
		var integral_int	=	$("#integral_int_vip").val();	//	充值积分
	}else{
		$('#pay_type').val(paytype);
		var integral_dk = $("#server_integral_dk").val();
		var integral_int = $("#integral_int").val();
	}
	// 获取服务类型
	var server = $('#pay_server').val();
	
	var pData =	{server: server, dkjf: integral_dk, price_int: integral_int, paytype : paytype};
	
	if(server == 'issuejob' || server == 'createson' || server == 'invite' ){
    	
    } else if(server == 'sxjob'){
    	
		pData.sxjobid = $('#jobids').val();
    }else if(server == 'sxpart'){
    	
		pData.sxpartid = $('#partids').val();
    } else if(server == 'sxltjob'){
    	
		pData.sxltjobid = $('#jobids').val();
    } else if(server == 'downresume'){
		pData.eid = $('#eid').val();
	} else if(server == 'zph'){
		pData.zid = $("#zid").val();
		pData.bid = $("#bid").val();
		var jobid = '';
		$("input[name=checkbox_job]:checked").each(function(){
			if(jobid == ""){
				jobid = $(this).val();
			}else{
				jobid = jobid+","+$(this).val();
			}
		});
		pData.jobid = jobid;
	} else if(server == 'vip'){
		pData.ratingid = $('#ratingid').val(); // 会员套餐ID
	} else if (server == 'pack'){
		pData.tcid = $('#comserviceid').val(); // 增值包id
	}else if(server == 'jobtop' || server == 'jobrec' || server == 'partrec' || server == 'joburgent' || server == 'autojob'){
		var jobid 	= 	$("#promoteid").val();
	   	var days	=	$("input[name='days']:checked").val(); // 选中的天数
		var xdays	=	$('#xdays').val(); //自定义天数
		pData.days = days;
		pData.xdays = xdays;
		if(server == 'jobtop'){
			pData.zdjobid = jobid;
		} else if(server == 'jobrec'){
			pData.recjobid = jobid;
	    } else if(server == 'partrec'){
	    	pData.recpartid = jobid;
	    } else if(server == 'joburgent'){
	    	pData.ujobid = jobid;
	    } else if(server == 'autojob'){
			var	jobautoids	=	$('#jobautoids').val();
			pData.jobautoids = jobautoids;
	    }
    }
	
	$.ajax({
        async: false, //设置ajax同步  
        type: 'POST',
        global: false,
     	url: weburl + "/index.php?m=ajax&c=addOrder",
        data: pData,
        success: function(data) {
            layer.close(index);
            var data = eval('(' + data + ')');

            if(data.error == 1) {
                layer.msg(data.msg, 2, 8);
                return false;
            } else if(data.error == 0) {
				if(server == 'vip' || server == 'pack'){
					$('#order_vip_id').val(data.orderid);
				}else{
					$('#order_single_id').val(data.orderid);
				}
                $('#orderid').val(data.id);
                //提交表单
                $('#'+formId).submit();
            }
        }
    });
}
/* 订单提交，form表单检测 */
function payforms(otype) {
	
	if(otype == 'single'){
		var pay_type = $("#pay_type").val(); // 单项购买
	}else if(otype == 'vip'){
		var pay_type = $("#pay_type_vip").val(); // 会员/增值包购买
	}
	
	var server	=	$("#pay_server").val();
	
    if(pay_type == '') {
    
    	layer.msg('请选择支付方式！', 2, 8);

    } else if(pay_type == 'bank') {
		var orderId = $("#orderid").val();
		window.location.href = weburl+'/member/index.php?c=payment&id='+orderId+'&paytype=bank';
		return false;
	} else {
		var orderId = $("#orderid").val();
		$("#repaya").attr('href',weburl+'/member/index.php?c=payment&id='+orderId);
        $.layer({
            type: 1,
            title: '提示',
            closeBtn: [0, true],
            border: [10, 0.3, '#000', true],
            area: ['450px', '280px'],
            page: {
                dom: "#payshow"
            }
        });
    }
}
/*
type: 服务类型
jifen: 需要的积分(刷新职位等存在非单个时需要)
price: 服务价格(刷新职位等存在非单个时需要)
*/
function server_single(type,jifen,price){
	
	$("#pay_server").val(type);
	var server = server_single_subject(type),
		pro = $("#integral_pro").val(),
		online = $("#integral_online").val(),
		integral = $("#user_integral_all").val();

	if(!price){
		price = server.price;
	}
	if(type=='sxltjob'||type=='sxpart'||type=='sxjob'){
    	type = 'sxjob';
    }else if(type=='partrec'){
    	type = 'jobrec';
    }
	//判断后台是否开启此服务的单项购买
	if(type!='autojob' && single_can_arr.indexOf(type)==-1){
		$('#singleTab').hide();
		$('#singleTab').attr('ishide',1);
	}else{
		$('#singleTab').show();
		$('#singleTab').attr('ishide',0);
	}
 
	if(online == 3){
		if(!jifen){
			jifen =	accMul(parseFloat(price), parseInt(pro));
		}
		var minJf =	$("#integral_min").val();
		
		if(parseInt(jifen) > parseInt(integral)){
			
			$('#integral_buy').hide();
			
			if(parseInt(jifen) < parseInt(minJf)){
				
				$('#integral_int').val(minJf);
				$('#price_int').text(accDiv(minJf, pro));
			}else{
				$('#integral_int').val(jifen);
				$('#price_int').text(accDiv(jifen, pro));
			}
			
			$("#single_integral").text(jifen);
			$("#single_order_price").text(price);
			var intrgral_name = $('#server_intrgral_name').val();
			
			$("#server_subject").val('充值'+intrgral_name+'购买'+server.name);
		}else{
			$('#single_integral').text(jifen);
			$('#pay_integral_buy').hide();
		}
	}else{
		$('#price_int').text(price);
		$("#single_order_price").text(price);
		$("#server_subject").val('购买'+server.name);
		
	}
}
function server_single_subject(type){
	
	$("#dayslist").addClass('none');
	var server = {};
 
	if(type == 'issuejob'){
		server.name = '职位发布';
		server.price = $("#server_integral_job").val();
	}else if (type == 'sxltjob' || type == 'sxjob' || type == 'sxpart'){
		if(type == 'sxjob'){
			server.name = '职位刷新';
		}else if(type == 'sxpart'){
			server.name = '兼职刷新';
		}
		server.price = $("#server_integral_jobefresh").val();
	}else if (type == 'invite'){
		server.name = '邀请面试';
		server.price = $("#server_integral_interview").val();
	}else if (type == 'downresume'){
		server.name = '下载简历';
	}else if (type == 'zph'){
		server.name = '预订招聘会';
	}else if (type == 'partrec' || type == 'jobrec' || type == 'joburgent' || type == 'jobtop' || type == 'autojob'){
		$("#dayslist").removeClass('none');
		var pname;
		if (type == 'partrec' || type == 'jobrec'){
			pname = '推荐时长:';
			var recname = type == 'partrec' ? '兼职' : '职位';
			server.name = recname + '推荐';
		}else if (type == 'joburgent'){
			pname = '紧急时长:';
			server.name = '职位紧急';
		}else if (type == 'jobtop'){
			pname = '置顶时长:';
			server.name = '职位置顶';
		}else if (type == 'autojob'){
			pname = '自动刷新:';
			server.name = '职位自动刷新';
			var price = $("#server_job_auto").val(),
				autoids = $("#jobautoids").val();
			var idarr = autoids.split(',');
			server.price = price * idarr.length;
		}
		$("#promote_name").text(pname);
	}
	
	return server;
}
/* 积分模式，拥有积分不足下载简历，充值积分输入检测 */
function checkIntegralPaySingle(integral, integral_min, integral_pro){
	
	var single_integral	= $('#single_integral').text(); 							//	单项购买所需积分
	var integral_need =	accSub(parseInt(single_integral), parseInt(integral));		//	单选购买减去账号积分，还需要积分数量
	
	if(parseInt(integral_need) < parseInt(integral_min)){
		
		var	minJifen = integral_min;
	}else{
		
		var	minJifen = integral_need;
	}
	
	var integral_int = $('#integral_int').val();
	
	if(parseInt(integral_int) < parseInt(minJifen)){
		
		$('#integral_int').val(minJifen);
		
		$('#price_int').text(accDiv(minJifen, integral_pro));
		
	}else if(integral_int != ''){
		
		$('#price_int').text(accDiv(parseInt(integral_int), integral_pro));
		
	} else{
 		$('#integral_int').val(single_integral);
		$('#price_int').text(single_integral/integral_pro);
	}
}

/* 积分抵扣，输入积分操作 */
function checkIntegralDK(integral, integral_pro,style) {
	
    var integral_dk        	=  	$("#integral_dk_vip").val();				// 	抵扣输入积分
     
    var service_price      	=  	$("#service_price_"+style).text(); 		//	套餐金额
    
    
 	var service_price	=	service_price;	// 	下单金额
 	
    
    var order_price        	=  	service_price;   
     
    var integral_dk_price  	=  	0;
    
    var integral_need      	=  	accMul(parseFloat(service_price), parseInt(integral_pro)); 	// 	套餐金额转积分
    
    if(parseInt(integral_need) == 0){
	
    	var integral_need	=	1;
    	
    }else if(integral_need > parseInt(integral_need)){
    	
    	var integral_need 	= 	accAdd(parseInt(integral_need), 1);
    }

    if(integral_dk){
    	
    	if(parseInt(integral_dk) > 0){
    		$("#integral_dk_vip").val(parseInt(integral_dk));
    	}
    
	    if(parseInt(integral) >= parseInt(integral_need)) { 					// 	拥有积分充足
	    	
	        if(parseInt(integral_dk) > parseInt(integral_need)) { 				// 	输入抵扣积分超过购买积分
	        	
	            $("#integral_dk_vip").val(parseInt(integral_need));				//	抵扣积分变更最大所需积分
	            
	            order_price			= 	accSub(parseFloat(service_price), accDiv(integral_need, parseInt(integral_pro)));	// 	抵扣后金额  0
	            integral_dk_price	=	accDiv(integral_need, parseInt(integral_pro));									// 	抵扣金额		
	            
	        } else {
	        	
	            order_price			= 	accSub(parseFloat(service_price), accDiv(parseInt(integral_dk), parseInt(integral_pro)));		//	抵扣积分后所需金额
	            integral_dk_price	=	accDiv(integral_dk, parseInt(integral_pro));													//	抵扣金额
	            
	        }	
	        
	    } else {																//	拥有积分不充足
	    	
	        if(parseInt(integral_dk) > parseInt(integral)) {					//	抵扣所有积分
	        	
	            $("#integral_dk_vip").val(parseInt(integral));
	            
	            order_price			= 	accSub(parseFloat(service_price), accDiv(integral, parseInt(integral_pro)));		//	抵扣积分后所需金额
	            integral_dk_price	=	accDiv(integral, parseInt(integral_pro));											//	抵扣金额
	        } else {
	        	
	            order_price			= 	accSub(parseFloat(service_price), accDiv(integral_dk, parseInt(integral_pro)));		//	抵扣积分后所需金额
	            integral_dk_price	=	accDiv(integral_dk, parseInt(integral_pro));										//	抵扣金额
	        }
	    }
    }
    
    /* 根据抵扣后金额，判断支付方式 */
    if(order_price <= 0) {																		
    	
        $("#vip_order_price_"+style).html(0);
        $("#vip_pay_div").hide();
        $("#vip_integral_div").show();
        
    } else {
        $("#vip_order_price_"+style).html(order_price);
        $("#vip_pay_div").show();
        $("#vip_integral_div").hide();
    }	
}
/* 积分模式，购买会员特权/增值服务，拥有积分不足，充值积分输入检测 */
function checkIntegralPay(integral, integral_min,integral_pro){
	
	var single_integral	=	$('#service_price_3').html(); 							//	单项购买所需积分
	var integral		=	integral;												//	目前拥有积分

	var integral_need	=	accSub(parseInt(single_integral), parseInt(integral));	//	单选购买减去账号积分，还需要积分数量
	var integral_min	=	integral_min;											//	站点最低充值积分
	var integral_pro	=	integral_pro;											//	站点积分比例

	if(parseInt(integral_need) < parseInt(integral_min)){
		
		var	minJifen	=	integral_min;
	}else{
		
		var	minJifen	=	integral_need;
	}
	
	var integral_int	=	$('#integral_int_vip').val();
	if(integral_int == ''){
		integral_int = 0;
	}
	if(parseInt(integral_int) <= parseInt(minJifen)){
		
		$('#integral_int_vip').val(minJifen);
		
		$('#vip_order_price_3').html(accDiv(minJifen, integral_pro));
		
	}else if (integral_int != ''){
		  
		$('#vip_order_price_3').html(accDiv(parseInt(integral_int), integral_pro));
		
	} else{
		
		$('#integral_int_vip').val(single_integral);
		$('#vip_order_price_3').html(single_integral/integral_pro);
	}
	
	
	$(".price_3").show();
	
}

/* 积分抵扣，单项购买 */
function checkIntegralDKSingle(integral, integral_pro) {
	
    var integral_dk		= 	$("#server_integral_dk").val();	// 	抵扣输入积分
    var single_price	= 	$("#price_int").html();		//	单份简历金额
    
    
    var order_price		=  	single_price;
     
    var integral_need	= 	accMul(parseFloat(order_price), parseInt(integral_pro));	// 	单份简历所需积分
    
    if(parseInt(integral_need) == 0){
    	
    	var integral_need	=	1;
    	
    }else if(integral_need > parseInt(integral_need)){
    	
    	var integral_need 	= 	accAdd(parseInt(integral_need), 1);
    }
    
    if(integral_dk){
    	
    	if(parseInt(integral_dk) > 0){
    		$("#server_integral_dk").val(parseInt(integral_dk));
    	}
    	
	    if(parseInt(integral) >= parseInt(integral_need)) { 					// 	拥有积分充足
	    	
	        if(parseInt(integral_dk) > parseInt(integral_need)) { 				// 	输入抵扣积分超过下载积分
	        	
	            $("#server_integral_dk").val(parseInt(integral_need));			//	抵扣积分变更最大所需积分
	            
	            order_price	= accSub(parseFloat(single_price), accDiv(integral_need, parseInt(integral_pro))); 	// 	抵扣后金额  0
	            
	        } else {
	        	order_price	= accSub(parseFloat(single_price), accDiv(parseInt(integral_dk), parseInt(integral_pro)));		//	抵扣积分后所需金额
	            
	        }
	    } else {																//	拥有积分不充足
	    	
	        if(parseInt(integral_dk) > parseInt(integral)) {					//	抵扣所有积分
	        	
	            $("#server_integral_dk").val(parseInt(integral));
	            
	            order_price	= accSub(parseFloat(single_price), accDiv(integral, parseInt(integral_pro)));		//	抵扣积分后所需金额
	            
	        } else {
	        	
	            order_price	= accSub(parseFloat(single_price), accDiv(integral_dk, parseInt(integral_pro)));			//	抵扣积分后所需金额
	        }
	    }
		/* 根据抵扣后金额，判断支付方式 */
        if(order_price <= 0) {																		
        	
            $("#single_order_price").text(0);
            $("#server_pay_div").hide();
            $("#server_integral_div").show();
        } else {
        	
        	$("#single_order_price").text(order_price);
            $("#server_pay_div").show();
            $("#server_integral_div").hide();
        }
    }else{
    	$("#single_order_price").text(single_price);
    	$("#server_pay_div").show();
        $("#server_integral_div").hide();
    }
}