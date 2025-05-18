$(document).ready(function(){

	if(document.getElementById("bind-captcha")){
		vaptcha({
			  vid: vaptchaid, // 验证单元id
			  type: "invisible", // 显示类型 隐藏式
			  scene: 0, // 场景值 默认0
			  offline_server: "", //离线模式服务端地址，若尚未配置离线模式，请填写任意地址即可。
			  //可选参数
			  //lang: 'auto', // 语言 默认auto,可选值auto,zh-CN,en,zh-TW,jp
			  //https: true, // 使用https 默认 true
			}).then(function (vaptchaObj) {
			  obj = vaptchaObj; //将VAPTCHA验证实例保存到局部变量中
			  //获取token的方式一：
			  //vaptchaObj.renderTokenInput('.login-form')//以form的方式提交数据时，使用此函数向表单添加token值
			  //获取token的方式二：
			  vaptchaObj.listen("pass", function () {
				// 验证成功进行后续操作
				
				$("input[name='verify_token']").val(vaptchaObj.getToken());
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
			  });
			  //关闭验证弹窗时触发
			  vaptchaObj.listen("close", function () {
				
			   
			  });
		});

	}
	
	
	$("#popup-submit").click(function(){
		
		
		$("input[name='verify_token']").val('');
		
		obj.reset();
		
	});
	$("#bind-submit").click(function(){
		
		
		obj.validate();

	});
});