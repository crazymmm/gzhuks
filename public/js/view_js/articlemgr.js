/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
		isCheckAll:false,
		artList:[],
		totalrows:0,
		operate:"add",
		artid:0
	},
	created:function(){
	  	var ue = UE.getEditor( 'editor', {
			//false出现滚动条
	        autoFloatEnabled: false,
	        autoHeightEnabled: false,
	        wordCount: false,
	        autotypeset: {
	            removeEmptyline: true
	        },
	        initialFrameHeight: 300
	    });
	  	this.getArticle();
	},
	methods:{
		checkedAll:function(){
			for(var i=0;i < this.artList.length;i++){
				this.artList[i].check = !this.isCheckAll
			}
		},
		getArticle:function(){
			  this.$http.post('/admin/index/getArticle',{}).then( function(data) {
				  /* 判断执行是否 成功 */
				  if(data.body.result){
					  toastr.success("获取数据成功");
					  this.artList = data.body.msg
					  this.totalrows = this.artList.length
				  }else{
					  toastr.error(data.body.msg);
				  }
			  })
		},
		createArticle:function(){
	    	  if($("input[name='name']").val().length == 0 || $("input[name='name']").val().length > 50){
	    		  toastr.error("标题长度为1至50个字符内");
	    		  return
	    	  }
	    	  if($("input[name='keyword']").val().length > 100){
	    		  toastr.error("关键字要大小于100");
	    		  return
	    	  }
			  if(UE.getEditor('editor').getContent() == ""){
				  toastr.warning("请填写文章内容");
				  return
			  }
			  if(this.operate == 'add'){
		            this.$http.post("/admin/index/createArticle", {
		            	name: $("input[name='name']").val(),
		            	content:$.trim(UE.getEditor('editor').getContent()),
		            	keyword:$("input[name='keyword']").val(),
		            	type:$("#artType").val()
		            }).then(function (data) {
		                if (data.body.result) {
							swal({
								title : "操作成功!",
								text :  "文章已创建",
								confirmButtonText : "确认",
								type : "success",
								timer : 1000
							});
							$("#modal-form").modal('hide');
							/*初始化*/
							$(".myform")[0].reset()
							UE.getEditor('editor').setContent("",false)
							this.getArticle()
		                } else {
		                    toastr.error(data.body.msg);
		                }
		            })
			  }else{
		            this.$http.post("/admin/index/editArticle", {
		            	name: $("input[name='name']").val(),
		            	content:$.trim(UE.getEditor('editor').getContent()),
		            	keyword:$("input[name='keyword']").val(),
		            	type:$("#artType").val(),
		            	id:this.artid
		            }).then(function (data) {
		                if (data.body.result) {
							swal({
								title : "操作成功!",
								text :  "文章已编辑",
								confirmButtonText : "确认",
								type : "success",
								timer : 1000
							});
							$("#modal-form").modal('hide');
							/*初始化*/
							$(".myform")[0].reset()
							UE.getEditor('editor').setContent("",false)
							this.getArticle()
		                } else {
		                    toastr.error(data.body.msg);
		                }
		            })
			  }

		},
		deleteArticle:function(item){
			  swal({
					title : "确定要删除这篇文章吗?",
					text : "你将不能恢复本次操作!",
					type : "warning",
					showCancelButton : true,
					cancelButtonText : "取消",
					confirmButtonColor : "#DD6B55",
					confirmButtonText : "确认, 删除!",
					closeOnConfirm : false
				}, function() {
					this.$http.post("/admin/index/deleteArticle",{
						id:item
					}).then(function(data){
						if (data.body.result) {
							swal({
								title : "操作成功!",
								text :  "选择的文章已删除",
								confirmButtonText : "确认",
								type : "success",
								timer : 1000
							});
							this.getArticle();
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
		init:function(){
			this.operate='add'
			$(".myform")[0].reset()
			UE.getEditor('editor').setContent("",false)
		},
		editArticle:function(item){
			this.operate = "edit"
			this.artid = item.art_id
        	$("input[name='name']").val(item.title)
        	UE.getEditor('editor').setContent(item.content,false);
        	$("input[name='keyword']").val(item.keyword)
        	$("#artType").val(item.type)
        	$("#modal-form").modal('show');
		}
	}
})