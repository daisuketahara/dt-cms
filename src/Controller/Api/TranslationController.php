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
use App\Entity\TranslationText;
use App\Entity\Locale;
use App\Service\LogService;

class TranslationController extends Controller
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/translation/info/", name="api_translation_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/translation/list/',
                'get' => '/translation/get/',
                'insert' => '/translation/insert/',
                'update' => '/translation/update/',
                'delete' => '/translation/delete/'
            ),
            'buttons' => array(
                [
                    'id' => 'generate',
                    'label' => $translator->trans('Generate translation files'),
                    'api' => '/translation/generate/'
                ],
                [
                    'id' => 'populate',
                    'label' => $translator->trans('Get missing translations'),
                    'api' => '/translation/populate/'
                ],
                [
                    'id' => 'export',
                    'label' => $translator->trans('Export'),
                    'url' => '/export/translation/'
                ]
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
                    'id' => 'tag',
                    'label' => 'tag',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'original',
                    'label' => 'original',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'complete',
                    'label' => 'complete',
                    'type' => 'text',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ]
            ),
        );


        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findActiveLocales();

        if ($locales)
        foreach($locales as $locale) {

            $info['fields'][] = [
                'id' => 'translation_' . $locale->getLocale(),
                'label' => $translator->trans('Translation') . ' (' . strtoupper($locale->getLocale()) . ')',
                'type' => 'text',
                'required' => false,
                'editable' => true,
                'show_list' => false,
                'show_form' => true,
            ];
        }

        return $this->json(json_encode($info));
    }

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

        $where = '1=1';
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

        $json = array(
            'total' => $count,
            'data' => $translations,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/translation/get/{id}/", name="api_translation_get"), methods={"GET","HEAD"})
    */
    final public function getTranslation(int $id, Request $request)
    {
        if (!empty($id)) {
            $translation = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->find($id);
            if ($translation) {

                $locales = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findActiveLocales();

                $data = array(
                    'id' => $id,
                    'original' => $translation->getOriginal(),
                    'tag' => $translation->getTag(),
                );

                foreach($locales as $locale) {

                    $fieldId = 'translation_' . $locale->getLocale();

                    $fieldTranslation = $this->getDoctrine()
                    ->getRepository(TranslationText::class)
                    ->findOneBy(['translation' => $translation, 'locale' => $locale]);

                    if ($fieldTranslation && !empty($fieldTranslation->getText())) $data[$fieldId] = $fieldTranslation->getText();
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

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/translation/insert/", name="api_translation_insert", methods={"PUT"})
    * @Route("/api/v1/translation/update/{id}/", name="api_translation_update", methods={"PUT"})
    */
    final public function update(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
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
                $logMessage .= $this->serializer->serialize($translation, 'json');
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

            if (isset($params['tag'])) $data['tag'] = $params['tag'];
            else $errors[] = 'tag cannot be empty';

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

                $translationText = $this->getDoctrine()
                    ->getRepository(TranslationText::class)
                    ->findOneBy(['translation'=> $translation, 'locale' => $locale]);

                if (!$translationText) $translationText = new TranslationText();

                $translationText->setTranslation($translation);
                $translationText->setLocale($locale);
                $translationText->setText($data['translation_' . $locale->getLocale()]);

                $em = $this->getDoctrine()->getManager();
                $em->persist($translationText);
                $em->flush();
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($data, 'json');
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
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $translationId) {

                $em = $this->getDoctrine()->getManager();
                $translation = $em->getRepository(Translation::class)->find($translationId);

                if ($translation) {

                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($translation, 'json');

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
                if (!empty($translation['text'])) $fs->appendToFile($file, "'" . htmlentities($translation['original']) . "': '" . htmlentities($translation['text']) . "'" . PHP_EOL);
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
    * @Route("/api/v1/translation/populate/", name="api_translation_populate"))
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
                $translations[] = trim(substr($line, ($fieldLengths[1]+$fieldLengths[2]+3), $fieldLengths[3]));
            }
            $i++;
        }

        if (!empty($translations)) {

            $locales = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findActiveLocales();

            foreach ($translations as $key => $original) {

                $translationDb = $this->getDoctrine()
                    ->getRepository(Translation::class)
                    ->findBy(array('original' => $original));

                if (!$translationDb) {
                    $translationDb = new Translation();

                    $tag = preg_replace('~[^\pL\d]+~u', '_', $original);
                    $tag = iconv('utf-8', 'us-ascii//TRANSLIT', $tag);
                    $tag = preg_replace('~[^-\w]+~', '', $tag);
                    $tag = trim($tag, '-');
                    $tag = preg_replace('~-+~', '-', $tag);
                    $tag = strtolower($tag);

                    $translationDb->setTag($tag);
                    $translationDb->setOriginal($original);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($translationDb);
                    $em->flush();
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

    /**
    * @Route("/api/v1/translation/locale/{locale}", name="api_translation_by_locale"), methods={"GET","HEAD"))
    */
    final public function getTranslationsByLocale(string $locale)
    {
        $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findBy(['locale' => $locale]);

        if ($locale) {
            $translations = $this->getDoctrine()
                ->getRepository(TranslationText::class)
                ->findBy(['locale' => $locale]);

            if ($translations) {

                $data = array();
                foreach($translations as $translation) {
                    if (!empty($translation->getText())) $data[$translation->getTranslation()->getTag()] = $translation->getText();
                    else $data[$translation->getTranslation()->getTag()] = $translation->getTranslation()->getOriginal();
                }

                $json = array(
                    'success' => true,
                    'data' => $data,
                );
            } else {
                $json = array(
                    'success' => false,
                    'message' => 'Cannot find translations',
                );
            }
        } else {
            $json = array(
                'success' => false,
                'message' => 'Locale cannot be found',
            );
        }

        $json = $this->serializer->serialize($json, 'json');
        return $this->json($json);
    }
}
