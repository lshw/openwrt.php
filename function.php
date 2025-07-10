<?php
error_reporting(E_ALL & ~E_WARNING);
$charset='UTF-8';
$rootdir = __DIR__;
if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            $https="https";
else
            $https="http";
$rooturl="${https}://$_SERVER[HTTP_HOST]";
if ($_SERVER['HTTPS']=='on') {
    $https='https://';
} else {
    $https='http://';
}
$goback=base64_encode($https.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]);
if (PHP_SAPI === 'cli-server' && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

include "$rootdir/sqlite.php";

function ubus_init($id)
{
#global $db;
}
function temp($filename, $zhushi = 1, $rand = '1')
{
    global $rootdir,$rooturl;
    extract($GLOBALS, EXTR_SKIP);
    if (substr($filename, -5)=='.html') {
        $filename=substr($filename, 0, -5);
    }
    $filenamex=$rootdir.'/temp/'.$filename.'.html';
    if (file_exists($filenamex)) {
        $re12=file_get_contents($filenamex);
        $re12=str_replace('"', '\"', $re12);
        eval('$ret="'.$re12.'";'); //变量落实
        return ($ret);
    }
}

function disp($temp='',$temp1=''){
echo temp('head');
if($temp!='') echo temp($temp);
if($temp1!='') echo temp($temp1);
echo temp('bottom');
}


function get_str(&$string)
{
//解决一下字符串的注入问题
    global $_getstr;
    if ($_getstr["_$string"]==1) {
        return $string ; //已经处理过的
    }
    $string=strtr($string, array('..'=>' ','"'=>' ',"'"=>' ',"\t"=>' ','\r'=>' ','\n'=>' '));
    $string=addslashes($string);
    $_getstr["_$string"]=1;
    return $string;
}

function goback($msg = '', $goto = '-1')
{
    if ($_GET['ajax']=='1') {
        echo $msg;
        return;
    }
    if (get_str($_GET['goback'])!='') {
        $goback=base64_decode(strtr($_GET['goback'], array(' '=>'+')));
        unset($_GET['goback']);
        if ($goback!='') {
            gotourl($goback, $msg);
        }
    }
    if ($msg=='') {
        echo "<script>history.go($goto);</script>";
    } else {
        $msg=strtr($msg, array("\r\n"=>';',"\r"=>'<br>',"\n"=>'<br>',' '=>'_'));
        echo "$msg<script>alert('$msg'); history.go($goto);</script>";
    }
    exit();
}
function gotourl($url = '', $msg = '', $sec = 0)
{
    if ($_GET['ajax'] == '1') {
        die($msg);
    }
    if ($sec!=0) {
        gotodelay($url, $sec, $msg);
        exit();
    }

    if ($url=='') {
        goback();
    }
    if ($msg=='') {
        header("Location: ".$url);
    } else {
        if ($sec!=0) {
            echo temp('head_empty');
            echo "<a href='$url'>to be continue.</a>";
            $msg=strtr($msg, array("'"=>" "));
            $msg=strtr($msg, array("\r\n"=>';',"\r"=>'<br>',"\n"=>'<br>'));
            echo "$msg<script>
        alert('$msg');
      window.location='$url';</script>";
            exit();
        }
        echo temp('head_empty');
        $msg=strtr($msg, array("\r\n"=>';',"\r"=>'<br>',"\n"=>'<br>'));
        echo "$msg<script>
      alert('$msg');
    window.location='$url';</script>";
    }
    exit();
}

function gotodelay($url = '', $time = 0, $msg = '')
{
//url不能带汉字
    global $charset;
    if ($url=='') {
        goback();
    }
    if ($time==0) {
        ob_clean();
        header("Location: ".$url);
    } else {
        echo "<head>
      <META charset=$charset'>
      <meta http-equiv=\"refresh\" content=\"${time};url=$url\">
      </head>
      <body>$msg,$time 秒后继续</body>";
    }
    exit();
}


