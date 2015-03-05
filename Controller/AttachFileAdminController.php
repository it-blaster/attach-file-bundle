<?php

namespace ItBlaster\AttachFileBundle\Controller;


use ItBlaster\AttachFileBundle\Model\AttachFileQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AttachFileAdminController
 * @package ItBlaster\AttachFileBundle\Controller
 */
class AttachFileAdminController extends Controller
{
    /**
     * Удаление файла
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fileDeleteAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw new \Exception('Access denied');
        }

        $id = $request->get('attach_file');
        $attach_file = AttachFileQuery::create()->findOneById($id);

        $success = $attach_file ? $attach_file->deleteFile() : false;

        $response = new JsonResponse(array(
            'success'   =>  $success,
        ));
        return $response;
    }
}