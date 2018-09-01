<?php
namespace App\Routing;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use App\Entity\Page;
use App\Entity\Redirect;

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

        $pages = $this->em->getRepository(Page::class)->getActivePages();

        foreach($pages as $page) {

            if ($page->getLocale()->getDefault()) $locale = '';
            else $locale = $page->getLocale()->getLocale() . '/';

            // prepare a new route
            $path = '/' . $locale . $page->getPageRoute();
            if (!empty($page->getPageRoute())) $path .= '/';

            $defaults = array(
                '_controller' => 'App\Controller\PageController::loadPage',
            );
            $requirements = array(
            );
            $route = new Route($path, $defaults, $requirements);

            // add the new route to the route collection
            $routeName = 'page_' . $page->getLocale()->getLocale() . '_' . strtolower(str_replace('/', '_', $page->getPageRoute()));
            $routes->add($routeName, $route);
        }

        $redirects = $this->em->getRepository(Redirect::class)->findBy(array('active' => 1));

        foreach($redirects as $redirect) {
            // prepare a new route
            $path = '/' . $redirect->getOldPageRoute() . '/';
            $defaults = array(
                '_controller' => 'App\Controller\PageController::loadPage',
            );
            $requirements = array(
            );
            $route = new Route($path, $defaults, $requirements);

            // add the new route to the route collection
            $routeName = 'dtredirect_' . strtolower(str_replace('/', '_', $redirect->getOldPageRoute()));
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
