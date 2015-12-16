<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  wilaheng@gmail.com
 */
if (!defined("ENV")) die("Undefined_ENV_Exception");

if (sizeof($_SERVER["argv"]) > 1) {
$conn = DB::lookup("vfs");
try {
    $conn->begin();
    $methodName = inf()->execute;
    $execTime = DB::constant("CURRENT_TIMESTAMP");
    for($i = 1, $j = sizeof($_SERVER["argv"]); $i < $j; $i++) {
        $className = $_SERVER["argv"][$i];
        $logMessage = Service::get($className, $methodName)->getInstance()->$methodName();
        $conn->vfs_crons_logs(array(
            "CMD" => $className,
            "LOG_MESSAGE" => $logMessage,
            "EXEC_TIME" => $execTime
        ))->insert();
    }
    $conn->commit();
} catch(Exception $e) {
    $conn->rollback();
    dbg()->log(NS_ERROR, $e->getMessage());
}}
?>