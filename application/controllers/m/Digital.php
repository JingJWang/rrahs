<?php
header("Content-type:text/html;charset=utf-8");
defined('BASEPATH') OR exit('No direct script access allowed');
class Digital extends RR_Controller {
    /**
     *查看首页
     */
    function quote(){
        $this->display('m/quote.html');
    }
	/**
	 * @abstract 提交订单  显示手机品牌及所有型号手机
	 */
	function ShowBrand(){
	    //微信登录
	    $this->wxLogin();
	    //校验当前用户权限组
	    $this->checkRole($_SESSION['user']['user_role'],$this->config->item('role_customer'));
	    //读取传递的品牌ID
	    $bid='';
	    $bid=$this->input->get('id');
	    if(empty($bid)){
	        $bid=1;
	    }else{
	       if(!is_numeric($bid) || $bid < 0){
	           $bid=1;
	       }
	    }
	    //读取品牌   型号列表
		$this->load->model('m/Digital_model');
		$response = $this->Digital_model->getBrand();
		$shops = $this->Digital_model->getShops($bid);
		$this->assign('shops', $shops);
		$this->assign('response', $response);
		$this->assign('bid', $bid);
		$this->display('m/brand.html');
	}
	/**
	 * @abstract 搜索 通过关键词 搜索
	 */
	function search(){
	    $keyword=$this->input->post('keyword',true);
	    if(!empty($keyword)){
	        $keyword=often_helper::safe_replace($keyword);
	    }else{
	        $keyword='';
	    }
	}
	/**
	 * @abstract 提交订单   获取某个型号手机的属性信息
	 * @param   $id  string  型号Id
	 */
	function Attribute(){
	    //校验是否登录
	    $this->isOnline();
	    //型号ID
	    $id=$this->input->get('id',true);
        if(!is_numeric($id) || !isset($id) || $id < 1){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        //获取保存的参数信息
        $this->load->model('m/Digital_model');
        $this->Digital_model->typeid=$id;
        //查询型号参数配置
        $result=$this->Digital_model->getParameter();
        if(empty($result['types_attr'])){
            often_helper::output('21011','','','读取内容出现异常!');
        }
        $attr=json_decode($result['types_attr'],true);
        //获取型号下的各参数详细信息
        $option=$this->Digital_model->getTypeCon();
        foreach ($attr as $k=>$v){
            foreach ($v as $i=>$n){
                $attr[$k][$i]=str_replace(array('[',']'),array('',''),$n);
            }
        }
        $model=array();
        foreach ($option['model'] as $key=>$val){
            $model[$val['alias']]=array('model'=>$val['model'],'type'=>$val['type'],
                    'name'=>$val['name'],'logic'=>$val['logic']);
        }
        $info=array();
        foreach ($option['info'] as $key=>$val){
            $info[$val['id']]=$val['info'];
        }
        $this->assign('attr', $attr);
        $this->assign('model', $model);
        $this->assign('info', $info);
        $this->assign('role', $this->user_role);
        $this->assign('id', $result['types_id']);
        $this->assign('name', $result['types_name']);
        $this->assign('role_Clerk', $this->config->item('role_Clerk'));
        $this->assign('role_customer', $this->config->item('role_customer'));
		$this->display('m/classify.html');
	}
	/**
	 * @abstract 提交订单干  校验用户选择的故障信息数据
	 * 
	 */
	function checkNextData(){
	    //校验当前用户是否已经登录
	    $this->isOnline();
	    
	    $data=$this->input->post();
	    $resp=array();
	    isset($this->againS) ? '': $this->againS = 0 ;
	    if(isset($this->againS) && $this->againS == 1){
	        $order=array();
	        $this->attr=array();
	        $newOrder=array();
	        $res=array();
	    }
	    //校验订单编号
	    if (!is_numeric($data['oid']) || $data['oid'] < 0){
	        $resp=array('21011','','','非法订单编号!');
	    }
	    $this->againS == 1 ? $newOrder['oid'] = $order['oid'] = $data['oid'] : '' ;
	    if (empty($data['hideInp'])){
	        $resp=array('21011','','','非法请求参数!');
	    }
	    $this->againS == 1 ? $newOrder['hideInp'] = $data['hideInp'] : '' ;
	    $this->againS == 1 ? $newOrder['hideInpVal'] = $data['hideInpVal'] : '' ;
	    $this->againS == 1 ? $newOrder['manycho'] = $data['manycho'] : '' ;
	    $this->againS == 1 ? $newOrder['manychoVal'] = $data['manychoVal'] : '' ;
	    $faultName=array();
	    $faultId=array();
	    $resName='';
	    $resId='';
	    $newOrder['vdata']=$data['hideInpVal'].$data['manychoVal'];
	    $cdata=substr($newOrder['vdata'],0,strlen($newOrder['vdata'])-1);
	    $faultName = explode(',',$cdata);
	    foreach($faultName as $k=>$v){
	        if(empty($v)){
	            $resp=array('21011','','','非法请求参数!');
	        }
	        $resName=$resName.$v.',';
	    }
	    $newOrder['vid']=$data['hideInp'].$data['manycho'];
	    $cid=substr($newOrder['vid'],0,strlen($newOrder['vid'])-1);
	    $faultId = explode(',',$cid);
	    foreach($faultId as $k=>$v){
	        if(empty($v) || !is_numeric($v) || $v<0){
	            $resp=array('21011','','','非法请求参数!');
	        }
	        $resId=$resId.$v.',';
	    }
	    $order['cdata']=substr($resName,0,strlen($resName)-1);
	    $order['cid']=substr($resId,0,strlen($resId)-1);
	    if(!empty($resp)){
	        $this->checkDataOut(1,$resp);
	    }
	    if(isset($this->againS) && $this->againS == 1){
	        $this->order=$order;
	        $this->attr=array_diff_key($data,$newOrder);
	    }
	    $res['orders']=$this->order;
	    $res['attr']=$this->attr;
	    return $res;
	}
	
	
	/**
	 *@abstract 提交订单   保存手机型号的现状选择 存session
	 */
	function saveSes(){
	    //校验当前用户是否已经登录
	    $this->isOnline();
	    //验证提交数据
	    $this->againS=1;
	    $res=$this->checkNextData();
	    if($res){
	        $attrs=$res['attr'];
	        $this->attr=$this->handleData($attrs);
	        $this->load->model('m/Digital_model');
	        //把用户下单的数据保存到session中
	        $_SESSION['use']['data']=$res['orders']['cdata'];
	        $_SESSION['use']['vid']=$res['orders']['cid'];
	        $_SESSION['use']['id']=$res['orders']['oid'];
	        $this->Digital_model->typeid=$res['orders']['oid'];
	        //获取商品名称赋值给session
	        $this->Digital_model->typeid=$res['orders']['oid'];
	        $resName=$this->Digital_model->getParameter();
	        $_SESSION['use']['name']=$resName['types_name'];
	        $this->Digital_model->coop=1447299307;
	        $plan=$this->Digital_model->GetPlan();
	        foreach ($plan as $k=>$v){
	            $this->quote=json_decode($v['plan_content'],true);
	            // 获取该型号的基本市价
	            $this->lock=$plan['0']['plan_base_price'];
	            $pri=$this->reckonRules();
	        }       
	        $_SESSION['use']['pri']=$pri;
	        $result=$_SESSION['use'];
	        if($result){
	            often_helper::output('21010','','','');
	        }else{
	            often_helper::output('21011','','','');
	        }
	    }
	}
	/**
	 * @abstract 获取用户的报单的故障信息
	 */
	function GetOffer(){
	    //校验当前用户是否已经登录
	    $this->isOnline();
	    $result=$_SESSION;
	    if(isset($_SESSION['user']['user_role'])){
	        if($_SESSION['user']['user_role'] != $this->config->item('role_customer')){
	            often_helper::alert('','ShowBrand','');
	        }else{
	            //读取当前用户的详细资料
	            $this->load->model('m/User_model');
	            $this->User_model->openid=$_SESSION['user']['user_openid'];
	            $userInfo = $this->User_model->selUser();
	            if(!empty($userInfo['user_mobile'])){
	                $mobile=$userInfo['user_mobile'];
	            }else{
	                $mobile='';
	            }
	        }
	    }else{
	        often_helper::alert('没有获取到当前登录信息!','quote',0);
	    }
	    $vals=array_filter(explode(',',$result['use']['data']));
	    $keys=array_filter(explode(',',$result['use']['vid']));
	    $role=$result['user']['user_role'];
	    $this->assign('role',$role);
	    $this->assign('keys',$keys);
	    $this->assign('vals',$vals);
	    $this->assign('mobile',$mobile);
	    $this->assign('name',$result['use']['name']);
	    $this->assign('id',$result['use']['id']);
	    $this->assign('pri',$result['use']['pri']);
	    $this->assign('role_customer', $this->config->item('role_customer'));
	    $this->display('m/offer.html');
	}
	/*************************自动报价****************/
	/**
	 * @abstract 处理自动报价的数据
	 * @param   array  data 报价数据
	 * @return  成功返回array | 失败输出 json  失败原因
	 */
	function handleData($data){
	    if(empty($data)){
	        often_helper::output('21010','','','本次选择的属性不符合规范!');
	    }
	    //去除数组中为空的选项
	    array_filter($data);
	    //校验时候存在多选参数 存在转换成数组
	    if(array_key_exists('other',$data)){
	        if(count($data['other'])>0){
	            $otee=$data['other'];
	            $other_temp=often_helper::safe_replace($otee);
	        }
	    }
	    if(isset($other_temp) && empty($other_temp) === false){
	        $sre_other=often_helper::safe_replace($other_temp);
	        if(!is_numeric($other_temp)){
	             often_helper::output('21010','','','本次选择的属性不符合规范!');
	        }
	        $other=str_replace(array(' '),array(''),$otee);
	        $other=trim($other,',');
	        $other=explode(',',$other);
	    }
	    if(isset($other)){
	        $data['other']=$other;
	    }
	    return $data;
	}
	/**
	 *@abstract 自动报价运算规则
	 *
	 */
	 function reckonRules(){
		$plan=$this->quote;
		//获取自动报价参数 分类
		$this->load->model('m/Digital_model');
		$model=$this->Digital_model->optionModel();
		$attr=$this->attr;		
		$quote=array();
		$basics=array();
		$unive=array();
		$special=array();
		foreach ($model as $key=>$val){
			switch ($val['type']){
				case '0':
					array_key_exists($val['alias'],$attr) == true ? $basics[$key]=$attr[$val['alias']] : '';
					break;
				case '1':
					array_key_exists($val['alias'],$attr) == true ? $unive[$key]=$attr[$val['alias']] : '';
					break;
				case '2':
					array_key_exists($val['alias'],$attr) == true ? $special=$attr[$val['alias']] : '';
					break;
			}
		}
		$basics=$this->position($basics,$plan,1);
		$unive=$this->position($unive,$plan,2);
		$special=$this->position($special,$plan,2);
		$pri=$this->reckon($this->lock, $basics, $unive, $special);
		return $pri;
	}
	/**
	 *@abstract 得到扣款中同类型百分比最多的一项
	 *@param array  $option  订单参数
	 *@param array  $plan    报价参数
	 *@param int    $type    类型   值为1 不返回最大值 2返回最大值
	 */
	 function position($option,$plan,$type){
		$ratio=array();
		if(!is_array($option)){
			return 0;
		}else{
			foreach ($option as $key=>$val){
				$ratio[]=str_replace(array('-','+','%'),array('','',''),$plan[$val]);
			}
			switch($type){
				case 1:
					$result=$ratio;
					break;
				case 2:
					$result=empty($ratio) ? '0' : max($ratio);
					break;
			}
			return  $result;
		}
		exit;
	}
	/**
	 *@abstract 计算价格
	 */
	function reckon($pri,$basics,$unive,$special){
	    if(!is_array($basics)){
	        $pri=$pri-$basics;
	    }else{
	        foreach ($basics as $key=>$val){
	            $pri=$pri-$val;
	        }
	    }
	    $pri=$pri-$pri*$unive/100;
	    if(!empty($special)){
	        $pri=$pri-$pri*$special/100;
	    }
	    $pri=round($pri);
	    return $pri;
	}
	/***********************自动报价*****************/
   /**
    * @abstract 提交订单校验数据
    *   校验  随机验证码/手机号码/网点编号/价格/手机验证码
    *   函数输出 2种结果 如果请求为ajax 校验数据和手机验证码 
    *   如果请求不是ajax则校验数据 完成后结果保存为 
    *   $this->order 和 $this->content 两个结果集
    *   其中 :
    *   $this->content = array_diff_key($this->input->post(),$this->order);
    *   两个数组的差集
    */
   function inftxyzm (){
       //校验是否已经登录
       $this->isOnline();
       //校验当前用户组是否具有相应的权限
       $this->checkRole($this->user_role, $this->config->item('role_customer'));       
       //获取请求数据 并且 校验数据的合法性
       $data=$this->input->post();
       $resp=array();
       isset($this->invalid) ? '': $this->invalid = 0 ;      
       if(isset($this->invalid) && $this->invalid == 1){
           $thisorder=array();
           $content=array();
       }
       //校验手机号码是不是为空
       if(isset($_SESSION['user']['user_mobile']) && empty($_SESSION['user']['user_mobile'])){
           $resp=array('21012','','','还未绑定手机号码!');
       }
       //校验网点编号
       if(empty($data['inp']) || !isset($data['inp'])){
           $resp=array('21011','','','网点编号不正确!');
       }
       $this->invalid == 1 ? $order['inp'] = $data['inp'] : '' ;
       //校验银行卡 姓名
       if(empty($data['username']) || !isset($data['username']) || preg_match("[^\x80-\xff]",$data['username'])){
           $resp=array('21011','','','姓名为空或填写格式不正确!');
       }
       $this->invalid == 1 ? $order['username'] = $data['username'] : '' ;
       //校验银行卡开户行
       if(empty($data['bankname']) || !isset($data['bankname'])){
           $resp=array('21011','','','开户行为空或填写格式不正确!');
       }
       $this->invalid == 1 ? $order['bankname'] = $data['bankname'] : '' ;
       //校验银行卡 卡号
       if(empty($data['banknumber']) || !isset($data['banknumber'])){
           $resp=array('21011','','','银行卡号为空或填写格式不正确!');
       }
       $this->invalid == 1 ? $order['banknumber'] = $data['banknumber'] : '' ;
       //校验报价价格
       if(empty($data['price']) || !is_numeric($data['price']) || $data['price'] < 0){
           $resp=array('21011','','','报价异常!');
       }
       $this->invalid == 1 ? $order['price'] = $data['price'] : '' ;
       //校验手机类型ID
       if(empty($data['phoneid']) || !is_numeric($data['phoneid']) || $data['phoneid'] < 0){
           $resp=array('21011','','','本次请求不符合规范!');
       }
       $this->invalid == 1 ? $order['phoneid'] = $data['phoneid'] : '' ;
       //校验手机名称
       if(empty($data['phonename']) && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $data['phonename']) < 0){
           $resp=array('21011','','','本次请求不符合规范!');
       }
       $this->invalid == 1 ? $order['phonename'] = $data['phonename'] : '' ; 
       if(isset($this->invalid) && $this->invalid == 1){
           $this->order=$order;
           $this->content=array_diff_key($data,$order);
       }
       //重置订单提交标识
       if(isset($_SESSION['user']['submit']['status'])){
           unset($_SESSION['user']['submit']['status']);
       }
       if(!empty($resp)){
           $this->checkDataOut(1,$resp);
       }else{
           $this->checkDataOut(0,array('21010',0,0,0));
       }
   }
   /**
    * @abstract 提交订单  函数根据请求方式的不同返回不同格式的数据
    * 当请求方式 是ajax 并且 返回错误 返回数据类型json
    * 当请求方式不是ajax 并且返回错误 返回数据类型bool true OR false
    */
   function checkDataOut($type,$data){
       $req=$this->input->is_ajax_request();
       $req == true ? $req='ajax' :$req='';
       if($type == 1 && $req == 'ajax'){
           //校验请求来源
           switch ($req){
               case 'ajax':
                   often_helper::output($data[0],$data[1],$data[2],$data[3]);
               default:
                   return false;
           }
       }else{
           //校验请求来源
           switch ($req){
               case 'ajax':
                   often_helper::output($data[0],$data[1],$data[2],$data[3]);
               default:
                   return false;
           }
       }
   }
   
   /**
    * @abstract 提交订单  处理提交订单请求
    */
   function listSucc(){ 
       //校验登录 
       $this->isOnline();
        //校验当前用户组是否具有相应的权限
       $this->checkRole($this->user_role, $this->config->item('role_customer'));
       //防止订单重复提交
       isset($_SESSION['user']['submit']['status']) ? '' :$_SESSION['user']['submit']['status'] = 1;
       if($_SESSION['user']['submit']['status'] == 0){
           $orderinfo= $_SESSION['order']['url'];
           
           $this->assign('orderinfo',$orderinfo );
           $this->display('m/listsucc.html');
           exit();
       }
       //验证提交数据
       $this->invalid=1;
       $check=$this->inftxyzm();
       //保存订单
       $last=$this->savefirOrder();
       if($last){
          $_SESSION['user']['submit']['status'] = 0;
          $orderinfo='/m/Order/getOrderDetail?code=1&id='.$this->lastId;
          $_SESSION['order']['url']=$orderinfo;
          $template=array('title'=>'提交成功',
                  'error'=>'订单提交成功',
                  'msg'=>'您的订单已经提交，等待网点验机');
          $this->assign('id', $this->lastId);
          $this->assign('orderinfo',$orderinfo );
          $this->assign('template',$template);
          $this->display('m/listsucc.html');
       }else{
           $template=array('title'=>'提交失败',
                   'error'=>'订单提交出现异常!',
                   'msg'=>'您的订单提交过程中出现异常，请联系客服');
           $this->assign('id',0);
           $this->assign('template',$template);
           $this->assign('orderinfo','#' );
           $this->display('m/listsucc.html');
       }
   }
   /**
    * @abstract 提交订单  保存订单
    *
    */
   function savefirOrder(){
       $this->load->model('m/User_model');
       $this->User_model->openid=$_SESSION['user']['user_openid'];
       $userIn=$this->User_model->selUser();
       $mobile=$userIn['user_mobile'];
       if($mobile == false){
           return false;
       }
       $this->load->model('m/Digital_model');
       $this->Digital_model->mobile=$mobile;
       $this->Digital_model->userid=$_SESSION['user']['user_id'];
       $this->Digital_model->number=$this->create_ordrenumber();
       $this->Digital_model->inp=$this->order['inp'];
       $this->Digital_model->price=$this->order['price'];
       $this->Digital_model->contentid=$_SESSION['use']['vid'];
       $this->Digital_model->phoneid=$this->order['phoneid'];
       $this->Digital_model->phonename=$this->order['phonename'];
       $this->Digital_model->username=$this->order['username'];
       $this->Digital_model->bankname=$this->order['bankname'];
       $this->Digital_model->banknumber=$this->order['banknumber'];
       $this->Digital_model->content=rtrim(implode(',',$this->content),',');
       $result=$this->Digital_model->savefirOrder();
       if($result){
           $this->orderid=$result;
           $this->agent=$this->order['inp'];
           $this->sendNotice();
           $this->lastId=$result;
           return $result;
       }else{
           return false;
       }
   }
   /**
    * 提交订单校验手机号码
    */
   function Messyz(){
     //校验是否登录
     $this->isOnline();
     //校验提交的数据
     $data=$this->input->post();
     if(!preg_match("/^1[34578]{1}\\d{9}$/",$data['mobile'])){
         often_helper::output('21011','','','手机号码不正确!');
     }     
     if($data['yzm']!=$_SESSION['hst_code']){
         often_helper::output('21011','','','图形验证码错误!');
     }
     $this->load->model('m/Digital_model');
     $this->Digital_model->mobile=$data['mobile'];     
     $this->Digital_model->num=$this->config->item('order_code_number');
     $this->Digital_model->time=$this->config->item('order_code_time');
     //校验同一个用户在10分钟内接受的验证码是否超过限制条数
     $check=$this->Digital_model->checkVerifyCode();
     if(!$check){
         often_helper::alert('当前发送信息频发,请稍后再试!','',1);
     }
     //调用阿里大于发送短信验证码
     $this->mobile=$data['mobile'];
     $msg=$this->sendVerifyCode();
     if(!$msg){
         often_helper::output('21011','','','短信验证码发送遇到异常!');
     }
     //短信验证码发送成功 保存信息
     $this->Digital_model->respid=$this->respid;
     $this->Digital_model->code=$this->code;
     $resp=$this->Digital_model->saveVerifyCode();
     if(!$msg){
         often_helper::output('21011','','','短信验证码发送遇到意外!');
     }else{
         often_helper::output('21011','','','短信验证码已发送!');
     }
   }
   /**
    * 阿里大于发送短信验证码
    * 
    */
   function sendVerifyCode(){
       //调用阿里大于发送验证码
       $this->load->library('RR_Message');
       //获取等待发送验证码 
       $this->code=often_helper::random();
       $res=RR_Message::sendSms( 
               $this->config->item('alidayu_signname'), // 短信签名
               $this->config->item('alidayu_template_1'), // 短信模板编号
               $this->mobile, // 短信接收者
               Array(  // 短信模板中字段的值
                       "code"=>$this->code,
               ),
               ""   // 流水号,选填
       );  
       if($res->Message =='OK' && $res->Code == 'OK'){
           $this->respid=$res->BizId;
           return true;
       }else{
           return false;
       }
   }
   /**
    *@abstract 根据网点编号查询代理商信息 发送微信通知
    *
    */
   function sendNotice(){
       $this->load->model('m/user_model');
       $this->user_model->Agent=$this->agent;
       $user=$this->user_model->selAgent();
       if($user === false){
           return false;
       }
       $this->load->model('m/wxcode_model');
       $url='http://m.rrahs.com/m/digital/getOrderDetail?id='.$this->orderid;
       $link='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.
               $this->config->item('appid').'&redirect_uri='.urlencode($url).
               '&response_type=code&scope=snsapi_base&state=#wechat_redirect';
       $content = sprintf($this->config->item('msg_suborder'),
               $user['user_openid'],$link);
       $response=$this->wxcode_model->sendmessage($content);
   }
   
   /**
    *@abstract 生成订单订单编号
    * @return string
    */
   function create_ordrenumber(){
       return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).rand(1000,9999);
   }
}