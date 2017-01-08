/**
 * usermgr module index.html
 */
Vue.http.options.emulateJSON = true;
var vm = new Vue({
  el: 'body',
  data: {
	  /*学校*/
	  school:"",
	  /*学院*/
	  college:"",
	  /*班级*/
	 classes:"",
	 /*爱好*/
     hobby:"",
	  /*头像*/
	  imgsrc:"/data/avatarResource/common/default.jpg",
	  /*生日*/
	  birthday:"1970-01-01",
	  /* 注册时间 */
	  registerTime:"1970-01-01",
	  /* 学号 */
	  Number:"",
	  /* 昵称 */
	  nickName:"",
	  /* 角色名称 */
	  rolename:"",
	  editInfo:{
		  Number:"",
		  nickName:"",
		  birthday:""
	  },
     editAbout:{
    	 school:"",
    	 college:"",
    	 classes:"",
    	 hobby:""
     },
     courseList:[]
  },
  created:function(){
	  
	  /* 用于修改头像 */
	  /*=======================begin==========================*/
	  (function (factory) {
		  if (typeof define === 'function' && define.amd) {
		    define(['jquery'], factory);
		  } else if (typeof exports === 'object') {
		    // Node / CommonJS
		    factory(require('jquery'));
		  } else {
		    factory(jQuery);
		  }
		})(function ($) {

		  'use strict';

		  var console = window.console || { log: function () {} };

		  function CropAvatar($element) {
		    this.$container = $element;

		    this.$avatarView = this.$container.find('.avatar-view');
		    this.$avatar = this.$avatarView.find('img');
		    this.$avatarModal = $("body").find('#avatar-modal');
		    this.$loading = $("#page-wrapper").find('.loading');

		    this.$avatarForm = this.$avatarModal.find('.avatar-form');
		    this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
		    this.$avatarSrc = this.$avatarForm.find('.avatar-src');
		    this.$avatarData = this.$avatarForm.find('.avatar-data');
		    this.$avatarInput = this.$avatarForm.find('.avatar-input');
		    this.$avatarSave = this.$avatarForm.find('.avatar-save');
		    this.$avatarBtns = this.$avatarForm.find('.avatar-btns');

		    this.$avatarWrapper = this.$avatarModal.find('.avatar-wrapper');
		    this.$avatarPreview = this.$avatarModal.find('.avatar-preview');

		    this.init();
		  }

		  CropAvatar.prototype = {
		    constructor: CropAvatar,
		    support: {
		      fileList: !!$('<input type="file">').prop('files'),
		      blobURLs: !!window.URL && URL.createObjectURL,
		      formData: !!window.FormData
		    },

		    init: function () {
		      this.support.datauri = this.support.fileList && this.support.blobURLs;
		      
		      if (!this.support.formData) {
		        this.initIframe();
		      }

		      this.initTooltip();
		      this.initModal();
		      this.addListener();
		    },

		    addListener: function () {
		      this.$avatarView.on('click', $.proxy(this.click, this));
		      this.$avatarInput.on('change', $.proxy(this.change, this));
		      this.$avatarForm.on('submit', $.proxy(this.submit, this));
		      this.$avatarBtns.on('click', $.proxy(this.rotate, this));
		    },

		    initTooltip: function () {
		      this.$avatarView.tooltip({
		        placement: 'bottom'
		      });
		    },

		    initModal: function () {
		      this.$avatarModal.modal({
		        show: false
		      });
		    },

		    initPreview: function () {
		      var url = this.$avatar.attr('src');
		      this.$avatarInput.empty();
		      this.$avatarPreview.empty().html('<img src="' + url + '">');
		    },

		    initIframe: function () {
		      var target = 'upload-iframe-' + (new Date()).getTime(),
		          $iframe = $('<iframe>').attr({
		            name: target,
		            src: ''
		          }),
		          _this = this;

		      // Ready ifrmae
		      $iframe.one('load', function () {

		        // respond response
		        $iframe.on('load', function () {
		          var data;

		          try {
		            data = $(this).contents().find('body').text();
		          } catch (e) {
		            console.log(e.message);
		          }

		          if (data) {
		            try {
		              data = $.parseJSON(data);
		            } catch (e) {
		              console.log(e.message);
		            }

		            _this.submitDone(data);
		          } else {
		            _this.submitFail('Image upload failed!');
		          }

		          _this.submitEnd();

		        });
		      });

		      this.$iframe = $iframe;
		      this.$avatarForm.attr('target', target).after($iframe.hide());
		    },

		    click: function () {
		      this.$avatarInput.val('')
		      this.$avatarModal.modal('show');
		      this.$avatarWrapper.empty()
		      this.initPreview();
		    },

		    change: function () {
		      var files,
		          file;
		      this.active = false
		      if (this.support.datauri) {
		        files = this.$avatarInput.prop('files');
		        if (files.length > 0) {
		          file = files[0];

		          if (this.isImageFile(file)) {
		            if (this.url) {
		              URL.revokeObjectURL(this.url); // Revoke the old one
		            }

		            this.url = URL.createObjectURL(file);
		            this.startCropper();
		          }
		        }
		      } else {
		        file = this.$avatarInput.val();

		        if (this.isImageFile(file)) {
		          this.syncUpload();
		        }
		      }
		    },

		    submit: function () {
		      if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
		        return false;
		      }

		      if (this.support.formData) {
		        this.ajaxUpload();
		        return false;
		      }
		    },

		    rotate: function (e) {
		      var data;

		      if (this.active) {
		        data = $(e.target).data();

		        if (data.method) {
		          this.$img.cropper(data.method, data.option);
		        }
		      }
		    },

		    isImageFile: function (file) {
		      if (file.type) {
		        return /^image\/\w+$/.test(file.type);
		      } else {
		        return /\.(jpg|jpeg|png|gif)$/.test(file);
		      }
		    },

		    startCropper: function () {
		      var _this = this;

		      if (this.active) {
		        this.$img.cropper('replace', this.url);
		      } else {
		        this.$img = $('<img src="' + this.url + '">');
		        this.$avatarWrapper.empty().html(this.$img);
		        this.$img.cropper({
		          aspectRatio: 1,
		          preview: this.$avatarPreview.selector,
		          strict: false,
		          crop: function (data) {
		            var json = [
		                  '{"x":' + data.x,
		                  '"y":' + data.y,
		                  '"height":' + data.height,
		                  '"width":' + data.width,
		                  '"rotate":' + data.rotate + '}'
		                ].join();

		            _this.$avatarData.val(json);
		          }
		        });

		        this.active = true;
		      }
		    },

		    stopCropper: function () {
		      if (this.active) {
		        this.$img.cropper('destroy');
		        this.$img.remove();
		        this.active = false;
		      }
		    },

		    ajaxUpload: function () {
		      var url = this.$avatarForm.attr('action'),
		          data = new FormData(this.$avatarForm[0]),
		          _this = this;

		      $.ajax(url, {
		        headers: {'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}, 
		        type: 'post',
		        data: data,
		        dataType: 'json',
		        processData: false,
		        contentType: false,

		        beforeSend: function () {
		          _this.submitStart();
		        },

		        success: function (data) {
		          _this.submitDone(data);
		        },

		        error: function (XMLHttpRequest, textStatus, errorThrown) {
		          _this.submitFail(textStatus || errorThrown);
		        },

		        complete: function () {
		          _this.submitEnd();
		        }
		      });
		    },

		    syncUpload: function () {
		      this.$avatarSave.click();
		    },

		    submitStart: function () {
		      this.$loading.fadeIn();
		    },

		    submitDone: function (data) {
		      if ($.isPlainObject(data)) {
		        if (data.result) {
		          /*url 基本不变 ,加个时间重新发送请求*/
		          this.url = data.url+"?"+new Date();
				  /*修改主页面的信息*/
				  $('#userHeadpic', parent.document).attr("src",this.url)
		          if (this.support.datauri || this.uploaded) {
		            this.uploaded = false;
		            this.cropDone();
		          } else {
		            this.uploaded = true;
		            this.$avatarSrc.val(this.url);
		            this.startCropper();
		          }
		          this.$avatarInput.val('');
		        } else if (data.message) {
		          toastr.error(data.message)
		        }
		      } else {
		    	toastr.error("响应失败")
		      }
		    },

		    submitFail: function (msg) {
		      toastr.error(msg)
		    },

		    submitEnd: function () {
		      this.$loading.fadeOut();
		    },

		    cropDone: function () {
		      this.$avatarForm.get(0).reset();
		      this.$avatar.attr('src', this.url);
		      this.stopCropper();
		      this.$avatarModal.modal('hide');
		    },

		    alert: function (msg) {
		      toastr.error(msg)
		    }
		  };

		  $(function () {
		    return new CropAvatar($('#crop-avatar'));
		  });

		});
	  /*=======================end==========================*/
	  /*在页面创建时先获取用户的基本信息 */
	  this.getUserinfo()
	  
  },
  methods:{
	  getCourese:function(){
		this.$http.post("/admin/index/getCoureseByUser",{
		}).then(function(data){
			if(data.body.result){
				this.courseList = data.body.msg
			}
		})
	  },
		cancelmycourse:function(id){
			  swal({
					title : "确定要取消学习该课程吗?",
					text : "取消后你可以重新选择学习",
					type : "warning",
					showCancelButton : true,
					cancelButtonText : "取消",
					confirmButtonColor : "#DD6B55",
					confirmButtonText : "确认!",
					closeOnConfirm : false
				}, function() {
		            $.ajax({
		                url: "/index/deletemycourse",
		                type: "post",
		                data: { "courseId": id },
		                dataType: "json",
		                success: function(data) {
		                    if (data.result) {
		                        toastr.success(data.msg);
								swal({
									title : "操作成功!",
									text :  "选择的课程已取消",
									confirmButtonText : "确认",
									type : "success",
									timer : 1000
								});
								this.getCourese();
		                    }else{
								swal({
									title : "操作失败!",
									text : data.body.msg,
									confirmButtonText : "确认",
									type : "error",
									timer : 1000
								});
		                    }
		                    return;
		                }.bind(this)
		            });
				}.bind(this));
		},
	  getUserinfo:function(){
		  /*获取用户信息 */
		  this.$http.post('/admin/user/getUser',{}).then( function(data) {
			  /* 判断执行是否 成功 */
			  if(data.body.result){
				  this.imgsrc = data.body.msg[0].headpic,
				  this.birthday = data.body.msg[0].createtime || "",
				  this.registerTime = data.body.msg[0].createtime || "",
				  this.nickName = data.body.msg[0].nickname,
				  this.rolename = data.body.rolename,
				  this.Number = data.body.msg[0].number||""
				  this.editInfo.Number= this.Number,
				  this.editInfo.nickName = data.body.msg[0].emailname,
				  this.editInfo.birthday = this.birthday
				  this.school = data.body.msg[0].school || "",
				  this.college = data.body.msg[0].college || "",
				  this.classes = (data.body.msg[0].classes || ""),
				  this.hobby= data.body.msg[0].hobby || "",
				  this.editAbout.school =  this.school,
				  this.editAbout.college = this.college,
				  this.editAbout.classes = this.classes,
				  this.editAbout.hobby= this.hobby
			  }else{
				  toastr.error(data.body.msg);
			  }
		  })
	  },
	  editUserinfo:function(){
		  /*提交用户修改个人基本信息*/
		  this.$http.post('/admin/user/editUserInfo',{
			  nickname:$.trim(this.editInfo.nickName),
			  number:this.editInfo.Number,
			  birthday:this.editInfo.birthday
		  }).then( function(data) {
			  /* 判断执行是否 成功 */
			  if(data.body.result){
				  toastr.success("修改成功")
				  this.getUserinfo();
				  /*修改主页面的信息*/
				  $('#userName', parent.document).html(this.editInfo.nickName.substring(0,this.editInfo.nickName.lastIndexOf("@")))
				  $("#editUser-modal").modal('hide');
			  }else{
				  toastr.error(data.body.msg)
			  }
		  }) 
	  },
	  editAboutUserinfo:function(){
		  /*提交用户修改个人基本信息*/
		  this.$http.post('/admin/user/editAboutUser',{
			  school: $.trim(this.editAbout.school),
			  college:  $.trim(this.editAbout.college),
			  classes: $.trim( this.editAbout.classes),
			  hobby: $.trim(this.editAbout.hobby)
		  }).then( function(data) {
//			  console.log(data);
//			   alert(data.body.result);
			  /* 判断执行是否 成功 */
			  if(data.body.result){
				  toastr.success("修改成功")
				  this.school = this.editAbout.school,
				  this.college = this.editAbout.college,
				  this.classes =this.editAbout.classes,
				  this.hobby = this.editAbout.hobby
			  }else{
				  toastr.error(data.body.msg)
			  }
		  }) 
	  },
	 
  	   testtoaster:function(){
  		 toastr.success('Simple notification!')
  	   },
  	   onSelectChange:function(){
  		   console.log($('#birthday').val())
  	   }
  }
});
//日期范围限制
var birthday = {
    elem: '#birthday',
    format: 'YYYY-MM-DD',
    min: '1950-01-01 00:00:00',
    max: laydate.now(), //最大日期
//    istime: true,
    istoday: false,
    choose: function (datas) {
    	vm.editInfo.birthday = datas
//         end.min = datas; //开始日选好后，重置结束日的最小日期
//         end.start = datas //将结束日的初始值设定为开始日
    }
};
laydate(birthday);