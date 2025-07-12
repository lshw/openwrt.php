<?php
include __DIR__."/../function.php";
get_str($_GET['id']);
get_str($_GET['cmd']);
//get_str($_GET['arg']);
get_str($_GET['method']);
get_str($_GET['object']);
if ($_GET['id'] != '0') {
    $host=$db->fetch_one_assoc("select * from hosts where id='$_GET[id]'");
    if (empty($host['id'])) {
        goback("没有找到站点");
    }
    if (!empty($_GET['object']) && !empty($_GET['method'])) {
        $host['object']=$_GET['object'];
        $host['method']=$_GET['method'];
        $host['arg']=$_GET['arg'];
        $host['cmd']=$_GET['cmd'];
        $send=temp('ubus');
        $ret0=ubus_post($send);
        $ret=print_r($ret0, 1);
    }
}
if (!isset($_GET['object']) and !isset($_GET['method'])) {
    $_GET['object'] = 'system';
    $_GET['method'] = 'info';
    $_GET['arg'] = '';
}
disp('debug');
