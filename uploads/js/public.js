
function loadlayer(){
	return parent.layer.load('执行中，请稍候...',0);
} 

//认领简历、企业
function claim(url){
	var loadi = layer.load('执行中，请稍候...',0);
	$.get(url,function(data){
		var data=eval('('+data+')');
		layer.close(loadi); 
		//layer.confirm(data.msg,9);return false; 
		$("#claimmsg").html(data.msg);
		$.layer({
			type : 1,
			title :'认领信息', 
			border : [10 , 0.3 , '#000', true],
			area : ['380px','auto'],
			page : {dom :"#status_div"}
		});
	})
}

$(function() {
   $.get(weburl+'/index.php?m=cron');
});

$(function() {
	var offset = 150;
    $(window).scroll(function(){
	( $(this).scrollTop() > offset ) ? $("#header_fix").show() : $("#header_fix").hide();
    });
	$(".header_fixed_login_dl").hover(function(){
	    var t=$(this).attr("did");
		$("#"+t+"_t").show();
	},function(){
	    var t=$(this).attr("did");
	   $("#"+t+"_t").hide();    
	});
});

$(document).ready(function() {
    $(".hp_top_rt_regist").hover(function() {
        $(".hp_top_regist_list").show();
    },function(){
        $(".hp_top_regist_list").hide();
	});
	$('.hp_nav li').hover(function() {
		var aid=$(this).attr('aid');
		$("#nav"+aid).addClass('hp_nav_cur');        
    },function(){
		var aid=$(this).attr('aid');
		$("#nav"+aid).removeClass('hp_nav_cur');
	});
	$('.hp_head_search_job').hover(function() {
        $('.yunHeaderSearch_list_box').show();
    },function(){
        $('.yunHeaderSearch_list_box').hide();
	});
	$('.fz_index_search_select').hover(function() {
        $('.yunHeaderSearch_list_box').show();
    },function(){
        $('.yunHeaderSearch_list_box').hide();
	});
});

//验证码，根据id刷新验证码，无需写多个方法
function checkCode(id, refresh){
	if(document.getElementById(id) && !refresh){
		document.getElementById(id).src=weburl+"/app/include/authcode.inc.php?"+Math.random();
	}
}

//由于复选框一般选中的是多个,所以可以循环输出
function get_comindes_jobid(){
	var codewebarr="";
	$("input[name=checkbox_job]:checked").each(function(){ //由于复选框一般选中的是多个,所以可以循环输出
		if(codewebarr==""){codewebarr=$(this).val();}else{codewebarr=codewebarr+","+$(this).val();}
	});
	return codewebarr;
}
function search_keyword(myform,defkeyword){
    var keyword = myform.keyword.value;
	if(defkeyword==keyword&&keyword){
		myform.keyword.value='';
	}
}
function check_keyword(name){
	var keyword=$("#keyword").val();
	if(keyword&&keyword==name){$("#keyword").val('');}
}

function search_hide(id){
	$("#"+id).hide();
}

function sqjobtop() {

	var resumenum = $("#resumenum").val();

	if(resumenum>1){

		var jobid = $("#jobid").val();
	
		$.post(weburl + "/index.php?m=ajax&c=index_ajaxjob", { jobid: jobid }, function(data) {
			
			data = eval('(' + data + ')');

			if(data.status == 1){
				
				$(".POp_up_r").html('');
				$(".POp_up_r").append(data.resumeList);

				var msglayer = layer.open({
					type: 1,
					title: '职位申请',
					closeBtn: 1,
					border: [10, 0.3, '#000', true],
					area: ['450px', 'auto'],
					content: $("#sqjob_show")
				});


			}else{

				if(data.login){
					showlogin('1');
				}else if(data.alert){

					layer.alert(data.msg, 0, '提示', function() {
					
						window.location.href = weburl + "/member/index.php?c=expect&act=add";
						window.event.returnValue = false;
						return false;
					});
				}else{

					layer.msg(data.msg, 2, 8);
				}
			}
		});
	}else{
		click_sq();
	}

	
}

function sqjobclic(id){
	$(".job_prompt_sendresume_list").removeClass("job_prompt_sendresume_list_cur");
	$('#resume_'+id).addClass("job_prompt_sendresume_list_cur");
}
function logout(url,redirecturl){
	$.get(url,function(msg){
		if(msg==1 || msg.indexOf('script')){
			if(msg.indexOf('script')){
				$('#uclogin').html(msg);
			}
			window.localStorage.setItem("socketState", "2");
			layer.msg('您已成功退出！', 2, 9,function(){window.location.href =redirecturl?redirecturl:weburl;});
		}else{
			layer.msg('退出失败！', 2, 8);
		}
	});
}

function cutusertype(url, utype){
	window.location.href = url+"&utype="+utype;
}
	
function logoutUser(url,tourl){
	$.get(url,function(msg){
		if(msg==1 || msg.indexOf('script')){
			if(msg.indexOf('script')){
				$('#uclogin').html(msg);
			}
			window.localStorage.setItem("socketState", "2");
			window.location.href = tourl;
		}
	});
}
function click_sq(){
	var companyname=$("#companyname").val();
	var jobname=$("#jobname").val();
	var companyuid=$("#companyuid").val();
	var jobid=$("#jobid").val();
	var eid=$(".job_prompt_sendresume_list_cur").length ? $(".job_prompt_sendresume_list_cur").attr('data_did') : '';
	loadlayer();
	$.post(weburl+"/index.php?m=ajax&c=sq_job",{companyname:companyname,jobname:jobname,companyuid:companyuid,jobid:jobid,eid:eid},function(res){
		layer.closeAll();
		res = eval('(' + res + ')');

		var data = res.errorcode;
		var msg = res.msg;

		if(data==4){
			layer.msg('该职位已邀请您面试，无需再投简历！', 2, 8);
		}else if(data==9){
			var i = layer.open({
				type: 1,
				title: '职位申请',
				closeBtn: 1,
				border: [10, 0.3, '#000', true],
				area: ['520px', 'auto'],
				content: $("#suc_job"),
				cancel:function(){
					window.location.reload();
				}
			});
		}else if(data==2){
			layer.msg('系统出错，请稍后再试！', 2,8);return false;
		}else if(data==3){
			layer.msg('您已申请过该职位！', 2,8);return false;
		}else if(data==5){
			layer.msg('该职位已过期，不能申请该职位！', 2, 8);return false;
		}else if(data==6){
			layer.msg('该职位不存在！', 2, 8);return false;
		}else if(data==7){
			layer.msg('该简历完整度未达到'+user_sqintegrity+'%，请先完善简历！', 2 , 8 ,function(){window.location.href =weburl+"/member/index.php?c=resume";window.event.returnValue = false;return false; });
		}else if(data==8){
			layer.msg('请选择投递的简历！', 2, 8);return false;
		}else if(data==10){
			layer.msg('请先公开您的简历！', 2, 8,function(){window.location.href =weburl+"/member/index.php?c=resume";window.event.returnValue = false;return false; });return false;
		}else if(data==11){
			layer.msg(msg, 2, 8);return false;
		}else{
			layer.alert('请先登录！',0,'提示',function(){window.location.href="index.php?m=login&usertype=1";window.event.returnValue=false;return false;});
		}
	});
}

$(document).ready(function(){	
	//职位详情页 申请职位
	$(".sq_job").click(function(){
		sqjobtop();
		
	});

	//头部提醒
	$("#login_head_div").on('mouseover', '.yun_topLogin', function(){
		$(this).find(".yun_More").attr("class","yun_More yun_Morecurrent");
		$(this).find("ul").show();
	});
	$("#login_head_div").on('mouseout', '.yun_topLogin', function(){
		$(this).find(".yun_More").attr("class","yun_More");
		$(this).find("ul").hide();
	});

	$("#login_head_div").on('mouseover', '.yun_topNav', function(){
		$(this).find(".yun_navMore").attr("class","yun_navMore yun_webMorecurrent");
		$(this).find(".yun_webMoredown").show();
	});
	$("#login_head_div").on('mouseout', '.yun_topNav', function(){
		$(this).find(".yun_navMore").attr("class","yun_navMore");
		$(this).find(".yun_webMoredown").hide();
	});
	//职位详情页 申请职位
	$("#click_sq").click(function(){
		click_sq();
	})
	
	/* PC 端，输入邀请信息，点击邀请操作 */
	$("#click_invite").click(function(){
		layer.closeAll();

		//判断是否达到每天最大操作次数
		$.post(weburl + '/index.php?m=ajax&c=ajax_day_action_check', {'type' : 'interview'}, function(data){
			
			data = eval('(' + data + ')');
			
			if(data.status == -1){
				
				layer.msg(data.msg, 2, 8);
			}else if(data.status == 1){
				
				if($("#jobtype").length>0){
				
					var jobtype=$("#jobtype").val();
				}else{
					
					var jobtype=0;
				}
				
				var uid			=	$("#uid").val();
				var content		=	$("#content").val();
				var username	=	$("#username").val();
				var jobname		=	$("#name").val();
				var jobid		=	$("#nameid").val();
		 		var intertime	=	$("#intertime").val();
				var linkman		=	$("#linkman").val();
				var linktel		=	$("#linktel").val();
				var address		=	$("#address").val();
				var ymid		=	$("#ymid").val();
				
				var save_yqmb	= 	$("#save_yqmb").attr("checked")=='checked' ? 1 : 0;

				if($.trim(intertime) == ''){
				
					layer.msg('面试时间不能为空！', 2, 8);return false;
				}
				
				if ((isjsTell(linktel) != true) && (isjsMobile(linktel) != true) && ($.trim(linktel) != '')) {
					
				    layer.msg('电话格式错误！', 2,8); return false;
				}
				if($.trim(address) == ''){
					
					layer.msg('面试地点不能为空！', 2, 8);return false;
				}
				loadlayer();
				
				$.post(weburl+"/index.php?m=ajax&c=sava_ajaxresume",{uid:uid,content:content,username:username,jobname:jobname,address:address,linkman:linkman,linktel:linktel,intertime:intertime,jobid:jobid,jobtype:jobtype,save_yqmb:save_yqmb,ymid:ymid},function(data){
				
					layer.closeAll();
					
					var data	=	eval('('+data+')');
					var status	=	data.status;
					
					if(status == 4){

						
				        
						layer.msg(data.msg, 2, 8,function(){
							$.layer({
								type : 1,
								offset: ['100px', ''],
								title :'邀请面试', 
								closeBtn : [0 , true],
								border : [10 , 0.3 , '#000', true],
								area : ['auto','auto'],
								page : {dom :"#job_box"}
							});
						}); 
					}else if(status == 3){
						
						layer.msg('您已成功邀请！', 2, 9,function(){location.reload();});
					}else{
						
						var login	=	data.login;
						if(login){
							
							layer.confirm(data.msg, {
								btn: ['登录','取消']  
							}, function(){
								window.location.href 		=	wapurl + "index.php?m=login&usertype=2&type=out";
								window.event.returnValue 	= 	false;
								return false;
							});
						}else{
							
							layer.msg(data.msg, 2, 8);
						}
					}
 					
					 
				});
			}
		});//end function(data)
	});//end $("#click_invite").click()

	

	$("input[name=city]").click(function(){
		$('.city_box').show();
	})
	$(".p_t_right").click(function(){
		$("#bg").hide(1000);
		$('.city_box').hide(1000);
	})
	$("#colse_box").click(function(){
		$('.job_box').hide();
	})
	$("#close_job").click(function(){
		var check_val="0";
		var name_val = "不限";
		$("input[type='checkbox'][name='job_box']:checked").each(function(){
		  var info = $(this).val().split("+");
			  check_val+=","+info[0];
			  name_val+="+"+info[1];
		  });
		  check_val = check_val.replace("0,","");
		  $("#qw_job").val(check_val);
		  name_val = name_val.replace("不限+","");
		  $("#qw_show_job").html(name_val);
		  $("#bg").hide(1000);
		  $('#pannel_job').hide(1000);
	})
	$("#click").click(function(){
		var info = $("input[@type=radio][name=cityid][checked]").val();
		var info_arr = info.split("+");
		var name = info_arr[0];
		var id = info_arr[1];
		$("#sea_place").val(name);
		$("#cityid").val(id);
		$("#bg").attr("style","display:none");
		$('.city_box').hide(1000);
	});
	$("#click_head").click(function(){
		var info = $("input[@type=radio][name=cityid][checked]").val();
		var info_arr = info.split("+");
		var name = info_arr[0];
		var id = info_arr[1];
		$("#sea_place_head").val(name);
		$("#cityid_head").val(id);
		$("#bg").hide(1000);
		$('#city_box_head').hide(1000);
	});
	$(".header_seach_find").mouseover(function(){
	    $(".index_header_seach_find_list").show();
	}).mouseout(function(){
	    $(".index_header_seach_find_list").hide();
	});
	$(".header_seach_find_list").mouseover(function(){
	    $(".index_header_seach_find_list").show();
	});
	
	$(".index_search_place").mouseover(function(){
		$(".index_place_position").show();
	}).mouseout(function(){
		$(".index_place_position").hide();
	});
	$(".index_place_position").mouseover(function(){
		$(".index_place_position").show();
	});
	$(".Company_post_ms span").click(function(){
		$(".Company_post_ms span").attr("class","");
		$(this).attr("class","Company_post_cur");
		$(".Company_toggle").hide();
		var name=$(this).attr("name");
		$("#Company_job_"+name).show();
	});

	//头部提醒
	$("#login_head_div").on('mouseover', '.header_Remind_hover', function(){
		$(".header_Remind_list").show();
		$(".header_Remind_em").addClass("header_Remind_em_hover");
	});
	$("#login_head_div").on('mouseout', '.header_Remind_hover', function(){
		$(".header_Remind_list").hide();
		$(".header_Remind_em_hover").removeClass("header_Remind_em_hover");
	});

	//前台头部登录后样式
	$("#header_fix").on('mouseover', '.header_fixed_login_after', function(){
		$(".header_fixed_reg_box").show();
	});
	$("#header_fix").on('mouseout', '.header_fixed_login_after', function(){
		$(".header_fixed_reg_box").hide();
	});
	
	if(!isPlaceholder('input')){
		$("input").not("input[type='password']").each(//把input绑定事件 排除password框
		function(){
			
			if($(this).val()=="" && $(this).attr("placeholder")!=""){
				$(this).val($(this).attr("placeholder"));
				$(this).focus(function(){
					if($(this).val()==$(this).attr("placeholder")) $(this).val("");
				});
				$(this).blur(function(){
					if($(this).val()=="") $(this).val($(this).attr("placeholder"));
				});
			}
		});
	}
	if(!isPlaceholder('textarea')){
		$("textarea").each(//把textarea绑定事件
		function(){
			if($(this).val()=="" && $(this).attr("placeholder")!=""){
				$(this).val($(this).attr("placeholder"));
				$(this).focus(function(){
					if($(this).val()==$(this).attr("placeholder")) $(this).val("");
				});
				$(this).blur(function(){
					if($(this).val()=="") $(this).val($(this).attr("placeholder"));
				});
			}
		});
	};
})

// pc端邀请面试
function sq_resume(obj){
	var jobid = $(obj).attr("jobid");
	var r_uid = $(obj).attr("uid");	// 人才UID
	
	if(jobid){
		$("#nameid").val(jobid);
		selects( jobid, 'name', $(this).attr("jobname") );
	}
	
	var jobtype	=	'';
	
	if($(obj).attr("uid")){$("#uid").val($(obj).attr("uid"));}
	if($(obj).attr("username")){$("#username").val($(obj).attr("username"));}
	if($(obj).attr("jobtype")){jobtype=$(obj).attr("jobtype");}
	
	//判断是否达到每天最大操作次数
	$.post(weburl + '/index.php?m=ajax&c=ajax_day_action_check', {'type' : 'interview'}, function(data){
			
		data = eval('(' + data + ')');
		
		if(data.status == -1){
			
			layer.msg(data.msg, 2, 8);
			
		}else if(data.status == 1){

			$.post(weburl+"/index.php?m=ajax&c=indexajaxresume",{show_job:1,jobid:jobid,jobtype:jobtype, ruid : r_uid},function(data){
				
				var data	=	eval('('+data+')');
				
				var status	=	data.status;
				if(data.jobname){ $("#name").val(data.jobname); }
				if(data.linkman){ $("#linkman").val(data.linkman); }
				if(data.linktel){ $("#linktel").val(data.linktel); }
				if(data.address){ $("#address").val(data.address); }
				
				if(status == 1){	//	没有职位
					
					var msgList		=	data.msgList;
					var	msg			=	msgList.join('');
					var	invite		=	data.invite;
					
					if(invite	==	1){
						$('#tips_way').html('<div class="yun_prompt_release_tip">以下条件尚未满足,暂时无法邀请面试，请按顺序完成：</div>');
					}
					
					$("#msgList").html(msg);
					
					var msgLayer	=	layer.open({
						
						type		:	1,
						title		:	'温馨提示',
						closeBtn	:	1,
						border		: 	[10, 0.3, '#000', true],
						area		: 	['auto', 'auto'],
						content		: 	$("#jobcheck")
					});
					
				}else if(status == 2){	//	购买服务
					
					server_single('invite');
					firstTab();
					var msglayer = layer.open({
						type: 1,
						title: '邀请面试',
						closeBtn: 1,
						border: [10, 0.3, '#000', true],
						area: ['auto', 'auto'],
						content: $("#tcmsg"),
						cancel:function(){
							window.location.reload();
						}
					});
				
					 
				}else if(status == 3){	// 邀请面试
					
					var msglayer = layer.open({
						type: 1,
						title: '邀请面试',
						closeBtn: 1,
						border: [10, 0.3, '#000', true],
						area: ['auto', 'auto'],
						content: $("#job_box"),
						cancel:function(){
							window.location.reload();
						}
					});
					
				}else{
					
					if(data.login==1){

						showlogin(data.login);
					}else{
						layer.msg('请先申请企业用户',2,8);
			            return false;
					}
				}
			});	
		}	
	});	
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
	$.get(weburl+'/index.php?c=changeusertype',{usertype:2},function(msg){

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
function userswitching(){
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
function check_email(strEmail) {
	 var emailReg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	 if (emailReg.test(strEmail))
	 return true;
	 else
	 return false;
 }
function isjsMobile(obj){
	var reg= /^[1][3456789]\d{9}$/;   
	
    if (obj.length != 11) return false;
    else if (!reg.test(obj)) return false;
    else if (isNaN(obj)) return false;
    else return true;
}
function isjsTell(str) {
//	var result = str.match(/^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/);
	var result = str.match(/^[0-9-]+?$/);
	if (result == null) return false;
    return true;
}
function isPlaceholder(ele){
	if(ele = 'input'){
		var input = document.createElement('input');
	    var isph = 'placeholder' in input;
	    return isph;
	}else if(ele = 'textarea'){
		var textarea = document.createElement('textarea');
		var isph = 'placeholder' in textarea;
	    return isph;
	}
}

/**
 * @desc	首页职位发布
 * @param num	1：正常发布 2：会员套餐已用完 0：会员过期
 * @param integral_job：	购买发布职位所需要金额
 * @param online	1 金额抵扣模式 2 金额模式 3积分模式 4套餐模式
 * @param pro：	积分比例
 * @returns
 */
function addJobIndex(num,integral_job,online,pro){
  	
	 
	var gourl 		= 	weburl + '/member/index.php?c=jobadd';
	var bindurl		= 	weburl + '/member/index.php?c=binding';
	var righturl	= 	weburl + '/member/index.php?c=right';
  	var url			= 	weburl + '/index.php?m=ajax&c=ajax_day_action_check';
	var checkUrl	= 	weburl + '/member/index.php?c=jobadd&act=jobCheck';

	layer.load('执行中，请稍候...',3);
	
	$.post(url, {'type': 'jobnum'}, function(data) {
			
		layer.closeAll();
		
		data = eval('(' + data + ')');
		
		if(data.status == -1) {
			
			layer.msg(data.msg, 2, 8);
			return false;
			
		} else if(data.status == 1) {
			
			$.post(checkUrl, {rand:Math.random()}, function(data){
				
				var data = eval('(' + data + ')');
				
 				if(data.msgList && data.msgList.length > 0 ){
 					
 					layer.msg('完成相关认证才能发布职位！', 2,8, function(){
 						
 						window.location.href = bindurl;
 						window.event.returnValue = false;
 					});
 					
 				}else{

					if(num == 1 || (integral_job == 0 && num == 2)) {
						
						window.location.href = gourl;
						window.event.returnValue = false;
						
					}else{
						layer.msg('套餐不足，请先购买会员！', 2,8, function(){
							window.location.href = righturl;
							window.event.returnValue = false; 
						});
						
					} 
				}
			});
		}
	});
}

function fav_job(id,type){//收藏职位
	$.post(weburl+"/index.php?m=ajax&c=favjobuser",{id:id},function(data){
		var data=eval('('+data+')');
		if(data.state==1){
			if(type==2){ 
				$("#favjobid"+id).html("已收藏");
				var msglayer = layer.open({
					type: 1,
					title: '收藏信息',
					closeBtn: 1,
					border: [10, 0.3, '#000', true],
					area: ['450px', 'auto'],
					content: $("#favjob"),
					cancel:function(){
						window.location.reload();
					}
				});
			}else{
				$(".scjobid"+id).html("已收藏");
				$(".scjobid"+id).attr('class','yun_job_operation_ysc');
				var i = layer.confirm(data.msg+'是否返回个人中心？',
					{btn : ['确定','继续浏览']},
					function(){
						window.location.href =weburl+"/member/index.php?c=favorite";window.event.returnValue = false;return false;
					},
					function(){
						layer.close(i);
						window.location.href=window.location.href;
					}
				);
			}
			
		}else if(data.state==2 || data.state==3){
			layer.msg(data.msg, 2, data.errcode);return false;
		}else if(data.state==0 ||data.state==4){
			if(type==2){
				$("#touch_lo").hide();
				$("#tologoin").show("1000");
			}else{
				layer.msg(data.msg, 2, data.errcode);return false;
			}
		}
	});
}
//加入收藏夹
function addwebfav(url,title){
	var title,url;
	if(document.all){
		window.external.addFavorite(url,title);
	}else if(window.sidebar){
		window.sidebar.addPanel(title,url,"");
	}
}
//设置首页
function setHomepage(url){
   var url;
   if(document.all){
	  document.body.style.behavior='url(#default#homepage)';
	  document.body.setHomePage(url);
   }else if(window.sidebar){
		if(window.netscape){
			 try{
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			 }
			 catch(e){
				layer.alert('您的浏览器未启用[设为首页]功能，开启方法：先在地址栏内输入about:config,然后将项 signed.applets.codebase_principal_support 值该为true即可！');return false; 
			 }
		}
		var prefs=Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
		prefs.setCharPref('browser.startup.homepage',url);
   }
}
function marquee(time,id){
	$(function(){
		var _wrap=$(id);
		var _interval=time;
		var _moving;
		_wrap.hover(function(){
			clearInterval(_moving);
		},function(){
			_moving=setInterval(function(){
			var _field=_wrap.find('li:first');
			var _h=_field.height();
			_field.animate({marginTop:-_h+'px'},800,function(){
			_field.css('marginTop',0).appendTo(_wrap);
			})
		},_interval)
		}).trigger('mouseleave');
	});
}
//弹出框
function forget(){
	var aucode = $("#txt_CheckCode").val();
	var username =  $("#username").val();
	if(username==""){
		$("#msg_error").html("<font color='red'>请填写你注册时的用户名！</font>");
		return false;
	}
	if(aucode==""){
		$("#msg_error").html("<font color='red'>验证码不能为空！</font>");
		return false;
	}
	return true;
}
function unselectall(){
	if(document.getElementById('chkAll').checked){
		document.getElementById('chkAll').checked = document.getElementById('chkAll').checked&0;
	}
}
function CheckAll(form){
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		if (e.Name != 'chkAll'&&e.disabled==false)
		e.checked = form.chkAll.checked;
	}
}

function report_com(){
	$.layer({
		type : 1,
		title :'举报该职位', 
		closeBtn : [0 , true],
		border : [10 , 0.3 , '#000', true],
		area : ['450px','460px'],
		page : {dom :"#jobreport"}
	});
}
/*-------------------------------------------------*/
function check_skill(id){
	$(".pop-ul-ul").hide();
	$(".user_tck_box1").removeClass("tanchu");
	$("#showskill"+id).addClass("tanchu");
	$("#skill"+id).show();
}
function box_delete(id){
	$("#sk"+id).remove();
	$("#td_"+id).remove();
	 $("#zn"+id).removeAttr("checked");
}
function checked_input2(id,name,divid,fid){
	var check_length = $("input[type='checkbox'][name='"+name+"'][checked]").length;
	if(name=="job_classid"){
		if($("#zn"+id).attr("checked")=="checked"){
			if(check_length>=5){
				layer.msg('您最多只能选择五个', 2,8);
				$("#zn"+id).attr("checked",false);
			}else{
				var info = $("#zn"+id).val();
				var info_arr = info.split("+");
				if(id==fid){
					$("."+fid).remove();
				}else{
					$("#td_"+fid).remove();
				}
				$("#"+divid).append("<li id='td_"+id+"' class='show_type"+id+" "+fid+"' ><input id='chk_"+id+"' onclick='box_delete("+id+");' type='checkbox' checked value='"+info+"' name='"+name+"'>"+info_arr[1]+"</li>");
			}
		}else{
			$("#td_"+id).remove();
		}
	}
}
$(document).ready(function () {
    //首页搜索框，搜索类型的选择
    $('body').click(function (evt) {
        if (!$(evt.target).parent().hasClass('yunHeaderSearch_s') && !$(evt.target).hasClass('yunHeaderSearch_s') && evt.target.id != 'search_name') {
            $('.yunHeaderSearch_list_box').hide();
        }
    });
	var jobarr=new Array();
	$("#close_skill").click(function(){
		$("#bg").hide();
		$('#skill_box').hide();
		var skill_val = "";
		var i=0;
		$("input[type='checkbox'][name='job_classid']:checked").each(function(){
		  var info = $(this).val().split("+");
			jobarr[i]=info[0];
			i++;
		  skill_val+="<li id=\"sk"+info[0]+"\" class=\"show_type"+info[0]+"\" onclick=\"box_delete('"+info[0]+"');\"><input type=\"checkbox\" name=\"job_classid[]\" checked=\"\" value="+info[0]+"><span>"+info[1]+"</span></li>";
		  });
		$("#job_classid").html(skill_val);
	})
})
function checkmore(type,div,size,msg){
	if(msg=="展开"){
		var msg="收起";
		$("#"+type+" a:gt("+size+")").show();
		$("#"+div).html("<a class=\"yun_close  icon\" href=\"javascript:;\" onclick=\"checkmore('"+type+"','"+div+"','"+size+"','"+msg+"');\">"+msg+"</a>");
	}else{
		var msg="展开";
		$("#"+type+" a:gt("+size+")").hide();
		$("#"+div).show();
		$("#"+div).html("<a class=\"yun_open  icon\" href=\"javascript:;\" onclick=\"checkmore('"+type+"','"+div+"','"+size+"','"+msg+"');\">"+msg+"</a>");
	}
}
function checkrest(url){window.location.href="index.php?m="+url;}
function Close(id){
	$("#"+id).hide();
	$("#bg").hide();
}
function check_pl(){//企业评论
	if($.trim($("#content").val())==""){
		layer.msg('评论留言内容不能为空！', 2,8);return false;
	}
	var authcode=$("#msg_CheckCode").val();
	if(authcode==''){
		layer.msg('验证码不能为空！', 2, 8);return false;
	} 
}

function layer_del(msg,url){ 
	if(msg==''){
		var i=layer.load('执行中，请稍候...',0);
		$.ajaxSetup({cache:false});
		$.get(url,function(data){
			layer.close(i);
			var data=eval('('+data+')');
			if(data.url=='1'){
				layer.msg(data.msg, Number(data.tm), Number(data.st),function(){location.reload();});return false;
			}else{
				layer.msg(data.msg, Number(data.tm), Number(data.st),function(){location.href=data.url;});return false;
			}
		});
	}else{
		layer.confirm(msg, function(){
			var i=layer.load('执行中，请稍候...',0);
			$.ajaxSetup({cache:false});
			$.get(url,function(data){
				layer.close(i);
				var data=eval('('+data+')');
				if(data.url=='1'){
					layer.msg(data.msg, Number(data.tm), Number(data.st),function(){location.reload();});return false;
				}else{
					layer.msg(data.msg, Number(data.tm), Number(data.st),function(){location.href=data.url;});return false;
				}
			});
		});
	}
}
function top_search(M, name, url, is_module_open, module_dir) {
    if ((is_module_open == '1') && (module_dir != '')) {
        $('#index_search_form #m').attr('name', '');
    } else {
        $('#index_search_form #m').attr('name', 'm');
    }
	if(M=='tiny'||M=='once'){
		$('#search').attr('name','');
	}else if(M=='article'){
		$('#search').attr('name','c');
		$('#search').attr('value','list');
	}else{
		$('#search').attr('name','c');
		$('#search').attr('value','search');
	}
    $('#index_search_form').attr('action', url);
    $('#index_search_form #m').val(M);
	$(".yunHeaderSearch_list_box").hide();
	$('#search_name').html(name)
}
function top_searchs(M,name){
	$("input[name='m']").val(M);
	$(".index_place_position").hide();
	$('#search_name').html(name)
}
function returnmessage(frame_id){ 
	if(frame_id==''||frame_id==undefined){
		frame_id='supportiframe';
	}
	var message = $(window.frames[frame_id].document).find("#layer_msg").val(); 
	if(message != null){
		var url=$(window.frames[frame_id].document).find("#layer_url").val();
		var layer_time=$(window.frames[frame_id].document).find("#layer_time").val();
		var layer_st=$(window.frames[frame_id].document).find("#layer_st").val();
		if(message=='验证码错误！'){$("#vcode_img").trigger("click");$("#vcodeimgs").trigger("click");}
		if(message=='验证码错误！'){$("#vcode_imgs").trigger("click");}
		if(message=='验证码错误！'){$("#vcodeimgs").trigger("click");}
		if(message=='请点击按钮进行验证！'){
			$("#popup-submit").trigger("click");
		}
		layer.closeAll('loading');
		if(url=='1'){
			layer.msg(message, layer_time, Number(layer_st),function(){window.location.reload();window.event.returnValue = false;return false;});
		}else if(url==''){
			layer.msg(message, layer_time, Number(layer_st));
		}else{
			layer.msg(message, layer_time, Number(layer_st),function(){location.replace(url);return false;});
		}
	}
}

function com_msg(){
	noplaceholder('msg_content');
	var msg_content=$.trim($("#msg_content").val());
	if(msg_content==''){
		layer.msg('咨询内容不能为空！', 2,8);return false;
	}
	noplaceholder('msg_CheckCode');
	var authcode=$("#msg_CheckCode").val();
	if(authcode==''){
		layer.msg('验证码不能为空！', 2, 8);return false;
	} 
}

/*简历修改页，城市弹出框 lgl */
function job_class(id,type,grade){
	if(type=='f'){
		var height=$("#dt_job_"+id).offset().top;
		$("#layout_job .dt_job_"+grade).removeClass('cur');
		$("#layout_job .dd_job_"+grade).hide();
		if(grade=='1'){
			var top=parseInt(height)-parseInt(615);
			$("#layout_job .dd_job_2").hide();
			$("#layout_job .dt_job_2").removeClass('cur');
		}else{ 
			var top=34;
		}
		$("#dt_job_"+id).addClass('cur');
		$("#dd_job_"+id).css("top",top);
		$("#dd_job_"+id).fadeIn("slow");
	}else{
		var check_length = $("input[type='checkbox'][name='job_class'][checked]").length;
		if($("#job_"+id).attr("checked")=="checked"){
			if(check_length>=5){
				layer.msg('您最多只能选择五项！', 2,8,function(){$("#job_"+id).attr("checked",false);}); 
			}else{
				var value=$("#job_"+id).val();
				$("#job_choosed").append("<span id='span_job_"+id+"'><input id='ck_job_"+id+"'  value='"+id+"' onclick=\"del_ck('job_"+id+"')\" name='job_class' checked='checked' type='checkbox' target='"+value+"'>"+value+"</span>");
			}
		}else{
			$("#span_job_"+id).remove();
		}
	}
}
function job_city(id,type,grade){
	if(type=='province'){
		var height=$("#dt_"+id).offset().top;
		if(grade=='1'){
			var top=parseInt(height)-parseInt(570);
		}else{
			var top=34;
		}
		$("#layout_inner .dt_"+grade).removeClass('cur');
		$("#dt_"+id).addClass('cur');
		$("#layout_inner .dd_"+grade).hide();
		$("#dd_"+id).css("top",top);
		$("#dd_"+id).show();
	}else{
		var check_length = $("input[type='checkbox'][name='select_city'][checked]").length;
		if($("#"+id).attr("checked")=="checked"){
			if(check_length>=5){
				layer.msg('您最多只能选择五个城市！', 2,8,function(){$("#"+id).attr("checked",false);}); 
			}else{
				var value=$("#"+id).val();
				$("#choosed").append("<span id='span_"+id+"'><input id='ck_"+id+"'  value='"+id+"' onclick=\"del_ck('"+id+"')\" name='select_city' checked='checked' type='checkbox' target='"+value+"'>"+value+"</span>");
			}
		}else{
			$("#span_"+id).remove();
		}
	}
}
function select_prop(name,id,div){
	var chk_value =[];
	var chk_ids =[];
	$('input[name="'+name+'"]:checked').each(function(){
		chk_value.push($(this).attr('target'));
		chk_ids.push($(this).val());
	});
	if(chk_value.length==0){
		layer.msg('请选择职位类别！', 2,8);return false;
	}else{
		$("#"+id+" dt").removeClass("cur");
		$("#"+id+" dd").hide();
		$("#"+id).val(chk_value);
		$("#"+name).val(chk_ids);
		$("#"+id).removeClass("city_cur");
		$("#"+div).hide();
	}
}
function close_prop(div,id){
	$("#"+div).hide();
	$("#"+id).removeClass("city_cur");
}
function del_ck(id){
	$("#span_"+id).remove();
	$("#"+id).removeAttr("checked");
}

/*弹出框结束*/
function atn(id,url,tid){//关注企业
	if(id){
		loadlayer();
		$.post(url,{id:id,tid:tid},function(data){
			layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == 2){
				layer.msg(data.msg, 2,8);
				return false;
			}else{
				layer.msg(data.msg, 2,9, function(){
					window.location.reload();
				});return false;
			} 
		});
	}
}




function jsmsg(id){
	var myuid = $("#myuid").val();
	if(myuid==""){
		layer.msg('你还没有登录！', 2, 8);
	}
	$("#msg"+id).show();
}
function showImgDelay(imgObj,imgSrc,maxErrorNum){   
	if(maxErrorNum>0){ 
        imgObj.onerror=function(){
            showImgDelay(imgObj,imgSrc,maxErrorNum-1);
        };
		
        setTimeout(function(){
            imgObj.src=imgSrc;
        },500);
		maxErrorNum=parseInt(maxErrorNum)-parseInt(1);
    }
}

function add_reason(s){
	if($("#r"+s).hasClass('report_job_ly_cur')){
		$("#r"+s).removeClass('report_job_ly_cur');
	}else{
		$("#r"+s).addClass('report_job_ly_cur');
	}
}

function reportSub(img){
	var authcode=$("#report_authcode").val();
	
	var r_uid=$("#r_uid").val();
	var id=$("#id").val();
	var r_name=$("#r_name").val();
	
		var r1 = $("#r1").html(),
        	r2 = $("#r2").html(),
        	r3 = $("#r3").html(),
        	r4 = $("#r4").html(),
        	r5 = $("#r5").html();
		var reason = "理由：";
		
		if($("#r1").hasClass('report_job_ly_cur')){
			var reason = reason+r1+"，";
		}
		if($("#r2").hasClass('report_job_ly_cur')){
			var reason = reason+r2+"，";
		}
		if($("#r3").hasClass('report_job_ly_cur')){
			var reason = reason+r3+"，";
		}
		if($("#r4").hasClass('report_job_ly_cur')){
			var reason = reason+r4+"，";
		}
		if($("#r5").hasClass('report_job_ly_cur')){
			var reason = reason+r5+"；";
		}
	
	
	if($.trim(reason)=="理由："){
		layer.msg('请选择举报理由！', 2, 8);
		return false;
	}
	
	var r_reason = reason + $("#r_reason").val();
	
	
	var i = loadlayer();
	$.post(weburl+"/job/index.php?c=report",{authcode:authcode,r_reason:r_reason,id:id,r_name:r_name,r_uid:r_uid},function(data){
		layer.close(i);
		if(data==1){
			layer.msg('验证码不正确！', 2, 8,function(){checkCode(img);});
		}else if(data==2){
			layer.msg('您已经举报过该用户！', 2, 8,function(){checkCode(img);});
		}else if(data==3){
			layer.closeAll();
			layer.msg('举报成功！', 2,9);
		}else if(data==4){
			layer.msg('举报失败！', 2, 8,function(){checkCode(img);});
		}
	})
}
//职位详情页，申请记录分页
/*function forrecord(id,page){ 
	$.post(weburl+"/index.php?m=ajax&c=jobrecord",{id:id,page:page},function(data){
		$(".Company_job_record_div").html(data);
	});
} */
$(function(){
	$('body').click(function(evt) {
		if($(evt.target).parents("#listhy").length==0 && evt.target.id != "buttonhy") {
			$('#listhy').hide();
		}
	})
});

//积分兑换表单
function checkform_redeem_show(){
	var num=$("#num").val();
	var stock=$("#stock").val();
	var uid=$("#uid").val();
	var myintegral=$("#myintegral").val();
	var redeemintegral=$("#redeemintegral").val();
	var restriction=$("#restriction").val();
	if(!uid){
		layer.msg('您还没有登录，请先登录！', 2, 8,function(){
			location.href=weburl+"/index.php?m=login";
		});
		return false;
	}
	if(num==0){
		layer.msg('请正确填写兑换数量！', 2, 8);
		return false;
	}
	if(Number(num)>Number(restriction) && restriction!="0"){
		layer.msg('超出限购数量,请正确填写！', 2, 8);
		return false;
	}
	if(Number(num)>Number(stock)){
		layer.msg('超出库存数量,请正确填写！', 2, 8);
		return false;
	}
	var integral=Number(num)*Number(redeemintegral);
	if(Number(myintegral)<Number(integral)){
		layer.msg(pricename+'不足，不能兑换！', 2, 8,function(){
			location.href=weburl+"/member/index.php?c=pay";
		});
 		return false;
	}
	return true;
}
function redeem_dh(){
	var linkman=$("input[name=linkman]").val();
	var linktel=$("input[name=linktel]").val();
	var password=$("input[name=password]").val();
	var cionly	=	$.trim($("#cionly").val());
	if(cionly=='1'){
		var citys	=$("#provinceid").val();
	}else{
		var citys	=$("#cityid").val();
	}
	
	var address	=$("input[name=address]").val();
	if(!linkman || !linktel){
		layer.msg('联系人或联系电话不能为空！', 2, 8);
		return false;
	}
	var reg_linktel= (/^[1][3456789]\d{9}$|^([0-9]{3,4}\-)?[0-9]{7,8}$/);
	if(linktel){
		if(!reg_linktel.test(linktel)){
			layer.msg('联系电话格式不正确请正确填写！', 2, 8);return false; 
		} 
	}
	if(!cityid || !address){
		layer.msg('请填写收货地址信息！', 2, 8);
		return false;
	}
	if(!password){
		layer.msg('请输入密码！', 2, 8);
		return false;
	}
	return true;
}
function noplaceholder(id){
	var value=$("#"+id).val();
	var placeholder=$("#"+id).attr('placeholder');
	if(value==placeholder){
		$("#"+id).val('');
	}
}
function tianyancha(url,name){
	if(url && name){
		$.post(url,{name:name},function(data){
			if(data){
				var business = eval('('+data+')');
				$('#creditCode').html(business.creditCode);
				$('#estiblishTime').html(business.estiblishTime);
				$('#orgNumber').html(business.orgNumber);
				$('#Time').html(business.fromTime+'至'+business.toTime);
				$('#companyOrgType').html(business.companyOrgType);
				$('#regInstitute').html(business.regInstitute);
				$('#regStatus').html(business.regStatus);
				$('#regLocation').html(business.regLocation);
				$('#regCapital').html(business.regCapital);
				$('#businessScope').html(business.businessScope);
				$('#tianyancha').attr('href','https://www.tianyancha.com/');
				$('#companybusiness').show();
				$('#businessInfo').show();
			}else{
				$('#businessInfo').hide();
				
				$('#companybusiness').hide();

			}
		});
	}
}
function redeem_show(id){
	$(".redeem_city_list").hide();
	$("#"+id).show();
}
function redeem_city(id,type,name,typeid){
	$('#'+type).val(name);
	$("#"+type+'_name').val(name);
	
	if(type=='province'){
		$("#city_name").val('请选择');
		$("#three_city_name").val('请选择');
		$("#city").val('');
		$("#three_city").val('');
	}
	if(type=='city'){
		$("#three_city_name").val('请选择');
		$("#three_city").val('');
	}
	$.post(weburl+'/index.php?m=ajax&c=redeem_city',{type:type,id:id,typeid:typeid},function(data){
		$('#redeem_'+type).hide();
		if(data){
			$('#redeem_'+typeid).html(data);
		}
	});
}
function redeems(id,type,name){
	$('#'+type).val(name);
	$("#"+type+'_name').val(name);
	$('#redeem_'+type).hide();
}