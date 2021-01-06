<?php

$servername = "localhost";
$username = "root";
$password = "saregamaA1!";
$dbname = "myr_dmr";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$data['request_time'] = $_SERVER['REQUEST_TIME'];
$data['ptr'] = $_SERVER['REQUEST_URI'];

$sql = "INSERT INTO temp_t (request_time, ptr) VALUES (:request_time, :ptr)";
$conn->prepare($sql)->execute($data);

$conn = null;

print '<pre>'; print_r([]); exit(__FUNCTION__);

?>