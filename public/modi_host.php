<?php
include __DIR__."/../function.php";
get_str($_GET['id']);
get_str($_GET['field']);
get_str($_GET['data']);
if ($_GET['id'] != '0') {
    $host=$db->fetch_one_assoc("select * from hosts where id='$_GET[id]'");
    if (empty($host['id'])) {
        goback("没有找到站点");
    }
}
switch ($_GET['field']) {
    case 'host':
        if ($_GET['id']=='0') {
            $host=$db->fetch_one_assoc("select * from hosts where host='$_GET[data]'");
            if (!empty($host['id'])) {
                goback("已经存在");
            }
            $db->query("insert into hosts (host) values('$_GET[data]');");
        } else {
            $db->query("update hosts set host='$_GET[data]' where id='$_GET[id]'");
        }
        break;
    case 'user':
    case 'passwd':
        $db->query("update hosts set `$_GET[field]`='$_GET[data]' where id='$_GET[id]'");
}
$db->exec('COMMIT');
$db->close();
goback("ok");
