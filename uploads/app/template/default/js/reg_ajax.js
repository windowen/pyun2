function exitsid(id){
	if(document.getElementById(id)){
		return true;
	}else{
		return false;
	}
}

function checkreg(type){
	$(".reg_cur").removeClass("reg_cur");
	$("#reg"+type).addClass("reg_cur");
	$("#regtype"+type).show();
	if(type=="1"){
		$("#regtype2").hide();
		$("#regtype3").hide();
	}else if(type=="2"){
		$("#regtype1").hide();
		$("#regtype3").hide();
	}else{
		$("#regtype1").hide();
		$("#regtype2").hide();
		$("#reg2").addClass("reg_cur");
	}
}
function uppassword(id){
	var password = $("#password").val();
	S_level=checkStrong(password);
	switch(S_level) { 
	case 0:
		$(".psw_span").removeClass("psw_span_cur");
	break; 
	case 1: //弱
		$("#pass1_"+id).addClass("psw_span_red");
		$("#pass2_"+id).removeClass("psw_span_yellow");
		$("#pass3_"+id).removeClass("psw_span_green");
	break; 
	case 2: //中
		$("#pass1_"+id).removeClass("psw_span_red");
		$("#pass2_"+id).addClass("psw_span_yellow");
		$("#pass3_"+id).removeClass("psw_span_green");
	break; 
	default: //强
		$("#pass1_"+id).removeClass("psw_span_red");
		$("#pass2_"+id).removeClass("psw_span_yellow");
		$("#pass3_"+id).addClass("psw_span_green");
	} 
}
//返回密码的强度级别 
function checkStrong(sPW){
	if (sPW.length<=4) 
	return 0; //密码太短 
	Modes=0; 
	for (i=0;i<sPW.length;i++){
	//测试每一个字符的类别并统计一共有多少种模式. 
	Modes|=CharMode(sPW.charCodeAt(i)); 
	}
	return bitTotal(Modes); 
} 
function CharMode(iN){ 
	if (iN>=48 && iN <=57) //数字 
	return 1; 
	if (iN>=65 && iN <=90) //大写字母 
	return 2; 
	if (iN>=97 && iN <=122) //小写 
	return 4; 
	else 
	return 8; //特殊字符 
} 

//计算出当前密码当中一共有多少种模式 
function bitTotal(num){ 
	modes=0; 
	for (i=0;i<4;i++){ 
	if (num & 1) modes++; 
	num>>>=1; 
	} 
	return modes; 
} 
//---邮箱获取后缀--
function get_def_email(email,type){
		$("#ajax_email"+type).hide();
		
    var postemail=email.split("@");
   
		var configemail = $('#defEmail').val();
    
		var def_email=configemail.split("|");
    
    
      var emails=[];
      if($.trim(postemail[1])!=""){
        $.each(def_email,function(index,data){ 
          if(data.indexOf(postemail[1])>-1){
            emails.push(data);
          };
        });
      }else{
        emails=def_email;
      }
      if(emails!=''){
         var html='';
          $.each(emails,function(index,data){ 
         if(index==0){
            $class=" reg_email_box_list_hover";
          }else{
            $class="";
          }
         
          html+='<div class="reg_email_box_list'+$class+' email'+index+'" aid="'+type+'" onclick="click_email('+index+','+type+');" onmousemove="hover_email('+index+');"><span class="eg_email_box_list_left">'+postemail[0]+'</span>'+data+'</div>';
        })
         $(".reg_email_box").html(html);
        $(".reg_email_box").show();
        $("#def").val(email);
        $("#default").val(0);
        $("#allnum").val(emails.length);
      }else{
       //  $(".reg_email_box")hide();
      }

}
function hover_email(id){
	$(".reg_email_box_list_hover").removeClass("reg_email_box_list_hover");
	$(".email"+id).addClass("reg_email_box_list_hover");
	$("#default").val(id);
}
function click_email(id,type){
	var email=$(".email"+id).html();
	email=email.replace('<span class="eg_email_box_list_left">','');
	email=email.replace('</span>','');
	email=email.replace('<SPAN class=eg_email_box_list_left>','');
	email=email.replace('</SPAN>','');
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/;
	if(myreg.test(email)){
		$("#email"+type).val(email);
	}else{
		$("#email"+type).val('');
	}
	$("#email"+type).val(email);
	$(".reg_email_box").hide();
}

function keyDown(event) {
	var aevt=event;
	var evt = (aevt) ? aevt : ((window.event) ? window.event : ""); //兼容IE和Firefox获得keyBoardEvent对象  
	var key = evt.keyCode?evt.keyCode:evt.which; //兼容IE和Firefox获得keyBoardEvent对象的键值
    if (key==38){//上
		var def=$("#default").val();
		if(def>0){
			var num=parseInt(def)-1;
			$("#default").val(num);
			$(".reg_email_box_list_hover").removeClass("reg_email_box_list_hover");
			$(".email"+num).addClass("reg_email_box_list_hover");
		}
	}
    if (key==40){//下
		var def=$("#default").val();
		var num=parseInt(def)+1;
		var allnum=$("#allnum").val();
		if(num<allnum){
			$("#default").val(num);
			$(".reg_email_box_list_hover").removeClass("reg_email_box_list_hover");
			$(".email"+num).addClass("reg_email_box_list_hover");
		}
	}
    if (key==13){//回车
		var type=$(".reg_email_box_list_hover").attr("aid");
		var email=$(".reg_email_box_list_hover").html();
		if(email){
			email=email.replace('<span class="eg_email_box_list_left">','');
			email=email.replace('</span>','');
			email=email.replace('<SPAN class=eg_email_box_list_left>','');
			email=email.replace('</SPAN>','');
			$("#event").val('13');
			var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/;
			if(myreg.test(email)){
				$("#email"+type).val(email);
			}else{
				$("#email"+type).val('');
			}
			$(".reg_email_box").hide();
 			setTimeout(function (){ $("#event").val('1');},1000);
		}
	}
}

$(function(){
	$('body').click(function(evt){
		if($(evt.target).parents("#defemail1").length==0 && evt.target.id != "defemail1") {
			$('#defemail1').hide();
		}
		if($(evt.target).parents("#defemail3").length==0 && evt.target.id != "defemail3") {
			$('#defemail3').hide();
		}
	});
	$("#email1").blur(function(){
		setTimeout("reg_checkAjax('email1')",300);
	});
	$("#email3").blur(function(){
		setTimeout("reg_checkAjax('email3')",300);
	});
})
document.onkeydown = keyDown;



function reg_checkAjax(id,check_user){
	var obj = $.trim($("#"+id).val());
	var msg; 
	if(id=="username1"){

		var reg = new RegExp('！',"g");
		var obj = obj.replace( reg , '!' );
		$("#"+id).val(obj);
		
		if(obj=="" || obj=='请输入用户名作为账号'){
			//msg='请输入2至16位不包含特殊字符的用户名！';
			msg='用户名不能为空！';
			update_html(id,"0",msg); 
		}else{
			$.ajax({
				type: "POST",
				async: false,
				url: "index.php?m=register&c=ajaxreg",
				data: {
					username: obj
				},
				success: function(data) {
					var res = JSON.parse(data);
					if(res.errcode==0){	
						msg='填写正确！';
						update_html(id,"1",msg);
					}else{
						if(res.errcode==1){
							msg="用户名已存在！";
						}else if(res.errcode==2){
							msg="用户名不得包含特殊字符！";
						}else if(res.errcode==3){
							msg="该用户名已被禁止注册！";
						}else if(res.errcode==4){
							msg=res.msg;
						}
						update_html(id,"0",msg);
					}
				}
			});
			
		}
	}
	if(id=="password"){
		if(obj=="" || obj=='请输入6-20位（字母、数字、符号）'){
			msg='密码不能为空！';
			update_html(id,"0",msg);
		}else if(obj.length<6 || obj.length>20 ){
			msg='只能输入6至20位密码！';
			update_html(id,"0",msg);
		}else{
		 	$.ajax({
				type: "POST",
				async: false,
				url: "index.php?m=register&c=ajaxreg",
				data: {
					password: obj
				},
				success: function(data) {
					var res = JSON.parse(data);
					if(res.errcode==0){	
						msg='输入正确！';
						update_html(id,"1",msg);
					}else{
						if(res.errcode==1){
							msg="用户名已存在！";
						}else if(res.errcode==2){
							msg="用户名不得包含特殊字符！";
						}else if(res.errcode==3){
							msg="该用户名已被禁止注册！";
						}else if(res.errcode==4){
							msg=res.msg;
						}
						update_html(id,"0",msg);
					}
				}
			});
		}
	}
	if(id=="passconfirm"){
		if(obj==""){
			 msg='确认密码不能为空！';
			 update_html(id,"0",msg);
		 }else if(obj.length<6 || obj.length>20 ){
			 msg='只能输入6至20位密码！';
			update_html(id,"0",msg);
		 }else{
			 var password = $('#password').val();
			 if(obj!=password){
				msg='两次输入密码不一致！';
				update_html(id,"0",msg);
			 }else{
				msg='输入正确！';
				update_html(id,"1",msg);
			 }			
		 }  
	}
	if(id=="email1"||id=="email3"){
		 //对电子邮件的验证
	    var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/;
		if(obj==""){
			 msg='邮箱不能为空！';
			 update_html(id,"0",msg);
		 }else if(!myreg.test(obj)){
			msg="邮箱格式错误！";
			update_html(id,"0",msg);
	     }else{
	     	$.ajax({
				type: "POST",
				async: false,
				url: "index.php?m=register&c=regemail",
				data: {
					email: obj
				},
				success: function(data) {
					if(data==0){	
						msg='填写正确！';
						update_html(id,"1",msg); 
					}else{
						if(document.getElementById('written_off').style.display!='none'){
							return;
						}
					 
						var data = eval('('+data+')');
						var msglayer = layer.open({
							type: 1,
							title: '邮箱已被占用',
							closeBtn: 1,
							border: [10, 0.3, '#000', true],
							area: ['550px', 'auto'],
							content: $("#written_off"),
							cancel:function(){
								window.location.reload();
							}
						});
						
						$("#email").val("");
						$("#zy_uid").val(data.uid);
						$("#zy_email").val(obj);
						
						$("#desc_toast").html("2. 解除邮箱与该账号的绑定，解除绑定后，您无法继续用该邮箱登录");
						
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
						}
					}
				}
			});
		}
	}
	
	if(id=="moblie" || id=="linkphone" || id=="usertel"){
		if(obj==''){
			msg="手机号不能为空！";
			 update_html(id,"0",msg);
		}else if(!isjsMobile(obj)){
			msg="手机号码格式错误！";
			 update_html(id,"0",msg);
		 }else{
			 $.ajax({
				type: "POST",
				async: false,
				url: "index.php?m=register&c=regmoblie",
				data: {
					moblie: obj
				},
				success: function(data) {
					if(data==0){	
						msg='填写正确';
						update_html(id,"1",msg);
					}else{
						if(data==2){
							msg="该手机号已被禁止使用！";
							update_html(id,"0",msg);
						} else{
							if(document.getElementById('written_off').style.display!='none'){
								return;
							}
							var data = eval('('+data+')');
							var msglayer = layer.open({
								type: 1,
								title: '手机号已被占用',
								closeBtn: 1,
								border: [10, 0.3, '#000', true],
								area: ['550px', 'auto'],
								content: $("#written_off"),
								cancel:function(){
									window.location.reload();
								}
							});
							$("#moblie").val("");
							$("#zy_uid").val(data.uid);
							$("#zy_mobile").val(obj);
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
							}
						}
					}
				}
			});
		 }
	}
	if(id=="moblie_code"){
		 if(obj=='' || obj=='请输入短信验证码'){
			msg="短信验证码不能为空！";
			 update_html(id,"0",msg);
		 }else{
			msg="输入成功！";
			update_html(id,"1",msg); 
		 }
	}
	
	if(id=="CheckCode"){
		if(obj=='' || obj=='请输入图片验证码'){
			msg="请输入验证码！";
			 update_html(id,"0",msg);
		 }else{
			msg="输入成功！";
			update_html(id,"1",msg);
		 }
	}else if(id=="unlock"){
		if(obj=="0"){
			msg="请点击按钮进行验证！";
			 update_html(id,"0",msg);
		 }else{
			msg="完成验证！";
			update_html(id,"1",msg);
		 }
	}
}

function update_html(id,type,msg){
	$("#ajax_"+id).show();
	$("#ajax_"+id).html('<i class="reg_tips_icon"></i>'+msg); 
	if(type=="0"){  
		$("#ajax_"+id).attr("class","reg_tips reg_tips_red");
		$("#"+id).addClass("logoin_text_focus");
		$("#"+id).attr('date','0');
		return false;
	}else{ 
		$("#ajax_"+id).attr("class","reg_tips reg_tips_blue");
		$("#"+id).removeClass("logoin_text_focus");
		$("#"+id).attr('date','1');
	}
}
function showpw(id){
	if($("#showpw"+id).html()=="显示密码"){
		PasswordToText("password");
		$("#showpw"+id).html('隐藏密码');
	}else{
		TextToPassword("password");
		$("#showpw"+id).html('显示密码');
	}
}
function TextToPassword(name){
	var control=document.getElementById(name);
	var newpassword = document.createElement("input");
	newpassword.type="password";
	//newpassword.name=control.name;
	newpassword.id=control.id;
	newpassword.value=control.value;
	newpassword.setAttribute("class",control.getAttribute("class"));
	newpassword.setAttribute("className",control.getAttribute("className"));
	newpassword.setAttribute("onblur",control.getAttribute("onblur"));
	newpassword.setAttribute("onkeyup",control.getAttribute("onkeyup"));
	setTimeout('document.getElementById("'+control.id+'").focus()',200);
	$("#"+name).replaceWith(newpassword);
}
function PasswordToText(name){
	var control=document.getElementById(name);
	var newpassword = document.createElement("input");
	newpassword.type="text";
	//newpassword.name=control.name;
	newpassword.id=control.id;
	newpassword.value=control.value;
	newpassword.setAttribute("class",control.getAttribute("class"));
	newpassword.setAttribute("className",control.getAttribute("className"));
	newpassword.setAttribute("onblur",control.getAttribute("onblur"));
	newpassword.setAttribute("onkeyup",control.getAttribute("onkeyup"));
	$("#"+name).replaceWith(newpassword);
}
function showtip(id){
	$("#tip"+id).show();
}
function hidetip(id){
	$("#tip"+id).hide();
}
function sendmsg(img){
	reg_checkAjax("moblie");
	var ajax_moblie = $("#ajax_moblie").text();
	if(ajax_moblie != '填写正确'){
		// 手机号ajax验证后有问题的，就不需要继续往下走了
		return false;
	}
	var date=$("#moblie").attr("date");
	var send=$("#send").val();

	var moblie = $("#moblie").val();
	if(!moblie){
		moblie = $("#usertel").val();
	}
	if(!moblie){
		moblie = $("#linkphone").val();
	}

	if(!moblie){
		layer.msg('手机不能为空！', 2, 8);return false;
	} 

	
	var code = '';
	var verify_token;
	if(code_kind==1){
		if($("#CheckCode").length>0){
			code=$.trim($("#CheckCode").val());  
			if(!code){
				layer.msg('图片验证码不能为空！', 2, 8);return false;
			}	
	    } 
	}else if(code_kind > 2){

		verify_token = $('input[name="verify_token"]').val();
		
		if(verify_token ==''){

			$("#bind-submit").trigger("click");
			
			return false; 
		}
	}
	if(send>0){ 
		layer.msg('请不要频繁重复发送！', 2, 8);return false;  
	}

	date = 1;
	if(date==1 && send==0){
		layer.closeAll('loading');
		$.post(weburl+"/index.php?m=ajax&c=regcode",{
			moblie:moblie,
				code:code,
				verify_token:verify_token
				},function(data){ 
					layer.closeAll('loading');
			if(data){
				var res = JSON.parse(data);
				var icon = res.error == 1 ? 9 : 8;
				layer.msg(res.msg, 2, icon, function(){
					if(res.error == 1){
						sendtime("121"); 
					}else if(res.error == 106){
						checkCode(img);
					}else if(res.error == 107){
						$("#popup-submit").trigger("click");
					}else{
						if(code_kind==1){
							checkCode(img);
						}else if(code_kind >2 ){
							$("#popup-submit").trigger("click");
						}
					}
				});
			}
		})
	}
}
function sendtime(i){
	i--;
	if(i==-1){
		$("#time").html("重新获取");
		$("#send").val(0)
	}else{
		$("#send").val(1)
		$("#time").html(i+"秒");
		setTimeout("sendtime("+i+");",1000);
	}
}
function exitsdate(id){
	if(document.getElementById(id)){
		if($('#'+id).attr('date')!='1'){
			return false;
		}else{
			return true;
		}
	}else{
		return true;
	}
}
function check_user(id,img){
	var email;
	var moblie;
	var moblie_code;
	var authcode;
	var username;
	var usertype=$("#usertype").val();	
	var arrayObj = new Array();
	var password;

	var verify_token;
	 

	reg_checkAjax("password");
	password = $.trim($("#password").val());
	arrayObj.push('password');

	if(exitsid("passconfirm")){
		reg_checkAjax("passconfirm");
		arrayObj.push('passconfirm');
	}

	if(exitsid("moblie")){
		reg_checkAjax("moblie");
		moblie = $.trim($('#moblie').val());
		arrayObj.push('moblie');
	}
	if(exitsid("email1")){
		reg_checkAjax("email1");
		email = $.trim($('#email1').val());
		arrayObj.push('email1');
	}
	if(exitsid("email3")){
		reg_checkAjax("email3");
		email = $.trim($('#email3').val());
		arrayObj.push('email3');
	}
	if(id=="1"){
		username=$.trim($("#username1").val());
		arrayObj.push('username1');
		reg_checkAjax("username1");
	}else if(id=="2"){
		//moblie = $.trim($('#moblie').val());
		var username=moblie;
		//arrayObj.push('moblie');
	}else if(id=="3"){
		//email=$.trim($("#email3").val());
		var username=email;
		//arrayObj.push('email3');
		//reg_checkAjax("email3");
	}

	if(exitsid("moblie_code")){
		reg_checkAjax("moblie_code");
		arrayObj.push('moblie_code');
		moblie_code=$.trim($("#moblie_code").val());
	}

	if(exitsid("CheckCode")){
			reg_checkAjax("CheckCode");
			arrayObj.push('CheckCode');
	}
	
	for(i=0;i<arrayObj.length;i++){
		if(!exitsdate(arrayObj[i])){
			return false;
		}
	}

	var codesear=new RegExp('注册会员');
	if(codesear.test(code_web) && !exitsid("moblie_code") ){
		if(code_kind==1){
			authcode=$("#CheckCode").val();
			if(authcode==''){
				return false;
			}
		}else if(code_kind > 2){
			verify_token = $('input[name="verify_token"]').val();
			
			if(verify_token ==''){
				$("#bind-submit").trigger("click");
					return false; 
			}
		}
	}
	
	if($("#xieyi"+id).attr("checked")!='checked'){  
		layer.msg('您必须同意注册协议才能成为本站会员！', 2, 8);return false;  
	}else{
		var loadi = layer.load('正在注册……',0);
		$.post(weburl+"/index.php?m=register&c=regsave",{
				username:username,
				password:password,
				email:email,
				moblie:moblie,
				moblie_code:moblie_code,
				
				authcode:authcode,
				codeid:id,
				verify_token:verify_token
			},function(data){
				layer.close(loadi);
				if(data){
         
					var data=eval('('+data+')');
					var status=data.status; 
					var msg=data.msg;
					if(status==1){
						window.location.href=data.url;//注册成功 选择身份
					}else{
						layer.msg(msg, 2,status,function(){
							if(code_kind==1){
								checkCode(img);
							}else if(code_kind>2){
								$("#popup-submit").trigger("click");
							}
						});return false;
					}
					
				}
			}
		);
	}
}
function check_login(url,img,num){

	var act_login=$("#act_login").val();
	var referurl=$("#referurl").val();
	var username='';
	var password='';
	if (act_login==0) {//普通账号登录
		username=$("#username").val();
		if(username=="" || username=="用户名"|| username=="邮箱/手机号/用户名"){ 
			$("#show_name").show();
			$("#username").focus(
			    function(){
			       $("#show_name").hide();
			    }
			);
			return false;
		}else{
		    $("#show_name").hide();
		}
		password=$("#password").val();
		if(password==""){
			$("#show_pass").show();
			$("#password").focus(
			    function(){
				    $("#show_pass").hide();
				}
			);
			return false;
		}else{
		    $("#show_pass").hide();
		}


	}else{//手机验证码登录
		
		username = $('#usermoblie').val();
		checkmoblie(username);
		password= $('#dynamiccode').val();
		if(password=="" || password=="短信动态码"){
			
			$("#show_dynamiccode").show();
			$("#dynamiccode").focus(
			    function(){
				    $("#show_dynamiccode").hide();
				}
			);
			return false;
		}else{
		    $("#show_dynamiccode").hide();
		}
	}

	//验证码验证
	var verify_token = '';
	var authcode = '';
	var codesear=new RegExp('前台登录');
	if(codesear.test(code_web) && act_login==0){
		if(code_kind==1){//数字验证
			if(exitsid("txt_CheckCode")){
				authcode=$("#txt_CheckCode").val();
				if(authcode==""||authcode=="验证码"){
					$("#show_code").show();
					$("#txt_CheckCode").focus(
						function(){
							$("#show_code").hide();
						}
					);
					return false;
				}else{
					$("#show_code").hide();
				}
			}
		}else if(code_kind > 2 ){//极验验证
			//改变验证需要的id
			$("#bind-captcha").attr('data-id','sublogin');
			verify_token = $('input[name="verify_token"]').val();
			
			if(verify_token == ''){
				$("#bind-submit").trigger("click");
				
				
				return false;
			}
		}
	}
	//是否记住登录状态
	if($("input[name=loginname]").attr("checked")=='checked'){
		var loginname=7;
	}else{
		var loginname=0;
	}
	var path=$("#path").val();
	var loadIndex = layer.load('登录中,请稍候...');
	$.post(url,{act_login:act_login,num:num,referurl:referurl,username:username,password:password,path:path,loginname:loginname,authcode:authcode,verify_token:verify_token},function(data){ 
    layer.close(loadIndex);
		
		var jsonObject = eval("(" + data + ")"); 
		if(jsonObject.error == '2'){//UC登录成功 
			$('#uclogin').html(jsonObject.msg); 
			setTimeout("window.location.href='"+jsonObject.url+"';",500); 
		}else if(jsonObject.error == '1'){//正常登录成功 			
			window.location.href=jsonObject.url; window.event.returnValue = false;return false;
		}else if(jsonObject.error == '0'){//登录失败或需要审核等提示 
			layer.msg(jsonObject.msg, 2, 8,function(){ 
				if(jsonObject.url){
					window.location.href=jsonObject.url; 
					window.event.returnValue = false;return false;
				}else{
					if(code_kind==1){
						checkCode(img);
						return false;
					}else if(code_kind>2){
 						$("#popup-submit").trigger("click");
						return false;
					
					}
				}
			}); 
			
		}
	});
	$("#txt_CheckCode").val('');
}
function checktype(id){
	$(".login_box_tit>li").attr('class','');
	if(id=='login_cur'){
		$("#lilogin_fast").addClass("login_fast");
	}else{
		$("#lilogin_cur").addClass("login_cur");
	}
	$(".login_box_cont>.lgoin_box_cot").hide();
	$("#"+id).show(); 
}
function checkmoblie(moblie){
	if(!isjsMobile(moblie)){ 
		$("#show_mobile").show();
		$("#usermobile").focus(
		    function(){
		       $("#show_mobile").hide();
		    }
		);
		return false;
	}else{
	    $("#show_mobile").hide();
	    return true;
	}
}

var Timer;
var smsTimer_time = 90;		//倒数 90
var smsTimer_flag = 90;		//倒数 90
var smsTime_speed = 1000;	//速度 1秒钟
//倒计时
function smsTimer(obj){
	if (smsTimer_flag > 0) {
		$(obj).html('重新发送('+smsTimer_flag+'s)');
		$(obj).attr({'style':'color:#f00;font-weight: bold;'});
		smsTimer_flag--;
	}else{
		$(obj).html('重新发送');
		$(obj).attr({'style':'color:#f00;font-weight: bold;'});
		smsTimer_flag = smsTimer_time;
		clearInterval(Timer);
	}
}

//发送手机短信
function send_msg2(url){
	var moblie = $('#usermoblie').val();
	if(!checkmoblie(moblie)){
		return false;
	}

	
	var verify_token	='';
	var code = '';
	
	var showCodeCheck = code_web.indexOf('前台登录');
	if(showCodeCheck >= 0){		
		if(code_kind==1){
			if($("#txt_CheckCode").length>0){

				code=$.trim($("#txt_CheckCode").val());  
				if(!code || code == '验证码'){
					layer.msg('图片验证码不能为空！', 2, 8);return false;
				}	
	    } 
		}else if(code_kind > 2){
			//改变验证需要的id
			$("#bind-captcha").attr('data-id','send_msg_tip');
			$("#bind-captcha").attr('data-type','click');
			verify_token = $('input[name="verify_token"]').val();
			
			if(verify_token ==''){

				$("#bind-submit").trigger("click");
					return false; 
			}
		}
	}

	var returntype = 1;
	layer.load('执行中，请稍候...',0);
	$.ajax({ 
		async: false, 
		type : "POST", 
		url : weburl+"/index.php?m=register&c=regmoblie", 
		dataType : 'text', 
		data:{
			'moblie':moblie
		},
		success : function(data) {
			layer.closeAll('loading');
			if(data == 0){
				returntype = 0;
			}
		} 
	});
	if(returntype == 0){
		layer.msg("手机号码不存在！",2,8);
		if(code_kind == 1) {
			checkCode('vcode_imgs');
		} else if(code_kind >2) {
			$("#popup-submit").trigger("click");
		}
		return false;
	}
	
	if(smsTimer_time==smsTimer_flag){
		Timer = setInterval("smsTimer($('#send_msg_tip'))", smsTime_speed);
		layer.load('执行中，请稍候...',0);
		$.post(url,{moblie:moblie,code:code,
		
			verify_token:verify_token
		},function(data){
			layer.closeAll('loading');
			if(data){
				var res = JSON.parse(data);
				if(res.error != 1){
					clearInterval(Timer);
				}
				var icon = res.error == 1 ? 9 : 8;
				layer.msg(res.msg, 2, icon, function(){
					if(res.error != 1){
						clearInterval(Timer);
						if(code_kind == 1) {
							checkCode('vcode_imgs');
						} else if(code_kind >2) {
							$("#popup-submit").trigger("click");
						}
					}
				});
			}
		})
	}else {
		layer.msg('请勿重复发送！', 2, 8);return false;
	}
}

//快捷登录绑定
function binduser(url){
	
	var username = $('#username').val();
	var password = $('#password').val();
	if(username && password){
		layer.load('执行中，请稍候...',0);
		$.post(url,{username:username,password:password},function(data){
			layer.closeAll('loading');
			var info = eval('('+data+')');
			if(info.url){
					layer.msg('绑定成功！', 2, 9,function(){window.location.href=info.url;}); 
			}else if(info.msg){
			
				layer.msg(info.msg, 2, 8);return false;
			}
		});
	}else{
	
		layer.msg('请输入需要绑定的账户、密码！', 2, 8);return false;
	}

}

function CheckPW(){
	
	$.layer({
		type: 1,
		title: '验证身份',
		offset: [($(window).height() - 200) / 2 + 'px', ''],
		closeBtn: [0, true],
		border: [10, 0.3, '#000', true],
		area: ['350px', '250px'],
		page: {
			dom: "#postpw"
		}
	});

}

function post_pass(img) {
	var zyuid = $("#zy_uid").val();
	var mobile = $("#zy_mobile").val();
	var email = $("#zy_email").val();
	var pw = $("#pw").val();
	var code = $("#code").val();
	
	if(zyuid==""){
		layer.msg('该用户不存在', 2, 8);
		return false;
	}
	if(pw == "") {
		layer.msg('请输入密码', 2, 8);
		return false;
	}
	if(code == "") {
		layer.msg('请输入验证码', 2, 8);
		return false;
	}
	layer.load('执行中，请稍候...',0);
 	$.post("index.php?m=register&c=writtenOff", {
		zyuid: zyuid,
		mobile: mobile,
		email: email,
		pw: pw,
		code: code
	},function(data) {
		layer.closeAll('loading');
		if(data == 3) {
			layer.msg('验证码错误！', 2, 8);
			checkCode(img);
			return false;
		} else if(data == 2) {
			layer.msg('密码错误！', 2, 8);
			return false;
		} else if(data == 1){
			layer.closeAll();
			
			layer.msg('解绑成功！', 2, 9,function(){
				window.location.reload();
			});
 		}
	})
}
function creatacount(){
	var provider = $("#provider").val();
	loadlayer();
	$.post(weburl+"/index.php?m=login&c=balogin", {provider:provider}, function(data) {
		layer.closeAll();
		data = eval('(' + data + ')');

		if (data.url != '' && data.msg != '') {
			layer.msg(data.msg, 2, 9, function() {
				window.location.href = data.url;
			});
		} else if (data.url != '') {
			window.location.href = data.url;
		}
	});
}
function bindacount(url,img,num){

	
	var username='';
	var password='';
	var provider = $("#provider").val();
	username=$("#username").val();
	if(username=="" || username=="用户名"|| username=="邮箱/手机号/用户名"){ 
		$("#show_name").show();
		$("#username").focus(
		    function(){
		       $("#show_name").hide();
		    }
		);
		return false;
	}else{
	    $("#show_name").hide();
	}
	password=$("#password").val();
	if(password==""){
		$("#show_pass").show();
		$("#password").focus(
		    function(){
			    $("#show_pass").hide();
			}
		);
		return false;
	}else{
	    $("#show_pass").hide();
	}


	//验证码验证

	var verify_token = '';
	var authcode = '';
	var codesear=new RegExp('前台登录');
	if(codesear.test(code_web)){
		if(code_kind==1){//数字验证
			if(exitsid("txt_CheckCode")){
				authcode=$("#txt_CheckCode").val();
				if(authcode==""||authcode=="验证码"){
					$("#show_code").show();
					$("#txt_CheckCode").focus(
						function(){
							$("#show_code").hide();
						}
					);
					return false;
				}else{
					$("#show_code").hide();
				}
			}
		}else if(code_kind >2){//极验验证
			//改变验证需要的id
			$("#bind-captcha").attr('data-id','sublogin');
			verify_token = $('input[name="verify_token"]').val();
		
			if(verify_token ==''){
				$("#bind-submit").trigger("click");
				
				
				return false;
			}
		}
	}
	//是否记住登录状态
	var loginname=0;
	
	var loadIndex = layer.load('登录中,请稍候...');
	$.post(wxbindacounturl,{username:username,password:password,provider:provider,authcode:authcode,verify_token:verify_token},function(data){ 
    	layer.close(loadIndex);

		
		var jsonObject = eval("(" + data + ")"); 

		if(jsonObject.error == '1'){//正常登录成功 

			window.location.href=jsonObject.url; window.event.returnValue = false;return false;

		}else if(jsonObject.error == '0'){//登录失败或需要审核等提示 

			layer.msg(jsonObject.msg, 2, 8,function(){ 
				if(jsonObject.url){
					window.location.href=jsonObject.url; 
					window.event.returnValue = false;return false;
				}else{
					if(code_kind==1){
						checkCode(img);
						return false;
					}else if(code_kind>2){
 						$("#popup-submit").trigger("click");
						return false;
					
					}
				}
			}); 
			
		}
	});
	$("#CheckCode").val('');
}
// 第三方登录，实名认证时绑定手机号
function bindphone(img){
	var moblie;
	var moblie_code;
	var authcode;
	var arrayObj = new Array();
	
	var verify_token;
	var provider = $("#provider").val();
	
	if(exitsid("moblie")){
		reg_checkAjax("moblie");
		moblie = $.trim($('#moblie').val());
		arrayObj.push('moblie');
	}

	if(exitsid("moblie_code")){
		reg_checkAjax("moblie_code");
		arrayObj.push('moblie_code');
		moblie_code=$.trim($("#moblie_code").val());
	}

	if(exitsid("CheckCode")){
		reg_checkAjax("CheckCode");
		arrayObj.push('CheckCode');
	}
	
	for(i=0;i<arrayObj.length;i++){
		if(!exitsdate(arrayObj[i])){
			return false;
		}
	}

	var codesear=new RegExp('注册会员');
	if(codesear.test(code_web) && !exitsid("moblie_code") ){
		if(code_kind==1){
			authcode=$("#CheckCode").val();
			if(authcode==''){
				return false;
			}
		}else if(code_kind > 2){
			verify_token = $('input[name="verify_token"]').val();
			
			if(verify_token ==''){
				$("#bind-submit").trigger("click");
					return false; 
			}
		}
	}
	
	var loadi = layer.load('手机号绑定中……',0);
	$.post(wxregurl,{
			moblie:moblie,
			moblie_code:moblie_code,
			authcode:authcode,
		
			verify_token:verify_token,
			provider: provider
		},function(data){
			layer.close(loadi);
			if(data){
				var data=eval('('+data+')');
				var status=data.status; 
				var msg=data.msg;
				if(status==9){
					window.location.href=data.url;//手机号绑定成功 选择身份
				}else{
					layer.msg(msg, 2,status,function(){
						if(code_kind==1){
							checkCode(img);
						}else if(code_kind>2){
							$("#popup-submit").trigger("click");
						}
					});return false;
				}
			}
		}
	);
}