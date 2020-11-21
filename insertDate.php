<?php

session_start();

require_once("dbh.class.php"); 

$connection = new Dbh();

if ( isset($_POST["submitBookingData"]) ) {
  $backgruppe = $_POST["InputBackgruppe"];
  $password = $_POST["InputPassword"];
  $requestedDate = $_POST["requestedDate"];
  $requestedSlot = $_POST["timeslot"];

  // lese aus dem angefragten datum wieder tag, monat und jahr
  $day = date("d", strtotime($requestedDate));
  $year = date("Y", strtotime($requestedDate));
  $month = date("m", strtotime($requestedDate));

  var_dump($year);
  var_dump($month);
  var_dump($backgruppe);

  // pruefe ob backgruppe gewaehlt
  if ( $backgruppe == "0" ) {
    // keine Backgruppe gewaehlt
    echo "<script> alert('Fehler: Bitte Backgruppe w&auml;hlen'); </script>";
    header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=failBackgruppe");
  } else {

    // lese Passwort der Backgruppe aus Datenbank
    $sql = "SELECT passwort FROM backgruppen WHERE backgruppeName = ?";
    $stmt = $connection->connect()->prepare($sql);
    $stmt->execute( [$backgruppe] );

    if ( $result = $stmt->fetchAll() ) {
      $passwordFromDb = $result[0]["passwort"]; 

      // pruefe ob passwort richtig eingegeben wurde
      if ( $password==$passwordFromDb ) {

        // schreibe PW und Gruppe in session-cookie
        $_SESSION["password"] = $password;
        $_SESSION["backgruppe"] = $backgruppe;

        // lese backgruppen type
        $sql = "SELECT type FROM backgruppen WHERE backgruppeName = ?";
        $stmt = $connection->connect()->prepare($sql);
        $stmt->execute( [$backgruppe] );
        $result = $stmt->fetchAll();
        $backkgruppenType = $result[0]["type"]; 

        // wenn buchung vor dem stichtag oder backgruppentyp nicht "vorstand" dann Fehler
        // stichtag ist 13 Monate in der Vergangenheit
        if ( $month==1) {
          $dateToCompare = strval(intval($year)-2) . "-12-" . $day;
        } else {
          $dateToCompare = strval(intval($year)-1) . "-" . sprintf("%'.02d", intval($month) - 1) . "-" . $day;
        }

        $today = date("Y-m-d");
        if ( $today<$dateToCompare and $backkgruppenType!="vorstand" ) {
          echo "<script> alert('Fehler: Dieser Termin kann erst ab " . $dateToCompare . " gebucht werden.'); </script>";
          header("Location: index_local.php?month=" . $month . "&year=" . $year . "&msg=failToEarly");
          exit();
        }

        // pruefe ob termin bereits gebucht wurde
        $sql = "SELECT backtermin FROM backtermine WHERE storniert!='ja' AND slot = ?";
        $stmt = $connection->connect()->prepare($sql);
        $stmt->execute( [$requestedSlot] );

        if ($result = $stmt->fetchAll()) {
          foreach ( $result as $row ) {
            $bookings[] = $row["backtermin"];
          }
        } 

        if ( !in_array($requestedDate, $bookings) ) {
          // Termin ist noch frei und wird gebucht

          $newQuery = "INSERT INTO backtermine (id,backgruppeName,backtermin,storniert,slot) VALUES (NULL,:backgruppe,:requestedDate,'nein',:slot)";
          $newStmt = $connection->connect()->prepare($newQuery);
          $newStmt->execute( array("backgruppe" => $backgruppe, "requestedDate" => $requestedDate, "slot" => $requestedSlot) );
          if ($newStmt) {
            echo "<script> alert('Backtermin gespeichert'); </script>";
            header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=successInsert");
          }

        } else {
          // Termin ist bereits gebucht
          echo "<script> alert('Fehler: Termin ist bereits gebucht'); </script>";
          header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=failInsert");
        }

      } else {
        // Passwort wurde falsch eingegeben
        echo "<script> alert('Fehler: falsches Passwort'); </script>";
        header("Location: index.php?month=" . $month . "&year=" . $year . "&msg=failPW");
      }
    }
  }

}

?>
