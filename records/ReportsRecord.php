<?php
/**
 * Reports plugin for Craft CMS
 *
 * Reports Record
 *
 * @author    Superbig
 * @copyright Copyright (c) 2017 Superbig
 * @link      https://superbig.co
 * @package   Reports
 * @since     1.0.0
 */

namespace Craft;

class ReportsRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName ()
    {
        return 'reports';
    }

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes ()
    {
        return array(
            'name'     => array( AttributeType::String, 'default' => '' ),
            'content'  => array( AttributeType::String, 'column' => ColumnType::Text ),
            'type'     => array( AttributeType::String, 'default' => '' ),
            'options'  => array( AttributeType::Mixed),
            'lastRun' => array( AttributeType::DateTime, 'default' => null),
        );
    }

    /**
     * @return array
     */
    public function defineRelations ()
    {
        return array();
    }
}