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
use Sulu\Component\ActivityLog\ActivityLogger;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\FieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
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
     * @return mixed
     */
    public function getFieldsAction()
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
     * Shows all activity-log-items.
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
     * returns datagrid list of activity-log for export.
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
            $view = $this->view([$e->getMessage()], 400);
        }

        return $this->handleView($view);
    }

    /**
     * Create field-descriptor array.
     *
     * @return FieldDescriptor[]
     */
    protected function getFieldDescriptors()
    {
        return [
            'uuid' => new FieldDescriptor('id', 'public.id', true, false),
            'data' => new FieldDescriptor('data', 'public.data', false, true),
        ];
    }

    /**
     * returns view of files.
     *
     * @param Request $request
     *
     * @throws EntityNotFoundException
     *
     * @return ListRepresentation
     */
    protected function getActivityLogs(Request $request)
    {
        $restHelper = $this->get('sulu_core.list_rest_helper');

        /** @var ActivityLogger $activityLogger */
        $activityLogger = $this->get('sulu_activity_log.activity_logger');

        $page = (int) $restHelper->getPage();
        $limit = (int) $restHelper->getLimit();
        $sortColumn = $restHelper->getSortColumn();
        $sortOrder = $restHelper->getSortOrder();
        $searchPattern = $restHelper->getSearchPattern();
        $searchFields = $restHelper->getSearchFields();

        $list = $activityLogger->findAllWithSearch(
            $searchPattern,
            $searchFields,
            $page,
            $limit,
            $sortColumn,
            $sortOrder
        );

        $total = $activityLogger->getCountForAllWithSearch($searchPattern, $searchFields);

        $list = array_values($list);

        $list = new ListRepresentation(
            $list,
            'activity-log-items',
            'get_activity_logs',
            $request->query->all(),
            $page,
            $limit,
            $total
        );

        return $list;
    }

    /**
     * @param ListRepresentation $list
     * @param string $delimiter
     *
     * @return string
     */
    protected function listToCsv($list, $delimiter)
    {
        $data = $list->getInline()->getResources();
        $csv = '';
        $headers = array_keys(reset($data));
        foreach ($headers as $header) {
            $csv .= $header . $delimiter;
        }
        $csv = rtrim($csv, $delimiter) . PHP_EOL;

        // iterate over data array
        foreach ($data as $dataLine) {
            $csv .= $this->addLine($dataLine, $delimiter);
        }

        return $csv;
    }

    /**
     * @param array $dataLine
     * @param string $delimiter
     *
     * @return string
     */
    protected function addLine($dataLine, $delimiter)
    {
        $csvLine = '';
        foreach ($dataLine as $dataField) {
            if ($dataField instanceof DateTime) {
                $csvLine .= $dataField->format(DateTime::ISO8601);
            } elseif (is_scalar($dataField)) {
                $csvLine .= $dataField;
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
    protected function generateCsvResponse($csv)
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
