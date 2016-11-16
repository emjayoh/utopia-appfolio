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
    if (file_exists(RENTLINX_FILENAME)) {
      return filemtime(RENTLINX_FILENAME);
    } else {
      return null;
    }
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
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $ret_val_body = substr($ret_val, $header_size);
    $this->xml_data = simplexml_load_string($ret_val_body);


//    DEBUG
//    echo 'local modfied: ' . $local_modified . '   ';
//    echo 'remote modified: ' . $remote_modified;

    // Only write file if not exist or older than remote
    if (!$local_modified || $local_modified < $remote_modified) {
      $modified = true;
      file_put_contents(RENTLINX_FILENAME, $ret_val_body);
    }

    curl_close($ch);

    return $modified;
  }

  private function callGoogleAPI() {
    foreach($this->xml_data->Properties->children() as $property) {
      $full_address = $property->Address . ' ' . $property->City . ' ' . $property->State . ' ' . $property->Zip;

      if (isset($full_address) && !empty($full_address)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_GEOCODE_API_ENDPOINT . 'address=' . rawurlencode($full_address) . '&key=' . GOOGLE_API_KEY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret_val = curl_exec($ch);

        if (isset($ret_val) && !empty($ret_val)) {
          $json = json_decode($ret_val);
          var_dump($json->results[0]);

          foreach($json->results[0]->address_components as $location) {
            if (in_array('administrative_area_level_2', $location->types)) { $county = $location->long_name; };
            if (in_array('neighborhood', $location->types)) { $neighborhood = $location->long_name; }
            if (in_array('locality', $location->types)) { $city = $location->long_name; }

            // Add Attributes to XML
            if (!empty($city)) {
              if ($city == 'Temecula') {
                $county = 'Temecula';
              } elseif ($city == 'Palm Springs') {
                $county = 'Palm Springs';
              }
            }
          }

          $property->Address->addAttribute('full_address', $full_address);

          if (isset($county) && !empty($county)) {
            $property->Address->addAttribute('county', $county);
          }

          if (isset($neighborhood) && !empty($neighborhood)) {
            $property->Address->addAttribute('neighborhood', $neighborhood);
          }
        }
      }
    }
    $this->xml_data->asXML('appfolio_1.xml');
  }

  private function printXMLData() {
    var_dump($this->xml_data);
  }
}
