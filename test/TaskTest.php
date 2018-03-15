<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 24/07/2017
 * Time: 00:26
 */

class TaskTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require "lib/Task.php";
    }

    function setUp()
    {

    }
    function testSLABasicDueDate(){

        $createAT = DateTime::createFromFormat(DATETIME_FORMAT, '2017-07-21  09:00:00');

        $task = new Task("03:00", $createAT,"09:00", "17:30");

        $dueDate = new DateTime('2017-07-21  12:00:00');
        $this->assertEquals($task->dueDate, $dueDate);
    }

    function testSLALockDueDate(){

        $createAT = DateTime::createFromFormat(DATETIME_FORMAT, '2017-07-21  09:00:00');

        $task = new Task("03:00", $createAT);

        $lockDate = new DateTime('2017-07-21  09:30:00');

        $task->lockTime($lockDate, $lockDate);

        $lockDate = new DateTime('2017-07-21  09:35:00');
        $task->unlockTime($lockDate);

        $new_time = new DateTime("2017-07-21 12:35:00");

        $this->assertEquals($task->dueDate, $new_time);

    }



    function testSLALockBeforeBusinessHours(){
        $createAT = DateTime::createFromFormat(DATETIME_FORMAT, '2017-07-21  12:30:00');

        $task = new Task("00:03", $createAT,"13:00", "17:30");

        $lockDate = new DateTime('2017-07-21  12:40:00');

        $task->lockTime($lockDate, $lockDate);

        $lockDate = new DateTime('2017-07-21  13:03:00');
        $task->unlockTime($lockDate);

        $new_time = new DateTime("2017-07-21 13:06:00");

        $this->assertEquals($task->dueDate, $new_time);
    }
    function testHoursMoorepay(){
        $createAT = DateTime::createFromFormat(DATETIME_FORMAT, '2017-07-24 11:56:53');

        $task = new Task("08:31", $createAT,"08:00", "17:30");

        $new_time = new DateTime("2017-07-25 10:57:53");

        $this->assertEquals($task->dueDate, $new_time);

    }
}