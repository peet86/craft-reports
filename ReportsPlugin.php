<?php
/**
 * Reports plugin for Craft CMS
 *
 * Report all the things
 *
 * @author    Superbig
 * @copyright Copyright (c) 2017 Superbig
 * @link      https://superbig.co
 * @package   Reports
 * @since     1.0.0
 */

namespace Craft;

class ReportsPlugin extends BasePlugin
{
    /**
     * @return mixed
     */
    public function init ()
    {
        parent::init();

        require_once __DIR__ . '/vendor/autoload.php';
    }

    /**
     * @return mixed
     */
    public function getName ()
    {
        return Craft::t('Reports');
    }

    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return Craft::t('Report all the things');
    }

    /**
     * @return string
     */
    public function getDocumentationUrl ()
    {
        return 'https://github.com/samuelbirch/reports';
    }

    /**
     * @return string
     */
    public function getReleaseFeedUrl ()
    {
        return 'https://github.com/samuelbirch/reports/feed';
    }

    /**
     * @return string
     */
    public function getVersion ()
    {
        return '1.1.0';
    }

    /**
     * @return string
     */
    public function getSchemaVersion ()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getDeveloper ()
    {
        return 'Samuel Birch';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl ()
    {
        return 'https://github.com/samuelbirch';
    }

    /**
     * @return bool
     */
    public function hasCpSection ()
    {
        return true;
    }

    public function registerCpRoutes ()
    {
        return [
            'reports'                        => [ 'action' => 'reports/index' ],
            'reports/new/(?P<reportType>[-\w]+)'  => [ 'action' => 'reports/new' ],
            'reports/edit/(?P<reportId>\d+)' => [ 'action' => 'reports/edit' ],
            'reports/run/(?P<reportId>\d+)'  => [ 'action' => 'reports/run' ],
            'reports/export/(?P<reportId>\d+)'  => [ 'action' => 'reports/export' ],
        ];
    }
}