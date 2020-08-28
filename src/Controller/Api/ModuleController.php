<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Module;
use App\Service\CacheService;


class ModuleController extends AbstractController
{
    /**
        * @Route("/api/v1/module/", name="api_module"), methods={"GET","HEAD"})
    */
    final public function getModules(Request $request, TranslatorInterface $translator)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['filter'])) $filter = $params['filter'];

        $whereString = '1=1';
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(Module::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Module::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        // $qb->orderBy('l.'.$sort_column, $sort_direction);
        // $qb->setMaxResults($limit);
        // $qb->setFirstResult($offset);
        $settings = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $settings,
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/module/activate/", name="api_module_activate"), methods={"POST"})
    */
    final public function activate(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['filter'])) $filter = $params['filter'];

        $whereString = '1=1';
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(Module::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Module::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $modules = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $modules,
        );

        return $this->json($json);
    }
}
