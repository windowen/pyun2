// 查看联系方式
function getLinkStyle(){

	var msglayer = layer.open({
		type: 1,
		title: '联系方式',
		closeBtn: 1,
		border: [10, 0.3, '#000', true],
		area: ['auto', 'auto'],
		content: $("#link_style_div"),
		cancel:function(){
			window.location.reload();
		}
	});

}


// 查看联系方式，提示剩余下载简历数
function isDownResume(eid, num, url) {
	
	var i = layer.confirm('您还可以查看' + num + '份简历，是否查看？', {
			btn: ['查看', '取消']
		},
		function() {
			layer.close(i);
			for_link(eid, url);
		},
		function() {
			layer.close(i);
		}
	);


}



//简历详情页查看联系方式、下载简历
function for_link(eid, url, todown) {
	
	var i = layer.load('执行中，请稍候...',0);
	
	$.post(url, {eid: eid}, function(data) {
		
		layer.close(i);
		
		var data 	= eval('(' + data + ')');
		var status 	= data.status; 		// 返回状态 
		var type 	= data.type; 		// 简历类型
		
		if(status == 1) {
			
			var msgList		=	data.msgList;
			var	msg			=	msgList.join('');
			var down		=	data.down;	// 下载简历提示语句
			
			if(down	==	1){
				if(type == 2){
					$('#tips_way').html('<div class="yun_prompt_release_tip">以下条件尚未满足,暂时无法下载简历，请按顺序完成：</div>');
				}else{
					$('#tips_way').html('<div class="yun_prompt_release_tip">以下条件尚未满足,暂时无法下载简历，请按顺序完成：</div>');
				}
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
			
		} else if(status == 2) {
			 
			$('#eid').val(eid);
			server_single('downresume',data.jifen,data.price);
			firstTab();
			var msglayer = layer.open({
				type: 1,
				title: '下载简历',
				closeBtn: 1,
				border: [10, 0.3, '#000', true],
				area: ['auto', 'auto'],
				content: $("#tcmsg"),
				cancel:function(){
					window.location.reload();
				}
			});
		 
			
		} else if(status == 3) {
			if(data.html){
				$('#link_style_div').html(data.html);
				var msglayer = layer.open({
					type: 1,
					title: '联系方式',
					closeBtn: 1,
					border: [10, 0.3, '#000', true],
					area: ['auto', 'auto'],
					content: $("#link_style_div"),
					cancel:function(){
						window.location.reload();
					}
				});
			}
			if(todown) {
				window.location.href = todown;
			}
		} else {
			if(data.login){
 				showlogin(data.login);
			}else if(data.usertype != '2'){
				layer.msg('请先登录企业账号' , 2, 8);return false;
			}else{
				layer.msg(data.msg , 2, 8);return false;
			}
		}
	});

}


function settemplate(msg, url) {
	layer.confirm(msg, function() {
		var i =layer.load('执行中，请稍候...',0);
		$.ajaxSetup({
			cache: false
		});
		$.get(url, function(data) {
			layer.close(i);
			var data = eval('(' + data + ')');
			if(data.st == '8') {
				layer.msg(data.msg, Number(data.tm), Number(data.st), function() {
					layer.closeAll('iframe');
				});
				return false;
			} else {
				layer.msg(data.msg, Number(data.tm), Number(data.st), function() {
					location.href = data.url;
				});
				return false;
			}
		});
	});
}

function add_user_talent(uid, usertype) {
	if(usertype == "2") {
		$.layer({
			type: 1,
			title: "添加备注",
			closeBtn: [0, true],
			border: [10, 0.3, '#000', true],
			area: ['380px', '230px'],
			page: {
				dom: "#talent_pool_beizhu"
			}
		});
	} else if(usertype == "") {
		showlogin('2');
	} else {
		layer.msg('只有企业用户，才可以操作！', 2, 8);
		return false;
	}
}

function talent_pool(uid, eid, url) {
	var remark = $("#remark").val();
	layer.load('执行中，请稍候...',0);
	$.post(url, { eid: eid, uid: uid, remark: remark }, function(data) {
		layer.closeAll('loading');
		var data=eval('('+data+')');
		if(data.state == '1') {
			layer.msg(data.msg, 2, data.errcode, function() {
				location.reload();
			});
		} else if(data.state == '2') {
			layer.msg(data.msg, 2, data.errcode, function() {
				layer.close(layer.index);
			});
		} else {
			layer.msg(data.msg, 2, data.errcode);
		}
	});
}

function dayin() {
	$("#operation_box").hide();
	window.print();
}

//检查上一次推荐职位、简历的时间间隔
function recommendInterval(uid, url) {
	var ajaxUrl = weburl + "/index.php?m=ajax&c=ajax_recommend_interval";
	$.post(ajaxUrl, {
		uid: uid
	}, function(data) {
		data = eval('(' + data + ')');
		if(data.status == 0) {
			window.location = url;
		} else {
			layer.msg(data.msg, 3, 8);
		}
	});
}