<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Log;
use App\Service\LogService;

class LogController extends Controller
{
    /**
    * @Route("/api/v1/log/info/", name="api_log_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/log/list/',
                'get' => '/log/get/',
                'delete' => '/log/delete/'
            ),
            'fields' => array(
                [
                    'id' => 'id',
                    'label' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],
                [
                    'id' => 'accountId',
                    'label' => 'account_id',
                    'type' => 'integer',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'entity',
                    'label' => 'entity',
                    'type' => 'text',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'entityId',
                    'label' => 'entity_id',
                    'type' => 'integer',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'log',
                    'label' => 'log',
                    'type' => 'text',
                    'required' => true,
                    'editable' => false,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'comment',
                    'label' => 'comment',
                    'type' => 'text',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'userIp',
                    'label' => 'user_ip',
                    'type' => 'text',
                    'required' => true,
                    'editable' => false,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'creationDate',
                    'label' => 'date',
                    'type' => 'datetime',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/log/list/", name="api_log_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Log::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Log::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $logs = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $logs,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/log/get/{id}/", name="api_log_get"), methods={"GET","HEAD"})
    */
    final public function getLog($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $log = $this->getDoctrine()
            ->getRepository(Log::class)
            ->find($id);
            if ($log) {
                $response = [
                    'success' => true,
                    'data' => $log,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find log',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/log/delete/", name="api_log_delete", methods={"PUT"})
    * @Route("/api/v1/log/delete/{id}/", name="api_log_delete_multiple", methods={"DELETE"})
    */
    final public function delete($id=0)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $logId) {

                $em = $this->getDoctrine()->getManager();
                $log = $em->getRepository(Log::class)->find($logId);

                if ($log) {
                    $em->remove($log);
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
