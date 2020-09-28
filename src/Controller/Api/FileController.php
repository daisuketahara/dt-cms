<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\File;
use App\Entity\FileGroup;
use App\Form\FileForm;
use App\Service\FileService;


class FileController extends AbstractController
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

        $json = array(
            'total' => 6,
            'data' => $files
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/file/upload/", name="api_file_upload"), methods={"POST"})
    */
    final public function processFileUpload(Request $request, string $uploadDir, string $secureUploadDir, FileService $fileService)
    {
        $group = $request->request->get('file-group', '');
        $hide = $request->request->get('file-hide', 0);
        $file = $request->files->get('upload');
        $result = $fileService->upload($uploadDir, $file, $group, $hide);

        if ($result) {
            $json = array(
                'success' => true,
                'url' => $filePath
            );
        } else {
            $json = array(
                'success' => true,
                'url' => $filePath
            );
        }

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/file/delete/{id}/", name="api_file_delete"))
    */
    final public function delete(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);

        $em->remove($file);
        $em->flush();

        $json = array(
            'success' => true,
        );

        return $this->json($json);
    }
}
