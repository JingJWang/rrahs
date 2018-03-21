var rrahs={
	getUrlParam:function (name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
			var r = window.location.search.substr(1).match(reg);  //匹配目标参数
			if (r != null) return unescape(r[2]); return null; //返回参数值
	},
	httpRequest:function (u,d,f,m='post'){
		var result='';
		$.ajax({
	        url:u,
	        type:m,
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
	//时间转换 月日
	nowTime:function(uDatas){
		var date = new Date(uDatas);  
	    date.setTime(uDatas * 1000);  
	    var y = date.getFullYear();      
	    var m = date.getMonth() + 1;      
	    m = m < 10 ? ('0' + m) : m;      
	    var d = date.getDate();      
	    d = d < 10 ? ('0' + d) : d;      
	   /* var h = date.getHours();    
	    h = h < 10 ? ('0' + h) : h;    
	    var minute = date.getMinutes();    
	    var second = date.getSeconds();    
	    minute = minute < 10 ? ('0' + minute) : minute;      
	    second = second < 10 ? ('0' + second) : second;    */ 
	    //return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;
	    return y + '年' + m + '月' + d+'日';
	},
}
	
