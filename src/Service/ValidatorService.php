<?php

namespace App\Service;

use App\Service\LogService;

class ValidatorService
{
    public function iban()
    {

    }

    public function vat(string $vatid, LogService $log)
    {
        if (!empty($vatid)) {
            $vatid = str_replace(' ','', trim($vatid));

            try {
                $country = substr(($vatid), 0, 2);
                $numberPortion = substr($vatid, 2);
                $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
                if($client) {
                    $params = array('countryCode' => $country, 'vatNumber' => $numberPortion);
                    $r = $client->checkVat($params);
                    if($r->valid == true) return true;
                    else return false;
                } else {
                    $logMessage = '<i>Error:</i><br>';
                    $logMessage .= "Can't connect to ec.europa.eu, VAT number cannot be validate.";
                    $log->add('VAT ', 0, $logMessage, 'Validate');
                    return 'error';
                }
            } catch (Exception $e) {
                $logMessage = '<i>Error:</i><br>';
                $logMessage .= $e->getMessage();
                $log->add('VAT ', 0, $logMessage, 'Validate');
                return 'error';
            }
        }
        return false;
    }

}
