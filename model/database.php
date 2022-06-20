<?php

ini_set('display_errors', 1);
ini_set('display_startup errors', 1);
error_reporting(E_ALL);

class DbConnect {

  public function connect($config)
  {
    try {
      $conn = new PDO('mysql:host=' . $config['server'] . ';dbname=' . $config['name'], $config['user'], $config['password']);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $conn;
    } catch (\Exception $e) {
      echo "Database Error: " . $e->getMessage();
    }
  }
}
