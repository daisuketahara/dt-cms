<?php

    // src/Controller/ContactController.php

    namespace App\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class ContactController extends Controller
    {
        /**
         * @Route("/{_locale}/contact", name="contact")
         */
        public function index()
        {
            $number = mt_rand(0, 100);

            return $this->render('contact/index.html.twig', array(
                'page_title' => 'Contact',
            ));
        }
    }
