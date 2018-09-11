<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\PrivacyApiBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/privacy-api",defaults={"_scope"="privacy_api","_token_check"=false})
 */
class PrivacyApiController extends Controller
{
    /**
     * @return Response
     *
     * @Route("/authenticate", name="privacy_api_authenticate")
     */
    public function authenticateAction(Request $request)
    {
        return new JsonResponse([]);
    }

}