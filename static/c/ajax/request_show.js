function getUserKind(num){
	var d='';
	var content='';//储存内容
	var zhuang='';//储存状态
	var wxname='';
	var phone='';//手机号
	var mobile=$('.sear').val();
	if(mobile!='') {
		d='mobile='+mobile+'&limit='+num;
	}else{
		d='mobile=&limit='+num;
	}
	var u='getUserKind';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		if(response['data']){
    			 $.each(response['data']['result'],function(k,v){
        			 switch(v['status']){
        			 	case '1':
        			 		status="正常用户";
        			 		zhuang='<span class="jiny" onclick="review('+v['uid']+',\'close\')">禁用</span>';
        			 		break;
        			 	case '-1':
        			 		status="失效用户";
        			 		zhuang='<span class="jiny" onclick="review('+v['uid']+',\'open\')">解封</span>';
        			 		break;
        			 	defalt:;break;
        			 }
        			 if(v['wxname'] == ''){
        				 wxname='未命名或没有获取到';
        			 }else{
        				 wxname=v['wxname'];
        			 }
        			 if(v['mobile'] == ''){
        				 phone='未绑定';
        			 }else{
        				 phone=v['mobile'];
        			 }
        			 content+='<ul class="l_list"><li class="tit_li flexTwo">'+v['uid']+'</li>'		    				 
        		     +'<li class="tit_li flexTwo">'+wxname+'</li>'
        			 +'<li class="tit_li flexTwo">'+phone+'</li>'
        			 +'<li class="tit_li flexThree">'+rrahs.nowTime(v['jointime'])+'</li>'
        			 +'<li class="tit_li flexTwo">'+status+'</li>'
        			 +'<li class="tit_li flexThree">'+zhuang+'</li></ul>';		
        		 });
    			 $("#page").paging({
 					pageNo: num,
 					totalPage: response['data']['Page'],
 					totalSize: response['data']['CountPage'],
 					callback: function(num) {
 						getUserKind(num)
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
//禁用
function review(id,status){
	var zhuang='';
	if(status=='close'){
		zhuang="禁用";
	}else{
		zhuang="解封";
	}
	if(confirm('你确定要'+zhuang+'该用户么')){
		var u='updateUserKind';
		var d='id='+id+'&status='+status;
		var f=function(res){
			var response=eval(res);
			if(response['status'] == '41011'){
	    		alert(response['msg']);
	    		return false;
	    	}else{
	    		window.location.reload();
	    	}
		}
		rrahs.httpRequest(u,d,f);
	}else{
		return false;
	}
} 