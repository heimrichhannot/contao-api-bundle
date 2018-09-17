<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\ApiResource;


use HeimrichHannot\ApiBundle\Security\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class MemberResource implements ResourceInterface
{
    /**
     * @inheritDoc
     */
    public function create(Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function update($id, Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function list(Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function show($id, Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function delete($id, Request $request, UserInterface $user): ?array
    {
        return null;
    }

}