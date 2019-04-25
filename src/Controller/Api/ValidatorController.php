<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Service\SmsService;
use App\Service\ValidatorService;

class ValidatorController extends Controller
{
    /**
    * @Route("/api/v1/validate/smscode/{smscode}/", name="api_validate_smscode", methods={"GET","HEAD"})
    */
    final public function smscode($smscode, Request $request, SmsService $sms)
    {
        $result = $sms->validate($smsCode);

        if ($result) $response = ['success' => true];
        else $response = ['success' => false];

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/validate/iban/{iban}/", name="api_validate_iban", methods={"GET","HEAD"})
    */
    final public function iban($iban, Request $request, ValidatorService $validator)
    {
        $result = $validator->iban($iban);

        if ($result) $response = ['success' => true];
        else $response = ['success' => false];

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/validate/vat/{vat}/", name="api_validate_vat", methods={"GET","HEAD"})
    */
    final public function vat($vat, Request $request, ValidatorService $validator)
    {
        $result = $validator->vat($vat);

        if ($result) $response = ['success' => true];
        else $response = ['success' => false];

        $json = json_encode($response);
        return $this->json($json);
    }
}
