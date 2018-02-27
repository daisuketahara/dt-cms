<?php
namespace App\Routing;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use App\Entity\Page;

class ExtraLoader extends Loader
{
    private $loaded = false;
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the route loader twice');
        }

        $routes = new RouteCollection();

        $pages = $this->em->getRepository(Page::class)->findAll();

        foreach($pages as $page) {
            // prepare a new route
            $path = '/' . $page->getPageRoute();
            $defaults = array(
                '_controller' => 'App\Controller\PageController::loadPage',
            );
            $requirements = array(
            );
            $route = new Route($path, $defaults, $requirements);

            // add the new route to the route collection
            $routeName = strtolower(str_replace('/', '_', $page->getPageRoute()));
            $routes->add($routeName, $route);
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}
