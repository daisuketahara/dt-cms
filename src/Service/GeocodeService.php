<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

use App\Service\SettingService;

class GeocodeService
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
        $key = $this->setting->get('tomtom.key');

        //$key = 'DpmSXVu5ZQJADM9nOdqWL4G5AVXvqngh';

        if (!empty($key)) {
            $url = 'https://api.tomtom.com/search/2/geocode/' . urlencode($address) . '.json?key=' . $key;

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

        return false;
    }

}
