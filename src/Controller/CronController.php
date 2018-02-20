<?php

    // src/Controller/CronController.php

    namespace App\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class CronController extends Controller
    {

         /**
          * @Route("/{_locale}/cron", name="cron")
          */
        public function index()
        {
            $number = mt_rand(0, 100);

            return $this->render('contact/index.html.twig', array(
                'page_title' => 'Contact',
            ));
        }
    }
