<?php
include __DIR__."/../function.php";
get_str($_GET['id']);
get_str($_GET['action']);
if ($_GET['id'] != '0') {
    $host=$db->fetch_one_assoc("select * from hosts where id='$_GET[id]'");
    if (empty($host['id'])) {
        goback("没有找到站点");
    }
}
switch ($_GET['action']) {
    case 'system':
        update_system($host['id']);
        break;
}
goback();
