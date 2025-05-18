if(typeof ci != "undefined" && typeof ct != "undefined" && typeof cn != "undefined") {
	var cityData = [];
	for(var i = 0; i < ci.length; i++) {
		var city = [];
		if(typeof ct[ci[i]] != "undefined"){
			for(var j = 0; j < ct[ci[i]].length; j++) {
				var threecity = [];
				if(ct[ct[ci[i]][j]]) {
					for(var k = 0; k < ct[ct[ci[i]][j]].length; k++) {
						threecity.push({
							value: ct[ct[ci[i]][j]][k],
							text: cn[ct[ct[ci[i]][j]][k]]
						})
					}
				}
				city.push({
					value: ct[ci[i]][j],
					text: cn[ct[ci[i]][j]],
					children: threecity
				})
			}
		}
		
		cityData.push({
			value: ci[i],
			text: cn[ci[i]],
			children: city
		})
	}
}
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
		// bounce: true //是否启用回弹
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
})(mui);
view.addEventListener('pageBeforeBack', function(e) {
	var production = document.getElementById('production');
	if(production.value != '') {
		document.getElementById('production').value = production.value;
		document.getElementById('productionshow').innerText = production.value;
	}
	//console.log(e);
});
(function($, doc) {
	$.init();
	$.ready(function() {
		var sexPicker = new $.PopPicker();
		sexPicker.setData(sexData);
		var sexPickerButton = doc.getElementById('sexPicker');
		var sex = doc.getElementById('sex');
		sexPickerButton.addEventListener('tap', function(event) {
			document.activeElement.blur();
			sexPicker.show(function(items) {
				sex.value = items[0].value;
				sexPickerButton.innerText = items[0].text;
			});
		}, false);
		var expPicker = new $.PopPicker();
		expPicker.setData(expData);
		var expPickerButton = doc.getElementById('expPicker');
		var exp = doc.getElementById('exp');
		expPickerButton.addEventListener('tap', function(event) {
			document.activeElement.blur();
			expPicker.show(function(items) {
				exp.value = items[0].value;
				expPickerButton.innerText = items[0].text;
			});
		}, false);
		//城市选择
		var cityPickerButton = doc.getElementById('cityPicker');
		if(typeof cityData != "undefined" && cityPickerButton) {
			var cclass = 2;
			if(ct.length<=0 || ct=='new Array()'){
				cclass = 1;
			}
			var cityPicker = new $.PopPicker({
				layer: cclass
			});
			cityPicker.setData(cityData);
			var provinceid = cityPickerButton.getAttribute('data-provinceid'),
				cityid = cityPickerButton.getAttribute('data-cityid'),
				three_cityid = cityPickerButton.getAttribute('data-three_cityid');
			if(provinceid) {
				cityPicker.pickers[0].setSelectedValue(provinceid);
			}
			if(cityid) {
				setTimeout(function() {
					if(cityPicker.pickers[1]){
						cityPicker.pickers[1].setSelectedValue(cityid);
					}
					
				}, 200);
			}
			if(three_cityid) {
				setTimeout(function() {
					if(cityPicker.pickers[2]){
						cityPicker.pickers[2].setSelectedValue(three_cityid);
					}
				}, 400);
			}
			cityPickerButton.addEventListener('tap', function(event) {
				document.activeElement.blur();
				cityPicker.show(function(items) {
					doc.getElementById('provinceid').value = items[0].value;
					if(items[1]){
						doc.getElementById('cityid').value = items[1].value;
					}
					if(items[2]){
						doc.getElementById('three_cityid').value = items[2].value;
					}
					
					var html = items[0].text;
					if(items[1]){
						html += " " + items[1].text
					}
					if(items[2]){
						html += items[2].text ? " " + items[2].text : '';
					}
					
					cityPickerButton.innerText = html;

				});
			}, false);
		}
	});
})(mui, document);

(function() {
	mui('#main').on('click', '#submit', function(event) {
		var name = $.trim(document.getElementById('username').value),
			id = $.trim(document.getElementById('id').value),
			sex = $.trim(document.getElementById('sex').value),
			exp = $.trim(document.getElementById('exp').value),
			job = $.trim(document.getElementById('job').value),
			provinceid = $.trim(document.getElementById('provinceid').value),
			cityid = $.trim(document.getElementById('cityid').value),
			three_cityid = $.trim(document.getElementById('three_cityid').value),
			production = $.trim(document.getElementById('production').value),
			mobile = $.trim(document.getElementById('mobile').value),
			password = $.trim(document.getElementById('password').value),
			checkcode,
			
			verify_token;
		if(name == '') {
			return mui.toast('请填写姓名！');
		}
		if(sex == '') {
			return mui.toast('请选择性别！');
		}
		if(exp == '') {
			return mui.toast('请选择工作年限！');
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
		
		if(production == '') {
			return mui.toast('请介绍自己！');
		}
		if(job == '') {
			return mui.toast('请填写工作！');
		}
		if(mobile == '') {
			return mui.toast('请填写联系手机！');
		}
		if(isjsMobile(mobile) == false) {
			return mui.toast('请注意联系手机格式！');
		}
		if(password == '') {
			return mui.toast('请填写密码！');
		}
		if(code_web.indexOf("店铺招聘") >= 0) {
			if(code_kind == 1) {
				var checkcode = $("#checkcode").val();
				if(checkcode == '') {
					return mui.toast('请填写验证码！');
				}
			} else if(code_kind >2) {
				
				verify_token = $('input[name="verify_token"]').val();
	
				if(verify_token == '') {
					$("#bind-submit").trigger("click");
					return false; 
				}
			}
		}
	
		mui.post(wapurl + "/index.php?c=tiny&a=add", {
			id: id,
			name: name,
			sex: sex,
			exp: exp,
			job: job,
			provinceid: provinceid,
			cityid: cityid,
			three_cityid: three_cityid,
			production: production,
			mobile: mobile,
			password: password,
			authcode: checkcode,
			
			verify_token:verify_token,
			submit: 'submit'
		}, function(data) {
			if(data.url) {
				layermsg(data.msg, 2, function() {
					location.href = data.url;
				});
			} else {
				checkCode('vcode_img');
				layermsg(data.msg, 2);
				return false;
			}
	
		}, 'json');
	})
	//选择快捷输入
	mui('.mui-popover').on('tap', 'li', function(e) {
		document.getElementById("name").value = document.getElementById("name").value + this.children[0].innerHTML;
		mui('.mui-popover').popover('toggle')
	})
	
})();