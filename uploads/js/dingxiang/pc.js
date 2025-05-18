$(document).ready(function(){

	if(document.getElementById("bind-captcha")){
		var myCaptcha = _dx.Captcha(document.getElementById('bind-captcha'), {
			appId: dxappid, //appId，在控制台中“应用管理”或“应用配置”模块获取
			style: 'popup',
			
			success: function (token) {
				myCaptcha.hide();
				$("input[name='verify_token']").val(token);
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
			   //console.log('token:', token)
			}
		})
	
	}
	
	
	$("#popup-submit").click(function(){
		
		
		$("input[name='verify_token']").val('');
		myCaptcha.reload();
		//throw SyntaxError();
	});
	$("#bind-submit").click(function(){
		
		
		myCaptcha.show();
	});
});