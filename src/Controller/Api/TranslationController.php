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

use App\Entity\Translation;
use App\Entity\Locale;
use App\Service\LogService;

class TranslationController extends Controller
{
    /**
    * @Route("/api/v1/translation/list/", name="api_translation_list"), methods={"GET","HEAD"})
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
        ->getRepository(Translation::class)
        ->countTranslationsList($where);

        $translations = $this->getDoctrine()
        ->getRepository(Translation::class)
        ->findTranslationsList($where, array($sort_column, $sort_direction), $limit, $offset);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $translations,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/translation/get/{id}/", name="api_translation_get"), methods={"GET","HEAD"})
    */
    final public function getTranslation($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $translation = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->find($id);
            if ($translation) {

                $locales = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findActiveLocales();

                $data = array('id' => $id);

                foreach($locales as $locale) {

                    $fieldId = 'translation_' . $locale->getLocale();

                    $fieldTranslation = $this->getDoctrine()
                    ->getRepository(Translation::class)
                    ->findTranslation($translation->getOriginal(), $locale->getId());

                    if ($fieldTranslation && !empty($fieldTranslation->getTranslation())) $data[$fieldId] = $fieldTranslation->getTranslation();
                    $data['original'] = $fieldTranslation->getOriginal();
                }

                $response = [
                    'success' => true,
                    'data' => $data,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find translation',
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
    * @Route("/api/v1/translation/fields/", name="api_translation_fields"), methods={"GET","HEAD"})
    */
    final public function getTranslationFields(Request $request, TranslatorInterface $translator)
    {
        $fields = array();

        $fields[] = [
            'id' => 'original',
            'type' => 'text',
            'label' => $translator->trans('Original'),
            'editable' => false,
            'required' => false,
            'editable' => true,
        ];

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findActiveLocales();

        if ($locales)
        foreach($locales as $locale) {

            $fields[] = [
                'id' => 'translation_' . $locale->getLocale(),
                'type' => 'text',
                'label' => $translator->trans('Translation') . ' (' . strtoupper($locale->getLocale()) . ')',
                'required' => false,
                'editable' => true,
            ];
        }

        $response = [
            'success' => true,
            'fields' => $fields,
        ];

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/translation/insert/", name="api_translation_insert", methods={"PUT"})
    * @Route("/api/v1/translation/update/{id}/", name="api_translation_update", methods={"PUT"})
    */
    final public function update($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $translation = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->find($id);
            if (!$translation) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find translation',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($translation, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Translation has been updated';

            }
        } else {
            $translation = new Translation();
            $message = 'Translation has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['original'])) $data['original'] = $params['original'];
            else $errors[] = 'Original cannot be empty';

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

                if (isset($params['translation_' . $locale->getLocale()])) $data['translation_' . $locale->getLocale()] = $params['translation_' . $locale->getLocale()];

                if (!empty($locale->getDefault())) {
                    $fieldTranslation = $translation;
                } else {
                    $fieldTranslation = $this->getDoctrine()
                    ->getRepository(Translation::class)
                    ->findTranslation($data['original'], $locale->getId());
                }

                if (!$fieldTranslation) {
                    $fieldTranslation = new Translation();
                }

                $fieldId = 'translation';
                if (empty($locale->getDefault())) $fieldId = 'translation_' . $locale->getLocale();

                $fieldTranslation->setLocale($locale);
                $fieldTranslation->setOriginal($data['original']);
                $fieldTranslation->setTranslation($data['translation_' . $locale->getLocale()]);

                $em = $this->getDoctrine()->getManager();
                $em->persist($fieldTranslation);
                $em->flush();

            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($data, 'json');
            $log->add('Translation', $id, $logMessage, $logComment);

            $response = [
                'success' => true,
                'id' => $id,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/translation/delete/", name="api_translation_delete", methods={"PUT"})
    * @Route("/api/v1/translation/delete/{id}/", name="api_translation_delete_multiple", methods={"DELETE"})
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
            foreach($toRemove as $translationId) {

                $em = $this->getDoctrine()->getManager();
                $translation = $em->getRepository(Translation::class)->find($translationId);

                if ($translation) {

                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($translation, 'json');

                    $log->add('Translation', $id, $logMessage, 'Delete');

                    $em->remove($translation);
                    $em->flush();

                    $relatedTranslations = $em->getRepository(Translation::class)->findBy(['parentId' => $translationId]);

                    if ($relatedTranslations) {
                        foreach($relatedTranslations as $relatedTranslation) {
                            $em->remove($relatedTranslation);
                            $em->flush();
                        }
                    }

                    $response = ['success' => true];

                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Translation not found.',
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
    * @Route("/api/v1/translation/generate/", name="api_translation_generate"))
    */
    final public function generate(TranslatorInterface $translator, LogService $log)
    {
        $fs = new Filesystem();

        $path = 'translations';

        if (!$fs->exists($path)) $fs->mkdir($path);

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findActiveLocales();

        $logMessage = '<i>Translation files created:</i><br>';

        foreach($locales as $locale) {

            $file = $path. '/messages.' . $locale->getLocale() . '.yml';
            $logMessage .= 'messages.' . $locale->getLocale() . '.yml' . '<br>';

            $fs->remove(array($file));
            $fs->touch($file);

            $translations = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->findTranslationsByLocaleId($locale->getId());

            foreach($translations as $translation) {
                if (!empty($translation->getTranslation())) $fs->appendToFile($file, "'" . htmlentities($translation->getOriginal()) . "': '" . htmlentities($translation->getTranslation()) . "'" . PHP_EOL);
            }

        }

        $log->add('Translation', 0, $logMessage, 'Generate translation files');
        $response = [
            'success' => true,
            'message' => $translator->trans('The translationfiles have been generated'),
        ];
        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/translation/populate/", name="translation_populate"))
    */
    final public function populate(TranslatorInterface $translator, LogService $log, KernelInterface $kernel) {

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'debug:translation',
            'locale' => 'en',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        $fieldLengths = array();
        $translations = array();

        $i = 0;
        $count = 0;
        $lines = preg_split("/((\r?\n)|(\n?\r))/", $content);
        foreach($lines as $line){
            if (empty($i)) {
                $fields = explode(' ', $line);
                foreach($fields as $field) {
                    $fieldLengths[] = strlen($field);
                }
            } elseif ($i < 3) {
                $i++;
                continue;
            } elseif ($i > (count($lines)-4)) {
                $i++;
                continue;
            } else {
                $state = trim(substr($line, 1, $fieldLengths[1]));
                $domain = trim(substr($line, ($fieldLengths[1]+2), $fieldLengths[2]));
                $translationId = trim(substr($line, ($fieldLengths[1]+$fieldLengths[2]+3), $fieldLengths[3]));

                if (!empty($domain)) {
                    $translations[] = array(
                        'state' => $state,
                        'domain' => $domain,
                        'id' => $translationId,
                    );
                } else {
                    $last = count($translations) - 1;
                    $translations[$last]['id'] .= PHP_EOL . $translationId;
                }
            }
            $i++;
        }

        if (!empty($translations)) {

            $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findActiveLocales();

            foreach ($translations as $key => $translation) {

                $parentId = 0;

                $translationDb = $this->getDoctrine()
                ->getRepository(Translation::class)
                ->findBy(array('original' => $translation['id']));

                if (!$translationDb) {

                    foreach($locales as $localeId) {

                        $locale = $this->getDoctrine()
                        ->getRepository(Locale::class)
                        ->find($localeId);

                        $translationDb = new Translation();
                        $translationDb->setLocale($locale);
                        $translationDb->setOriginal($translation['id']);
                        $translationDb->setTranslation('');
                        if (!empty($parentId)) $translationDb->setParentId($parentId);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($translationDb);
                        $em->flush();

                        if (empty($parentId)) $parentId = $translationDb->getId();
                    }
                }
            }
        }

        $log->add('Translation', 0, '<i>Translations table populated:</i><br>', 'Translation populate');
        $response = [
            'success' => true,
            'message'=> $translator->trans('Missing translation scan completed'),
        ];
        $json = json_encode($response);
        return $this->json($json);
    }

}
