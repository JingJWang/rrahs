function updateDistill(id){ 
		if(confirm("你确定要提走该批订单么")){
			$('.foot_a').removeAttr("onclick");
			var u='updateDistill';
			var d='id='+id;
			var f=function(res){
				var response=eval(res);
				if(response['status'] == '21011'){
	        		alert(response['msg']);
	        		$('.foot_a').attr("onclick","updateDistill("+id+")");
	        		return false;
	        	}else{
	        		window.location.reload();
	        	}
			}
			rrwx.httpSynchroRequest(u,d,f);
		}else{
			return false;
		}	
}