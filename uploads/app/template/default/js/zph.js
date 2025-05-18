$(document).ready(function(){
	$(".fairs_disp_position").hover(function(){
		
		var aid=$(this).attr("aid");
		$(this).addClass("zph_popup");
		$(".order_two").hide();
		$("#showstatus"+aid).show();
	},function(){
		
		var aid=$(this).attr("aid");
		$(this).removeClass("zph_popup");
		$("#showstatus"+aid).hide();
	})   
})


function submitzph(){
	var bid = $("#bid").val();
	var zid =$("#zid").val();
	var jobid = "";
	$("input[name=checkbox_job]:checked").each(function(){
		if(jobid==""){jobid=$(this).val();}else{jobid=jobid+","+$(this).val();}
	});
	
	if(!jobid){
		
		layer.msg('请选择参展职位' , 2, 8);return false;
	}else{
		
		layer.load('执行中，请稍候...',0);
		
		$.post(weburl+"/index.php?m=ajax&c=ajaxZph",{zid:zid,id:bid,jobid:jobid},function(data){
			
			layer.closeAll('loading');
			
			var data	=	eval('('+data+')');
			var status 	= 	data.status;
			
			if(status == 1){
				
				layer.msg(data.msg, 2, 9, function(){
					location.reload();
				});
				
			}else if(status == 2){
				
				server_single('zph',data.jifen,data.price);
				
				$('#zid').val(data.zid);
				$('#bid').val(data.bid);
				if(data.spid == 1){
					layer.msg('当前账户套餐余量不足，请联系主账户增配！' , 2, 8);return false;
				}else{
					firstTab();
					var msglayer = layer.open({
						type: 1,
						title: '预定招聘会',
						closeBtn: 1,
						border: [10, 0.3, '#000', true],
						area: ['auto', 'auto'],
						content: $("#tcmsg"),
						cancel: function(){
							window.location.reload();
						}
					});
				}
			}else if(status == 3){
				layer.msg(data.msg, 2, 8, function(){
					location.reload();
				});
			}else{
				if(data.login == 1){
					showlogin('2');
				}else{
					layer.msg(data.msg, 2, 8);
				}
			}
		}) 
	}
}
