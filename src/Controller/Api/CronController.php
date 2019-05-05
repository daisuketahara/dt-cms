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

use App\Entity\Cron;
use App\Service\LogService;

class CronController extends Controller
{
    /**
    * @Route("/api/v1/cron/info/", name="api_cron_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/cron/list/',
                'get' => '/cron/get/',
                'insert' => '/cron/insert/',
                'update' => '/cron/update/',
                'delete' => '/cron/delete/'
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
                    'id' => 'name',
                    'label' => 'name',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'script',
                    'label' => 'script',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'minute',
                    'label' => 'minute',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'hour',
                    'label' => 'hour',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'day',
                    'label' => 'day',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'month',
                    'label' => 'month',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'day_of_week',
                    'label' => 'day_of_week',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'last_run',
                    'label' => 'last_run',
                    'type' => 'datetime',
                    'required' => false,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],
                [
                    'id' => 'next_run',
                    'label' => 'next_run',
                    'type' => 'datetime',
                    'required' => false,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],
                [
                    'id' => 'run_count',
                    'label' => 'run_count',
                    'type' => 'text',
                    'required' => false,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],
                [
                    'id' => 'active',
                    'label' => 'active',
                    'type' => 'checkbox',
                    'required' => false,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/cron/list/", name="api_cron_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Cron::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Cron::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $crons = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $crons,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/cron/get/{id}/", name="api_cron_get"), methods={"GET","HEAD"})
    */
    final public function getCron($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $cron = $this->getDoctrine()
            ->getRepository(Cron::class)
            ->find($id);
            if ($cron) {
                $response = [
                    'success' => true,
                    'data' => $cron,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find cron',
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
    * @Route("/api/v1/cron/insert/", name="api_cron_insert", methods={"PUT"})
    * @Route("/api/v1/cron/update/{id}/", name="api_cron_update", methods={"PUT"})
    */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $cron = $this->getDoctrine()
            ->getRepository(Cron::class)
            ->find($id);
            if (!$cron) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find cron',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($cron, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Cron has been updated';

            }
        } else {
            $cron = new Cron();
            $message = 'Cron has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['name'])) $cron->setName($params['name']);
            else $errors[] = 'Name cannot be empty';

            if (isset($params['script'])) $cron->setScript($params['script']);
            else $errors[] = 'Script cannot be empty';

            if (isset($params['minute'])) $cron->setMinute($params['minute']);
            else $errors[] = 'Minute cannot be empty';

            if (isset($params['hour'])) $cron->setHour($params['hour']);
            else $errors[] = 'Hour cannot be empty';

            if (isset($params['day'])) $cron->setDay($params['day']);
            else $errors[] = 'Day cannot be empty';

            if (isset($params['month'])) $cron->setMonth($params['month']);
            else $errors[] = 'Month cannot be empty';

            if (isset($params['day_of_week'])) $cron->setDayOfWeek($params['day_of_week']);
            else $errors[] = 'Day of week cannot be empty';

            if (!empty($params['active'])) $cron->setActive(true);
            else $cron->setActive(false);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($cron, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($cron);
            $em->flush();
            $id = $cron->getId();

            $log->add('Cron', $id, $logMessage, $logComment);

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
    * @Route("/api/v1/cron/delete/", name="api_cron_delete", methods={"PUT"})
    * @Route("/api/v1/cron/delete/{id}/", name="api_cron_delete_multiple", methods={"DELETE"})
    */
    final public function delete($id=0, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $cronId) {

                $em = $this->getDoctrine()->getManager();
                $cron = $em->getRepository(Cron::class)->find($cronId);

                if ($cron) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($cron, 'json');

                    $log->add('Cron', $id, $logMessage, 'Delete');

                    $em->remove($cron);
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
