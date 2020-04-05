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

use App\Finance\Entity\Orders;
use App\Service\LogService;
use App\Service\CacheService;

class OrderController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/order/info/", name="api_order_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Finance/Config/Order.yaml');

        $api = [];
        $settings = ['title' => 'orders'];

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
    * @Route("/api/v1/order/list/", name="api_order_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Orders::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Orders::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $orders = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $orders,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/order/get/{id}/", name="api_order_get"), methods={"GET","HEAD"})
    */
    final public function getOrder(int $id, Request $request)
    {
        if (!empty($id)) {
            $order = $this->getDoctrine()
            ->getRepository(Orders::class)
            ->find($id);
            if ($order) {
                $response = [
                    'success' => true,
                    'data' => $order,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find order',
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
    * @Route("/api/v1/order/insert/", name="api_order_insert", methods={"PUT"})
    * @Route("/api/v1/order/update/{id}/", name="api_order_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $order = $this->getDoctrine()
            ->getRepository(Orders::class)
            ->find($id);
            if (!$order) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find order',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($order, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Order has been updated';

            }
        } else {
            $order = new Orders();
            $message = 'Order has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['orderKey'])) $order->setOrderKey($params['orderKey']);
            else $errors[] = 'Key cannot be empty';

            if (isset($params['orderValue'])) $order->setOrderValue($params['orderValue']);
            else $errors[] = 'Value cannot be empty';

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($order, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();
            $id = $order->getId();

            $log->add('Order', $id, $logMessage, $logComment);

            $cache = new CacheService();
            $cache->delete('order.'.$order->getOrderKey());

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
    * @Route("/api/v1/order/delete/", name="api_order_delete", methods={"PUT"})
    * @Route("/api/v1/order/delete/{id}/", name="api_order_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $orderId) {

                $em = $this->getDoctrine()->getManager();
                $order = $em->getRepository(Orders::class)->find($orderId);

                if ($order) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($order, 'json');

                    $log->add('Order', $id, $logMessage, 'Delete');

                    $em->remove($order);
                    $em->flush();
                }
            }

            $response = ['success' => true];

            $cache = new CacheService();
            $cache->delete('order.'.$order->getOrderKey());

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
