<?php

    // src/Service/SmsService.php

    namespace App\Service;

    use SpryngApiHttpPhp\Client;
    use SpryngApiHttpPhp\Exception\InvalidRequestException;

    class SmsService
    {
        // http://www.spryng.nl/developers/http-api/
        public function send($recipient, $message) {

            $spryng = new Client('gebruikersnaam', 'wachtwoord', 'Uw Bedrijf' );

            try {
                $spryng->sms->send($recipient, $message, array(
                'route' => 'business',
                'allowlong' => true,
                'reference' => 'ABC123456789')
                );
            } catch (InvalidRequestException $e) {
                error_log('Error sending Sms: ' . $e->getMessage());
            }

        }
    }
