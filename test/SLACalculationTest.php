<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 22/02/2017
 * Time: 13:50
 */


class SLACalculationTest extends PHPUnit_Framework_TestCase
{
    protected $sla;

    public static function setUpBeforeClass()
    {
        require "lib/SLACalculation.php";
    }

    function setUp()
    {
        $this->sla = new SLACalculation('Europe/Dublin','08','00','16','00');
    }

    function testSetWrongTimezone(){
        $sla = new SLACalculation('xpto' );

        $this->assertEquals($sla->timezone, 'Europe/Dublin');
    }

    function testSetTimezone(){
        $sla = new SLACalculation('Europe/Lisbon' );

        $this->assertEquals($sla->timezone, 'Europe/Lisbon');
    }

    function testDifferentStartBusinessHour(){
        $sla = new SLACalculation();
        $sla->startBusinessHours = '07';

        $this->assertEquals($sla->startBusinessHours, '07');
    }

    function testMondayAddTwoHoursInsideBusinessHours(){

        $hour = '02:00';

        $date = new DateTime("2017-01-02 10:00:00");

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //2 January	Monday	New Year’s Day (UK)
        $expected_datetime = new DateTime("2017-01-03 10:00:00");

        $this->assertEquals($date, $expected_datetime);
    }

    function testSundayAddTwoHours(){

        $hour = '02:00';

        $date = new DateTime("2017-01-01 10:00:00");

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );
        //2 January	Monday	New Year’s Day (UK)
        $new_time = new DateTime("2017-01-03 10:00:00");

        $this->assertEquals($date, $new_time);
    }

    function testFridayAddTwoHours(){
        $hour = '02:00';

        $date = new DateTime("2017-01-02 10:00:00");

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //2 January	Monday	New Year’s Day (UK)
        $new_time = new DateTime("2017-01-03 10:00:00");

        $this->assertEquals($date, $new_time);
    }

    function testNotWorkOnSaturday(){

        $hour = '02:00';
        //Saturday
        $date = new DateTime("2016-12-31 10:00:00");

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //Monday - 10:00
        $new_time = new DateTime("2017-01-03 10:00:00");

        $this->assertEquals($date, $new_time);

    }

    function testWorkingOnSaturday(){

        $hour = '02:00';
        //Saturday
        $date = new DateTime("2016-12-31 10:00:00");

        $this->sla->workSaturday = true;

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //Monday - 10:00
        $new_time = new DateTime("2016-12-31 12:00:00");

        $this->assertEquals($date, $new_time);

    }
    
    function testDontWorkBankHolidaysUK(){

        $hour = '02:00';

        //Monday
        $date = new DateTime("2017-05-01 10:00:00");

        $sla = new SLACalculation('','08','00','16','00',false,false,false);

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //Tuesday
        $new_time = new DateTime("2017-05-2 10:00:00");

        $this->assertEquals($date, $new_time);

    }

    function testWorkBankHolidaysUK(){

        $hour = '02:00';

        //Monday
        $date = new DateTime("2017-05-01 10:00:00");

        $sla = new SLACalculation('','08','00','16','00',false,false,true);

        $date = $sla->get_sla_overdue_by_hour($date, $hour );

        //Tuesday
        $new_time = new DateTime("2017-05-01 12:00:00");

        $this->assertEquals($date, $new_time);

    }

    function testSLAAfterWorkAddNineHoursTest()
    {

        $hour = '09:00';

        //Monday
        $date = new DateTime("2017-05-01 19:45:00");

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //Tuesday
        $new_time = new DateTime("2017-05-03 09:00:00");

        $this->assertEquals($date, $new_time);

    }

    function testSLAwith45Minutes()
    {

        $hour = '01:00';
        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        //Monday
        $date = new DateTime("2017-07-10 17:15:00");

        $date = $this->sla->get_sla_overdue_by_hour($date, $hour );

        //Tuesday
        $new_time = new DateTime("2017-07-11 09:45:00");

        $this->assertEquals($date, $new_time);

    }

    function testSLATimeLeftSameTime(){

        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        //Tuesday
        $new_time = new DateTime("2017-07-10 11:15:00");

        $now = new DateTime("2017-07-10 11:15:00");

        $timeLeft =  $this->sla->getTimeLeftWithBusinessHours($now, $new_time);

        $this->assertEquals($timeLeft, '00:00');

    }

    function testSLATimeLeftOneHourDiffSameDay(){

        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        //Tuesday
        $new_time = new DateTime("2017-07-10 12:15:00");

        $now = new DateTime("2017-07-10 11:15:00");

        $timeLeft =  $this->sla->getTimeLeftWithBusinessHours($now, $new_time);

        $this->assertEquals($timeLeft, '01:00');

    }

    function testSLATimeLeftFourHourDiffSameDay()
    {

        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        //Tuesday
        $new_time = new DateTime("2017-07-10 14:15:00");

        $now = new DateTime("2017-07-10 10:15:00");

        $timeLeft =  $this->sla->getTimeLeftWithBusinessHours($now, $new_time);

        $this->assertEquals($timeLeft, '04:00');

    }

    function testSLATimeLeftFourHourDiffSameDayWithMinutes()
    {

        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        //Tuesday
        $new_time = new DateTime("2017-07-10 14:00:00");

        $now = new DateTime("2017-07-10 10:15:00");

        $timeLeft =  $this->sla->getTimeLeftWithBusinessHours($now, $new_time);

        $this->assertEquals($timeLeft, '03:45');

    }

    function testSLATimeLeftNextDayHours()
    {

        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        $now = new DateTime("2017-07-10 17:00:00");

        $dueDate = new DateTime("2017-07-11 10:00:00");

        $timeLeft =  $this->sla->getTimeLeftWithBusinessHours($now, $dueDate);

        $this->assertEquals($timeLeft, '01:30');

    }

    function testSLATimeLeftNextDayHoursWithHoursAndMinutes()
    {

        $this->sla = new SLACalculation('Europe/Dublin','09','00','17','30');

        $now = new DateTime("2017-07-10 17:15:00");

        $dueDate = new DateTime("2017-07-11 11:05:00");

        $timeLeft =  $this->sla->getTimeLeftWithBusinessHours($now, $dueDate);

        $this->assertEquals($timeLeft, '02:20');

    }

}