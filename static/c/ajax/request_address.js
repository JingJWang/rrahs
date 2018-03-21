function getAddressList(num){
	var d='';
	var content='';//储存内容
	var zhuang='';//储存状态
	var typeclass='';
	var times='';//储存时间
	var address=$('.sear').val();
	if(address!='') {
		d='address='+address+'&limit='+num;
	}else{
		d='address=&limit='+num;
	}
	var u='getAddressList';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		if(response['data']){
    			 $.each(response['data']['result'],function(k,v){
        			 switch(v['address_status']){
	        			 case '-1':
	     			 		status="地址停用";
	     			 		zhuang='<span class="jiny" onclick="">启用</span>';
	     			 		break;
        			 	case '1':
        			 		status="地址正常";
        			 		zhuang='<span class="jiny" onclick="upaddressBack('+v['address_id']+')">修改</span><span class="jief" onclick="del('+v['address_id']+')">删除</span>';
        			 		break;
        			 	case '0':
        			 		status="地址停用";
        			 		zhuang='<span class="jiny" onclick="">查看</span>';
        			 		break;
        			 	defalt:;break;
        			 }
        			 content+='<ul class="l_list">'
        			 +'<li class="tit_li flexTwo">'+v['address_id']+'</li>'		    				 
        		     +'<li class="tit_li flexTwo">'+v['address_name']+'</li>'
        		     +'<li class="tit_li flexTwo">'+v['address_number']+'</li>'
        			 +'<li class="tit_li flexThree">'+v['address_address']+'</li>'
        			 +'<li class="tit_li flexTwo">'+rrahs.nowTime(v['address_jointime'])+'</li>'
        			 +'<li class="tit_li flexTwo">'+status+'</li>'
        			 +'<li class="tit_li flexTwo">'+zhuang+'</li></ul>';		
        			 $('.add_name').val(response['data']['result'][k]['address_name']);
        			 $('.add_address').val(response['data']['result'][k]['address_address']);
        		 });
    			 $("#page").paging({
 					pageNo: num,
 					totalPage: response['data']['Page'],
 					totalSize: response['data']['CountPage'],
 					callback: function(num) {
 						getAddressList(num)
 					}
 				})
 				 $('.page_xian').show();
    		}else{
    			content='暂无数据';
    		}
    		$('.mess_box').html(content);
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//点击添加显示弹框内
function addressBack(state=0){
	$('.addAddres').show();
	$('.addAddres .add_name').val('');
	$('.addAddres .add_address').val('');
	var u='../../c/Statis/getTihuo';
	var d='';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		var jiaose='';
    		$.each(response['data'],function(k,v){
    			jiaose+='<li onclick="addInput(this);" data="'+v['manage_id']+'">'+v['manage_name']+'</li>'
    		})
    		$('.jiaose').html(jiaose);
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//删除地址
function del(id){
	if(confirm('您确定要删除该地址么')){
		alert('该地址下面有未完成订单,不能删除');
	}
	return true;
}
//添加地址
function intoAddress(){
	var name=$('.add_name').val();
	var address=$('.add_address').val();
	var number=$('.add_number').val();
	var manid=$('.juese').val();
	if(name=='' || address==''|| number==''|| manid==''){
		alert('信息不能为空');
		addressBack();
		return false;
	}
	var u='insertAddress';
	var d='name='+name+'&address='+address+'&number='+number+'&manid='+manid;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		alert('添加成功');
    		getAddressList(1);
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//点击修改显示弹框内
function upaddressBack(id){
	$('.upAddres').show();
	var u='getAddressOne';
	var d='id='+id;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		$('.add_name').val(response['data']['addName']);
    		$('.add_address').val(response['data']['addloca']);
    		$('.add_number').val(response['data']['number']);
    		$('.addid').val(response['data']['addid']);
    		$('.juese').val(response['data']['name']);
    		$('.juese').attr('data',response['data']['manid']);
    		var jiaose='';
    		$.each(response['data']['tihuo'],function(k,v){
    			jiaose+='<li onclick="addInput(this);" data="'+v['manage_id']+'">'+v['manage_name']+'</li>'
    		})
    		$('.jiaose').html(jiaose);
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//修改地址
function updateAddress(){
	var name=$('.upAddres .add_name').val();
	var address=$('.upAddres .add_address').val();
	var number=$('.upAddres .add_number').val();
	var manid=$('.upAddres .juese').attr('data');
	var id=$('.upAddres .addid').val();
	if(name=='' || address==''|| number==''|| manid==''){
		alert('信息不能为空');
		addressBack();
		return false;
	}
	var u='updateAddress';
	var d='id='+id+'&name='+name+'&address='+address+'&number='+number+'&manid='+manid;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		alert('修改成功');
    		getAddressList(0)
    	}
	}
	rrahs.httpRequest(u,d,f);
}