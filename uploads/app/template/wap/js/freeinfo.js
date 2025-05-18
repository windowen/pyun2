 UM.getEditor('content', {
	toolbar: false,
	elementPathEnabled: false,
	wordCount: false,
	autoHeightEnabled: false
});

 UM.getEditor('services', {
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
view.addEventListener('pageBeforeBack', function(e) {
        var content = '',
				showid = '',
				textid = '';
			if(e.detail.page.id == 'contenthtml') {
				showid = 'contentshow';
				textid = 'contenttext';
				content = UM.getEditor('content').getContent();
			} else if(e.detail.page.id == 'serviceshtml') {
				showid = 'servicesshow';
				textid = 'servicestext';
				content = UM.getEditor('services').getContent();
			}
			if(content != "") {
				document.getElementById(showid).innerText = content.replace(/<\/?.+?>/g, "").replace(/ /g, "");
				document.getElementById(textid).innerText = content;
			}
});
	var infosubmitBtn = document.getElementById('infosubmit');
	if(infosubmitBtn) {
   
		infosubmitBtn.addEventListener('tap', checkinfo, false)
	}
  
  function checkinfo(){
    var name = $.trim(document.getElementById('name').value),
        sex = $.trim(document.getElementById('sex').value),
        provinceid = $.trim(document.getElementById('provinceid').value),
        cityid = $.trim(document.getElementById('cityid').value),
        three_cityid = $.trim(document.getElementById('three_cityid').value),
        services = $.trim(document.getElementById('servicestext').value),
        speciality = $.trim(document.getElementById('speciality').value),
        salary = $.trim(document.getElementById('salary').value),
        content = $.trim(document.getElementById('contenttext').value),
        moblie = $.trim(document.getElementById('moblie').value);
        if(name ==  ''){
            mui.toast('请填写姓名');return false;
        }
        if(sex  ==  ''){
            mui.toast('请选择性别');return false;
            
        }
        var cionly='';
        if(ct.length<=0 || ct=='new Array()'){
          cionly = '1';
        }
        if(cionly=='1'){
          if(provinceid == '' || provinceid == '0') {
             mui.toast('请选择所在地！');return false;
          }
        }else{
          if(cityid == '' || cityid == '0') {
             mui.toast('请选择所在地！');return false;
          }
        }
        
        if(services==''){
           mui.toast('请填写提供服务！');return false;
        }
        if(speciality==''){
          mui.toast('请填写技能特长！');return false;
        }
        if(salary==''){
          mui.toast('请填写工作薪资');return false;
        }
        if(content==''){
          mui.toast('请填写个人简介！');return false;
        }
        if(moblie==''){
          mui.toast('请填写联系电话！');return false;
        }
        if(moblie != '' && !isjsMobile(moblie)) {  
            mui.toast('请填写正确联系电话格式！');
            return false;
        }else{
            $.post('index.php?c=free&a=freemoblie',{moblie:moblie},function(data){
                if(data==1){
                   mui.toast('手机号码已存在，请重新输入！');return false;
                }else{
                    formData.append('name',name);
                    formData.append('sex',sex);
                    formData.append('provinceid',provinceid);
                    formData.append('cityid',cityid);
                    formData.append('three_cityid',three_cityid);
                    formData.append('services',services);
                    formData.append('speciality',speciality);
                    formData.append('salary',salary);
                    formData.append('content',content);
                    formData.append('moblie',moblie);
                    formData.append('submit', 'submit');
                    layer_load('执行中，请稍候...');
                    $.ajax({
                      url:  "index.php?c=free&a=info",
                      type: 'post',
                      data :formData,
                      contentType: false,
                      processData: false,
                      dataType: 'json',
                      success: function(res) {
                        layer.closeAll();
                        var res = JSON.stringify(res);
                        var data = JSON.parse(res);
                        if(data.url){
                          layermsg(data.msg, 2, function() {location.href = data.url;});
                        }else{
                          layermsg(data.msg, 2);return false;
                        }
                    }
          
                  });
                }
                
            });
        }

      

  }
  
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
			url: "index.php?c=free&a=photo",
			cache: false,
			dataType: 'json',
			data: {
				uimage: uimage,
				submit: 1
			},
			success: function(res) {
				layer.closeAll();
        if(res==9){
           layermsg('供求头像更新成功', 2, function() {
            window.location.href = wapurl + "index.php?c=free&a=info";});
        }else{
            layermsg('供求头像更新失败');
        }
         
			}
		});
	}
	
}



