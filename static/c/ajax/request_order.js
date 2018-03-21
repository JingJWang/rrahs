//第一个参数first 是选择 当天,最近一月  最近一周 first= day week month
//第二个参数 second 是订单状态 已提交 已完成
//第三个参数 ywc 是取已成交的时间   wwc 是取未成交的时间  
//第四个参数 搜索名字或者手机号 
//第五个参数是 分页
function getOrderList(first,second,three,throu,num){
	var d='';
	var throu=$('.sear').val();
	if(three =="ywc"){
		var ystar=new Date(Date.parse($('#ystart').html().replace(/-/g, "/"))).getTime()/1000;
		var yend=new Date(Date.parse($('#yend').html().replace(/-/g, "/"))).getTime()/1000;
		d='first='+first+'&status='+second+'&ystart='+ystar+'&yend='+yend+'&wstart=&wend=&mobile='+throu+'&limit='+num;
	}else if(three =="wwc"){
		var ystar=new Date(Date.parse($('#wstart').html().replace(/-/g, "/"))).getTime()/1000;
		var yend=new Date(Date.parse($('#wend').html().replace(/-/g, "/"))).getTime()/1000;
		d='first='+first+'&status='+second+'&ystart=&yend=&wstart='+wstar+'&wend='+wend+'&mobile='+throu+'&limit='+num;
	}else{
		d='first='+first+'&status='+second+'&ystart=&yend=&wstart=&wend=&mobile='+throu+'&limit='+num;
	}
	var content='';//储存内容
	var zhuang='';//储存状态
	var typeclass='';
	var times='';//储存时间
	var u='getOrderList';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		if(response['data']){
    			 $.each(response['data']['result'],function(k,v){
        			 switch(v['orderstu']){
        			 	case '-1':
	     			 		status="已取消";
	     			 		zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
	     			 		times=rrahs.nowTime(v['jointime']);
	     			 		break;
	        			case '1':
	     			 		status="已提交,待验机";
	     			 		zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
	     			 		times=rrahs.nowTime(v['jointime']);
	     			 		break;
        			 	case '2':
        			 		status="已验机,待确认";
        			 		zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
        			 		times=rrahs.nowTime(v['jointime']);
        			 		break;
        			 	case '10':
        			 		if(v['voustatus']!=1){
        			 			status="已成交,待打款";
        			 			zhuang='<span class="jiny" onclick="showbank('+v['oid']+')">打款</span>';
        			 		}else{
        			 			status="已成交,已打款";
        			 			zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
        			 		}
        			 		times=rrahs.nowTime(v['dealtime']);
        			 		break;
        			 	case '11':
        			 		status="待提走";
        			 		zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
        			 		times=rrahs.nowTime(v['jointime']);
        			 		break;
        			 	case '12':
        			 		status="已收货";
        			 		zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
        			 		times=rrahs.nowTime(v['jointime']);
        			 		break;
        			 	case '13':
        			 		status="已入库";
        			 		zhuang='<span class="jiny"><a href="../../m/Order/getOrderDetail?id='+v['oid']+'">查看</a></span>';
        			 		times=rrahs.nowTime(v['jointime']);
        			 		break;
        			 	defalt:;break;
        			 }
        			 content+='<ul class="l_list">'
        			 +'<li class="tit_li flexTwo">'+v['oid']+'</li>'		    				 
        		     +'<li class="tit_li flexThree">'+v['number']+'</li>'
        			 +'<li class="tit_li flexThree">'+v['goodsname']+'</li>'
        			 +'<li class="tit_li flexTwo">'+v['mobile']+'</li>'
        			 +'<li class="tit_li flexTwo">'+v['cnumber']+'</li>'
        			 +'<li class="tit_li flexThree">'+v['addid']+'</li>'
        			 +'<li class="tit_li flexTwo">'+v['price']/100+'</li>'
        			 +'<li class="tit_li flexTwo">'+times+'</li>'
        			 +'<li class="tit_li flexTwo">'+status+'</li>'
        			 +'<li class="tit_li flexThree">'+zhuang+'</li></ul>';		
        		 });
    			 $("#page").paging({
 					pageNo: num,
 					totalPage: response['data']['Page'],
 					totalSize: response['data']['CountPage'],
 					callback: function(num) {
 						getOrderList('','','','',num)
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
//显示弹框内容
function showbank(id){
	$('#orderid').val(id);
	$('.ruku').show();
}
//添加打款凭证后保存
function editOrder(){
	var id=$('#orderid').val();
	var yhmz=$('.yhmz').val();
	var yhlsh=$('.yhlsh').val();
	var u='voucher';
	var d='dkyh='+yhmz+'&dklsh='+yhlsh+'&orderId='+id;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		getOrderList('','','','',1);
    	}
	}
	rrahs.httpRequest(u,d,f);
}