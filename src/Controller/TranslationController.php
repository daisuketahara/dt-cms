<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\Entity\Translation;
use App\Form\TranslationForm;
use App\Entity\Locale;
use App\Service\LogService;

class TranslationController extends Controller
{
    /**
     * @Route("/{_locale}/admin/translation", name="translation")
     */
     final public function list(TranslatorInterface $translator, LogService $log)
     {

         return $this->render('translation/admin/list.html.twig', array(
             'page_title' => $translator->trans('Translations'),
             'can_add' => true,
             'can_edit' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/translation/ajaxlist", name="translation_ajaxlist")
      */
     final public function ajaxlist(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
         $limit = $request->request->get('limit', 15);
         $offset = $request->request->get('offset', 0);
         $filter = $request->request->get('filter', '');

         $where = array();
         $filter = explode('&', $filter);
         if (!empty($filter))
         foreach($filter as $filter_item) {
             $filter_item = explode('=', $filter_item);
             if (!empty($filter_item[1])) $where[$filter_item[0]] = $filter_item[1];
         }

         if (empty($limit)) {
             $pages = $this->getDoctrine()
                 ->getRepository(Translation::class)
                 ->findTranslationsList();
         } else {
             $pages = $this->getDoctrine()
                 ->getRepository(Translation::class)
                 ->findTranslationsList($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $pages
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/translation/add", name="translation_add")
      * @Route("/{_locale}/admin/translation/edit/{id}", name="translation_edit")
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'Unable to access this page!');

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
                $translation = new Translation();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested setting does not exist!')
                );
                $id = 0; // Set ID to 0, will be used later on
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($translation, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $translation = new Translation();
        }

        $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findActiveLocales();

        $form = $this->createFormBuilder();
        $disabled = false;
        if (!empty($translation->getOriginal())) $disabled = true;
        $form->add('original', TextType::class, array('label' => 'Original text', 'data' => $translation->getOriginal(), 'disabled' => $disabled));

        foreach($locales as $locale) {

            $fieldId = 'translation';
            if (empty($locale->getDefault())) $fieldId = 'translation_' . $locale->getLocale();

            $fieldTranslation = $this->getDoctrine()
                ->getRepository(Translation::class)
                ->findTranslation($translation->getOriginal(), $locale->getId());

            $fieldValue = '';
            if ($fieldTranslation && !empty($fieldTranslation->getTranslation())) $fieldValue = $fieldTranslation->getTranslation();

            $form->add($fieldId, TextType::class, array('label' => 'Translation (' . strtoupper($locale->getLocale()) . ')', 'data' => $fieldValue, 'required' => false));
        }
        $form->add('save', SubmitType::class, array('label' => 'Save'));

        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $formData = $form->getData();

            if (!empty($id)) {
                $fieldTranslation = $this->getDoctrine()
                    ->getRepository(Translation::class)
                    ->find($id);
                $original = $fieldTranslation->getOriginal();
            } else {
                $original = $formData['original'];
            }

            foreach($locales as $locale) {

                $fieldTranslation = $this->getDoctrine()
                    ->getRepository(Translation::class)
                    ->findTranslation($translation->getOriginal(), $locale->getId());

                if (!$fieldTranslation) {
                    $fieldTranslation = new Translation();
                }

                $fieldId = 'translation';
                if (empty($locale->getDefault())) $fieldId = 'translation_' . $locale->getLocale();

                $fieldTranslation->setLocaleId($locale->getId());
                $fieldTranslation->setOriginal($original);
                $fieldTranslation->setTranslation($formData[$fieldId]);

                $em = $this->getDoctrine()->getManager();
                $em->persist($fieldTranslation);
                $em->flush();
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($translation, 'json');
            $log->add('Translation', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );

            return $this->redirectToRoute('translation_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit translation');
        else $title = $translator->trans('Add translation');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

    /**
     * @Route("/{_locale}/admin/translation/generate", name="translation_generate")
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
                $fs->appendToFile($file, "'" . addslashes($translation->getOriginal()) . "': '" . addslashes($translation->getTranslation()) . "'" . PHP_EOL);
            }

        }

        $log->add('Translation', 0, $logMessage, 'Generate translation files');

        $this->addFlash(
        'success',
        $translator->trans('The translation files have been generated!')
        );

        return $this->redirectToRoute('translation');
    }
}
