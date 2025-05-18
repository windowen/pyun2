function CheckPost(){
    var name=$.trim($("#name").val());
    var sex=$.trim($("#sex").val());
	var telphone=$.trim($("#telphone").val());
	var email=$.trim($("#email").val());
	var birthday=$.trim($("#birthday").val());
	var edu=$.trim($("#educid").val()); 
	var exp=$.trim($("#expid").val()); 
	var living=$.trim($("#living").val());
	var telhome=$.trim($("#telhome").val());
	if(name==''){layer.msg('请填写姓名', 2, 8);return false;}
	if(sex==''){layer.msg('请选择性别', 2, 8);return false;}
	if(birthday==''){layer.msg("请选择出生年月", 2, 8);return false;}
	ifemail = check_email(email); 
	ifmoblie = isjsMobile(telphone);
	if(telphone==''){
		layer.msg('请填写手机号', 2, 8);return false;
	}else{
		if(ifmoblie==false){layer.msg("手机格式不正确", 2, 8);return false;}
	}
	if(email!=""&&ifemail==false){layer.msg("电子邮件格式不正确", 2, 8);return false;}
	if(living==''){layer.msg('请填写现居住地', 2, 8);return false;}
	if(edu==''){layer.msg('请选择学历', 2, 8);return false;} 
	if(exp==''){layer.msg('请选择工作经验', 2, 8);return false;}  
	if(telhome&&isjsTell(telhome)==false){
		layer.msg('请填写正确的座机号', 2, 8);return false;
	}
	layer.load('执行中，请稍候...',0);
}
function ScrollTo(id){
	$("#"+id).ScrollTo(700);
}
function movelook(type){
	$("#"+type+"_upbox").addClass("yun_resume_handle_bg");
	$("#compile_"+type).show();
}
function outlook(type){
	$("#"+type+"_upbox").removeClass("yun_resume_handle_bg");
	$("#compile_"+type).hide();
}
function toDate(str){
    var sd=str.split("-");
    return new Date(sd[0],sd[1],sd[2]);
}
function numresume(numresume,type){
	if(numresume<user_sqintegrity){
		 var showhtml="您现在的简历完整度太低，还不能够使用此简历应聘!"
	}else{
		var showhtml="您的简历已符合要求！"
	}
	$("#_ctl0_UserManage_LeftTree1_msnInfo").html(showhtml);
	$("#numresume").html(numresume+"%");
	//$(".resume_"+type).show();
	$(".play").attr("style","width:"+numresume+"%");
}
function changeRightIntegrityState(id,state){
	if(state=="add"){
		$("#"+id).find(".dom_m_right_state").removeClass("state");
		$("#"+id).find(".dom_m_right_state").addClass("state_done");
		$("."+id).removeClass("state");
		$("."+id).addClass("state_done");		
	}else{
		$("#"+id).find(".dom_m_right_state").removeClass("state_done");
		$("#"+id).find(".dom_m_right_state").addClass("state");	
		$("."+id).removeClass("state_done");
		$("."+id).addClass("state");		
	}
}
function saveexpect(){
	var name = $.trim($("#nameid").val());  
	var hy = $.trim($("#hyid").val());  
	var job_classid = $.trim($("#job_class").val());
	var city_classid = $.trim($("#city_class").val());
//	var provinceid = $.trim($("#provinceid").val());
//	var cityid = $.trim($("#cityid").val());
//	var three_cityid = $.trim($("#three_cityid").val());
	var minsalary = $.trim($("#minsalary").val()); 
	var maxsalary = $.trim($("#maxsalary").val()); 
	var type = $.trim($("#typeid").val()); 
	var report = $.trim($("#reportid").val());
	var eid = $.trim($("#eid").val());
	var jobstatus = $.trim($("#statusid").val());
	if(name==""){layer.msg('请填写期望岗位！', 2, 8);return false;}
    if(hy==""){layer.msg('请选择从事行业！', 2, 8);return false;}
	if(city_classid==''){layer.msg('请选择工作地点！', 2, 8);return false;}
	if(job_classid==""){layer.msg('请选择从事职位！', 2, 8);return false;}
	
	if(minsalary=="" || minsalary=="最低薪资"||parseInt(minsalary)<=0){layer.msg('请填写期望薪资！', 2, 8);return false;}
	if(maxsalary && parseInt(maxsalary)!=0 && parseInt(maxsalary) < parseInt(minsalary)){
		layer.msg('最高薪资必须大于最低薪资！', 2, 8);return false;
	}
	
	if(type==""){layer.msg('请选择工作性质！', 2, 8);return false;}
	if (report == "") { layer.msg('请选择到岗时间！', 2, 8); return false; }
	if (jobstatus == "") { layer.msg('请选择求职状态！', 2, 8); return false; }
	loadlayer();
	$.post("index.php?c=expect&act=saveexpect", {name:name, hy:hy, job_classid: job_classid, city_classid: city_classid, minsalary: minsalary, maxsalary: maxsalary, jobstatus: jobstatus, type: type, report: report, eid: eid, submit: "1", dom_sort: getDomSort() }, function (data) {
		layer.closeAll('loading');
		layer.close($("#layindex").val());
		if(data==0){
			layer.msg('操作失败！', 2, 8);
		}else if(data==-1){
			layer.msg('请先完善您的求职意向信息！', 2, 8);
		}else{
			data=eval('('+data+')');
			$("#saveexpect").hide();
			$("#eid").val(data.id);
			var html='<li>期望岗位：'+data.name+'</li><li>期望工作地点：'+data.city_classname+'</li><li>期望从事行业：'+data.hy_n+'</li><li>期望薪资：'+data.salary+'</li><li>到岗时间：'+data.report_n+'</li><li>期望工作性质：'+data.type_n+'</li><li>求职状态：'+data.jobstatus_n+'</li><li style="width:100%;">工作职能：'+data.job_classname+'</li>';
			console.log(html);
			$("#expect").html(html);
			layer.msg('操作成功！', 2,9,function(){ScrollTo("expect_botton");$(".resume_expect").addClass('state_done');}); 
		}
	});
}
function totoday(){
	if($("#totoday").attr("checked")=='checked'){
		$('#work_emonthid').val('');
		$('#work_emonth').val('');
		$('#work_eyearid').val('');
		$('#work_eyear').val('');
		$('#yearwork').hide();
		$('#monthwork').hide();
	}else{
		$('#yearwork').show();
		$('#monthwork').show();
	}
}
function getDomSort(){
	var domsort="";
	var elements=$("#dom0 .dom_m");
	for(var i=0;i<elements.length;i++){
		domsort=domsort+","+$(elements[i]).attr("id");
	}
	return domsort=domsort.substring(1,domsort.length);
}
function showMore(type,width,height,name){
	var path = $("#upload_path").val();
	$("#add"+type+" li").show();
	$(".newresumebox").removeClass("newresumebox");  //打开弹出框时移除新加内容的class
	if(type=='expect'){
		$("#cityshowth").hide();
	}
	var layerindex = $.layer({
		type : 1,
		title : name,
		shift : 'top',
		offset : [($(window).height() - height)/2 + 'px', ''],
		closeBtn : [0 , true],
		border : [10 , 0.3 , '#000', true],
		area : [width+'px',height+'px'],
		page : {dom :"#save"+type},
		close: function(index){
			$(".newresumebox").remove();  //关闭弹出框移除未添加内容的新弹出的class
			$("#save"+type).hide();
			var num=$(".show"+type+"num");
			if(num.length==1){
				$(".hidepic").hide();
			}
			//if(type=='skill'){window.location.reload();ScrollTo(type+"_upbox");}
		} 
	});
	$("#layindex").val(layerindex);
	//form.render();
	if(type=='skill'){
		var uploadEle=document.getElementsByClassName ('resumeupload');
		for (var i = 0; i < uploadEle.length; i++) {
			if(uploadEle[i]){
				var id=$(uploadEle[i]).attr('id');
		
				layui.use('upload', function(){
					var upload = layui.upload;
					var layaccept = 'images', layexts = 'jpg|png|gif|bmp|jpeg';
					upload.render({
						elem: '#'+id 
						//,url: weburl+'/index.php?m=ajax&c=layui_upload'
						,field: 'skillfile[]'
						,auto: false
						,bindAction: '#test9'   //触发上传的对象，暂未用到
						,accept: layaccept
						,exts: layexts
						,choose: function(obj){
							if(this.imgid){
								//预读本地文件示例，不支持ie8/9
								var imgid = null,
									parentid = null;
								if(this.imgid){
									imgid = this.imgid;
								}
								if(this.parentid){
									parentid = this.parentid;
								}
								obj.preview(function(index, file, result){
									if (parentid && $('#'+parentid).length>0){
										$('#'+parentid).removeClass('none');
										$('#'+imgid).attr('src', result); 
									}else if(imgid && $('#'+imgid).length>0){
										$('#'+imgid).removeClass('none');
										$('#'+imgid).attr('src', result); //图片链接（base64）
									}
									
								});
							}
						}
					});
				});
			}
		}
		$("input[name='skillfile[]']").removeAttr("id");
	}
}

function hideMore(type){
	$(".newresumebox").remove();//关闭弹出框移除未添加内容的新弹出的class
	$("#save"+type).hide();
	var num=$(".show"+type+"num");
	if(num.length==1){
		$(".hidepic").hide();
	}
	//if(type=='skill'){window.location.reload();ScrollTo(type+"_upbox");}
	layer.close($("#layindex").val());
}
function changeIntegrityState(id,state){
	if(state=="add"){
		$("#"+id).find(".integrity_degree").addClass("state_done");		
	}else{
		$("#"+id).find(".integrity_degree").removeClass("state_done");	
	}
}
function checkModel(id){
	if(id==1){
		$("#module").addClass("resume_right_box_tit_cur");
		$("#template").removeClass("resume_right_box_tit_cur");
		$("#resume_module").show();
		$("#resume_template").hide();
	}else{
		$("#module").removeClass("resume_right_box_tit_cur");
		$("#template").addClass("resume_right_box_tit_cur");
		$("#resume_module").hide();
		$("#resume_template").show();
	}
}
function untiltoday(id,edate,num,toid,edid){
	if($("#"+id).attr("checked")=='checked'){
		var date = new Date();
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var time = y+"-"+m;
		$("#"+edate).val(time);
		$("#"+edate).attr('disabled','disabled');
		$("#"+edate+"un").removeAttr('disabled');
		$("#"+toid).val('2');
	}else{
		$("#"+edate).removeAttr('disabled');
		$("#"+edate+"un").attr('disabled','disabled');
		$("#"+edate).val('');
		$("#"+toid).val('1');
		$("#"+edate).show();
	}
	$("#"+num).hide();
	$("#"+edid).hide();
}
function deleteupbox(delid,boxid,showid,table){
	var id=$("#add"+table).find("."+delid).val();
	var eid=$("#eid").val();
	$("#"+delid).remove();
	$("#"+boxid).removeClass(showid);	
	var num=$("."+showid);

	/**
	if(num.length==1){
		$("."+showid).hide();
		$(".newresumebox").removeClass("newresumebox");  //只剩下最后一个新添加移除新加内容的class
	}
	*/

	if(id&&eid&&table){
		loadlayer();
		$.post("index.php?c=resume&act=publicdel",{id:id,eid:eid,table:table},function(data){
			layer.closeAll('loading');
			if(data!='0'){
				
				data=eval('('+data+')');
				if(parseInt(data.integrity)<parseInt(user_sqintegrity)){
					 var showhtml="您现在的简历完整度太低，还不能够使用此简历应聘!"
				}else{
					var showhtml="您的简历已符合要求！"
				}
				//更新右侧完整度
				if(data.num<1){
					changeIntegrityState("m_right_"+table,"remove");
				}
				$("#"+table+""+id).remove();
				$("#_ctl0_UserManage_LeftTree1_msnInfo").html(showhtml);
				$("#numresume").html(data.integrity.integrity+"%");
				$(".play").attr("style","width:"+data.integrity+"%");
				
				if(data.num=="0"){
					$(".resume_"+table).removeClass('state_done');
					$("#"+table+"_empty").show();             //没有内容显示提示
				} 			
			}else{
				layer.msg('操作失败！', 2, 9,function(){location.reload();});
			}
		});
	}
}
function cancelBubble(e) {
    var evt = e ? e : window.event;
    if(evt.stopPropagation) { //W3C 
        evt.stopPropagation();
    } else { //IE      
        evt.cancelBubble = true;
    }
}

function deleteTeam(id,eid,table){
	
	cancelBubble();
	
	layer.confirm('确定要删除该项？', function(){
	
		loadlayer();
		$.post("index.php?c=resume&act=publicdel",{id:id,eid:eid,table:table},function(data){
			layer.closeAll();
			if(data!='0'){
				
				data=eval('('+data+')');

				if(parseInt(data.integrity)<parseInt(user_sqintegrity)){
					var showhtml="您现在的简历完整度太低，还不能够使用此简历应聘!"
				}else{
					var showhtml="您的简历已符合要求！"
				}
				//更新右侧完整度
				if(data.num<1){
					changeIntegrityState("m_right_"+table,"remove");
				}
				$("#"+table+""+id).remove();
				$("#_ctl0_UserManage_LeftTree1_msnInfo").html(showhtml);
				$("#numresume").html(data.integrity.integrity+"%");
				$(".play").attr("style","width:"+data.integrity+"%");
				
				if(data.num=="0"){
					$(".resume_"+table).removeClass('state_done');
					$("#"+table+"_empty").show();             //没有内容显示提示
				} 			
			}else{
				layer.msg('操作失败！', 2, 9,function(){location.reload();});
			}
		});
	
	});
	
	
	
	 
}

function addWork(){
	$("#addwork").append(function(){
		$(".lastupbox").removeClass("lastupbox");
		var randnum='w'+parseInt(Math.random()*1000); 
		var html="<li class='yun_resume_popup newresumebox lastupbox' id='"+randnum+"'><i class='yun_resume_popup_del showworknum' id='iw"+randnum+"' onclick=\"deleteupbox('"+randnum+"','iw"+randnum+"','showworknum','work')\">删除</i><input type='hidden' name='id[]' value='' class='"+randnum+"'/><input type='hidden' name='nameid[]' value='n"+randnum+"'><input type='hidden' name='titleid[]' value='t"+randnum+"'><input type='hidden' name='sdateid[]' value='s"+randnum+"'><input type='hidden' name='edateid[]' value='ed"+randnum+"'><input type='hidden' name='timeid[]' value='iw"+randnum+"'><input type='hidden' class='usedwork' name='usedid[]' value=''><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>公司名称：</span><div class='yun_resume_popup_qyname'><input type='text' name='name[]' value=''placeholder='必填' onfocus=\"hidemsg('mworkn"+randnum+"')\" onblur=\"hidemsg('mworkn"+randnum+"')\" class='yun_resume_popup_text work_name'><i class='yun_resume_popup_qyname_tip' id='mworkn"+randnum+"' style='display:none'>请填写公司名称</i></div></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>担任职位：</span><div class='yun_resume_popup_qyname'><input type='text' name='title[]' value='' class='yun_resume_popup_text work_title' placeholder='必填' onfocus=\"hidemsg('mworkt"+randnum+"')\" onblur=\"hidemsg('mworkt"+randnum+"')\"><i class='yun_resume_popup_qytitle_tip' id='mworkt"+randnum+"' style='display:none'>请填写担任职位</i></div></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>工作时间：</span><div class='yun_resume_popup_list_box'><input type='text' id='work_sdate"+randnum+"' name='sdate[]' value='' placeholder='选择开始日期'onfocus=\"hidemsg('mworkiw"+randnum+"','mworks"+randnum+"')\" onblur=\"hidemsg('mworkiw"+randnum+"','mworks"+randnum+"')\" class='yun_resume_popup_text yun_resume_popup_textw90 work_sdate'><script>layui.use(['laydate'],function(){var laydate = layui.laydate;monthclick(laydate,'#work_sdate"+randnum+"');monthclick(laydate,'#work_edate"+randnum+"');});<\/script><i class='yun_resume_popup_list_box_tip' id='mworks"+randnum+"' style='display:none'>请选择开始日期</i></div><span class='yun_resume_popup_time'>-</span><div class='yun_resume_popup_list_box'><input type='text' id='work_edate"+randnum+"' name='edate[]' value=''placeholder='选择结束日期' onfocus=\"hidemsg('mworkiw"+randnum+"','mworked"+randnum+"')\" onblur=\"hidemsg('mworkiw"+randnum+"','mworked"+randnum+"')\" class='yun_resume_popup_text yun_resume_popup_textw90 work_edate'><input id=\'work_edate"+randnum+"un\' type=\'hidden\' name=\'edate[]\' value=\'至今\' disabled=\'disabled\'/><i class='yun_resume_popup_list_box_tip' id='mworkiw"+randnum+"' style='display:none'>请确认日期先后顺序</i><i class='yun_resume_popup_list_box_tip' id='mworked"+randnum+"' style='display:none'>请选择结束日期</i></div><input type='hidden' id='to"+randnum+"' name='totoday[]' value='1'><input class='yun_resume_popup_checkbox' type='checkbox' value='1' onclick=\"untiltoday('totoday"+randnum+"','work_edate"+randnum+"','mworkiw"+randnum+"','to"+randnum+"','mworked"+randnum+"')\" id='totoday"+randnum+"'><span class='yun_resume_popup_checkbox_s'><label for='totoday'>至今</label></span> </div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'></i>工作内容：</span><textarea rows='5' cols='50' name='content[]' id='work_content' class='infor_textarea work_content'placeholder='请描述你在职期间的职责范围、工作内容和工作业绩，为保护个人隐私，请不要填写手机号、QQ、微信等联系方式' ></textarea></div></li>";
        return html;
    });
	$(".showworknum").show();
	var div = document.getElementById('work_div');
	$("#work_div").animate({scrollTop:div.scrollHeight},1000);

}
 
function addTraining(){
	$("#addtraining").append(function(){
		$(".lastupbox").removeClass("lastupbox");
		var randnum='t'+parseInt(Math.random()*1000); 
		var html="<li class='yun_resume_popup newresumebox lastupbox' id='"+randnum+"'><i class='yun_resume_popup_del showtrainingnum'  id='it"+randnum+"' onclick=\"deleteupbox('"+randnum+"','it"+randnum+"','showtrainingnum','training')\">删除</i><input type='hidden' name='id[]' class='"+randnum+"' value=''/><input type='hidden' name='nameid[]' value='n"+randnum+"'><input type='hidden' name='sdateid[]' value='s"+randnum+"'><input type='hidden' name='edateid[]' value='ed"+randnum+"'><input type='hidden' name='timeid[]' value='it"+randnum+"'><input type='hidden' class='usedtraining' name='usedid[]' value=''><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>培训中心：</span><div class='yun_resume_popup_qyname'><input type='text' name='name[]' value=''placeholder='必填'  onfocus=\"hidemsg('mtrainingn"+randnum+"')\" onblur=\"hidemsg('mtrainingn"+randnum+"')\" class='yun_resume_popup_text'><i class='yun_resume_popup_qyname_tip' id='mtrainingn"+randnum+"' style='display:none'>请填写培训中心名称</i></div></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'>培训方向：</span><input type='text' name='title[]' value='' class='yun_resume_popup_text'></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>培训时间：</span><div class='yun_resume_popup_list_box'><input type='text' id='training_sdate"+randnum+"' name='sdate[]' value=''placeholder='选择开始时间' onfocus=\"hidemsg('mtrainingit"+randnum+"','mtrainings"+randnum+"')\" onblur=\"hidemsg('mtrainingit"+randnum+"','mtrainings"+randnum+"')\"class='yun_resume_popup_text yun_resume_popup_textw90'><script>layui.use(['laydate'], function(){var laydate = layui.laydate;monthclick(laydate,'#training_sdate"+randnum+"');monthclick(laydate,'#training_edate"+randnum+"');});<\/script><i class='yun_resume_popup_list_box_tip' id='mtrainings"+randnum+"' style='display:none'>请选择开始日期</i></div><span class='yun_resume_popup_time'>至</span><div class='yun_resume_popup_list_box'><input type='text' id='training_edate"+randnum+"' name='edate[]' value=''placeholder='选择结束时间' onfocus=\"hidemsg('mtrainingit"+randnum+"','mtraininged"+randnum+"')\" onblur=\"hidemsg('mtrainingit"+randnum+"','mtraininged"+randnum+"')\" class='yun_resume_popup_text yun_resume_popup_textw90'><i class='yun_resume_popup_list_box_tip' id='mtrainingit"+randnum+"' style='display:none'>请确认日期先后顺序</i><i class='yun_resume_popup_list_box_tip' id='mtraininged"+randnum+"' style='display:none'>请选择结束日期</i></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'>培训内容：</span><textarea rows='5' cols='50' name='content[]' id='training_content' class='infor_textarea '  placeholder='为保护个人隐私，请不要填写手机号、QQ、微信等联系方式' ></textarea></div></li>";
        return html;
    });
	$(".showtrainingnum").show();
	var div = document.getElementById('training_div');
	$("#training_div").animate({scrollTop:div.scrollHeight},1000);
}
function addProject(){
	$("#addproject").append(function(){
		$(".lastupbox").removeClass("lastupbox");
		var randnum='p'+parseInt(Math.random()*1000); 
		var html="<li class='yun_resume_popup newresumebox lastupbox' id='"+randnum+"'><i class='yun_resume_popup_del showprojectnum'  id='ip"+randnum+"' onclick=\"deleteupbox('"+randnum+"','ip"+randnum+"','showprojectnum','project')\">删除</i><input type='hidden' name='id[]' class='"+randnum+"' value=''/><input type='hidden' name='nameid[]' value='n"+randnum+"'><input type='hidden' name='sdateid[]' value='s"+randnum+"'><input type='hidden' name='edateid[]' value='ed"+randnum+"'><input type='hidden' name='timeid[]' value='ip"+randnum+"'><input type='hidden' class='usedproject' name='usedid[]' value=''><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>项目名称：</span><div class='yun_resume_popup_qyname'><input type='text' name='name[]' value=''placeholder='必填'  onfocus=\"hidemsg('mprojectn"+randnum+"')\" onblur=\"hidemsg('mprojectn"+randnum+"')\" class='yun_resume_popup_text'><i class='yun_resume_popup_qyname_tip' id='mprojectn"+randnum+"' style='display:none'>请填写培项目名称</i></div></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'>担任职位：</span><input type='text' name='title[]' value='' class='yun_resume_popup_text'></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>项目时间：</span>            <div class='yun_resume_popup_list_box'><input type='text' id='project_sdate"+randnum+"' name='sdate[]' value=''placeholder='选择开始时间' onfocus=\"hidemsg('mprojectip"+randnum+"','mprojects"+randnum+"')\" onblur=\"hidemsg('mprojectip"+randnum+"','mprojects"+randnum+"')\" class='yun_resume_popup_text yun_resume_popup_textw90'><script>layui.use(['laydate'], function(){var laydate = layui.laydate;monthclick(laydate,'#project_sdate"+randnum+"');monthclick(laydate,'#project_edate"+randnum+"');});<\/script><i class='yun_resume_popup_list_box_tip' id='mprojects"+randnum+"' style='display:none'>请选择开始日期</i></div><span class='yun_resume_popup_time'>至</span><div class='yun_resume_popup_list_box'><input type='text' id='project_edate"+randnum+"' name='edate[]' value='' placeholder='选择结束时间'onfocus=\"hidemsg('mprojectip"+randnum+"','mprojected"+randnum+"')\" onblur=\"hidemsg('mprojectip"+randnum+"','mprojected"+randnum+"')\" class='yun_resume_popup_text yun_resume_popup_textw90'><i class='yun_resume_popup_list_box_tip' id='mprojectip"+randnum+"' style='display:none'>请确认日期先后顺序</i><i class='yun_resume_popup_list_box_tip' id='mprojected"+randnum+"' style='display:none'>请选择结束日期</i></div></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'>项目内容：</span><textarea rows='5' cols='50' name='content[]' id='project_content' class='infor_textarea 'placeholder='你在项目中承担了哪些工作？创造了什么业绩？为保护个人隐私，请不要填写手机号、QQ、微信等联系方式'></textarea></div></li>";
        return html;
    });
	$(".showprojectnum").show();
	var div = document.getElementById('project_div');
	$("#project_div").animate({scrollTop:div.scrollHeight},1000);
}
function addOther(){
	$("#addother").append(function(){
		$(".lastupbox").removeClass("lastupbox");
		var randnum='o'+parseInt(Math.random()*1000); 
		var html="<li class='yun_resume_popup newresumebox lastupbox' id='"+randnum+"'><i class='yun_resume_popup_del showothernum'  id='io"+randnum+"' onclick=\"deleteupbox('"+randnum+"','io"+randnum+"','showothernum','other')\">删除</i><input type='hidden' name='id[]' class='"+randnum+"' value=''/><input type='hidden' name='nameid[]' value='n"+randnum+"'><input type='hidden' name='contentid[]' value='noh1"+randnum+"'><input type='hidden' name='timeid[]' value='io"+randnum+"'><input type='hidden' class='usedother' name='usedid[]' value=''><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>标题：</span><div class='yun_resume_popup_qyname'><input type='text' name='name[]' id='skill_title' value=''placeholder='必填'  onfocus=\"hidemsg('mothern"+randnum+"')\" onblur=\"hidemsg('mothern"+randnum+"')\" class='yun_resume_popup_text'><i class='yun_resume_popup_qyname_tip' id='mothern"+randnum+"' style='display:none'>请填写标题</i></div></div><div class='yun_resume_popup_list'><span class='yun_resume_popup_name'><i class='yun_resume_popup_bt'>*</i>内容：</span><textarea rows='5' cols='50' name='content[]' id='skill_content' onfocus=\"hidemsg('mothernoh1"+randnum+"')\" onblur=\"hidemsg('mothernoh1"+randnum+"')\" class='infor_textarea 'placeholder='为保护个人隐私，请不要填写手机号、QQ、微信等联系方式'></textarea><i class='yun_resume_popup_qyname_tip2' id='mothernoh1"+randnum+"' style='display:none'>请填写内容</i></div></li>";
        return html;
    });
	$(".showothernum").show();
	var div = document.getElementById('other_div');
	$("#other_div").animate({scrollTop:div.scrollHeight},1000);
}

function hidemsg(eid,sid){
	$("#"+eid).hide();
	$("#"+sid).hide();
}
function resumeFanhui(frame_id){ 
	if(frame_id==''||frame_id==undefined){
		frame_id='supportiframe';
	}
	var msg = $(window.frames[frame_id].document).find("#layer_msg").val(); 
	if(msg != null){
		var url=$(window.frames[frame_id].document).find("#layer_url").val();
		var layer_time=$(window.frames[frame_id].document).find("#layer_time").val();
		var layer_st=$(window.frames[frame_id].document).find("#layer_st").val();
		layer.msg(msg, layer_time, Number(layer_st),function(){window.location.href = url;window.event.returnValue = false;return false;});
	}
	var wrong = $(window.frames[frame_id].document).find("#wrong").val(); 
	var timenum = $(window.frames[frame_id].document).find("#timenum").val(); //显示时间顺序出错的
	if(timenum !=null && wrong !=null){
		var tnums=timenum.split('-');
		for(var i=0;i<tnums.length;i++){
			$("#m"+wrong+tnums[i]).show();
		}
	}
	var namenum = $(window.frames[frame_id].document).find("#namenum").val(); //显示name为空的
	if(namenum !=null && wrong !=null){
		var namenums=namenum.split('-');
		for(var i=0;i<namenums.length;i++){
			$("#m"+wrong+namenums[i]).show();
		}
	}
	var sdatenum = $(window.frames[frame_id].document).find("#sdatenum").val(); //显示sdate为空的
	if(sdatenum !=null && wrong !=null){
		var sdatenums=sdatenum.split('-');
		for(var i=0;i<sdatenums.length;i++){
			$("#m"+wrong+sdatenums[i]).show();
		}
	}
	var edatenum = $(window.frames[frame_id].document).find("#edatenum").val(); //显示edate为空的
	if(edatenum !=null && wrong !=null){
		var edatenums=edatenum.split('-');
		for(var i=0;i<edatenums.length;i++){
			$("#m"+wrong+edatenums[i]).show();
		}
	}
	var ingnum = $(window.frames[frame_id].document).find("#ingnum").val(); //显示ing为空的
	if(ingnum !=null && wrong !=null){
		var ingnums=ingnum.split('-');
		for(var i=0;i<ingnums.length;i++){
			$("#m"+wrong+ingnums[i]).show();
		}
	}
	var titlenum = $(window.frames[frame_id].document).find("#titlenum").val(); //显示工作经历title为空的
	if(titlenum !=null && wrong !=null){
		var titlenums=titlenum.split('-');
		for(var i=0;i<titlenums.length;i++){
			$("#m"+wrong+titlenums[i]).show();
		}
	}
	var edunum = $(window.frames[frame_id].document).find("#edunum").val(); //显示教育经历学历为空的
	if(edunum !=null && wrong !=null){
		var edunums=edunum.split('-');
		for(var i=0;i<edunums.length;i++){
			$("#m"+wrong+edunums[i]).show();
		}
	}
	
	var contentnum = $(window.frames[frame_id].document).find("#contentnum").val(); //显示other_content为空的
	if(contentnum !=null && wrong !=null){
		var contentnums=contentnum.split('-');
		for(var i=0;i<contentnums.length;i++){
			$("#m"+wrong+contentnums[i]).show();
		}
	}
	var message = $(window.frames[frame_id].document).find("#resumeAll").val();
	if(message != null){
		layer.msg('操作成功！', 2,9,function(){window.location.reload();})
    }
}
function changeModel(type,name,height){
	$.layer({
		type: 2,
		shadeClose: true,
		title: name,
		closeBtn: [0, true],
		shade: [0.8, '#000'],
		border: [0],
		offset: ['20px',''],
		area: ['1000px', ($(window).height() - 50) +'px'],
		iframe: {src: type}
    }); 
}
$(function(){
		//光标悬停时，显示知名企业关注信息
	$('.yun_resume_info').delegate('.user_item','mouseover',function(){
		$(this).find('.photochange').show();
	});
	//光标离开时，隐藏知名企业关注信息
	$('.yun_resume_info').delegate('.user_item','mouseout',function(){
		$(this).find('.photochange').hide();
	});
	
	$(".changetag").live('click',function(){
		
		var tag=$(this).attr('tag-class');
		if(tag=='1'){
			$(this).addClass('resume_pop_bq_cur');
			$(this).attr('tag-class','2');
		}else{
			$(this).removeClass('resume_pop_bq_cur');
			$(this).attr('tag-class','1');
		}
		var tag_value;
		var tagi = 0;
		$(".resume_pop_bq_cur").each(function(){
			if($(this).attr('tag-class')=='2'){
				var info =$(this).attr("data-tag");
		        tag_value+=","+info;
				tagi++;

			}
		});
		if(tagi>5){
			layer.msg('最多只能选择五项！', 2,8);
			if(tag=='1'){
				$(this).removeClass('resume_pop_bq_cur');
			}
			return false;
		}
		if(tag_value){ 
		    tag_value = tag_value.replace("undefined,","");
		    $("#tag").val(tag_value); 
	    }else{
			$("#tag").val(''); 
		}
	});

	//添加自定义个人标签
	$('.checkboxAddBton').click(function(){

		var ntag = $('#addfuli').val();
		//判断当前已选标签数量 限定为5个
		var tagid = $('#tag').val();
		if(tagid && tagid.split(',').length>=5){

			layer.msg('最多只能选择五项！', 2,8);
		}else{
			var error=0;
			if(ntag.length>=2 && ntag.length<=8){
				//判断信息是否已经存在 
				$('.changetag').each(function(){
					var otag = $(this).attr('data-tag');
					if(ntag == otag){
						layer.msg('相同标签已存在，请选择或重新填写！', 2,8);
						error = 1;
					}
				});
				if(error==0){
					$('#newtag').append('<li class="changetag  resume_pop_bq_cur" data-tag="'+ntag+'" tag-class="2"><em>'+ntag+'</em></li>');
					
					var tag_value;
					$(".resume_pop_bq_cur").each(function(){
						if($(this).attr('tag-class')=='2'){
							var info =$(this).attr("data-tag");
							tag_value+=","+info;
						}
					});
					tag_value = tag_value.replace("undefined,","");
					$("#tag").val(tag_value); 
				}
				$('#addfuli').val('');
				
			}else{
				layer.msg('请输入2-8个标签字符！', 2,8);
			}
		}
	});
});
function checkdes(){
	var description=$.trim($("#check_des").val());
 	if(description==''){
		$("#des_show").show();
		return false;
	} 
}
function opendiv(){
	var obj=document.getElementById('divopen');
	if(obj.style.display=='none'){
		$('#divopen').show();
		var intro=document.getElementById('introduce');
		if(intro==null){
			$.post(weburl+"/member/index.php?c=expect&act=getIntroduceInfo",{rand:Math.random()},function(data){
				$('#divopen').html(data);
			});
		}
	}else{
		$('#divopen').hide();
	}
}
function nextintroduce(){
	var intro=$("#introduce").val();
	$.post(weburl+"/member/index.php?c=expect&act=getIntroduceInfo",{introduceid:intro},function(data){
		$('#divopen').html();
		$('#divopen').html(data);
	});
		
}