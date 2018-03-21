<?php

class RR_Wxpay  {
    //微信支付分配的商户号
    public  $mch_id  ='';
    //商户订单号，需保持唯一(只允许数字[0~9]或字母[A~Z]和[a~z]，最短8位，最长32位)
    public  $partner_trade_no ='';
    //随机字符串，不长于32位
    public $nonce_str = '';
    //签名
    public $sign= '';
    //收款方银行卡号
    public $enc_bank_no= '';
    //收款方用户名
    public $enc_true_name = '';
    //收款方开户行
    public $bank_code = '';
    //付款金额
    public $amount = '';
    //付款说明
    public $desc ='';
    //
    public $appSecret='0400452cd20ab7aac14f080f17259cb4';
    //
    public $para = '';
    
    public  function  pem ($content){
        
       //$pi_key =  openssl_pkey_get_private();//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id  
       //var_dump($pi_key);
       
       //$pu_key = openssl_pkey_get_public('E:\hots\rrahs\application\libraries\apiclient_key.pem');//这个函数可用来判断公钥是否是可用的  
       //var_dump($pi_key);
       
       $priKey = file_get_contents('E:\hots\rrahs\application\libraries\apiclient_key.pem');
       
       /* 从PEM文件中提取私钥 */
        $res = openssl_get_privatekey($priKey);
       
       openssl_private_encrypt($content, $sign, $res);
       
       $sign=base64_encode($sign);
       
       return  $sign;
    }
    
    public  function para(){
        
        $this->mch_id='1490635702';
        $this->partner_trade_no=$this->build_order_number();
        $this->nonce_str=$this->getRandomString();
        $this->enc_bank_no=$this->pem('6222021613008260340');//6222021613008260340
        $this->enc_true_name=$this->pem('马小涛');
        $this->bank_code = '1002';
        $this->amount='100';
        $this->desc='';
        $info=array(
                'mch_id'=>$this->mch_id,
                'partner_trade_no'=>$this->partner_trade_no,
                'nonce_str'=>$this->nonce_str,
                'enc_bank_no'=>$this->enc_bank_no,
                'enc_true_name'=>$this->enc_true_name,
                'bank_code'=>$this->bank_code,
                'amount'=>$this->amount,
                'desc'=>$this->desc
        );
        return $info;
    }
    public  function sign(){
        $info=$this->para();
        ksort($info);
        $sign='';
        foreach ($info as $key=>$val){
            if(!empty($val)){
                $sign .= $key.'='.$val.'&';
            }
        }
         $sign=trim($sign,'&'); echo '</br>';
         $sign=$sign.'&key='.$this->appSecret; echo '</br>';
        $sign=md5($sign); 
         $sign=strtoupper($sign); echo '</br>';
        return $sign;
    }
    public  function type(){
        $info=$this->para();
        $info['sign']=$this->sign();
        $xml='';
        foreach ($info as $key=>$val){
            if(!empty($val)){
                $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
            }
        }
         $xml='<xml>'.$xml.'</xml>';
        
        return $xml;
    }
    public  function  pay(){
        //$this->pem();exit();
        $xml=$this->type();
        $resp=$this->sslHttpRequest('https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank', $xml);
    }
    public  function getRandomString(){
        $str = '1234567890abcdefghijklmnopqrstuvwxyz';
        $t1='';
        for($i=0;$i<30;$i++){
            $j=rand(0,35);
            $t1 .= $str[$j];
        }
        return $t1;
    }
    
    public  function  build_order_number(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).rand(1000,9999);
    }
    
    public  function  sslHttpRequest($url, $vars, $second=30,$aHeader=array()){

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
       
        //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, 'apiclient_cert.pem');
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, 'apiclient_key.pem');
        
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        //运行curl
        $data = curl_exec($ch);
        var_dump($data);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            var_dump($error);
        }
    }
    
    
    public  function rsapub(){
        $data=array(
                'mch_id'=>'1490635702',
                'nonce_str'=>$this->getRandomString(),
                'sign_type'=>'MD5',
                'sign'=>''
        );
        ksort($data);
        $sign='';
        foreach ($data as $key=>$val){
            if(!empty($val)){
                $sign .= $key.'='.$val.'&';
            }
        }
        $sign=trim($sign,'&');
        $sign=$sign.'&key='.$this->appSecret;
        $sign=md5($sign);
        $sign=strtoupper($sign);
    
        $data['sign']=$sign;
        $xml='';
        foreach ($data as $key=>$val){
            if(!empty($val)){
                $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
            }
        }
        $xml='<xml>'.$xml.'</xml>';
        var_dump($xml);
        $resp=$this->sslHttpRequest('https://fraud.mch.weixin.qq.com/risk/getpublickey', $xml);
    }
}