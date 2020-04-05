<?php

namespace App\Finance\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Cron;
use App\Service\LogService;
use App\Service\SettingService;

use App\Finance\Service\MollieService;

class PaymentController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/payment/get-methods/", name="api_payment_get_methods"), methods={"GET"})
    */
    final public function getMethods(Request $request, SettingService $setting, MollieService $mollie, KernelInterface $kernel)
    {
        $paymentProvider = $setting->get('finance.payment.provider');

        if ($paymentProvider == 'mollie') $methods = $mollie->getPaymentMethods();

        if (!empty($methods)) {

            $response = [
                'success' => true,
                'data' => $methods,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Cannot find payment methods',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/payment/handle/{orderId}/", name="api_payment_handle"), methods={"POST"})
    */
    final public function handlePayment(int $orderId, Request $request, SettingService $setting, MollieService $mollie, KernelInterface $kernel)
    {
        $paymentProvider = $setting->get('finance.payment.provider');

        if ($paymentProvider == 'mollie') {

            try {
                /*
                 * Initialize the Mollie API library with your API key.
                 *
                 * See: https://www.mollie.com/dashboard/developers/api-keys
                 */
                require "../initialize.php";

                /*
                 * Retrieve the payment's current state.
                 */
                $payment = $mollie->payments->get($_POST["id"]);
                $orderId = $payment->metadata->order_id;

                /*
                 * Update the order in the database.
                 */
                database_write($orderId, $payment->status);

                if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {
                    /*
                     * The payment is paid and isn't refunded or charged back.
                     * At this point you'd probably want to start the process of delivering the product to the customer.
                     */
                } elseif ($payment->isOpen()) {
                    /*
                     * The payment is open.
                     */
                } elseif ($payment->isPending()) {
                    /*
                     * The payment is pending.
                     */
                } elseif ($payment->isFailed()) {
                    /*
                     * The payment has failed.
                     */
                } elseif ($payment->isExpired()) {
                    /*
                     * The payment is expired.
                     */
                } elseif ($payment->isCanceled()) {
                    /*
                     * The payment has been canceled.
                     */
                } elseif ($payment->hasRefunds()) {
                    /*
                     * The payment has been (partially) refunded.
                     * The status of the payment is still "paid"
                     */
                } elseif ($payment->hasChargebacks()) {
                    /*
                     * The payment has been (partially) charged back.
                     * The status of the payment is still "paid"
                     */
                }
            } catch (\Mollie\Api\Exceptions\ApiException $e) {
                echo "API call failed: " . htmlspecialchars($e->getMessage());
            }


        }








    }


}
