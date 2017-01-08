/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
	},
	created:function(){

	},
	methods:{
		refresh:function(){
			location.reload();
		},
		addCL:function(){
            swal({
                title: "新建课程分类",
                text: "请输入分类名称:",
                type: "input",
				showCancelButton : true,
				cancelButtonText : "取消",
				confirmButtonColor : "#DD6B55",
				confirmButtonText : "创建",
                closeOnConfirm: false,
                animation: "slide-from-top",
                inputPlaceholder: "名称长度1-10之间"
            }, function(inputValue) {
                if (inputValue === false)
                    return
                if (inputValue.length < 1 || inputValue.length > 10) {
                	toastr.warning("名称长度1-10之间");
                    return 
                }
                this.$http.post("/admin/index/addcouclassify", {
                	name: inputValue
                }).then(function (data) {
                    if (data.body.result) {
						swal({
							title : "操作成功!",
							text :  "分类已创建",
							confirmButtonText : "确认",
							type : "success",
							timer : 1000
						});
						this.refresh()
                    } else {
                        toastr.error(data.body.msg);
                    }
                })
            }.bind(this));
		},
		saveList:function(){
		  var r = $('#nestable').nestable('serialize'); 
       	  this.$http.post("/admin/index/saveCllist",{
    		  data:r
    	  }).then(function(data){
    		  if(data.body.result){
    			  toastr.success("保存成功")
    			  this.refresh();
    		  }else{
    			  toastr.error(data.body.msg)
    		  }
    	  })
		}
	}
})