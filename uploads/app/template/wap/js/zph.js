// JavaScript Document
 
$(document).ready(function(){
	$(".fairs_disp_position").click(function(){
		$('.zph_make_icon_yxz').addClass('zph_make_icon_kyd');
		$('.zph_make_icon_yxz').removeClass('zph_make_icon_yxz');
		$(this).find('i').addClass('zph_make_icon_yxz');
		var aid = $(this).attr("aid");
		var price = $(this).attr("price");
		var zph_id = $("#zph_id").val();
		
		layer_load('执行中，请稍候...',0);
	
		$.post(wapurl+"index.php?c=ajax&a=ajaxComjob",{zph:1,id:zph_id},function(data){
			
			layer.closeAll();
			var data	=	eval('('+data+')');
			
			if(data.status == 1){
				
				$("#zphzwprice").text(price);
				$("#clickzph").attr('onclick','clickzph(\''+ aid +'\',\''+ zph_id +'\')');
				$("#zphjobbox").show();
				$("#bg").show(); 
			}else{
				
				layermsg(data.msg,2,8);return false;
			}
		});
	});
	$('#bg').click(function(){
		$('.zph_make_icon_yxz').addClass('zph_make_icon_kyd');
		$('.zph_make_icon_yxz').removeClass('zph_make_icon_yxz');
		$('#bg').hide();
		$(".check_all").attr("checked",false)
		$('.zph_makebox').hide();
		$("#clickzph").attr('onclick','');
	});
	$(".fairs_disp_position1").click(function(){
		var aid=$(this).attr("aid");
		$("#showstatus"+aid).show();
		$("#bg").show();
	}); 
	$(".checkall").click(function() { 
		var name =  'checkbox_job'
		var checked = this.checked;
			
		$("input[name="+name+"]").each(function(){ 
			this.checked=checked; 
		}); 
	});
	$("input[name^='checkbox_job']").click(function(){ 

		if(!this.checked){
			$(".checkall").prop("checked",false);
		}
	});
});


function closeShowStatus(aid){
  	location.reload(true);
}


function clickzph(id, zid) {
	
	var jobid = "";
	$("input[name=checkbox_job]:checked").each(function(){
		if(jobid==""){jobid=$(this).val();}else{jobid=jobid+","+$(this).val();}
	});
	if(!jobid){
		
		layermsg('请选择参展职位', 2, 8);return false;
	}else{
		$.post(wapurl + "/index.php?c=ajax&a=ajaxzphjob",{zid : zid, id : id,jobid:jobid}, function(data) {
			
			var data = eval('(' + data + ')');
			
			if (data.status == 1 || data.status == 3) {	// 1-报名成功 3-展位有其他人报名
				layermsg(data.msg,2,function(){
					
					location.reload();
				});
			} else if (data.status == 2) {
				 
				layer.open({
					title : [ '温馨提示', 'background-color: #FF4351; color:#fff;' ],
					content : data.msg,
					btn : [ '确认', '取消' ],
					shadeClose : false,
					yes : function() {
						window.location.href = wapurl + "member/index.php?c=server&server=zph&id=" + zid + "&bid=" + id;
					}
				});
				 
			} else{ 
				if(data.login == 1){
					
					pleaselogin('您还未登录企业账号，是否登录？',wapurl+'/index.php?c=login')
				}else{
					
					layermsg(data.msg, 2, 8);return false;
				}
			}
		})
	}

}