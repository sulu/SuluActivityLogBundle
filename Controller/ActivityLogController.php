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

use Exception;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\ActivityLogBundle\Compatibility\FieldDescriptor;
use Sulu\Component\ActivityLog\ActivityLogger;
use Sulu\Component\ActivityLog\ActivityLoggerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\ListBuilder\ListRestHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ActivityLogController extends FOSRestController implements ClassResourceInterface
{

    const EXPORT_COLUMN_DELIMITER = ';';
    const EXPORT_FILENAME = 'acitivity-log-export';

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
            'data' => new FieldDescriptor('data', 'public.data', false, true),
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
        $list = $this->getActivityLogs($request);

        $view = $this->view($list, 200);

        return $this->handleView($view);
    }

    /**
     * returns datagrid list of activity-log for export
     *
     * @param Request $request
     *
     * @Get("activity-log/export")
     *
     * @return Response
     */
    public function getActivityLogExportAction(Request $request)
    {
        try {
            $list = $this->getActivityLogs($request);

            return $this->generateCsvResponse($this->listToCsv($list, self::EXPORT_COLUMN_DELIMITER));
        } catch (Exception $e) {
            $view = $this->view(array($e->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * returns view of files
     *
     * @param Request $request
     *
     * @throws EntityNotFoundException
     *
     * @return ListRepresentation
     */
    private function getActivityLogs(Request $request)
    {
        $restHelper = new ListRestHelper($request);

        /** @var ActivityLogger $activityLogger */
        $activityLogger = $this->get('sulu_activity_log.activity_logger');

        $page = (int)$restHelper->getPage();
        $limit = (int)$restHelper->getLimit();

        $list = $activityLogger->findAll($page, $limit);
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

        return $list;
    }

    /**
     * @param ListRepresentation $list
     * @param string $delimiter
     *
     * @return string
     */
    private function listToCsv($list, $delimiter)
    {
        $data = $list->getInline()->getResources();
        $csv = '';
        $headers = array_keys(reset($data));
        foreach ($headers as $header) {
            $csv .= $header . $delimiter;
        }
        $csv = rtrim($csv, $delimiter) . PHP_EOL;

        // iterate over data array
        foreach ($data as $dataline) {
            $csv .= $this->addLine($dataline, $delimiter);
        }

        return $csv;
    }

    /**
     * @param array $dataline
     * @param string $delimiter
     *
     * @return string
     */
    private function addLine($dataline, $delimiter)
    {
        $csvLine = '';
        foreach ($dataline as $datafield) {
            if ($datafield instanceof DateTime) {
                $csvLine .= $datafield->format(DateTime::ISO8601);
            } elseif (is_scalar($datafield)) {
                $csvLine .= $datafield;
            }
            $csvLine .= $delimiter;
        }
        $csvLine = rtrim($csvLine, $delimiter) . PHP_EOL;

        return $csvLine;
    }

    /**
     * @param string $csv
     *
     * @return Response
     */
    private function generateCsvResponse($csv)
    {
        $response = new Response();
        $response->setContent($csv);

        $name = self::EXPORT_FILENAME . '-' . date('Ymd') . '.csv';
        $disponent = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        $response->headers->set('Content-Disposition', $disponent);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }
}
