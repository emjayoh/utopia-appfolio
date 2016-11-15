<?php
include(ABSPATH . '_config.php');

class AppFolio {
  private $properties, $xml_data;

  function __construct() {
    $this->loadXMLData();
  }

  private function loadXMLData() {
    $this->xml_data = $this->downloadPageIfModified();
  }

  private function downloadPageIfModified() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, APPFOLIO_XML_ENDPOINT));
    curl_setopt($ch, CURLOPT_FILETIME, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    if (curl_getinfo($ch, CURLINFO_FILETIME) > -1) {}

    $ret_val = curl_exec($ch);
    curl_close($ch);

    return $ret_val;
  }
}
