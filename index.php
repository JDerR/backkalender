<?php

header("Content-Type: text/html; charset=UTF-8");

// gibt Kalendertabelle eines Monats aus
// showCalender(12,2019)
function showCalender($month, $year) {
  // Wochennamen
  $daysOfTheWeek = array('Maandag','Dingsdag','Middeweeken','Dünnersdag','Freedag','Sünnabend','Sünndag');
  // erster Tag des Monats
  $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
  // Anzahl der Tage des Monats (28, 29, 30 oder 31)
  $numberOfDaysOfMonth = date('t',$firstDayOfMonth);
  // dateComponent erster Tag des Monats
  $dateCoponent = getdate($firstDayOfMonth);
  // Name des Monats
  $monthName = $dateCoponent['month'];
  // Wochentag des ersten Monatstages als integer 0-6
  $dayOfTheWeek = $dateCoponent['wday'];
  // heutiges Datum
  $dateToday = date('Y-m-d');

  // DB handler
  $connection = mysqli_connect("localhost","root","");
  $db = mysqli_select_db($connection, "backhausverein");
  mysqli_set_charset($connection, "utf8");

  // bereits gebuchte termine aus DB lesen
  $bookings = array();

  $query = "SELECT backtermin FROM backtermine WHERE storniert!='1'";
  $runQuery = mysqli_query($connection, $query);

  if ($runQuery) {
    foreach ( $runQuery as $row ) {
      $bookings[] = $row["backtermin"];
    }
  } 

  // bereits stornierte termine aus DB lesen
  $deleted = array();

  $query = "SELECT backtermin FROM backtermine WHERE storniert='1'";
  $runQuery = mysqli_query($connection, $query);

  if ($runQuery) {
    foreach ( $runQuery as $row ) {
      $deleted[] = $row["backtermin"];
    }
  } 

  // HTML table fuer Ausgabe
  $calender = "<center><h1>" . $monthName . ", " . $year . "   " . $month . "</h1></center><br>";

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
    $calender .= "<th class='header'>" . $day . "</th>";
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

    // wurde der Tag gebucht?
    if ( in_array("$year-$month-$dayCountStr", $bookings) ) {

      // lese aus DB welche Gruppe den Termin gebucht hat
      $query = "SELECT backgruppeName FROM backtermine WHERE backtermin=" . "'$year-$month-$dayCountStr'" . "and storniert!='1'";
      $runQuery = mysqli_query($connection, $query);
      if ( $result = $runQuery->fetch_row() ) {

        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-info btn-xs' data-toggle='modal' disabled>" . $result[0] . "</button>";
        } else {
          $calender .= "<button class='btn btn-info btn-xs' data-toggle='modal' data-target='#deleteModal' data-request='$year-$month-$dayCountStr' data-gruppe='$result[0]'>" . $result[0] . "</button>";
        }

      }
    } else {

      // wurde der Tag bereits einmal storniert?
      if ( in_array("$year-$month-$dayCountStr", $deleted) ) {
 
        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-warning btn-xs' disabled>wieder frei</button>";
        } else {
          $calender .= "<button class='btn btn-warning btn-xs' data-toggle='modal' data-target='#bookingModal' data-request='$year-$month-$dayCountStr'>wieder frei</button>";
        }

      } else {

        // Tag in der Vergangenheit?
        if ( "$year-$month-$dayCountStr" < $dateToday ) {
          $calender .= "<button class='btn btn-success btn-xs' disabled>frei</button>";
        } else {
          $calender .= "<button class='btn btn-success btn-xs' data-toggle='modal' data-target='#bookingModal' data-request='$year-$month-$dayCountStr'>frei</button>";
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

  $connection = mysqli_connect("localhost","root","");
  $db = mysqli_select_db($connection, "backhausverein");
  mysqli_set_charset($connection, "utf8");

  $query = "SELECT backgruppeName FROM backgruppen WHERE aktiv = '1'";
  $runQuery = mysqli_query($connection, $query);

  $backgruppenList = "";

  if ($runQuery) {
    foreach ( $runQuery as $row ) {
      $backgruppenList .= "<option>" . $row["backgruppeName"] . "</option>";
    }
  } 

  // eintraege sortieren
  asort($backgruppenList);

  echo $backgruppenList;
}

?>


<html lang="de">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
  <style>
    table{ table-layout: fixed; }
    td{ width: 33%; }
  </style>
</head>

<body>

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
              <select class="form-control" name="InputBackgruppe" placeholder="Backgruppe wählen">
                <option>Bitte Backgruppe wählen</option>
                <!-- Lese Backgruppen aus DB -->
                <?php fetchBackgruppen(); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="InputPassword">Passwort</label>
              <input type="password" class="form-control" name="InputPassword" placeholder="">
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
              <input type="password" class="form-control" name="InputPassword" placeholder="">
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
            echo $_GET["msg"];
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
    })
  </script>

</body>

</html>
