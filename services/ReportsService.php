<?php
/**
 * Reports plugin for Craft CMS
 *
 * Reports Service
 *
 * @author    Superbig
 * @copyright Copyright (c) 2017 Superbig
 * @link      https://superbig.co
 * @package   Reports
 * @since     1.0.0
 */

namespace Craft;

use League\Csv\Writer;

abstract class Json
{
    public static function getLastError ($asString = false)
    {
        $lastError = \json_last_error();

        if ( !$asString ) return $lastError;

        // Define the errors.
        $constants    = \get_defined_constants(true);
        $errorStrings = array();

        foreach ($constants["json"] as $name => $value)
            if ( !strncmp($name, "JSON_ERROR_", 11) )
                $errorStrings[ $value ] = $name;

        return isset($errorStrings[ $lastError ]) ? $errorStrings[ $lastError ] : false;
    }

    public static function getLastErrorMessage ()
    {
        return \json_last_error_msg();
    }

    public static function hasError ()
    {
        return json_last_error();
    }

    public static function clean ($jsonString)
    {
        if ( !is_string($jsonString) || !$jsonString ) return '';

        // Remove unsupported characters
        // Check http://www.php.net/chr for details
        for ($i = 0; $i <= 31; ++$i)
            $jsonString = str_replace(chr($i), "", $jsonString);

        $jsonString = str_replace(chr(127), "", $jsonString);

        // Remove the BOM (Byte Order Mark)
        // It's the most common that some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
        // Here we detect it and we remove it, basically it's the first 3 characters.
        if ( 0 === strpos(bin2hex($jsonString), 'efbbbf') ) $jsonString = substr($jsonString, 3);

        return $jsonString;
    }

    public static function encode ($value, $options = 0, $depth = 512)
    {
        return \json_encode($value, $options, $depth);
    }

    public static function decode ($jsonString, $asArray = true, $depth = 512, $options = JSON_BIGINT_AS_STRING)
    {
        if ( !is_string($jsonString) || !$jsonString ) return null;

        $result = \json_decode($jsonString, $asArray, $depth, $options);

        if ( $result === null )
            switch (self::getLastError()) {
                case JSON_ERROR_SYNTAX :
                    // Try to clean json string if syntax error occured
                    $jsonString = self::clean($jsonString);
                    $result     = \json_decode($jsonString, $asArray, $depth, $options);
                    break;

                default:
                    // Unsupported error
            }

        return $result;
    }
}

class ReportsService extends BaseApplicationComponent
{
    private $types;
    
    public function getReportById ($id = null)
    {
        if ( $id ) {
            $record = ReportsRecord::model()->findByPk($id);

            if ( !$record ) {
                throw new Exception(sprintf('Report with id %s was not found', $id));
            }

            return ReportsModel::populateModel($record->getAttributes());
        }
    }

    public function getAllReports ()
    {
        $records = ReportsRecord::model()->findAll();

        return ReportsModel::populateModels($records);
    }

    public function run ($id = null)
    {
        $report           = $this->getReportById($id);
        //$report->runCount = $report->runCount + 1;
        $report->lastRun = new DateTime();
        $this->saveReport($report);

        return $this->parseReport($report);
    }

    public function exportCsv ($id = null)
    {
        $report           = $this->getReportById($id);
        $report->lastRun = new DateTime();
        $this->saveReport($report);

        $data = $this->parseReport($report);

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        if ( isset($data['columns']) ) {
            $csv->insertOne($data['columns']);
        }

        foreach ($data['rows'] as $row) {
            $csv->insertOne($row);
        }

        $csv->output(StringHelper::toKebabCase($report->name) . '.csv');
    }

    public function parseReport (ReportsModel $report)
    {
        if($report->type == 'manual'){
            $result  = craft()->templates->renderString($report->content);
        }else{
            $result  = craft()->templates->renderString($this->getReportTemplate($report));
        }
        $matches = [ ];

        if ( preg_match('/(%report%(.+)%endreport%)/um', $result, $matches) ) {
            $result         = $matches[2];
            $parsed         = Json::decode($result, true);
            $parsed['name'] = $report->name;
            $parsed['id']   = $report->id;

            return $parsed;
        }

        if ( Json::getLastError(true) !== 'JSON_ERROR_NONE' ) {
            throw new Exception('There was an issue with your twig code. Please check.');
        }

        return null;
    }

    /**
     */
    public function saveReport (ReportsModel &$report)
    {
        if ( $report->id ) {
            $record = ReportsRecord::model()->findByPk($report->id);
        }
        else {
            $record = new ReportsRecord();
        }

        if ( !$report->validate() ) {
            return false;
        }

        $record->name     = $report->name;
        $record->type   = $report->type;
        $record->content  = $report->content;
        $record->options = $report->options;
        $record->lastRun = $report->lastRun;

        if ( $record->save() ) {
            return true;
        }

        return false;
    }

    public function deleteReportById($id)
    {
        ReportsRecord::model()->deleteByPk($id);
    }

    private function removeBOM ($data)
    {
        if ( 0 === strpos(bin2hex($data), 'efbbbf') ) {
            return substr($data, 3);
        }

        return $data;
    }


    public function getTypes()
    {
        if(!$this->types){

            $registered = craft()->plugins->call('registerReports');

            foreach($registered as $plugin => $report){
                foreach($report as $key => $value){
                    $this->types[$key] = ['path'=>$value, 'plugin'=>$plugin];
                }
            }

        }

        return $this->types;
    }

    public function getOptions($type)
    {
        if($type == 'manual'){
            return null;
        }
        
        $types = $this->getTypes();
        $type = $types[$type];

        $oldPath = craft()->path->getTemplatesPath();
        $newPath = craft()->path->getPluginsPath().$type['plugin'].'/templates';
        craft()->path->setTemplatesPath($newPath);
        $options = craft()->templates->render($type['path'].'/settings');
        craft()->path->setTemplatesPath($oldPath);

        return $options;
    }

    public function getReportTemplate($report)
    {
        $types = $this->getTypes();
        $type = $types[$report->type];

        $oldPath = craft()->path->getTemplatesPath();
        $newPath = craft()->path->getPluginsPath().$type['plugin'].'/templates';
        craft()->path->setTemplatesPath($newPath);
        $results = craft()->templates->render($type['path'].'/results', [
            'options' => $report->options,
        ]);
        craft()->path->setTemplatesPath($oldPath);

        return $results;
    }
}