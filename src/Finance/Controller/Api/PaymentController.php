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


}
