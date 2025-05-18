function accAdd(arg1, arg2) {
	if (isNaN(arg1)) {
		arg1 = 0;
	}
	if (isNaN(arg2)) {
		arg2 = 0;
	}
	arg1 = Number(arg1);
	arg2 = Number(arg2);
	var r1, r2, m, c;
	try {
		r1 = arg1.toString().split(".")[1].length;
	}
	catch (e) {
		r1 = 0;
	}
	try {
		r2 = arg2.toString().split(".")[1].length;
	}
	catch (e) {
		r2 = 0;
	}
	c = Math.abs(r1 - r2);
	m = Math.pow(10, Math.max(r1, r2));
	if (c > 0) {
		var cm = Math.pow(10, c);
		if (r1 > r2) {
			arg1 = Number(arg1.toString().replace(".", ""));
			arg2 = Number(arg2.toString().replace(".", "")) * cm;
		} else {
			arg1 = Number(arg1.toString().replace(".", "")) * cm;
			arg2 = Number(arg2.toString().replace(".", ""));
		}
	} else {
		arg1 = Number(arg1.toString().replace(".", ""));
		arg2 = Number(arg2.toString().replace(".", ""));
	}
	return (arg1 + arg2) / m;
}

function accSub(arg1, arg2) {
	arg1 = $.trim(arg1);
	arg2 = $.trim(arg2);
	return accAdd(arg1, -arg2);
}

function accMul(arg1, arg2) {
	arg1 = $.trim(arg1);
	arg2 = $.trim(arg2);
	var m = 0,
		s1 = arg1.toString(),
		s2 = arg2.toString();
	try {
		m += s1.split(".")[1].length
	} catch (e) {}
	try {
		m += s2.split(".")[1].length
	} catch (e) {}
	return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
}

function accDiv(arg1, arg2) {
	arg1 = $.trim(arg1);
	arg2 = $.trim(arg2);
	var t1 = 0,
		t2 = 0,
		r1, r2;
	try {
		t1 = arg1.toString().split(".")[1].length
	} catch (e) {}
	try {
		t2 = arg2.toString().split(".")[1].length
	} catch (e) {}
	with(Math) {
		r1 = Number(arg1.toString().replace(".", ""));
		r2 = Number(arg2.toString().replace(".", ""));
		return (r1 / r2) * pow(10, t2 - t1);
	}
}

function shareEnd(etime){
	
	etime = parseInt(etime);
	//当前时间
	var nTime=Date.now();
	//距离结束时间
	var endTime=Math.round((etime*1000-nTime)/1000);
	//剩余天数
	var endDay=parseInt(endTime/3600/24);
	//剩余小时
	var endHour=parseInt(endTime/3600%24);
	//剩余分钟
	var endMiu=parseInt(endTime/60%60);
	//剩余秒
	var endSecond=endTime%60;
	//倒计时显示
	
	$('.help_time').html('活动倒计时：'+endDay+'天'+endHour+'小时'+endMiu+'分'+endSecond+'秒');
	
	setTimeout("shareEnd("+etime+")",1000);
	//倒计时结束 停止并 刷新页面
	//if(timediff==0){return;}
}
