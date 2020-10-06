<?php

header("Content-Type: text/html; charset=UTF-8");

session_start();

require("dbh.class.php"); 
require("bookingDay.class.php");

// gibt Kalendertabelle eines Monats aus
// showCalender(12,2019)
function showCalender($month, $year) {

  // Wochennamen
  $daysOfTheWeek = array('Mo','Di','Mi','Do','Fr','Sa','So');
  // erster Tag des Monats
  $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
  // Anzahl der Tage des Monats (28, 29, 30 oder 31)
  $numberOfDaysOfMonth = date('t',$firstDayOfMonth);
  // dateComponent erster Tag des Monats
  $dateCoponent = getdate($firstDayOfMonth);
  // Name des Monats
  $monthNames = array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
  $monthName = $monthNames[ $dateCoponent['mon']-1 ];
  // Wochentag des ersten Monatstages als integer 0-6
  $dayOfTheWeek = $dateCoponent['wday'];
  // heutiges Datum
  $dateToday = date('Y-m-d');

  // HTML table fuer Ausgabe
  $calender = "<center><h1>" . $monthName . " " . $year . "</h1></center><br>";

  // Buttons um Monat zu wechseln
  $calender .= "<center>";
  $calender .= "<a class='btn btn-primary' href='?month=".date('m',mktime(0,0,0,$month-1,1,$year))."&year=".date('Y',mktime(0,0,0,$month-1,1,$year))."'><<</a>&nbsp;";
  $calender .= "<a class='btn btn-primary' href='?month=".date('m')."&year=".date('Y')."'>aktueller Monat</a>&nbsp;";
  $calender .= "<a class='btn btn-primary' href='?month=".date('m',mktime(0,0,0,$month+1,1,$year))."&year=".date('Y',mktime(0,0,0,$month+1,1,$year))."'>>></a>";
  $calender .= "</center><br>";

  // Kopfzeile mit Wochentagen
  $calender .= "<table class='table table-bordered'>";
  $calender .= "<tr>";
  foreach ($daysOfTheWeek as $day) {
    $calender .= "<th class='header'><h4>" . $day . "</h4></th>";
  }
  $calender .= "</tr>";

  // erste Zeile falls ggf nach links mit leeren Felder aufgefuellt werden muss
  $calender .= "<tr>";
  if ( $dayOfTheWeek > 0 ) {
    for ($i=0; $i<$dayOfTheWeek-1; $i++) {
      $calender .= "<td></td>";
    }
  } else {
    for ($i=0; $i<6; $i++) {
      $calender .= "<td></td>";
    }
  }
  // counter fuer Tage
  $dayCount = 1;

  // alle Tage des Monats
  while ( $dayCount <= $numberOfDaysOfMonth ){

    // ggf fuehrende Null an counter anhaengen
    $dayCountStr = str_pad($dayCount, 2, '0', STR_PAD_LEFT);

    // heutigen Tag highlighten
    if ( "$year-$month-$dayCountStr" == $dateToday ) {
      $calender .= "<td class='bg-info'>";
    } else {
      $calender .= "<td>";
    }
    $calender .= "<h4>" . $dayCountStr . "</h4>";


    $newBookingDay = new BookingDay();
    $newBookingDay->setBacktermin("$year-$month-$dayCountStr");

    // Button Logic Baum 
    // Gibt es aktuell gueltige Buchung? ja/nein   
    if ( $newBookingDay->getGebuchtFromDB() ) {
    
      // wurde der ganze Tag von einer Gruppe gebucht?
      if ( in_array("ganzerTag", array_keys($newBookingDay->getSlot())) ) {

        $grpName = $newBookingDay->getSlot()["ganzerTag"];
        $slot = "ganzerTag";
        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' disabled data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
        } else {
          $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#deleteModal' data-request='$year-$month-$dayCountStr' data-gruppe='$grpName' data-slot='$slot' data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
        }

      } else if ( in_array("morgen", array_keys($newBookingDay->getSlot())) ) {

        // morgens besetzt durch grupppe
        // mittags frei?
        if ( in_array("nachmittag", array_keys($newBookingDay->getSlot())) ) {

          // morgen und nachmittag besetzt durch gruppe
          $grpName = $newBookingDay->getSlot()["morgen"];
          $slot = "morgen";
          // Tag in der Vergangenheit?
          if ( "$year-$month-$dayCountStr" < $dateToday ) {
            $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' disabled data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
          } else {
            $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#deleteModal' data-request='$year-$month-$dayCountStr' data-gruppe='$grpName' data-slot='$slot' data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
          }

          // neue Zeile, dann Nachmittaggruppe
          $calender .= "<br><br>";

          $grpName = $newBookingDay->getSlot()["nachmittag"];
          $slot = "nachmittag";
          // Tag in der Vergangenheit?
          if ( "$year-$month-$dayCountStr" < $dateToday ) {
            $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' disabled data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
          } else {
            $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#deleteModal' data-request='$year-$month-$dayCountStr' data-gruppe='$grpName' data-slot='$slot' data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
          }

        } else {

          // morgen besetzt durch gruppe, nachmittag frei
          $grpName = $newBookingDay->getSlot()["morgen"];
          $slot = "morgen";
          // Tag in der Vergangenheit?
          if ( "$year-$month-$dayCountStr" < $dateToday ) {
            $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' disabled data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
          } else {
            $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#deleteModal' data-request='$year-$month-$dayCountStr' data-gruppe='$grpName' data-slot='$slot' data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
          }

          // neue Zeile, dann nachmittag frei
          $calender .= "<br><br>";
          $slot = "nachmittag";

          // termin nachmittag bereits einmal storniert?
          if ( $newBookingDay->getStorniertFromDB("nachmittag") ) {

            // Tag in der Vergangenheit?
            if ( "$year-$month-$dayCountStr" < $dateToday ) {
              $calender .= "<button class='btn btn-warning btn-xs' style='font-size: 1vw' disabled>wieder frei</button>";
            } else {
              $calender .= "<button class='btn btn-warning btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#bookingModalFixedSlot' data-request='$year-$month-$dayCountStr' data-slot='$slot'>wieder frei</button>";
            }

          } else {

            // Tag in der Vergangenheit?
            if ( "$year-$month-$dayCountStr" < $dateToday ) {
              $calender .= "<button class='btn btn-success btn-xs' style='font-size: 1vw' disabled>frei</button>";
            } else {
              $calender .= "<button class='btn btn-success btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#bookingModalFixedSlot' data-request='$year-$month-$dayCountStr' data-slot='$slot'>frei</button>";
            }

          }

        }

      } else {

        // termin nachmittags muss gebucht sein, morgens frei
        $slot = "morgen";
        // termin morgnens bereits einmal storniert?
        if ( $newBookingDay->getStorniertFromDB("morgen") ) {

          // Tag in der Vergangenheit?
          if ( "$year-$month-$dayCountStr" < $dateToday ) {
            $calender .= "<button class='btn btn-warning btn-xs' style='font-size: 1vw' disabled>wieder frei</button>";
          } else {
            $calender .= "<button class='btn btn-warning btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#bookingModalFixedSlot' data-request='$year-$month-$dayCountStr' data-slot='$slot'>wieder frei</button>";
          }

        } else {

          // Tag in der Vergangenheit?
          if ( "$year-$month-$dayCountStr" < $dateToday ) {
            $calender .= "<button class='btn btn-success btn-xs' style='font-size: 1vw' disabled>frei</button>";
          } else {
            $calender .= "<button class='btn btn-success btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#bookingModalFixedSlot' data-request='$year-$month-$dayCountStr' data-slot='$slot'>frei</button>";
          }

        }

        // neue Zeile, dann nachmittag gebucht durch gruppe
        $calender .= "<br><br>";

        $grpName = $newBookingDay->getSlot()["nachmittag"];
        $slot = "nachmittag";
        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' disabled data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
        } else {
          $calender .= "<button class='btn btn-info btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#deleteModal' data-request='$year-$month-$dayCountStr' data-gruppe='$grpName' data-slot='$slot' data-toggle='tooltip' data-placement='bottom' title='" . $grpName . "'>" . shorter($grpName) . "</button>";
        }

      }

    } else {

      // wurde der Tag zuvor einmal storniert?
      if ( $newBookingDay->getStorniertFromDB() ) {

        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-warning btn-xs' style='font-size: 1vw' disabled>wieder frei</button>";
        } else {
          $calender .= "<button class='btn btn-warning btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#bookingModal' data-request='$year-$month-$dayCountStr'>wieder frei</button>";
        }

      } else {

        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-success btn-xs' style='font-size: 1vw' disabled>frei</button>";
        } else {
          $calender .= "<button class='btn btn-success btn-xs' style='font-size: 1vw' data-toggle='modal' data-target='#bookingModal' data-request='$year-$month-$dayCountStr'>frei</button>";
        }

      }

    }

    $calender .= "</td>";

    // wenn Sünndag erreicht, dann neue Zeile 
    if ( ($dayOfTheWeek + $dayCount) % 7 == 1 ) {
      $calender .= "</tr><tr>";
    }
    $dayCount++;
  }

  // letzte Zeile ggf mit leeren Feldern auffuellen
  while ( ($dayOfTheWeek + $dayCount) % 7 != 2 ) {
    $calender .= "<td></td>";
    $dayCount++;
  }
  $calender .= "</tr>";
  $calender .= "</table>";

  echo $calender;
}


// liesst backgruppen aus db fuer buchungsmenue
function fetchBackgruppen() {

  $connection = new Dbh();

  $query = "SELECT backgruppeName FROM backgruppen WHERE aktiv = '1'";
  $stmt = $connection->connect()->prepare($query);
  $stmt->execute();

  $backgruppenList = "";

  if ( $result = $stmt->fetchAll() ) {
    // eintraege sortieren
    asort($result);
    foreach ( $result as $row ) {
      $backgruppenList .= "<option>" . $row["backgruppeName"] . "</option>";
    }
  } 

  echo $backgruppenList;
}


// kuerzt string falls zu lang
function shorter( $str ) {
  if ( strlen($str)>12 ) {
    return substr( $str, 0, 9 ) . "...";
  } else {
    return $str;
  }
}

?>


<html lang="de">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <style>
    table{ table-layout: fixed; }
    td{ width: 33%; }
  </style>
  <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
</head>

<body>

  <!-- Dialog fuer Erklaerung der Farben (Legende) -->
  <div class="modal hide fade" id="infoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Legende</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Jeder Buchungsoption ist eine Farbe zugeordnet:</p>
          <p>
            <table class="table">
              <tr>
                <td style="width: 30%"><button class='btn btn-success btn-xs'>frei</button></td>
                <td style="width: 65%"">... freier Termin, Sie können eine Buchung vornehmen.</td>
              </tr>
              <tr>
                <td><button class='btn btn-warning btn-xs'>wieder frei</button></td>
                <td>... der Termin wurde zuvor gebucht, dann aber wieder von der Backgruppe storniert. Sie können eine Buchung vornehmen.</td>
              </tr>
              <tr>
                <td><button class='btn btn-info btn-xs'>Backgruppe</button></td>
                <td>... der Termin wurde von der eingetragenen Backgruppe gebucht. Nur die Backgruppe kann den Termin wieder stornieren.</td>
              </tr>
            </table>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Weiter</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Passwort speichern Dialog -->
  <div class="modal hide fade" id="pwModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Passwort speichern?</h5>
        </div>
        <form action="cookieHandling.php" method="POST">
          <div class="modal-body">
            <p>Wollen Sie das Passwort speichern? </p>
            <p class="text-danger">Achtung: Diese Option verwendet <b  data-toggle="tooltip" data-placement="bottom" title="Cookies sind kleine Dateien mit denen ein Nutzer idetifiziert werden kann und in diesem Fall nichts, was im Backhaus gebacken wird!">Cookies</b>.</p>
            <div class="form-group">
              <input type="hidden" class="form-control InputYear" name="year" placeholder="">
              <input type="hidden" class="form-control InputMonth" name="month" placeholder="">
              <input type="hidden" class="form-control InputMsg" name="msg" placeholder="">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="acceptCookie">Ja, Weiter mit Cookies</button>
            <button type="submit" class="btn btn-secondary" name="deleteCookie">Nein</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Dialog fuer Terminbuchung -->
  <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingModalLabel">Backtermin ?? buchen:</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="insertDate.php" method="POST">
          <div class="modal-body">
            <div class="form-group">
              <label for="backgruppe">Backgruppe</label>
              <select class="form-control InputBackgruppe" name="InputBackgruppe" placeholder="Backgruppe wählen">
                <option value="0">Bitte Backgruppe wählen</option>
                <!-- Lese Backgruppen aus DB -->
                <?php fetchBackgruppen(); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="InputPassword">Passwort</label>
              <input type="password" class="form-control InputPassword" name="InputPassword" placeholder="">
            </div>
            <div class="form-group">
              <label for="timeslot">Wann gebucht?</label>
              <select class="form-control" name="timeslot" placeholder="ganzer Tag">
                <option value="ganzerTag">ganzer Tag</option>
                <option value="morgen">nur Vormittag bis ??:?? Uhr</option>
                <option value="nachmittag">nur Nachmittag ab ??:?? Uhr</option>
              </select>
            </div>
            <div class="form-group">
              <input type="hidden" class="form-control requestedDate" name="requestedDate" placeholder="">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Zurück</button>
            <button type="submit" name="submitBookingData" class="btn btn-primary">Termin buchen</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Dialog fuer Terminbuchung mit vorgegebenem Slot -->
  <div class="modal fade" id="bookingModalFixedSlot" tabindex="-1" role="dialog" aria-labelledby="bookingModalFixedSlotLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingModalFixedSlotLabel">Backtermin ?? buchen:</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="insertDate.php" method="POST">
          <div class="modal-body">
            <div class="form-group">
              <label for="backgruppe">Backgruppe</label>
              <select class="form-control InputBackgruppe" name="InputBackgruppe" placeholder="Backgruppe wählen">
                <option value="0">Bitte Backgruppe wählen</option>
                <!-- Lese Backgruppen aus DB -->
                <?php fetchBackgruppen(); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="InputPassword">Passwort</label>
              <input type="password" class="form-control InputPassword" name="InputPassword" placeholder="">
            </div>
            <div class="form-group">
              <label for="timeslot">Wann gebucht?</label>
              <input type="hidden" class="form-control InputTimeSlot" name="timeslot" placeholder="" readonly>
              <select class="form-control InputTimeSlot" name="timeslot" placeholder="" disabled>
                <option value="ganzerTag">ganzer Tag</option>
                <option value="morgen">nur Vormittag bis ??:?? Uhr</option>
                <option value="nachmittag">nur Nachmittag ab ??:?? Uhr</option>
              </select>
            </div>
            <div class="form-group">
              <input type="hidden" class="form-control requestedDate" name="requestedDate" placeholder="">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Zurück</button>
            <button type="submit" name="submitBookingData" class="btn btn-primary">Termin buchen</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Dialog fuer Stornierung -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Backtermin ?? stornieren:</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="deleteDate.php" method="POST">
          <div class="modal-body">
            <div class="form-group">
              <label for="InputBackgruppe">Backgruppe</label>
              <input type="text" class="form-control InputBackgruppe" name="InputBackgruppe" placeholder="" readonly>
            </div>
            <div class="form-group">
              <label for="InputPassword">Passwort</label>
              <input type="password" class="form-control InputPassword" name="InputPassword" placeholder="">
            </div>
            <div class="form-group">
              <label for="timeslot">Wann gebucht?</label>
              <input type="hidden" class="form-control InputTimeSlot" name="timeslot" placeholder="" readonly>
              <select class="form-control InputTimeSlot" name="timeslot" placeholder="" disabled>
                <option value="ganzerTag">ganzer Tag</option>
                <option value="morgen">nur Morgen bis ??:?? Uhr</option>
                <option value="nachmittag">nur Nachmittag ab ??:?? Uhr</option>
              </select>
            </div>
            <div class="form-group">
              <input type="hidden" class="form-control requestedDate" name="requestedDate" placeholder="">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Zurück</button>
            <button type="submit" name="submitBookingData" class="btn btn-primary">Termin stornieren</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <?php
          // Kalenderblatt
          if( isset($_GET['month']) && isset($_GET['year']) ) {
            $month = $_GET['month'];
            $year = $_GET['year'];
          } else {
            // dateCOmponents heutiges Datum
            $dateComponents = getdate();
            $month = $dateComponents['mon'];
            // ggf fuehrende Nullen ergaenzen
            $month = str_pad($month, 2 ,'0', STR_PAD_LEFT);	
            $year = $dateComponents['year'];
          }
          echo showCalender($month, $year);
        ?>
      </div>
    </div>
  </div>

  <!-- Message container -->
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <?php
          if( isset($_GET["msg"]) ) {
            if ( $_GET["msg"] == "successInsert" ) {
              echo "<div class='alert alert-success' role='alert'>Backtermin erfolgreich eingetragen.</div>";
            } else if ( $_GET["msg"] == "failInsert" ) {
              echo "<div class='alert alert-danger' role='alert'>Fehler: Der Termin ist bereits vergeben.</div>";
            } else if ( $_GET["msg"] == "failPW" ) {
              echo "<div class='alert alert-danger' role='alert'>Fehler: Sie haben das falsche Passwort eingegeben.</div>";
            } else if ( $_GET["msg"] == "failToEarly" ) {
              echo "<div class='alert alert-danger' role='alert'>Fehler: Dieser Termin kann erst ab dem 01.12." . $_GET["year"] . " gebucht werden.</div>";
            } else if ( $_GET["msg"] == "successDelete" ) {
              echo "<div class='alert alert-success' role='alert'>Backtermin erfolgreich storniert.</div>";
            } else if ( $_GET["msg"] == "failBackgruppe" ) {
              echo "<div class='alert alert-danger' role='alert'>Fehler: Bitte Backgruppe wählen.</div>";
            }
          }
        ?>
      </div>
    </div>
  </div>

  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="./jquery/jquery-3.3.1.slim.min.js"></script>
  <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>-->
  <script src="./bootstrap/js/bootstrap.min.js"></script>
  <script>
    // Dialog fuer Terminbuchung, Parameteruebergabe
    $('#bookingModal').on('show.bs.modal', function (event) {
      // Button der Dialog startet
      var button = $(event.relatedTarget) 
      // Lese Information aus data-* Attribut
      var recipient = button.data('request') 
      // konvertiere Parameter nach deutscher Datumskonvention
      convertedDate = recipient[8] + recipient[9] + "." + recipient[5] + recipient[6] + "." + recipient[0] + recipient[1] + recipient[2] + recipient[3]
      // Schreibe Parameter in Titelzeile in Modal
      var modal = $(this)
      modal.find('.modal-title').text('Backtermin am ' + convertedDate + ' buchen:')
      modal.find('.modal-body .form-group .requestedDate').val(recipient)
    })
  </script>

  <script>
    // Dialog fuer Terminbuchung mit vorgegebenem Slot, Parameteruebergabe
    $('#bookingModalFixedSlot').on('show.bs.modal', function (event) {
      // Button der Dialog startet
      var button = $(event.relatedTarget) 
      // Lese Information aus data-* Attribut
      var recipient = button.data('request') 
      // konvertiere Parameter nach deutscher Datumskonvention
      convertedDate = recipient[8] + recipient[9] + "." + recipient[5] + recipient[6] + "." + recipient[0] + recipient[1] + recipient[2] + recipient[3]
      // Schreibe Parameter in Titelzeile in Modal
      var modal = $(this)
      modal.find('.modal-title').text('Backtermin am ' + convertedDate + ' buchen:')
      modal.find('.modal-body .form-group .requestedDate').val(recipient)
      var slot = button.data('slot')
      modal.find('.modal-body .form-group .InputTimeSlot').val(slot)
    })
  </script>

  <script>
    // Dialog fuer Stornierung, Parameteruebergabe
    $('#deleteModal').on('show.bs.modal', function (event) {
      // Button der Dialog startet
      var button = $(event.relatedTarget) 
      // Lese Information aus data-* Attribut
      var recipient = button.data('request') 
      // konvertiere Parameter nach deutscher Datumskonvention
      convertedDate = recipient[8] + recipient[9] + "." + recipient[5] + recipient[6] + "." + recipient[0] + recipient[1] + recipient[2] + recipient[3]
      // Schreibe Parameter in Titelzeile in Modal
      var modal = $(this)
      modal.find('.modal-title').text('Backtermin am ' + convertedDate + ' stornieren:')
      modal.find('.modal-body .form-group .requestedDate').val(recipient)
      var gruppe = button.data('gruppe')
      modal.find('.modal-body .form-group .Inputbackgruppe').val(gruppe)
      var slot = button.data('slot')
      modal.find('.modal-body .form-group .InputTimeSlot').val(slot)
    })
  </script>

  <!-- InfoBox bei page load falls msg nicht gesetzt -->
  <?php
    if ( !isset($_GET["msg"]) & !isset($_GET["month"]) ) {
      $script = "<script>";
      $script .= "$(window).on('load',function(){";
      $script .= "if(screen.width<800){";
      $script .= "$('#infoModal').modal('show')";
      $script .= "}})";
      $script .= "</script>";
      echo $script;
    }
  ?>

  <!-- InfoBox bei page load falls session cookie gesetzt aber kein einverstaendnis sonst PW felder fuellen -->
  <?php
    $script = "<script>";
    $script .= "$(window).on('load',function(){";
    if ( isset($_SESSION["password"]) & !isset($_SESSION["einv"]) ) {
      $script .= "$('#pwModal').modal('show');";
      // falls gesetzt werte ueber hidden fields mitgeben
      if ( isset($_GET["year"]) & isset($_GET["month"]) & isset($_GET["msg"]) ) {
        echo $_GET["year"];
        $script .= "$('#pwModal').find('.modal-body .form-group .InputYear').val('" . $_GET["year"] . "');";
        $script .= "$('#pwModal').find('.modal-body .form-group .InputMonth').val('" . $_GET["month"] . "');";
        $script .= "$('#pwModal').find('.modal-body .form-group .InputMsg').val('" . $_GET["msg"] . "');";
      }
    } else if ( isset($_SESSION["password"]) & isset($_SESSION["einv"]) ) {
      // Passwort setzen
      $script .= "$('#bookingModal').find('.modal-body .form-group .InputPassword').val('" . $_SESSION["password"] . "');";
      $script .= "$('#bookingModalFixedSlot').find('.modal-body .form-group .InputPassword').val('" . $_SESSION["password"] . "');";
      $script .= "$('#deleteModal').find('.modal-body .form-group .InputPassword').val('" . $_SESSION["password"] . "');";
      // Backgruppe setzen
      $script .= "$('#bookingModal').find('.modal-body .form-group .Inputbackgruppe').val('" . $_SESSION["backgruppe"] . "');";
      $script .= "$('#bookingModalFixedSlot').find('.modal-body .form-group .Inputbackgruppe').val('" . $_SESSION["backgruppe"] . "');";
      $script .= "$('#deleteModal').find('.modal-body .form-group .Inputbackgruppe').val('" . $_SESSION["backgruppe"] . "');";
    }
    $script .= "});";
    $script .= "</script>";
    echo $script;
  ?>
      
</body>

</html>
