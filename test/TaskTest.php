<?php

/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 24/07/2017
 * Time: 00:26
 */

require_once 'Task.php';

class TaskTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {

    }
    function testSLABasicDueDate(){

        $createAT = DateTime::createFromFormat(DATE_FORMAT, '2017-07-21  09:00:00');

        $task = new Task("03:00", $createAT,"09:00", "17:30");

        $dueDate = new DateTime('2017-07-21  12:00:00');
        $this->assertEquals($task->dueDate, $dueDate);
    }

    function testSLALockDueDate(){

        $createAT = DateTime::createFromFormat(DATE_FORMAT, '2017-07-21  09:00:00');

        $task = new Task("03:00", $createAT);

        $lockDate = new DateTime('2017-07-21  09:30:00');

        $task->lockTime($lockDate, $lockDate);

        $lockDate = new DateTime('2017-07-21  09:35:00');
        $task->unlockTime($lockDate);

        $new_time = new DateTime("2017-07-21 12:05:00");

        $this->assertEquals($task->dueDate, $new_time);

    }



    function testSLALockBeforeBusinessHours(){
        $createAT = DateTime::createFromFormat(DATE_FORMAT, '2017-07-21  12:30:00');

        $task = new Task("00:03", $createAT,"13:00", "17:30");

        $lockDate = new DateTime('2017-07-21  12:40:00');

        $task->lockTime($lockDate, $lockDate);

        $lockDate = new DateTime('2017-07-21  13:03:00');
        $task->unlockTime($lockDate);

        $new_time = new DateTime("2017-07-21 13:06:00");

        $this->assertEquals($task->dueDate, $new_time);
    }
}