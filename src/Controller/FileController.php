<?php

    // src/Controller/FileController.php

    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    class FileController extends Controller
    {
        public function index($file)
        {
            if (file_exists($file)) return $this->file($file);
            else throw $this->createNotFoundException('The file does not exist');

        }
    }
