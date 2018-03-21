function getAllUser(option,type,num=0){
	var d='';
	switch (type) {
	case 1:
		d='option='+option+'&starttime=&endtime=&mobile=&limit=';
		break;
	case 2:
		var starttime=new Date(Date.parse($('#start').html().replace(/-/g, "/"))).getTime()/1000;
		var endtime=new Date(Date.parse($('#end').html().replace(/-/g, "/"))).getTime()/1000;
		d='option=&starttime='+starttime+'&endtime='+endtime+'&mobile=&limit=';
		break;
	case 3:
		var mobile=$('.sear').val();
		d='option=&starttime=&endtime=&limit=&mobile='+mobile;
		break;
	case 4:
		//var limit=$('.next_page').val();
		d='option=&starttime=&endtime=&mobile=&limit='+num;
		break;
	default:
		d='option=&starttime=&endtime=&mobile=&limit=';
		break;
	}
	var content='';//储存内容
	var status='';//储存当前状态
	var zhuang='';//储存要操作的状态
	var u='getAllUser';
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		if(response['data']['res']){
    			 $.each(response['data']['res']['result'],function(k,v){
        			 switch(v['status']){
        			 	case '0':
        			 		status="未审核";
        			 		zhuang='<span class="jiny" data="'+v['man_id']+'" onclick="shows(this)">审核</span>';
        			 		break;
        			 	case '1':
        			 		status="正常用户";
        			 		zhuang='<span class="jiny" onclick="review('+v['man_id']+',\'close\')">禁用</span>'+
							'<span class="jief" onclick="getOneUser('+v['man_id']+')">修改</span>';
        			 		break;
        			 	case '-1':
        			 		status="失效";
        			 		zhuang='<span class="jiny" onclick="review('+v['man_id']+',\'open\')">解封</span>';
        			 		break;
        			 	defalt:;break;
        			 }
        			 content+='<ul class="l_list"><li class="tit_li flexTwo">'+v['man_id']+'</li>'	
        			 +'<li class="tit_li flexThree">'+v['openid']+'</li>'
        		     +'<li class="tit_li flexTwo">'+v['mobile']+'</li>'
        			 +'<li class="tit_li flexTwo">'+v['name']+'</li>'
        			 +'<li class="tit_li flexThree">'+rrahs.nowTime(v['jointime'])+'</li>'
        			 +'<li class="tit_li flexTwo">'+status+'</li>'
        			 +'<li class="tit_li flexThree">'+zhuang+'</li></ul>';		
        		 });
    			 $("#page").paging({
 					pageNo: num,
 					totalPage: response['data']['res']['Page'],
 					totalSize: response['data']['res']['CountPage'],
 					callback: function(num) {
 						getAllUser(option,4,num)
 					}
 				})
				var options='';
 				if(response['data']['qx']){
 					$.each(response['data']['qx'],function(k,v){
 						options +='<li onclick="addInput(this);addInputs(this)" data="'+v['roleid']+'">'+v['rolename']+'</li>';
 					})
 				}
 				var addre='';
 				if(response['data']['adre']['result']){
 					$.each(response['data']['adre']['result'],function(k,v){
 						addre +='<li onclick="addInput(this);" data="'+v['addid']+'">'+v['addname']+'--'+v['address']+'</li>';
 					})
 				}
 				var dianz='';
 				if(response['data']['kep']){
 					$.each(response['data']['kep'],function(k,v){
 						dianz +='<li onclick="addInput(this);" data="'+v['mid']+'">'+v['name']+'</li>';
 					})
 				}
 				$('.page_xian').show();
    		}else{
    			content='暂无数据';
    		}
    		$('.form_d .addrs').html(addre);
    		$('.form_d .sel').html(options);
    		$('.form_d .dian').html(dianz);
    		$('.mess_box').html(content);
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//审核通过
function review(id,status){
	var zhuang='';
	switch(status){
		case 'close':
			zhuang="禁用";break;
		case 'open':
			zhuang="解封";break;
		default:;break;
	}
	if(confirm('你确定要'+zhuang+'该用户么')){
		var u='UpdateUserStatus';
		var d='id='+id+'&status='+status;
		var f=function(res){
			var response=eval(res);
			if(response['status'] == '41011'){
	    		alert(response['msg']);
	    		return false;
	    	}else{
	    		getAllUser('','',0)
	    	}
		}
		rrahs.httpRequest(u,d,f);
	}else{
		return false;
	}
} 
//显示弹框内容
function shows(obj){
	var dataval=$(obj).attr('data');
	$('#userid').attr('data',dataval);
	$('.shadows').show();
	$('.shadow').show();
	$('.shw_addc').show();
}
//点击相应的权限 修改用户权限 并保存
function saves(){
	var roleid=$('.roleid').attr('data');//权限id
	var addrid=$('.addrid').attr('data');//角色id
	var userid=$('#userid').attr('data');//用户id
	var manid=$('.manid').attr('data');//用户id
	
	if(manid<0  || manid==null){
		manid='';
	}
	var u='editUser';
	var d='roleid='+roleid+'&addrid='+addrid+'&userid='+userid+'&manid='+manid;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		getAllUser('','',num=0)
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//获取需要修改的用户信息
function getOneUser(id){
	var jiaose='';
	var dianpu='';
	$('.edituser').show();
	var u='getUserManageOne';
	var d='id='+id;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		$('.ids').val(response['data']['res']['man_id']);
    		$('.name').val(response['data']['res']['name']);
    		$('.mobile').val(response['data']['res']['mobile']);
    		$('.number').val(response['data']['res']['number']);
    		$('.juese').val(response['data']['res']['rolename']);
    		$('.addid').val(response['data']['res']['addids']);
    		$.each(response['data']['qx'],function(k,v){
    			jiaose+='<li onclick="addInput(this);" data="'+v['roleid']+'">'+v['rolename']+'</li>';
    		});
    		$('.juese').attr('data',response['data']['res']['roleid']);
    		$('.form_d .jiaose').html(jiaose)
    		$.each(response['data']['adre']['result'],function(k,v){
    			dianpu+='<li onclick="addInput(this);" data="'+v['addid']+'">'+v['addname']+'--'+v['address']+'</li>';
    		});
    		$('.addid').attr('data',response['data']['res']['addid']);
    		$('.form_d .dianpu').html(dianpu)
    	}
	}
	rrahs.httpRequest(u,d,f);
}
//修改用户信息
function editUser(){
	var number=$('.number').val();//编号
	var mobile=$('.mobile').val();//手机号
	var juese=$('.juese').attr('data');//权限
	var addid=$('.addid').attr('data');//地址id
	var id=$('.ids').val();
	var u='editUserOne';
	var d='id='+id+'&number='+number+'&mobile='+mobile+'&juese='+juese+'&addid='+addid;
	var f=function(res){
		var response=eval(res);
		if(response['status'] == '41011'){
    		alert(response['msg']);
    		return false;
    	}else{
    		$('.edituser').hide();
    		alert('修改成功');
    		getAllUser('','',num=0)
    	}
	}
	rrahs.httpRequest(u,d,f);
}