<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\File;
use App\Entity\FileGroup;
use App\Form\FileForm;
use App\Service\FileService;
use App\Service\LogService;


class FileController extends Controller
{

    /**
    * @Route("/api/v1/file/list/", name="api_file_list"))
    */
    final public function getFiles(Request $request)
    {
        $sort_column = $request->request->get('sortColumn', 'id');
        $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
        $limit = $request->request->get('limit', 15);
        $offset = $request->request->get('offset', 0);
        $filter = $request->request->get('filter', '');

        $where = array();
        $whereString = '1=1';
        $filter = explode('&', $filter);
        if (!empty($filter))
        foreach($filter as $filter_item) {
            $filter_item = explode('=', $filter_item);
            if (!empty($filter_item[1])) {
                $where[$filter_item[0]] = $filter_item[1];
                $whereString .= " AND `" . $filter_item[0] . "`='" . $filter_item[1] . "'";
            }
        }

        $qb = $this->getDoctrine()->getRepository(File::class)->createQueryBuilder('s');
        $qb->select('count(s.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        if (empty($limit)) {
            $files = $this->getDoctrine()
            ->getRepository(File::class)
            ->findBy($where, array($sort_column => $sort_direction));
        } else {
            $files = $this->getDoctrine()
            ->getRepository(File::class)
            ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
        }

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => 6,
            'data' => $files
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/file/upload/", name="api_file_upload"))
    */
    final public function processFileUpload(Request $request, FileService $fileService)
    {
        $group = $request->request->get('file-group', '');
        $hide = $request->request->get('file-hide', 0);

        foreach($request->files as $file) {
            $fileId = $fileService->upload($file[0], $group, $hide);
        }

        $json = array(
            'success' => true,
        );

        return $this->json(json_encode($json));
    }

    /**
    * @Route("/api/v1/file/delete/{id}/", name="api_file_delete"))
    */
    final public function delete(int $id, LogService $log)
    {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '<i>Data:</i><br>';
        $logMessage .= $serializer->serialize($file, 'json');

        $log->add('File', $id, $logMessage, 'Delete');

        $em->remove($file);
        $em->flush();

        $json = array(
            'success' => true,
        );

        return $this->json(json_encode($json));
    }
}
