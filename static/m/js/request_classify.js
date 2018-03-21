function checkForm(){
	var datas=$('#attr').serialize();
	//获取单选的值 与原来的值作比较
	var yuandan=$('.yuan_hideInp').val();
	var dan=$('.sh_hideInp').val();
	//获取多选的值与原来的值作比较
	var yuanChoose=$('.yuan_manyChoose').val();
	var manyChoose=$('.manyChoose').val();
	var manyChoosecon=$('.manychoVal').val();
	if(yuandan == dan && yuanChoose==manyChoose){
		alert("参数没有变化,无需提交");
		return false;
	}else{
		if(confirm("参数已经发生变化,您确定修改么")){
			$('.lastBtn').removeAttr("onclick");
			var u='saveNextAgain';
			var d=datas;
			var f=function(res){
				var response=eval(res);
				if(response['status'] == '21011'){
	        		alert(response['msg']);
	        		$('.lastBtn').attr("onclick","checkForm()");
	        		return false;
	        	}else{
	        		window.location.assign("getOrderDetail?code=1&id="+response['data']);
	        	}
			}
			rrwx.httpSynchroRequest(u,d,f);
		}else{
			return false;
		}
	}
}

function checkCus(){
	var datas=$('#attr').serialize();
	var dan=$('.sh_hideInp').val();
	if(dan==''){
		alert("您没有选择故障内容");
		return false;
	}else{
		if(confirm("故障内容已经选好了,您确定要提交么")){
			$('.lastBtn').removeAttr("onclick");
			var u='saveSes';
			var d=datas;
			var f=function(res){
				var response=eval(res);
				if(response['status'] == '21011'){
	        		alert(response['msg']);
	        		$('.lastBtn').attr("onclick","checkCus()");
	        		return false;
	        	}else{
	        		window.location.assign("GetOffer");
	        	}
			}
			rrwx.httpSynchroRequest(u,d,f);
		}else{
			return false;
		}
	}
	
}