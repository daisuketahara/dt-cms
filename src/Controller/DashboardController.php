<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends Controller
{
    /**
     * @Route("/admin", name="admin_dashboard")
     * @Route("/{_locale}/admin", name="admin_dashboard_locale")
     */
    public function index()
    {
        return $this->render('dashboard/admin/index.html.twig', array(
            'page_title' => 'Dashboard',
        ));
    }
}
