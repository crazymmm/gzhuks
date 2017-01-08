/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
		typelist:[],
		typeChildlist:[],
		row:-1,
		col:-1,
	    All1: {  
	    	'background-color': 'green',  
		    'color':"#FFF"
		},
		All21:{  
			'background-color': 'green',  
		    'color':"#FFF"
		},
		All22:{  
			'background-color': '#f3f3f4',  
		    'color':"#676a6c"
		},
		courseList:[],
		parInfo:[],
		showDetail:false,
		courseInfo:[]
	},
	created:function(){
		this.getCoucl();
		this.getCourese();
	},
	methods:{
		login:function(){
			if(this.username == ""){
				toastr.info("请输入用户名");
				return;
			}
			if(this.password == ""){
				toastr.info("请输入密码");
				return;
			}
			this.$http.post("/index/login",{
				username:this.username,
				password:this.password
			}).then(function(data){
				if(data.body.result){
					toastr.success(data.body.msg);
					jQuery("#modal-form").modal('hide');
					location.reload();
				}else{
					toastr.error(data.body.msg);
				}
			})
		},
		addmycourse:function(id){
            $.ajax({
                url: "/index/addmyselectcourse",
                type: "post",
                data: { "courseId": id },
                dataType: "json",
                success: function(data) {
                    if (data.result) {
                        toastr.success(data.msg);
						for(var i=0;i<this.courseList.length;i++){
							if(this.courseList[i].c_id == this.courseInfo.c_id){
								this.courseList[i].isCheck = true
								this.courseList[i].studentNum++
							}
						}
						this.courseInfo.isCheck = true
                    }else{
                    	if(data.msg == "nologin"){
                    		$("#modal-form").modal('show');
                    	}else{
                            toastr.error(data.msg);
                    	}
                    }
                    return;
                }.bind(this)
            });
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
								for(var i=0;i<this.courseList.length;i++){
									if(this.courseList[i].c_id == this.courseInfo.c_id){
										this.courseList[i].isCheck = false
										this.courseList[i].studentNum--
									}
								}
		                    }else{
		                    	if(data.msg == "nologin"){
		                    		$("#modal-form").modal('show');
		                    	}else{
									swal({
										title : "操作失败!",
										text : data.body.msg,
										confirmButtonText : "确认",
										type : "error",
										timer : 1000
									});
		                    	}
		                    }
		                    return;
		                }.bind(this)
		            });
				}.bind(this));
		},
		showCourseDetail:function(item){
			this.showDetail = true
			this.courseInfo = item
		},
		StatuCommon:function(item,row,col){
			if(1 == item){
			    this.All1 = {  
						'background-color': 'green',  
					    'color':"#FFF"
					}
				this.All21 = {  
					'background-color': 'green',  
				    'color':"#FFF"
				}
				this.All22 = {  
					'background-color': '#f3f3f4',  
				    'color':"#676a6c"
				}
			    this.typeChildlist = []
			}else if(21 == item){
				this.All21 = {  
						'background-color': 'green',  
					    'color':"#FFF"
					}
				this.All22 = {  
					'background-color': '#f3f3f4',  
				    'color':"#676a6c"
				}
			}else{
				this.All22 = {  
						'background-color': 'green',  
					    'color':"#FFF"
					}
				this.All21 = {  
					'background-color': '#f3f3f4',  
				    'color':"#676a6c"
				}
			}
			this.row = row
			this.col = col
			for(var i=0;i<this.typelist.length;i++){
				if(this.typelist[i].cl_id == row){
					this.typelist[i].color = "green";
					this.typelist[i].textcolor = "#FFF";
				}else{
					this.typelist[i].color = "#f3f3f4";
					this.typelist[i].textcolor = "#676a6c";	
				}
				for(var j=0;j<this.typelist[i].childnode.length;j++){
					this.typelist[i].childnode[j].color = "#f3f3f4";
					this.typelist[i].childnode[j].textcolor = "#676a6c";
				}
			}
			this.getCourese();
		},
		changeStatu:function(item,type){
			if(type == 0){
			    this.All1 = {  
						'background-color': '#f3f3f4',  
					    'color':"#676a6c"
					}
				this.All21 = {  
					'background-color': 'green',  
				    'color':"#FFF"
				}
				this.All22 = {  
					'background-color': '#f3f3f4',  
				    'color':"#676a6c"
				}
				this.parID = item.cl_id
				/*重置选择*/
				for(var i=0;i<this.typelist.length;i++){
					if(this.typelist[i].cl_id != item.cl_id){
						this.typelist[i].color = "#f3f3f4";
						this.typelist[i].textcolor = "#676a6c";
					}else{
						this.typelist[i].color = "green";
						this.typelist[i].textcolor = "#FFF";
					}
				}
				for(var i=0;i<item.childnode.length;i++){
					item.childnode[i].color = "#f3f3f4";
					item.childnode[i].textcolor = "#676a6c";
				}
				this.typeChildlist = item.childnode
				this.row = item.cl_id
			}else{
			    this.All1 = {  
						'background-color': '#f3f3f4',  
					    'color':"#676a6c"
					}
				this.All21 = {  
						'background-color': '#f3f3f4',  
					    'color':"#676a6c"
					}
				this.All22 = {  
					'background-color': '#f3f3f4',  
				    'color':"#676a6c"
				}
				this.col = item.cl_id
				for(var i=0;i<this.typeChildlist.length;i++){
					if(item.cl_id != this.typeChildlist[i].cl_id){
						this.typeChildlist[i].color = "#f3f3f4";
						this.typeChildlist[i].textcolor = "#676a6c";
					}else{
						this.typeChildlist[i].color = "green";
						this.typeChildlist[i].textcolor = "#FFF";
					}

				}
			}
			this.getCourese();
		},
		getCoucl:function(){
			this.$http.post("/admin/index/couclassify",{}).then(function(data){
				if(data.body.result){
					this.typelist = data.body.msg
				}
			})
		},
		getCourese:function(){
			this.$http.post("/admin/index/getCourese",{
				row:this.row,
				col:this.col
			}).then(function(data){
				if(data.body.result){
					this.courseList = data.body.msg
				}
			})
		}
	}
})