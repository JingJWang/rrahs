//点击input显示ul
function showul(obj){
	if($(obj).next('ul').is(':visible')){
		$(obj).next('ul').hide();
	}else{
		$(obj).next('ul').show();
	}
}
//点击li  input放入值
function addInput(obj){
	$(obj).parent().prev().val($(obj).html());
	var dataval = $(obj).attr('data');
	$(obj).parent().prev('input').attr('data' ,dataval);
	$(obj).parent().hide();
}
function addInputs(obj){
	$(obj).parent().prev().val($(obj).html());
	var dataval = $(obj).attr('data');
	$(obj).parent().prev('input').attr('data' ,dataval);
	$(obj).parent().hide();
	if($(obj).parent().prev('input').attr('data')=='20'){
		$('.dianzhan').show();
	}else{
		$('.dianzhan').hide();
	}
}
//隐藏弹框
function hides(){
	//$('.shadow,.box').hide();
	$('.addAddres').hide();
	$('.upAddres').hide();
	$('.ruku').hide();
	$('.edituser').hide();
	$('.shadows').hide();
}
