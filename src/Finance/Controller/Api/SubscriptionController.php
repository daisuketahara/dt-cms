<?php

namespace App\Finance\Controller\Api;

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

use App\Finance\Entity\Subscription;
use App\Finance\Entity\Vat;
use App\Service\LogService;

class SubscriptionController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/subscription/info/", name="api_subscription_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Finance/Config/Subscription.yaml');

        $api = [];
        $settings = ['title' => 'subscriptions'];

        if (!empty($properties['actions'])) {
            foreach($properties['actions'] as $key => $action) {
                if (!empty($action['api'])) $api[$key] = $action['api'];
                elseif (!empty($action['url'])) $settings[$key] = $action['url'];
            }
        }

        $info = array(
            'api' => $api,
            'settings' => $settings,
            'fields' => $properties['fields'],
        );

        if (!empty($properties['buttons'])) $info['buttons'] = $properties['buttons'];

        return $this->json($info);
    }

    /**
    * @Route("/api/v1/subscription/list/", name="api_subscription_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];

        $whereString = '1=1';
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(Subscription::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Subscription::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $subscriptions = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $subscriptions,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/subscription/get/{id}/", name="api_subscription_get"), methods={"GET","HEAD"})
    */
    final public function getSubscription(int $id, Request $request)
    {
        if (!empty($id)) {
            $subscription = $this->getDoctrine()
            ->getRepository(Subscription::class)
            ->find($id);
            if ($subscription) {
                $response = [
                    'success' => true,
                    'data' => $subscription,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find subscription',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/subscription/insert/", name="api_subscription_insert", methods={"PUT"})
    * @Route("/api/v1/subscription/update/{id}/", name="api_subscription_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $subscription = $this->getDoctrine()
            ->getRepository(Subscription::class)
            ->find($id);
            if (!$subscription) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find subscription',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($subscription, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Subscription has been updated';

            }
        } else {
            $subscription = new Subscription();
            $message = 'Subscription has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['title'])) $subscription->setTitle($params['title']);
            else $errors[] = 'TItle cannot be empty';

            if (isset($params['price'])) $subscription->setPrice($params['price']);
            else $errors[] = 'Price cannot be empty';

            if (isset($params['amountTerms'])) $subscription->setAmountTerms($params['amountTerms']);
            else $errors[] = 'Term amount cannot be empty';

            if (isset($params['term'])) $subscription->setTerm($params['term']);
            else $errors[] = 'Term cannot be empty';

            if (isset($params['vat'])) {

                $vat = $this->getDoctrine()
                    ->getRepository(Vat::class)
                    ->find($params['vat']);

                if ($vat) $subscription->setVat($vat);
                 else $errors[] = 'Given Vat id doesn  not exist';

            } else $errors[] = 'Vat cannot be empty';

            if (isset($params['description'])) $subscription->setDescription($params['description']);
            if (isset($params['active'])) $subscription->setActive($params['active']);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($subscription, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($subscription);
            $em->flush();
            $id = $subscription->getId();

            $log->add('Subscription', $id, $logMessage, $logComment);

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/subscription/delete/", name="api_subscription_delete", methods={"PUT"})
    * @Route("/api/v1/subscription/delete/{id}/", name="api_subscription_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $subscriptionId) {

                $em = $this->getDoctrine()->getManager();
                $subscription = $em->getRepository(Subscription::class)->find($subscriptionId);

                if ($subscription) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($subscription, 'json');

                    $log->add('Subscription', $id, $logMessage, 'Delete');

                    $em->remove($subscription);
                    $em->flush();
                }
            }

            $response = ['success' => true];

        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }
}
