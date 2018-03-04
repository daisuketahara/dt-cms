<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Service\SmsService;
use App\Service\ValidatorService;

class ValidatorController extends Controller
{
    /**
     * @Route("/validate/smscode", name="validate_smscode")
     */
     final public function smscode(Request $request, SmsService $sms)
     {
         $smsCode = $request->request->get('smscode');

         if ($sms->validate($smsCode)) return new Response('1');
         return new Response('0');
     }

     /**
      * @Route("/validate/iban", name="validate_iban")
      */
      final public function iban(Request $request, ValidatorService $validator)
      {
          $iban = $request->request->get('iban');

          if ($validator->iban($iban)) return new Response('1');
          return new Response('0');
      }

      /**
       * @Route("/validate/vat", name="validate_vat")
       */
       final public function vat(Request $request, ValidatorService $validator)
       {
           $vat = $request->request->get('vat');

           if ($validator->vat($vat)) return new Response('1');
           return new Response('0');
       }
}
