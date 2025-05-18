function check_email(strEmail) {
	var emailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/;
	if(emailReg.test(strEmail))
		return true;
	else
		return false;
}

$(function() {
	mui('.tblink').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 1 : 2;
			$("#tblink").val(active);
		});
	});
	mui('.is_graduate').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 1 : 0;
			$("#is_graduate").val(active);
		});
	});
	mui('.salary_type').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 1 : 2;
			var salarytype = document.getElementById('salary_type'),
				minsalary = document.getElementById('minsalary'),
				maxsalary = document.getElementById('maxsalary');
			if(active == 1) {
				minsalary.value = "";
				minsalary.disabled = true;
				maxsalary.value = "";
				maxsalary.disabled = true;
			} else if(active == 2) {
				minsalary.disabled = false;
				maxsalary.disabled = false;
			}
			salarytype.value = active;
		});
	});
	mui('.islink').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 3 : 1;
			$("#islink").val(active);
			$("#islink1").removeClass('admin_job_style_n');
			$("#islink2").removeClass('admin_job_style_n');
			document.getElementById('link').innerText = '请选择';
		});
	});
	mui('.isemail').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 3 : 1;
			$("#isemail").val(active);
		});
	});
	mui('.yunset_bth_box').on('tap', '.addnext',  function() {
		var name = $.trim($("#name").val()),
			job_classid 	=	$.trim($("#job_classid").val()),
     		provinceid 		=	$.trim($("#provinceid").val()),
			cityid 			=	$.trim($("#cityid").val()),
			minsalary 		=	$.trim($("#minsalary").val()),
			maxsalary 		=	$.trim($("#maxsalary").val()),
			salary_type 	=	$.trim($("#salary_type").val()),
			description 	=	$.trim($("#contenttext").val()),
			islink 			=	$.trim($("#islink").val()),
			link_moblie 	=	$.trim($("#link_moblie").val()),
			link_address 	=	$.trim($("#link_address").val()),
			link_man 		=	$.trim($("#link_man").val()),
			isemail 		=	$.trim($("#isemail").val()),
			email 			=	$.trim($("#email").val()),
			welfare 		=	$.trim($("#welfare").val());
		if(name == "") {
			 mui.toast('请填写职位名称');return false;
		} else if(job_classid == "") {
			 mui.toast('请选择职位类别');return false;
		}

		var cionly ='';
		if(ct.length<=0 || ct=='new Array()'){
			cionly = '1';
		}

		if(cionly == '1'){
			if(provinceid == '') {
				mui.toast('请选择工作地点');return false;
			}
		}else{
			if(cityid == '') {
				mui.toast('请选择工作地点');
				return false;
			}
		}


		if(salary_type != 1) {
			if(minsalary == "" || minsalary == "0") {
				 mui.toast('请填写薪资待遇');return false;
			}
			if(maxsalary > 0) {
				if(parseInt(maxsalary) <= parseInt(minsalary)) {
					 mui.toast('最高工资必须大于最低工资');return false;
				}
			}
		}
		if(description == "") {
			 mui.toast('请填写职位描述');return false;
		}
		if(islink == 2) {
			if(link_man == '' || link_moblie == '') {
				 mui.toast('联系人及联系电话均不能为空');return false;
			}
			if(link_moblie && (isjsMobile(link_moblie) == false && !isjsTell(link_moblie))) {
				 mui.toast('联系电话格式错误');return false;
			}
		}
		if(isemail == '2') {
			if(email == '') {
				 mui.toast('请输入邮箱');return false;
			} else if(check_email(email) == false) {
				 mui.toast('新邮箱格式错误');return false;
			}
		}
	})

	var jobsubmit = document.getElementById('jobsubmit');
	if(jobsubmit) {
		jobsubmit.addEventListener('tap', function(event) {
			var name = $.trim($("#name").val()),
				r_status = $.trim($("#r_status").val()),
				job_classid = $.trim($("#job_classid").val()),
				provinceid = $.trim($("#provinceid").val()),
				cityid = $.trim($("#cityid").val()),
				three_cityid = $.trim($("#three_cityid").val()),
				minsalary = $.trim($("#minsalary").val()),
				maxsalary = $.trim($("#maxsalary").val()),
				salary_type = $.trim($("#salary_type").val()),
				description = $.trim($("#contenttext").val()),
				islink = $.trim($("#islink").val()),
				link_moblie = $.trim($("#link_moblie").val()),
				link_address = $.trim($("#link_address").val()),
				link_man = $.trim($("#link_man").val()),
				x = $.trim(document.getElementById('map_x').value),
				y = $.trim(document.getElementById('map_y').value),
				tblink = $.trim($("#tblink").val()),
				hy = $.trim($("#hy").val()),
				number = $.trim($("#number").val()),
				exp = $.trim($("#exp").val()),
				report = $.trim($("#report").val()),
				age = $.trim($("#age").val()),
				sex = $.trim($("#sex").val()),
				edu = $.trim($("#edu").val()),
				is_graduate = $.trim($("#is_graduate").val()),
				marriage = $.trim($("#marriage").val()),
				lang = $.trim($("#lang").val()),
				isemail = $.trim($("#isemail").val()),
				email = $.trim($("#email").val()),
				id = $.trim($("#id").val()),
				welfare = $.trim($("#welfare").val()),
				exp_req = $.trim($("#exp_req").val()),
				edu_req = $.trim($("#edu_req").val()),
				sex_req = $.trim($("#sex_req").val()),
				state = $.trim($("#state").val());
				
			var joblock = $.trim($("#joblock").val());

			if(name == "") {
				return mui.toast('请填写职位名称');
			} else if(job_classid == "") {
				return mui.toast('请选择职位类别');
			}
			var cionly ='';
			if(ct.length<=0 || ct=='new Array()'){
				cionly = '1';
			}
			if(cionly == '1'){
				if(provinceid == '') {
					mui.toast('请选择工作地点');return false;
				}
			}else{
				if(cityid == '') {
					mui.toast('请选择工作地点');
					return false;
				}
			}

			if(salary_type != 1) {
				if(minsalary == "" || minsalary == "0") {
					return mui.toast('请填写薪资待遇');
				}
				if(maxsalary > 0) {
					if(parseInt(maxsalary) <= parseInt(minsalary)) {
						return mui.toast('最高工资必须大于最低工资');
					}
				}
			}
			if(description == "") {
				return mui.toast('请填写职位描述');
			}
			if(islink == 2) {
				if(link_man == '' || link_moblie == '') {
					return mui.toast('联系人及联系电话均不能为空');
				}
				if(link_moblie && (isjsMobile(link_moblie) == false && !isjsTell(link_moblie))) {
					return mui.toast('联系电话格式错误');
				}
			}
			if(isemail == '2') {
				if(email == '') {
					return mui.toast('请输入邮箱');
				} else if(check_email(email) == false) {
					return mui.toast('新邮箱格式错误');
				}
			}
			
			if(joblock==2 || id>0){
				layer_load('执行中，请稍候...');
				mui.post('index.php?c=jobadd', {
					r_status: r_status,
					name: name,
					job_classid:job_classid,
					provinceid: provinceid,
					cityid: cityid,
					three_cityid: three_cityid,
					minsalary: minsalary,
					maxsalary: maxsalary,
					salary_type: salary_type,
					description: description,
					islink: islink,
					link_moblie: link_moblie,
					link_address:link_address,
					link_man: link_man,
					y: y,
					x: x,
					tblink: tblink,
					hy: hy,
					number: number,
					exp: exp,
					report: report,
					age: age,
					sex: sex,
					edu: edu,
					is_graduate: is_graduate,
					marriage: marriage,
					lang: lang,
					isemail: isemail,
					email: email,
					id: id,
					state: state,
					welfare: welfare,
					exp_req: exp_req,
					edu_req:edu_req,
					sex_req:sex_req,
					submit: 'submit'
				}, function(data) {
					layer.closeAll();
					if(data.url_tg) {
						location.href = wapurl + 'member/' + data.url_tg;
					} else {
						layermsg(data.msg, 2, function() {
							location.href = data.url;
						});
					}
				}, 'json');
			}else{
				layer.open({
					title : [ '温馨提示', 'background-color: #FF4351; color:#fff;' ],
					content : '发布后,职位名称将不可修改是否继续？',
					btn : [ '继续', '取消' ],
					shadeClose : false,
					yes : function() {
					 layer.closeAll();
						layer_load('执行中，请稍候...');
						mui.post('index.php?c=jobadd', {
							r_status: r_status,
							name: name,
							job_classid:job_classid,
							provinceid: provinceid,
							cityid: cityid,
							three_cityid: three_cityid,
							minsalary: minsalary,
							maxsalary: maxsalary,
							salary_type: salary_type,
							description: description,
							islink: islink,
							link_moblie: link_moblie,
							link_address:link_address,
							link_man: link_man,
							y: y,
							x: x,
							tblink: tblink,
							hy: hy,
							number: number,
							exp: exp,
							report: report,
							age: age,
							sex: sex,
							edu: edu,
							is_graduate: is_graduate,
							marriage: marriage,
							lang: lang,
							isemail: isemail,
							email: email,
							id: id,
							state: state,
							welfare: welfare,
							exp_req: exp_req,
							edu_req:edu_req,
							sex_req:sex_req,
							submit: 'submit'
						}, function(data) {
							layer.closeAll();
							if(data.url_tg) {
								location.href = wapurl + 'member/' + data.url_tg;
							} else {
								layermsg(data.msg, 2, function() {
									location.href = data.url;
								});
							}
						}, 'json');				
					}
				});
			}
			
		})
	}

})