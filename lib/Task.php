<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 21/07/2017
 * Time: 15:57
 */

require_once "SLACalculation.php";
date_default_timezone_set(SLACalculation::DEFAULT_TIMEZONE);

class Task
{
    public $createAt;
    public $modifiedAt;
    public $dueDate;
    public $timeLeft;
    public $duration;
    public $serviceLevel;
    public $locked;
    public $lockedAT;
    public $unlockedAT;
    public $lockedLog;
    public $businessDayStartHour;
    public $businessDayStartMinute;
    public $businessDayEndHour;
    public $businessDayEndMinute;
    private $sla;


    function __construct($serviceLevel = "02:00", $createAt, $startAt = "09:00", $endAt = "17:00")
    {
        $this->setBusinessTime($startAt,$endAt);
        $this->sla = new SLACalculation(  'Europe/Dublin',
                                    $this->businessDayStartHour,
                                    $this->businessDayStartMinute,
                                    $this->businessDayEndHour,
                                    $this->businessDayEndMinute);
        $this->createAt = $createAt;
        $this->lockedLog = array();
        $this->unlockedAT = array();
        $this->serviceLevel = $serviceLevel;
        $this->dueDate = $this->sla->get_sla_overdue_by_hour($createAt, $serviceLevel);
    }

    public function setBusinessTime($startAt,$endAt){

        $arrayBusinessStart =  $this->splitTime($startAt);
        $arrayBusinessEnd =  $this->splitTime($endAt);
        $this->businessDayStartHour = $arrayBusinessStart[0];
        $this->businessDayStartMinute = $arrayBusinessStart[1];
        $this->businessDayEndHour = $arrayBusinessEnd[0];
        $this->businessDayEndMinute = $arrayBusinessEnd[1];

    }
    public function lockTime($lockeAT = null, $now = null){
        $this->locked = true;
        $this->lockedAT = $lockeAT == null ? new DateTime() : $lockeAT;
        $this->duration = $this->sla->duration($now, $this->createAt);
        $this->timeLeft = $this->sla->timeLeft($this->duration, $this->serviceLevel);
        array_push($this->lockedLog, array( "user" => "user1", "created_at" => date_format(new DateTime(), DATE_FORMAT)));
    }

    public function unlockTime($lockeAT = null){
        $this->locked = false;

        array_push($this->unlockedAT, array( "user" => "user1", "created_at" => date_format($lockeAT, DATE_FORMAT)));

        if ($this->timeLeft != null){
            $this->dueDate = $this->sla->get_sla_overdue_by_hour($lockeAT,$this->timeLeft);
        }else{
            $this->dueDate = $this->sla->get_sla_overdue_by_hour($lockeAT,$this->serviceLevel);
        }

    }

    public function getValues() {
        echo var_dump(get_object_vars($this));
    }

    private function splitTime($hours){
        return explode(":", $hours);
    }
}