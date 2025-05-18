//职位多选---------------------------------------------------------------------------------------------------------------开始----------------------
var jobchoose = document.getElementById("jobchoose");
var jobone = document.getElementById("jobone");
var jobtwo = document.getElementById("jobtwo");
var jobthree = document.getElementById("jobthree");
var jobnum = document.getElementById("jobnum");
var jcnum	= jobnum && parseInt(jobnum.value)>0 ? parseInt(jobnum.value) : 5;
var jobhtml = '';
var jobhtmltwo = '';
var jobhtmlthree = '';
if(typeof jobclass == "undefined") {
	var jobclass = '';
}
if(typeof jobclassname == "undefined") {
	var jobclassname = '';
}

//点击一级类别
mui("#jobone").on('tap', 'li', function() {
	$(".yun_category_on").removeClass('yun_category_on');
	this.classList.add('yun_category_on');
	var jobid = this.getAttribute('data-id');
	$(".jobtwo").addClass('none');
	$(".job" + jobid).removeClass('none');
	$(".jobthree").addClass('none');
	var twostyle = $("#jobtwo").attr("style");
	if(!twostyle){
		$("#jobtwo").css("left", "30.48%");
	}
	$("#jobthree").removeAttr("style");
});
//点击二级类别
mui("#jobtwo").on('tap', 'li', function() {
	$(".yun_category_ons").removeClass('yun_category_ons');
	this.classList.add('yun_category_ons');
	var jobid = this.getAttribute('data-id');
	$(".jobthree").addClass('none');
	var threeobj = $(".job" + jobid);
	// 如该二级下没有三级,则第三列不显示
	if(threeobj.length > 0){
		$(".job" + jobid).removeClass('none');
		$("#jobthree").css("left", "56.96%");
	}
});
//删除已选类别
mui("#jobchoosed").on('tap', 'a', function() {
	var id = this.getAttribute('data-id');
	var choosetwo = document.getElementById('jobcheckAll' + id);

	if(document.getElementById('jobclass'+id)){//搜索项存在的设为未选中状态
		document.getElementById('jobclass'+id).checked = false;
	}
	
	if(choosetwo) {
		choosetwo.checked = false;
		var listBox = mui('.jobcheck' + id);
		listBox.each(function() {
			var ele = this;
			ele.checked = false;
			ele.disabled = false;

			if(document.getElementById('jobclass'+ele.value)){//搜索项的三级设为未选中 可选中状态
				document.getElementById('jobclass'+ele.value).checked = false;
				document.getElementById('jobclass'+ele.value).disabled = false;
			}
		});
	} else {
		if(document.getElementById('jobtwo' + id)){
			document.getElementById('jobtwo' + id).checked = false;
		} else if(document.getElementById('jobthree' + id)){
			document.getElementById('jobthree' + id).checked = false;
		}else if(document.getElementById('jobone' + id)){
			document.getElementById('jobone' + id).checked = false;
		}
	}
	document.getElementById("jobchoosed").removeChild(this);
	//处理jobclass和jobclassname，减少内容
	var list = arrsplice(jobclass, id);
	var jobnamelist = [];
	for(var i = 0; i < list.length; i++) {
		jobnamelist.push(jn[list[i]]);
	}
	if(list.length > 0) {
		document.getElementById('jobpencent').classList.remove('none');
		document.getElementById('jobpencent').innerHTML = list.length + '/'+jcnum;
	} else {
		document.getElementById('jobpencent').classList.add('none');
		document.getElementById('jobpencent').innerHTML = '';
	}
	jobclass = list.join(',');
	jobclassname = jobnamelist.join('+');
	document.getElementById("job_classid").value = jobclass;
	document.getElementById("jobnameshow").innerHTML = jobclassname;
});
(function(m) {

	$('#jobthree .checkAll').each(function(i, jobtwo) {
		//根据获取到的已选数据，处理类别选中
		if(typeof jobclassidData != "undefined") {
			$.each(jobclassidData, function(index, vaule, arr) {
				if(jobtwo.value == vaule.value) {
					jobtwo.checked = true;
					m('.jobcheck' + jobtwo.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		//选中三级全部处理
		document.getElementById(jobtwo.id).addEventListener('change', function() {
			var jobtwolist = jobclass.split(',');
			var list = [];
			for(var job in jobtwolist) {
				if(jobtwolist[job])
					list.push(jobtwolist[job]);
			}
			if(jcnum>1){
				if(list.length >= jcnum && this.checked == true) {
					this.checked = false;
					if(document.getElementById("jobclass"+this.value)){
						//搜索项设为为选中
						document.getElementById("jobclass"+this.value).checked = false;
					}
					return mui.toast("最多只能选择"+jcnum+"个类别哦");
				}
			}
			
			var listBox = m('.jobcheck' + this.value);
			
			if(this.checked) {
				if(jcnum==1){//单选

					singleDeal(list);
					jobclass = [];
					jobclassname = '';
				}
				//选中处理下方已选显示
				var checked = [],
					newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + jn[this.value] + '</a>';
				$("#jobchoosed").prepend(newchoosed);
				//选中全部则该类下所有三级都设为已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					if(ele.checked == true) {
						checked.push(ele.value);
					}
					ele.checked = true;
					ele.disabled = true;

					if(document.getElementById('jobclass'+ele.value)){//搜索项的三级设为已选中 不可选中状态
						document.getElementById('jobclass'+ele.value).checked = true;
						document.getElementById('jobclass'+ele.value).disabled = true;
					}
				})
				if(checked.length > 0) {
					var jobarr = jobclass.split(','),
						newjobarr = [];
					for(var i = 0; i < jobarr.length; i++) {
						var flag = true;
						for(var j = 0; j < checked.length; j++) {
							if(jobarr[i] == checked[j]) {
								flag = false;
								m("#jobchoosed a").each(function() {
									var id = this.getAttribute('data-id');
									if(id == checked[j]) {
										document.getElementById("jobchoosed").removeChild(this);
									}
								})
							}
						}
						if(flag) {
							newjobarr.push(jobarr[i]);
						}
					}
					var jobnamelist = [];
					for(var i = 0; i < newjobarr.length; i++) {
						jobnamelist.push(jn[newjobarr[i]]);
					}
					jobclass = newjobarr.join(',');
					jobclassname = jobnamelist.join('+');
				}
				//处理jobclass和jobclassname，增加内容
				if(jobclass != '' || jobclassname != '') {
					jobclass += ',' + this.value;
					jobclassname += ' ' + jn[this.value];
				} else {
					jobclass += this.value;
					jobclassname += jn[this.value];
				}
				var listlength = jobclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#jobchoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("jobchoosed").removeChild(elechoose);
					}
				});
				//取消该类下所有三级的已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					ele.checked = false;
					ele.disabled = false;

					if(document.getElementById('jobclass'+ele.value)){//搜索项的三级设为未选中 可选中状态
						document.getElementById('jobclass'+ele.value).checked = false;
						document.getElementById('jobclass'+ele.value).disabled = false;
					}
				})
				//处理jobclass和jobclassname，减少内容
				var list = arrsplice(jobclass, this.value);
				var jobnamelist = [];
				for(var i = 0; i < list.length; i++) {
					jobnamelist.push(jn[list[i]]);
				}
				jobclass = list.join(',');
				jobclassname = jobnamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('jobpencent').classList.remove('none');
				document.getElementById('jobpencent').innerHTML = listlength + '/'+jcnum;
			}else{
				document.getElementById('jobpencent').classList.add('none');
				document.getElementById('jobpencent').innerHTML ='';
			}
			document.getElementById("job_classid").value = jobclass;
			document.getElementById("jobnameshow").innerHTML = jobclassname;
		})
	})
	//选中单个三级处理
	$('#jobthree .jobthree div .jobthreebox').each(function(j, jobthree) {
		//根据获取到的已选数据，处理类别选中
		if(typeof jobclassidData != "undefined") {
			$.each(jobclassidData, function(index, vaule, arr) {
				if(jobthree.value == vaule.value) {
					jobthree.checked = true;
					m('.jobcheck' + jobthree.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		document.getElementById(jobthree.id).addEventListener('change', function() {
			var jobtwolist = jobclass.split(',');

			var list = [];
			for(var job in jobtwolist) {
				if(jobtwolist[job])
					list.push(jobtwolist[job]);
			}
			if(jcnum>1){
			
				if(list.length >= jcnum && this.checked == true) {
					this.checked = false;
					if(document.getElementById("jobclass"+this.value)){
						//搜索项设为为选中
						document.getElementById("jobclass"+this.value).checked = false;
					}
					return mui.toast("最多只能选择"+jcnum+"个类别哦");
				}
			}
			
			if(this.checked == true) {

				if(jcnum==1){//单选

					singleDeal(list);
					jobclass = [];
					jobclassname = '';
				}

				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + jn[this.value] + '</a>';
				$("#jobchoosed").prepend(newchoosed);
				//处理jobclass和jobclassname，增加内容
				if(jobclass != '' || jobclassname != '') {
					jobclass += ',' + this.value;
					jobclassname += ' ' + jn[this.value];
				} else {
					jobclass += this.value;
					jobclassname += jn[this.value];
				}
				var listlength = jobclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#jobchoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("jobchoosed").removeChild(elechoose);
					}
				});
				//处理jobclass和jobclassname，减少内容
				var list = arrsplice(jobclass, this.value);
				var jobnamelist = [];
				for(var i = 0; i < list.length; i++) {
					jobnamelist.push(jn[list[i]]);
				}
				jobclass = list.join(',');
				jobclassname = jobnamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('jobpencent').classList.remove('none');
				document.getElementById('jobpencent').innerHTML = listlength + '/'+jcnum;
			}else{
				document.getElementById('jobpencent').classList.add('none');
				document.getElementById('jobpencent').innerHTML ='';
			}
			document.getElementById("job_classid").value = jobclass;
			document.getElementById("jobnameshow").innerHTML = jobclassname;
		})
	})
	//选中单个没有子集的二级处理
	
	$('#jobtwo .jobtwo div .jobtwobox').each(function(j, jobtwo) {
		//根据获取到的已选数据，处理类别选中

		if(typeof jobclassidData != "undefined") {
			$.each(jobclassidData, function(index, vaule, arr) {
				if(jobtwo.value == vaule.value) {
					jobtwo.checked = true;
					m('.jobcheck' + jobtwo.value).each(function() {
						var le = this;
						le.checked = true;
					})
				}
			})
		}
		document.getElementById(jobtwo.id).addEventListener('change', function() {
			var jobtwolist = jobclass.split(',');
			var list = [];
			for(var job in jobtwolist) {
				if(jobtwolist[job])
					list.push(jobtwolist[job]);
			}
			if(jcnum>1){
				if(list.length >= jcnum && this.checked == true) {
					this.checked = false;
					if(document.getElementById("jobclass"+this.value)){
						//搜索项设为为选中
						document.getElementById("jobclass"+this.value).checked = false;
					}
					return mui.toast("最多只能选择"+jcnum+"个类别哦");
				}
			}
			if(this.checked == true) {
				if(jcnum==1){//单选

					singleDeal(list);
					jobclass = [];
					jobclassname = '';
				}
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + jn[this.value] + '</a>';
				$("#jobchoosed").prepend(newchoosed);
				//处理jobclass和jobclassname，增加内容
				if(jobclass != '' || jobclassname != '') {
					jobclass += ',' + this.value;
					jobclassname += ' ' + jn[this.value];
				} else {
					jobclass += this.value;
					jobclassname += jn[this.value];
				}
				var listlength = jobclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#jobchoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("jobchoosed").removeChild(elechoose);
					}
				});
				//处理jobclass和jobclassname，减少内容
				var list = arrsplice(jobclass, this.value);
				var jobnamelist = [];
				for(var i = 0; i < list.length; i++) {
					jobnamelist.push(cn[list[i]]);
				}
				jobclass = list.join(',');
				jobclassname = jobnamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('jobpencent').classList.remove('none');
				document.getElementById('jobpencent').innerHTML = listlength + '/'+jcnum;
			} else {
				document.getElementById('jobpencent').classList.add('none');
				document.getElementById('jobpencent').innerHTML = '';
			}
			document.getElementById("job_classid").value = jobclass;
			document.getElementById("jobnameshow").innerHTML = jobclassname;
		})
	});
	//当只有一级时，选中单个一级
	$('#jobone .jobone div .jobonebox').each(function(j, jobone) {
		//根据获取到的已选数据，处理类别选中
		if(typeof jobclassidData != "undefined") {
			$.each(jobclassidData, function(index, vaule, arr) {
				if(jobone.value == vaule.value) {
					jobone.checked = true;
					m('.jobcheck' + jobone.value).each(function() {
						var le = this;
						le.checked = true;
					})
				}
			})
		}
		document.getElementById(jobone.id).addEventListener('change', function() {
			var jobonelist = jobclass.split(',');
			var list = [];

			
			for(var job in jobonelist) {
				
				if(jobonelist[job])
					list.push(jobonelist[job]);
			}
			if(jcnum>1){
				if(list.length >= jcnum && this.checked == true) {
					this.checked = false;
					if(document.getElementById("jobclass"+this.value)){
						//搜索项设为为选中
						document.getElementById("jobclass"+this.value).checked = false;
					}
					return mui.toast("最多只能选择"+jcnum+"个类别哦");
				}
			}
			if(this.checked == true) {
				if(jcnum==1){//单选

					singleDeal(list);
					jobclass = [];
					jobclassname = '';
				}
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + jn[this.value] + '</a>';
				$("#jobchoosed").prepend(newchoosed);
				//处理jobclass和jobclassname，增加内容
				if(jobclass != '' || jobclassname != '') {
					jobclass += ',' + this.value;
					jobclassname += ' ' + jn[this.value];
				} else {
					jobclass += this.value;
					jobclassname += jn[this.value];
				}
				var listlength = jobclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#jobchoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("jobchoosed").removeChild(elechoose);
					}
				});
				//处理jobclass和jobclassname，减少内容
				var list = arrsplice(jobclass, this.value);
				var jobnamelist = [];
				for(var i = 0; i < list.length; i++) {
					jobnamelist.push(cn[list[i]]);
				}
				jobclass = list.join(',');
				jobclassname = jobnamelist.join('+');
				var listlength = list.length;
			}
			
			if(listlength > 0) {
				document.getElementById('jobpencent').classList.remove('none');
				document.getElementById('jobpencent').innerHTML = listlength + '/'+jcnum;
			} else {
				document.getElementById('jobpencent').classList.add('none');
				document.getElementById('jobpencent').innerHTML = '';
			}
			document.getElementById("job_classid").value = jobclass;
			document.getElementById("jobnameshow").innerHTML = jobclassname;
		})
	});
    var myDiv = document.getElementById("jobclass_search");
    if(myDiv){
    	document.addEventListener("click",function(){
	    	myDiv.style.display="none";
	    });
	    myDiv.addEventListener("click",function(event){
	        event=event||window.event;
	        event.stopPropagation();
	    });
    }
    

})(mui);
//职位多选---------------------------------------------------------------------------------------------------------------结束----------------------

//城市多选---------------------------------------------------------------------------------------------------------------开始----------------------
var citychoose = document.getElementById("citychoose");
var cityone = document.getElementById("cityone");
var citytwo = document.getElementById("citytwo");
var citythree = document.getElementById("citythree");
var cityhtml = '';
var cityhtmltwo = '';
var cityhtmlthree = '';
if(typeof cityclass == "undefined") {
	var cityclass = '';
}
if(typeof cityclassname == "undefined") {
	var cityclassname = '';
}
//点击一级类别
mui("#cityone").on('tap', 'li', function() {
	$(".yun_category_on").removeClass('yun_category_on');
	this.classList.add('yun_category_on');
	var cityid = this.getAttribute('data-id');
	$(".citytwo").addClass('none');
	$(".city" + cityid).removeClass('none');
	$(".citythree").addClass('none');
	var twostyle = $("#citytwo").attr("style");
	if(!twostyle){
		$("#citytwo").css("left", "30.48%");
	}
	$("#citythree").removeAttr("style");
});
//点击二级类别
mui("#citytwo").on('tap', 'li', function() {
	$(".yun_category_ons").removeClass('yun_category_ons');
	this.classList.add('yun_category_ons');
	var cityid = this.getAttribute('data-id');
	$(".citythree").addClass('none');
	var threeobj = $(".city" + cityid);
	// 如该二级下没有三级,则第三列不显示
	if(threeobj.length > 0){
		$(".city" + cityid).removeClass('none');
		$("#citythree").css("left", "56.96%");
	}
});
//删除已选类别
mui("#citychoosed").on('tap', 'a', function() {
	var id = this.getAttribute('data-id');
	var choosetwo = document.getElementById('citycheckAll' + id);

	if(document.getElementById('cityclass'+id)){//搜索项存在的设为未选中状态
		document.getElementById('cityclass'+id).checked = false;
	}

	if(choosetwo) {
		choosetwo.checked = false;
		var listBox = mui('.citycheck' + id);
		listBox.each(function() {
			var ele = this;
			ele.checked = false;
			ele.disabled = false;

			if(document.getElementById('cityclass'+ele.value)){//搜索项的三级设为未选中 可选中状态
				document.getElementById('cityclass'+ele.value).checked = false;
				document.getElementById('cityclass'+ele.value).disabled = false;
			}
		});
	} else {
		if(document.getElementById('citytwo' + id)){
			document.getElementById('citytwo' + id).checked = false;
		} else if(document.getElementById('citythree' + id)){
			document.getElementById('citythree' + id).checked = false;
		}else if(document.getElementById('cityone' + id)){
			document.getElementById('cityone' + id).checked = false;
		}
		id=Number(id);
		if(ci && ct && city_parent &&ci.indexOf(id)!=-1){
			var searhtml = $("#cityclass_searhtml input[name=\"cityclass_s\"]");
			
			if(searhtml ){
				
				var sval	=	0;
				searhtml.each(function(key,item){
					sval	=	$(item).val()!='' ? $(item).val() : 0;
					if(city_parent[sval] && (ct[id].indexOf(city_parent[sval])!=-1 || ct[id].indexOf(city_parent[sval].toString())!=-1 || id==city_parent[sval])){
						$(item).prop({"checked":false,"disabled":false});
					} 
				})
			}
		}
		
	}
	document.getElementById("citychoosed").removeChild(this);
	//处理cityclass和cityclassname，减少内容
	var list = arrsplice(cityclass, id);
	var citynamelist = [];
	for(var i = 0; i < list.length; i++) {
		citynamelist.push(cn[list[i]]);
	}
	if(list.length > 0) {
		document.getElementById('citypencent').classList.remove('none');
		document.getElementById('citypencent').innerHTML = list.length + '/5';
	} else {
		document.getElementById('citypencent').classList.add('none');
		document.getElementById('citypencent').innerHTML = '';
	}
	cityclass = list.join(',');
	cityclassname = citynamelist.join('+');
	document.getElementById("city_classid").value = cityclass;
	document.getElementById("citynameshow").innerHTML = cityclassname;
});
(function(m) {
	$('#citythree .checkAll').each(function(i, citytwo) {
		//根据获取到的已选数据，处理类别选中
		if(typeof cityclassidData != "undefined") {
			$.each(cityclassidData, function(index, vaule, arr) {
				if(citytwo.value == vaule.value) {
					citytwo.checked = true;
					m('.citycheck' + citytwo.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		//选中三级全部处理
		document.getElementById(citytwo.id).addEventListener('change', function() {
			var citytwolist = cityclass.split(',');
			var list = [];
			for(var city in citytwolist) {
				if(citytwolist[city])
					list.push(citytwolist[city]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				if(document.getElementById("cityclass"+this.value)){
					//搜索项设为为选中
					document.getElementById("cityclass"+this.value).checked = false;
				}
				return mui.toast("最多只能选择5个类别哦");
			}
			var listBox = mui('.citycheck' + this.value);
			if(this.checked) {
				//选中处理下方已选显示
				var checked = [],
					newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + cn[this.value] + '</a>';
				$("#citychoosed").prepend(newchoosed);
				//选中全部则该类下所有三级都设为已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					if(ele.checked == true) {
						checked.push(ele.value);
					}
					ele.checked = true;
					ele.disabled = true;

					if(document.getElementById('cityclass'+ele.value)){//搜索项的三级设为已选中 不可选中状态
						document.getElementById('cityclass'+ele.value).checked = true;
						document.getElementById('cityclass'+ele.value).disabled = true;
					}
				})
				if(checked.length > 0) {
					var cityarr = cityclass.split(','),
						newcityarr = [];
					for(var i = 0; i < cityarr.length; i++) {
						var flag = true;
						for(var j = 0; j < checked.length; j++) {
							if(cityarr[i] == checked[j]) {
								flag = false;
								m("#citychoosed a").each(function() {
									var id = this.getAttribute('data-id');
									if(id == checked[j]) {
										document.getElementById("citychoosed").removeChild(this);
									}
								})
							}
						}
						if(flag) {
							newcityarr.push(cityarr[i]);
						}
					}
					var citynamelist = [];
					for(var i = 0; i < newcityarr.length; i++) {
						citynamelist.push(cn[newcityarr[i]]);
					}
					cityclass = newcityarr.join(',');
					cityclassname = citynamelist.join('+');
				}
				//处理cityclass和cityclassname，增加内容
				if(cityclass != '' || cityclassname != '') {
					cityclass += ',' + this.value;
					cityclassname += ' ' + cn[this.value];
				} else {
					cityclass += this.value;
					cityclassname += cn[this.value];
				}
				var listlength = cityclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#citychoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("citychoosed").removeChild(elechoose);
					}
				});
				//取消该类下所有三级的已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					ele.checked = false;
					ele.disabled = false;

					if(document.getElementById('cityclass'+ele.value)){//搜索项的三级设为未选中 可选中状态
						document.getElementById('cityclass'+ele.value).checked = false;
						document.getElementById('cityclass'+ele.value).disabled = false;
					}
				})
				//处理cityclass和cityclassname，减少内容
				var list = arrsplice(cityclass, this.value);
				var citynamelist = [];
				for(var i = 0; i < list.length; i++) {
					citynamelist.push(cn[list[i]]);
				}
				cityclass = list.join(',');
				cityclassname = citynamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('citypencent').classList.remove('none');
				document.getElementById('citypencent').innerHTML = listlength + '/5';
			} else {
				document.getElementById('citypencent').classList.add('none');
				document.getElementById('citypencent').innerHTML = '';
			}
			document.getElementById("city_classid").value = cityclass;
			document.getElementById("citynameshow").innerHTML = cityclassname;
		})
	})
	//选中单个三级处理
	$('#citythree .citythree div .citythreebox').each(function(j, citythree) {
		//根据获取到的已选数据，处理类别选中
		if(typeof cityclassidData != "undefined") {
			$.each(cityclassidData, function(index, vaule, arr) {
				if(citythree.value == vaule.value) {
					citythree.checked = true;
					m('.citycheck' + citythree.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		document.getElementById(citythree.id).addEventListener('change', function() {
			var citytwolist = cityclass.split(',');
			var list = [];
			for(var city in citytwolist) {
				if(citytwolist[city])
					list.push(citytwolist[city]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				if(document.getElementById("cityclass"+this.value)){
					//搜索项设为为选中
					document.getElementById("cityclass"+this.value).checked = false;
				}
				return mui.toast("最多只能选择5个类别哦");
			}
			if(this.checked == true) {
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + cn[this.value] + '</a>';
				$("#citychoosed").prepend(newchoosed);
				//处理cityclass和cityclassname，增加内容
				if(cityclass != '' || cityclassname != '') {
					cityclass += ',' + this.value;
					cityclassname += ' ' + cn[this.value];
				} else {
					cityclass += this.value;
					cityclassname += cn[this.value];
				}
				var listlength = cityclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#citychoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("citychoosed").removeChild(elechoose);
					}
				});
				//处理cityclass和cityclassname，减少内容
				var list = arrsplice(cityclass, this.value);
				var citynamelist = [];
				for(var i = 0; i < list.length; i++) {
					citynamelist.push(cn[list[i]]);
				}
				cityclass = list.join(',');
				cityclassname = citynamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('citypencent').classList.remove('none');
				document.getElementById('citypencent').innerHTML = listlength + '/5';
			} else {
				document.getElementById('citypencent').classList.add('none');
				document.getElementById('citypencent').innerHTML = '';
			}
			document.getElementById("city_classid").value = cityclass;
			document.getElementById("citynameshow").innerHTML = cityclassname;
		})
	});
	//选中单个没有子集的二级处理
	$('#citytwo .citytwo div .citytwobox').each(function(j, citytwo) {
		//根据获取到的已选数据，处理类别选中
		if(typeof cityclassidData != "undefined") {
			$.each(cityclassidData, function(index, vaule, arr) {
				if(citytwo.value == vaule.value) {
					citytwo.checked = true;
					m('.citycheck' + citytwo.value).each(function() {
						var le = this;
						le.checked = true;
					})
				}
			})
		}
		document.getElementById(citytwo.id).addEventListener('change', function() {
			var citytwolist = cityclass.split(',');
			var list = [];
			for(var city in citytwolist) {
				if(citytwolist[city])
					list.push(citytwolist[city]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				if(document.getElementById("cityclass"+this.value)){
					//搜索项设为为选中
					document.getElementById("cityclass"+this.value).checked = false;
				}
				return mui.toast("最多只能选择5个类别哦");
			}
			if(this.checked == true) {
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + cn[this.value] + '</a>';
				$("#citychoosed").prepend(newchoosed);
				//处理cityclass和cityclassname，增加内容
				if(cityclass != '' || cityclassname != '') {
					cityclass += ',' + this.value;
					cityclassname += ' ' + cn[this.value];
				} else {
					cityclass += this.value;
					cityclassname += cn[this.value];
				}
				var listlength = cityclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#citychoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("citychoosed").removeChild(elechoose);
					}
				});
				//处理cityclass和cityclassname，减少内容
				var list = arrsplice(cityclass, this.value);
				var citynamelist = [];
				for(var i = 0; i < list.length; i++) {
					citynamelist.push(cn[list[i]]);
				}
				cityclass = list.join(',');
				cityclassname = citynamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('citypencent').classList.remove('none');
				document.getElementById('citypencent').innerHTML = listlength + '/5';
			} else {
				document.getElementById('citypencent').classList.add('none');
				document.getElementById('citypencent').innerHTML = '';
			}
			document.getElementById("city_classid").value = cityclass;
			document.getElementById("citynameshow").innerHTML = cityclassname;
		})
	});
	
	//当只有一级时，选中单个一级
	$('#cityone .cityone div .cityonebox').each(function(j, cityone) {
		
		//根据获取到的已选数据，处理类别选中
		if(typeof cityclassidData != "undefined") {
			$.each(cityclassidData, function(index, vaule, arr) {
				if(cityone.value == vaule.value) {
					cityone.checked = true;
					m('.citycheck' + cityone.value).each(function() {
						var le = this;
						le.checked = true;
					})
				}
			})
		}

		document.getElementById(cityone.id).addEventListener('change', function() {
			
			var cityonelist = cityclass.split(',');
			var list = [];
			for(var city in cityonelist) {
				if(cityonelist[city])
					list.push(cityonelist[city]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				if(document.getElementById("cityclass"+this.value)){
					//搜索项设为为选中
					document.getElementById("cityclass"+this.value).checked = false;
				}
				return mui.toast("最多只能选择5个类别哦");
			}
			if(this.checked == true) {
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + cn[this.value] + '</a>';
				$("#citychoosed").prepend(newchoosed);
				//处理cityclass和cityclassname，增加内容
				if(cityclass != '' || cityclassname != '') {
					cityclass += ',' + this.value;
					cityclassname += ' ' + cn[this.value];
				} else {
					cityclass += this.value;
					cityclassname += cn[this.value];
				}
				var listlength = cityclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#citychoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("citychoosed").removeChild(elechoose);
					}
				});
				//处理cityclass和cityclassname，减少内容
				var list = arrsplice(cityclass, this.value);
				var citynamelist = [];
				for(var i = 0; i < list.length; i++) {
					citynamelist.push(cn[list[i]]);
				}
				cityclass = list.join(',');
				cityclassname = citynamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('citypencent').classList.remove('none');
				document.getElementById('citypencent').innerHTML = listlength + '/5';
			} else {
				document.getElementById('citypencent').classList.add('none');
				document.getElementById('citypencent').innerHTML = '';
			}
			document.getElementById("city_classid").value = cityclass;
			document.getElementById("citynameshow").innerHTML = cityclassname;
		})
	});
	var myDiv = document.getElementById("cityclass_search");
	if(myDiv){
		document.addEventListener("click",function(){
	    	myDiv.style.display="none";
	    });
	    myDiv.addEventListener("click",function(event){
	        event=event||window.event;
	        event.stopPropagation();
	    });
	}
    
})(mui);
//城市多选---------------------------------------------------------------------------------------------------------------结束----------------------
function arrsplice(classlist, id) {
	var list = classlist.split(',');
	for(var i = 0; i < list.length; i++) {
		if(id == list[i]) {
			list.splice(i, 1);
		}
	}
	return list;
}

//职位、城市类别搜索---------------------------------------------------------------------------------------------------------------结束----------------------


mui('.zn_search').on('input','.inputListener',function(){
	var inputv		= this.value.trim().toLowerCase(),
		type 		= this.getAttribute('data-type').trim(),
		fsArr 		= 	[],
		thisclass	=	[],
		fsi			=	[],
		fst			=	[],
		fsn			=	[],
		fs_parent	=	[],
		fsone		=	[],
		fstwo		=	[],
		fsthr		=	[],
		rfs			=	[];
	if(type=='jobclass'){
		fsn			=	jn;
		fs_parent	=	job_parent;
		fsi			=	ji;
		fst			=	jt;
	}else if(type=='cityclass'){
		fsn			=	cn;
		fs_parent	=	city_parent;
		fsi			=	ci;
		fst			=	ct;
	}
	
	if(inputv!=''){
		if(fsn.length>0){
			var itemv 	=	'';

			fsn.forEach(function(item,index){

				itemv	=	item.toLowerCase();

				if(itemv.indexOf(inputv)!= -1){//当前级（可为1/2/3级）
					thisclass.push(index);
				}
	        })
		}

		if(thisclass.length>0){
        	for(var i=0;i<thisclass.length;i++){

        		var t=thisclass[i];
        		for(var lev = 1;fs_parent[t]>0;t = fs_parent[t]){
        			lev++;
        		}
        		
        		if(lev==1){
        			fsone.push(thisclass[i]);
        		}else if(lev==2){
        			fstwo.push(thisclass[i]);
        		}else{
        			fsthr.push(thisclass[i]);
        			rfs.push({'three':thisclass[i],'two':fs_parent[thisclass[i]],'one':fs_parent[fs_parent[thisclass[i]]]});
        		}
        		
        	}
        	
        	
        	if(fst.length>0 && fst!='new Array()'){
        		fsone = [];
        	}
        	
        	
        	
        	if(fsone.length>0){
        		var hastwo = false,
        			allctwo= [];
        		for(var i=0;i<fsone.length;i++){
        			hastwo = false;
        			fsArr.push({"name":fsn[fsone[i]],"value":fsone[i],'class':'1',"selected":'',"disabled":''});
        			//江苏
        			if(fst[fsone[i]] && fst[fsone[i]].length>0){
        				for(var j=0;j<fst[fsone[i]].length;j++){//先判断选项里是否有二级属于此一级
		        			if(fstwo.indexOf(parseInt(fst[fsone[i]][j]))!=-1){
		        				hastwo = true;
		        			}
		        		}
        			}
	        		
	        		
	        		if(hastwo){//有二级
        				if(fstwo.length>0){
	        				for(var m=0;m<fstwo.length;m++){
			        			if(fst[fsone[i]] && (fst[fsone[i]].indexOf(fstwo[m])!=-1 || fst[fsone[i]].indexOf(fstwo[m].toString())!=-1)){
			        				fsArr.push({"name":fsn[fstwo[m]],"value":fstwo[m],"selected":'',"disabled":'',"upclass":1});
			        				
			        				if(fsthr.length>0){
			        					for(var t=0;t<fsthr.length;t++){
				        					if(fst[fstwo[m]] && (fst[fstwo[m]].indexOf(fsthr[t])!=-1 || fst[fstwo[m]].indexOf(fsthr[t].toString())!=-1)){
				        						fsArr.push({"name":fsn[fsthr[t]],"value":fsthr[t],"selected":'',"disabled":'',"upclass":2});
				        						//江苏-宿迁-沭阳
				        						fsthr.splice(t,1);
				        						t--;
				        					}
				        				}
			        				}
			        				fstwo.splice(m,1);
			        				m--;
			        			}
			        		}
		        		}
        			}else{
        				if(rfs.length>0){
		        			rfs.forEach(function(item,index){
								if(parseInt(item.one) == parseInt(fsone[i])){//
									fsArr.push({"name":fsn[item.three],"value":item.three,"selected":'',"disabled":'',"upclass":1,"upname":fsn[item.two]});
									//江苏-沭阳
									fsthr.splice(fsthr.indexOf(parseInt(item.three)),1);
								}
					        })
		        		}
		        	}
	        	}
        	}
        	
        	if(fstwo.length>0){
        		for(var m=0;m<fstwo.length;m++){
        			
    				fsArr.push({"name":fsn[fstwo[m]],"value":fstwo[m],"selected":'',"disabled":'','class':'2',"upname":fsn[fs_parent[fstwo[m]]]});
    				//宿迁
    				if(fsthr.length>0){
    					for(var t=0;t<fsthr.length;t++){
        					if(fst[fstwo[m]] && (fst[fstwo[m]].indexOf(fsthr[t])!=-1 || fst[fstwo[m]].indexOf(fsthr[t].toString())!=-1)){
        						fsArr.push({"name":fsn[fsthr[t]],"value":fsthr[t],"selected":'',"disabled":'','class':'3',"upclass":1});
        						//宿迁-沭阳
        						fsthr.splice(t,1);
        						t--;
        					}
        				}
    				}
        			
        		}
        	}
        	
        	if(fsthr.length>0){
        		for(var t=0;t<fsthr.length;t++){
        			fsArr.push({"name":fsn[fsthr[t]],"value":fsthr[t],"selected":'',"disabled":'','class':'3',"upname":fsn[fs_parent[fsthr[t]]]});
					//沭阳
				}
        	}

        }

        showSearchResult(type,fsArr);
        document.getElementById(type+"_search").style.display = 'block';
	}else{
		document.getElementById(type+"_search").style.display = 'none';
	}
});

function showSearchResult(type,resultArr){
	var html = '';
	if(resultArr.length>0){
		var typeclass 	= '',
			upnameHtml 	= '',
			choosedid	= '',
			checked		= '',
			fs_parent	= [],
			ft			= [];

		if(type=='jobclass'){
			choosedid	= document.getElementById("job_classid").value;
			ft			= jt;
			fs_parent	= job_parent;
		}else if (type=='cityclass'){
			choosedid	= document.getElementById("city_classid").value;
			ft			= ct;
			fs_parent	= city_parent;
		}

		var choosedArr	= choosedid.trim()=='' ? [] : choosedid.split(',');//已选中的值
		
		
		var cval 		= 0,
			disabled 	= '';
		resultArr.forEach(function(item,index){

			typeclass 	= item.upclass==1 ? 'zn_search_three' : 'zn_search_two';
			//检查是否有上级类名称
			if(item.upname!='' && typeof(item.upname)!="undefined"){
				upnameHtml 	= ' <span ><font size="1" color="#a5a5a5">'+item.upname+'</font></span>';
			}else{
				upnameHtml 	= '';
			}
			//检查是否已选中
			if(choosedArr.indexOf(item.value.toString())!=-1){
				checked		= 'checked';
			}else{
				checked		= '';
			}

			disabled 		= '';

			for(var i=0;i<choosedArr.length;i++){//检查已选项里是否有该项的上级,有的话，该项设为 选中 不可选状态
				cval	=	parseInt(choosedArr[i]);
				
				if(ft[cval] 
					&& ( 
						(ft[cval].indexOf(item.value)!=-1 || ft[cval].indexOf(item.value.toString())!=-1) 
						|| (fs_parent[item.value] && (ft[cval].indexOf(fs_parent[item.value])!=-1 || ft[cval].indexOf(fs_parent[item.value].toString())!=-1)) 
						)
				){
					disabled = 'disabled';
					checked	 = 'checked';
				};
			}


			html +='<div class="'+typeclass+'">';
    		html +=		'<label>'+item.name+upnameHtml+'</label>';
			html +=		'<input id="'+type+item.value+'" class="" name="'+type+'_s" value="'+item.value+'" data-class="'+item.class+'" data-type="'+type+'" type="checkbox" '+checked+' '+disabled+' >';
    		html +='</div>';
		})
	}else{
		html += '<div class="zn_notip">暂无数据</div>';
		
	}
	
	document.getElementById(type+'_searhtml').innerHTML = html;
}
//点击搜索项选中或取消选中时，动态触发三级联动选择的选中或取消选中
mui('.classTap').on('change', 'input', function() {
	var thisclassid		=	this.value,
		tclass			=	this.getAttribute('data-class'),
		type			=	this.getAttribute('data-type');

	var tapid = '';
	if(type=='jobclass'){
		if(tclass == '1'){
			tapid	=	document.getElementById("jobone"+thisclassid);
		}else if(tclass == '2'){
			tapid	=	document.getElementById("jobcheckAll"+thisclassid);
		}else{
			tapid	=	document.getElementById("jobthree"+thisclassid);
		}
	}else if(type=='cityclass'){
		if(tclass == '1'){
			tapid	=	document.getElementById("cityone"+thisclassid);
		}else if(tclass == '2'){
			tapid	=	document.getElementById("citycheckAll"+thisclassid);
		}else{
			tapid	=	document.getElementById("citythree"+thisclassid);
		}
	}

	tapid.checked = this.checked ? true : false;
	
	
	if(tapid){
		mui.trigger(tapid,'change');
	}
	
})
function singleDeal(list){
	for(var j=0;j<list.length;j++){
		if(document.getElementById('jobclass'+list[j])){//搜索项存在的设为未选中状态
			document.getElementById('jobclass'+list[j]).checked = false;
		}
		//取消选中处理下方已选显示
		if(document.getElementById("jobthree"+list[j])){
			document.getElementById("jobthree"+list[j]).checked = false;
		}
		if(document.getElementById("jobtwo"+list[j])){
			document.getElementById("jobtwo"+list[j]).checked = false;
		}
		if(document.getElementById("jobone"+list[j])){
			document.getElementById("jobone"+list[j]).checked = false;
		}
		if(document.getElementById("jobcheckAll"+list[j])){
			var listBoxed = mui('.jobcheck' + list[j]);
			document.getElementById("jobcheckAll"+list[j]).checked = false;
			//取消该类下所有三级的已选中和不可选状态
			listBoxed.each(function() {
				var ele = this;
				ele.checked = false;
				ele.disabled = false;

				if(document.getElementById('jobclass'+ele.value)){//搜索项的三级设为未选中 可选中状态
					document.getElementById('jobclass'+ele.value).checked = false;
					document.getElementById('jobclass'+ele.value).disabled = false;
				}
			})
		}
		$("#jobchoosed a").each(function() {
			var elechoose = this;
			var elechooseid = elechoose.getAttribute('data-id');
			if(elechooseid == list[j]) {
				document.getElementById("jobchoosed").removeChild(elechoose);
			}
		});
	}
	
}
