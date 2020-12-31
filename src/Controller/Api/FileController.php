<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
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
        $limit = $request->request->get('limit', 99);
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
    final public function processFileUpload(Request $request, FileService $fileService)
    {
        $group = $request->request->get('file-group', '');
        $hide = $request->request->get('file-hide', false);

        foreach($request->files as $file) {
            if ($file->isValid()) {
                $result = $fileService->upload($file, $group, $hide);
            } else {
                $json = array(
                    'success' => false,
                    'message' => 'An error occurred during upload'
                );
                return $this->json($json);
            }

        }

        if ($result) {
            $json = array(
                'success' => true,
                'url' => $result
            );
        } else {
            $json = array(
                'success' => false,
            );
        }

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/file/delete/{id}/", name="api_file_delete"))
    */
    final public function delete(int $id, ParameterBagInterface $params)
    {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);

        if ($file) {

            $filesystem = new Filesystem();

            if ($file->getHidden()) $path = $params->get('secure_upload_dir');
            else $path = $params->get('upload_dir');

            $filepath = $path . $file->getFileName();
            if ($filesystem->exists($filepath)) $filesystem->remove([$filepath]);

            $em->remove($file);
            $em->flush();
        }

        $json = array(
            'success' => true,
        );

        return $this->json($json);
    }
}
