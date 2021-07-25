<?php
namespace SkyBlueSofa\Canvass\Support\Contract\Cron;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Wordpress;

abstract class CronJob extends BaseObject
{
    protected $recurrance = 'daily'; // hourly, daily, twicedaily

    public function __construct()
    {
        parent::__construct();

        $this->installCronJob();

        Wordpress::addAction($this->getActionHookName(), function (){
            $this->run();
        });
    }

    abstract protected function run();

    protected function installCronJob()
    {
        Wordpress::scheduleEventIfNotAlready(time(), $this->recurrance, $this->getActionHookName());
    }

    protected function getActionHookName()
    {
        $class = explode('\\', get_class($this));
        $class = array_pop($class);
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $class, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return 'di_'.implode('_', $ret);
    }
}
