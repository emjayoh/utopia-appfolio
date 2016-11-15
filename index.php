<?php

// =======================================================================//
// ! Parse AppFolio XML       //
// =======================================================================//
if (file_exists('utopiamanagement_rentlinx.xml')) {

    //loads the xml and returns a simplexml object

    $xml = simplexml_load_file('utopiamanagement_rentlinx.xml');

    foreach ($xml->Properties->children() as $property) {
        $address = $property->Address;
        $city = $property->City;
        $state = $property->State;
        $zip = $property->Zip;
        $fullAddress = $address . ' ' . $city . ' ' . $state . ' ' . $zip;

// =======================================================================//
// ! Call Google Geocoding API to determine neighborhood and county       //
// =======================================================================//

//  construct search query
        $search = (!empty($fullAddress) ? 'address=' . rawurlencode($fullAddress) : null);


// set api key
        $api_key = '&key=AIzaSyDXABORr21g0U1jpMdyyRPQkiaIEN1QK6o';


// build $request_url for api call
        $request_url = 'https://maps.googleapis.com/maps/api/geocode/json?' . $search . $api_key;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $request_url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($curl);

        curl_close($curl);

// parse api response
        if (!empty($json)) {

            $details = json_decode($json, true);


            foreach ($details['results'][0]['address_components'] as $location) {

                if (in_array("administrative_area_level_2", $location['types'])) {

                    $county = $location['long_name'];
                }
            }

            foreach ($details['results'][0]['address_components'] as $location) {

                if (in_array("neighborhood", $location['types'])) {

                    $neighborhood = $location['long_name'];
                }
            }

            foreach ($details['results'][0]['address_components'] as $location) {

                if (in_array("locality", $location['types'])) {

                    $city = $location['long_name'];

                }
            }
        }

// Add Attributes to XML

        if (!empty($city)) {
            if ($city == 'Temecula') {
                $county = 'Temecula';

            } elseif ($city == 'Palm Springs') {
                $county = 'Palm Springs';
            }
        }

            $property->Address->addAttribute('FullAddress', $fullAddress);

            if (!empty($county)) {
                $property->Address->addAttribute('County', $county);
            }

            if (!empty($neighborhood)) {
                $property->Address->addAttribute('Neighborhood', $neighborhood);
            }
        }

        echo $xml->asXML('appfolio.xml');
    }

    ?>