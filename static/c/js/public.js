//后台系统 菜单样式
function loadFun(num,snum){
	var winh = $(window).height();
	var conth = winh - 70;
	$('.cont').height(conth);
	//num 是左边大项
	if(num == 1){
		$('.l_li').eq(0).find('span').addClass('lisp_hov').next('b').addClass('clbhov');
	}
	// snum 大项的小项选择
	if(snum == 1){
		$('.left_li').eq(0).addClass('leftli_hov').siblings().removeClass('leftli_hov');	
	}
}