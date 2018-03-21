/**
 * 
 */

var rrwx={
	getUrlParam:function (name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
			var r = window.location.search.substr(1).match(reg);  //匹配目标参数
			if (r != null) return unescape(r[2]); return null; //返回参数值
	},
	httpRequest:function (u,d,f){
		var result='';
		$.ajax({
	        url:u,
	        type:"POST",
	        dataType:"json",
	        data:d,
	        beforeSend: function(){
	        },
	        success:function(res){	        	
	        	  f(res);
	        },
	        complete: function(res){  
	       	 	return result;
	       	},
	        error:function(msg){
	        }
	    });
	},
	httpSynchroRequest:function (u,d,f){
		var result='';
		$.ajax({
	        url:u,
	        type:"post",
	        dataType:"json",
	        data:d,
	        async: false,
	        beforeSend: function(){
	        },
	        success:function(res){	
	        	  f(res);
	        },
	        complete: function(res){ 
	       	},
	        error:function(msg){
	        	return false;
	        }
	    });
	},	
}
