<?php

namespace App\Finance\Controller\Api;

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

use App\Finance\Entity\DiscountCode;
use App\Service\LogService;

class DiscountCodeController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/discount-code/info/", name="api_discount_code_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Finance/Config/DiscountCode.yaml');

        $api = [];
        $settings = ['title' => 'discount_codes'];

        if (!empty($properties['actions'])) {
            foreach($properties['actions'] as $key => $action) {
                if (!empty($action['api'])) $api[$key] = $action['api'];
                elseif (!empty($action['url'])) $settings[$key] = $action['url'];
            }
        }

        $info = array(
            'api' => $api,
            'settings' => $settings,
            'fields' => $properties['fields'],
        );

        if (!empty($properties['buttons'])) $info['buttons'] = $properties['buttons'];

        return $this->json($info);
    }

    /**
    * @Route("/api/v1/discount-code/list/", name="api_discount_code_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
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

        $qb = $this->getDoctrine()->getRepository(DiscountCode::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(DiscountCode::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $discountCodes = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $discountCodes,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/discount-code/get/{id}/", name="api_discount_code_get"), methods={"GET","HEAD"})
    */
    final public function getDiscountCode(int $id, Request $request)
    {
        if (!empty($id)) {
            $discountCode = $this->getDoctrine()
            ->getRepository(DiscountCode::class)
            ->find($id);
            if ($discountCode) {
                $response = [
                    'success' => true,
                    'data' => $discountCode,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find discountCode',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/discount-code/insert/", name="api_discount_code_insert", methods={"PUT"})
    * @Route("/api/v1/discount-code/update/{id}/", name="api_discount_code_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $discountCode = $this->getDoctrine()
            ->getRepository(DiscountCode::class)
            ->find($id);
            if (!$discountCode) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find discountCode',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($discountCode, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'DiscountCode has been updated';

            }
        } else {
            $discountCode = new DiscountCode();
            $message = 'DiscountCode has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['description'])) $discountCode->setDescription($params['description']);
            else $errors[] = 'Description cannot be empty';

            if (isset($params['code'])) $discountCode->setCode($params['code']);
            else $errors[] = 'Code cannot be empty';

            if (isset($params['startDate'])) $discountCode->setStartDate(new \DateTime($params['startDate']));
            if (isset($params['endDate'])) $discountCode->setEndDate(new \DateTime($params['endDate']));
            if (isset($params['price'])) $discountCode->setPrice($params['price']);
            if (isset($params['percentage'])) $discountCode->setPercentage($params['percentage']);
            if (isset($params['maxUse'])) $discountCode->setMaxUse($params['maxUse']);
            if (isset($params['active'])) $discountCode->setActive($params['active']);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($discountCode, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($discountCode);
            $em->flush();
            $id = $discountCode->getId();

            $log->add('DiscountCode', $id, $logMessage, $logComment);

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/discount-code/delete/", name="api_discount_code_delete", methods={"PUT"})
    * @Route("/api/v1/discount-code/delete/{id}/", name="api_discount_code_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $discountCodeId) {

                $em = $this->getDoctrine()->getManager();
                $discountCode = $em->getRepository(DiscountCode::class)->find($discountCodeId);

                if ($discountCode) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($discountCode, 'json');

                    $log->add('DiscountCode', $id, $logMessage, 'Delete');

                    $em->remove($discountCode);
                    $em->flush();
                }
            }

            $response = ['success' => true];

        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/discount-code/check/{code}/", name="api_discount_code_check"), methods={"GET","HEAD"})
    */
    final public function checkCode($code, Request $request)
    {
        $discountCode = $this->getDoctrine()
            ->getRepository(DiscountCode::class)
            ->findOneBy(['code' => $code]);

        if (!$discountCode) {
            $response = [
                'success' => false,
                'message' => 'cannot_find_discount_code',
            ];
        }

        $now = new \DateTime();

        if ($discountCode && $discountCode->getStartDate() > $now) {
            $response = [
                'success' => false,
                'message' => 'discount_code_not_valid_yet',
            ];
        }

        if ($discountCode && !empty($discountCode->getEndDate()) && $discountCode->getEndDate() < $now) {
            $response = [
                'success' => false,
                'message' => 'discount_code_has_expired',
            ];
        }

        if ($discountCode && !empty($discountCode->getMaxUse()) && $discountCode->getMaxUse() >= $discountCode->getUsed()) {
            $response = [
                'success' => false,
                'message' => 'discount_code_cannot_be_used_anymore',
            ];
        }

        if (empty($response)) {
            $response = [
                'success' => true,
                'data' => $discountCode,
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

}
