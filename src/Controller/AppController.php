<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Entity\Locale;
use App\Entity\Translation;
use App\Entity\TranslationText;

class AppController extends Controller
{
    /**
    * @Route("/", name="app_spa_home")
    * @Route("/{_locale}/", name="app_spa_page1")
    * @Route("/{_locale}/{page}/", name="app_spa_page2")
    * @Route("/{_locale}/{page}/{page2}/", name="app_spa_page3")
    * @Route("/{_locale}/{page}/{page2}/{page3}/", name="app_spa_page4")
    */
    public function index(RequestStack $requestStack)
    {
        $_locale = $requestStack->getCurrentRequest()->getLocale();
        $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findOneBy(array('locale' => $_locale));

        if (!$locale) {
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->getDefaultLocale();
                $requestStack->getCurrentRequest()->setLocale($locale->getLocale());
        }

        $translations = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->findTranslationsByLocaleId($locale->getId());

        $translationsOutput = array();
        foreach($translations as $translation) {
            $translationsOutput[$translation['tag']] = $translation['text'];
        }

        return $this->render('layout/app/index.html.twig', array(
            'page_title' => 'App',
            'translations' => $translationsOutput,
        ));
    }
}
