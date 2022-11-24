<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Controller;

use HeimrichHannot\ApiBundle\Security\JWTCoder;
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
    private JWTCoder $jwtCoder;

    public function __construct(JWTCoder $jwtCoder)
    {
        $this->jwtCoder = $jwtCoder;
    }

    /**
     * @return Response
     *
     * @Route("/login/member", name="api_login_member", methods={"POST"}, defaults={"_scope"="api_login_member", "_entity"="huh.api.entity.member"})
     */
    public function loginMemberAction(Request $request)
    {
        $tokenData = [
            'entity' => 'huh.api.entity.member',
        ];

        if (method_exists($this->getUser(), 'getUserIdentifier')) {
            $tokenData['username'] = $this->getUser()->getUserIdentifier();
        } else {
            $tokenData['username'] = $this->getUser()->getUsername();
        }

        return new JsonResponse(['token' => $this->jwtCoder->encode($tokenData)]);
    }

    /**
     * @return Response
     *
     * @Route("/login/user", name="api_login_user", methods={"POST"}, defaults={"_scope"="api_login_user"})
     */
    public function loginUserAction(Request $request)
    {
        $tokenData = [
            'entity' => 'huh.api.entity.user',
        ];

        if (method_exists($this->getUser(), 'getUserIdentifier')) {
            $tokenData['username'] = $this->getUser()->getUserIdentifier();
        } else {
            $tokenData['username'] = $this->getUser()->getUsername();
        }

        return new JsonResponse(['token' => $this->jwtCoder->encode($tokenData)]);
    }
}
