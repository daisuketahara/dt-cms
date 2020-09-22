<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Translation;
use App\Entity\TranslationTranslation;
use App\Entity\Locale;

class TranslationController extends AbstractController
{
    /**
    * @Route("/api/v1/translation/info/", name="api_translation_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/translation.yaml');

        $api = [];
        $settings = ['title' => 'translations'];

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
            ->findAll();

        if ($locales)
        foreach($locales as $locale) {

            $info['fields'][] = [
                'id' => 'translation_' . $locale->getLocale(),
                'label' => $translator->trans('Translation') . ' (' . strtoupper($locale->getLocale()) . ')',
                'type' => 'text',
                'required' => false,
                'editable' => true,
                'list' => false,
                'form' => true,
                'translate' => false,
            ];
        }

        if (!empty($properties['buttons'])) $info['buttons'] = $properties['buttons'];

        return $this->json($info);
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
                    ->findAll();

                $data = array(
                    'id' => $id,
                    'original' => $translation->getOriginal(),
                    'tag' => $translation->getTag(),
                );

                foreach($locales as $locale) {
                    $fieldId = 'translation_' . $locale->getLocale();
                    $data[$fieldId] = $translation->translate($locale->getLocale())->getTranslation();
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

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/translation/insert/", name="api_translation_insert", methods={"PUT"})
    * @Route("/api/v1/translation/update/{id}/", name="api_translation_update", methods={"PUT"})
    */
    final public function update(int $id=0, Request $request, TranslatorInterface $translator)
    {
        if (!empty($id)) {
            $translation = $this->getDoctrine()
                ->getRepository(Translation::class)
                ->find($id);

            if (!$translation) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find translation',
                ];

                return $this->json($json);

            } else {
                $message = 'Translation has been updated';

            }
        } else {
            $translation = new Translation();
            $message = 'Translation has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['original'])) $translation->setOriginal($params['original']);
            else $errors[] = 'Original cannot be empty';

            if (isset($params['tag'])) $translation->setTag($params['tag']);
            else $errors[] = 'tag cannot be empty';

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];

                return $this->json($response);
            }

            $locales = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findAll();

            foreach($locales as $locale) {

                if (isset($params['translation_' . $locale->getLocale()])) $text = $params['translation_' . $locale->getLocale()];
                else $text = '';

                $translation->translate($locale->getLocale())->setTranslation($text);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($translation);
            $translation->mergeNewTranslations();
            $em->flush();

            $response = [
                'success' => true,
                'id' => $id,
            ];
        }

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/translation/delete/", name="api_translation_delete", methods={"PUT"})
    * @Route("/api/v1/translation/delete/{id}/", name="api_translation_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $translationId) {

                $em = $this->getDoctrine()->getManager();
                $translation = $em->getRepository(Translation::class)->find($translationId);

                if ($translation) {

                    $em->remove($translation);
                    $em->flush();
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

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/translation/generate/", name="api_translation_generate"))
    */
    final public function generate(TranslatorInterface $translator)
    {
        $fs = new Filesystem();

        $path = 'translations';

        if (!$fs->exists($path)) $fs->mkdir($path);

        $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findActiveLocales();

        foreach($locales as $locale) {

            $file = $path. '/messages.' . $locale->getLocale() . '.yml';

            $fs->remove(array($file));
            $fs->touch($file);

            $translations = $this->getDoctrine()
                ->getRepository(Translation::class)
                ->findTranslationsByLocaleId($locale->getId());

            foreach($translations as $translation) {
                if (!empty($translation['translation'])) $fs->appendToFile($file, "'" . htmlentities($translation['original']) . "': '" . htmlentities($translation['translation']) . "'" . PHP_EOL);
            }

        }

        $response = [
            'success' => true,
            'message' => $translator->trans('The translationfiles have been generated'),
        ];

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/translation/populate/", name="api_translation_populate"))
    */
    final public function populate(TranslatorInterface $translator, KernelInterface $kernel) {

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

        $response = [
            'success' => true,
            'message'=> $translator->trans('Missing translation scan completed'),
        ];

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/translation/locale/{locale}/", name="api_translation_by_locale"), methods={"GET","HEAD"))
    */
    final public function getTranslationsByLocale(string $locale)
    {
        $translations = $this->getDoctrine()
            ->getRepository(TranslationTranslation::class)
            ->findBy(['locale' => $locale]);

        if ($translations) {

            $data = array();
            foreach($translations as $translation) {
                if (!empty($translation->getTranslation())) $data[$translation->getTranslatable()->getTag()] = $translation->getTranslation();
                else $data[$translation->getTranslatable()->getTag()] = $translation->getTranslatable()->getOriginal();
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

        return $this->json($json);
    }
}
