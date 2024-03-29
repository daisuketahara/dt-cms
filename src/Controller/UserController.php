<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\UserInformation;
use App\Service\RouteService;


class UserController extends AbstractController
{
    /**
    * @Route("/cron/user/geocode-user/", name="cron_user_geocode")
    */
    final public function send(RouteService $route)
    {
        $where = [
            "u.address1 IS NOT NULL AND u.address1 <> ''",
            "u.zipcode IS NOT NULL AND u.zipcode <> ''",
            "u.city IS NOT NULL AND u.city <> ''",
            //"u.country IS NOT NULL AND u.country <> ''",
            "(u.latitude IS NULL OR u.latitude = '')",
            "(u.longitude IS NULL OR u.longitude = '')",
        ];
        $where = implode(' AND ', $where);

        $qb = $this->getDoctrine()->getRepository(UserInformation::class)->createQueryBuilder('u');
        $qb->select();
        $qb->where($where);
        $qb->setMaxResults(5);
        $users = $qb->getQuery()->getResult();

        $success = 0;
        $fail = 0;
        foreach($users as $info) {

            $location = array();
            $location[] = $info->getAddress1();
            $location[] = $info->getZipcode();
            $location[] = $info->getCity();
            $location[] = $info->getCountry();
            $location = implode(', ', $location);

            $coordinates = $route->getCoordinates($location);

            if (!empty($coordinates)) {
                $info->setMailLongitude($coordinates[0]);
                $info->setMailLatitude($coordinates[1]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($info);
            $em->flush();
        }

        return new Response($logMessage);
    }
}
