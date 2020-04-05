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

use App\Finance\Entity\Vat;
use App\Service\LogService;

class VatController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/vat/info/", name="api_vat_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Finance/Config/Vat.yaml');

        $api = [];
        $settings = ['title' => 'vats'];

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
    * @Route("/api/v1/vat/list/", name="api_vat_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'asc';
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

        $qb = $this->getDoctrine()->getRepository(Vat::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Vat::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $vats = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $vats,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/vat/get/{id}/", name="api_vat_get"), methods={"GET","HEAD"})
    */
    final public function getVat(int $id, Request $request)
    {
        if (!empty($id)) {
            $vat = $this->getDoctrine()
            ->getRepository(Vat::class)
            ->find($id);
            if ($vat) {
                $response = [
                    'success' => true,
                    'data' => $vat,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find vat',
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
    * @Route("/api/v1/vat/insert/", name="api_vat_insert", methods={"PUT"})
    * @Route("/api/v1/vat/update/{id}/", name="api_vat_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $vat = $this->getDoctrine()
            ->getRepository(Vat::class)
            ->find($id);
            if (!$vat) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find vat',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($vat, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Vat has been updated';

            }
        } else {
            $vat = new Vat();
            $message = 'Vat has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['title'])) $vat->setTitle($params['title']);
            else $errors[] = 'Title cannot be empty';

            if (isset($params['percentage'])) $vat->setPercentage($params['percentage']);
            else $errors[] = 'Percentage cannot be empty';

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($vat, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($vat);
            $em->flush();
            $id = $vat->getId();

            $log->add('Vat', $id, $logMessage, $logComment);

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
    * @Route("/api/v1/vat/delete/", name="api_vat_delete", methods={"PUT"})
    * @Route("/api/v1/vat/delete/{id}/", name="api_vat_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $vatId) {

                $em = $this->getDoctrine()->getManager();
                $vat = $em->getRepository(Vat::class)->find($vatId);

                if ($vat) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($vat, 'json');

                    $log->add('Vat', $id, $logMessage, 'Delete');

                    $em->remove($vat);
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
