<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ActivityLogBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\ActivityLogBundle\Compatibility\FieldDescriptor;
use Sulu\Component\ActivityLog\ActivityLoggerInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\ListBuilder\ListRestHelper;
use Symfony\Component\HttpFoundation\Request;

class ActivityLogController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Returns all fields that can be used by list.
     *
     * @Get("activity-log/fields")
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getFieldsAction(Request $request)
    {
        // default contacts list
        return $this->handleView(
            $this->view(
                array_values(
                    $this->getFieldDescriptors()
                ),
                200
            )
        );
    }

    /**
     * Create field-descriptor array.
     *
     * @return FieldDescriptor[]
     */
    private function getFieldDescriptors()
    {
        return [
            'uuid' => new FieldDescriptor('id', 'public.id', true, false),
            'source' => new FieldDescriptor('source', 'public.source', false, true),
            'action' => new FieldDescriptor('action', 'public.action', false, true),
            'value' => new FieldDescriptor('value', 'public.value', false, true),
            'timestamp' => new FieldDescriptor('timestamp', 'public.timestamp', false, true),
            'userId' => new FieldDescriptor('userId', 'public.userId', false, true),
            'userIP' => new FieldDescriptor('userIP', 'public.userIP', false, true),
        ];
    }

    /**
     * Shows all activity-log-items
     *
     * @param Request $request
     *
     * @Get("activity-log")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $restHelper = new ListRestHelper($request);

        /** @var ActivityLoggerInterface $activityLogger */
        $activityLogger = $this->get('sulu_activity_log.activity_logger');

        $page = (int)$restHelper->getPage();
        $limit = (int)$restHelper->getLimit();

        $results = $activityLogger->findAll($page, $limit);

        $list = [];
        foreach ($results as $result) {
            $list[] = [
                'id' => $result['uuid'],
                'source' => $result['src'],
                'action' => $result['action'],
                'value' => $result['value'],
                'timestamp' => $result['ts'],
                'userId' => $result['uid'],
                'userIP' => $result['uip']
            ];
        }
        $list = array_values($list);

        $list = new ListRepresentation(
            $list,
            'activity-log-items',
            'get_activity_logs',
            $request->query->all(),
            $page,
            $limit,
            count($list)
        );

        $view = $this->view($list, 200);

        return $this->handleView($view);
    }

}
