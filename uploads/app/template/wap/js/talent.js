function ckresume(type){
	var val=$("#"+type).find("option:selected").text(); 
	$('.'+type).html(val); 
}

function tresume(){	
	var id = $.trim(document.getElementById('id').value),
		name=$.trim(document.getElementById('name').value),
		hy=$.trim(document.getElementById('hy').value),
		provinceid=$.trim(document.getElementById('provinceid').value),
		cityid=$.trim(document.getElementById('cityid').value),
		three_cityid=$.trim(document.getElementById('three_cityid').value),
		minsalary=$.trim(document.getElementById('minsalary').value),
		maxsalary=$.trim(document.getElementById('maxsalary').value),
		jobstatus=$.trim(document.getElementById('jobstatus').value),
		jobname=$.trim(document.getElementById('jobname').value),
		sex=$.trim(document.getElementById('sex').value),
		age=$.trim(document.getElementById('age').value),
		edu=$.trim(document.getElementById('edu').value),
		exp=$.trim(document.getElementById('exp').value),
		telphone=$.trim(document.getElementById('telphone').value),
		living=$.trim(document.getElementById('living').value),
		expinfo=$.trim(document.getElementById('expinfo').value),
		eduinfo=$.trim(document.getElementById('eduinfo').value),
		skillinfo=$.trim(document.getElementById('skillinfo').value),
		projectinfo=$.trim(document.getElementById('projectinfo').value);
	if(name==""){
		return mui.toast('请填写姓名！');return false;
	}
	if(sex==''){
		return mui.toast("请选择性别！");return false;
	}
	if(age==''){
		return mui.toast("请填写年龄！");return false;
	}
	
	if(edu==''){
		return mui.toast("请选择最高学历！");return false;
	}
	if(exp==''){
		return mui.toast("请选择工作经验！");return false;
	}
	if(telphone==''){
		return mui.toast("请填写手机号码！");return false;
	}else{
	  var reg= /^[1][3456789]\d{9}$/; //验证手机号码  
		 if(!reg.test(telphone)){
			return mui.toast("手机号码格式错误！");return false;
		 }
	}
	
	if(living==''){
		return mui.toast("请填写现居住地！");return false;
	}
	
	if(jobname==""){
		return mui.toast('请填写意向岗位！');return false;
	}
	if(hy==""){
		return mui.toast('请选择从事行业！');return false;
	}
	if(minsalary==""){
		return mui.toast('请填写期望薪资！');return false;
	}
	if(maxsalary){
		if(parseInt(maxsalary)<=parseInt(minsalary)){
			return mui.toast('最高薪资必须大于最低薪资！');return false;
		}
	}
	var cionly ='';
	if(ct.length<=0 || ct=='new Array()'){
		cionly = '1';
	}
	if(cionly == '1'){
		if(provinceid == '') {
			return mui.toast('请选择期望城市！');return false;
		}
	}else{
		if(cityid==""){
			return mui.toast('请选择期望城市！');return false;
		}
	}
	
	
	if(jobstatus==""){
		return mui.toast('请选择求职状态！');return false;
	}		

	if(expinfo==""){
		return mui.toast('请填写工作经历！');return false;
	}
	if(eduinfo==""){
		return mui.toast('请填写教育经历！');return false;
	}
	document.getElementById('resumesubmit').innerText='提交中...';
	document.getElementById('resumesubmit').id='submit';
	mui.post(wapurl + "/member/index.php?c=savetalentexpect", 
		{id:id,name:name,hy:hy,jobname:jobname,provinceid:provinceid,cityid:cityid,three_cityid:three_cityid,minsalary:minsalary,maxsalary:maxsalary,jobstatus:jobstatus,sex:sex,age:age,edu:edu,exp:exp,telphone:telphone,living:living,eduinfo:eduinfo,expinfo:expinfo,skillinfo:skillinfo,projectinfo:projectinfo,submit:'submit'}, function(data) {
			if(data.error=='1'){
				layermsg('操作成功！',2,function(){window.location.href='index.php?c=talent';}); 
			}else{
				return mui.toast(date.msg);
			}
		}, 'json');
}
$(document).ready(function(){	
	//职位详情页 申请职位
	$(".lt_reward_sq").click(function(){
		
		var jobid=$(this).attr('data-jobid');
		var eid=$(this).attr('data-eid');
		
		
		layer_load('执行中，请稍候...');
		$.post(wapurl+"/member/index.php?c=talentsqjob",{jobid:jobid,eid:eid},function(data){
			layer.closeAll();
			var data=eval('('+data+')');
			if(data.error==1){          
				layermsg('推荐成功',2,function(){location.reload(true);});
				
			}else{
				layermsg(data.msg, 2);return false;
			}
		});
	})
	
})

function tsendmoblie(){
	if($("#send").val()=="1"){
		return false;
	}
	var moblie=$("input[name=linktel]").val();
	var authcode=$("input[name=authcode]").val();
	var reg= /^[1][3456789]\d{9}$/; //验证手机号码
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
					tsend(121);
				}else if(res.error == 106){
					checkCode('vcode_img');
				}
			});
		}
	})
}
function tsend(i){
	i--;
	if(i==-1){
		$("#time").html("重新获取");
		$("#send").val(0);
	}else{
		$("#send").val(1);
		$("#time").html(i+"秒");
		setTimeout("tsend("+i+");",1000);
	}
}
function telstatus(){
	var id = $('#telid').val();
	var linktel = $('#linktel').val();
	
	if(linktel==""){ 
		layermsg('请输入手机号码！',2);return false;
	}
	var code=$("#moblie_code").val();
	if(code==""){ 
		layermsg('请输入短信验证码！',2);return false;
	}
	
	var i=layer_load('执行中，请稍候...',0);
	$.ajaxSetup({cache:false});
	$.post("index.php?c=telstatus",{id:id,linktel:linktel,code:code},function(data){
		layer.closeAll();
		data = eval('('+data+')');
		if(data.error=='1'){
			
			layermsg('授权认证成功！',2,function(){window.location.href='index.php?c=talent';}); 
			
		}else{
			layermsg(data.msg,2); 
		}
	})
}