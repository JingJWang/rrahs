function loadFun(){
	var winH = $(window).height();
	var mobileH = winH - $('header').height();
	$('.mobile,.mo').height(mobileH);
}
function changBg(obj){
	$(obj).css('color','#fff').addClass('mbChane').siblings().css('color','#6a6a6a').removeClass('mbChane');
}
function changRBg(obj){
	$(obj).css('color','#4bddbe').siblings().css('color','#515151');
}