<?php
include '../function.php';
$value=$db->fetch_one_assoc("select '12345' 字段1, '114514' 字段2 ");
echo json_encode($value, JSON_UNESCAPED_UNICODE);
