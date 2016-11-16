<?php
//include(ABSPATH . '_config.php');

class AppFolio {
  private $xml_data;

  function __construct() {
    if ($this->downloadPageIfModified()) {
      $this->callGoogleAPI();
    }
  }

  private function getLastXMLModificationTime() {
    return filemtime(RENTLINX_FILENAME);
  }

  private function downloadPageIfModified() {
    $ret_val = null;
    $modified = false;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, APPFOLIO_XML_ENDPOINT);
    curl_setopt($ch, CURLOPT_FILETIME, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $ret_val = curl_exec($ch);
    $local_modified = $this->getLastXMLModificationTime();
    $remote_modified = curl_getinfo($ch, CURLINFO_FILETIME);

//    DEBUG
//    echo 'local modfied: ' . $local_modified . '   ';
//    echo 'remote modified: ' . $remote_modified;

    // Only write file if not exist or older than remote
    if (!$local_modified || $local_modified < $remote_modified) {
      $modified = true;
      file_put_contents(RENTLINX_FILENAME, $ret_val);
    }

    curl_close($ch);
    $this->raw_xml_data = simplexml_load_string($ret_val);

    return $modified;
  }

  private function callGoogleAPI() {
    foreach($this->xml_data->Properties->children() as $property) {
      $full_address = $property->Address . ' ' . $property->City . ' ' . $property->State . ' ' . $properties->Zip;
    }
  }

  private function printXMLData() {
    var_dump($this->xml_data);
  }
}
