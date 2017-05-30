<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ActivityLogBundle\Tests\Functional\Controller;

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\ActivityLog\Storage\ActivityLogStorageInterface;
use Symfony\Component\HttpKernel\Client;

class ActivityLogControllerTest extends SuluTestCase
{

    /**
     * @var ActivityLogStorageInterface
     */
    private $storage;

    /**
     * @param Client $client
     */
    private function initStorage(Client $client)
    {
        $this->storage = $client->getContainer()->get('sulu_activity_log.array_activity_log_storage');

        $activityLog = $this->storage->create('log');
        $activityLog->setTitle('testA');
        $this->storage->persist($activityLog);

        $activityLog2 = $this->storage->create('log');
        $activityLog2->setTitle('testB');
        $this->storage->persist($activityLog2);

        $activityLog2 = $this->storage->create('log');
        $activityLog2->setTitle('testC');
        $this->storage->persist($activityLog2);

        $activityLog3 = $this->storage->create('log');
        $activityLog3->setTitle('different title');
        $this->storage->persist($activityLog3);

        $activityLog4 = $this->storage->create('log');
        $this->storage->persist($activityLog4);
        $this->storage->flush();
    }

    public function testGetActivityLogs()
    {
        $client = $this->createAuthenticatedClient();
        $this->initStorage($client);

        $client->request(
            'GET',
            '/api/activity-log?sortOrder=asc'
        );

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayNotHasKey('title', $response['_embedded']['activity-log-items'][0]);
        $this->assertArrayHasKey('title', $response['_embedded']['activity-log-items'][1]);
        $this->assertCount(5, $response['_embedded']['activity-log-items']);
    }

    public function testGetActivityLogsWithSortOrder()
    {
        $client = $this->createAuthenticatedClient();
        $this->initStorage($client);

        $client->request(
            'GET',
            '/api/activity-log?sortOrder=desc'
        );

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('title', $response['_embedded']['activity-log-items'][0]);
        $this->assertArrayNotHasKey('title', $response['_embedded']['activity-log-items'][4]);
        $this->assertCount(5, $response['_embedded']['activity-log-items']);
    }

    public function testGetActivityLogsWithSearch()
    {
        $client = $this->createAuthenticatedClient();
        $this->initStorage($client);

        $client->request(
            'GET',
            '/api/activity-log?search=test'
        );

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(3, $response['_embedded']['activity-log-items']);
    }

    public function testGetActivityLogsWithPagination()
    {
        $client = $this->createAuthenticatedClient();
        $this->initStorage($client);

        $client->request(
            'GET',
            '/api/activity-log?page=3&limit=2'
        );

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['_embedded']['activity-log-items']);
    }

    public function testGetActivityLogsWithSearchAndPagination()
    {
        $client = $this->createAuthenticatedClient();
        $this->initStorage($client);

        $client->request(
            'GET',
            '/api/activity-log?search=test&page=2&limit=2'
        );

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['_embedded']['activity-log-items']);
    }
}
