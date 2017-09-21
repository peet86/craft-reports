# Reports plugin for Craft CMS

Write reports with Twig.

![Screenshot](resources/icon.png)

## Installation

To install Reports, follow these steps:

1. Download & unzip the file and place the `reports` directory into your `craft/plugins` directory
2. Install plugin in the Craft Control Panel under Settings > Plugins
3. The plugin folder should be named `reports` for Craft to see it.

Reports works on Craft 2.4.x and higher;

## Reports Overview

Reports allow you to collect data by writing Twig tags as normal. All you need to in the end is pass that data to the `.prepare()` method, like the following example:

Example report in Twig:

```twig
{% set newsCount = craft.entries.section('news').limit(null).total() %}
{{ craft.reports.prepare({
    columns: ['Total news entries'], 
    rows: [ [ newsCount ] ]
}) }}
```
List all users in a specific user group that have logged in within the last 30 days:
```twig
{% set loginPeriod = now|date_modify('-30 days') %}
{% set users = craft.users.group('clients').lastLoginDate('> ' ~ loginPeriod) %}
{% set userRows = [] %}
{% for user in users %}
    {% set userRows = userRows|merge([ [user.username, user.getName(), user.email] ]) %}
{% endfor %}

{{ craft.reports.prepare({
    columns: ['Username', 'Name', 'Email'], 
    rows: userRows
}) }}
```

The result is available in the control panel, or as a CSV export.


You can register report types from other plugins and use templates for both options and results:

```twig
public function registerReports()
{
    $types = [];
    $folders = IOHelper::getFolders(craft()->path->getPluginsPath().'tester/templates/reports/');
    
    foreach($folders as $folder){
        $name = IOHelper::getFolderName($folder, false);
        $types[$name] = 'reports/'.$name;
    }
    
    return $types;
}
```

Use the reports forms variable to access cp forms macros
```twig
{{ craft.reports.forms('dateTimeField', {
	id: 'startDate',
    label: "Start Date"|t,
    name: 'options[startDate]',
    value: options.startDate
}) }}
```
The options variable is now available in the results.twig 

folder structure:
templates
    reports
        reportType
            results.twig
            settings.twig

## Reports Roadmap

* Show error if there is something wrong with the template
* Add helpers for getting data from query results
* Make it possible to include templates from the `templates` directory
* Charts
* Permissions
* E-mail digests
* Slack digests

Brought to you by [Superbig](https://superbig.co)
