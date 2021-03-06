/**
 *  front 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
		username:"",
		password:""
	},
	created:function(){
		$.noConflict()
		jQuery('.zy-Slide').zySlide({ speed: 500 })
		.css('border', '0px solid blue')
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
		}
	}
})