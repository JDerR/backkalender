<?php

$connection = mysqli_connect("localhost","root","");
$db = mysqli_select_db($connection, "backhausverein");
mysqli_set_charset($connection, "utf8");

if ( isset($_POST["submitBookingData"]) ) {
  $backgruppe = $_POST["InputBackgruppe"];
  $password = $_POST["InputPassword"];
  $requestedDate = $_POST["requestedDate"];

  // lese aus dem angefragten datum wieder monat und jahr
  $year = date("Y", strtotime($requestedDate));
  $month = date("m", strtotime($requestedDate));

  // lese Passwort der Backgruppe aus Datenbank
  $query = "SELECT passwort FROM backgruppen WHERE backgruppeName = '" . $backgruppe . "'";
  $runQuery = mysqli_query($connection, $query);
  if ( $result = $runQuery->fetch_row() ) {
    $passwordFromDb = $result[0]; 

    // pruefe ob passwort richtig eingegeben wurde
    if ( $password==$passwordFromDb ) {

      // beim angefragten backtermin "storniert" auf Wert 1 setzen
      $newQuery = "UPDATE backtermine SET storniert = '1' WHERE backtermin='" . $requestedDate . "'";
      $runNewQuery = mysqli_query($connection, $newQuery);
      echo var_dump($newQuery);

      if ($runNewQuery) {
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
