<?php

namespace App\Service;

use SpryngApiHttpPhp\Client;
use SpryngApiHttpPhp\Exception\InvalidRequestException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

use App\Service\SettingService;
use App\Service\LogService;

class SmsService
{

    // http://www.spryng.nl/developers/http-api/
    public function send($recipient, $message, SettingService $setting, LogService $log) {

        $enabled = $setting->getSetting('spryng.enable');
        $username = $setting->getSetting('spryng.username');
        $password = $setting->getSetting('spryng.password');
        $route = $setting->getSetting('spryng.route');
        $long = $setting->getSetting('spryng.long');
        $reference = $setting->getSetting('spryng.reference');
        $company = $setting->getSetting('company.name');

        if (!empty($enabled)) {

            $spryng = new Client($username, $password, $company);

            try {
                $spryng->sms->send($recipient, $message, array(
                    'route' => $route,
                    'allowlong' => $long,
                    'reference' => $reference)
                );
                return true;
            } catch (InvalidRequestException $e) {

                $logMessage = '<i>Error:</i><br>';
                $logMessage .= $e->getMessage();

                $log->add('SMS', 0, $logMessage, 'Send');
            }
        }
        return false;
    }

    public function sendSmsCode($phone, TranslatorInterface $translator) {

        $smsCode = $this->generateCode();

        $company = $setting->getSetting('site.name');
        $message = $company . ' - ' . $translator->trans('SMS verification code') . ': ' . $smsCode;

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

    public function validate($smsCode)
    {
        $session = new Session();
        $session->start();

        $confirmCode = $session->get('confirmsms');

        if (md5($smsCode) == $confirmCode) return true;

        return false;
    }
}
