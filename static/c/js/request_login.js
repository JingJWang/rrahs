/**
 * 
 */
var fall=41011;
var succ=41010;
var login={
		skip:function(addres){
			self.location.href=addres;
		},
		msg:function(msg,s){
			if( s == 0){
				$(".rember").text('');
				$(".rember").css('display','block');
				$(".rember").text(msg);
			}else{
				$(".rember").text('');
				$(".rember").css('display','none');
			}
		},
		check:function(){
			var user=$('#user').val();
			if(user == ''){
				login.msg('用户名不能为空!',0);
				return false;
			}
			var pwd=$('#pwd').val();
			if(pwd == ''){
				login.msg('密码不能为空!',0);
				return false;
			}
			var check=$('#check').val();
			if(check == ''){
				login.msg('随机验证码不能为空!',0);
				return false;
			}
			login.msg('',1);
			return 'check='+check+'&pwd='+pwd+'&user='+user;
		},		
		login:function(){
			var d=login.check();
			if(d == false){
				return false;
			}
			var u='checkLogin';
			var f=function response(res){
					if(fall == res.status){
						login.msg(res.msg,0);
					}
					if(succ == res.status){
						login.skip(res.url);
					}
			}
			rrwx.httpRequest(u, d, f)
		}
		
}