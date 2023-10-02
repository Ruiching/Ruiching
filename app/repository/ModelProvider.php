<?php

namespace app\repository;

use app\model\Event;
use app\model\EventEvolveTheme;
use app\model\EventField;
use app\model\EventQuality;
use app\model\EventReference;
use app\model\EventRelation;
use app\model\EventRelationReference;
use app\model\EventSubject;
use app\model\EventTag;
use app\model\Field;
use app\model\FormatedLocation;
use app\model\Literature;
use app\model\LiteratureTextualContent;
use app\model\RawEvent;
use app\model\Reference;
use app\model\Subject;
use app\model\Tag;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ModelProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['eventModel'] = function ($pimple) {
            return new Event();
        };
        $pimple['eventEvolveThemeModel'] = function ($pimple) {
            return new EventEvolveTheme();
        };
        $pimple['eventFieldModel'] = function ($pimple) {
            return new EventField();
        };
        $pimple['eventQualityModel'] = function ($pimple) {
            return new EventQuality();
        };
        $pimple['eventReferenceModel'] = function ($pimple) {
            return new EventReference();
        };
        $pimple['eventRelationModel'] = function ($pimple) {
            return new EventRelation();
        };
        $pimple['eventRelationReferenceModel'] = function ($pimple) {
            return new EventRelationReference();
        };
        $pimple['eventSubjectModel'] = function ($pimple) {
            return new EventSubject();
        };
        $pimple['eventTagModel'] = function ($pimple) {
            return new EventTag();
        };
        $pimple['fieldModel'] = function ($pimple) {
            return new Field();
        };
        $pimple['formatedLocationModel'] = function ($pimple) {
            return new FormatedLocation();
        };
        $pimple['literatureModel'] = function ($pimple) {
            return new Literature();
        };
        $pimple['literatureTextualContentModel'] = function ($pimple) {
            return new LiteratureTextualContent();
        };
        $pimple['rawEventModel'] = function ($pimple) {
            return new RawEvent();
        };
        $pimple['referenceModel'] = function ($pimple) {
            return new Reference();
        };
        $pimple['subjectModel'] = function ($pimple) {
            return new Subject();
        };
        $pimple['tagModel'] = function ($pimple) {
            return new Tag();
        };
    }
}