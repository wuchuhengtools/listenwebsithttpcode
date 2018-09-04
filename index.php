#!/bin/php;
<?php

/**
 * @info    网站访问状监听邮件预警器
 * @author  小孩别跑
 * @email   wuchuheng@163.com
 * @time    2018-09-03
 *
 */

require 'vendor/autoload.php';
class index
{
    public  $myEmail         = "hwlfcwl@163.com";
    public  $myEmailPassword = 501501;
    public  $myEmailName     = '网站监听预警报告';
    public  $myEmailHost     = "smtp.163.com";
    public  $myEmailPort     = 25;

    /**
     * @info 初始化
     *
     */
    public function __construct()
    {
        if(!extension_loaded('curl')) exit('请安装curl扩展!');
    }
    /*
     * @info    发送邮件
     * @param   string      $tomail     对方邮箱
     * @param   string      $subject    邮件标题
     * @param   string      $body       邮件正文
     * @return  numeral     1成功0失败
     *
     */
    public function send_mail($tomail, $subject, $body){
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = 2;//显示调试信息
            $mail->SMTPAuth = true;
            //$mail->SMTPSecure = "ssl";//启用ssl加密
            $mail->Host = $this->myEmailHost;//邮箱服务器名称
            $mail->Port = $this->myEmailPort;//邮箱服务端口
            $mail->Username = $this->myEmail;//发件人邮箱地址
            $mail->Password = $this->myEmailPassword;//发件人邮箱密码
            $mail->CharSet = "UTF-8";
            $mail->SetFrom($this->myEmail, $this->myEmailName);//发件人信息 邮箱地址，姓名
            $mail->Subject = $subject;
            $mail->MsgHTML($body);
            $mail->AddAddress($tomail,"");
            if (!$mail->Send())
            {
                $stat = 0;
            }
            else
            {
                $stat = 1;
            }
            return $stat;
    }


    /**
     * @info 获取指定网站访问状态码
     * @params      string      $url    要获取状态码的网址
     * @return      numeral             状态码
     *
     */
    public function httpStatusCode($url)
    {
        get_headers($url,1);
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpcode;
     }

    /**
     * @info    错误日志
     * @param   string      $message    错误信息
     * @param   string      $level      错误等级
     *
     */

    public function errorLog($message,$level='email')
    {
         $logDir = dirname(__FILE__).DIRECTORY_SEPARATOR .'log';
         if(!is_dir($logDir)) mkdir($logDir,0755);
         $logFile = $logDir.DIRECTORY_SEPARATOR.date('Ymd',time()).'.log';
         $fileHandle = fopen($logFile,'a');
         $time = date('Y-m-d H:i:s',time());
         fwrite($fileHandle,"[{$level}] {$message} [{$time}]\n");
         fclose($fileHandle);
    }



    /**
     * @info        网站访问状监听邮件预警
     * @param       string      $url    监听网址
     * @parmas      string      $toEamil    对方邮件地址
     * @return      boolearn
     *
     */
    public function listenUrl($url,$toEmail)
    {
        $httpdStatusCode = $this->httpStatusCode($url);
        if($httpdStatusCode !== 200 ){
            $subject = '警报!!!监听的网站不能访问了,请做好准备，马上出发';
            $date = date('Y-m-d H:i:s',time());
            $body    = "网址{$url}在{$date}不能正常访问,状态码:{$httpdStatusCode}，请尽快修复！我觉得我还可以抢救一下^_^";
            $this->errorLog($body,'error');
            if(in_array($httpdStatusCode,[400,401,404])){
            $body    = "<h3>网址<span style='color:red'>{$url}</span>在<span style='color:red'>{$date}</span>不能正常访问,状态码:<span style='color:red'>{$httpdStatusCode}</span>，请尽快修复！我觉得我还可以抢救一下!^_^</h3>";
            $result = $this->send_mail($toEmail,$subject,$body);
            if($result !== 1) $this->errorLog("发送邮件到{$toEmail}失败",'email');
            }
        }

    }

}

/**
 * @info    开始执行监听网站状态码业务
 *
 */
(new index())->listenUrl('http://www.zgfzh.com','jdnfuzhaung@163.com');
(new index())->listenUrl('http://www.zgspjmw.com/','jdnfuzhaung@163.com');
