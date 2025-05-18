var umeditor = UM.getEditor('content', {
	toolbar: false,
	elementPathEnabled: false,
	wordCount: false,
	autoHeightEnabled: false
});
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
view.addEventListener('pageShow', function(e) { //动画开始前触发
	document.activeElement.blur();
	if(e.detail.page.id == 'maphtml') {
		var mapx = document.getElementById('map_x').value,
			mapy = document.getElementById('map_y').value,
			address = document.getElementById('address').value;
		if((mapx==""||mapy=="")&&address!=""){
			localsearch(searchcity); 
		}else if(mapx!="" && mapy!=""){
			setLocation('map_container', mapx, mapy, "map_x", "map_y"); 
		}else{
			var geolocation = new BMap.Geolocation();
			geolocation.getCurrentPosition(function(r) {
				if(this.getStatus() == BMAP_STATUS_SUCCESS) {
					location_r = r;
					setLocation('map_container', r.point.lng, r.point.lat, "map_x", "map_y");
				}
			}, {
				enableHighAccuracy: true
			}); 
		}
	}
});
view.addEventListener('pageBeforeBack', function(e) {
	if(e.detail.page.id == 'welfarehtml') {
		var allwel = '';
		$('.welfare').each(function(w, wel) {
			if(wel.checked == true) {
				if(allwel == '') {
					allwel = wel.dataset.name;
				} else {
					allwel = allwel + ',' + wel.dataset.name;
				}
			}
		});
		document.getElementById('welfareshow').innerText = allwel?allwel.substring(0,15):'请选择';
		document.getElementById('welfare').value = allwel;
	}
	if(e.detail.page.id == 'moneyhtml') {
		var money = document.getElementById('money').value;
		var moneytype = document.getElementById('moneytype').value;
 		
		if(money != "") {
			if(moneytype==1){
				document.getElementById('moneyshow').innerText = document.getElementById('money').value + "万元";
 			}else if(moneytype==2){
				document.getElementById('moneyshow').innerText = document.getElementById('money').value + "万美元";
			}
		}
	}
	if(e.detail.page.id == 'contenthtml') {
		var content = umeditor.getContent();
		if(content != "") {
			document.getElementById('contentshow').innerText = content.replace(/<\/?.+?>/g, "").replace(/ /g, "");
			document.getElementById('content').innerText = content;
		}else{
			document.getElementById('contentshow').innerText='';
			document.getElementById('content').innerText='';
		}
	}
});
var moneytypeData = [{
	value: 1,
	text: '人民币'
}, {
	value: 2,
	text: '美元'
}];
(function($, doc) {
	$.init();
	$.ready(function() {
		var sdatePicker = document.getElementById('sdatePicker');
		if(sdatePicker) {
			var sdate = document.getElementById('sdate');
			sdatePicker.addEventListener('tap', function() {
				document.activeElement.blur();
				var optionsJson = this.getAttribute('data-options') || '{}';
				var options = JSON.parse(optionsJson);
				var picker = new $.DtPicker(options);
				picker.show(function(rs) {
					sdate.value = rs.text;
					sdatePicker.innerText = rs.text;
					picker.dispose();
				});
			}, false);
		}
	});
	//添加福利
	//var result = $('#result')[0];
	var addwelfarebox = $('.addwelfarebox')[0];
	if(addwelfarebox) {
		addwelfarebox.addEventListener('tap', function(event) { //添加福利
			var welfare = doc.getElementById('addwelfare').value;
			var error = 0;
			if(welfare.length >= 2 && welfare.length <= 8) {
				//判断信息是否已经存在 
				$('.welfare').each(function(i, arr) {
					var otag = arr.dataset.name;
					if(welfare == otag) {
						error = 1;
						return mui.toast('相同福利已存在，请选择或重新填写！');
					}
				});
				if(error == 0) {
					var html = "<div class='mui-input-row mui-checkbox'><label>" + welfare + "</label><input name='welfare[]' value='" + welfare + "' type='checkbox' class='welfare' data-name='" + welfare + "' checked></div>";
					var oDiv = doc.createElement('div');
					oDiv.className = 'yun_info_fl_list';
					oDiv.innerHTML = html;
					doc.getElementById('addwelfarelist').appendChild(oDiv);
				}
				doc.getElementById('addwelfare').value = '';
			} else {
				return mui.toast('请输入2-8个福利字符！');
			}
		}, false);
	}

})(mui, document);

(function() {
	mui('.yunset_list').on('tap', '.addnext', function() {
		var name = $.trim(document.getElementById('name').value),
			shortname = $.trim(document.getElementById('shortname').value),
			hy = $.trim(document.getElementById('hy').value),
			pr = $.trim(document.getElementById('pr').value),
			mun = $.trim(document.getElementById('mun').value),
			provinceid = $.trim(document.getElementById('provinceid').value),
			cityid = $.trim(document.getElementById('cityid').value),
			three_cityid = $.trim(document.getElementById('three_cityid').value),
			address = $.trim(document.getElementById('address').value),
			x = $.trim(document.getElementById('map_x').value),
			y = $.trim(document.getElementById('map_y').value),
			linkman = $.trim(document.getElementById('linkman').value),
			linktel = $.trim(document.getElementById('linktel').value),
			linkphone = $.trim(document.getElementById('linkphone').value),
			content = $.trim(document.getElementById('content').value);

		if(name == '') {
			mui.toast('请填写企业全称！');
			return false;
		}

 		if(hy == '') {
			mui.toast('请选择从事行业！');
			return false;
		}
		if(pr == '') {
			mui.toast('请选择企业性质！');
			return false;
		}
		if(mun == '') {
			mui.toast('请选择企业规模！');
			return false;
		}
		var cionly ='';
		if(ct.length<=0 || ct=='new Array()'){
			cionly = '1';
		}
		if(cionly == '1'){
			if(provinceid == '') {
				mui.toast('请选择所在地！');return false; 
			}
		}else{
			if(cityid == '') {
				mui.toast('请选择所在地！');
				return false;
			}
		}
		
		if(address == '') {
			mui.toast('请填写公司地址！');
			return false;
		}
		if(setPosition == '1' && x == '' && y == ''){
			mui.toast('请设置企业地图！');
			return false;
		}
		if(linkman == '') {
			mui.toast('请填写联系人！');
			return false;
		}
		if(linktel == '' ) {
			mui.toast('请填写联系手机！');
			return false;
		}
		if(linktel != '' && !isjsMobile(linktel)) {
			mui.toast('请填写正确手机格式！');
			return false;
		}
		if(linkphone != '' && !isjsTell(linkphone)) {
			mui.toast('请填写正确电话格式！');
			return false;
		}
		if(content == '') {
			mui.toast('请填写企业简介！');
			return false;
		}
	})
	
	var infosubmitBtn = document.getElementById('infosubmit')
	if(infosubmitBtn) {
		infosubmitBtn.addEventListener('tap', checkinfo, false)
	}
	var infosubmitBtn2 = document.getElementById('infosubmit2')
	if(infosubmitBtn2) {
		infosubmitBtn2.addEventListener('tap', checkinfo, false)
	}
})();

function checkIsUsed(typeStr){
	
	var checkStr	= 	$.trim(document.getElementById(typeStr).value);
	
	if(checkStr){
	
		$.ajax({
			url: "index.php?c=ajaxCheckInfo",
			type: 'post',
			data: {
				typeStr:typeStr,
				checkStr:checkStr
			},
			success: function(data) {
				var data	= 	eval('('+data+')');
				if(data.errcode == 8){
					if(typeStr == 'name'){
						layermsg('企业名称已存在，请重新填写！', 2);
			            return false;
					}else if(typeStr == 'linktel'){
						layermsg('手机号码已存在，请重新填写！', 2);
			            return false;
					}	
				}	
			}
		});
	}
}

function checkinfo() {

	var name = $.trim(document.getElementById('name').value),
		shortname = $.trim(document.getElementById('shortname').value),
		hy = $.trim(document.getElementById('hy').value),
		pr = $.trim(document.getElementById('pr').value),
		mun = $.trim(document.getElementById('mun').value),
		provinceid = $.trim(document.getElementById('provinceid').value),
		cityid = $.trim(document.getElementById('cityid').value),
		three_cityid = $.trim(document.getElementById('three_cityid').value),
		address = $.trim(document.getElementById('address').value),
		x = $.trim(document.getElementById('map_x').value),
		y = $.trim(document.getElementById('map_y').value),
		linkman = $.trim(document.getElementById('linkman').value),
		linktel = $.trim(document.getElementById('linktel').value),
		linkphone = $.trim(document.getElementById('linkphone').value),
		linkmail = $.trim(document.getElementById('linkmail').value),
		website = $.trim(document.getElementById('website').value),
		linkjob = $.trim(document.getElementById('linkjob').value),
		linkqq = $.trim(document.getElementById('linkqq').value),
		busstops = $.trim(document.getElementById('busstops').value),
		infostatus = $.trim(document.getElementById('infostatus').value),
		sdate = $.trim(document.getElementById('sdate').value),
		money = $.trim(document.getElementById('money').value),
		preview = $.trim(document.getElementById('preview').value),
		moneytype = $.trim(document.getElementById('moneytype').value),
		content = $.trim(document.getElementById('content').value),
		welfare = $.trim(document.getElementById('welfare').value);

	if(name == '') {
		return mui.toast('请填写企业全称！');
	}
	if(hy == '') {
		return mui.toast('请选择从事行业！');
	}
	if(pr == '') {
		return mui.toast('请选择企业性质！');
	}
	if(mun == '') {
		return mui.toast('请选择企业规模！');
	}
	var cionly ='';
	if(ct.length<=0 || ct=='new Array()'){
		cionly = '1';
	}
	if(cionly == '1'){
		if(provinceid == '') {
			return mui.toast('请选择所在地！');
		}
	}else{
		if(cityid == '') {
			return mui.toast('请选择所在地！');
		}
	}
	
	if(address == '') {
		return mui.toast('请填写公司地址！');
	}
	if(setPosition == '1' && x == '' && y == ''){
		mui.toast('请设置企业地图！');
		return false;
	}
	if(linkman == '') {
		return mui.toast('请填写联 系 人！');
	}
	if(linktel == '' && linkphone == '') {
		return mui.toast('固定电话与手机号码必须填写一项！');
	}
	if(linktel != '' && !isjsMobile(linktel)) {
		mui.toast('请填写正确手机格式！');
		return false;
	}
	if(linkphone != '' && !isjsTell(linkphone)) {
		mui.toast('请填写正确电话格式！');
		return false;
	}
	if(content == '') {
		return mui.toast('请填写企业简介！');
	}

	formData.append('name', name);
	formData.append('shortname', shortname);
	formData.append('hy', hy);
	formData.append('pr', pr);
	formData.append('mun', mun);
	formData.append('provinceid', provinceid);
	formData.append('cityid', cityid);
	formData.append('three_cityid', three_cityid);
	formData.append('address', address);
	formData.append('x', x);
	formData.append('y', y);

	formData.append('linkman', linkman);
	formData.append('linktel', linktel);
	formData.append('linkphone', linkphone);
	formData.append('linkmail', linkmail);

	formData.append('infostatus', infostatus);
	formData.append('website', website);
	formData.append('linkjob', linkjob);
	formData.append('linkqq', linkqq);
	formData.append('busstops', busstops);
	formData.append('sdate', sdate);
	formData.append('preview', preview);
	formData.append('money', money);
	formData.append('moneytype', moneytype);

	formData.append('content', content);
	formData.append('welfare', welfare);
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

$(function() {
	
	function cutImg() {
		$('#showResult').fadeOut();
	}
	$('#image').on('change', function() {
		cutImg();
	});
	
	var $inputImage = $('#image'),
		URL = window.URL || window.webkitURL;
	if(URL) {
		$inputImage.change(function() {
			var files = this.files,
				file;
			previewImage(this,'previewlogo');
			if(files && files.length) {
				file = files[0];

				if(/^image\/\w+$/.test(file.type)) {
					setTimeout(function(){
                        toAlloyCrop($('#previewlogo').val());
                    },1000);
					$inputImage.val('');
				} else {
					showMessage('请上传图片');
				}
			}
		});
	} else {
		$inputImage.parent().remove();
	}
	function toAlloyCrop(pic){
        var mAlloyCrop;
        
        mAlloyCrop = new AlloyCrop({
            image_src: pic,
            width: 200,
            height: 200,
            output: 1,
            ok: function(base64, canvas) {
                $("#changeAvatar > img").attr("src", base64);
                document.getElementById('uimage').value = base64;
                $('#showResult').fadeIn();
                mAlloyCrop.destroy();
            },
            cancel: function() {
                $('#showResult').fadeIn();
                mAlloyCrop.destroy();
            },
            ok_text: '确定',
            cancel_text: '取消'
        });
    }
});

function photo() {
	var uimage = $("#uimage").val();
	if(uimage == '') {
		layermsg('图片未改变，无需修改');
		return false;
	}else{
		var regS = new RegExp("\\+", "gi");
		uimage = uimage.replace(regS, "#");
		layer_load('执行中，请稍候...');
		$.ajax({
			type: 'POST',
			url: "index.php?c=photo",
			cache: false,
			dataType: 'json',
			data: {
				uimage: uimage,
				submit: 1
			},
			success: function(res) {
				layer.closeAll();
				if(res){
                    layermsg(res.msg, 2, function() {
                        window.location.href = wapurl + "member/index.php?c=info";
                    });
                    return false;
                }
			}
		});
	}
	
}