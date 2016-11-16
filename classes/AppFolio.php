<?php
//include(ABSPATH . '_config.php');

class AppFolio {
  private $xml_data;

  function __construct() {
    $this->loadXMLData();
    $this->printXMLData();
  }

  private function loadXMLData() {
    $this->xml_data = $this->downloadPageIfModified();
  }

  private function getLastXMLModificationTime() {
    return filemtime(RENTLINX_FILENAME);
  }

  private function downloadPageIfModified() {
    $ret_val = null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, APPFOLIO_XML_ENDPOINT);
    curl_setopt($ch, CURLOPT_FILETIME, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


//    $modified = curl_getinfo($ch, CURLINFO_FILETIME);
//    if ($modified > -1) {
    $ret_val = curl_exec($ch);

//    }
    // DEBUG
    $local_modified = $this->getLastXMLModificationTime();
    echo 'local modfied: ' . $local_modified . '   ';
    $remote_modification = curl_getinfo($ch, CURLINFO_FILETIME);

    echo 'remote modified: ' . $remote_modification;
    exit;
    var_dump($info);

    curl_close($ch);
    var_dump($ret_val);
    return $ret_val;
  }

  private function printXMLData() {
    var_dump($this->xml_data);
  }
}
