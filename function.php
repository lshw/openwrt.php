<?php
error_reporting(E_ALL & ~E_WARNING);
$charset='UTF-8';
$rootdir = __DIR__;
$host; //站点信息
global $host;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $https="https";
} else {
    $https="http";
}
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

function ubus_post($json)
{
    global $host;
    $ch = curl_init("http://$host[host]/ubus");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function ubus_login()
{
    global $host, $db;
    $json=temp('ubus_login');
    $ar=ubus_post($json)['result'];
    if ($ar[0] != 0) {
        goback("登陆错误,$json $host[user]");
    }
    $host['token']=$ar[1]['ubus_rpc_session'];
    $db->query("update hosts set token='$host[token]',login_time = datetime('now') where id='$host[id]'");
    $db->exec('COMMIT');
    $db->close();
}

function ubus($object, $method, $argu = '')
{
    global $host, $argu;
    if ($host['token'] =='' || $host['login_time'] < date('Y-m-d H:i:s', time() - 3600)) {
        ubus_login($host);
    }
    $host['object'] = $object;
    $host['method'] = $method;
    $host['argu'] = $argu;
    $json=temp('ubus');
    echo "$object,$method,$json";
    $ar=ubus_post($json);
    if (isset($ar['error']['message'])) {
        ubus_login($host);
        $json=temp('ubus');
        $ar=ubus_post($json);
    }
    return $ar['result'];
}
function update_system($id)
{
    global $db, $host;
    $host=$db->fetch_one_assoc("select * from hosts where id='$id'");
    if ($host['user']=='') {
        $host['user']='root';
    }
    if ($host['passwd']=='') {
        $host['passwd']='admin';
    }
    $ret=ubus('system', 'board');
    if ($ret[0] != 0) {
        goback("错误 $ret");
    }
    $ret = $ret[1];
/*
 [1] => Array
                (
                    [kernel] => 6.6.93
                    [hostname] => bei_xi_2_42
                    [system] => Atheros AR7161 rev 2
                    [model] => Netgear WNDR3800CH
                    [board_name] => netgear,wndr3800ch
                    [rootfs_type] => squashfs
                    [release] => Array
                        (
                            [distribution] => OpenWrt
                            [version] => 24.10.2
                            [revision] => r28739-d9340319c6
                            [target] => ath79/generic
                            [description] => OpenWrt 24.10.2 r28739-d9340319c6
                            [builddate] => 1750711236
                        )

                )
*/
    $release = $ret['release'];
    $db->query("update hosts set hostname='$ret[hostname]',model='$ret[model]',soft='$release[distribution]',ver='$release[version]' where id='$host[id]'");
    $db->close();
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

function disp($temp = '', $temp1 = '')
{
    echo temp('head');
    if ($temp!='') {
        echo temp($temp);
    }
    if ($temp1!='') {
        echo temp($temp1);
    }
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
