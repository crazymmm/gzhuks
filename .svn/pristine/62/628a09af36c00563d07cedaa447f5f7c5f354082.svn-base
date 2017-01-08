/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
		totalrow:0,
		resResList:[]
	},
	created:function(){
		$("[data-toggle='tooltip']").tooltip();
		$('#avatar').on('change', $.proxy(this.change, this));
		this.getfrontPic()
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
					this.url = '/data/webResource/upload.png';
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
		getfrontPic:function(){
			this.$http.post("/admin/index/getfrontpic",{
				
			}).then(function(data){
				if(data.body.result){
					toastr.success('获取数据成功');
					this.resResList = data.body.msg
					this.totalrow = this.resResList.length
				}else{
					toastr.error(data.body.msg)
				}
			})
		},
		addfrontpic:function(){
	    	  if($(".avatar")[0].src.indexOf("/data/webResource/upload.png") != -1 && $("input[name='avatarfile']").val().length == 0){
	    		  toastr.error("请选择图片");
	    		  return
	    	  }

		      $.ajax('/admin/index/addfrontpic', {
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
			        		$('.myform')[0].reset()
							/*清空选择的文件*/
							var file = $('.avatarfile')
							file.after(file.clone().val("")); 
							file.remove(); 
							$('#avatar').on('change', $.proxy(this.change, this));
							$(".avatar").attr('src','/data/webResource/upload.png'); 
			        		this.getfrontPic()
			        	}else{
			        		toastr.error(data.msg)
			        	}
			        }.bind(this),

			        error: function (XMLHttpRequest, textStatus, errorThrown) {
			        	console.log("error");
			        },

			        complete: function () {
			        	$(".submit").removeAttr("disabled");
			        }
			      });
		},
		deleteFrontpic:function(item){
			  swal({
					title : "确定要删除这个图片吗?",
					text : "你将不能恢复本次操作!",
					type : "warning",
					showCancelButton : true,
					cancelButtonText : "取消",
					confirmButtonColor : "#DD6B55",
					confirmButtonText : "确认, 删除!",
					closeOnConfirm : false
				}, function() {
					this.$http.post("/admin/index/deleteFrontpic",{
						id:item.p_id
					}).then(function(data){
						if (data.body.result) {
							swal({
								title : "操作成功!",
								text :  "选择的图片已删除",
								confirmButtonText : "确认",
								type : "success",
								timer : 1000
							});
							this.getfrontPic();
						} else {
							swal({
								title : "操作失败!",
								text : data.body.msg,
								confirmButtonText : "确认",
								type : "error",
								timer : 1000
							});
						}
					})
				}.bind(this));
		},
		lookPic:function(value){
			console.log("");
			  $('.originalPic').attr("disabled",false);
			  $(".img-responsive").attr('src',value.p_url); 
			  $("#gallery-image-modal").modal('show');
		}
	}
})