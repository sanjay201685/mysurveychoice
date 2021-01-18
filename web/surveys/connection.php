<?php

require_once '../sites/default/settings.php';

try {
  $hostname = $databases['default']['default']['host'];
  $username = $databases['default']['default']['username']; 
  $password = $databases['default']['default']['password'];
  $dbname = $databases['default']['default']['database'];

  $conn = new PDO("mysql:host=$hostname; dbname=$dbname", $username, $password);
} catch (PDOException $e){
  echo "Error!: " . $e->getMessage() . "<br/>";
  die();
}