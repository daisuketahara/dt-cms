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

class AdminController extends Controller
{
    /**
    * @Route("/{_locale}/admin/", name="admin_dashboard")
    * @Route("/{_locale}/admin/{page}/", name="admin_spa_page")
    * @Route("/{_locale}/admin/{page}/{page2}/", name="admin_spa_page2")
    * @Route("/{_locale}/admin/{page}/{page2}/{id}/", name="admin_spa_page3")
    */
    public function index(RequestStack $requestStack)
    {
        $_locale = $requestStack->getCurrentRequest()->getLocale();
        $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findOneBy(array('locale' => $_locale));

        $translations = $this->getDoctrine()
            ->getRepository(TranslationText::class)
            ->findBy(['locale' => $locale]);

        if ($translations) {
            $translationsOutput = array();
            foreach($translations as $translation) {
                if (!empty($translation->getText())) $translationsOutput[$translation->getTranslation()->getTag()] = $translation->getText();
                else $translationsOutput[$translation->getTranslation()->getTag()] = $translation->getTranslation()->getOriginal();
            }
        }

        return $this->render('dashboard/admin/index.html.twig', array(
            'page_title' => 'Dashboard',
            'translations' => $translationsOutput,
        ));
    }
}
