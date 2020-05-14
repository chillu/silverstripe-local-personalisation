<?php


namespace Symbiote\Personalisation;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\DataObject;
use Symbiote\MultiValueField\Fields\MultiValueTextField;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

class ProfileRule extends DataObject
{
    private static $table_name = 'ProfileRule';

    private static $db = [
        'Title' => 'Varchar(128)',
        'Selector' => 'Varchar(128)',
        'AppliesTo' => "Enum('content,url,useragent,click,referrer')",
        // "ExtractFrom" => "Enum('content,url,useragent,referrer')",
        'Target' => 'Varchar(255)',
        'Regex' => 'Varchar(2000)',
        'Attribute' => 'Varchar(255)',
        // 'Timeframe' => 'Varchar(128)',
        'EventData' => 'Varchar(255)',
        'Timeblock' => 'Varchar(128)',
        'TimeOnPage' => 'Int',
        'Apply' => MultiValueField::class,
    ];

    private static $applies_to = [
        'content' => 'Page content',
        'url' => 'Current page URL',
        'useragent' => 'Browser user agent',
        'click' => 'A user click event',
    ];

    public function getCMSFields()
    {
        $eventDoc = '<p>For click events, the $0 and $1 attributes are set to the href attribute and innerHTML of the link.</p>';
        $eventDoc .= '<p>However, if you set a selector or regex above, the rules around those are used for populating attributes for tags</p> ';

        $fields = FieldList::create([
            TextField::create('Title', 'Rule name'),
            DropdownField::create('AppliesTo', 'Applies to', self::config()->applies_to),
            MultiValueTextField::create('Apply', 'Tags to apply')
                ->setRightTitle('Use $0, $1 etc for parameter replacements'),
            $selectorFields = ToggleCompositeField::create('selector_rules', 'CSS Based', [
                TextField::create('Selector', 'CSS Selector'),
                TextField::create('Attribute', 'Element attribute extracted')
                    ->setRightTitle("Use #content for innerHTML"),
            ]),
            $regexFields = ToggleCompositeField::create('regex_fields', 'Regex options', [
                TextField::create('Regex', 'Regex for content')
            ]),
            $eventFields = ToggleCompositeField::create('event_fields', 'Event options', [
                LiteralField::create('event_doc', $eventDoc),
                TextField::create('Target', 'CSS selector to bind events to'),
            ]),
            $timeFields = ToggleCompositeField::create('time_fields', 'Time options', [
                DropdownField::create('TimeOnPage', 'Time on page', ['0' => '0', '3' => '3', '10' => '10', '30' => '30', '120' => '120'])
                    ->setRightTitle("User must be on page at least this many seconds before tagging")
                // TextField::create('Timeblock', 'Timeblock')
                //     ->setRightTitle('(NOT IMPLEMENTED) ie 8:00-10:30 to indicate that this is only triggered during this window of the day')
            ])
        ]);

        $selectorFields->setStartClosed($this->AppliesTo != 'content' || strlen($this->Selector) === 0);
        $regexFields->setStartClosed(strlen($this->Regex) === 0);
        $eventFields->setStartClosed(strlen($this->Target) === 0);
        $timeFields->setStartClosed(!$this->TimeOnPage);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function toJson()
    {
        return [
            'name' => $this->Title,
            'appliesTo' => $this->AppliesTo,
            'regex' => $this->Regex,
            'selector' => $this->Selector,
            'attrbiute' => $this->Attribute,
            'apply' => $this->Apply->getValues(),
        ];
    }
}
