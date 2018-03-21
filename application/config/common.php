<?php
$config['appid']='wx780b312e2fc3d66f';
$config['appsecret']='0400452cd20ab7aac14f080f17259cb4';
//提交订单的时候 发送通知到网点
$config['msg_suborder'] = '{ "touser":"%s","msgtype":"news","news":{"articles":[{"title":"收到新的订单推送!", "description":"有新的订单推送,请及时处理!",
                "url":"%s", "picurl":""}]}}';
//网点修改订单 重新报价后发送通知给用户
$config['msg_upprc']    = '{ "touser":"%s","msgtype":"news","news":{"articles":[{"title":"订单价格被重新修改!", "description":"您的订单有新的变化!",
                "url":"%s", "picurl":""}]}}';
//用户确认修改完价格后发送通知到网点
$config['msg_confirm']  = '{ "touser":"%s","msgtype":"news","news":{"articles":[{"title":"用户确认新的价格!", "description":"新的价格已经被用户接受!",
                "url":"%s", "picurl":""}]}}';
//店长发送通知给提货员
$config['msg_distill']  = '{ "touser":"%s","msgtype":"news","news":{"articles":[{"title":"收到新的提货请求!", "description":"有新的订单提货请求,请及时处理!",
                "url":"%s", "picurl":""}]}}';
/*********阿里大鱼 SDK配置信息***********/
$config['alidayu_appkey']                   = 'LTAIcO3hcXJ6CMah';                       //阿里大鱼SDK  key
$config['alidayu_secretKey']                = 'wDGL0rZdJql4l1sVq1PILaueK4129C';         //阿里大鱼SDK  secretKey
$config['alidayu_signname']                 = '收多多';                                  //短信签名
$config['alidayu_extend']                   = '1001';                                  //知通科技 回收通项目编号
$config['alidayu_sendsucc']                 =  0;                                      //阿里大鱼短信发送成功标示
$config['alidayu_template_1']               = 'SMS_105210361';//订单提交页面手机验证码模板

/***********订单提交验证配置信息********************/
$config['order_code_number']                ='5';//校验手机号码在同一个时间内接受的验证码条数  数量
$config['order_code_time']                  ='10';//校验手机号码在同一个时间内接受的验证码条数  时间(分钟)

/***********权限判断********************/
$config['role_customer']                   ='10';//用户角色
$config['role_Clerk']                      ='20';//店员角色
$config['role_Shopowner']                  ='30';//店长角色
$config['role_Distill']                    ='40';//提货员角色
$config['role_Manage']                     ='90';//管理员角色
$config['role_admin']                      ='99';//超级管理员角色
/***********订单状态******************/
$config['order_status_manage']             = array(  //管理员
        '1'=>'已提交,待验机',
        '2'=>'已验机,待确认',
        '10'=>'已成交',
        '11'=>'待提走',
        '12'=>'已收货',//提货已经收货
        '13'=>'已入库',
        '-1'=>'已取消'
);
$config['order_status_ordinary']           = array(//普通用户
        '1'=>'已提交,待验机',
        '2'=>'已验机,待确认',
        '10'=>'已成交,待打款'
);
/***************提成比例******************/
$config['commission_ratio']                = array(
        '20'=>0.03,
        '30'=>0.04,
);  
/****************t提货记录状态*************************/
$config['distill_state']                   = array(
        '1'=>'待确认',
        '2'=>'等待入库',
        '5'=>'已入库'
);