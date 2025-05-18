//grade开始
$(document).ready(function(){
    $(".grade-w>li").click(function(){
        $(".grade-t")
            .css("left","30.48%")
    });
});
$(document).ready(function(){
    $(".grade-t>li").click(function(){
        $(".grade-s")
            .css("left","66.96%")
    });
});


$(document).ready(function(){
    $(".popup").click(function(){
		var pop=$(this).attr('data-pop');
		
		var type='grade-w-roll';
		var style='top:5.9rem;'
		
        if ($('.'+pop+'-eject').hasClass(type)) {
			$('body').removeAttr('style');
			$('.popshow').removeClass(type);
			$('.popshow').removeAttr('style',style);
			$('.popshow').removeClass('popshow');
        } else {
        	$('.'+pop+'-eject').attr('style',style);
			$('.popshow').removeAttr('style',style);
            $('.'+pop+'-eject').addClass(type);
			$('.popshow').removeClass(type);
			$('.popshow').removeClass('popshow');
			$('.'+pop+'-eject').addClass('popshow');
			$('body').attr('style','position: fixed; width: 100%;');
        }
    });
});
//Category开始
$(document).ready(function(){
    $(".Category-w>li").click(function(){
        $(".Category-t").css("left","30.48%")
    });
});

$(document).ready(function(){
    $(".Category-t>li").click(function(){
        $(".Category-s").css("left","66.96%")
    });
});

//Gengduo开始
$(document).ready(function(){
    $(".Gengduoj-w>li").click(function(){
        $(".Gengduoj-t").css("left","50%")
    });
});

//Gengduos开始
$(document).ready(function(){
    $(".Gengduos-w>li").click(function(){
        $(".Gengduos-t").css("left","50%")
    });
});

//Gengduot开始
$(document).ready(function(){
    $(".Gengduot-w>li").click(function(){
        $(".Gengduot-t").css("left","50%")
    });
});

//redeem开始
$(document).ready(function(){
    $(".redeem-w>li").click(function(){
        $(".redeem-t").css("left","50%")
    });
});
//js点击事件监听开始
function grade1(id,name,type,smartya){
    $(".grade-w li").removeClass("yun_category_on");	
	$(".qgrade"+id).addClass('yun_category_on');
	
	var url = wapurl+"?c=ajax&a=getcity";
	
	$(".grade-s").attr("style","");
    $.post(url,{id:id,type:'cityid'},function(data){
		$("#cityid").html(data);	
	})	
}

function gradet(id,name,type){
    $(".grade-t li").removeClass("yun_category_ons");	
	$(".qgradet"+id).addClass('yun_category_ons');
    $.post(wapurl+"?c=ajax&a=getcity",{id:id,type:'three_cityid'},function(data){
		 $(".grade-s").css("left","66.96%");
		 $("#three_cityid").html(data);			
	})	
}


function Categorytw(id,name,type){
	$(".Category-w li").removeClass("yun_category_on");	
	$(".qCategorytw"+id).addClass('yun_category_on');
	$(".Category-s").attr("style","");
	$.post(wapurl+"?c=ajax&a=getjob",{id:id,type:'jobone_son'},function(data){
		$("#jobone_son").html(data);			
	})	
}

function Categoryt(id,name,type){
	$(".Category-t li").removeClass("yun_category_ons");	
	$(".qCategoryt"+id).addClass('yun_category_ons');
	layer_load('执行中，请稍候...');
	$.post(wapurl+"?c=ajax&a=getjob",{id:id,type:'job_post'},function(data){	
		layer.closeAll();
		$(".Category-s").css("left","56.96%")        
		$("#job_post").html(data);									
	})	
}


function redeemw(id,name,type){
	$(".redeem-w li").removeClass("yun_category_on");	
	$(".qredeemw"+id).addClass('yun_category_on');
	$.post(wapurl+"?c=ajax&a=getredeem",{id:id,type:'tnid'},function(data){
		$("#tnid").html(data);			
	})	
}
$(document).ready(function(){
	$(".Gengduoj-w").find("li").bind("click",function(){
		var id=$(this).attr("tab_name");
		$(".Gengduoj-t").hide();
		$(".yun_category_on").removeClass("yun_category_on");
		$(this).addClass("yun_category_on");
		$("#"+id).show();});	
	$(".Gengduoj-t").find("li").bind("click",function(){
		var id=$(this).attr("tab_name");
		var data=$(this).attr("data");
		var html=$(this).html();
		$("#"+id).html("&nbsp;&nbsp;"+html);
		$("#"+id+"i").val(data);
		$(".yun_category_on").removeClass("yun_category_on");
		$(this).parent().hide();
		});
});

$(document).ready(function(){
	$(".Gengduos-w").find("li").bind("click",function(){
		var id=$(this).attr("tab_name");
		$(".Gengduos-t").hide();
		$(".yun_category_on").removeClass("yun_category_on");
		$(this).addClass("yun_category_on");
		$("#"+id).show();
		});	
	$(".Gengduos-t").find("li").bind("click",function(){
		var id=$(this).attr("tab_name");
		var data=$(this).attr("data");
		var html=$(this).html();
		$("#"+id).html("&nbsp;&nbsp;"+html);
		$("#"+id+"i").val(data);
		$(".yun_category_on").removeClass("yun_category_on");
		$(this).parent().hide();
		});
});

$(document).ready(function(){
	$(".Gengduot-w").find("li").bind("click",function(){
		var id=$(this).attr("tab_name");
		$(".Gengduot-t").hide();
		$(".yun_category_on").removeClass("yun_category_on");
		$(this).addClass("yun_category_on");
		$("#"+id).show();});
	$(".Gengduot-t").find("li").bind("click",function(){
		var id=$(this).attr("tab_name");
		var data=$(this).attr("data");
		var html=$(this).html();
		$("#"+id).html("&nbsp;&nbsp;"+html);
		$("#"+id+"i").val(data);
		$(".yun_category_on").removeClass("yun_category_on");
		$(this).parent().hide();
		});		
	
});
function lihide(name){
	$('body').removeAttr('style');
	$("."+name).removeClass('grade-w-roll');
	$("."+name).removeClass('grade-w-rolls');
	$("."+name).removeClass('popshow');
	$("."+name).removeAttr('style');
}

function Closes(type) {
    $('body').removeAttr('style');
	$("#"+type+"list").removeClass('grade-w-roll');
	$("#"+type+"list").removeClass('grade-w-rolls');
    $("#"+type+"list").removeClass('popshow'); 	
    $("#"+type+"list").removeAttr('style');
}