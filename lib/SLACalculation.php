<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 22/02/2017
 * Time: 13:49
 */

require_once "lib/UKBankHoliday.php";


class SLACalculation
{
    const DEFAULT_TIMEZONE = 'Europe/Dublin';
    public $startBusinessHours;
    public $startBusinessMinutes;
    public $finishBusinessHours;
    public $finishBusinessMinutes;
    public $workSaturday;
    public $workSunday;
    public $timezone;
    public $workbankHoliday;


    function __construct($timezone = 'Europe/Dublin', $startBusinessHours = "09", $startBusinessMinutes = "00", $finishBusinessHours = "17" , $finishBusinessMinutes = "30" , $workSaturday = false , $workSunday = false, $workbankHoliday = false )
    {
        $this->startBusinessHours = $startBusinessHours;
        $this->startBusinessMinutes = $startBusinessMinutes;
        $this->finishBusinessHours =  $finishBusinessHours;
        $this->finishBusinessMinutes = $finishBusinessMinutes;
        $this->workSaturday = $workSaturday;
        $this->workSunday = $workSunday;
        $this->workbankHoliday = $workbankHoliday;
        $this->setTimezone($timezone);

    }

    function setTimezone($timezone){
        if($this->validateTimeZone($timezone)){
            $this->timezone = $timezone;
            date_default_timezone_set($timezone);
        }else{
            $this->timezone = $this::DEFAULT_TIMEZONE;
            date_default_timezone_set($this::DEFAULT_TIMEZONE);
        }
    }

    function validateTimeZone($timezone){
        return in_array($timezone, DateTimeZone::listIdentifiers());
    }

    function is_business_time($hour){

        $start = strtotime($this->startBusinessHours . ':' . $this->startBusinessMinutes);
        $end = strtotime($this->finishBusinessHours . ':' . $this->finishBusinessMinutes);

        if (($hour > $start) and ($hour <= $end) ){
            return true;
        }
        return false;
    }

    function is_valid($date){

        $weekDay = date('w', strtotime($date->format('Y-m-d H:i:s')));
        $hour = strtotime($date->format('H:i'));

        if(!$this->workbankHoliday){
            $year = $date->format('Y');
            $holidays = (new UKBankHoliday($year))->all();
            if(in_array($date->format('Y-m-d'),$holidays)){
                return false;
            }
        }
        if (($weekDay == 0 && !$this->workSunday ) or ($weekDay == 6 && !$this->workSaturday)){
            return false;
        }

        return $this->is_business_time($hour);
    }

    function ajust_start_time($date){
        $hour = intval($date->format('H'));
        $minutes = intval($date->format('i'));

        $date->format('Y-m-d H:i:s');
        $weekDay = date('w', strtotime($date->format('Y-m-d H:i:s')));
        if ($weekDay == 0 or $weekDay == 6){
            return $date->setTime(intval($this->startBusinessHours) -1, intval($this->startBusinessMinutes));
        }
        if ($hour < $this->startBusinessHours){
            return $date->setTime(intval($this->startBusinessHours) -1, intval($this->startBusinessMinutes));
        }
        if ($hour > $this->finishBusinessHours) {
            return $date->setTime(intval($this->finishBusinessHours) , intval($this->finishBusinessMinutes));
        }

        return $date;

    }

    function get_sla_overdue($date,$hours){

        if (strpos($hours, ':') !== false) {
            return $this->get_sla_overdue_by_hour($date,$hours);
        }

        $x = 0;
        if(!$this->is_valid($date)){
            $date = $this->ajust_start_time($date);
        }
        while($hours > $x){
            $date->modify('+ 1 hour');

            if ($this->is_valid($date)){
                $hours--;
            }
        }
        return  $date;
    }

    function getBusinessTimeLeft($date){

        if($this->is_valid($date)){
            $date->format('Y-m-d H:i:s');
            $business =  clone $date;
            $business->setTime($this->finishBusinessHours, $this->finishBusinessMinutes);
            $business->format('Y-m-d H:i:s');

            return 60 - ($this->getDiffInMinutes($date, $business) + intval($date->format('i')));
        }
        return 0;

    }

    function get_sla_overdue_by_hour($date,$time){
        $times= explode(":", $time);
        $hours = intval($times[0]);
        $minutes = intval( $times[1] ) + ($hours * 60);
        $dueDate = clone $date;
        $x = 0;
        if(!$this->is_valid($dueDate)){
            $dueDate = $this->ajust_start_time($dueDate);
        }

        while($minutes > $x){
            $dueDate->modify('+ 1 minute');

            if ($this->is_valid($dueDate)){
                $minutes--;
            }
        }

        return  $dueDate;
    }

    function getDiffInMinutes($from,$to){

        $since_start = $from->diff($to);
        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;
        return intval($since_start->format("%R$minutes"));

    }

    function holiday($year){
        return (new UKBankHoliday($year))->all();
    }

    function getTimeLeftWithBusinessHours($now_db, $dueDate){
        $hours = 0;
        $minutes = 0;
        $now = clone $now_db;

        if ($now == $dueDate){
            return str_pad($hours,  2, "0") . ":" . str_pad($minutes,  2, "0");
        }

        $minutes = $this->getDiffInMinutes($now, $dueDate);

        $x = 0;
        $diffMinutes = 0;

        while($minutes > $x){
            $now->modify('+ 1 minute');

            if ($this->is_valid($now)){
                $diffMinutes++;
            }
            $minutes--;
        }
        return $this->convertToHoursMins($diffMinutes, '%02d:%02d');

    }

    function convertToHoursMins($time, $format = '%02d:%02d') {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    public function duration($now, $createAt){
        if($this->is_valid($now)) {
            $minutes = $this->getDiffInMinutes($now, $createAt);
            if ($minutes != -1) {
                return $this->convertToHoursMins($minutes, '%02d:%02d');
            }
        }
    }

    public function timeLeft($duration, $serviceLevel){
        $pr_sla_time_left = $this->getTimeDiff($duration, $serviceLevel);

        return $this->convertToHoursMins($pr_sla_time_left, '%02d:%02d');
    }

    function getTimeDiff($dtime,$atime)
    {
        if(!empty($dtime) && !empty($atime)) {
            $nextDay = $dtime > $atime ? 1 : 0;
            $dep = explode(':', $dtime);
            $arr = explode(':', $atime);
            $diff = abs(mktime($dep[0], $dep[1], 0, date('n'), date('j'), date('y')) - mktime($arr[0], $arr[1], 0,
                    date('n'), date('j') + $nextDay, date('y')));
            $hours = floor($diff / (60 * 60));
            $mins = floor(($diff - ($hours * 60 * 60)) / (60));
            $secs = floor(($diff - (($hours * 60 * 60) + ($mins * 60))));
            if (strlen($hours) < 2) {
                $hours = "0" . $hours;
            }
            if (strlen($mins) < 2) {
                $mins = "0" . $mins;
            }
            if (strlen($secs) < 2) {
                $secs = "0" . $secs;
            }
            return ($hours * 60) + $mins;
        }
    }

}