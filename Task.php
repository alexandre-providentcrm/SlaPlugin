<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 21/07/2017
 * Time: 15:57
 */

define("DATE_FORMAT", "Y-m-d H:i:s");
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
    public $businessDayEndMinute = "00";
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
    public function getValues() {
        echo var_dump(get_object_vars($this));
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
        $this->duration($now);
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
    private function splitTime($hours){
        return explode(":", $hours);
    }


    public function duration($now){
        if($this->sla->is_valid($now)) {
            $minutes = $this->sla->getDiffInMinutes($now, $this->createAt);
            if ($minutes != -1) {
                $this->duration = $this->sla->convertToHoursMins($minutes, '%02d:%02d');

                $pr_sla_time_left = $this->getTimeDiff($this->duration, $this->serviceLevel);

                $this->timeLeft = $this->sla->convertToHoursMins($pr_sla_time_left, '%02d:%02d');
            }
        }
    }

    function getTimeDiff($dtime,$atime)
    {
        $nextDay = $dtime>$atime?1:0;
        $dep = explode(':',$dtime);
        $arr = explode(':',$atime);
        $diff = abs(mktime($dep[0],$dep[1],0,date('n'),date('j'),date('y'))-mktime($arr[0],$arr[1],0,date('n'),date('j')+$nextDay,date('y')));
        $hours = floor($diff/(60*60));
        $mins = floor(($diff-($hours*60*60))/(60));
        $secs = floor(($diff-(($hours*60*60)+($mins*60))));
        if(strlen($hours)<2){$hours="0".$hours;}
        if(strlen($mins)<2){$mins="0".$mins;}
        if(strlen($secs)<2){$secs="0".$secs;}
        return ($hours *60 ) + $mins;
    }

}


/*
$createAT = DateTime::createFromFormat(DATE_FORMAT, '2017-07-21  12:30:00');

$task = new Task("00:03", $createAT,"13:00", "17:30");

$lockDate = new DateTime('2017-07-21  12:40:00');

$task->lockTime($lockDate, $lockDate);

$lockDate = new DateTime('2017-07-21  13:03:00');
$task->unlockTime($lockDate);

$new_time = new DateTime("2017-07-21 12:05:00");

////

$createAT = DateTime::createFromFormat(DATE_FORMAT, '2017-07-21  09:00:00');

$date = new DateTime('2017-07-21  09:00:00');
$task = new Task("03:00", $createAT);

$lockDate = new DateTime('2017-07-21  09:30:00');

$task->lockTime($lockDate, $lockDate);

$lockDate = new DateTime('2017-07-21  09:35:00');
echo $task->unlockTime($lockDate);

$task->getValues();
*/

/*

$createAT = DateTime::createFromFormat(DATE_FORMAT, '2017-07-21  09:00:00');

$task = new Task("03:00", $createAT);

$lockDate = new DateTime('2017-07-21  09:30:00');

$task->lockTime($lockDate, $lockDate);

$lockDate = new DateTime('2017-07-21  09:35:00');
$task->unlockTime($lockDate);

$new_time = new DateTime("2017-07-21 12:05:00");

//$this->assertEquals($task->dueDate, $new_time);
$task->getValues();

*/

$hour = '09:00';

//Monday
$date = new DateTime("2017-05-01 19:45:00");
$sla = new SLACalculation();
$date = $sla->get_sla_overdue_by_hour($date, $hour );

//Tuesday
$new_time = new DateTime("2017-05-03 10:00:00");