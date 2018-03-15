<?php
/**
 * Created by ProvidentCRM.
 * @author: Alexandre Tavares <alexandre@providentcrm.com>
 * Date: 15/03/2018
 * Time: 12:07
 */

require_once 'lib/Task.php';

$createAT = DateTime::createFromFormat(DATETIME_FORMAT, '2017-07-24 11:56:53');

$task = new Task('01:15', $createAT);

$result = $task->getStatus(DateTime::createFromFormat(DATETIME_FORMAT, '2017-07-24 12:56:53'));

echo var_dump($task);