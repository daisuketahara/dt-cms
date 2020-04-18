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

use App\Finance\Entity\Orders;
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
    * @Route("/api/v1/payment/handle/{id}/", name="api_payment_handle"), methods={"POST"})
    */
    final public function handlePayment($id, Request $request, SettingService $setting, MollieService $mollie, KernelInterface $kernel)
    {
        $paymentProvider = $setting->get('finance.payment.provider');

        if ($paymentProvider == 'mollie') {

            $key = $setting->get('finance.payment.provider.apikey');

            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setApiKey($key);

            try {
                /*
                 * Retrieve the payment's current state.
                 */
                $postId= $request->request->get('id');
                $payment = $mollie->payments->get($postId);
                $orderId = $payment->metadata->order_id;

                if (!empty($orderId)) {

                    $response = array(
                        'success' => false,
                        'message' => 'Order id cannot be empty',
                    );

                    $json = $this->serializer->serialize($response, 'json');
                    return $this->json($json);
                }

                $order = $this->getDoctrine()
                    ->getRepository(Orders::class)
                    ->find($orderId);

                if (!$order) {

                    $response = array(
                        'success' => false,
                        'message' => 'Cannot find order',
                    );

                    $json = $this->serializer->serialize($response, 'json');
                    return $this->json($json);
                }

                // Get Subscription
                $subscription = $order->getUserSubscription();

                /*
                 * Update the order in the database.
                 */

                if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {
                    /*
                     * The payment is paid and isn't refunded or charged back.
                     * At this point you'd probably want to start the process of delivering the product to the customer.
                     */

                     $order->setState('PAID');
                     if ($subscription) $subscription->setActive(true);

                } elseif ($payment->isOpen()) {
                    /*
                     * The payment is open.
                     */
                     $order->setState('OPEN');
                     if ($subscription) $subscription->setActive(false);
                } elseif ($payment->isPending()) {
                    /*
                     * The payment is pending.
                     */
                     $order->setState('PENDING');
                     if ($subscription) $subscription->setActive(false);
                } elseif ($payment->isFailed()) {
                    /*
                     * The payment has failed.
                     */
                } elseif ($payment->isExpired()) {
                    /*
                     * The payment is expired.
                     */
                     $order->setState('EXPIRED');
                     if ($subscription) $subscription->setActive(false);
                } elseif ($payment->isCanceled()) {
                    /*
                     * The payment has been canceled.
                     */
                     $order->setState('CANCEL');
                     if ($subscription) $subscription->setActive(false);
                } elseif ($payment->hasRefunds()) {
                    /*
                     * The payment has been (partially) refunded.
                     * The status of the payment is still "paid"
                     */
                     $order->setState('REFUND');
                     if ($subscription) $subscription->setActive(false);
                } elseif ($payment->hasChargebacks()) {
                    /*
                     * The payment has been (partially) charged back.
                     * The status of the payment is still "paid"
                     */
                     $order->setState('CHARGEBACK');
                     if ($subscription) $subscription->setActive(false);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();
                $em->persist($subscription);
                $em->flush();

                $response = [
                    'success' => true,
                    'message' => 'Processed',
                ];

            } catch (\Mollie\Api\Exceptions\ApiException $e) {
                $response = [
                    'success' => false,
                    'message' => "API call failed: " . htmlspecialchars($e->getMessage())
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Cannot process'
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/payment/verify/{id}/", name="api_payment_verify"), methods={"POST"})
    */
    final public function verifyPayment($id, Request $request, SettingService $setting, MollieService $mollie, KernelInterface $kernel)
    {
        $order = $this->getDoctrine()
            ->getRepository(Orders::class)
            ->find($id);

        if ($order) {

            $response = [
                'success' => true,
                'message' => $order->getState()
            ];

        } else {
            $response = [
                'success' => false,
                'message' => 'Cannot find order'
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }
}
