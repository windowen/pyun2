function sendmoblie(img){
	if($("#send").val()=="1"){
		return false;
	}
	var moblie=$("input[name=moblie]").val();
	var reg= /^[1][3456789]\d{9}$/; //验证手机号码
	var authcode=$("input[name=authcode]").val();
	if(moblie==''){
		layermsg('手机号不能为空！',2);return false;
	}else if(!reg.test(moblie)){
		layermsg('手机号码格式错误！',2);return false;
	}
	if(!authcode){
		layermsg('请输入验证码！',2);return false;
	}
	layer_load('执行中，请稍候...',0);
	$.post(wapurl+"/index.php?c=ajax&a=mobliecert", {str:moblie,code:authcode},function(data) {
		layer.closeAll();
		if(data){
			var res = JSON.parse(data);
			layermsg(res.msg, 2, function(){
				if(res.error == 1){
					sends(121);
				}else if(res.error == 106){
					checkCode(img);
				}
			});
		}
	})
}
function sends(i){
	i--;
	if(i==-1){
		$("#time").html("重新获取");
		$("#send").val(0)
	}else{
		$("#send").val(1)
		$("#time").html(i+"秒");
		setTimeout("sends("+i+");",1000);
	}
}


function check_moblie(img){

	var moblie=$("input[name=moblie]").val();
	var authcode=$("input[name=authcode]").val();
	var code=$("#moblie_code").val();
	
	if(moblie==""){ 
		layermsg('请输入手机号码！',2);return false;
	}else if(code==""){ 
		layermsg('请输入短信验证码！',2);return false;
	}else if(!authcode){
		layermsg('请输入验证码！',2);return false;
	}
	
	layer_load('执行中，请稍候...',0);
	
	$.post("index.php?c=binding",{moblie:moblie,code:code},function(data){

		layer.closeAll();
		
		if(data==1){
			if(usertype=='4'){
				layermsg('手机绑定成功！',2,function(){window.location.href = 'index.php?c=binding'}); 
			}else{
				layermsg('手机绑定成功！',2,function(){window.location.href = 'index.php?c=set'}); 
			}				
		}else if(data==4){
			layermsg('短信验证码已过期，请重新发送！',2);
		}else if(data==3){
			layermsg('短信验证码不正确！',2);
		}else{
			layermsg('请先获取短信验证码！',2); 
		}
	})
}

function check_email(img){
	
	var email=$("input[name='email']").val();
	var authcode=$("input[name='authcode']").val();
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	
	if(email==''){
		layermsg('邮箱不能为空！',2);return false;
	}else if(!myreg.test(email)){
		layermsg('邮箱格式错误！',2);return false;
	}else if(!authcode){
		layermsg('验证码不能为空！',2);return false;
	}
	
	layer_load('执行中，请稍候...',0);
	
	$.post(wapurl + '/index.php?c=ajax&a=emailcert',{email:email,authcode:authcode},function(data){
		layer.closeAll();
		if(data){
			if(data=="3"){
				layermsg('邮件没有配置，请联系管理员！',2);
			}else if(data=="2"){
				layermsg('邮件通知已关闭，请联系管理员！',2);
			}else if(data=="1"){
				if(usertype=='4'){
					layermsg('邮件已发送到您邮箱，请注意查收验证！',2,function(){window.location.href = 'index.php?c=binding'});
				}else{
					layermsg('邮件已发送到您邮箱，请注意查收验证！',2,function(){window.location.href = 'index.php?c=set'});
				}				
			}else if(data=="5"){
				layermsg('验证码不能为空！',2);
			}else if(data=="4"){
				layermsg('验证码不正确！',2,function(){checkCode(img)});
			}
		}else{
			layermsg('请重新登录！',2);
		} 
	})
}
