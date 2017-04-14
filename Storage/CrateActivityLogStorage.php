<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ActivityLogBundle\Storage;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Driver\Connection;
use Sulu\Component\ActivityLog\Model\ActivityLog;
use Sulu\Component\ActivityLog\Model\ActivityLogInterface;
use Sulu\Component\ActivityLog\Storage\ActivityLogStorageInterface;

class CrateActivityLogStorage implements ActivityLogStorageInterface
{
    const ACTIVITY_LOG_TABLE_NAME = 'sulu_activity_logs';

    /** @var Registry $em */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type, $uuid = null)
    {
        return new ActivityLog($type, $uuid);
    }

    /**
     * @{inheritdoc}
     */
    public function find($uuid = null)
    {
        $stmt = 'SELECT * FROM ' . self::ACTIVITY_LOG_TABLE_NAME .
            ' WHERE uuid = \'' . $uuid . '\'';

        /** @var Connection $conn */
        $conn = $this->doctrine->getConnection('crate');
        $result = $conn->query($stmt)->fetchAll();

        return $result;
    }

    /**
     * @{inheritdoc}
     */
    public function findAll($page = 1, $pageSize = null)
    {
        $stmt = 'SELECT * FROM ' . self::ACTIVITY_LOG_TABLE_NAME;

        /** @var Connection $conn */
        $conn = $this->doctrine->getConnection('crate');
        $result = $conn->query($stmt)->fetchAll();

        return $result;
    }

    /**
     * @{inheritdoc}
     */
    public function findByParent(ActivityLogInterface $activityLog, $page = 1, $pageSize = null)
    {
    }

    /**
     * @{inheritdoc}
     */
    public function persist(ActivityLogInterface $activityLog)
    {
        $stmt = 'INSERT INTO ' . self::ACTIVITY_LOG_TABLE_NAME .
            ' (uuid, msg) VALUES (\'' . $activityLog->getUuid() . '\', \'' . $activityLog->getMessage() . '\')';


        $conn = $this->doctrine->getConnection('crate');
        $conn->query($stmt);
    }

    /**
     * @{inheritdoc}
     */
    public function flush()
    {

    }
}
