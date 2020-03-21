<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

use App\Service\SettingService;

class RouteService
{
    protected $setting;

    public function __construct(\App\Service\SettingService $setting)
    {
        $this->setting = $setting;
    }

    public function stripHousenumberSuffix($housenumber)
    {
        $housenumber .= ' ';
        $matches = array();
        preg_match('/([0-9]+)([^0-9]+)/',$housenumber,$matches);
        $housenumber = $matches[1];
        return $housenumber;
    }

    public function getCoordinates($address)
    {
        $tKey = $this->setting->get('tomtom.key');

        //$key = 'DpmSXVu5ZQJADM9nOdqWL4G5AVXvqngh';

        if (!empty($key)) {
            $url = 'https://api.tomtom.com/search/2/geocode/' . urlencode($address) . '.json?key=' . $tKey;

            $client = HttpClient::create();
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {

                $result = $response->toArray();

                if (!empty($result['results'])) {

                    $lng = $result['results'][0]['position']['lon'];
                    $lat = $result['results'][0]['position']['lat'];

                    return array($lng, $lat);
                }
            }
        }

        $gKey = $this->setting->get('google.maps.key');

        if (!empty($gKey)) {

		    $url = 'https://maps.google.com/maps/api/geocode/json?sensor=false&address=' . urlencode($address) . '&key=' . $gKey;

            $client = HttpClient::create();
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {

                $result = $response->toArray();

                $lng = $json['results'][0]['geometry']['location']['lng'];
    		    $lat = $json['results'][0]['geometry']['location']['lat'];

                return array($lng, $lat);
            }
        }

        return false;
    }

    public function getRouteInfo(array $waypoints)
    {
        if (empty($waypoints) && !is_array($waypoints)) return false;

        $points = array();

        foreach($waypoints as $waypoint) {
            if (is_object($waypoint)) $points[] = $waypoint->latitude . ',' . $waypoint->longitude;
            elseif (is_array($waypoint)) $points[] = $waypoint['latitude'] . ',' . $waypoint['longitude'];
            else $points[] = $waypoint;
        }

        $tKey = $this->setting->get('tomtom.key');

        if (!empty($key)) {
            $url = 'https://api.tomtom.com/routing/1/calculateRoute/';
			$url .= urlencode(implode(':', $points));
            $url .= '/json?key=' . $tKey . '&travelMode=car';

            $client = HttpClient::create();
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {

                $result = $response->toArray();

                if (!empty($result['routes'])) {

                    $distance = round($result['routes'][0]['summary']['lengthInMeters']/1000);
                    $traveltime = round($result['routes'][0]['summary']['travelTimeInSeconds']/60);

                    return array(
                        'distance' => $distance,
                        'traveltime' => $traveltime,
                    );
                }
            }
        }

        $gKey = $this->setting->get('google.maps.key');

        if (!empty($gKey)) {

            $distance = 0;
            $traveltime = 0;

			$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . urlencode(implode('|', $waypoints));
			$url .= '&destinations=' . urlencode(implode('|', $points));
			//$url .= '&mode=car&language=nl-NL&key=' . $gKey;

            $client = HttpClient::create();
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {

                $result = $response->toArray();

                if (!empty($result['rows'])) {

                    $i = 0;
                    foreach($result['rows']as $gKey => $gRow) {
                        $traveltime += round($gRow['elements'][$i]['duration']['value']/60);
                        $i++;
                    }
                }
            }

            return array(
                'distance' => $distance,
                'traveltime' => $traveltime,
            );
        }

        return false;
    }

}
