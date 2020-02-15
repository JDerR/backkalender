<?php

require_once("dbh.class.php"); 

$connection = new Dbh();

if ( isset($_POST["submitBookingData"]) ) {
  $backgruppe = $_POST["InputBackgruppe"];
  $password = $_POST["InputPassword"];
  $requestedDate = $_POST["requestedDate"];
  $requestedSlot = $_POST["timeslot"];

  // lese aus dem angefragten datum wieder monat und jahr
  $year = date("Y", strtotime($requestedDate));
  $month = date("m", strtotime($requestedDate));

  // lese Passwort der Backgruppe aus Datenbank
  $sql = "SELECT passwort FROM backgruppen WHERE backgruppeName = ?";
  $stmt = $connection->connect()->prepare($sql);
  $stmt->execute( [$backgruppe] );

  if ( $result = $stmt->fetchAll() ) {
    $passwordFromDb = $result[0]["passwort"]; 

    // pruefe ob passwort richtig eingegeben wurde
    if ( $password==$passwordFromDb ) {

      // beim angefragten backtermin "storniert" auf Wert 1 setzen
      $newQuery = "UPDATE backtermine SET storniert = 'ja' WHERE backtermin = :requestedDate AND slot = :slot";
      $newStmt = $connection->connect()->prepare($newQuery);

      if ( $newStmt->execute( array("requestedDate" => $requestedDate, "slot" => $requestedSlot) ) ) {

        echo "<script> alert('Backtermin storniert'); </script>";
        header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=<div class='alert alert-success' role='alert'>Backtermin erfolgreich storniert.</div>");
      }

    } else {
      echo "<script> alert('Fehler: falsches Passwort'); </script>";
      header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=<div class='alert alert-danger' role='alert'>Fehler: Sie haben das falsche Passwort eingegeben.</div>");
    }
  }

}

?>
