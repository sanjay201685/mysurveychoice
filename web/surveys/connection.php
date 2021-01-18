<?php

require_once '../sites/default/settings.php';

try {
  $hostname = $databases['default']['host'];
  $username = $databases['default']['username']; 
  $password = $databases['default']['password'];
  $dbname = $databases['default']['default']['database'];

  $conn = new PDO("mysql:host=$hostname; dbname=$dbname", $username, $password);
} catch (PDOException $e){
  echo "Error!: " . $e->getMessage() . "<br/>";
  die();
}