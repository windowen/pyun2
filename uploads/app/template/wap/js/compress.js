
var Orientation = null;

function previewImage(obj,type){
	
	setFilesReader(obj.files[0], 0,type);
	
}
function setFilesReader(file, ismul,type) {
	//获取照片方向角属性，用户旋转控制  
	EXIF.getData(file, function() {     
		Orientation = EXIF.getTag(this, 'Orientation');  
	});  
		
	var reader = new window.FileReader();
	reader.onload = function(e) {
		compress(this.result, fileSize, ismul,type);
	}
	reader.readAsDataURL(file);
	//console.log(this.files[0]);
	var fileSize = Math.round(file.size/1024/1024) 
}

//onchange="readMultiFiles(this.files)"
function readMultiFiles(files) {
	for (var i = 0; i < files.length; i++) {
		setFilesReader(files[i]);
	}
}

//res代表上传的图片，fileSize大小图片的大小
function compress(res, fileSize, ismul,type) {
    var img = new Image(), maxW = 1000; //设置最大宽度

    img.onload = function () {
        var cvs = document.createElement('canvas'), ctx = cvs.getContext('2d');

        if(img.width > maxW) {
            img.height *= maxW / img.width;
            img.width = maxW;
        }

		var w = img.width,
			h = img.height;
			
		cvs.width = w;
		cvs.height = h;

		ctx.clearRect(0, 0, cvs.width, cvs.height);

		if (Orientation != "" && Orientation != 1 && Orientation != undefined) {

			switch (Orientation) {
				case 6://需要顺时针90度旋转
					cvs.width = h;
					cvs.height = w;
					ctx.rotate(Math.PI / 2);
					ctx.drawImage(img, 0, -h, w, h);
					break;
				case 8://需要逆时针90度旋转
					cvs.width = h;
					cvs.height = w;
					ctx.rotate(-90 * Math.PI / 180);
					ctx.drawImage(img, -w, 0, w, h);
					break;
				case 3://需要180度旋转
					ctx.rotate(180 * Math.PI / 180);
					ctx.drawImage(img, -w, -h, w, h);
					break;
			}
		}else{
			ctx.drawImage(img, 0, 0, img.width, img.height);
		}
		
		var compressRate = getCompressRate(1,fileSize);

        var dataUrl = cvs.toDataURL('image/jpeg', compressRate);

        if($('#'+type+'img')){
        	$('#'+type+'img').attr('src',dataUrl);
        }
		if($('#'+type+'show')){
        	$('#'+type+'show').show();
        }
        
        
		if($('#'+type)){
			
			$('#'+type).val(dataUrl);
			
        }
		
	}
    img.src = res;
}

//计算压缩比率，size单位为MB
function getCompressRate(allowMaxSize,fileSize){
	var compressRate = 1;
		
	if(fileSize/allowMaxSize > 4){
	   compressRate = 0.5;
	} else if(fileSize/allowMaxSize >3){
	   compressRate = 0.6;
	} else if(fileSize/allowMaxSize >2){
	   compressRate = 0.7;
	} else if(fileSize > allowMaxSize){
	   compressRate = 0.8;
	} else{
	   compressRate = 0.9;
	}

	return compressRate;
}