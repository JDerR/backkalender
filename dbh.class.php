<?php

// Database Handler
class Dbh {

  private $host = "localhost";
  private $user = "root";
  private $pass = "";
  private $dbName = "backhausverein";

  public function connect() {
    $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';charset=utf8';
    $pdo = new PDO( $dsn, $this->user, $this->pass );
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
  }

}
