<?php
/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Sulu\Bundle\ActivityLogBundle\Repository;

use Sulu\Component\ActivityLog\Repository\UserRepositoryInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface as SuluUserRepositoryInterface;

/**
 * Repository for the User, implementing some additional functions
 * for querying objects
 */
class SuluUserRepositoryAdapter implements UserRepositoryInterface
{
    private $userRepository;

    public function __construct(SuluUserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findOneById($id)
    {
        // TODO: Implement findOneById() method.
    }
}
