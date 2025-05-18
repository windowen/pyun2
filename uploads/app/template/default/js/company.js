function exitsid(id) {
	if (document.getElementById(id)) {
		return true;
	} else {
		return false;
	}
}

function notuser() {
	$.layer({
		type : 1,
		title : '网站提示',
		area : [ '350px', 'auto' ],
		page : {
			dom : "#notuser"
		}
	});
}
function switching() {
	layer.closeAll();
	$.get(weburl+'/index.php?c=changeusertype',{usertype:1},function(msg){

		var data=eval('('+msg+')');
		if(data.errcode == 9){
			layer.msg('切换成功！', 2, 9, function(){
				 location.reload();
				window.event.returnValue = false;
				return false;
			});
		}else if(data.msg == 9){
			layer.msg(data.msg, 2, 8);
		}else{
			layer.msg('切换失败！', 2, 8);
		}
	});
	
}
function applyjobuid() {
	window.addresumelayer = $.layer({
		type : 1,
		fix : false,
		maxmin : false,
		shadeClose : true,
		title : '完善简历',
		offset : [ ($(window).height() - 650) / 2 + 'px', '' ],
		closeBtn : [ 0, true ],
		zIndex:100,
		area : [ '760px', '680px' ],
		page : {
			dom : "#applydiv"
		}
	})
}
function addresumereturn() {
	layer.closeAll();
	window.addresumelayer = $.layer({
		type : 1,
		fix : false,
		maxmin : false,
		shadeClose : true,
		title : '完善简历',
		offset : [ ($(window).height() - 650) / 2 + 'px', '' ],
		closeBtn : [ 0, true ],
		area : [ '760px', '680px' ],
		zIndex:100,
		page : {
			dom : "#applydiv"
		}
	})
}
function nextaddresume() {
	var uname = $.trim($("#uname").val());
	var sex = $("#sex").val();
	var birthday = $.trim($("#birthday").val());
	var edu = $.trim($("#educid").val());
	var exp = $.trim($("#expid").val());
	var telphone = $.trim($("#telphone").val());
	var email = $.trim($("#email").val());
	var type = $.trim($("#typeid").val());
	var report = $.trim($("#reportid").val());
	var authcode = $.trim($("#authcode").val());
	var checkcode = $.trim($("#CheckCodefast").val());

	if (uname == "") {
		parent.layer.msg("请填写真实姓名！", 2, 8);
		return false;
	}
	if (sex == "") {
		parent.layer.msg("请填写性别！", 2, 8);
		return false;
	}
	if (edu == "") {
		parent.layer.msg("请选择最高学历！", 2, 8);
		return false;
	}
	if (exp == "") {
		parent.layer.msg("请选择工作经验！", 2, 8);
		return false;
	}
	
	if (telphone == '') {
		parent.layer.msg("请填写手机号码！", 2, 8);
		return false;
	} else {
		if (!isjsMobile(telphone)) {
			parent.layer.msg("手机号码格式错误！", 2, 8);
			return false;
		} else {
			var authcode = $("#authcode");
			if (authcode.length > 0 && authcode.val() == '') {
				parent.layer.msg("请输入短信验证码！", 2, 8);
				return false;
			}
		}
	}
	layer.closeAll();
	window.nextaddresumelayer = $.layer({
		type : 1,
		fix : false,
		maxmin : false,
		shadeClose : true,
		title : '快速申请职位',
		offset : [ ($(window).height() - 650) / 2 + 'px', '' ],
		closeBtn : [ 0, true ],
		area : [ '760px', '620px' ],
		zIndex: 100,
		page : {
			dom : "#nextaddresume"
		}
	})
}
function OnLogin() {
	layer.closeAll();
	showlogin('1');
}
function checkaddresume(img, imgreg, url) {
	var jobid = $.trim($("#jobid").val());
	var uname = $.trim($("#uname").val());
	var sex = $("#sex").val();
	var birthday = $.trim($("#birthday").val());
	var edu = $.trim($("#educid option:selected").val());
	var exp = $.trim($("#expid option:selected").val());
	var telphone = $.trim($("#telphone").val());
	var email = $.trim($("#email").val());
	var type = $.trim($("#typeid").val());
	var report = $.trim($("#reportid").val());
	
	var password = $("#reg_password").val();
	var resumeid = $("#resumeid").val();
	var jobid = $("#jobid").val();
	if (uname == "") {
		parent.layer.msg("请填写真实姓名！", 2, 8);return false;
	}
	if (sex == "") {
		parent.layer.msg("请填写性别！", 2, 8);return false;
	}
	if (edu == "") {
		parent.layer.msg("请选择最高学历！", 2, 8);return false;
	}
	if (exp == "") {
		parent.layer.msg("请选择工作经验！", 2, 8);return false;
	}

	if (telphone == '') {
		parent.layer.msg("请填写手机号码！", 2, 8);return false;
	} else {
		if (!isjsMobile(telphone)) {
			parent.layer.msg("手机号码格式错误！", 2, 8);return false;
		}
	}
	
	if (password == "") {
		parent.layer.msg("请输入密码！", 2, 8);return false;
	}else if (password.length < 6 || password.length > 20) {
		parent.layer.msg("请输入6至20位密码！", 2, 8);return false;
	}
	
	if(code_kind == '1') {
		var checkcode = $.trim($("#CheckCodefast").val());
		if (checkcode == '') {
			parent.layer.msg("请输入图片验证码！", 2, 8);
			return false;
		}
	} else if(code_kind >2) {
		//改变验证需要的id
		$("#bind-captcha").attr('data-id','subform');
		$("#bind-captcha").attr('data-type','submit');
		var verify_token = $('input[name="verify_token"]').val();
	
		
		if(verify_token == '') {
			$("#bind-submit").trigger("click");
			return false;
		}
	
	}
	var authcode = $("#authcode");
	if (authcode.length > 0 && authcode.val() == '') {
		parent.layer.msg("请输入短信验证码！", 2, 8);
		return false;
	}
	
	if (document.getElementById('resume_exp').value == '1') {
		var workname = document.getElementById('workname'), 
			worksdate = document.getElementById('worksdate'), 
			workedate = document.getElementById('workedate'), 
			worktitle = document.getElementById('worktitle'), 
			workcontent = document.getElementById('workcontent');
		if (workname.value == '') {
			parent.layer.msg("请填写公司名称！", 2, 8);
			return false;
		}
		if (worktitle.value == '') {
			parent.layer.msg("请填写担任职务！", 2, 8);
			return false;
		}
		if (worksdate.value == '') {
			parent.layer.msg("请填写入职时间！", 2, 8);
			return false;

		}
	}
	if (document.getElementById('resume_edu').value == '1') {
		var eduname = document.getElementById('eduname'), 
			edusdate = document.getElementById('edusdate'), 
			eduedate = document.getElementById('eduedate'), 
			education = $.trim($("#educationcid option:selected").val()), 
			eduspec = document.getElementById('eduspec');
		if (eduname.value == '') {
			parent.layer.msg("请填写学校名称！", 2, 8);
			return false;
		}
		if (edusdate.value == '') {
			parent.layer.msg("请填写入学时间！", 2, 8);
			return false;
		}
		if (eduedate.value == '') {
			parent.layer.msg("请填写离校或预计离校时间！", 2, 8);
			return false;
		}
		
		if (education == '') {
			parent.layer.msg("请选择毕业学历！", 2, 8);
			return false;
		}
	}
	if (document.getElementById('resume_pro').value == '1') {
		var proname = document.getElementById('proname'), 
			prosdate = document.getElementById('prosdate'), 
			proedate = document.getElementById('proedate'), 
			protitle = document.getElementById('protitle'), 
			procontent = document.getElementById('procontent');
		if (proname.value == '') {
			parent.layer.msg("请填写项目名称！", 2, 8);
			return false;
		}
		if (protitle.value == '') {
			parent.layer.msg("请填写项目担任职务！", 2, 8);
			return false;
		}
		if (prosdate.value == '') {
			parent.layer.msg("请填写项目开始时间！", 2, 8);
			return false;
		}
		if (proedate.value == '') {
			parent.layer.msg("请填写项目结束时间！", 2, 8);
			return false;
		}
	}
	layer.load('执行中，请稍候...',0);
}

function ckjobreg(id) {
	var telphone = $.trim($("#telphone").val());
	var email = $.trim($("#email").val());
	if (id == 1) {
		if (telphone !== '') {
			if (!isjsMobile(telphone)) {
				parent.layer.msg("手机号码格式错误！", 2, 8);
				return false;
			} else {
				$.post(weburl + "/index.php?m=register&c=regmoblie", {
					moblie : telphone
				}, function(data) {
					if (data != 0) {
						parent.layer.msg("手机号码已被使用！", 2, 8);
						return false;
					}
				});
			}
			return true;
		} else {
			parent.layer.msg("手机号码格式错误！", 2, 8);
			return false;
		}
	} else {
		if (email == '') {
			parent.layer.msg("请填写联系邮箱！", 2, 8);
			return false;
		} else {
			var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
			if (!myreg.test(email)) {
				parent.layer.msg("邮箱格式错误！", 2, 8);
				return false;
			} else {
				$.post(weburl + "/index.php?m=register&c=ajaxreg", {
					email : email
				}, function(data) {
					if (data != 0) {
						parent.layer.msg("邮箱已被使用！", 2, 8);
						return false;
					}
				});
			}
		}
	}
}

var Timer;
var smsTimer_time = 90; // 倒数 90
var smsTimer_flag = 90; // 倒数 90
var smsTime_speed = 1000; // 速度 1秒钟
// 发送手机短信
function send_msg(url) {
	
	var telphone = $('#telphone').val();
	var checkcode,
		
		verify_token;
	
	if (telphone == '') {
		parent.layer.msg("请填写手机号码！", 2, 8);
		return false;
	} else {
		if (!isjsMobile(telphone)) {
			parent.layer.msg("手机号码格式错误！", 2, 8);
			return false;
		} else {
			if(code_kind == '1') {
				checkcode = $.trim($("#CheckCodefast").val());
				if (checkcode == '') {
					parent.layer.msg("请输入图片验证码！", 2, 8);
					return false;
				}
			} else if(code_kind > 2) {
				//改变验证需要的id
				$("#bind-captcha").attr('data-id','send_msg_tips');
				$("#bind-captcha").attr('data-type','click');
				verify_token = $('input[name="verify_token"]').val();
			
				if(verify_token == '') {
					$("#bind-submit").trigger("click");
					return false;
				}
			
			}

			var returntype;
			layer.load('执行中，请稍候...',0);
			$.ajax({
				async : false,
				type : "POST",
				url : weburl + "/index.php?m=register&c=regmoblie",
				dataType : 'text',
				data : {
					'moblie' : telphone
				},
				success : function(data) {
					layer.closeAll('loading');
					if (data != 0) {
						returntype = 1;
					}
				}
			});
			if (returntype == 1) {
				parent.layer.msg("手机号码已被使用！", 2, 8);
				return false;
			}
		}
	}
	if (smsTimer_time == smsTimer_flag) {
		Timer = setInterval("smsTimer($('#send_msg_tips'))", smsTime_speed);
		layer.load('执行中，请稍候...',0);
		$.post(url, {
			moblie : telphone,
			authcode : checkcode,
			
			verify_token: verify_token
		}, function(data) {
			layer.closeAll('loading');
			if (data) {
				var res = JSON.parse(data);
				if (res.error != 1) {
					clearInterval(Timer);
				}
				var icon = res.error == 1 ? 9 : 8;
				parent.layer.msg(res.msg, 2, icon, function() {
					if (res.error != 1) {
						clearInterval(Timer);
						if (code_kind == 1) {
							checkCode('vcode_imgs');
						} else if (code_kind>2) {
							$("#popup-submit").trigger("click");
						}
					}
				});
			}
		})
	} else {
		layer.msg('请勿重复发送！', 2, 8);
		return false;
	}
}

// 倒计时
function smsTimer(obj) {
	if (smsTimer_flag > 0) {
		$(obj).html('重新发送(' + smsTimer_flag + 's)');
		$(obj).attr({
			'style' : 'color:#f00;font-weight: bold;'
		});
		smsTimer_flag--;
	} else {
		$(obj).html('重新发送');
		$(obj).attr({
			'style' : 'color:#f00;font-weight: bold;'
		});
		smsTimer_flag = smsTimer_time;
		clearInterval(Timer);
	}
}

// 推荐太多
function recommendToMuch(maxNum) {
	layer.msg('每天最多推荐' + maxNum + '次职位/简历！', 2, 8);
}

// 检查上一次推荐职位、简历的时间间隔
function recommendInterval(uid, url, interval) {
	var ajaxUrl = weburl + "/index.php?m=ajax&c=ajax_recommend_interval";
	layer.load('执行中，请稍候...',0);
	$.post(ajaxUrl, {
		uid : uid
	}, function(data) {
		layer.closeAll('loading');
		data = eval('(' + data + ')');
		if (data.status == 0) {
			window.location = url;
			// window.open(url);
		} else {
			layer.msg(data.msg, 3, 8);
		}
	});
}