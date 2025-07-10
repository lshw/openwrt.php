<?php
include __DIR__.'/../function.php';
$re=$db->query("select * from hosts ");
while($host=$db->fetch_assoc($re)) {
if(empty($host['user']))
$host['user']="root";
if(empty($host['passwd']))
$host['passwd']="admin";
$lines.=temp('hosts_line');
}
disp('hosts');
