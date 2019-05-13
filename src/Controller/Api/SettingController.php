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

use App\Entity\Setting;
use App\Service\LogService;
use App\Service\CacheService;

class SettingController extends Controller
{
    /**
    * @Route("/api/v1/setting/info/", name="api_setting_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/setting/list/',
                'get' => '/setting/get/',
                'insert' => '/setting/insert/',
                'update' => '/setting/update/',
                'delete' => '/setting/delete/'
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
                    'id' => 'settingKey',
                    'label' => 'key',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'settingValue',
                    'label' => 'value',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/setting/list/", name="api_setting_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Setting::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Setting::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $settings = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $settings,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/setting/get/{id}/", name="api_setting_get"), methods={"GET","HEAD"})
    */
    final public function getSetting(int $id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $setting = $this->getDoctrine()
            ->getRepository(Setting::class)
            ->find($id);
            if ($setting) {
                $response = [
                    'success' => true,
                    'data' => $setting,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find setting',
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
    * @Route("/api/v1/setting/insert/", name="api_setting_insert", methods={"PUT"})
    * @Route("/api/v1/setting/update/{id}/", name="api_setting_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $setting = $this->getDoctrine()
            ->getRepository(Setting::class)
            ->find($id);
            if (!$setting) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find setting',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($setting, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Setting has been updated';

            }
        } else {
            $setting = new Setting();
            $message = 'Setting has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['settingKey'])) $setting->setSettingKey($params['settingKey']);
            else $errors[] = 'Key cannot be empty';

            if (isset($params['settingValue'])) $setting->setSettingValue($params['settingValue']);
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
            $logMessage .= $serializer->serialize($setting, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($setting);
            $em->flush();
            $id = $setting->getId();

            $log->add('Setting', $id, $logMessage, $logComment);

            $cache = new CacheService();
            $cache->delete('setting.'.$setting->getSettingKey());

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
    * @Route("/api/v1/setting/delete/", name="api_setting_delete", methods={"PUT"})
    * @Route("/api/v1/setting/delete/{id}/", name="api_setting_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $settingId) {

                $em = $this->getDoctrine()->getManager();
                $setting = $em->getRepository(Setting::class)->find($settingId);

                if ($setting) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($setting, 'json');

                    $log->add('Setting', $id, $logMessage, 'Delete');

                    $em->remove($setting);
                    $em->flush();
                }
            }

            $response = ['success' => true];

            $cache = new CacheService();
            $cache->delete('setting.'.$setting->getSettingKey());

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