<?php

namespace App\Finance\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

use App\Service\SettingService;

class MollieService
{
    protected $em;
    protected $request;
    protected $setting;

    public function __construct(EntityManager $em, \App\Service\SettingService $setting)
    {
        $this->em = $em;
        $this->setting = $setting;
    }

    public function getPaymentMethods()
    {
        $key = $this->setting->get('finance.payment.provider.apikey');

        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($key);

        $paymentMethods = array();

        $methods = $mollie->methods->allActive();
        foreach ($methods as $method) {

            $paymentMethods[] = array(
                'image' => $method->image->size1x,
                'image2x' => $method->image->size2x,
                'id' => $method->id,
                'description' => $method->description
            );
        }

        return $paymentMethods;
    }

    public function getPaymentLink($order, $redirectUrl='')
    {
        $key = $this->setting->get('finance.payment.provider.apikey');

        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($key);

        $path = $this->setting->get('site.url');
        if (empty($redirectUrl)) $redirectUrl = "{$path}/payment/return/{$order->getId()}/";

        /*
         * Payment parameters:
         *   amount        Amount in EUROs. This example creates a € 10,- payment.
         *   description   Description of the payment.
         *   redirectUrl   Redirect location. The customer will be redirected there after the payment.
         *   webhookUrl    Webhook location, used to report when the payment changes state.
         *   metadata      Custom metadata that is stored with the payment.
         */
        $data = array(
            "amount" => [
                "currency" => "EUR",
                "value" => $order->getTotalIncl() // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => "Order #{$order->getId()}",
            "redirectUrl" => $redirectUrl,
            "webhookUrl" => "{$path}/api/v1/payment/handle/{$order->getId()}/",
            "metadata" => [
                "order_id" => $order->getId(),
            ],
        );

        $userSubscription = $order->getUserSubscription();

        if ($userSubscription) {

            $user = $order->getUser();
            $mollieCustomerId = $user->getSetting('mollieId');

            try {
                $customer = $mollie->customers->create([
                    "name" => $user->getFirstname() . ' ' . $user->getLastname(),
                    "email" => $user->getEmail(),
                    "metadata" => [
                        "id" => $user->getId(),
                    ],
                ]);

                $user->setSetting('mollieId', $customer->id);

                $this->em->persist($user);
                $this->em->flush();

                $data['description'] = 'Subscription first payment';
                $data['customerId'] = $customer->id;
                $data['sequenceType'] = 'first';

            } catch (\Mollie\Api\Exceptions\ApiException $e) {
                //echo "API call failed: " . htmlspecialchars($e->getMessage());
                return false;
            }

        }

        $payment = $mollie->payments->create($data);

        return $payment->getCheckoutUrl();
    }
}
