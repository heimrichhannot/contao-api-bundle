<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api",defaults={"_format": "json","_scope"="api_login","_token_check"=false})
 */
class ApiUserController extends Controller
{
    /**
     * @return Response
     *
     * @Route("/login", name="api_login", methods={"POST"})
     */
    public function loginAction(Request $request)
    {
        $token = $this->get('huh.api.jwt_coder')->encode(
            [
                'username' => $this->getUser()->getUsername(),
            ]
        );

        return new JsonResponse(['token' => $token]);
    }

}