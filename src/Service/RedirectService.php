<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\Redirect;

class RedirectService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getRedirect($pageRoute)
    {
        $redirect = $this->em->getRepository(Redirect::class)
            ->findBy(array('oldPageRoute' => $pageRoute, 'active' => 1), array());

        if ($redirect) return $redirect[0];
        return false;
    }

    public function addRedirect($oldPageRoute, $newPageRoute)
    {
        // Check if page route exists
        $redirect = $this->getRedirect($oldPageRoute);


        return false;
    }
}
