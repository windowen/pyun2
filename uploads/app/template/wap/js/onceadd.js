mui.init();
var viewApi = mui('#app').view({
	defaultPage: '#main'
});

var view = viewApi.view;
(function($) {
	//初始化单页的区域滚动
	mui('.mui-scroll-wrapper').scroll({
		scrollY: true, //是否竖向滚动
		scrollX: false, //是否横向滚动
		startX: 0, //初始化时滚动至x
		startY: 0, //初始化时滚动至y
		indicators: true, //是否显示滚动条
		deceleration: 0.0006, //阻尼系数,系数越小滑动越灵敏
	    bounce: true //是否启用回弹
	});
	//处理view的后退与webview后退
	var oldBack = $.back;
	$.back = function() {
		if(viewApi.canBack()) { //如果view可以后退，则执行view的后退
			viewApi.back();
		} else { //执行webview后退
			oldBack();
		}
	};
	view.addEventListener('pageBeforeBack', function(e) {
		document.getElementById('contentshow').innerText = document.getElementById('content').value;
		//console.log(e);
	});
})(mui);

(function() {
	mui('#main').on('click', '#oncesubmit', function(event) {
		var title = $.trim(document.getElementById('title').value),
			salary = $.trim(document.getElementById('salary').value),
			provinceid = $.trim(document.getElementById('provinceid').value),
			cityid = $.trim(document.getElementById('cityid').value),
			three_cityid = $.trim(document.getElementById('three_cityid').value),
			address = $.trim(document.getElementById('address').value),
			content = $.trim(document.getElementById('content').value),
			companyname = $.trim(document.getElementById('companyname').value),
			linkman = $.trim(document.getElementById('linkman').value),
			phone = $.trim(document.getElementById('phone').value),
			edate = $.trim(document.getElementById('edate').value),
			password = $.trim(document.getElementById('password').value),
			preview = $.trim(document.getElementById('preview').value),
			id = $.trim(document.getElementById('id').value),
			checkcode,
		
			verify_token;
		if(!id || id == '') {
			id = 0;
		} else {
			id = id;
		}

		if(!pic || pic == '') {
			pic = '';
		} else {
			pic = pic;
		}
		if(title == '') {
			return mui.toast('请填写招聘名称！');
		}
		if(salary == '') {
			return mui.toast('请填写工资！');
		}
		var cionly ='';
		if(ct.length<=0 || ct=='new Array()'){
			cionly = '1';
		}
		if(cionly == '1'){
			if(provinceid == '') {
				return mui.toast('请选择工作地区！');
			}
		}else{
			if(cityid == '') {
				return mui.toast('请选择工作地区！');
			}
		}
		
		if(address == '') {
			return mui.toast('请填写详细地址！');
		}
		if(content == '') {
			return mui.toast('请填写招聘要求！');
		}
		if(companyname == '') {
			return mui.toast('请填写店面名称！');
		}
		if(linkman == '') {
			return mui.toast('请填写联系人！');
		}
		if(phone == '') {
			return mui.toast('请填写联系电话！');
		}
		if(isjsMobile(phone) == false) {
			return mui.toast('请注意联系电话格式！');
		}
		if(edate == '') {
			return mui.toast('请填写招聘有效期！');
		}
		if(password == '') {
			return mui.toast('请填写密码！');
		}
		if(code_web.indexOf("店铺招聘")>=0) {
			if(code_kind == 1) {
				var code = $('#checkcode').val();
				if(code == '') {
					return mui.toast('请填写验证码！');
				}
			} else if(code_kind > 2) {
			
				verify_token = $('input[name="verify_token"]').val();

				if(verify_token == '') {
					$("#bind-submit").trigger("click");
					return false; 
				}
			}
		}
		formData.append('title', title);
		formData.append('salary', salary);
		formData.append('provinceid', provinceid);
		formData.append('cityid', cityid);
		formData.append('three_cityid', three_cityid);
		formData.append('address', address);
		formData.append('companyname', companyname);
		formData.append('linkman', linkman);
		formData.append('phone', phone);
		formData.append('require', content);
		formData.append('edate', edate);
		formData.append('password', password);
		formData.append('preview', preview);
		formData.append('id', id);
		if(code_web.indexOf("店铺招聘") >= 0){
			if(code_kind == 1){
				formData.append('authcode', code);
			}else if(code_kind > 2){
			
				formData.append('verify_token', verify_token);
			}
		}
		formData.append('submit', 1);
		layer_load('执行中，请稍候...');
		$.ajax({
			url: "index.php?c=once&a=add",
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
					checkCode('vcode_img');
					layermsg(data.msg, 2);
					return false;
				}
			}
		})
	}, false)
})();