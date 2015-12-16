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
if (!defined("REQUEST_URI")) die("Undefined_ENV_Exception");

$inf = inf();
$className = $inf->session;
$methodName = $inf->execute;

$p = strpos(REQUEST_URI, "?");
$q = ($p > 0) ? substr(REQUEST_URI, 0, $p) : REQUEST_URI;
$u = trim($q, FILE_SEPARATOR);
if (!empty($u)) {
    if (substr($q, -1) == FILE_SEPARATOR || strpos($u, FILE_SEPARATOR) === false) $u.= "/HEAD";
    $p = strrpos($u, FILE_SEPARATOR);
    $methodName = substr($u, $p + 1);
    $className = substr($u, 0, $p);
}

$req = req();
$chk = ($req->ajax()) ? false : (REQUEST_METHOD == "POST");
$inc = false;

try {
    ssn()->initialize();
    if (isset($_SESSION["EXP"]) && $_SESSION["EXP"] === true) $req->context = $_SESSION["CTX"];
    else {
        if (empty($req->context)) $req->context = (isset($_SESSION["CTX"])) ? $_SESSION["CTX"] : "vfs";
        else {
            if (!ctype_alnum($req->context)) throw new Exception($inf->resourceNotFound($req->context));
        }
    }

    $serv = Service::get($className, $methodName);
    if (!$serv->noted($inf->service) && !$serv->noted($inf->secure_service)) throw new Exception($inf->resourceNotFound($className));

    $instance = $serv->getInstance();
    $name = get_class($instance);

    if ($serv->noted($inf->secure_service)) {
        if (isset($_SESSION["USR"])) {
        if (!$serv->noted($inf->no_context)) {
            if (!isset($_SESSION["ACL"][$name])) throw new Exception($inf->accessException($name, $methodName));
            $ACL = $_SESSION["ACL"][$name];
            $map = array("POST" => 0, "PUT" => 1, "DELETE" => 2, "GET" => 3, "HEAD" => 4);
            if (isset($map[$methodName])) {
                $rq = $map[$methodName];
                if (isset($ACL[$rq]) && $ACL[$rq] == false) throw new Exception($inf->accessException($name, $methodName));
                if ($rq === 4 && $_SESSION["MOBILE"]) $inc = true;
            }
            envWrapper::$ACL = $ACL;
        }} else {
            $methodName = $inf->execute;
            $serv = Service::get($inf->session, $methodName);
            $instance = $serv->getInstance();
        }
    }

    $info = $serv->getMethod();
    if ($info === null) throw new Exception($inf->methodException($name, $methodName));
    if (isset($info["rest"])) {
        if (REQUEST_METHOD !== $methodName) {
            if (RESTful || REQUEST_METHOD !== "POST") throw new Exception($inf->methodException($name, $methodName));
        }
    }

    if (isset($info["ajax"])) {
        if (!$req->ajax()) throw new Exception($inf->ajaxException($name, $methodName));
    }

    $silent = isset($info["silent"]);
    if ($silent) {
        if (isset($info["contentType"])) header("Content-Type: ".$info["contentType"]);
    }

    if ($inc) {
        ob_start();
        //
        // require header, navigation + client document controller
        //
    }

    $argv = null;

    if (isset($info["conn"])) {
        if (!empty($info["conn"])) $argv[] = DB::lookup($info["conn"]);
    }

    if (isset($info["argv"])) {
        $_dbm = dbm();
        $_map = array("only", "copy", "copyOnly", "slice", "push"); # auto configure
        foreach($info["argv"] as $schema => $j) {
            $k = $_dbm->$schema;
            if ($j != null) {
                $j = json_decode($j);
                foreach($_map as $m) {
                    if (property_exists($j, $m)) call_user_func_array(array($k, $m), $j->$m);
                }
            }
            $argv[] = $k->argv(null, $methodName); # passing methodName for bitwise op
        }
    }

    if (isset($info["env"])) $argv[] = env();

    $output = ($argv == null) ? $instance->$methodName() : call_user_func_array(array($instance, $methodName), $argv);

    if ($inc) {
        //
        // require footer (classic approach)
        //
        $output = ob_get_flush();
    }

    if (!$silent && $output !== null) {
        if (is_scalar($output)) {
            if ($chk) $output = ($req->confirm()) ? confirm_message($output) : alert($output);
        } else {
            $obj = ($output instanceof envWrapper) ? true : false;

#ifdef: comment for production use to avoid useless eval
            if (DEBUG_MSG && !isset($info["ignore"])) {
                $map = api_DBG::getMap();
                if ($obj) $output->debug($map); else $output["__DBG__"] = $map;
            }
#endif
            $output = ($obj) ? $output->toString() : json_encode($output);
            if ($chk) {
                $output = sprintf("<html><head><script>window.onload=function(){window.parent.rs(eval('(%s)'))}</script></head></html>", $output);
            } else {
                header("Content-Type: application/json");
            }
        }

#ifdef: without buffer hack (to reduce 4kb buffer size) avoid useless task
        /*if (isset($info["gzip"])) {
            $output = gzencode($output, 9);
            gzheader($output);
        }*/
#endif
        print($output);
    }
} catch(Exception $x) {
    $message = $x->getMessage();
    dbg()->log(null, $message);
    print(($chk) ? alert($message) : $message);
}
?>