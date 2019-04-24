<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class FileGroupController extends Controller
{
    /**
    * @Route("/{_locale}/admin/filegroup/", name="filegroup"))
    */
    final public function list(TranslatorInterface $translator)
    {
        return $this->render('file_group/admin/list.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $translator->trans('Filegroups'),
        ));
    }

}
