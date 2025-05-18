function keySelect(value, type, name){
	$("#"+type+"_name").html(name);
	$("#"+type).val(value);
	$("#"+type+"_n").hide();
}

var weburl = "{yun:}$config.sy_weburl{/yun}";

$(document).ready(function(){
	
	$("#newCustomer").click(function(){
		window.location.href = 'index.php?m=crm_customer&c=add'; 
	});
	
	$(".CrmnewFollow").click(function(){
		var uid	= $(this).attr('data-uid');
		var name= $(this).attr('data-name');
		
		if(uid && name){
			$("#com_uid").val(uid);
			$("#fcomname").val(name);
			$("#fcomname").attr("disabled", true);
		}
		
		$("#newwindow").val('1');
		$('.layui-anim_all').html('');
		$.layer({
			type : 1,
			title :'联系跟进', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['20px', ''],
			page : {dom :"#crmNewConcern"}
		});
	});
	
	$(".CrmnewTask").click(function(){
		
		var uid	= $(this).attr('data-uid');
		var name= $(this).attr('data-name');
		
		if(uid && name){
			$("#com_uid").val(uid);
			$("#tcomname").val(name);
			$("#tcomname").attr("disabled", true);
		}
		$('.layui-anim_all').html('');
		$.layer({
			type : 1,
			title :'新建任务', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','460px'],
			offset: ['20px', ''],
			page : {dom :"#crmNewTask"}
		});
	});
	
	$("#newDeal").click(function(){
		$("#newwindow").val('1');
		$('.layui-anim_all').html('');
		$.layer({
			type : 1,
			title :'录订单', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['20px', ''],
			page : {dom :"#crmDeal"}
		});
	});
	
	$("#newWorkLog").click(function(){
		$("#logtitle").val('');
		$("#logcontent").val('')
		$.layer({
			type : 1,
			title :'新建工作日志', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['650px','450px'],
			offset: ['20px', ''],
			page : {dom :"#crmworklog"}
		});
	});
	
	$("#newOut").click(function(){
		
		var uid		=	$(this).attr('data-uid');
		var name	=	$(this).attr('data-name');
		
		$("#com_uid").val(uid);
		$("#outComName").val(name);
		
		$.layer({
			type : 1,
			title :'外出申请', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['500px','480px'],
			offset: ['100px', ''],
			page : {dom :"#crmOut"}
		});
	});
	
})

function OpenContact(uid,url){
	var pytoken = $('#pytoken').val();
	$.post(url,{pytoken:pytoken,uid:uid},function(data){
		var data=eval('('+data+')');
		$('#comLink_name').val(data.name);
		$('#comLink_ratingname').val(data.ratingname);
		$('#comLink_vipetime').val(data.ratingtime);
		$('#comLink_city').val(data.cityname);
		$('#comLink_linkman').val(data.linkman);
		$('#comLink_linktel').val(data.moblie);
		
		$.layer({
			type : 1,
			title :'客户信息', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','350px'],
			offset: ['20px', ''],
			page : {dom :"#comLinkInfo"}
		});
	});
}

function CompleteDetail(id,url){
	var pytoken = $('#pytoken').val();
	$.post(url,{pytoken:pytoken,id:id},function(data){
		$('#reason').val(data);
		$('#taskreasonsubmit').hide();
		
		$.layer({
			type : 1,
			title :'任务未完成原因', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','250px'],
			offset: ['20px', ''],
			page : {dom :"#taskstatus"}
		});
	});
}
 
function crmworklog(){
	if(!$("#logtitle").val()){
       layer.msg('请填写日志标题',2,8);return false;
	}
	if(!$("#logcontent").val()){
       layer.msg('请填写日志内容',2,8);return false;
	}
}

function showWorkLog(id,url) {
	var pytoken = $('#pytoken').val();
	$.post(url,{pytoken:pytoken,id:id},function(data){
		var data=eval('('+data+')');
		$('#logtitleshow').html(data.title);

		$('#logcontentshow').html(data.content);
	});
	$.layer({
		type : 1,
		title :'工作日志详情', 
		closeBtn : [0 , true],
		border : [10 , 0.3 , '#000', true],
		area : ['850px','300px'],
		offset: ['20px', ''],
		page : {dom :"#worklogshow"}
	});
}


$(document).ready(function(){
	$("#Deliver").click(function(){
		
		var uid	=	$(this).attr('data-uid');
		
		if(uid){
			
			$("#com_uid").val(uid);
		}else{
			
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			
			if (uids == "") {
				parent.layer.msg("请选择要转交的客户！", 2, 8);
				return false;
			} else {
				$("#com_uid").val(uids);
			}
		}
		
		
		$.layer({
			type : 1,
			title :'转交客户', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['50px', ''],
			page : {dom :"#crmDeliver"}
		});
	});
	
	$("#Giveup").click(function(){
		var uid	=	$(this).attr('data-uid');
		if(uid){
			
			$("#com_uid").val(uid);
		}else{
			
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			
			if (uids == "") {
				parent.layer.msg("请选择要放弃的客户！", 2, 8);
				return false;
			} else {
				$("#com_uid").val(uids);
			}
		}
		
		$.layer({
			type : 1,
			title :'放弃客户', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['50px', ''],
			page : {dom :"#crmGiveup"}
		});
	});
	
	$("#CrmnewReceive").click(function(){
		
		var uid		=	$(this).attr('data-uid');
		
		if(uid){
			var uids = uid;
		}else{
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			
			if (uids == "") {
				parent.layer.msg("请选择要转交的客户！", 2, 8);
				return false;
			} 
		}
		 
		var pytoken	=	$('#pytoken').val();
		
		loadlayer();
		$.post('index.php?m=crm_index&c=receiveKh', {uids: uids , pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			
			if(data == '1'){
				
				parent.layer.msg('客户领取成功！', 2, 9, function(){
						
					location.reload();
				});
				
			}else if (data == '2'){
				
				parent.layer.msg('客户领取失败！', 2, 8);
				return false;
			}
		});
		
	});
	
	$("#UserStatus").click(function(){
		
		var uid		=	$(this).attr('data-uid');
		
		if(uid){
			var status	=	$(this).attr('data-status');
			$("#com_uid").val(uid);
			layui.use(['form'], function() {
		        var f = layui.form;
	 
		        f.val('formCrmStatusType',{
		        	"status": status
		        })
		        f.render();
		    });
			
		}else{
			
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			
			if (uids == "") {
				parent.layer.msg("请选择要修改状态的客户！", 2, 8);
				return false;
			} else {
				$("#com_uid").val(uids);
				layui.use(['form'], function() {
			        var f = layui.form;
			        f.render();
			    });
			}
		}
		
		$("#isSt").val('status');
		$("#select_CrmStatus").show();
		$("#select_CrmType").hide();
		
		$.layer({
			type : 1,
			title :'客户状态', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['50px', ''],
			page : {dom :"#crmStatusType"}
		});
	});

	
	
	$("#LevelType").click(function(){
		
		var uid		=	$(this).attr('data-uid');
		
		if(uid){
			var type	=	$(this).attr('data-type');
			$("#com_uid").val(uid);
			layui.use(['form'], function() {
		        var f = layui.form;
		        f.val('formCrmStatusType',{
		        	"level": type
		        })
		        f.render();
		    });
		}else{
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			
			if (uids == "") {
				parent.layer.msg("请选择要修改等级的客户！", 2, 8);
				return false;
			} else {
				$("#com_uid").val(uids);
				layui.use(['form'], function() {
			        var f = layui.form;
			        f.render();
			    });
			}
			
		}	
		
		$("#isSt").val('type');
		$("#select_CrmStatus").hide();
		$("#select_CrmType").show();
		
		$.layer({
			type : 1,
			title :'客户等级', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['50px', ''],
			page : {dom :"#crmStatusType"}
		});
	});
	
    $("#crmRemarkCom").click(function(){
		
		var remark	=	$("#crm_remark_com").val();
		var uid		=	$("#crmComid").val();
		
		if(remark == ''){
			layer.msg('请填写备注信息！');
			return false;
		}
		
		var pytoken	=	$('#pytoken').val();
		
		loadlayer();
		
		$.post('index.php?m=crm_customer&c=remarkCom', {uid: uid, crm_remark:remark, pytoken:pytoken}, function(data){
			
			parent.layer.closeAll('loading');
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_customer&c=com&id='+uid;
				});
				
			}else if (data == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
		
	});
	
	// 添加/修改根据记录
	$("#crmConcern").click(function(){
		
		var uid		=	$("#crmComid").val();
		var comid	=	$("#com_uid").val();
		var remark	=	$("#fRemark").val();
		var type	=	$("#fWay option:selected").val();
		
		var linkman	=	$("#flinkman").val();
		var linktel	=	$("#flinktel").val();
		
		var follow	=	$("#follow").is(":checked") ? 1: 0;
		
		if(follow == 1){
			var ftime	=	$("#ftime").val();
			
			var now		=	Date.parse(new Date()) / 1000;
			if(ftime){
				stime	=	Date.parse(new Date(ftime)) / 1000;
				if(stime < now){
					parent.layer.msg('任务时间需在当前时间之后，请重新填写',2,8);
					return false;
				}
			}
		}
		var id		=	$("#follow_id").val();
		var taskid	=	$("#task_id").val();
		
		var pytoken	=	$('#pytoken').val();
		
		var newWindow	=	$("#newwindow").val();
		
		loadlayer();
		
		$.post('index.php?m=crm_customer&c=addFollow', {id: id, comid:comid, uid: uid, type: type, remark: remark, linkman: linkman, linktel: linktel, follow: follow, ftime: ftime, taskid:taskid, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					
					if(newWindow=='1'){	//	CRM首页添加
						location.href = 'index.php?m=crm_customer&self=1';
						window.open(weburl+'/admin/index.php?m=crm_customer&c=com&id='+comid,'_blank');
						
					}else{
						location.href = 'index.php?m=crm_customer&c=com&id='+uid;
					}
					
				});
				
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
		
	});
	
	// 修改订单点击
	$("#upOrder").click(function(){
		
		var comid	=	$(this).attr('data-uid');
		$("#com_uid").val(comid);
		$("#comname").attr('disabled', '');
		var id		=	$(this).attr('data-id');
		var comname	=	$(this).attr('data-comname');
		var paytype	=	$(this).attr('data-pay');
		var rating	=	$(this).attr('data-rating');
		var price	=	$(this).attr('data-price');
		var remark	=	$(this).attr('data-remark');
		
		// 表单（lay-filter：crmFormDeal）赋值
		layui.use(['form'], function() {
	        var f = layui.form;
	        f.val('crmFormDeal',{
	        	"crm_keyword": comname,
	        	"order_type": paytype,
	        	"rid": rating,
	        	"order_price": price,
	        	"order_remark": remark,
	        	"orderId": id
	        })
	        f.render();
	    });
		
		$("#newwindow").val('1');
		$('.layui-anim_all').html('');
		$.layer({
			type : 1,
			title :'录订单', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['20px', ''],
			page : {dom :"#crmDeal"}
		});
	});
	
	
	// 录入订单
	$("#newOrder").click(function(){
		
		var id		=	$("#orderId").val();
		
		var uid		=	$("#com_uid").val();
		
		if(uid == ''){
			parent.layer.msg('请填写客户名称！', 2, 8);
			return false;
		}
		
		var type	=	$("#order_type option:selected").val();
		if(type == ''){
			parent.layer.msg('请选择支付方式！', 2, 8);
			return false;
		}
		
		var rating	=	$("#rid_val option:selected").val();
		if(rating == ''){
			parent.layer.msg('请选择会员套餐！', 2, 8);
			return false;
		}
		
		var price	=	$("#order_price").val();
		if(parseFloat(price) <= 0){
			parent.layer.msg('付款金额不得低于0元！', 2, 8);
			return false;
		}
		
		var remark	=	$("#order_remark").val();
		
		var pytoken	=	$('#pytoken').val();
		var newWindow	=	$("#newwindow").val();
		
		loadlayer();
		
		$.post('index.php?m=crm_index&c=addDeal', {id: id, uid: uid, rating: rating, order_price: price, order_type: type, order_remark: remark, pytoken:pytoken}, function(data){
			
			parent.layer.closeAll('loading');
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					
					if(newWindow=='1'){	//	CRM首页添加
						location.href = 'index.php?m=crm_index';
						window.open(weburl+'/admin/index.php?m=crm_customer&c=com&id='+uid,'_blank');
						
					}else{
						location.href = 'index.php?m=crm_customer&c=com&id='+uid;
					}
					
				});
				
			}else if (data == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
		
	});
	
	
	// 新建任务
	$("#crmTask").click(function(){
		
		var type	=	$("#taskType option:selected").val();
		if(type == ''){
			parent.layer.msg('请选择任务类型', 2, 8);return false;
		}
		var comid	=	$("#com_uid").val();
		
		if(comid == ''){
			parent.layer.msg('客户数据错误，请重新填写客户名称', 2, 8);return false;
		}
		
		var now		=	Date.parse(new Date()) / 1000;
		var stime	=	$('#taskTime').val();
		if(stime){
			stime	=	Date.parse(new Date(stime)) / 1000;
			if(stime < now){
				parent.layer.msg('任务时间需在当前时间之后，请重新填写');
				return false;
			}
		}
		 
		var remark	=	$("#taskRemark").val();
		if(remark == ''){
			parent.layer.msg('请简要地填写任务描述内容', 2, 8);return false;
		}
		var pytoken	=	$('#pytoken').val();
		
		loadlayer();
		
		$.post('index.php?m=crm_waitingtask&c=add', {comid: comid, type: type, content: remark, stime: stime, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_waitingtask';
				});
				
			}else if (data == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
		
	});
	
    
    
    $("#crmComInfo").click(function(){
		
		var uid			=	$("#crmComid").val();
		var linkman		=	$("#linkman").val();
		var linkjob		=	$("#linkjob").val();
		var linktel		=	$("#linktel").val();
		var linkphone	=	$("#linkphone").val();
		var linkmail	=	$("#linkmail").val();
		
		if(linktel =='' ){
			layer.msg('请填写联系手机！');
			return false;
		}else if(!isjsMobile(linktel)){
			layer.msg('联系手机格式错误！');
			return false;
		}
		if(linkphone  && !isjsTell(linkphone)){
			layer.msg('固定电话格式错误！');
			return false;
		}
		if(linkmail  && !check_email(linkmail)){
			layer.msg('联系邮箱格式错误！');
			return false;
		}
		var pytoken	=	$('#pytoken').val();
		loadlayer();
		
		$.post('index.php?m=crm_customer&c=upComLink', {uid: uid,linkman:linkman, linkjob:linkjob, linktel:linktel, linkphone:linkphone, linkmail:linkmail, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_customer&c=com&id='+uid;
				});
				
			}else if (data == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
		
	});
    
}) 

function crmDeliver(){
	
	var crmuser	= 	$("#crmuser  option:selected").val();
	
	if(crmuser == ''){
		layer.msg('请选择需要转交的客户经理！');
		return false;
	}else{
		var crmArr 	= 	crmuser.split('-');
		var crm_uid	=	crmArr[0];
		var crm_name= 	crmArr[1];
	}
	
	var uid		=	$("#com_uid").val();	
	if(uid == ''){
		layer.msg('请选择需要转交的客户！');
		return false;
	}
	var remark	=	$("#deliverRemark").val();	
	var pytoken	=	$("#pytoken").val();
	
	loadlayer();
	$.post('index.php?m=crm_customer&c=deliver', {crm_uid: crm_uid, crm_name: crm_name, uid: uid, remark: remark, pytoken:pytoken}, function(data){
		
		parent.layer.closeAll('loading');
		
		var data = eval('('+data+')');
		
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.reload();
			});
			
		}else if (data == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
		
	});
}

function crmGiveup(){
	
	var uid		=	$("#com_uid").val();
	
	if(uid == ''){
		layer.msg('请选择需要放弃的客户！');
		return false;
	}
	var remark	=	$("#giveupRemark").val();
	if(remark == ''){
		layer.msg('请填写放弃客户的说明备注！');
		return false;
	}
	var pytoken	=	$("#pytoken").val();
	
	loadlayer();
	$.post('index.php?m=crm_customer&c=giveUp', {uid: uid, remark: remark, pytoken:pytoken}, function(data){
		
		parent.layer.closeAll('loading');
		
		var data = eval('('+data+')');
		
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.reload();
			});
			
		}else if (data == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
		
	});
}

function crmStatusType(){
	
	var uid		=	$("#com_uid").val();
	
	if(uid == ''){
		layer.msg('请选择需要修改的客户！');
		return false;
	}
	var isSt	=	$("#isSt").val();
	
	if(isSt == 'status'){
		var status	=	$('input[name="status"]:checked').val();
		if(status == ''){
			layer.msg('请选择需要客户状态！');
			return false;
		}else{
			var status 	= 	status.split('-');
			var crm_st	=	status[0];
			var crm_st_n	= 	status[1];
		}
	}else if(isSt == 'type'){
		var type	=	$('input[name="level"]:checked').val();
		if(type == ''){
			layer.msg('请选择需要客户等级！');
			return false;
		}else{
			var type 	= 	type.split('-');
			var crm_st	=	type[0];
			var crm_st_n	= 	type[1];
		}
	}
	
	var remark	=	$("#st_Remark").val();
	var pytoken	=	$("#pytoken").val();
	loadlayer();
	$.post('index.php?m=crm_customer&c=upStatusType', {uid: uid, st: crm_st, st_n:crm_st_n, isSt: isSt, remark: remark, pytoken:pytoken}, function(data){
		
		parent.layer.closeAll('loading');
		
		var data = eval('('+data+')');
		
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.reload();
			});
			
		}else if (data == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
		
	});
}

function crmOut(){
	
	var reason	= 	$("#reason").val();
	var stime	=	$("#outStime").val();
	var etime	=	$("#outEtime").val();
	var remark	=	$("#outRemark").val();
	
	var uid		=	$("#com_uid").val();
	var name	=	$("#outComName").val();
	
	
	if(!uid && !name){
       layer.msg('请填写客户名称',2,8);return false;
	}
	
	if(!reason){
       layer.msg('请选择外出原因',2,8);return false;
	}
	
	if(!stime){
       layer.msg('请选择外出时间',2,8);return false;
	}
	
	var now		=	Date.parse(new Date()) / 1000;
	
	if(stime){
		
		stime	=	Date.parse(new Date(stime)) / 1000;
		
		if(stime < now){
			layer.msg('外出时间必须大于现在时间，请重新设置',2,8);
			return false;
		}
		etime	=	Date.parse(new Date(etime)) / 1000;
		
		if(stime > etime){
			layer.msg('返回时间必须大于外出时间，请重新设置',2,8);
			return false;
		}
	}
	var pytoken	=	$("#pytoken").val();
	
	loadlayer();
	
	$.post('index.php?m=crm_out&c=add', {reason:reason, uid: uid, name: name, stime: stime, etime: etime, remark: remark, pytoken: pytoken}, function(data){
		
		parent.layer.closeAll('loading');
		
		var data = eval('('+data+')');
		
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.href = 'index.php?m=crm_customer&c=com&id='+uid;
			});
			
		}else if (data == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
		
	});
	
}

// 短信模板快捷操作
$(document).ready(function(){
	
	$(".msgCrmTp").click(function(){
		
		var uid	=	$("#crmComid").val();
		var tp 	= 	$(this).attr("data-tp");
		
		if(tp == '' || uid == ''){
			layer.msg('参数错误，请重试', 2, 8);
			return false;
		}else{
			$("#msgTp").val(tp);
		}
		var pytoken = $('#pytoken').val();
		
		$.post('index.php?m=crm_customer&c=getMsgTpl', {uid: uid, tp: tp, pytoken: pytoken}, function(data){
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				 $("#sms_conent").html(data.content);
				 $("#msgLength").html(data.length);
			}else if (data == '8'){
				layer.msg(data.msg, 2, 8);
				return false;
			}
			
		})
		
	});
	
	$("#btnSendMsg").click(function(){
		
		var uid	=	$("#crmComid").val();
		var tp 	= 	$("#msgTp").val();
		
		if(tp == '' || uid == ''){
			layer.msg('参数错误，请重试', 2, 8);
			return false;
		}
		
		var content =	$("#sms_conent").val();
		var phone	=	$("#msgPhone").val();
		var pytoken = 	$('#pytoken').val();
		
		$.post('index.php?m=crm_customer&c=sendMsg', {uid: uid, tp: tp, content: content, mobile: phone, pytoken: pytoken}, function(data){
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_customer&c=com&id='+uid;
				});
			}else if (data == '8'){
				layer.msg(data.msg, 2, 8);
				return false;
			}
			
		})
		
	});
	
	//修改密码
	$("#upPassword").click(function(){
		
		var password 	= 	$("#newPass").val();
		var uid			=	$("#uid").val();
		var pytoken		=	$("#pytoken").val();
		
		if(password =='' || password.length<6 || password.length>20){
			layer.msg("请输入6-20位（字母、数字、符号）!",2,8);return false;
		}
		 
		loadlayer();
		
		$.post("index.php?m=crm_customer&c=upPassword",{password: password, uid: uid, pytoken: pytoken},function(data){
			
			parent.layer.closeAll('loading');
			
			var data = eval('('+data+')');
			
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_customer&c=com&id='+uid;
				});
			}else if (data == '8'){
				layer.msg(data.msg, 2, 8);
				return false;
			}
			
		});
	});
	
});
