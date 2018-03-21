//用户在修改价格后取消订单 不操作
function useCancle(){
	 $('.shadow').css('display','none');
	 $('.change').hide();
}
//用户确认报价 跳转页面
function yesOrder(id){
	var u='hShadow';
	var d='id='+id;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '21011'){
    		alert(response['msg']);
    		$('.sureb').attr("onclick","yesOrder("+id+")");
    		return false;
    	}else{
    		window.location.reload();
    	}
	}
	rrwx.httpRequest(u,d,f);
}
function addCheck(obj,status){
	switch(status){
		case 1:
			$(obj).attr('onblur','checkName()');
			break;
		case 2:
			$(obj).attr('onblur','checkKhh()');
			break;
		default :break;
	}
}
//回收商确认
function cusDeal(id){
	if(confirm("您确定要成交此订单么")){
		$('.sure').removeAttr("onclick");
		var u='shopOrder';
		var d='id='+id;
		var f=function(res){
			var response=eval(res);
			if(response['status'] == '21011'){
	    		alert(response['msg']);
	    		$('.sureb').attr("onclick","yesOrder("+id+")");
	    		return false;
	    	}else{
	    		window.location.assign("orderSee");
	    	}
		}
		rrwx.httpRequest(u,d,f);
	}else{
		return false;
	}
}
//9在订单详情页面取消订单
function cancles(id){
	if(confirm("您确定要取消此订单么")){
		$('.cancel').removeAttr("onclick");
		var u='cancles';
		var d='id='+id;
		var f=function(res){
			var response=eval(res);
			if(response['status'] == '21011'){
	    		alert(response['msg']);
	    		$('.cancel').attr("onclick","cancles("+id+")");
	    		return false;
	    	}else{
	    		window.location.assign("getOrderDetail?id="+id);
	    	}
		}
		rrwx.httpRequest(u,d,f);
	}else{
		return false;
	}
}