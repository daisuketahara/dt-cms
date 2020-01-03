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
use Symfony\Component\Yaml\Yaml;

use App\Entity\Locale;
use App\Service\LogService;

class LocaleController extends Controller
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/locale/info/", name="api_locale_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/locale.yaml');

        $api = [];
        $settings = ['title' => $translator->trans('languages')];

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

        return $this->json($info);
    }

    /**
    * @Route("/api/v1/locale/list/", name="api_locale_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'default';
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

        $qb = $this->getDoctrine()->getRepository(Locale::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Locale::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $locales = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $locales,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/locale/get/{id}/", name="api_locale_get"), methods={"GET","HEAD"})
    */
    final public function getLocale(int $id, Request $request)
    {
        if (!empty($id)) {
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($id);
            if ($locale) {
                $response = [
                    'success' => true,
                    'data' => $locale,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find locale',
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
    * @Route("/api/v1/locale/insert/", name="api_locale_insert", methods={"PUT"})
    * @Route("/api/v1/locale/update/{id}/", name="api_locale_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($id);
            if (!$locale) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find locale',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($locale, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Locale has been updated';

            }
        } else {
            $locale = new Locale();
            $message = 'Locale has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['name'])) $locale->setName($params['name']);
            else $errors[] = 'Name cannot be empty';

            if (isset($params['locale'])) $locale->setLocale($params['locale']);
            else $errors[] = 'Locale cannot be empty';

            if (isset($params['lcid'])) $locale->setLcid($params['lcid']);
            else $errors[] = 'LCID cannot be empty';

            if (isset($params['isoCode'])) $locale->setIsoCode($params['isoCode']);
            else $errors[] = 'ISO code cannot be empty';

            if (!empty($params['default'])) $locale->setDefault(true);
            else $locale->setDefault(false);

            if (!empty($params['active'])) $locale->setActive(true);
            else $locale->setActive(false);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($locale, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($locale);
            $em->flush();
            $id = $locale->getId();

            $log->add('Locale', $id, $logMessage, $logComment);

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
    * @Route("/api/v1/locale/delete/", name="api_locale_delete", methods={"PUT"})
    * @Route("/api/v1/locale/delete/{id}/", name="api_locale_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $localeId) {

                $em = $this->getDoctrine()->getManager();
                $locale = $em->getRepository(Locale::class)->find($localeId);

                if ($locale) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($locale, 'json');

                    $log->add('Locale', $id, $logMessage, 'Delete');

                    $em->remove($locale);
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
