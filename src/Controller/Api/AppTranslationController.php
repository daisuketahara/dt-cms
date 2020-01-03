<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Yaml\Yaml;

use App\Entity\AppTranslation;
use App\Entity\Locale;
use App\Service\LogService;

class AppTranslationController extends Controller
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/apptranslation/info/", name="api_apptranslation_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/apptranslation.yaml');

        $api = [];
        $settings = ['title' => $translator->trans('app_translations')];

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

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findActiveLocales();

        if ($locales)
        foreach($locales as $locale) {

            array_push($info['fields'], [
                'id' => 'apptranslation_' . $locale->getLocale(),
                'label' => $translator->trans('Translation') . ' (' . strtoupper($locale->getLocale()) . ')',
                'type' => 'text',
                'required' => false,
                'editable' => true,
                'list' => false,
                'form' => true,
            ]);
        }

        return $this->json($info);
    }

    /**
    * @Route("/api/v1/apptranslation/list/", name="api_apptranslation_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];

        $defaultLocale = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->getDefaultLocale();

        $where = 't.locale_id=' . $defaultLocale->getId();
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $where .= " AND t." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $count = $this->getDoctrine()
        ->getRepository(AppTranslation::class)
        ->countTranslationsList($where);

        $apptranslations = $this->getDoctrine()
        ->getRepository(AppTranslation::class)
        ->findTranslationsList($where, array($sort_column, $sort_direction), $limit, $offset);

        $json = array(
            'total' => $count,
            'data' => $apptranslations,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/apptranslation/get/{id}/", name="api_apptranslation_get"), methods={"GET","HEAD"})
    */
    final public function getAppTranslation(int $id, Request $request)
    {
        if (!empty($id)) {
            $apptranslation = $this->getDoctrine()
            ->getRepository(AppTranslation::class)
            ->find($id);
            if ($apptranslation) {

                $locales = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findActiveLocales();

                $data = array('id' => $id);

                foreach($locales as $locale) {

                    $fieldId = 'apptranslation_' . $locale->getLocale();

                    $fieldAppTranslation = $this->getDoctrine()
                    ->getRepository(AppTranslation::class)
                    ->findTranslation($apptranslation->getTag(), $locale->getId());

                    if ($fieldAppTranslation && !empty($fieldAppTranslation->getTranslation())) $data[$fieldId] = $fieldAppTranslation->getTranslation();
                    $data['tag'] = $fieldAppTranslation->getTag();
                }

                $response = [
                    'success' => true,
                    'data' => $data,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find apptranslation',
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
    * @Route("/api/v1/apptranslation/insert/", name="api_apptranslation_insert", methods={"PUT"})
    * @Route("/api/v1/apptranslation/update/{id}/", name="api_apptranslation_update", methods={"PUT"})
    */
    final public function update(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $apptranslation = $this->getDoctrine()
            ->getRepository(AppTranslation::class)
            ->find($id);
            if (!$apptranslation) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find apptranslation',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($apptranslation, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'AppTranslation has been updated';

            }
        } else {
            $apptranslation = new AppTranslation();
            $message = 'AppTranslation has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['tag'])) $data['tag'] = $params['tag'];
            else $errors[] = 'Tag cannot be empty';

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findActiveLocales();

            foreach($locales as $locale) {

                if (isset($params['apptranslation_' . $locale->getLocale()])) $data['apptranslation_' . $locale->getLocale()] = $params['apptranslation_' . $locale->getLocale()];

                if (!empty($locale->getDefault())) {
                    $fieldAppTranslation = $apptranslation;
                } else {
                    $fieldAppTranslation = $this->getDoctrine()
                    ->getRepository(AppTranslation::class)
                    ->findTranslation($data['tag'], $locale->getId());
                }

                if (!$fieldAppTranslation) {
                    $fieldAppTranslation = new AppTranslation();
                }

                $fieldId = 'apptranslation';
                if (empty($locale->getDefault())) $fieldId = 'apptranslation_' . $locale->getLocale();

                $fieldAppTranslation->setLocale($locale);
                $fieldAppTranslation->setTag($data['tag']);
                $fieldAppTranslation->setTranslation($data['apptranslation_' . $locale->getLocale()]);

                $em = $this->getDoctrine()->getManager();
                $em->persist($fieldAppTranslation);
                $em->flush();

            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($data, 'json');
            $log->add('AppTranslation', $id, $logMessage, $logComment);

            $response = [
                'success' => true,
                'id' => $id,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/apptranslation/delete/", name="api_apptranslation_delete", methods={"PUT"})
    * @Route("/api/v1/apptranslation/delete/{id}/", name="api_apptranslation_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $apptranslationId) {

                $em = $this->getDoctrine()->getManager();
                $apptranslation = $em->getRepository(AppTranslation::class)->find($apptranslationId);

                if ($apptranslation) {

                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($apptranslation, 'json');

                    $log->add('AppTranslation', $id, $logMessage, 'Delete');

                    $em->remove($apptranslation);
                    $em->flush();

                    $relatedAppTranslations = $em->getRepository(AppTranslation::class)->findBy(['parentId' => $apptranslationId]);

                    if ($relatedAppTranslations) {
                        foreach($relatedAppTranslations as $relatedAppTranslation) {
                            $em->remove($relatedAppTranslation);
                            $em->flush();
                        }
                    }

                    $response = ['success' => true];

                } else {
                    $response = [
                        'success' => false,
                        'message' => 'AppTranslation not found.',
                    ];
                }
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/apptranslation/generate/", name="api_apptranslation_generate"))
    */
    final public function generate(TranslatorInterface $translator, LogService $log)
    {
        $fs = new Filesystem();

        $path = 'apptranslations';

        if (!$fs->exists($path)) $fs->mkdir($path);

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findActiveLocales();

        $logMessage = '<i>AppTranslation files created:</i><br>';

        foreach($locales as $locale) {

            $file = $path. '/messages.' . $locale->getLocale() . '.yml';
            $logMessage .= 'messages.' . $locale->getLocale() . '.yml' . '<br>';

            $fs->remove(array($file));
            $fs->touch($file);

            $apptranslations = $this->getDoctrine()
            ->getRepository(AppTranslation::class)
            ->findTranslationsByLocaleId($locale->getId());

            foreach($apptranslations as $apptranslation) {
                if (!empty($apptranslation->getTranslation())) $fs->appendToFile($file, "'" . htmlentities($apptranslation->getTag()) . "': '" . htmlentities($apptranslation->getTranslation()) . "'" . PHP_EOL);
            }

        }

        $log->add('AppTranslation', 0, $logMessage, 'Generate apptranslation files');
        $response = [
            'success' => true,
            'message' => $translator->trans('The apptranslationfiles have been generated'),
        ];
        $json = json_encode($response);
        return $this->json($json);
    }
}
