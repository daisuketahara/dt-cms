<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Leafo\ScssPhp\Compiler;

use App\Entity\Template;
use App\Service\CacheService;

class TemplateController extends AbstractController
{
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

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/template/update/{id}/", name="api_template_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, Filesystem $fileSystem)
    {
        ini_set('max_execution_time', 300);

        if (!empty($id)) {
            $template = $this->getDoctrine()
                ->getRepository(Template::class)
                ->find($id);

            if (!$template) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find template',
                ];
                return $this->json($response);

            } else {
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
                return $this->json($response);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

            try {
                $scss = new Compiler();
                $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');

                $css = '';

                if ($template->getId() == 1) {
                    if ($params['settings']['header'] == 'standard') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header.scss";');
                    elseif ($params['settings']['header'] == 'top') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header-top.scss";');
                    elseif ($params['settings']['header'] == 'centered') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header-centered.scss";');
                    elseif ($params['settings']['header'] == 'overlay') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/header-overlay.scss";');

                    if ($params['settings']['footer'] == 'standard') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/footer.scss";');
                    elseif ($params['settings']['footer'] == 'bottom') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/footer-bottom.scss";');
                    elseif ($params['settings']['footer'] == 'centered') $css .= $scss->compile('@import "templates/layout/' . $template->getTag() . '/scss/footer-centered.scss";');
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

                return $this->json($response);
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

        return $this->json($response);
    }
}
