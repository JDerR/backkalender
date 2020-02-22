<?php

session_start();

echo var_dump($_POST);

if ( isset($_POST["acceptCookie"]) ) {
  $_SESSION["einv"] = "einverstanden";
  header("Location: index.php?month=" . $_POST["month"] . "&year=" . $_POST["year"] . "&msg=" . $_POST["msg"] );
}

if ( isset($_POST["deleteCookie"]) ) {
  session_destroy();
  header("Location: index.php?month=" . $_POST["month"] . "&year=" . $_POST["year"] . "&msg=" . $_POST["msg"] );
}
