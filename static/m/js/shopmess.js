	//点击选择
	function addth(obj,id){
		if($(obj).find('.radio_sp').hasClass('check_sp')){
			$(obj).find('.radio_sp').removeClass('check_sp');
			var packageid =  $('#package').val()
			packageid=packageid.replace(id+',','');
			$('#package').val(packageid);
		}else{
			$(obj).find('.radio_sp').addClass('check_sp');
			var packageid =  $('#package').val()
			$('#package').val(packageid+id+',');
		}
		var radnum =  $('.radio_sp').length;
		$('.check_sp').each(function(){
			var checknum = $('.check_sp').length;
			if(radnum == checknum){
				$('.qx').addClass('qx_rad');
			}else{
				$('.qx').removeClass('qx_rad');
			}
		})
	}
	//全选
	function changeAll(obj){
		var num = 0;
		if($(obj).hasClass('qx_rad')){
			$(obj).removeClass('qx_rad');
			$('.sjop_tit .check_sp').each(function(){
				$(this).removeClass('check_sp');
				$('#package').val('');
			})
			$('.last_btn').removeClass('radChange').attr('onclick','');
		}else{
			$(obj).addClass('qx_rad');
			$('.sjop_tit .radio_sp').each(function(){
				$(this).addClass('check_sp');
				saveId();
			})
			$('.last_btn').addClass('radChange');
		}
	}
	function saveId(){
		var id='';
		$('.sjop_tit').each(function(){
			id += $(this).attr('data')+',';
		})
		id=id.replace('undefined,','');
		$('#package').val(id);
	}
	function extractList(){
		var u='initiateExtract';
		var d='id='+$('#package').val();
		var f=function(res){
			if(res.status = 21010){
				window.location.reload();
			}
			if(res.status = 21011){
				alert(res.msg);
			}
		}
		rrwx.httpRequest(u,d,f);
	}