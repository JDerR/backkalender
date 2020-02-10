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

  echo var_dump($year);
  echo var_dump($month);

  // lese Passwort der Backgruppe aus Datenbank
  $query = "SELECT passwort FROM backgruppen WHERE backgruppeName = '" . $backgruppe . "'";
  $runQuery = mysqli_query($connection, $query);
  if ( $result = $runQuery->fetch_row() ) {
    $passwordFromDb = $result[0]; 

    // pruefe ob passwort richtig eingegeben wurde
    if ( $password==$passwordFromDb ) {

      // pruefe ob termin bereits gebucht wurde
      $query = "SELECT backtermin FROM backtermine WHERE storniert!='1'";
      $runQuery = mysqli_query($connection, $query);

      if ($runQuery) {
        foreach ( $runQuery as $row ) {
          $bookings[] = $row["backtermin"];
        }
      } 

      if ( !in_array($requestedDate, $bookings) ) {
        // Termin ist noch frei und wird gebucht

        // angefragter backtermin in DB speichern
        $newQuery = "INSERT INTO backtermine (id,backgruppeName,backtermin,storniert) VALUES (NULL,'" . $backgruppe . "','" . $requestedDate . "','0')";
        $runNewQuery = mysqli_query($connection, $newQuery);
        echo var_dump($runNewQuery);
        if ($runNewQuery) {
          echo "<script> alert('Backtermin gespeichert'); </script>";
          header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=<div class='alert alert-success' role='alert'>Backtermin erfolgreich eingetragen.</div>");
        }

      } else {
        // Termin ist bereits gebucht
        echo "<script> alert('Fehler: Termin ist bereits gebucht'); </script>";
        header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=<div class='alert alert-danger' role='alert'>Fehler: Der Termin ist bereits vergeben.</div>");
      }

    } else {
      // Passwort wurde falsch eingegeben
      echo "<script> alert('Fehler: falsches Passwort'); </script>";
      header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=<div class='alert alert-danger' role='alert'>Fehler: Sie haben das falsche Passwort eingegeben.</div>");
    }
  }

}

?>
