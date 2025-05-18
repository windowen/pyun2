function checkinfo() {
	var name = $.trim(document.getElementById('name').value),
		sex = $.trim(document.getElementById('sex').value),
		birthday = $.trim(document.getElementById('birthday').value),
		edu = $.trim(document.getElementById('edu').value),
		exp = $.trim(document.getElementById('exp').value),
		living = $.trim(document.getElementById('living').value),
		telphone = $.trim(document.getElementById('telphone').value),
		email = $.trim(document.getElementById('email').value),
		address = $.trim(document.getElementById('address').value),
		height = $.trim(document.getElementById('height').value),
		weight = $.trim(document.getElementById('weight').value),
		nationality = $.trim(document.getElementById('nationality').value),
		marriage = $.trim(document.getElementById('marriage').value),
		domicile = $.trim(document.getElementById('domicile').value),
		qq = $.trim(document.getElementById('qq').value),
		preview = $.trim(document.getElementById('preview').value),
		homepage = $.trim(document.getElementById('homepage').value),
		phototype = $.trim(document.getElementById('phototype').value),
		nametype = $.trim(document.getElementById('nametype').value);

	if(name == '') {
		return mui.toast('请输入姓名！');
	}
	if(sex == '') {
		return mui.toast('请选择性别！');
	}
	if(birthday == '') {
		return mui.toast('请选择出生年月！');
	}
	if(edu == '') {
		return mui.toast('请选择最高学历！');
	}
	if(exp == '') {
		return mui.toast('请选择工作经验！');
	}
	if(living == '') {
		return mui.toast('请输入现居住地！');
	}
	if(telphone == '') {
		return mui.toast('请输入手机！');
	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(email != "" && !myreg.test(email)) {
		mui.toast('邮箱格式错误！');
		return false;
	}


	formData.append('name', name);
	formData.append('sex', sex);
	formData.append('birthday', birthday);
	formData.append('edu', edu);
	formData.append('exp', exp);
	formData.append('living', living);
	formData.append('telphone', telphone);
	formData.append('email', email);
	formData.append('address', address);
	formData.append('height', height);
	formData.append('weight', weight);
	formData.append('nationality', nationality);
	formData.append('marriage', marriage);
	formData.append('domicile', domicile);
	formData.append('qq', qq);
	formData.append('preview', preview);
	formData.append('homepage', homepage);
	formData.append('phototype', phototype);
	formData.append('nametype', nametype);
	formData.append('submit', 'submit');
	layer_load('执行中，请稍候...');
	$.ajax({
		url: "index.php?c=info",
		type: 'post',
		data: formData,
		contentType: false,
		processData: false,
		dataType: 'json',
		success: function(res) {
			layer.closeAll();
			var res = JSON.stringify(res);
			var data = JSON.parse(res);
			if(data.url) {
				layermsg(data.msg, 2, function() {
					location.href = data.url;
				});
			} else {
				layermsg(data.msg, 2);
				return false;
			}
		}
	})
}

function kresume() {
	var table = $.trim(document.getElementById('table').value),
		eid = $.trim(document.getElementById('eid').value),
		id = $.trim(document.getElementById('id').value);

	if(table == 'work') {
		var name = $.trim(document.getElementById('name').value),
			title = $.trim(document.getElementById('title').value),
			sdate = $.trim(document.getElementById('sdate').value),
			edate = $.trim(document.getElementById('edate').value),
			totoday = $.trim(document.getElementById('totoday').value),
			content = $.trim(document.getElementById('content').value);
		if(name == '') {
			return mui.toast('请输入单位名称！');
		}
		if(sdate == '') {
			return mui.toast('请选择入职时间！');
		} else if(edate) {
			if(sdate > edate) {
				return mui.toast('请确认日期先后顺序！');
			}
		}
		if(edate == '' && totoday != 1) {
			return mui.toast('请选择离职时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			totoday: totoday,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'edu') {
		var name = $.trim(document.getElementById('name').value),
			sdate = $.trim(document.getElementById('sdate').value),
			edate = $.trim(document.getElementById('edate').value),
			education = $.trim(document.getElementById('education').value),
			specialty = $.trim(document.getElementById('specialty').value);
		if(name == '') {
			return mui.toast('请输入学校名称！');
		}
		if(sdate == '' || edate == '') {
			return mui.toast('请正确选择在校时间！');
		}
		if(sdate > edate) {
			return mui.toast('入校时间不能大于离校时间！');
		}
	    
    if(education=="") {
			return mui.toast('请选择最高学历');
		}
		var arr = {
			name: name,
			sdate: sdate,
			edate: edate,
			table: table,
			eid: eid,
			id: id,
			education: education,
			specialty: specialty,
			submit: 'submit'
		}
	} else if(table == 'project') {
		var name = $.trim(document.getElementById('name').value),
			title = $.trim(document.getElementById('title').value),
			sdate = $.trim(document.getElementById('sdate').value),
			edate = $.trim(document.getElementById('edate').value),
			content = $.trim(document.getElementById('content').value);
		if(name == '') {
			return mui.toast('请输入项目名称！');
		}
		if(sdate == '' || edate == '') {
			return mui.toast('请正确选择项目时间！');
		}
		if(sdate > edate) {
			return mui.toast('开始时间不能大于结束时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'training') {
		var name = $.trim(document.getElementById('name').value),
			title = $.trim(document.getElementById('title').value),
			sdate = $.trim(document.getElementById('sdate').value),
			edate = $.trim(document.getElementById('edate').value),
			content = $.trim(document.getElementById('content').value);
		if(name == '') {
			return mui.toast('请输入培训中心！');
		}
		if(sdate == '' || edate == '') {
			return mui.toast('请正确选择培训时间！');
		}
		if(sdate > edate) {
			return mui.toast('开始时间不能大于结束时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'skill') {
		var name = $.trim(document.getElementById('name').value),
			longtime = $.trim(document.getElementById('longtime').value),
			ing = $.trim(document.getElementById('ing').value),
			preview = $.trim(document.getElementById('preview').value);
		if(name == '') {
			return mui.toast('请输入技能名称！');
		}
		if(ing == '') {
			return mui.toast('请选择熟练程度！');
		}

		formData.append('name', name);
		formData.append('longtime', longtime);
		formData.append('ing', ing);
		formData.append('table', table);
		formData.append('preview', preview);
		formData.append('eid', eid);
		formData.append('id', id);
		formData.append('submit', submit);
		
	} else if(table == 'show') {
		var title = $.trim(document.getElementById('title').value),
			sort = $.trim(document.getElementById('sort').value),
 			id = $.trim(document.getElementById('id').value),
		 	preview = $.trim(document.getElementById('preview').value),
			picid = $('input[name="picid"]').val();
		
		if(title == '') {
			return mui.toast('作品标题不能为空！');
		}
		if(sort == '') {
			return mui.toast('作品排序不能为空！');
		}
		if(preview == '' && id == '') {
			return mui.toast('请上传作品！');
		}

		formData.append('title', title);
		formData.append('sort', sort);
		formData.append('table', table);
		formData.append('eid', eid);
		formData.append('preview', preview);
		formData.append('id', id);
		formData.append('submit', submit);
	} else if(table == 'other') {
		var name = $.trim(document.getElementById('name').value),
			content = $.trim(document.getElementById('content').value);
			
		if(name == '') {
			return mui.toast('请输入其他标题！');
		}
		if(content == '') {
			return mui.toast('请输入其他描述！');
		}
		var arr = {
			name: name,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'resume') {
		var description = $.trim(document.getElementById('description').value);
		var alltag = $.trim(document.getElementById('tag').value);
		var num = 0;
		$(".yun_my_introduce_bq_cur").each(function(){
			if($(this).attr('tag-class')=='2'){ 
				num++; 
			 } 
		}); 
		if(num > 5) {
			return mui.toast('最多只能选择五项！');
		}
		if(description == '') {
			return mui.toast('请输入自我评价！');
		}
		var arr = {
			description: description,
			table: table,
			eid: eid,
			id: id,
			tag: alltag,
			submit: 'submit'
		}
	} else if(table == 'doc') {
		var doc = UM.getEditor('doc').getContent();
		if(doc == '') {
			return mui.toast('请输入黏贴简历内容！');
		}
		var arr = {
			doc: doc,
			table: table,
			eid: eid,
			id: id,
			submit: 'submit'
		}
	}
	var fr = $('#fr').val();
	
	if(table == "skill" || table == "show") {
		formData.append('fr', fr);
		layer_load('执行中，请稍候...');
		$.ajax({
			url: "index.php?c=saveresumeson",
			type: 'post',
			data: formData,
			contentType: false,
			processData: false,
			dataType: 'json',
			success: function(res) {
				layer.closeAll();
				var res = JSON.stringify(res);
				var data = JSON.parse(res);
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = data.url;
					});
				} else {
					layermsg(data.msg, 2);
					return false;
				}
			}
		})
	} else {
		
		arr.fr = fr;
		mui.post('index.php?c=saveresumeson', arr, function(data) {
			if(data.url) {
				mui.openWindow({
					url: data.url,
				});
			} else {
				layermsg(data.msg);
			}
		}, 'json');
	}
}

function saveexpect() {
	var name = $.trim(document.getElementById('name').value),
		job_classid = $.trim(document.getElementById('job_classid').value),
		hy = $.trim(document.getElementById('hy').value),
		city_classid = $.trim(document.getElementById('city_classid').value),
		type = $.trim(document.getElementById('type').value),
		report = $.trim(document.getElementById('report').value),
		minsalary = $.trim(document.getElementById('minsalary').value),
		maxsalary = $.trim(document.getElementById('maxsalary').value),
		eid = $.trim(document.getElementById('eid').value),
		jobstatus = $.trim(document.getElementById('jobstatus').value);

	if(name == '') {
		return mui.toast('请填写期望岗位！');
	}
	if(job_classid == '') {
		return mui.toast('请选择工作职能！');
	}
	if(hy == '') {
		return mui.toast('请选择从事行业！');
	}
	if(city_classid == '') {
		return mui.toast('请选择期望城市！');
	}
	if(type == '') {
		return mui.toast('请选择工作性质');
	}
	if(report == '') {
		return mui.toast('请选择到岗时间！');
	}
	if(jobstatus == '' && linkphone == '') {
		return mui.toast('请选择求职状态！');
	}	
	layer_load('执行中，请稍候...');
	mui.post('index.php?c=expect', {
		name: name,
		job_classid: job_classid,
		hy: hy,
		city_classid: city_classid,
		type: type,
		report: report,
		jobstatus: jobstatus,
		minsalary: minsalary,
		maxsalary: maxsalary,
		eid: eid,
		submit: 'submit'
	}, function(data) {
		layer.closeAll();
		if(data > 0) {
			mui.openWindow({
				url: wapurl + "member/index.php?c=resume&eid=" + eid,
			});

		}else if(data == -1) {
			return mui.toast('请将信息填写完整');
		} else {
			return mui.toast('保存失败！');
		}
	}, 'json');
}

function addresume() {
	
	var name = $.trim(document.getElementById('name').value),
		hy = $.trim(document.getElementById('hy').value),
		job_classid = $.trim(document.getElementById('job_classid').value),
		city_classid = $.trim(document.getElementById('city_classid').value),
		minsalary = $.trim(document.getElementById('minsalary').value),
		maxsalary = $.trim(document.getElementById('maxsalary').value),
		report = $.trim(document.getElementById('report').value),
		type = $.trim(document.getElementById('type').value),
		jobstatus = $.trim(document.getElementById('jobstatus').value),
		uname = $.trim(document.getElementById('uname').value),
		sex = $.trim(document.getElementById('sex').value),
		birthday = $.trim(document.getElementById('birthday').value),
		edu = $.trim(document.getElementById('edu').value),
		exp = $.trim(document.getElementById('exp').value),
		telphone = $.trim(document.getElementById('telphone').value),
		email = $.trim(document.getElementById('email').value),
		living = $.trim(document.getElementById('living').value);
	if(uname == "") {
		return mui.toast('请输入真实姓名！');
	}
	if(sex == '') {
		return mui.toast('请选择性别！');
	}
	if(birthday == '') {
		return mui.toast('请选择出生年月！');
	}
	if(edu == '') {
		return mui.toast('请选择最高学历！');
	}
	if(exp == '') {
		return mui.toast('请选择工作经验！');
	}
	if(telphone == '') {
		return mui.toast('请输入手机号码！');
	} else {
		if(!isjsMobile(telphone)) {
			return mui.toast('手机号码格式错误！');
		}
	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(email != "" && !myreg.test(email)) {
		return mui.toast('邮箱格式错误！');
	}
	if(living == '') {
		return mui.toast('请输入现居住地！');
	}
	if(name == "") {
		return mui.toast('请输入期望岗位！');
	}
	if(hy == "") {
		return mui.toast('请选择从事行业！');
	}
	if(job_classid == "") {
		return mui.toast('请选择工作职能！');
	}
	if(minsalary == "") {
		return mui.toast('请输入期望薪资！');
	}
	if(parseInt(maxsalary)>0) {
		if(parseInt(maxsalary) <= parseInt(minsalary)) {
			return mui.toast('最高薪资必须大于最低薪资！');
		}

	}
	if(city_classid == "") {
		return mui.toast('请选择期望城市！');
	}
	if(type == "") {
		return mui.toast('请选择工作性质！');
	}
	if(report == "") {
		return mui.toast('请选择到岗时间！');
	}
	if(jobstatus == "") {
		return mui.toast('请选择求职状态！');
	}
	var arr = {};

	arr = {
		name: name,
		hy: hy,
		job_classid: job_classid,
		city_classid: city_classid,
		minsalary: minsalary,
		maxsalary: maxsalary,
		report: report,
		type: type,
		jobstatus: jobstatus,
		uname: uname,
		sex: sex,
		birthday: birthday,
		edu: edu,
		exp: exp,
		email: email,
		telphone: telphone,
		living: living
	};

	if($.trim(document.getElementById('resume_exp').value) == '1'&&$("input[name='iscreateexp']:checked").val()!='2') {
		
		var workname=document.getElementsByName('workname');
		var workName = [];
		for(var i=0;i<workname.length;i++){
			if(workname[i].value == '') {
		 		mui.toast('请填写第'+ (i+1) +'份工作经历的公司名称！');return false;
			}else{
				workName.push(workname[i].value);
			}
        }
        var worktitle=document.getElementsByName('worktitle');
		var workTitle = [];
		for(var i=0;i<worktitle.length;i++){
			if(worktitle[i].value == '') {

				mui.toast('请填写第'+ (i+1) +'份工作经历的担任职位！');return false;

			}else{
				workTitle.push(worktitle[i].value);
			}
        }
        var worksdate=document.getElementsByName('workSdate');
		var workSdate = [];
		var workedate=document.getElementsByName('workEdate');
		var workEdate = [];
		var totoday=document.getElementsByName('toToday');
		var toToday = [];
		for(var i=0;i<worksdate.length;i++){
			if(worksdate[i].value == '') {
				mui.toast('请选择第'+ (i+1) +'份工作经历的入职时间！');return false;
			}else{
				workSdate.push(worksdate[i].value);
			}
			if(totoday[i].value == '' && workedate[i].value == '') {
				mui.toast('请选择第'+ (i+1) +'份工作经历的离职时间！');return false;
			}
			if(worksdate[i].value  > workedate[i].value) {
				mui.toast('第'+ (i+1) +'份工作经历的离职时间不得小于入职时间！');return false;
			}
        }
        
		for(var i=0;i<workedate.length;i++){
            workEdate.push(workedate[i].value);
        }
        
        
		for(var i=0;i<totoday.length;i++){
            toToday.push(totoday[i].value);
        }
        var workcontent=document.getElementsByName('workContent');
		var workContent = [];
		for(var i=0;i<workcontent.length;i++){
            workContent.push(workcontent[i].value);
        }
		arr.iscreateexp = $("input[name='iscreateexp']:checked").val();
		arr.workname = workName;
		arr.worksdate = workSdate;
		arr.workedate = workEdate;
		arr.worktitle = workTitle;
		arr.totoday = toToday;
		arr.workcontent = workContent;
	}
	
	if($.trim(document.getElementById('resume_edu').value) == '1'&&$("input[name='iscreateedu']:checked").val()!='2') {
		
		var eduname=document.getElementsByName('eduname');
		var eduName = [];
		for(var i=0;i<eduname.length;i++){
			if(eduname[i].value == '') {
		 		mui.toast('请填写第'+ (i+1) +'份教育经历的学校名称！');return false;
			}else{
				eduName.push(eduname[i].value);
			}
        }
        var edusdate=document.getElementsByName('eduSdate');
		var eduSdate = [];
		for(var i=0;i<edusdate.length;i++){
			if(edusdate[i].value == '') {
		 		mui.toast('请选择第'+ (i+1) +'份教育经历的入学时间！');return false;
			}else{
				eduSdate.push(edusdate[i].value);
			}
        }
        var eduedate=document.getElementsByName('eduEdate');
		var eduEdate = [];
		for(var i=0;i<eduedate.length;i++){
			if(eduedate[i].value == '') {
		 		mui.toast('请选择第'+ (i+1) +'份教育经历的离校或预计离校时间！');return false;
			}else if(eduedate[i].value < edusdate[i].value){
				mui.toast('第'+ (i+1) +'份教育经历的离校时间不得小于入学时间！');return false;
			}else{
				eduEdate.push(eduedate[i].value);
			}
        }
        var education=document.getElementsByName('education');
		var eduCation = [];
		for(var i=0;i<education.length;i++){
			if(education[i].value == '') {
		 		mui.toast('请选择第'+ (i+1) +'份教育经历的毕业学历！');return false;
			}else{
				eduCation.push(education[i].value);
			}
        }
        var eduspec=document.getElementsByName('eduspec');
		var eduSpec = [];
		for(var i=0;i<eduspec.length;i++){
			
			eduSpec.push(eduspec[i].value);
			
        }
		arr.iscreateedu = $("input[name='iscreateedu']:checked").val();
		arr.eduname = eduName;
		arr.edusdate = eduSdate;
		arr.eduedate = eduEdate;
		arr.eduspec = eduSpec;
		arr.education = eduCation;
	}
	if($.trim(document.getElementById('resume_pro').value) == '1') {
		
		var proname=document.getElementsByName('proname');
		var proName = [];
		for(var i=0;i<proname.length;i++){
			if(proname[i].value == '') {
		 		mui.toast('请填写第'+ (i+1) +'份项目经历的项目名称！');return false;
			}else{
				proName.push(proname[i].value);
			}
        }
        var protitle=document.getElementsByName('protitle');
		var proTitle = [];
		for(var i=0;i<protitle.length;i++){
			if(protitle[i].value == '') {
		 		mui.toast('请填写第'+ (i+1) +'份项目经历的项目担任职位！');return false;
			}else{
				proTitle.push(protitle[i].value);
			}
        }
        var prosdate=document.getElementsByName('proSdate');
		var proSdate = [];
		for(var i=0;i<prosdate.length;i++){
			if(prosdate[i].value == '') {
		 		mui.toast('请选择第'+ (i+1) +'份项目经历的项目开始时间！');return false;
			}else{
				proSdate.push(prosdate[i].value);
			}
        }
        var proedate=document.getElementsByName('proEdate');
		var proEdate = [];
		for(var i=0;i<proedate.length;i++){
			if(proedate[i].value == '') {
		 		mui.toast('请选择第'+ (i+1) +'份项目经历的项目结束时间！');return false;
			}else if(proedate[i].value < prosdate[i].value){
				mui.toast('第'+ (i+1) +'份项目经历的项目结束时间不得小于开始时间');return false;
			}else{
				proEdate.push(proedate[i].value);
			}
        }
        var procontent=document.getElementsByName('proContent');
		var proContent = [];
		for(var i=0;i<procontent.length;i++){
			
			proContent.push(procontent[i].value);
			
        }
		arr.proname = proName;
		arr.prosdate = proSdate;
		arr.proedate = proEdate;
		arr.protitle = proTitle;
		arr.procontent = proContent;
	}
	arr.submit = 'submit';
    layer_load('执行中，请稍候...');
	mui('.yunset_bth_box').off('tap', '#resumesubmit', addresume);
	
	mui.post('index.php?c=kresume', arr, function(data) {
		layer.closeAll();
		if(data.url) {
			layermsg(data.msg, 2, function() {
				window.location.href = data.url;
			});
		} else {
			mui('.yunset_bth_box').on('tap', '#resumesubmit', addresume);
			return mui.toast(data.msg);
		}
	}, 'json');
}
 
function topCheck(eid){
	layer_load();
	$.post(wapurl + "/member/index.php?c=topCheck", {
		eid: eid
	}, function(data) {
		layer.closeAll();
		if(data){
			var res = eval('(' + data + ')');
			if(res.msg){
				layermsg(res.msg);
				return false;
			}else{
				location.href = wapurl + '/member/index.php?c=getserver&server=1&id=' + eid;
			}
		}
	});
}
function frConfirm(integrity,fr,eid){
	if(integrity<100 && fr=='1'){
		var btnArray =['继续完善','跳过'];
        mui.confirm('您的简历还没有全部完善，是否确认跳过？', '', btnArray, function(e) {
            if(e.index==1){
                location.href = wapurl + 'member/index.php?c=rcomplete&eid='+eid;
            }
        })
	}else{
		location.href = wapurl + 'member/index.php?c=rcomplete&eid='+eid;
	}
}