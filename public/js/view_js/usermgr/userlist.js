/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
		isCheckAll:false,
		userList:[],
		editUser:{
			edit_headpic:"",
			edit_email:'',
			edit_oldpassword:"",
			edit_newpassword1:"",
			edit_newpassword2:""
		}
	},
	created:function(){
		$("[data-toggle='tooltip']").tooltip();
		$('#avatar').on('change', $.proxy(this.change, this));
		$('#edit_avatar').on('change', $.proxy(this.change, this));
		this.getAllUser();
		$().ready(function() {
            $("#adduserFrom").validate({ 
                rules: {
                    password: {
                        required: true,
                        minlength: 6,
                        maxlength: 16
                    },
                    passwordVerify: {
                        required: true,
                        minlength: 6,
                        maxlength: 16,
                        equalTo: "#password"
                    },
                    email: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    password: {
                        required: "请输入用户密码",
                        minlength: "密码必须6个字符以上",
                        maxlength: "密码必须16个字符以下",
                    },
                    passwordVerify: {
                        required: "请再次输入密码",
                        minlength: "密码必须6个字符以上",
                        maxlength: "密码必须16个字符以下",
                        equalTo: "两次输入的密码不一致"
                    },
                    email:{
                        required:"请输入用户的E-mail",
                        email:"请输入一个正确的邮箱"
                    }
                }
            });
        });
	},
	filters:{
		getName:function(item){
			return item.substring(0,item.lastIndexOf("@"))
		}
	},
	methods:{
		checkedAll:function(){
			for(var i=0;i < this.userList.length;i++){
				this.userList[i].check = !this.isCheckAll
			}
		},
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
		getAllUser:function(){
			  this.$http.post('/admin/user/getAllUser',{
			  }).then( function(data) {
				  /* 判断执行是否 成功 */
				  if(data.body.result){
					  toastr.success("获取数据成功")
					  this.userList = data.body.msg
				  }else{
					  toastr.error(data.body.msg)
				  }
			  }) 
		},
		addUser:function(item){
			$('#modal-form').modal('show')
		},
		deleteUser:function(u_id){

			  if(u_id==''){
				  toastr.error("请选择要删除的行");
	    		  return
			  }else{
				  this.$http.post('/admin/user/deleteUserinfo',{
					  cou_u_id:u_id
				  }).then( function(data) {
					  /* 判断执行是否 成功 */
					  if(data.body.result){
						  toastr.success("获取数据成功");
						  this.getAllUser()
					  }else{
						  toastr.error(data.body.msg);
					  }
				  })
			  }
		  
		},
		addUserconfirm:function(){
	  		/*POST之前检查一下数据*/
			  if(!$("#adduserFrom").valid()){
				  toastr.info("请将信息填写完整");
				  return
			  }
		      $.ajax('/admin/user/addUser', {
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
			        		this.getAllUser()
			        		$('#adduserFrom')[0].reset()
							/*清空选择的文件*/
							var file = $('.avatarfile')
							file.after(file.clone().val("")); 
							file.remove(); 
							$('#avatar').on('change', $.proxy(this.change, this));
							$(".avatar").attr('src','/data/avatarResource/common/default.jpg'); 
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
		editUserinfo:function(user){
		// console.log(user.headpic);
			  if(user == ''){
				  this.u_id = ""
			  }else{
				  this.u_id = user.u_id
				 
				  this.editUser = {
						  edit_headpic:user.headpic,
						  edit_email:user.username,
						  edit_oldpassword:"",
						  edit_newpassword1:"",
						  edit_newpassword2:""
				}
			  }
		  $('#modal-form-edit').modal('show')
	  },
	  updateUserconfirm:function(){
		  var form=new FormData($('.editform')[0]);
    	  form.append("id",this.u_id);
		  $.ajax('/admin/user/updateUserinfo', {
		        headers: {'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}, 
		        type: 'post',
		        data: form,
		        dataType: 'json',
		        processData: false,
		        contentType: false,

		        beforeSend: function () {
		        	$(".submit").attr({ disabled: "disabled" });
		        },

		        success: function (data) {
		        	if(data.result){
		        		toastr.success(data.msg)
		        		this.getAllUser()
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
		  
	  }
	}
})