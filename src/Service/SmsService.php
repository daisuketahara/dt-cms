<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Service\SettingService;
use App\Service\LogService;

class SmsService
{

    private $setting;
    private $log;

    public function __construct(\App\Service\SettingService $setting, \App\Service\LogService $log)
    {
        $this->setting = $setting;
        $this->log = $log;
    }

    // http://www.spryng.nl/developers/http-api/
    public function send(array $recipients, string $message, string $reference='') {

        $enabled = $this->setting->get('sms.enable');
        $token = $this->setting->get('spryng.token');
        $route = $this->setting->get('spryng.route');
        $long = $this->setting->get('spryng.long');
        $company = $this->setting->get('company.name');
        $site = $this->setting->get('site.url');

        if (empty($reference)) $reference = $this->setting->get('spryng.reference');
        if (empty($reference)) $reference = $company;
        if (empty($route)) $route = 'business';

        if (!empty($enabled)) {

            $data = json_encode([
                'encoding' => 'auto',
                'body' => $message,
                'originator' => $company,
                'recipients' => $recipients,
                'route' => 'business',
                'reference' => $site
            ]);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://rest.spryngsms.com/v1/messages",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpcode == 200) return true;
        }

        return false;
    }

    public function sendSmsCode(string $phone, TranslatorInterface $translator) {

        $smsCode = $this->generateCode();
        $message = $translator->trans('SMS verification code') . ': ' . $smsCode;

        if ($this->send($phone, $message)) return true;
        return false;
    }

    public function generateCode()
    {
        $session = new Session();
        $session->start();
        $smsCode = rand(100000, 999999);
        $session->set('confirmsms', md5($smsCode));
        return $smscode;
    }

    public function validate(string $smsCode)
    {
        $session = new Session();
        $session->start();

        $confirmCode = $session->get('confirmsms');

        if (md5($smsCode) == $confirmCode) return true;

        return false;
    }
}
