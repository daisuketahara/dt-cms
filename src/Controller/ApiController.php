<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    public function index()
    {
        $number = mt_rand(0, 100);

        return $this->render('contact/index.html.twig', array(
            'page_title' => 'Contact',
        ));
    }
}
