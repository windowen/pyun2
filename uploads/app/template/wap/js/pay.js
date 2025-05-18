//加减乘除
function accAdd(arg1, arg2) {
	var r1, r2, m;
	try {
		r1 = arg1.toString().split(".")[1].length
	} catch(e) {
		r1 = 0
	}
	try {
		r2 = arg2.toString().split(".")[1].length
	} catch(e) {
		r2 = 0
	}
	m = Math.pow(10, Math.max(r1, r2))
	return(arg1 * m + arg2 * m) / m
}

function accSub(arg1, arg2) {
	return accAdd(arg1, -arg2);
}

function accMul(arg1, arg2) {
	var m = 0,
		s1 = arg1.toString(),
		s2 = arg2.toString();
	try {
		m += s1.split(".")[1].length
	} catch(e) {}
	try {
		m += s2.split(".")[1].length
	} catch(e) {}
	return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
}

function accDiv(arg1, arg2) {
	var t1 = 0,
		t2 = 0,
		r1, r2;
	try {
		t1 = arg1.toString().split(".")[1].length
	} catch(e) {}
	try {
		t2 = arg2.toString().split(".")[1].length
	} catch(e) {}
	with(Math) {
		r1 = Number(arg1.toString().replace(".", ""));
		r2 = Number(arg2.toString().replace(".", ""));
		return(r1 / r2) * pow(10, t2 - t1);
	}
}

function myFunction(_this) {
	_this.value = _this.value.replace(/[^0-9]/g, '');
}

/* 选择不同会员等级操作 */
$("ul#level_rating").on("tap", "li", function() {
	$(this).addClass("member_set_meal_cur"); // 点击li添加class
	$(this).siblings('li').removeClass("member_set_meal_cur"); // 删除兄弟li的class属性

	var price = $(this).find("#server_price").val();
	$("#price").html(price);
	$("#r_price").val(price);
	$("#order_price").html(price);
	$("#vip_price").val(price);
	$("#bank_price").val(price);

	var rating = $(this).find("#ratingid").text();
	$("#rating_id").val(rating);
	$("#id").val(rating);
	// 切换套餐时，支付方式又积分模式恢复成付款模式
	$("#paymentform").show();
	$("#integral_buy").hide();
	if(document.getElementById('integral_switch_rating')) {
		document.getElementById('integral_switch_rating').classList.remove('mui-active');
		document.querySelector('.mui-switch-handle').setAttribute('style', '');
		
	}
	$("#integral_pay").val('');
	$("#dkjf").val('');
	$("#spread_integral_box").hide();
})

/* 选择不同增值服务操作 */
$("ul#pack_type").on("tap", "li", function() {
	$(this).addClass("value_added_cur"); // 点击li添加class
	$(this).siblings('li').removeClass("value_added_cur"); // 删除兄弟li的class属性

	var price = $(this).find("#server_price").val();
	$("#price").html(price);
	$("#r_price").val(price);
	$("#order_price").html(price);
	$("#vip_price").val(price);

	var service = $(this).find("#serviceid").text();
	$("#service_id").val(service);
	$("#id").val(service);
	// 切换套餐时，支付方式又积分模式恢复成付款模式
	$("#paymentform").show();
	$("#integral_buy").hide();
	if(document.getElementById('integral_switch_rating')) {
		document.getElementById('integral_switch_rating').classList.remove('mui-active');
		document.querySelector('.mui-switch-handle').setAttribute('style', '');
	}
	$("#integral_pay").val('');
	$("#dkjf").val('');
	$("#spread_integral_box").hide();

})

/* 选择不同充值积分操作 */
$("ul#integral_num").on("click", "li", function() {
	var integral = $(this).attr('data-integral'); // 充值积分
	if(parseInt(integral)>=parseInt(min_integral) || parseInt(integral)==0){
		$(this).addClass("pay_choice_cur"); // 点击li添加class
		$(this).siblings('li').removeClass("pay_choice_cur"); // 删除兄弟li的class属性
	}else{
		layermsg('最低充值：' + min_integral + jifen, 2);
		return false;
	}

	if(integral == '0') {
		$('.defined').show();
		$('#price').html(0);
		$('#bank_price').val(0);
		$('#order_price').val(0);
		$('#integral_int').val(integral);
		$('#user_defined').val('');
	} else {
		var integralid = $(this).attr('data-id'); // 积分类别id
		var discount = $(this).attr('data-discount'); // 积分折扣

		if(parseInt(discount) > 0) {
			var price = (integral / pro) * (discount / 100);
		} else {
			var price = integral / pro;
		}
		price=Math.round(price * 100) / 100;
		$("#integralid").val(integralid);
		$('.defined').hide();
		$('#user_defined').val('');

		$('#price').html(price);
		$('#bank_price').val(price);
		$('#order_price').val(price);
		 
		$('#integral_int').val(integral);
	}
})

/* 支付方式选择 */
function paycheck(type) {
	var type;

	if(type == "alipay") {

		$(".alipay").addClass("member_set_meal_fk_xz_cur");
		$(".bank").removeClass("member_set_meal_fk_xz_cur");
		$("#paytype").val('alipay');
		$(".paybank").hide();
		$("#paymentform").attr("action", "index.php?c=dingdan");
	} else if(type == "bank") {
		$(".bank").addClass("member_set_meal_fk_xz_cur");
		$(".alipay").removeClass("member_set_meal_fk_xz_cur");
		$("#paytype").val('bank');
		$(".paybank").show();
	 
		$("#paymentform").attr("action", "index.php?c=paybank");
 	}
}

/* 积分填写 */

function checkIntegralNum() {
	var integral = $("#user_defined").val();
	if(parseInt(integral) > 0) {
		var integralval = integral;
		var discountarr = [];
		for(var i in integralclass) {
			if(integralval >= parseInt(integralclass[i]['integral'])) {
				discountarr.push({
					'discount': integralclass[i]['discount'],
					'id': integralclass[i]['id']
				});
			}
		}
		if(discountarr.length > 0) {
			var discount = discountarr[discountarr.length - 1].discount;
			$("#integralid").val(discountarr[discountarr.length - 1].id);
		}
		if(parseInt(discount) > 0) {
			var price = (integral / pro) * (discount / 100);
		} else {
			var price = integral / pro;
		}
		price=Math.round(price * 100) / 100;
		$('#price').html(price);
		$('#bank_price').val(price);
		$('#order_price').val(price);
		$('#integral_int').val(integral);
	} else {
		$('#price').html(0);
		$('#bank_price').val(0);
		$('#order_price').val(0);
		$('#integral_int').val(0);

	}
}

function autointegral() {
	var integral = parseInt($("#user_defined").val());
	if(integral) {
		if(min_integral > 0 && integral < min_integral) {
			integral = min_integral;
			$("#user_defined").val(integral);
			$('#integral_int').val(integral);
		} else {
			$("#user_defined").val(integral);
			$('#integral_int').val(integral);
		}
		var integralval = integral;
		var discountarr = [];
		for(var i in integralclass) {
			if(integralval >= parseInt(integralclass[i]['integral'])) {
				discountarr.push({
					'discount': integralclass[i]['discount'],
					'id': integralclass[i]['id']
				});
			}
		}
		if(discountarr.length > 0) {
			var discount = discountarr[discountarr.length - 1].discount;
			$("#integralid").val(discountarr[discountarr.length - 1].id);
		}else{
			var discount = 0;
			$("#integralid").val('');
		}
		if(parseInt(discount) > 0) {
			var price = (integral / pro) * (discount / 100);
		} else {
			var price = integral / pro;
		}
		price=Math.round(price * 100) / 100;
		$('#price').html(price);
		$('#bank_price').val(price);
		$('#order_price').val(price);

	}
}

function checkNum(integral, integral_pro) {

	var integral_pay = $("#integral_pay").val();

	var price = $("#r_price").val();

	var need_integral = accMul(parseFloat(price), integral_pro);

	if(parseInt(need_integral) == 0){
    	
    	var need_integral	=	1;
    	
    }else if(need_integral > parseInt(need_integral)){
    	
    	var need_integral 	= 	accAdd(parseInt(need_integral), 1);
    }
	
	if(integral_pay){
		
		if(parseInt(integral_pay) > 0){
    		$("#integral_pay").val(parseInt(integral_pay));
    	}
		
		if(parseInt(integral) >= parseInt(need_integral)) {
	
			if(parseInt(integral_pay) >= parseInt(need_integral)) {
	
				$("#integral_pay").val(parseInt(need_integral));
				
				var price_n = 0;
			} else {
				var price_n = accSub(parseFloat(price), accDiv(parseInt(integral_pay), integral_pro));
			}
	
		} else {
	
			if(parseInt(integral_pay) > parseInt(integral)) {
				$("#integral_pay").val(parseInt(integral));
				var price_n = accSub(parseFloat(price), accDiv(integral, integral_pro));
			} else {
				var price_n = accSub(parseFloat(price), accDiv(integral_pay, integral_pro));
			}
	
		}
	
		if(price_n <= 0) {
	
			$("#order_price").html(0);
	
			$("#paymentform").hide();
	
			$("#integral_buy").show();
	
		} else {
			var integral_dk	=	$("#integral_pay").val();
			$("#dkjf").val(integral_dk);
			$("#order_price").html(price_n);
			$("#bank_price").val(price_n);
			$("#paymentform").show();
			$("#integral_buy").hide();
	
		}
	}else{
		$("#dkjf").val('');
		$("#order_price").html(price);
		$("#bank_price").val(price);
		$("#paymentform").show();
		$("#integral_buy").hide();
	}
}

function integral_add_buy() {
	var service_id = $("#service_id").val();
	var r_integral = $("#r_price").val();
	var integral_need = accMul(r_integral, pro);

	if(parseInt(integral) < parseInt(integral_need)) {
		layermsg(pricename+"不足，请先充值", 2, function() {
			location.href = wapurl + "member/index.php?c=pay";
		});
		return false;
	} else {
		$.post("index.php?c=dkzf", {
			tcid: service_id,
			integral: integral_need
		}, function(data) {
			var data = eval('(' + data + ')');
			if(data.error == '0') {
				layermsg(data.msg, 2, function() {
					location.href = wapurl + "member/index.php?c=com";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = wapurl + "member/index.php?c=rating";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload(true);
					});
					return false;
				}
			}
		});
	}
}

function integral_rating_buy() {
	var rating_id = $("#rating_id").val();
	var r_integral = $("#r_price").val();
	var integral_need = accMul(r_integral, pro);

	if(parseInt(integral) < parseInt(integral_need)) {
		layermsg(pricename+"不足，请先充值", 2, function() {
			location.href = "index.php?c=pay";
		});
		return false;
	} else {
		$.post("index.php?c=dkzf", {
			id: rating_id,
			integral: integral_need
		}, function(data) {
			var data = eval('(' + data + ')');
			if(data.error == '0') {
				layermsg(data.msg, 2, function() {
					location.href = wapurl + "member/index.php?c=com";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = wapurl + "member/index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload(true);
					});
					return false;
				}
			}
		});
	}
}

function pay_form() {
	var price = $("#vip_price").val();

	if(parseInt(price) < parseInt(scope)) {
		layermsg('该优惠券未达消费条件！', 2);
		return false;
	}

	var paytype = $('#paytype').val();
	if(paytype == "") {
		layermsg('请选择一种支付方式！', 2);
		return false;
	}

	if(paytype == 'bank') {
		if($('#bank_name').val() == '') {
			layermsg('请填写汇款银行！', 2);
			return false;
		} else if($('#bank_number').val() == '') {
			layermsg('请填写汇入账号！', 2);
			return false;
		} else if($('#bank_price').val() == '') {
			layermsg('请填写汇款金额！', 2);
			return false;
		} else if($('#bank_time').val() == '') {
			layermsg('请填写汇款时间！', 2);
			return false;
		}
	}
}

function integral_form() {

	var integral = $('#integral_int').val();

	if(parseInt(min_integral) > parseInt(integral)) {
		layermsg('最低充值：' + min_integral + pricename, 2);
		return false;
	}

	var price = $("#order_price").val();

	if(parseInt(price) < parseInt(scope)) {
		layermsg('该优惠券未达消费条件！', 2);
		return false;
	}

	var paytype = $('#paytype').val();
	if(paytype == "") {
		layermsg('请选择一种支付方式！', 2);
		return false;
	}

	if(paytype == 'bank') {
		if($('#bank_name').val() == '') {
			layermsg('请填写汇款银行！', 2);
			return false;
		} else if($('#bank_number').val() == '') {
			layermsg('请填写汇入账号！', 2);
			return false;
		} else if($('#bank_price').val() == '') {
			layermsg('请填写汇款金额！', 2);
			return false;
		} else if($('#bank_time').val() == '') {
			layermsg('请填写汇款时间！', 2);
			return false;
		}
	}
}

// 其他支付方式
if(document.getElementById('spreadpaymore')) {
	document.getElementById('spreadpaymore').addEventListener('tap', function(event) {
		if(document.getElementById('spreadotherpay').style.display == 'none') {
			document.getElementById('spreadotherpay').style.display = '';
			document.getElementById('switchbank').classList.remove('member_set_meal_fk_xz_cur');
		} else {
			document.getElementById('spreadpaybank').style.display = 'none';
			document.getElementById('spreadotherpay').style.display = 'none';
			document.getElementById('switchbank').classList.remove('member_set_meal_fk_xz_cur');
		}

	});
}

// 企业getserver.htm内积分兑换开关
if(document.getElementById('integral_switch')) {
	document.getElementById('integral_switch').addEventListener('toggle', function(event) {
		if(event.detail.isActive) {
			document.getElementById('spread_integral_box').style.display = 'block';
		} else {
			if(document.getElementById('spread_integral_box')) { // 兑换积分部分隐藏
				document.getElementById('spread_integral_box').style.display = 'none';
			}
			if(document.getElementById('integral_pay')) { // 积分输入框
				document.getElementById('integral_pay').value = '';
			}
			if(document.getElementById('qrzf_btn')) {
				if(document.getElementById('qrzf_btn').style.display == '') {
					document.getElementById('qrzf_btn').style.display = 'none';
					document.getElementById('zffs').style.display = '';
				}
			}
			if(document.getElementById('price') && document.getElementById('r_price')) { // 发布职位抵扣积分，关闭时恢复价格
				document.getElementById('price').innerText = document.getElementById('r_price').innerHTML
			}
			if(document.getElementById('dkjfh_price') && document.getElementById('r_price')) { // 刷新职位抵扣后金额隐藏
				document.getElementById('dkjfh_price').innerText = '';
				document.getElementById('dkhdiv').style.display = 'none';
			}
		}
	});

}


// 汇款时间
var banktimePicker = document.getElementById('banktimePicker');
if(banktimePicker) {
	var bank_time = document.getElementById('bank_time');
	banktimePicker.addEventListener('tap', function() {
		document.activeElement.blur();
		var optionsJson = this.getAttribute('data-options') || '{}';
		var options = JSON.parse(optionsJson);
		var picker = new mui.DtPicker(options);
		picker.show(function(rs) {
			bank_time.value = rs.text;
			banktimePicker.innerText = rs.text;
			picker.dispose();
		});
	}, false);
}

// 职位推广：置顶、推荐、紧急招聘
function setJobPromote(id , type){
	
	var jobid	=	id;
	var days	= 	$("#days").val();
	var type 	=	type;
	
	if (days == '') {
		
		layermsg('请填写职位推广天数！', 2);
		return false;
	}
	
	layer_load('执行中，请稍候...');
	
	$.post('index.php?c=setJobPromote', {jobid : jobid, type : type, days : days}, function(data) {
		
		layer.closeAll();
		
		var data 	= 	eval('(' + data + ')');
		var errcode = 	data.errcode;
		var msg  	= 	data.msg;
		
		if(errcode == 7){
			
			layermsg(msg, 2);
			return false;
		}else{
			
			layermsg(msg, 2, function() {
				location.href = "index.php?c=job";
			})
		}
	});
}