$(document).ready(function(){


var handlerPopup = function (captchaObj) {
	// 成功的回调
	
	captchaObj.onSuccess(function () {

		var validate = captchaObj.getValidate();
		
		if(validate){

			
			$("input[name='verify_token']").val(validate.geetest_challenge+'*'+validate.geetest_validate+'*'+validate.geetest_seccode);

			//提交操作
			
			var type = $('#bind-captcha').attr('data-type');
			var dataid = $('#bind-captcha').attr('data-id');
			//提交表单
			if(type=='submit'){
				$('#'+dataid).submit();
			}else{
				//模拟点击
				$("#"+dataid).trigger("click");
			}
		}

	});

	$("#bind-submit").click(function(){
		captchaObj.verify();
	});
	$("#popup-submit").click(function(){
		$("input[name='verify_token']").val('');
		
		captchaObj.reset();
	});
	
	// 将验证码加到id为captcha的元素里
	
	//captchaObj.appendTo("#bind-captcha");
	// 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html

};

if($("#bind-captcha").length>0){
	$.ajax({
			url: weburl+"/index.php?m=geetest&t=" + (new Date()).getTime(), // 加随机数防止缓存
			type: "get",
			dataType: "json",
			success: function (data) {
				// 使用initGeetest接口
				// 参数1：配置参数
				// 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
				initGeetest({
					gt: data.gt,
					challenge: data.challenge,
					product: "bind", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
					width:"100%",
					offline: !data.success, // 表示用户后台检测极验服务器是否宕机，一般不需要关注
					new_captcha: data.new_captcha
				}, handlerPopup);
			}
	});
}

});