//1在订单页面查看所有订单信息
function cancleOrder(id){
	if(confirm("您确定要取消此订单么")){
		$('.canList').removeAttr("onclick");
		var u='cancleOrder';
		var d='id='+id;
		var f=function(res){
			var response=eval(res);
			if(response['status'] == '21011'){
	    		alert(response['msg']);
	    		$('.canList').attr("onclick","cancleOrder("+id+")");
	    		return false;
	    	}else{
	    		window.location.assign("getList");
	    	}
		}
		rrwx.httpRequest(u,d,f);
	}else{
		return false;
	}
}