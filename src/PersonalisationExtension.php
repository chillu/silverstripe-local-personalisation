<?php

namespace Symbiote\Personalisation;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use Symbiote\MultiValueField\Fields\MultiValueTextField;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

class PersonalisationExtension extends DataExtension
{
    private static $db = [
        'ShowTags' => 'Varchar',
        'HideTags' => 'Varchar',
        'ShowCount' => 'Int',
        'HideCount' => 'Int',
        'ShowTimeblock' => 'Varchar',
        'HideTimeblock' => 'Varchar',
        'ShowPreference' => 'Varchar',
        'InitState' => 'Varchar',
    ];

    private static $defaults = [
        'ShowCount' => 1,
        'HideCount' => 1,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $prefOpts = [
            'show' => 'Show',
            'hide' => 'Hide',
        ];

        $syntax = 'Use + to require all tags to match, and use ! to indicate matching if this tag is "not" present';

        $fields->addFieldsToTab('Root.Personalisation', [
            TextField::create('ShowTags', 'Display tags')->setRightTitle('Show if content is tagged with any of these values (comma separated). ' . $syntax),
            TextField::create('ShowCount', 'Show count')->setRightTitle('How many times the user has touched a tag for it to trigger show rules on item'),
            TextField::create('HideTags', 'Hide tags')->setRightTitle('Hide if content is tagged with these values (comma separated). ' . $syntax),
            TextField::create('HideCount', 'Hide count')->setRightTitle('How many times the user has touched a tag for it to trigger hide rules on item'),
            TextField::create('ShowTimeblock', 'Timeframe for show tags')->setRightTitle("Timeframe (ie -1 week) for which tags must have been created to 'show'"),
            TextField::create('HideTimeblock', 'Timeframe for hide tags')->setRightTitle("Timeframe (ie -1 week) for which tags must have been created to 'hide'"),
            DropdownField::create('ShowPreference', 'Display preference', $prefOpts)->setRightTitle('Preferred show/hide option if multiple tags match'),
            DropdownField::create('InitState', 'Initial display state', $prefOpts)->setEmptyString('Default')->setRightTitle('If this should be displayed in a specific state initially'),
        ]);
    }

    public function p13nAttributes()
    {

        /**
         * @var DataObject
         */
        $owner = $this->owner;
        $show = $owner->ShowTags;
        $hide = $owner->HideTags;

        if (!$show && !$hide) {
            return;
        }

        $attrs = [];
        $attrs[] = 'data-lp-prefer="' . $owner->ShowPreference . '"';

        if ($show) {
            $attrs[] = 'data-lp-show-times="' . Convert::raw2htmlatt($owner->ShowCount) . '"';
            $attrs[] = 'data-lp-show-tags="' . Convert::raw2htmlatt(str_replace(' ', '', $show)) . '"';
        }
        if ($hide) {
            $attrs[] = 'data-lp-hide-times="' . Convert::raw2htmlatt($owner->HideCount) . '"';
            $attrs[] = 'data-lp-hide-tags="' . Convert::raw2htmlatt(str_replace(' ', '', $hide)) . '"';
        }

        if ($owner->ShowTimeblock) {
            $attrs[] = 'data-lp-show-timeblock="' . strtotime($owner->ShowTimeblock) . '"';
        }

        if ($owner->HideTimeblock) {
            $attrs[] = 'data-lp-hide-timeblock="' . strtotime($owner->HideTimeblock) . '"';
        }

        return implode(" ", $attrs);
    }

    public function p13nClasses()
    {
        /**
         * @var DataObject
         */
        $owner = $this->owner;

        $show = $owner->ShowTags;
        $hide = $owner->HideTags;

        if (!$show && !$hide) {
            return;
        }

        $state = $owner->InitState;

        return 'lp-item' . ($owner->InitState ? ' lp-' . $owner->InitState : '');
    }
}
