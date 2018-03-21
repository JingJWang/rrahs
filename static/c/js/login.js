var widh = $(window).height();
$('.shadow').height(widh);
function show(){
	$('.shadow').show();
}
//验证中文名字
function checkName(obj){
	var name=$(obj).val();
	var reg=/^[\u4E00-\u9FA5]{2,4}$/;
	if(name!='' && !reg.test(name)){
		$(obj).val("").focus();
		$('.redp').html('请正确填写你的中文名字!');
	}
}
//验证手机号
function testMoblie(obj){
	var myreg=/^[1][3,4,5,7,8][0-9]{9}$/;
	if(!myreg.test($(obj).val()) && $(obj).val() != ''){
		$('.redp').html('输入正确的手机号!');
		$(obj).val('');
	}else{
		return '&phone='+$(obj).val();
	}
}
//注册
function register(){
	var u='registerUser';
	var d=$("#register").serialize()+'&wxcode='+rrwx.getUrlParam('code');
	var f=function(res){
		if(res.status == 41011){
			$(".redp").html();
			$(".redp").html(res.msg);
		}
		if(res.status == 41010){
			$("#register").html('<div style="text-align:center;font-size:18px;color:red;">'+res.msg+'</div>');
		}
	}
	rrwx.httpRequest(u,d,f);
}