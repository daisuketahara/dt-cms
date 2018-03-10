<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Leafo\ScssPhp\Compiler;

use App\Entity\Template;
use App\Service\LogService;

class TemplateController extends Controller
{
    /**
     * @Route("/admin/template", name="template")
     * @Route("/{_locale}/admin/template", name="template_locale")
     */
     final public function list(TranslatorInterface $translator)
     {
         $templates = $this->getDoctrine()
             ->getRepository(Template::class)
             ->findAll();

         return $this->render('template/admin/list.html.twig', array(
             'page_title' => $translator->trans('Templates'),
             'templates' => $templates,
         ));
     }

     /**
      * @Route("/admin/template/edit/{id}", name="template_edit")
      * @Route("/{_locale}/admin/template/edit/{id}", name="template_edit_locale")
      */
    public function edit($id, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {

            $template->setCustomCss($request->request->get('custom-css', ''));
            $template->setCustomJs($request->request->get('custom-js', ''));

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
        }

        return $this->render('template/admin/edit.html.twig', array(
            'page_title' => $translator->trans('Edit template'),
            'templateId' => $template->getId(),
            'custom_css' => $template->getCustomCss(),
            'custom_js' => $template->getCustomJs(),
        ));
    }

     /**
      * @Route("/admin/template/compile/{id}", name="template_compile")
      * @Route("/{_locale}/admin/template/compile/{id}", name="template_compile_locale")
      */
    public function compile($id, TranslatorInterface $translator, LogService $log)
    {
        ini_set('max_execution_time', 300);

        $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);

        try {
			$scss = new Compiler();
            $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');
            $scss->setImportPaths(array(
                'assets/scss/',
                'vendor/twbs/bootstrap/scss',
                'templates/layout/' . $template->getTag() . '/scss',
            ));
            $css = $scss->compile(file_get_contents('vendor/components/css-reset/reset.min.css'));

            if(!empty($template->getAdmin())) $css .= $scss->compile('@import "admin.scss";');

            $css .= $scss->compile(file_get_contents('vendor/daneden/animate.css/animate.min.css'));
            $css .= $scss->compile('@import "index.scss";');
            $css .= $scss->compile($template->getCustomCss());

            if (file_exists('public/css/' . $template->getTag() . '.css')) unlink('public/css/' . $template->getTag() . '.css');
			$myfile = fopen('public/css/' . $template->getTag() . '.css', 'w');
			fwrite($myfile, $css);
			fclose($myfile);
			chmod('public/css/' . $template->getTag() . '.css', 0644);

            $build_style = 1;

        } catch(Exception $e) {
        	$build_style = $e->getMessage();
        }

        // Create symlinks
        // https://symfony.com/doc/current/components/filesystem.html
        $symlinks = array(
            //'vendor/components/jquery/jquery.min.js' => 'public/vendor/jquery/jquery.min.js',
            //'vendor/components/jquery/jquery.min.map' => 'public/vendor/jquery/jquery.min.map',

            //'vendor/components/font-awesome/fonts/fontawesome-webfont.eot' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.eot',
            //'vendor/components/font-awesome/fonts/fontawesome-webfont.svg' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.svg',
            //'vendor/components/font-awesome/fonts/fontawesome-webfont.ttf' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.ttf',
            //'vendor/components/font-awesome/fonts/fontawesome-webfont.woff' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.woff',
            //'vendor/components/font-awesome/fonts/fontawesome-webfont.woff2' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.woff2',
            //'vendor/components/font-awesome/fonts/FontAwesome.otf' => 'public/vendor/font-awesome/fonts/FontAwesome.otf',



        );

        if (!empty($symlinks))
        foreach($symlinks as $target => $link)
        {
            if (file_exists($link)) if (is_link($link)) unlink($link);
            if (!file_exists($link)) symlink($target, $link);
        }

        $this->addFlash(
            'success',
            $translator->trans('CSS file compiled!')
        );

        return $this->redirectToRoute('template_edit', array('id' => $id));
    }
}
