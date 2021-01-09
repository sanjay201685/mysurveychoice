<?php

try {
  $hostname = "localhost";
  $username = "root"; 
  $password = "root";
  $dbname = "mysurveychoice";

  $conn = new PDO("mysql:host=$hostname; dbname=$dbname", $username, $password);
} catch (PDOException $e){
  echo "Error!: " . $e->getMessage() . "<br/>";
  die();
}