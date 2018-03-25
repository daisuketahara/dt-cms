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

    private $setting;
    private $log;

    public function __construct(\App\Service\SettingService $setting, \App\Service\LogService $log)
    {
        $this->setting = $setting;
        $this->log = $log;
    }

    // http://www.spryng.nl/developers/http-api/
    public function send($recipient, $message, $reference='') {

        $enabled = $this->setting->getSetting('sms.enable');
        $username = $this->setting->getSetting('spryng.username');
        $password = $this->setting->getSetting('spryng.password');
        $route = $this->setting->getSetting('spryng.route');
        $long = $this->setting->getSetting('spryng.long');
        $company = $this->setting->getSetting('company.name');

        if (empty($reference)) $reference = $this->setting->getSetting('spryng.reference');
        if (empty($reference)) $reference = $company;
        if (empty($route)) $route = 'business';

        if (!empty($enabled)) {

            $company = $this->setting->getSetting('site.name');
            $message = $company . ' - ' . $message;

            $spryng = new Client($username, $password, $company);

            try {
                $spryng->sms->send($recipient, $message, array(
                    'route' => $route,
                    'allowlong' => true,
                    'reference' => $reference)
                );
                return true;
            } catch (InvalidRequestException $e) {

                $logMessage = '<i>Error:</i><br>';
                $logMessage .= $e->getMessage();

                $this->log->add('SMS', 0, $logMessage, 'Send');
            }
        }
        return false;
    }

    public function sendSmsCode($phone, TranslatorInterface $translator) {

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

    public function validate($smsCode)
    {
        $session = new Session();
        $session->start();

        $confirmCode = $session->get('confirmsms');

        if (md5($smsCode) == $confirmCode) return true;

        return false;
    }
}
