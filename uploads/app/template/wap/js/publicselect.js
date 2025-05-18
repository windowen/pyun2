//mui插件select、日期，个人标签
(function($) {
	$.init();
	//邀请面试时间
	var intertimePicker = document.getElementById('intertimePicker');
	if(intertimePicker){
		var intertime = document.getElementById('intertime');
		intertimePicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				intertime.value =rs.text;
				intertimePicker.innerText =  rs.text;
				picker.dispose();
			});	
		}, false);
	}
	//出生日期
	var birthdayUserPicker = document.getElementById('birthdayUserPicker');
	if(birthdayUserPicker){
		var birthday = document.getElementById('birthday');
		birthdayUserPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				birthday.value =rs.text;
				birthdayUserPicker.innerText =  rs.text;
				picker.dispose();
			});	
		}, false);
	}
	//开始日期
	var sdatePicker = document.getElementById('sdatePicker');
	if(sdatePicker){
		var sdate = document.getElementById('sdate');
		sdatePicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				sdate.value =rs.text;
				sdatePicker.innerText =  rs.text;
				picker.dispose();
			});				
		}, false);
	}
	//结束日期
	var edatePicker = document.getElementById('edatePicker');
	if(edatePicker){
		var edate = document.getElementById('edate');
		edatePicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				edate.value =rs.text;
				edatePicker.innerText =  rs.text;
				picker.dispose();
			});				
		}, false);
	}
	//创建简历最近工作开始时间
	var worksdateComPicker = document.getElementById('worksdateComPicker');
	if(worksdateComPicker){
		var worksdate = document.getElementById('worksdate');
		worksdateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				worksdate.value =rs.text;
				worksdateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近工作结束时间
	var workedateComPicker = document.getElementById('workedateComPicker');
	if(workedateComPicker){
		var workedate = document.getElementById('workedate');
		workedateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				workedate.value =rs.text;
				workedateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近教育入学时间
	var edusdateComPicker = document.getElementById('edusdateComPicker');
	if(edusdateComPicker){
		var edusdate = document.getElementById('edusdate');
		edusdateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				edusdate.value =rs.text;
				edusdateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近教育离校时间
	var eduedateComPicker = document.getElementById('eduedateComPicker');
	if(eduedateComPicker){
		var eduedate = document.getElementById('eduedate');
		eduedateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				eduedate.value =rs.text;
				eduedateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近项目开始时间
	var prosdateComPicker = document.getElementById('prosdateComPicker');
	if(prosdateComPicker){
		var prosdate = document.getElementById('prosdate');
		prosdateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				prosdate.value =rs.text;
				prosdateComPicker.innerText =  rs.text;
				picker.dispose();
			});		
		}, false);
	}
	//创建简历最近项目结束时间	
	var proedateComPicker = document.getElementById('proedateComPicker');
	if(proedateComPicker){
		var proedate = document.getElementById('proedate');
		proedateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				proedate.value =rs.text;
				proedateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
})(mui)
//邀请职位选择职位
var interjobPickerButton = document.getElementById('interjobPicker');
if(typeof interjobData != "undefined" && interjobPickerButton){
	var interjobPicker = new mui.PopPicker();
	interjobPicker.setData(interjobData);
	var jobname = document.getElementById('jobname');
	var linkman = document.getElementById('linkman');
	var linktel = document.getElementById('linktel');
	var address = document.getElementById('address');
	interjobPickerButton.addEventListener('tap', function(event) {
		document.activeElement.blur();
		interjobPicker.show(function(items) {
			jobname.value = items[0].value;
			
			if(items[0].link_man && items[0].link_moblie){
				linkman.value = items[0].link_man;
				linktel.value = items[0].link_moblie;
				address.value = items[0].address;
			}
			
  			interjobPickerButton.innerText = items[0].text;
		});
	}, false);
}
//邀请模板选择
var interYqmbPickerButton = document.getElementById('interYqmbPicker');
if(typeof yqmbData != "undefined" && interYqmbPickerButton){
	var interYqmbPicker = new mui.PopPicker();
	interYqmbPicker.setData(yqmbData);
	var linkman = document.getElementById('linkman');
	var linktel = document.getElementById('linktel');
	var address = document.getElementById('address');
	var content = document.getElementById('content');
	var ymid = document.getElementById('ymid');
	var ymctrl = document.getElementById('ymctrl');
	var ymtext = document.getElementById('ymtext');
	var intertime = document.getElementById('intertime');
	interYqmbPickerButton.addEventListener('tap', function(event) {
		document.activeElement.blur();

		interYqmbPicker.show(function(items) {
			ymctrl.style.display = 'block';
			document.getElementById('yqmb_switch').classList.remove('mui-active');
			document.getElementById('save_yqmb').value = 0;
			ymtext.innerText = '更新面试模板';
			linkman.value = items[0].link_man;
			linktel.value = items[0].link_moblie;
			address.value = items[0].address;
			content.value = items[0].content;
			intertime.value = items[0].intertime;
			document.getElementById('intertimePicker').innerText = items[0].intertime;
  			interYqmbPickerButton.innerText = items[0].text;
  			ymid.value = items[0].value;
		});
	}, false);
}
//教育经历里的最高学历
var educationUserPickerBtn = document.getElementById('educationUserPicker');
if(typeof eduData != "undefined" && educationUserPickerBtn){
	var educationuserPicker = new mui.PopPicker();
	educationuserPicker.setData(eduData);
	var education = document.getElementById('education'),
		deducation = educationUserPickerBtn.getAttribute('data-education');
	if(deducation) {
		educationuserPicker.pickers[0].setSelectedValue(deducation);
	}
	educationUserPickerBtn.addEventListener('tap', function(event) {
		document.activeElement.blur();
		educationuserPicker.show(function(items) {
			education.value = items[0].value;
			educationUserPickerBtn.innerText = items[0].text;
		});
	}, false);
}
//创建简历，可进行多个学历选择
if(typeof eduData != "undefined"){
	var educationuserPicker = new mui.PopPicker();
	educationuserPicker.setData(eduData);
	mui('#redu').on('tap','.reedupick',function(){
		var rand=this.getAttribute('data-rand');
		var type=this.getAttribute('data-type');
		var redutypedate = document.getElementById(type+rand);
		var that =this;
		
		educationuserPicker.pickers[0].setSelectedValue(redutypedate.value ? redutypedate.value : eduData[0].value);
		
		document.activeElement.blur();
		educationuserPicker.show(function(items) {
			redutypedate.value = items[0].value;
			that.innerText = items[0].text;
		});
						
	})
}
//熟练程度
var ingUserPickerBtn = document.getElementById('ingUserPicker');
if(typeof ingData != "undefined" && ingUserPickerBtn){
	var inguserPicker = new mui.PopPicker();
	inguserPicker.setData(ingData);
	var ing = document.getElementById('ing'),
		ding = ingUserPickerBtn.getAttribute('data-ing');
	if(ding) {
		inguserPicker.pickers[0].setSelectedValue(ding);
	}
	ingUserPickerBtn.addEventListener('tap', function(event) {
		document.activeElement.blur();
		inguserPicker.show(function(items) {
			ing.value = items[0].value;
			ingUserPickerBtn.innerText = items[0].text;
		});
	}, false);
}
//自我评价标签
var addtagbox = $('.addtagbox')[0];
if(addtagbox){
	addtagbox.addEventListener('tap', function(event) {//添加
		var addfuli = document.getElementById('addfuli').value;
		var error=0;
		var num=0;
		if(addfuli.length>=2 && addfuli.length<=8){
			//判断信息是否已经存在 
			var num = 0; 
			var tag_value; 
			$(".yun_my_introduce_bq_cur").each(function(){
				if($(this).attr('tag-class')=='2'){ 
					 var info =$(this).attr("data-tag"); 
					 if(addfuli == info){
						error = 1;
						return mui.toast('相同福利已存在，请选择或重新填写！');false;
					}
					tag_value+=","+info; 
					num++; 
				 } 
			}); 
			if(num>4){
				document.getElementById('addfuli').value='';
				error = 1;
				return mui.toast('最多只能选择五项！');
			}
			if(error==0){
				$('#newtag').append('<span class="yun_my_introduce_bq  yun_my_introduce_bq_cur" data-tag="'+addfuli+'" tag-class="2">'+addfuli+'+</span>'); 
				tag_value+=","+addfuli; 
				tag_value = tag_value.replace("undefined,","");
				$("#tag").val(tag_value); 
				document.getElementById('addfuli').value='';
			}
			
		}else{
			return mui.toast('请输入2-8个标签字符！');
		}
	}, false);
}
//更多设置
if(document.getElementById("moreset")){
	document.getElementById("moreset").addEventListener('tap', function(e) {
		if(document.getElementById("bg").style.display=='none'){
			setTimeout(function(){
				document.getElementById("bg").style.display='block';
			document.getElementById("bgset").style.display='block';
			},350);
		}
	});
	if(document.getElementById("bg")){
		$("#bgset").on('click',function(){
			document.getElementById("bg").style.display='none';
			document.getElementById("bgset").style.display='none';
		})
		$("#bg").on('click',function(){
			document.getElementById("bg").style.display='none';
			document.getElementById("bgset").style.display='none';
		})
	}
	if(document.getElementById("resume_del")){
		$("#resume_del").on('click',function(){
			document.getElementById("bg").style.display='none';
			document.getElementById("bgset").style.display='none';
		})
	}
}
if(document.getElementById("showmoreset")){
	document.getElementById("showmoreset").addEventListener('tap', function(e) {
		if(document.getElementById("morelist").style.display=='none'){
			setTimeout(function(){
				document.getElementById("morelist").style.display='block';
			},350);
		}else{
			setTimeout(function(){
				document.getElementById("morelist").style.display='none';
			},350);
		}
	});
}
//简历状态切换
if(document.getElementById("privacy")){
	document.getElementById("privacy").addEventListener('toggle', function(e) {
		evalue=e.detail.isActive?1:2;
		$.get(wapurl+"member/index.php?c=up&status="+evalue,function(data){});
		document.getElementById("showprivacy").innerText=e.detail.isActive?'简历公开':'简历保密';
	});
}
//简历刷新
if(document.getElementById("refresh")){
	document.getElementById("refresh").addEventListener('tap', function(e) {
		layer_load('执行中，请稍候...');
		$.get(wapurl+"member/index.php?c=resumeset&update="+e.target.dataset.id,function(data){
			layer.closeAll();
			layermsg(data ? '刷新成功！' : '刷新失败！', 2, function() {
                        mui.openWindow({
                            url:wapurl+"member/index.php?c=resume&eid="+e.target.dataset.id,
                        });
                    });
		});
	});
}
//简历默认
if(document.getElementById("resumedefaults")){
	document.getElementById("resumedefaults").addEventListener('tap', function(e) {
		layer_load('执行中，请稍候...'); 
		$.get(wapurl+"member/index.php?c=resumeset&def="+e.target.dataset.id,function(data){
			layer.closeAll(); 
			if(data){
				mui.openWindow({
					url:wapurl+"member/index.php?c=resume&eid="+e.target.dataset.id,
				});
			}else{
				return mui.toast('设置失败！');
			}
		});
	});
}

//切换简历
var resumeUserPickerButton = document.getElementById('resumeUserPicker');
if(typeof resumeData != "undefined" && resumeUserPickerButton){
	var resumeuserPicker = new mui.PopPicker();
	resumeuserPicker.setData(resumeData);
	var resume = document.getElementById('resume'),
		dresume = resumeUserPickerButton.getAttribute('data-resume');
	if(dresume) {
		resumeuserPicker.pickers[0].setSelectedValue(dresume);
	}
	resumeUserPickerButton.addEventListener('tap', function(event) {
		resumeuserPicker.show(function(items) {
			mui.openWindow({
				url:wapurl+"member/index.php?c=resume&eid="+items[0].value,
			});
		});
	}, false);
}
