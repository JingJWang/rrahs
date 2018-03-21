	function tab(obj){
		var i = $(obj).index();//下标第一种写法
		$(obj).addClass('addNav').siblings().removeClass('addNav');
		$('.list .listTab').eq(i).show().siblings().hide();
	}
	//点击选择
	function changeRad(obj){
		var num = 0;
		var anum = 0;
		if($(obj).hasClass('rad_check')){
			$(obj).removeClass('rad_check');
		}else{
			$(obj).addClass('rad_check');
		}
		//已选
		var orderId = [];
		$('.list .rad_check').each(function(){
			num += $(this).length;
			orderId +=$(this).attr('data')+';';
		})
		$('.shad .num').html(num);
		$('.orderIdS').val(orderId);
		if(num != 0){
			$('.last_btn').addClass('radChange').attr('onclick','showsd();transfer()');
		}else{
			$('.last_btn').removeClass('radChange').attr('onclick','');
		}
		//所有
		$('.list .rad').each(function(){
			anum += $(this).length;
		})
		//全选是否显示
		if(num == anum){
			$('.footer .qx').addClass('qx_rad');
		}else{
			$('.footer .qx').removeClass('qx_rad');
		}
	}
	//全选
	function changeAll(obj){
		var num = 0;
		if($(obj).hasClass('qx_rad')){
			$(obj).removeClass('qx_rad');
			$('.list .rad_check').each(function(){
				$(this).removeClass('rad_check');
			})
			$('.last_btn').removeClass('radChange').attr('onclick','');
			$('.orderIdS').val('');
		}else{
			$(obj).addClass('qx_rad');
			$('.list .rad').each(function(){
				$(this).addClass('rad_check');
			})
			$('.last_btn').addClass('radChange').attr('onclick','showsd();transfer()');
			var orderId = [];
			$('.list .rad_check').each(function(){
				num += $(this).length;
				orderId +=$(this).attr('data')+';';
			})
			$('.shad .num').html(num);
			$('.orderIdS').val(orderId);
		}
		
	}
	//遮罩层
	function showsd(){
		$('.shadow').show();
		$('body').css('overflow','hidden');
	}
	//下拉
	function showUl(){
		if($('.crew_ul').is(':visible')){
			$('.crew_ul').hide();
		}else{
			$('.crew_ul').show();
		}
	}
	//隐藏
	function hideshad(){
		//$('.sure').css({'background':'#d0d0d0'}).attr('onclick','');
		$('.shadow').hide();
		$('body').css('overflow','auto');
	}