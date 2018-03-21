function transfer(){
	var content='';
	var u='getDistillList';
	var d='';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '21011'){
    		return false;
    	}else{
    		$.each(response['data'],function(k,v){
				content = content + '<li class="crew" onclick="disName(this);" data="'+v['manage_id']+'">'+v['manage_name']+'</li>';
			});
			$('.crew_ul').html(content);
    	}
		var firstName = $('.crew').eq(0).html();
		var firstData = $('.crew').eq(0).attr('data');
		$('.crew_name').html(firstName);
		//$('.crew_name').attr('data',firstData);
		$('.distillId').val(firstData);;
	}
	rrwx.httpSynchroRequest(u,d,f);
}
function disName(obj){
	var didsValue=$(obj).html();
	var didsId=$(obj).attr("data");
	$('.crew_name').html(didsValue);
	$('.distillId').val(didsId);
	$('.crew_ul').hide();
}
function orderTran(){
	var orderIdS = $(".orderIdS").val();
	var shopOwnerId=$('.shopOwnerId').val();
	if(shopOwnerId==''){
		alert("数据不能为空");
		return false;
	}
	var distillId=$('.distillId').val();
	if(distillId==''){
		alert("提货员信息不能为空");
		return false;
	}
	var orderNum=$('.num').html();
	if(orderNum==''){
		alert("移交手机台数不能为空");
		return false;
	}
	var u='reqDistill';
	var d='oid='+orderIdS+'&shopId='+shopOwnerId+'&disId='+distillId+'&orderNum='+orderNum;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '21011'){
    		return false;
    	}else{
    		UrlGoto('distillSucc?id='+response['data']);
    	}
	}
	rrwx.httpSynchroRequest(u,d,f);
}