/**
 * 
 */
Vue.http.options.emulateJSON = true;
new Vue({
	el:"body",
	data:{
		typelist:[{id:0,name:"创新创业",color:'green',textcolor:"#FFF"},{id:1,name:"产品运营",color:'#f3f3f4',textcolor:"#676a6c"},{id:2,name:"名书解读",color:'#f3f3f4',textcolor:"#676a6c"}],
		row:0,
		articleList:[],
		styleObject: {
			color: 'red',
		},
		showDetail:false,
		artInfo:[]
	},
	created:function(){
		this.getArticle();
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
		showArt:function(item){
			this.showDetail = true;
			this.artInfo = item
		},
		statuChange:function(item){
			for(var i=0;i<this.typelist.length;i++){
				if(item.id == this.typelist[i].id){
					this.typelist[i].color = "green";
					this.typelist[i].textcolor = "#FFF";
				}else{
					this.typelist[i].color = "#f3f3f4";
					this.typelist[i].textcolor = "#676a6c";
				}
			}
			this.row = item.id;
			this.getArticle();
		},
		getArticle:function(){
			this.$http.post("/admin/index/getArticle",{id:this.row}).then(function(data){
				if(data.body.result){
					this.articleList = data.body.msg
				}
			})
		}
	}
})