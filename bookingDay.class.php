<?php

class BookingDay extends Dbh {

  private $backtermin;
  // $gebucht Boolean ob Tag gebucht wurde
  private $gebucht;
  // $storniert Boolean ob Tag storniert wurde
  private $storniert;
  // slots falls gebucht
  private $slots = array();
  // Backgruppen Name falls gebucht
  private $gruppe;

  // SETTERS
  public function setBacktermin($date) {
    $this->backtermin = $date;
  }

  public function setGebucht($key) {
    $this->gebucht = $key;
  }

  public function setStorniert($key) {
    if ( $this->gebucht == 1 ) {
      $this->storniert = $key;
    }
  }

  public function setGruppe($groupName) {
    if ( $this->gebucht == 1 ) {
      $this->gruppe = $groupName;
    }
  }

  //GETTERS
  public function getSlot() {
    return $this->slots;
  }

  public function getGebuchtFromDB() {
    $sql = "SELECT * FROM backtermine WHERE backtermin = :backtermin AND storniert = :storniert";
    $stmt = $this->connect()->prepare($sql);
    $stmt->execute( array("backtermin" => $this->backtermin, "storniert" => "nein") );
    $data = $stmt->fetchAll();

    if ( count($data)>0 ) {
      for ( $i=0; $i<count($data); $i++ ) {
        $this->slots += [$data[$i]["slot"] => $data[$i]["backgruppeName"]];
      }
      return true;
    } else {
      return false;
    }
  }

  public function getStorniertFromDB( $tageszeit = null) {

    if ( isset($tageszeit) ) {

      $sql = "SELECT * FROM backtermine WHERE backtermin = :backtermin AND storniert = :storniert AND slot = :slot";
      $stmt = $this->connect()->prepare($sql);
      $stmt->execute( array("backtermin" => $this->backtermin, "storniert" => "ja", "slot" => $tageszeit) );
      $data = $stmt->fetchAll();

      if ( count($data)>0 ) {
        return true;
      } else {
        return false;
      }

    } else {

      $sql = "SELECT * FROM backtermine WHERE backtermin = :backtermin AND storniert = :storniert";
      $stmt = $this->connect()->prepare($sql);
      $stmt->execute( array("backtermin" => $this->backtermin, "storniert" => "ja") );
      $data = $stmt->fetchAll();

      if ( count($data)>0 ) {
        return true;
      } else {
        return false;
      }

    }

  }

}
