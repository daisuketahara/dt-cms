<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;;
use Leafo\ScssPhp\Compiler;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\Locale;
use App\Entity\Page;
use App\Entity\PageContent;

class PageController extends AbstractController
{
    public function loadPage(Request $request)
    {
        $route = $request->attributes->get('_route');
        //var_dump($route);exit;
        $route = explode('_', $route);
        $locale = $route[1];
        $routeName = $route[2];

        // Check if visitor already visited site and locale session is set
        $session = new Session();
        $sessionLocale = $session->get('user_locale');
        if (empty($sessionLocale)) {
            $clientLocale = strtolower(str_split($_SERVER['HTTP_ACCEPT_LANGUAGE'], 2)[0]);
            //$session->set('user_locale', $clientLocale);

            if ($locale != $clientLocale) {







                //return $this->redirect($this->generateUrl('en_route'));
            }
        }


        $request->setLocale($locale);
        $localeEntity = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findOneBy(array('locale' => $locale));

        $page = $this->getDoctrine()
        ->getRepository(PageContent::class)
        ->findOneBy(array('locale' => $localeEntity, 'pageRoute' => $routeName));

        if (!$page) {
            throw $this->createNotFoundException('The page does not exist');
        }

        $metaTitle = $page->getMetaTitle();
        if (empty($metaTitle)) $metaTitle = $page->getPageTitle();

        $customCss = $page->getCustomCss();
        $constructCss = $page->getConstructCss();
        try {
            $scss = new Compiler();
            $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');
            $css = $scss->compile($customCss);
            $css .= $scss->compile($constructCss);
        } catch(Exception $e) {
            $css = $customCss;
        }

        return $this->render('layout/app/page.html.twig', array(
            'page_title' => $page->getPageTitle(),
            'content' => $page->getContent(),
            'meta_title' => $metaTitle,
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'meta_custom' => $page->getMetaCustom(),
            'publish_date' => $page->getPage()->getPublishDate(),
            'page_width' => $page->getPageWidth(),
            'disable_layout' => $page->getDisableLayout(),
            'main_image' => $page->getMainImage(),
            'custom_css' => $css,
            'custom_js' => $page->getCustomJs(),
        ));
    }

    final public function notfound()
    {
        throw $this->createNotFoundException('The file does not exist');
        return $this->render('page/404.html.twig');
    }

    /**
    * @Route("/{_locale}/page-not-found/", name="site_page_not_found"))
    */
    public function pageNotFound(Request $request)
    {
        return $this->render('page/404.html.twig');
    }

    /**
    * @Route("/{_locale}/access-denied/", name="site_page_access_denied"))
    */
    public function accessDenied(Request $request)
    {
        return $this->render('page/401.html.twig');
    }

    /**
    * @Route("/cron/generate-sitemap/", name="cron_page_generate_sitemap"))
    */
    public function generateSitemap(Request $request)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        $defaultLocale = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findBy(array('default' => true));

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findAll();

        $pages = $this->getDoctrine()
        ->getRepository(Page::class)
        ->findBy(array('status' => 1, 'locale' => $defaultLocale));

        $setting = $this->getDoctrine()
        ->getRepository(Setting::class)
        ->findOneBy(array('settingKey' => 'site.url'));
        $domain = $setting->getSettingValue();

        if ($pages) {
            foreach($pages as $page) {

                $pageRoute = $page->getPageRoute();
                if (!empty($pageRoute)) $pageRoute .= '/';

                $xml .= '<url>' . PHP_EOL;
                $xml .= '<loc>' . $domain . '/' . $pageRoute . '</loc>' . PHP_EOL;
                $xml .= '<lastmod>' . $page->getLastModified()->format('Y-m-d') . '</lastmod>' . PHP_EOL;
                $xml .= '<changefreq>monthly</changefreq>' . PHP_EOL;
                $xml .= '<priority>' . round(($page->getPageWeight()/10),1) . '</priority>' . PHP_EOL;

                if ($locales) {
                    foreach($locales as $locale) {

                        if (empty($locale->getDefault())) {

                            $translatedPage = $this->getDoctrine()
                            ->getRepository(Page::class)
                            ->findOneBy(array('defaultId' => $page->getId(), 'status' => 1, 'locale' => $locale));

                            if ($translatedPage) {
                                $translatedPageRoute = $translatedPage->getPageRoute();
                                if (!empty($pageRoute)) $translatedPageRoute .= '/';
                                $xml .= '<xhtml:link rel="alternate" hreflang="' . $locale->getLocale() . '" href="' . $domain . '/' . $locale->getLocale()  . '/' . $translatedPageRoute . '"/>' . PHP_EOL;
                            }

                        } else {
                            $xml .= '<xhtml:link rel="alternate" hreflang="' . $locale->getLocale() . '" href="' . $domain . '/' . $pageRoute . '"/>' . PHP_EOL;
                        }
                    }
                }
                $xml .= '</url>' . PHP_EOL;
            }
        }
        $xml .= '</urlset>';

        if (file_exists('public/sitemap.xml')) unlink('public/sitemap.xml');
        $sitemap = fopen('public/sitemap.xml', 'w');
        fwrite($sitemap, $xml);
        fclose($sitemap);

        echo '1';
        exit;
    }
}
