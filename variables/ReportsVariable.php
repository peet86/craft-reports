<?php
/**
 * Reports plugin for Craft CMS
 *
 * Reports Task
 *
 * @author    Superbig
 * @copyright Copyright (c) 2017 Superbig
 * @link      https://superbig.co
 * @package   Reports
 * @since     1.0.0
 */

namespace Craft;

class ReportsVariable
{
    public function prepare ($options = [ ])
    {
        $defaultOptions = [
            'type'    => 'table',
            'columns' => [ ],
            'rows'    => [ ],
            'labels'  => [ ],
            'data'    => [ ],
        ];
        $data           = array_merge($defaultOptions, $options);
        $data['rows'] = $this->safelyConvert($data['rows']);

        $json = json_encode($data);

        return TemplateHelper::getRaw('%report%' . $json . '%endreport%');
    }

    public function db ()
    {
        return craft()->db->createCommand();
    }

    private function safelyConvert ($rows = [ ])
    {
        $safeTypes = [ 'integer', 'double', 'string', 'NULL' ];

        $newRows = [ ];

        foreach ($rows as $row) {
            $newRow = [ ];

            foreach ($row as $cell) {
                if ( !in_array(gettype($cell), $safeTypes) ) {
                    if ($cell instanceof \Twig_Markup) {
                        $newRow[] = (string) $cell;
                    } else {
                        $newRow[] = $cell;
                    }
                } else {
                    $newRow[] = $cell;
                }
            }

            $newRows[] = $newRow;
        }

        return $newRows;
    }


    public function getReportTypes()
    {
        //$oldPath = craft()->templates->getTemplatesPath();
        //craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

        $types = IOHelper::getFolders(craft()->path->getSiteTemplatesPath().'reports/');
$types = 'bob';
        print_r($types);

        //craft()->templates->setTemplatesPath($oldPath);

        return $types;
    }

    public function forms($macro, array $args)
    {
        // Get the current template path
        $originalPath = craft()->path->getTemplatesPath();
        
        // Point Twig at the CP templates
        craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());

        // Render the macro.
        $html = craft()->templates->renderMacro('_includes/forms', $macro, array($args));

        // Restore the original template path
        craft()->path->setTemplatesPath($originalPath);

        return TemplateHelper::getRaw($html);
    }

    public function userGroups()
    {
        return craft()->userGroups->allGroups;
    }
}