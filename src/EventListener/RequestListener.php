<?php

namespace App\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestListener
{
    private $tokenStorage;
    private $router;
    private $em;

    public function __construct(EntityManager $em, TokenStorageInterface $t, RouterInterface $r)
    {
        $this->tokenStorage = $t;
        $this->router = $r;
        $this->em = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $permissions = array(
            '_twig_error_test',
            '_wdt',
            '_profiler_home',
            '_profiler_search',
            '_profiler_search_bar',
            '_profiler_phpinfo',
            '_profiler_search_results',
            '_profiler_open_file',
            '_profiler',
            '_profiler_router',
            '_profiler_exception',
            '_profiler_exception_css',
            'page_not_found',
            'page_access_denied',
            'login',
            'logout',
        );

        $sql = "SELECT p2.route_name FROM role AS r ";
        $sql .= "LEFT JOIN role_permission AS rp ON r.id = rp.role_id ";
        $sql .= "LEFT JOIN permission AS p2 ON p2.id = rp.permission_id ";
        $sql .= "WHERE r.name = 'Anonymous' ";
        $sql .= "GROUP BY p2.route_name ";

        //return true; // comment this line to stop permission check
        if ($this->tokenStorage->getToken()) $user = $this->tokenStorage->getToken()->getUser();
        if (!empty($user) && $user != 'anon.' && $event->isMasterRequest()) {
            $sql .=  "UNION ";
            $sql .=  "SELECT p.route_name FROM user_permission AS up ";
            $sql .=  "LEFT JOIN permission AS p ON up.permission_id = p.id ";
            $sql .=  "WHERE up.user_id = " . $user->getId() . " ";
            $sql .=  "GROUP BY p.route_name ";
            $sql .=  "UNION ";
            $sql .=  "SELECT p2.route_name FROM user_role AS ur ";
            $sql .=  "LEFT JOIN role_permission AS rp ON ur.role_id = rp.role_id ";
            $sql .=  "LEFT JOIN permission AS p2 ON p2.id = rp.permission_id ";
            $sql .=  "WHERE ur.user_id = " . $user->getId() . " ";
            $sql .=  "GROUP BY p2.route_name";
        }

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if ($result)
        foreach($result as $row) $permissions[] = $row['route_name'];

        $currentRoute = $event->getRequest()->attributes->get('_route');
        $redirectpos = strpos($currentRoute, 'dtredirect_');

        //var_dump($currentRoute);exit;

        if (empty($currentRoute)) {
            throw new NotFoundHttpException('The page does not exist');
        } elseif (!in_array($currentRoute, $permissions) && $redirectpos === false) {
            $response = new RedirectResponse($this->router->generate('page_access_denied'));
            $event->setResponse($response);
        }
    }
}
