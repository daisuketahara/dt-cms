<?php

namespace App\Finance\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Cron;
use App\Service\LogService;
use App\Service\MailService;
use App\Service\SettingService;

use App\Finance\Entity\UserSubscription;
use App\Finance\Entity\Orders;
use App\Finance\Service\MollieService;


class CronController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/cron/finance_renew_subscription/", name="cron_finance_renew_subscription")
    */
    public function renewSubscription(MollieService $mollie, MailService $mail, SettingService $setting)
    {
        $userSubscriptions = $this->getDoctrine()
            ->getRepository(UserSubscription::class)
            ->findBy(['active' => true]);

        if ($userSubscriptions) {

            foreach($userSubscriptions as $userSubscription) {

                $subscription = $userSubscription->getSubscription();
                $term = $subscription->getTerm();
                $amountTerms = $subscription->getAmountTerms();

                // If amount of terms is 1, then skip
                if ($amountTerms == 1) continue;

                $order = new Orders();
                $order->setUser($this->getUser());
                $order->setUserSubscription($userSubscription);
                $order->setOrderDate(new \DateTime());

                $incl = $subscription->getPrice();
                $vat = $subscription->getVat();
                $excl = $incl/(1+($vat->getPercentage()/100));
                $vatAmount = $excl * ($vat->getPercentage()/100);

                $order->setTotalExcl($excl);
                $order->setTotalIncl($incl);
                $order->setTotalVat($vatAmount);

                $em->persist($order);
                $em->flush();

                $recurring = $mollie->sendRecurringPayment($order);

                if ($recurring) {
                    $renewDate = new \DateTime();
                    switch ($subscription->getTerm()) {
                        case '1w':
                            $renewDate->add(new DateInterval('P7D'));
                            break;
                        case '2w':
                            $renewDate->add(new DateInterval('P14D'));
                            break;
                        case '1m':
                            $renewDate = $this->MonthShifter($renewDate, 1);
                            break;
                        case '3m':
                            $renewDate = $this->MonthShifter($renewDate, 3);
                            break;
                        case '6m':
                            $renewDate = $this->MonthShifter($renewDate, 6);
                            break;
                                $renewDate = $this->MonthShifter($renewDate, 12);
                            break;
                    }

                    $userSubscription->setRenewDate($renewDate);
                    $em->persist($userSubscription);
                    $em->flush();

                    $user = $userSubscription->getUser();

                    // Send email
                    if (!empty($user->getEmail())) {
                        $mail->addToQueue(
                            $user->getEmail(),
                            'finance-subscription-renewed',
                            $user->getLocale()->getId(),
                            array(
                                'name' => $user->getFirstname() . ' ' . $user->getLastname(),
                                'site' => $setting->get('site.name'),
                            )
                        );
                    }
                }
            }
        }
        return new Response(
            'Done'
        );
    }

    private function monthShifter (DateTime $aDate,$months){
        $dateA = clone($aDate);
        $dateB = clone($aDate);
        $plusMonths = clone($dateA->modify($months . ' Month'));
        //check whether reversing the month addition gives us the original day back
        if($dateB != $dateA->modify($months*-1 . ' Month')){
            $result = $plusMonths->modify('last day of last month');
        } elseif($aDate == $dateB->modify('last day of this month')){
            $result =  $plusMonths->modify('last day of this month');
        } else {
            $result = $plusMonths;
        }
        return $result;
    }
}
