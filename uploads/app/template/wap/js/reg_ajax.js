function exitsid(id) {
	if(document.getElementById(id)) {
		return true;
	} else {
		return false;
	}
}

function checkRegUser(target_form) {
	var regway = $("#regway").val();

	var isRealnameCheck = $("#isRealnameCheck").val();

	var authcode;
	
	var verify_token;

	var usertype = $("#usertype").val();

	if(exitsid("username")) {
		var username = $("#username").val();
		if(username == '') {
			layermsg("用户名不能为空！");
			return false;
		}else{
			$.post(wapurl + "index.php?c=register&a=ajaxreg",{username:username},function(data){
				if(data==1){
					layermsg("用户名已存在！");return false;
				}else if(data==2){
					layermsg("用户名不得包含特殊字符！");return false;
				}else if(data==3){
					layermsg("该用户名已被禁止注册！");return false;
				}
			});
		}
	}

	if(exitsid("moblie")) {
		var moblie = $("#moblie").val();
		if(moblie == "") {
			layermsg("请填写手机号！");
			return false;
		} else if(!isjsMobile(moblie)) {
			layermsg("手机格式不正确！");
			return false;
		}
	}

	if(exitsid("email")) {
		var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		var email = $("#email").val();
		if(email == "") {
			layermsg("邮箱不能为空！");
			return false;
		} else if(!myreg.test(email)) {
			layermsg("邮箱格式不正确！");
			return false;
		}
	}

	

	var password = $("#password").val();
	if(password == "") {
		layermsg("密码不能为空！");
		return false;
	} else if(password.length < 6 || password.length > 20) {
		layermsg("密码长度应在6-20位！");
		return false;
	}
	if(exitsid("passconfirm")) {
		var passconfirm = $("#passconfirm").val();
		if(passconfirm == "") {
			layermsg("确认密码不能为空！");
			return false;
		} else if(password != passconfirm) {
			layermsg("两次密码不一致！");
			return false;
		}
	}
	
	if(exitsid("moblie_code")) {
		if($("#moblie_code").val() == "") {
			layermsg('短信验证码不能为空！');
			return false;
		}
	}
	
	

	if($("#xieyi").attr("checked") != 'checked') {
		layermsg('您必须同意注册协议才能成为本站会员！');
		return false;
	}
	// 有发送短信验证码不需要触发验证
	// 1-实名认证，需要发送短信验证码
	// 2-手机号注册，有极验/顶象验证码
	var noblur = document.getElementById('noblur');
	var regway = $("#regway").val();
	var isRealnameCheck = $("#isRealnameCheck").val();
	// 1-邮箱/3-用户名注册且实名认证，需要发送短信验证码
	if(((regway == 1 || regway == 3) && isRealnameCheck != 1) || (regway == 2 && !noblur)){
		var codesear = new RegExp('注册会员');
		if(codesear.test(code_web)) {
			if(code_kind == 1) {
				authcode = $.trim($("#checkcode").val());
				if(!authcode) {
					layermsg('图片验证码不能为空！');
					return false;
				}
			} else if(code_kind >2) {
				
				verify_token = $('input[name="verify_token"]').val();
				if(verify_token == '') {
					$("#bind-submit").trigger("click");
						return false; 
				}
			}
		}
	}
	
	post2ajax(target_form);
	return false;
}

function sendmsg(img) {
	var send = $("#send").val();
	var moblie = $("#moblie").val();
	var code;
	
	var verify_token;
	var codesear = new RegExp('注册会员');
	if(moblie == "") {
		layermsg("请填写手机号！");
		return false;
	}else if(!isjsMobile(moblie)){
		layermsg("手机格式不正确！");
		return false;
	}
	if(send > 0) {
		layermsg('请不要频繁重复发送！');
		return false;
	}
	if(codesear.test(code_web)) {
		if(code_kind == 1) {
			code = $.trim($("#checkcode").val());
			if(!code) {
				layermsg('请填写图片验证码！');
				return false;
			}
		} else if(code_kind >2) {

			
			verify_token = $('input[name="verify_token"]').val();

			if(verify_token == '') {
				$("#bind-submit").trigger("click");
				return false; 
			}
		}
	}
	// 两种情况验证手机号是否被使用，改为在发送验证码时验证
	// 1-用户名注册且实名认证，需要发送短信验证码
	// 2-手机号注册，有极验/顶象验证码
	var noblur;
	var regway = $("#regway").val();
	var isRealnameCheck = $("#isRealnameCheck").val();
	// 1-邮箱/3-用户名注册且实名认证，需要发送短信验证码
	if((regway == 1 || regway == 3) && isRealnameCheck == 1){
		noblur = 1;
	}else if(regway == 2){
		noblur = $("#noblur").val()
	}
	
	layer_load('执行中，请稍候...');
	$.post(wapurl + "/index.php?c=ajax&a=regcode", {
		moblie: moblie,
		code: code,
		
		verify_token:verify_token,
		noblur: noblur
	}, function(data) {
		layer.closeAll();
		if(data){
			$("#zy_mobile").val(moblie);
			var res = JSON.parse(data);
			if(res.errcode && noblur){
				mobileUserd(res.data);
			}else{
				layermsg(res.msg, 2, function(){
					if(res.error == 1){
						sendtime("121");
					}else if(res.error == 106){
						checkCode(img);
					}else if(res.error == 107){
						$("#popup-submit").trigger("click");
					}else{
						if(code_kind==1){
							checkCode(img);
						}else if(code_kind>2){
							$("#popup-submit").trigger("click");
						}
					}
				});
			}
		}
	})
}

function sendtime(i) {
	i--;
	if(i == -1) {
		$("#time").html("重新获取");
		$("#send").val(0)
	} else {
		$("#send").val(1)
		$("#time").html(i + "秒");
		setTimeout("sendtime(" + i + ");", 1000);
	}
}

function check_email() {
	
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	var email = $("#email").val();
	if(email == "") {
		$("#email_yes").hide();
		layermsg("邮箱不能为空！");
		return false;
	}else if(!myreg.test(email)) {
		layermsg("邮箱格式不正确！");
		return false;
	}
	$.post(wapurl + "index.php?c=register&a=regemail", {
		email: email
	}, function(data) {
		if(data == 0 && email != "") {
			$("#email_yes").show();
		} else {
			 
			var data = eval('(' + data + ')');
			$("#email").val("");
			$("#zy_uid").val(data.uid);
			$("#zy_email").val(email);
			$("#zy_type").html("该邮箱已被注册");
			$("#desc_toast").html("2. 解除邮箱与该账号的绑定，解除绑定后，您无法继续用该邮箱登录");
			$("#jcbind").removeClass("none");
			
			if(data.usertype=='1'){
				$("#zy_type").html("该邮箱已被注册为个人账号");
				if(data.name){
					$("#zy_name").html("个人名称：<span class=reg_have_tip_tit_name>"+data.name.substr(0,1)+"**</span>");
				}
				
			}else if(data.usertype=='2'){
				$("#zy_type").html("该邮箱已被注册为企业账号");
				if(data.name){
					$("#zy_name").html("企业名称：<span class=reg_have_tip_tit_name>"+data.name+"</span>");
				}
			}else if(data.usertype=='0'){
				$("#jcbind").addClass("none");
				$("#zy_name").html("");
			} 
			
			layer.open({
				type: 1,
				title: '邮箱已被占用',
				closeBtn: 1,
				border: [10, 0.3, '#000', true],
				content: $("#written_off").html()
			});
		}
	});
}

function check_moblie() {
	// 不需要触发此方法情况,改为发送验证码时验证
	// 1-用户名注册且实名认证，需要发送短信验证码
	// 2-手机号注册，有极验/顶象验证码
	var noblur = document.getElementById('noblur');
	var regway = $("#regway").val();
	var isRealnameCheck = $("#isRealnameCheck").val();
	// 1-邮箱/3-用户名注册且实名认证，需要发送短信验证码
	if((regway == 1 || regway == 3) && isRealnameCheck == 1){
		return false;
	}else if(regway == 2 && noblur){
		return false;
	}
	
	var moblie = $("#moblie").val();
	if(moblie == "") {
 		$("#moblie_yes").hide();
		layermsg("手机不能为空！");
		return false;
	}else if(!isjsMobile(moblie)){
		layermsg("手机格式不正确！");
		return false;
	}
	
	$.post(wapurl + "index.php?c=register&a=regmoblie", {
		moblie: moblie
	}, function(data) {
		if(data == 0 && moblie != "") {
			$("#moblie").attr('date', '1');
			$("#moblie_yes").show();
		} else {
			
			if(data == 2) {
				msg = "该手机号已被禁止使用！";
				layermsg("该手机号已被禁止使用！");
			} else {
				$("#zy_mobile").val(moblie);
				var data = eval('(' + data + ')');
				mobileUserd(data);
			}
		}
	});
}

// 手机号已被使用提示框
function mobileUserd(data){
	$("#moblie").val("");
	$("#zy_uid").val(data.uid);
	$("#jcbind").removeClass("none");
	
	if(data.usertype=='1'){
		$("#zy_type").html("该手机号已被注册为个人账号");
		if(data.name){
			$("#zy_name").html("个人名称：<span class=reg_have_tip_tit_name>"+data.name.substr(0,1)+"**</span>");
		}
		
	}else if(data.usertype=='2'){
		$("#zy_type").html("该手机号已被注册为企业账号");
		if(data.name){
			$("#zy_name").html("企业名称：<span class=reg_have_tip_tit_name>"+data.name+"</span>");
		}
	}else if(data.usertype=='0'){
		$("#jcbind").addClass("none");
		$("#zy_name").html("");
	} 

	layer.open({
		type: 1,
		title: [ '温馨提示', 'background-color: #FF4351; color:#fff;'],
		closeBtn: 1,
		border: [10, 0.3, '#000', true],
		content: $("#written_off").html()
	});
}

function check_username() {
	var username = $("#username").val();

	var reg = new RegExp('！',"g");
	var username = username.replace( reg , '!' );
	$("#username").val(username);
		
	if(username == "") {
		$("#username_yes").hide();
	} else {
      $.post(wapurl + "index.php?c=register&a=ajaxreg",{username:username},function(data){
      	var data = eval('(' + data + ')');
        if(data.errcode==1){
			layermsg("用户名已存在！");return false;
		}else if(data.errcode==2){
			layermsg("用户名不得包含特殊字符！");return false;
		}else if(data.errcode==3){
			layermsg("该用户名已被禁止注册！");return false;
		}else if(data.errcode==4){
			layermsg(data.msg);return false;
		}else{
  			$("#username_yes").show();
        }
      });
	
	}
}

function check_password() {
	var password = $("#password").val();
	if(password == "") {
		$("#password_yes").hide();
	} else {
		$.post(wapurl + "index.php?c=register&a=ajaxreg",{password:password},function(data){

	      	var data = eval('(' + data + ')');

	        if(data.errcode==4){
				layermsg(data.msg);return false;
			}else{
	  			$("#password_yes").show();
	        }
	    });
		
	}
}

function check_passconfirm() {
	var passconfirm = $("#passconfirm").val();
	if(passconfirm == "") {
		$("#passconfirm_yes").hide();
	} else {
		var pass = $('#password').val();
		if(pass == passconfirm){
			$("#passconfirm_yes").show();
		}else{
			$("#passconfirm_yes").hide();
		}
		
	}
}

function check_code() {
	var checkcode = $("#checkcode").val();
	if(checkcode == "") {
		$("#checkcode_yes").hide();
	} else {
		$("#checkcode_yes").show();
	}
}

function check_moblie_code() {
	var moblie_code = $("#moblie_code").val();
	if(moblie_code == "") {
		$("#moblie_code_yes").hide();
	} else {
		$("#moblie_code_yes").show();
	}
}

function check_realname() {
	var name = $("#name").val();
	if(name == "") {
		$("#realname_yes").hide();
	} else {
		$("#realname_yes").show();
	}
}

function check_linkman() {
	var linkman = $("#linkman").val();
	if(linkman == "") {
		$("#linkman_yes").hide();
	} else {
		$("#linkman_yes").show();
	}
}

function showservices() {
	$('#services').show();
}
function showyinsi() {
	$('#yinsi').show();
}
function checkRegLt(target_form) {
	var username = $("#username").val();
	if(username == '') {
		layermsg("用户名不能为空！");
		return false;
	} else if(username.length < 2 || username.length > 16) {
		layermsg("用户名应在2-12位字符之间！");
		return false;
	}
	var password = $("#password").val();
	if(password == "") {
		layermsg("密码不能为空！");
		return false;
	} else if(password.length < 6 || password.length > 20) {
		layermsg("密码长度应在6-20位！");
		return false;
	}
	if(exitsid("passconfirm")) {
		var passconfirm = $("#passconfirm").val();
		if(passconfirm == "") {
			layermsg("确认密码不能为空！");
			return false;
		} else if(password != passconfirm) {
			layermsg("两次密码不一致！");
			return false;
		}
	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	var email = $("#email").val();
	if(email == "") {
		layermsg("邮箱不能为空！");
		return false;
	} else if(!myreg.test(email)) {
		layermsg("邮箱格式不正确！");
		return false;
	}
	var moblie = $("#moblie").val();
	if(moblie == "") {
		layermsg("请填写手机号！");
		return false;
	} else if(!isjsMobile(moblie)) {
		layermsg("手机格式不正确！");
		return false;
	}

	var isRealnameCheck = $("#isRealnameCheck").val();
	if(isRealnameCheck == 1) {

		if(exitsid("name")) {
			var name = $("#name").val();
			if(name == "") {
				layermsg("真实名称不能为空！");
				return false;
			}
		}

		var moblie_code = $("#moblie_code").val();
		if(moblie_code.length < 4) {
			layermsg('请输入正确的短信验证码！');
			return false;
		}
	}

	if($("#xieyi").attr("checked") != 'checked') {
		layermsg('您必须同意注册协议才能成为本站会员！');
		return false;
	}

	var authcode;
	
	var verify_token;
	var codesear = new RegExp('注册会员');

	if(codesear.test(code_web)) {

		if(code_kind == 1) {
			authcode = $.trim($("#checkcode").val());
			if(!authcode) {
				layermsg('请填写验证码！');
				return false;
			}
		} else if(code_kind > 2) {

		
			verify_token = $('input[name="verify_token"]').val();

			if(verify_token == '') {
				$("#bind-submit").trigger("click");
					return false; 
			}
		}
	}
	post2ajax(target_form);
	return false;
}

function CheckPW() {
	
	layer.closeAll(); 
	
	$("#postpw .pw").html("<div class=tiny_show_tckbox_cont_p>登录密码</div><div class=tiny_show_tckbox_p><input type=\"password\" value=\"\" id=\"login_password\"placeholder=\"请输入登录密码\" class=tiny_show_tckbox_text></div><div class=tiny_show_tckbox_bth><input type=\"submit\" value=\"确定\" class=tiny_show_tckbox_bth1 onclick=\"post_pass();\" /></div>")
	
	postpwhtml = $("#postpw").html();
	
	$("#postpw").html('');

	layer.open({
		type: 1,
		title: '验证身份',
		border: [10, 0.3, '#000', true],
		content: postpwhtml,
		end: function() {
			$("#postpw").html("<div class=\"tiny_show_tckbox_cont pw\"></div>");
		}
	});
	
}

function post_pass() {
	var zyuid = $("#zy_uid").val();
	var mobile = $("#zy_mobile").val();
	var email = $("#zy_email").val();
	var pw = $("#login_password").val();
	if(zyuid == "") {
		layermsg('该用户不存在');
		return false;
	}
	if(pw == "") {
		layermsg('请输入密码');
		return false;
	}
	$.post(wapurl + "index.php?c=register&a=writtenoff", {
		zyuid: zyuid,
		mobile: mobile,
		email: email,
		pw: pw
	}, function(data) {
		if(data == 2) {
			layermsg('密码错误！');
			return false;
		} else if(data == 1){
			layer.closeAll();
			layermsg("解绑成功", 2, function() {
				location.reload(true);
			});
		}
	})
}
// 微信等，登录直接注册账号绑定手机号
function checkwxbind(target_form) {

	if(exitsid("moblie")) {
		var moblie = $("#moblie").val();
		if(moblie == "") {
			layermsg("请填写手机号！");
			return false;
		} else if(!isjsMobile(moblie)) {
			layermsg("手机格式不正确！");
			return false;
		}
	}
	if(exitsid("moblie_code")) {
		if($("#moblie_code").val() == "") {
			layermsg('短信验证码不能为空！');
			return false;
		}
	}
	
	post2ajax(target_form);
	return false;
}
//快捷登录绑定已有账号
function bindacount(){
	var provider=$.trim($("#provider").val());
	var username=$.trim($("#username").val());
	var password=$.trim($("#password").val()); 
	if(username==''||password==''){
		layermsg('用户名或密码均不能为空！');return false; 
	}
	// 验证码验证
	var authcode;
	
	var verify_token;
	var codesear=new RegExp('前台登录');
	if(codesear.test(code_web)){
		if(code_kind==1){
			authcode=$.trim($("#checkcode").val());  
			if(!authcode){
				layermsg('请填写验证码！');return false;
			}					
		}else if(code_kind>2){
		
			verify_token = $('input[name="verify_token"]').val();
			
			if(verify_token ==''){
				
				$("#bind-submit").trigger("click");
				return false; 
			}
		}
	}
	
	layer_load('执行中，请稍候...');
    
    $.post(wapurl + "index.php?c=login&a=baloginsave",{provider:provider,username:username,password:password,authcode:authcode,verify_token:verify_token}, function (data) {

		layer.closeAll();

        var json_data = eval('(' + data + ')');

        if (json_data.msg) {
			if($("#bind-captcha").length>0){
				$("#popup-submit").trigger("click");
			}
			
			layermsg(json_data.msg, json_data.tm, function () {
				if (json_data.url) {
					location.href = json_data.url; 
				}  
			});
			checkCode('vcode_img'); 
			
			return false; 
        } else if (json_data.url) {
            location.href = json_data.url;
			return false; 
        }

    });
    
    
    return false;
}
//快捷登录直接注册账号
function creatacount(){
	var provider=$.trim($("#provider").val());
	layer_load('执行中，请稍候...');
	$.post(wapurl + "index.php?c=login&a=balogin", {provider:provider}, function(data) {
		layer.closeAll();
		data = eval('(' + data + ')');

		if (data.url != '' && data.msg != '') {
			layermsg(data.msg,2, function() {
				window.location.href = data.url;
			});
		} else if (data.url != '') {
			window.location.href = data.url;
		}
	});
}