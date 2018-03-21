<?php
session_start();

$code =new code();

$code->lenght=4;

$code->width =60;

$code->height=20;

$code->getCode();

if(!empty($_GET['name'])){
    switch ($_GET['name']){
        case '1':
            $_SESSION['extract_code']=$code->code;
        default:
            $_SESSION['hst_code']=$code->code;
    }
}else{
    $_SESSION['hst_code']=$code->code;
}
$code->showImg();

class  code{
    
    public  $lenght='';
    
    public  $code = '';
    
    public  $width='';
    
    public  $height='';
    
    /**
     * 生成验证码内容
     * @param   int     lenght  长度
     * @param   string  code    验证码内容
     */
    function getCode(){
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $this->lenght; $i++) {
            $code .= $str[mt_rand(0, strlen($str)-1)];
        }
        $this->code=$code;
    }
    
    function showImg() {
        //获取验证码
        $code=$this->code;        
        
        Header("Content-type: image/png");
        
        $im = imagecreate($this->width, $this->height);
        
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        
        $gray = imagecolorallocate($im, 118, 151, 199);
        
        $bgcolor = imagecolorallocate($im, 235, 236, 237);
        //画背景
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bgcolor);
        
        //画边框
        imagerectangle($im, 0, 0, $this->width-1, $this->height-1, $gray);
       
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($im, rand(0, $this->width), rand(0, $this->height), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(6, 8);
        for ($i = 0; $i < $this->lenght; $i++) {
            $strpos = rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 14);
        }
        imagepng($im);
        imagedestroy($im);
    }
    
}


?>
