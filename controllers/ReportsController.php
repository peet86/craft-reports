<?php
/**
 * Reports plugin for Craft CMS
 *
 * Reports Controller
 *
 * @author    Superbig
 * @copyright Copyright (c) 2017 Superbig
 * @link      https://superbig.co
 * @package   Reports
 * @since     1.0.0
 */

namespace Craft;

class ReportsController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = [ ];

    /**
     */
    public function actionIndex ()
    {
        $reports = craft()->reports->getAllReports();
        $types = craft()->reports->getTypes();
        
        $this->renderTemplate('reports/Reports_Index', [
            'reports' => $reports,
            'types' => $types,
        ]);
    }

    public function actionNew (array $variables = [ ])
    {
        if ( !isset($variables['report']) ) {
            $report = new ReportsModel();
        }
        else {
            $report = $variables['report'];
        }

        $report->type = $variables['reportType'];
        
        $options = craft()->reports->getOptions($report->type);

        $this->renderTemplate('reports/Reports_Edit', [
            'report' => $report,
            'options' => $options,
        ]);
    }

    public function actionEdit (array $variables = [ ])
    {
        if ( isset($variables['reportId']) ) {
            $reportId = $variables['reportId'];
            $report   = craft()->reports->getReportById($reportId);
        }
        elseif ( isset($variables['report']) ) {
            $report = $variables['report'];
        }

        if ( empty($report) ) {
            $this->redirect('reports');
        }

        $options = craft()->reports->getOptions($report->type);

        $this->renderTemplate('reports/Reports_Edit', [
            'report' => $report,
            'options' => $options,
        ]);
    }

    public function actionRun (array $variables = [ ])
    {
        $reportId = null;

        if ( isset($variables['reportId']) ) {
            $reportId = $variables['reportId'];
        }

        if ( empty($reportId) ) {
            $this->redirect('reports');
        }

        $result = craft()->reports->run($reportId);

        $this->renderTemplate('reports/Reports_Run', [
            'result' => $result,
        ]);
    }
    public function actionExport (array $variables = [ ])
    {
        $reportId = null;

        if ( isset($variables['reportId']) ) {
            $reportId = $variables['reportId'];
        }

        if ( empty($reportId) ) {
            $this->redirect('reports');
        }

        craft()->reports->exportCsv($reportId);

        craft()->end();
    }


    public function actionSave (array $variables = [ ])
    {
        $id      = craft()->request->getParam('id');
        $name    = craft()->request->getParam('name');
        $type  = craft()->request->getParam('type');
        $content = craft()->request->getParam('content');
        $options = craft()->request->getParam('options');

        if ( $id ) {
            $report = craft()->reports->getReportById($id);
        }
        else {
            $report = new ReportsModel();
        }

        $report->name    = $name;
        $report->type  = $type;
        $report->content = $content;
        $report->options = $options;

        $result = craft()->reports->saveReport($report);

        if ( $result ) {
            craft()->userSession->setNotice(Craft::t('Report saved'));
        }
        else {
            craft()->urlManager->setRouteVariables([
                'report' => $report,
            ]);
        }

        $this->redirect('reports');
    }

    public function actionDeleteReport()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $id = craft()->request->getRequiredPost('id');

        craft()->reports->deleteReportById($id);
        $this->returnJson(['success' => true]);
    }
}