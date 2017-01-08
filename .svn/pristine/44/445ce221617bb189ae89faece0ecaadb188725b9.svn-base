/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
	},
	created:function(){
		$('#logo').on('change', $.proxy(this.change, this));
	},
	methods:{
		change : function(item) {
			var files, file;
			files = $('.'+item.currentTarget.id+'file').prop('files');

			if (files.length > 0) {
				file = files[0];

				if (this.isImageFile(file)) {
					if (this.url) {
						URL.revokeObjectURL(this.url); // Revoke the old one
					}
					this.url = URL.createObjectURL(file);
				}else{
					toastr.error('请选择图片文件')
					this.url = '/data/avatarResource/common/default.jpg';
					/*清空选择的文件*/
					var file = $('.'+item.currentTarget.id+'file')
					file.after(file.clone().val("")); 
					file.remove(); 
					$('.'+item.currentTarget.id+'file').on('change', $.proxy(this.change, this));
				}
				$("."+item.currentTarget.id).attr('src',this.url); 
			}
		},
		isImageFile : function(file) {
			if (file.type) {
				return /^image\/\w+$/.test(file.type);
			} else {
				return /\.(jpg|jpeg|png|gif)$/.test(file);
			}
		},
        onSubmit: function() {
        	
    		/*POST之前检查一下数据*/
    	  if($("input[name='webtitle']").val().length == 0 || $("input[name='webtitle']").val().length > 15){
    		  toastr.error("标题长度为1至15个字符内");
    		  return
    	  }
    	  if($("input[name='webkeyword']").val().length > 100){
    		  toastr.error("关键字长度为100个字符内");
    		  return
    	  }
    	  if($("input[name='webkeycontent']").val().length > 100){
    		  toastr.error("描述长度为100个字符内");
    		  return
    	  }
    	  if($(".logo")[0].src.indexOf("/data/webResource/upload.png") != -1 && $("input[name='logofile']").val().length == 0){
    		  toastr.error("请选择站点的LOGO");
    		  return
    	  }

	      $.ajax('/admin/index/saveWebSetting', {
		        headers: {'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}, 
		        type: 'post',
		        data: new FormData($('.myform')[0]),
		        dataType: 'json',
		        processData: false,
		        contentType: false,

		        beforeSend: function () {
		        	$(".submit").attr({ disabled: "disabled" });
		        },

		        success: function (data) {
		        	if(data.result){
		        		toastr.success(data.msg)
		        	}else{
		        		toastr.error(data.msg)
		        	}
		        },

		        error: function (XMLHttpRequest, textStatus, errorThrown) {
		        	console.log("error");
		        },

		        complete: function () {
		        	$(".submit").removeAttr("disabled");
		        }
		      });
    }
	}
})