<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';


function gmailinfo_install() {
    $cron = cron::byClassAndFunction('gmailinfo', 'pull');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('gmailinfo');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('2-59/5 * * * *');
        $cron->save();
    }
}

function gmailinfo_update() {
    $cron = cron::byClassAndFunction('gmailinfo', 'pull');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('gmailinfo');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('2-59/5 * * * *');
        $cron->save();
    }
    else {
        $cron->setSchedule('2-59/5 * * * *');
        $cron->save();
    }
    $cron->stop();

    foreach (eqLogic::byType('gmailinfo') as $gmail) {
        foreach ($gmail->getCmd() as $cmd) {
            if($cmd->getConfiguration('data')=='unreadcount'){
                $cmd->setLogicalId('unreadcount');
                $cmd->setEventOnly(true);
                $cmd->save();
            }
        }
    }
}

function gmailinfo_remove() {
    $cron = cron::byClassAndFunction('gmailinfo', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}

?>