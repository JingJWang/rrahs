//1 用户端操作
function choose(obj){
	//获取屏幕距离顶部高
	var marhei = $('.title').height() + $('.header').height();
	//点击分类信息，让下一个内容到顶部
    if($(obj).parent().parent().hasClass('firstClass')){
    	//添加点击效果
		$(obj).css('color','#fff').addClass('choHover').siblings().css('color','#444').removeClass('choHover');
		//滑动
    	$('html,body').animate({scrollTop: $(obj).parent().parent().next().offset().top-marhei+'px'},500);
    }else if($(obj).parent().parent().prev().find('input').val() == ''){
    		alert('请按顺序选择！');
    		return false;
    }else{
    	//添加点击效果
		$(obj).css('color','#fff').addClass('choHover').siblings().css('color','#444').removeClass('choHover');
		$('html,body').animate({scrollTop: $(obj).parent().parent().next().offset().top-marhei+'px'},500);
	}
	//把值给input
	var chilrName = $(obj).html();			//名字
	var childNum = $(obj).attr('name');		//id
	$(obj).parent().prev('.hiddenInp').attr({'value':childNum});
	$(obj).parent().prev('.hiddenInp').attr({'data-childName':chilrName,'data-childNum':childNum});
	//判断最后一个单选项是   判断按钮显示
	if($('.lastClass').find('input').val() != ''){
		$('.lastBtn').css({'background':'#30d3b6','color':'#fff'});
	}else{
		$('.lastBtn').css({'background':'#d1d1d1','color':'#fff'})
	}
	//循环单选
	var radArr = [];
	var radeHtml = [];
	$('.classify .choHover').each(function(){
		radeHtml += $(this).html()+',';
		radArr += $(this).attr('name')+',';
	});
	$('.sh_hideInp').val(radArr);
	$('.sh_hideInp').attr('data',radeHtml);
	$('.sh_hideInpVal').val(radeHtml);
}
//9 商户端修改操作
function choose_s(obj){
	//获取屏幕距离顶部高
	var marhei = $('.title').height() + $('.header').height();
	var marhei = $('.title').height() + $('.header').height();
	//添加点击效果
	$(obj).css('color','#fff').addClass('choHover').siblings().css('color','#444').removeClass('choHover');
	$('html,body').animate({scrollTop: $(obj).parent().parent().next().offset().top-marhei+'px'},500);
	//把值给input
	var chilrName = $(obj).html();			//名字
	var childNum = $(obj).attr('name');		//id
	$(obj).parent().find('.hiddenInp').attr({'value':childNum});
	$(obj).parent().find('.hiddenInp').attr({'data-childName':chilrName,'data-childNum':childNum});	
	//循环单选
	var radArr = [];
	var radeHtml = [];
	$('.classify .choHover').each(function(){
		radeHtml += $(this).html()+',';
		radArr += $(this).attr('name')+',';
	});
	$('.sh_hideInp').val(radArr);
	$('.sh_hideInp').attr('data',radeHtml);
	$('.sh_hideInpVal').val(radeHtml);
}
//1 多选
function chooseMany(obj){
	var moreArr = [];
	var moreHtml = [];
	if($(obj).hasClass('choHover')){
		$(obj).css('color','#444').removeClass('choHover');		//删除class
	}else{
		$(obj).css('color','#fff').addClass('choHover');			//添加class
	}
	$('.clamore .choHover').each(function(){
		moreHtml += $(this).html()+',';
		moreArr += $(this).attr('name')+',';
	})
	$('.manyChoose').val(moreArr);
	$('.manyChoose').attr('data',moreHtml);
	$('.manychoVal').val(moreHtml);
	//多选赋值
	$('#other').val(moreArr);
	$('.manychoVal').val(moreHtml);
}
