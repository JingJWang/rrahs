//验证验证码
function changeColor(obj){
	if($('#imgcode').val()!= ''){
		$(obj).attr('onblur','checkMobile(this)')
	}else{
		alert('请输入图像验证码!');
		$(obj).val('');
	}
}
//验证手机号
function checkMobile(obj){
	var re = /^1\d{10}$/;
	var mobNum = $(obj).val();
	if (re.test(mobNum)) {
		$(obj).next('input').attr('onclick','settime(this);Messyz();').css('color','#ff4e5e');
	}else{
		$(obj).val('');
		alert('手机号码错误,请重新输入!');
	};
}
//验证
var countdown=60;  
function settime(val) {  
	if($('.inf_phonew').val() == ''){
		alert('请输入您的手机号码！');
		return false;
	}else{
		if (countdown == 0) {
	        val.removeAttribute("disabled");  
	        val.value="获取验证码";  
	        countdown = 60;  
	        return false;  
	    } else {  
	        val.setAttribute("disabled", true);  
	        val.value="重新发送(" + countdown + ")";  
	        countdown--;  
	    }
	    setTimeout(function() {  
	        settime(val);  
	    },1000);
	}
}
//短信验证
function Messyz(){
	var mobile=$('.inf_phonew').val();
    var yzm=$('#imgcode').val();
    //var t = $("#order").serialize();
	var u='Messyz';
	var d='mobile='+mobile+'&yzm='+yzm;
	var f=function(res){
   	 var response=eval(res)
        if(response['status'] == '21010'){
        	return true;
        }else{
   		 alert(response['msg']);
   		 return false;
   	   }
   }
	rrwx.httpRequest(u,d,f);
}

//button提交
function checkFormAll(){
	var datas=$('#order').serialize();
	if($('.wd_inp').val()==''){
		alert("网点编号不能为空");
		return false;
	}
	var u='inftxyzm';
	var d=datas;
	var f=function(res){
		var response=eval(res)
		if(response['status'] == '21012'){
			$('.pos_tk').css('display','block');
		}
    	if(response['status'] == '21011' || response['status'] == '21012'){
    		alert(response['msg']);
    		return false;
    	}else{
    		alert('提交订单');
    		$("#order").submit();
    		return true;
    	}
	}
	rrwx.httpRequest(u,d,f);
}
//弹框获取屏幕高
function loadhei(){
	var winh = $(window).height();
	$('.pos_tk').height(winh);
	if($('.pos_tk').is(':visible')){
		$('body').css('overflow','hidden');
		$('footer').hide();
	}else{
		$('body').css('overflow','auto');
		$('footer').show();
	}
}
//点击按钮关闭弹框
function hideshadow(){
	if($('.inf_txyzm,.inf_phonew,.inf_yzm').val()==''){
		alert("信息不能为空");
		return false;
	}
	var txyzm=$('#imgcode').val();
	var mobile=$('.inf_phonew').val();
	var yzm=$('.inf_yzm').val();
	var u='/m/user/upUserMobile';
	var d='inf_txyzm='+txyzm+'&inf_phonew='+mobile+'&inf_yzm='+yzm;
	var f=function(res){
		var response=eval(res)
    	if(response['status'] == '21011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		$('.pos_tk').hide();
    		$('body').css('overflow','auto');
    		$('footer').show();
    		checkFormAll();
    		return true;
    	}
	}
	rrwx.httpRequest(u,d,f);
}
//取消注册
function cancleRegis(){
	$('.pos_tk').hide();
	$('body').css('overflow','auto');
	/*$('footer').hide();
	setInterval(function(){  //使用  setTimeout（）方法设定定时2000毫秒
		window.location.reload();//页面刷新
	},5000);*/
}
//姓名验证
function checkName(){
	var name=$('#username').val();
	var reg=/^[\u4E00-\u9FA5]{2,4}$/;
	if(name!='' && !reg.test(name)){
		$('.inf_name').val("").focus();
		alert('请正确填写你的中文名字');
	}
}
//开户行验证
function checkKhh(){
	var khh=$('#bankname').val();
	var reg=/^[\u4E00-\u9FA5]{4,50}$/;
	if(khh!='' && !reg.test(khh)){
		$('.inf_khh').val("").focus();
		alert('请输入正确的开户银行');
	}
}
//卡号验证
function checkYhkh(){
	var yhkh=$('#banknumber').val().replace(/[ ]/g,"");
	var reg = /^([1-9]{1})(\d{14}|\d{18})$/;
	if(yhkh!='' && !reg.test(yhkh)){
		$('.inf_yhkh').val("").focus();
		alert('请输入正确的银行卡号');
	}
}