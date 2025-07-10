<?php
include __DIR__.'/../function.php';
$re=$db->query("select * from hosts ");
while ($host=$db->fetch_assoc($re)) {
    if (empty($host['user'])) {
        $host['user']="root";
    }
    $host['passwd0'] = '******';
    if (empty($host['passwd'])) {
        $host['passwd']='admin';
        $host['passwd0']='admin';
    }
    $lines.=temp('hosts_line');
}
disp('hosts');
