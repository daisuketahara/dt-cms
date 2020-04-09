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

    public function getPaymentLink($order)
    {
        $key = $this->setting->get('finance.payment.provider.apikey');

        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($key);

        $path = $setting->get('site.url');

        /*
         * Payment parameters:
         *   amount        Amount in EUROs. This example creates a € 10,- payment.
         *   description   Description of the payment.
         *   redirectUrl   Redirect location. The customer will be redirected there after the payment.
         *   webhookUrl    Webhook location, used to report when the payment changes state.
         *   metadata      Custom metadata that is stored with the payment.
         */
        $payment = $mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => $order->getTotalIncl() // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => "Order #{$order->getId()}",
            "redirectUrl" => "{$path}/mollie/return/order_id={$order->getId()}",
            "webhookUrl" => "{$path}/api/v1/payment/handle/{$order->getId()}/",
            "metadata" => [
                "order_id" => $order->getId(),
            ],
        ]);

        return $payment->getCheckoutUrl();
    }
}
