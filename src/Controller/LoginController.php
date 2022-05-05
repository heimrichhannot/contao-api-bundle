<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api",defaults={"_format": "json","_token_check"=false})
 */
class LoginController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route("/login/member", name="api_login_member", methods={"POST"}, defaults={"_scope"="api_login_member", "_entity"="huh.api.entity.member"})
     */
    public function loginMemberAction(Request $request)
    {
        $token = $this->get('huh.api.jwt_coder')->encode(
            [
                'entity' => 'huh.api.entity.member',
                'username' => $this->getUser()->getUsername(),
            ]
        );

        return new JsonResponse(['token' => $token]);
    }

    /**
     * @return Response
     *
     * @Route("/login/user", name="api_login_user", methods={"POST"}, defaults={"_scope"="api_login_user", "_entity"="huh.api.entity.user"})
     */
    public function loginUserAction(Request $request)
    {
        $token = $this->get('huh.api.jwt_coder')->encode(
            [
                'entity' => 'huh.api.entity.user',
                'username' => $this->getUser()->getUsername(),
            ]
        );

        return new JsonResponse(['token' => $token]);
    }
}
