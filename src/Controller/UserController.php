<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\UserInformation;
use App\Service\LogService;
use App\Service\RouteService;


class UserController extends AbstractController
{
    /**
    * @Route("/cron/user/geocode-user/", name="cron_user_geocode")
    */
    final public function send(LogService $log, RouteService $route)
    {
        $where = [
            "u.mailAddress1 IS NOT NULL AND u.mailAddress1 <> ''",
            "u.mailZipcode IS NOT NULL AND u.mailZipcode <> ''",
            "u.mailCity IS NOT NULL AND u.mailCity <> ''",
            //"u.mailCountry IS NOT NULL AND u.mailCountry <> ''",
            "(u.mailLatitude IS NULL OR u.mailLatitude = '')",
            "(u.mailLongitude IS NULL OR u.mailLongitude = '')",
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
            $location[] = $info->getMailAddress1();
            $location[] = $info->getMailZipcode();
            $location[] = $info->getMailCity();
            $location[] = $info->getMailCountry();
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

        $logMessage = '5 users have been updated with geocode';
        //$log->add('Cron User Geocode', 0, $logMessage, 'Cron Geocode User');

        return new Response($logMessage);
    }
}
