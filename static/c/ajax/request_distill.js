function getDistill(num){
	var d='';
	var content='';//储存内容
	var statu='';
	var name=$('.sear').val();
	if(name!='') {
		d='name='+name+'&limit='+num;
	}else{
		d='name=&limit='+num;
	}
	var u='getDistillList';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		if(response['data']){
    			 $.each(response['data']['result'],function(k,v){
        			 switch(v['deed']){
	        			 case '1':
	     			 		status="等待提货";
	     			 		break;
        			 	case '2':
        			 		status="运输中";
        			 		break;
        			 	case '3':
        			 		status="申请入库";
        			 		break;
        			 	case '4':
        			 		status="已入库";
        			 		break;
        			 	defalt:;break;
        			 }
        			 content+='<ul class="l_list">'
        			 +'<li class="tit_li flexTwo">'+v['id']+'</li>'		    				 
        		     +'<li class="tit_li flexTwo">'+v['shopName']+'</li>'
        			 +'<li class="tit_li flexThree">'+v['distName']+'</li>'
        			 +'<li class="tit_li flexTwo">'+status+'</li>'
        			 +'<li class="tit_li flexTwo"><span class="jiny" onclick="updateDistill('+v['id']+')">入库</span><span class="jief" onclick="select('+v['id']+')">查看</span></li></ul>';
        		 });
    			 $("#page").paging({
 					pageNo: num,
 					totalPage: response['data']['Page'],
 					totalSize: response['data']['CountPage'],
 					callback: function(num) {
 						getDistill(num)
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
//删除地址
function updateDistill(id){
	if(confirm('您确定要入库该笔订单么')){
		var u='checkDistill';
		var d='id='+id
		var f=function(res){
			var response=eval(res);
			if(response['status'] == '41011'){
	    		alert(response['msg']);
	    		return false;
	    	}else{
	    		alert('入库成功');
	    		return true;
	    	}
		}
	}else{
		return false;
	}
	rrahs.httpRequest(u,d,f);
}