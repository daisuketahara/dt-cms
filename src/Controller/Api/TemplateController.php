<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Leafo\ScssPhp\Compiler;

use App\Entity\Template;
use App\Service\CacheService;
use App\Service\LogService;

class TemplateController extends Controller
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/template/list/", name="api_template_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $templates = $this->getDoctrine()
            ->getRepository(Template::class)
            ->findAll();

        $json = array(
            'success' => true,
            'data' => $templates,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/template/get/{id}/", name="api_template_get"), methods={"GET","HEAD"})
    */
    final public function getTemplate(int $id, Request $request)
    {
        if (!empty($id)) {
            $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);
            if ($template) {
                $response = [
                    'success' => true,
                    'data' => $template,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find template',
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
    * @Route("/api/v1/template/update/{id}/", name="api_template_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log, Filesystem $fileSystem)
    {
        ini_set('max_execution_time', 300);

        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);
            if (!$template) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find template',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($template, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Template has been updated';

            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['customCss'])) $template->setCustomCss($params['customCss']);
            if (isset($params['customJs'])) $template->setCustomJs($params['customJs']);

            if ($template->getId() == 1) {
                if (isset($params['settings'])) $template->setSettings($params['settings']);
            }

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($template, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

            $log->add('Template', $id, $logMessage, $logComment);

            try {
                $scss = new Compiler();
                $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');

                $css = '';

                if ($template->getId() == 1) {
                    if ($params['settings']['header'] == 'standard') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header.scss";');
                    elseif ($params['settings']['header'] == 'topfixed') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header-top.scss";');
                    elseif ($params['settings']['header'] == 'overlay') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header-overlay.scss";');




                }

                $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/index.scss";');
                $css .= $scss->compile($template->getCustomCss());

                if (file_exists('public/css/' . $template->getTag() . '.css')) unlink('public/css/' . $template->getTag() . '.css');
                $myfile = fopen('public/css/' . $template->getTag() . '.css', 'w');
                fwrite($myfile, $css);
                fclose($myfile);
                chmod('public/css/' . $template->getTag() . '.css', 0644);

            } catch(Exception $e) {
                $response = array(
                    'success' => false,
                    'message' => $e->getMessage()
                );

                $json = json_encode($response);
                return $this->json($json);
            }

            // Create symlinks
            // https://symfony.com/doc/current/components/filesystem.html
            $symlinks = array(
                'assets/js/dymo-print.min.js' => 'public/js/dymo-print.js',
                'assets/vendor/onsen-ui' => 'public/vendor/onsen-ui',
                'assets/vendor/signature_pad/docs/js/signature_pad.umd.js' => 'public/vendor/signature-pad/signature-pad.js',
                'assets/vendor/dymo/DYMO.Label.Framework.2.0.2.js' => 'public/vendor/dymo-framework.js',
                'assets/vendor/JsBarcode.all.min.js' => 'public/vendor/JsBarcode.all.min.js',
                'vendor/nnnick/chartjs/dist' => 'public/vendor/chartjs',
                'vendor/moment/moment/min' => 'public/vendor/moment',
            );

            if ($fileSystem->exists('public/js')) $fileSystem->mkdir('public/js', 0644);
            if ($fileSystem->exists('public/vendor')) $fileSystem->mkdir('public/vendor', 0644);

            $path = getcwd() . '/';

            if (!empty($symlinks))
            foreach($symlinks as $target => $link) {
                if (file_exists($path.$link)) if (is_link($path.$link)) unlink($path.$link);
                if (!file_exists($path.$link)) $fileSystem->symlink($path.$target, $path.$link, true);
            }

            if ($template->getId() == 1) {
                $cache = new CacheService();
                $cache->delete('template.front');
            }

            $response = [
                'success' => true,
                'message' => $message,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }
}
