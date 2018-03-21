	 //平台、设备和操作系统
    var system = {
        win: false,
        mac: false,
        xll: false,
        ipad:false
    };
    //检测平台
    var p = navigator.platform;
    system.win = p.indexOf("Win") == 0;
    system.mac = p.indexOf("Mac") == 0;
    system.x11 = (p == "X11") || (p.indexOf("Linux") == 0);
    system.ipad = (navigator.userAgent.match(/iPad/i) != null)?true:false;
    //跳转语句，如果是PC访问就自动跳转到所要访问的页面http://www.电脑.com
    if (system.win || system.mac || system.xll||system.ipad) {
			(function (doc, win) {
				var docEl = doc.documentElement,  
				resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',  
				recalc = function () {  
					var clientWidth = 414; 
					if (!clientWidth) return;  
					docEl.style.fontSize = 20 * (clientWidth / 320) + 'px';  
				}; 
				if (!doc.addEventListener) return;  
				win.addEventListener(resizeEvt, recalc, false);  
				doc.addEventListener('DOMContentLoaded', recalc, false);  
			})(document, window);
    } else {	
			(function (doc, win) {
				var docEl = doc.documentElement,  
				resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',  
				recalc = function () {  
					var clientWidth = docEl.clientWidth; 
					if (!clientWidth) return;  
					docEl.style.fontSize = 20 * (clientWidth / 320) + 'px';  
				}; 
				if (!doc.addEventListener) return;  
				win.addEventListener(resizeEvt, recalc, false);  
				doc.addEventListener('DOMContentLoaded', recalc, false);  
			})(document, window);
			$('.h_p_box::-webkit-scrollbar').css('width',0);
   }
//跳转
var UrlGoto=function name(url) {
  if(url != ''){
    location.href=url;
  }else{
    return '路径为空!'; 
  }
}
//时间转换年月日时分秒
function formatDate(nS)  {     
	return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');    
}  
//时间转换 月日时分
function nowTime(now) {
	var now = new Date(now * 1000);
	var month = now.getMonth() + 1;
	var date = now.getDate();
	var hour = now.getHours();
	var minute = now.getMinutes();
	//return   year+"年"+(month,2)+"月"+fixZero(date,2)+"日    "+fixZero(hour,2)+":"+fixZero(minute,2)+":"+fixZero(second,2);
	return   month+"."+date+"&nbsp;&nbsp;&nbsp;&nbsp;"+hour+":"+minute; 
}