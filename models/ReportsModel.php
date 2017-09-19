<?php
/**
 * Reports plugin for Craft CMS
 *
 * Reports Model
 *
 * @author    Superbig
 * @copyright Copyright (c) 2017 Superbig
 * @link      https://superbig.co
 * @package   Reports
 * @since     1.0.0
 */

namespace Craft;

class ReportsModel extends BaseModel
{
    /**
     * @return array
     */
    protected function defineAttributes ()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'       => array( AttributeType::Number, 'default' => null ),
            'name'     => array( AttributeType::String, 'default' => '' ),
            'content'  => array( AttributeType::String, 'default' => '' ),
            'type'  => array( AttributeType::String, 'default' => 'manual' ),
            'options'  => array( AttributeType::Mixed, 'default' => null),
            'lastRun' => array( AttributeType::DateTime),
        ));
    }

}