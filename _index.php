<?php
require_once(ABS_PATH . '_config.php');

// =======================================================================//
// ! Parse AppFolio XML                                                   //
// =======================================================================//
if (file_exists(UTOPIA_RENTLINX_FILENAME)) {

  // Loads the xml and returns a simplexml object
  $xml = simplexml_load_file(UTOPIA_RENTLINX_FILENAME);

  foreach ($xml->Properties->children() as $property) {
    $full_address = $address . ' ' . $city . ' ' . $state . ' ' . $zip;

    // =======================================================================//
    // ! Call Google Geocoding API to determine neighborhood and county       //
    // =======================================================================//
    // Construct search query
    $search = (!empty($full_address) ? 'address=' . rawurlencode($full_address) : null);

    // Set api key
    $api_key = '&key=' . GOOGLE_API_KEY;

    // Build $request_url for api call
    $request_url = GOOGLE_GEOCODE_API_ENDPOINT . $search . $api_key;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $request_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $json = curl_exec($curl);
    curl_close($curl);

    // Parse API response
    if (!empty($json)) {
      $details = json_decode($json, true);

      foreach ($details['results'][0]['address_components'] as $location) {
        if (in_array('administrative_area_level_2', $location['types'])) { $county = $location['long_name']; }
      }

      foreach ($details['results'][0]['address_components'] as $location) {
        if (in_array('neighborhood', $location['types'])) { $neighborhood = $location['long_name']; }
      }

      foreach ($details['results'][0]['address_components'] as $location) {
        if (in_array('locality', $location['types'])) { $city = $location['long_name']; }
      }
    }

    // Add Attributes to XML
    if (!empty($city)) {
      if ($city == 'Temecula') {
        $county = 'Temecula';
      }  elseif ($city == 'Palm Springs') {
        $county = 'Palm Springs';
      }
    }

    $property->Address->addAttribute('FullAddress', $full_address);

    if (!empty($county)) {
      $property->Address->addAttribute('County', $county);
    }

    if (!empty($neighborhood)) {
      $property->Address->addAttribute('Neighborhood', $neighborhood);
    }
  }

  echo $xml->asXML(UTOPIA_APPFOLIO_FILENAME);
}
