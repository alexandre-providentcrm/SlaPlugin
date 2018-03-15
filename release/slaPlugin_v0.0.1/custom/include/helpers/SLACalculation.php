<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 22/02/2017
 * Time: 13:49
 */
define('DEFAULT_TIMEZONE', 'Europe/Dublin');

require_once "lib/UKBankHoliday.php";


class SLACalculation
{

    public $startBusinessHours;
    public $startBusinessMinutes;
    public $finishBusinessHours;
    public $finishBusinessMinutes;
    public $workSaturday;
    public $workSunday;
    public $timezone;
    public $workbankHoliday;


    function __construct($timezone = DEFAULT_TIMEZONE, $startBusinessHours = "08", $startBusinessMinutes = "00", $finishBusinessHours = "16" , $finishBusinessMinutes = "00" , $workSaturday = false , $workSunday = false, $workbankHoliday = false )
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
            $this->timezone = DEFAULT_TIMEZONE;
            date_default_timezone_set(DEFAULT_TIMEZONE);
        }
    }
    function validateTimeZone($timezone){
        return in_array($timezone, DateTimeZone::listIdentifiers());
    }

    function is_business_time($hour){

        $start = strtotime($this->startBusinessHours . ':' . $this->startBusinessMinutes);
        $end = strtotime($this->finishBusinessHours . ':' . $this->finishBusinessMinutes);

        if (($hour > $start) and ($hour < $end) ){
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
        $hour = strtotime($date->format('H:i'));
        $start = strtotime($this->startBusinessHours . ':' . $this->startBusinessMinutes);
        $end = strtotime($this->finishBusinessHours . ':' . $this->finishBusinessMinutes);
        $weekDay = date('w', strtotime($date->format('Y-m-d H:i:s')));
        if ($weekDay == 0 or $weekDay == 6){
            return $date->setTime(intval($this->startBusinessHours), intval($this->startBusinessMinutes));
        }
        if ($hour < $start){
            return $date->setTime(intval($this->startBusinessHours), intval($this->startBusinessMinutes));
        }
        if ($hour > $end) {
            return $date->setTime(intval($this->finishBusinessHours), intval($this->finishBusinessMinutes));
        }

        return $date;

    }

    function get_sla_overdue_by_hour($date,$time){
        $times= explode(":", $time);
        $hours = intval($times[0]);
        $minutes = intval( $times[1] );

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
        while($minutes > $x){
            $date->modify('+ 1 minute');

            if ($this->is_valid($date)){
                $minutes--;
            }
        }
        return  $date;
    }

    function getStatusColor($minutes){
        global $sugar_config;

        $amber = $sugar_config['provident']['SLA']['colour']['amber'];
        $red = $sugar_config['provident']['SLA']['colour']['red'];
        echo $minutes;
        if (($minutes <= $amber ) and ($minutes > $red ))
        {
            return "Amber";
        }
        if ($minutes <= 30 ){
            return "Red";
        }
        return "Green";

    }
    
    function getDiffTime($from, $to, $format){

        $diff = $from->diff($to);

        return intval($diff->format($format));
    }

    function holiday($year){
        return (new UKBankHoliday($year))->all();
    }

}