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
 * @version 3.27-GA, 17/12/13
 */
if (!defined("EPR")) die("Undefined_EPR_Exception");

error_reporting(LOG_LEVEL);

define("RESTful", false);

define("FILE_SEPARATOR", "/");
define("ENV", EPR."/classes/");
define("LIB", FWK."/lib/");
define("phpExt", ".php");
define("STR_EMPTY", "");
define("REQUEST_METHOD", (isset($_SERVER["REQUEST_METHOD"])) ? strtoupper($_SERVER["REQUEST_METHOD"]) : "HEAD");
define("HTTP_CLIENT_IP", (isset($_SERVER["HTTP_CLIENT_IP"])) ? $_SERVER["HTTP_CLIENT_IP"] : (isset($_SERVER["HTTP_X_FORWARDED"])) ? $_SERVER["HTTP_X_FORWARDED"] : (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : (isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : "127.0.0.1");

//define("WITH_APC", function_exists("apc_fetch")); # portable alternative.
define("WITH_APC", false);

define("NS_DEBUG", "DEBUG");
define("NS_ALERT", "ALERT");
define("NS_ERROR", "ERROR");

define("RE_ADD", 1);
define("RE_PUT", 2);
define("RE_DEL", 4);
define("RE_QPR", 8);

function _2lp($n) {return strtr($n, "\\", FILE_SEPARATOR);}
function _2cp($n) {return strtr($n, array("." => FILE_SEPARATOR, "_" => FILE_SEPARATOR));}
function _2cn($n) {return strtr($n, array("." => "_", FILE_SEPARATOR => "_"));}
function _2nf($n, $d = 0) {return ($d == 0) ? number_format($n) : number_format($n, $d, ".", ",");}
function _2lc($n) {require_once _2cp($n).phpExt;}

set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), ENV, LIB)));
setlocale(LC_ALL, "id_ID");
date_default_timezone_set("GMT");
spl_autoload_register("_2lc");

final class apcWrapper {static public $m = array();}
if (WITH_APC) {
    //
    // TODO
    //
} else {
    function apc_store($i, $j) {apcWrapper::$m[$i] = $j;}
    function apc_fetch($i, &$j = null) {return (isset(apcWrapper::$m[$i])) ? apcWrapper::$m[$i] : false;}
    function apc_clear_cache($i = null) {apcWrapper::$m = array();}
}
if (apc_fetch("init") == false) {
    apc_store("conf", include ENV."META-INF/conf.php");
    apc_store("init", true);
}
class infWrapper {
    protected $conf;
    protected function set(array $conf) {
        $file = implode(FILE_SEPARATOR, $conf).phpExt;
        $conf = apc_fetch($file);
        if ($conf == false) {
            $conf = (include ENV.$file);
            apc_store($file, $conf);
        }
        $this->conf = $conf;
    }
    public function __construct(array $conf = null) {
        if ($conf == null) {
            $this->conf = apc_fetch("conf");
            Service::push($this);
        } else {
            array_push($conf, "META-INF", array_pop($conf));
            $this->set($conf);
        }
    }
    public function __unset($n) {unset($this->conf[$n]);}
    public function __isset($n) {return (isset($this->conf[$n]));}
    public function __get($n) {
        return (isset($this->conf[$n])) ? $this->conf[$n] : ($this->conf[$n] = new infWrapper(explode("_", $n)));
    }
    public function __call($n, $v) {
        return (isset($this->conf[$n])) ? vsprintf($this->conf[$n], $v) : $n;
    }
    public function push($n, $v) {
        $this->conf[$n] = (is_array($v)) ? $v : (array) $v;
        return $this;
    }
    public function getMap() {return $this->conf;}
}
function inf() {
    static $r = null;
    return ($r == null) ? ($r = new infWrapper()) : $r;
}
final class dbmWrapper extends infWrapper {
    private $ele;
    public function __construct(array $conf = null)
    {
        if ($conf !== null) {
            $args = array(array_shift($conf), "META-INF", implode("_", $conf));
            $this->set($args);
        }
    }
    public function __get($n) {
        return (isset($this->conf[$n])) ? $this->conf[$n] : (new dbmWrapper(explode("_", $n)));
    }
    public function __set($n, $v) {
        if (isset($this->conf[$n])) $this->conf[$n]["name"] = $v;
        else {
            $this->conf[$n] = array("name" => $v);
        }
    }
    public function nullify(array $exclude) {
        $_map = array();
        foreach($this->conf as $k => $r) {
            if (isset($exclude[$k])) $_map[$k] = array("value" => $exclude[$k]);
            else {
                $_map[$k] = STR_EMPTY;
            }
        }

        return $_map;
    }
    public function copy() {
        $n = func_num_args();
        $f = func_get_args();
        for($i = 0, $j, $k; $i < $n; $i++) {
            $j = $f[$i];
            $k = sprintf("%s_COPY", $j);
            if (isset($this->conf[$j])) $this->conf[$j]["name"] = $k;
            else {
                $this->conf[$j] = array("name" => $k);
            }
            $this->conf[$j]["req"] = true;
            $this->ele[] = $j;
        }

        return $this;
    }
    public function slice() {
        $n = func_num_args();
        $f = func_get_args();
        $r = array();
        for($i = 0, $j; $i < $n; $i++) {
            $j = $f[$i];
            $r[$j] = $this->conf[$j];
            unset($this->conf[$j]);
        }

        return $r;
    }
    public function only() {
        $n = func_num_args();
        $f = func_get_args();
        $r = array();
        for($i = 0, $j; $i < $n; $i++) {
            $j = $f[$i];
            $r[$j] = $this->conf[$j];
        }
        $this->conf = $r;

        return $this;
    }
    public function copyOnly() {
        $n = func_num_args();
        $f = func_get_args();
        $r = array();
        for($i = 0, $j, $k; $i < $n; $i++) {
            $j = $f[$i];
            $k = sprintf("%s_COPY", $j);
            if (isset($this->conf[$j])) $r[$j]["name"] = $k;
            else {
                $r[$j] = array("name" => $k);
            }
            $r[$j]["req"] = true;
            $this->ele[] = $j;
        }
        $this->conf = $r;

        return $this;
    }
    public function merge() {
        $n = func_num_args();
        $f = func_get_args();
        for($i = 0, $j; $i < $n; $i++) {
            if (is_string($f[$i])) $j = dbm()->$j->getMap();
            else {
                $j = ($f[$i] instanceof dbmWrapper) ? $f[$i]->getMap() : $f[$i];
            }
            $m = array_merge_recursive($this->conf, $j);
            $this->conf = $m;
        }

        return $this;
    }
    public function map($rs, $editable = false) {
        $_map = array();
        foreach($this->conf as $k => $r) {
            $_map[$k] = $rs->get($k, STR_EMPTY);
            if (isset($r["name"])) $_map[$r["name"]] = $_map[$k];
        }
        if ($this->ele != null) {
            foreach($this->ele as $k) {
                $v = $_map[$k];
                $_map[$k] = array("value" => $v, "editable" => $editable);
            }
        }

        return $_map;
    }
    public function argv(array $parameters = null, $methodName = null) {
        if ($parameters == null) {
            if ($this->conf == null) return;
            $parameters = $this->conf;
        }
        $_map = array();
        $_req = req();
        $_inf = inf();
        $_msg = null;
        try {
        foreach($parameters as $k => $r) {
            if ($r == null) continue;
            $_msg = (isset($r["msg"])) ? $r["msg"] : null;
            $v = trim($_req->getParameter((isset($r["name"])) ? $r["name"] : $k));
            if ($v == STR_EMPTY) {
                if (isset($r["req"])) {
                    if (isset($r["constant"])) $_map[$k] = DB::constant($r["constant"]);
                    if (isset($r["session"]) && $r["session"] === true) {
                        if (isset($_SESSION[$k])) $_map[$k] = $_SESSION[$k];
                    }
                    if ($methodName != null && isset($r["req"][$methodName])) {
                        if ($r["req"][$methodName] && !isset($_map[$k])) throw new Exception($_inf->requiredException($k));
                    }
                }
            } else {
                if (isset($r["type"])) {
                    switch($r["type"]) {
                        case "email": if (!ctype_email($v)) quit($_inf->requiredException($k)); break;
                        case "digit": case "int":
                            $x = preg_replace("/([^0-9.\-])/", STR_EMPTY, $v);
                            if (!is_numeric($x)) throw new Exception($_inf->digit($k, $v));
                            $v = ($r["type"] == "int") ? intval($x) : $x;
                        break;
                        case "alnum": if (!ctype_alnum($v)) throw new Exception($_inf->alnum($k, $v)); break;
                        case "alpha": if (!ctype_alpha($v)) throw new Exception($_inf->alpha($k, $v)); break;
                        case "regex": if (!preg_match($r["regex"], $v)) throw new Exception($_inf->regexException($k, $r["regex"], $v)); break;
                        case "bool":
                            if ($v !== "true" || $v !== "false") throw new Exception($_inf->boolean($k, $v));
                            $v = ($v === "true") ? true : false;
                        break;
                        case "date":
                            if (strlen($v) === 8) $v = sprintf("%s-%s-%s", substr($v, 0, 4), substr($v, 4, 2), substr($v, 6));
                            if (isset($r["format"])) {
                                $c = array_combine(preg_split("/([\/\.-])/", $r["format"], -1), preg_split("/([\/\.-])/", $v, -1));
                                $d = intval($c["d"]);
                                $m = intval($c["m"]);
                                $Y = intval($c["Y"]);
                                if (!checkdate($m, $d, $Y)) throw new Exception($_inf->date($k, $v));
                                $v = sprintf("%s-%s-%s", $c["Y"], $c["m"], $c["d"]);
                            }
                        break;
                        case "periode":
                            $j = strlen($v);
                            if ($j > 7) throw new Exception($_inf->length($k, $v, $j));
                            if ($j === 6) {
                                $v = sprintf("%s-%s", substr($v, 0, 4), substr($v, 4));
                            }
                        break;
                    }
                }
                if (isset($r["uppercase"])) {
                    if ($r["uppercase"] === true) $v = strtoupper($v);
                }
                if (isset($r["lowercase"])) {
                    if ($r["lowercase"] === true) $v = strtolower($v);
                }
                $x = (isset($r["minl"])) ? true : false;
                $y = (isset($r["maxl"])) ? true : false;
                if ($x || $y) {
                    $j = strlen($v);
                    if ($x) if ($j < $r["minl"]) throw new Exception($_inf->length($k, $v, $j));
                    if ($y) if ($j > $r["maxl"]) throw new Exception($_inf->length($k, $v, $j));
                }
                $x = (isset($r["minv"])) ? true : false;
                $y = (isset($r["maxv"])) ? true : false;
                if ($x || $y) {
                    if ($x) if ($v < $r["minv"]) throw new Exception($_inf->value($k, $v, "<", $r["minv"]));
                    if ($y) if ($v > $r["maxv"]) throw new Exception($_inf->value($k, $v, ">", $r["maxv"]));
                }
                $_map[$k] = $v;
            }
        }} catch(Exception $e) {
            throw new Exception(($_msg == null) ? $e->getMessage() : $_msg);
        }
        if (DEBUG_MSG) api_DBG::setMap($_map, $parameters); #comment for production use to avoid useless eval

        return $_map;
    }
}
function dbm() {
    static $r = null;
    return ($r == null) ? ($r = new dbmWrapper()) : $r;
}
final class reqWrapper {
    private $argv, $json = true, $ajax = false, $_cnt = 0;
    public function __construct() {
        Service::push($this);
        if ((isset($_SERVER["CONTENT_TYPE"])) && (stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false)) {
            $j = json_decode(file_get_contents("php://input", 10000000));
            foreach($_GET as $k => $v) $j->$k = $v;
            $this->argv = $j;
        } else {
            foreach($_GET as $k => $v) $_POST[$k] = $v;
            $this->argv = $_POST;
            $this->json = false;
        }
        $inf = inf();
        if (isset($_SERVER[$inf->xreq])) {
            if ($_SERVER[$inf->xreq] == $inf->ajax) $this->ajax = true;
        }
    }
    public function inc() {$this->_cnt+= 1;}
    public function counter() {return $this->_cnt;}
    public function confirm() {return ($this->_cnt >= 1) ? true : false;}
    public function setParameter($argv) {$this->argv = $argv;}
    public function getParameter($name) {
        return ($this->json) ? ((property_exists($this->argv, $name)) ? $this->argv->$name : STR_EMPTY) : ((isset($this->argv[$name])) ? $this->argv[$name] : STR_EMPTY);
    }
    public function getMap() {return ($this->json) ? (array)$this->argv : $this->argv;}
    public function ajax() {return $this->ajax;}
    public function __isset($name) {
        return ($this->json) ? (property_exists($this->argv, $name)) : (isset($this->argv[$name]));
    }
    public function __get($name) {return $this->getParameter($name);}
    public function __set($name, $value) {
        if ($this->json) {
            $this->argv->$name = $value;
        } else {
            $this->argv[$name] = $value;
        }
    }
}
function req() {
    static $r = null;
    return ($r == null) ? ($r = new reqWrapper()) : $r;
}
final class envWrapper {
    static public $ACL; /* bad practice :D */
    private $map;
    private function setMap($i, $j) {
        $this->map[$i] = $j;

        return $this;
    }
    public function __construct()
    {
        Service::push($this);
        $this->map = array("a" => 200, "c" => false);
    }
    public function callback() {return $this->setMap("c", true);}
    public function reset($callback = true) {return $this->setMap(($callback) ? "d" : "e", true);}
    public function code($j) {return $this->setMap("a", $j);}
    public function data(array $j) {return $this->setMap("b", $j);}
    public function call(array $j) {return $this->setMap("r", $j);}
    public function info() {
        $j = func_num_args();
        $q = func_get_arg(0);
        if ($j > 1) {
            for($i = 1, $v = null; $i < $j; $i++) $v[] = func_get_arg($i);
            $q = vsprintf($q, $v);
        }

        return $this->setMap("s", $q);
    }
    public function warn() {
        $j = func_num_args();
        $q = func_get_arg(0);
        if ($j > 1) {
            for($i = 1, $v = null; $i < $j; $i++) $v[] = func_get_arg($i);
            $q = vsprintf($q, $v);
        }

        return $this->setMap("w", $q);
    }
    public function ctrl($_create = false, $_update = false, $_delete = false) {
        if (self::$ACL !== null) {
            if ($_create) $_create = $_create && self::$ACL[0];
            if ($_update) $_update = $_update && self::$ACL[1];
            if ($_delete) $_delete = $_delete && self::$ACL[2];
        }

        return $this->setMap("x", array(
            "c" => $_create, "u" => $_update, "d" => $_delete
        ));
    }
    public function puts(array $j) {return $this->setMap("i", $j);}
    public function push($msg = null, array $res = null, $code = 200) {
        $this->setMap("a", $code);
        if ($msg !== null) $this->setMap("w", $msg);
        if ($res !== null) $this->setMap("r", $res);

        return $this;
    }
    public function debug(array $z) {if (sizeof($z)) $this->setMap("__DBG__", $z);}
    public function toString() {
        return json_encode($this->map);
    }
}
function env() {
    static $r = null;
    return ($r == null) ? ($r = new envWrapper()) : $r;
}
final class ssnWrapper {
    private $lifetime;
    public function __destruct() {session_write_close();}
    public function __construct() {
        Service::push($this);
        session_set_save_handler(
            array($this, "op"), array($this, "cl"), array($this, "rd"),
            array($this, "wr"), array($this, "dl"), array($this, "gc")
        );
        $this->lifetime = time() - intval(ini_get("session.gc_maxlifetime"));
    }
    public function initialize() {
        $conn = DB::lookup("vfs", true);
        $r = $conn->executeQuery(
            "SELECT COUNT(*) T FROM vfs_users_sessions WHERE SES_TIME>'%s'", $this->lifetime
        );
        $j = ($r->next()) ? $r->getInt("T") : 0;
        if ($j >= inf()->session_stack) throw new Exception("SessionStackException: please wait for session GC");
        session_start();
    }
    public function cl() {return true;}
    public function op($a, $b) {return true;}
    public function rd($a) {
        $conn = DB::lookup("vfs", true);
        $r = $conn->executeQuery(
            "SELECT SES_DATA FROM vfs_users_sessions WHERE SES='%s' AND SES_TIME>'%s'", $a, $this->lifetime
        );

        return ($r->next()) ? $r->get("SES_DATA", STR_EMPTY) : "";
    }
    public function ch($a) {
        $conn = DB::lookup("vfs", true);
        $r = $conn->executeQuery(
            "SELECT COUNT(*) T FROM vfs_users_sessions WHERE USR='%s' AND SES_TIME>'%s'", $a, $this->lifetime
        );

        return ($r->next()) ? $r->getInt("T", 0) : 0;
    }
    public function wr($a, $b) {
        $r = false;
        if (isset($_SESSION["USR"])) {
            $_now = time();
            try {
                $conn = DB::lookup("vfs", true);
                $conn->begin();
                $conn->vfs_users_sessions(array(
                    "SES" => $a, "USR" => $_SESSION["USR"], "SES_DATA" => $b, "SES_DATE" => date("Y-m-d H:i:s", $_now), "SES_TIME" => $_now, "SES_ADDR" => HTTP_CLIENT_IP
                ))->replace();
                $conn->reset();
                $conn->commit();
            } catch(Exception $e) {
                dbg()->log($this, $e->getMessage());
            }
            $r = true;
        }

        return $r;
    }
    public function gc($a) {
        $conn = DB::lookup("vfs", true);
        $conn->begin();
        $conn->executeUpdate(
            "REPLACE INTO vfs_users_sessions_logs SELECT SES,USR,SES_ADDR,SES_TIME,SES_DATE FROM vfs_users_sessions WHERE SES_TIME<'%s'",
            $this->lifetime
        );
        $conn->executeUpdate("DELETE FROM vfs_users_sessions WHERE SES_TIME<'%s'", $this->lifetime);
        $conn->commit();

        return true;
    }
    public function dl($a) {
        $conn = DB::lookup("vfs", true);
        $conn->begin();
        $conn->executeUpdate("DELETE FROM vfs_users_sessions WHERE SES='%s'", $a);
        $conn->commit();

        return true;
    }
}
function ssn() {
    static $r = null;
    return ($r == null) ? ($r = new ssnWrapper()) : $r;
}
final class StringHelper {
    private $a;
    public function __construct($a) {$this->a = $a;}
    public function toString() {return $this->a;}
}
final class dbgWrapper {
    private $PER, $USR;
    public function __construct() {
        $this->argv = array(
            "PER" => date("Ymd"),
            "USR" => (isset($_SESSION["USR"])) ? $_SESSION["USR"] : "GUEST",
            "ADDR" => HTTP_CLIENT_IP,
            "NAMESPACE" => null,
            "MSG" => null,
            "LOGTIME" => DB::constant("CURRENT_TIMESTAMP")
        );
    }
    public function log($x = null, $m = "*") {
        $this->argv["NAMESPACE"] = ($x == null) ? "STD" : ((is_object($x)) ? get_class($x) : $x);
        $this->argv["MSG"] = $m;
        $conn = DB::lookup("vfs", true);
        $conn->begin();
        $conn->vfs_logs($this->argv)->insert();
        $conn->reset();
        $conn->commit();
    }
}
function dbg() {
    static $r = null;
    return ($r == null) ? ($r = new dbgWrapper()) : $r;
}
function mobile() {
    static $r = null;
    if ($r == null) $r = (isset($_SESSION["MOBILE"]) && $_SESSION["MOBILE"] === true);

    return $r;
}
final class Service {
    static private $singleton = array();
    private $instance, $clazz, $method;
    public function __construct($c, $name = null) {
        $r = new ReflectionClass($c);
        $this->clazz = $this->parse($r->getDocComment());
        if ($name != null) {
        if ($r->hasMethod($name)) {
            $this->method = $this->parse($r->getMethod($name)->getDocComment());
        }}
        $this->instance = $r->newInstance();
    }
    private function parse($x) {
        $b = array();
        if (preg_match_all("/(?:\@(.*))/", $x, $m)) foreach($m[1] as $v) {
            $a = preg_split("/[\s]+/", $v, -1, PREG_SPLIT_NO_EMPTY);
            $n = array_shift($a);
            if ($n == "argv") {
                $t = array_shift($a);
                $b[$n][$t] = (sizeof($a)) ? implode(" ", $a) : null;
            } else {
                $b[$n] = implode(" ", $a);
            }
        }

        return $b;
    }
    public function getInstance() {return $this->instance;}
    public function getMethod() {return $this->method;}
    public function noted($n) {return (isset($this->clazz[$n])) ? true : false;}
    static public function get() {
        $f = func_get_args();
        $n = array_shift($f);
        $n = _2cn($n);
        if (isset(self::$singleton[$n])) return self::$singleton[$n];
        if (!class_exists($n, false)) {
            require_once _2cp($n).phpExt;
            if (!class_exists($n, false)) throw new Exception(inf()->resourceNotFound($n));
        }
        $x = new Service($n, (sizeof($f)) ? $f[0] : null);
        if ($x->noted(inf()->singleton)) {
            if (!isset(self::$singleton[$n])) self::$singleton[$n] = $x;
        }

        return $x;
    }
    static public function push($instance) {
        $n = get_class($instance);
        if (isset(self::$singleton[$n])) throw new Exception(inf()->singletonException($n));
        self::$singleton[$n] = $instance;
    }
}
function gzheader($binary) {
    $headers = array(
        "Expires: Mon, 26 Jul 1997 05:00:00 GMT", "Pragma: no-cache", "Cache-Control: no-cache, must-revalidate",
        "Content-Transfer-Encoding: binary", "Content-Encoding: gzip", "Content-Length: ".strlen($binary),
        "Vary: Accept-Encoding"
    );
    foreach($headers as $header) header($header);
}
function send_binary($fileName, $binary, $gzip = false) {
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $map = inf()->mime->getMap();
    if ($gzip) gzheader($binary);
    header(sprintf("Content-Type: %s", (isset($map[$ext])) ? $map[$ext] : $map["txt"]));
    print($binary);
    exit(0);
}
function send_attach($fileName, $binary) {
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $map = inf()->mime->getMap();
    header(sprintf("Content-Type: %s", (isset($map[$ext])) ? $map[$ext] : $map["txt"]));
    header(sprintf("Content-Disposition: attachment;filename=%s", $fileName));
    print($binary);
    exit(0);
}
function ctype_email($m) {
    $rx = array(
        "?", "|/+|", "#^\s*\*(.*)#m", "/[\s]+/", "/\\.\\./",
        '/^[A-Za-z0-9\\-\\.]+$/', "/\\.\\./", '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
        '/^"(\\\\"|[^"])+"$/', "\\\\"
    );
    $sp = strrpos($m, "@");
    if ($sp === false) return false;
    $dp = substr($m, $sp + 1);
    $dL = strlen($dp);
    $ll = substr($m, 0, $sp);
    $lL = strlen($ll);
    $lR = str_replace($rx[9], STR_EMPTY, $ll);
    $rs = true;
    if ($lL < 1 || $lL > 64 || $dL < 1 || $dL > 255) {
        $rs = false;
    } elseif ($ll[0] == "." || $ll[$lL - 1] == ".") {
        $rs = false;
    } elseif (preg_match($rx[4], $ll) || !preg_match($rx[5], $dp) || preg_match($rx[6], $dp)) {
        $rs = false;
    } elseif (!preg_match($rx[7], $lR) && !preg_match($rx[8], $lR)) $rs = false;

    return $rs;
}
function alert($m) {
    return sprintf('<html><head><script>function _(){window.alert(document.getElementById("m").innerHTML)}</script></head><body onload="_()"><div id="m">%s</div></body></html>', $m);
}
function confirm_message($m) {
    return sprintf('<html><head><script>function _(){window.parent.cf(%d, document.getElementById("m").innerHTML)}</script></head><body onload="_()"><div id="m">%s</div></body></html>', req()->counter(), $m);
}
function confirm($m) {
    $r = req();
    $r->inc();
    if (!empty($r->CONFIRM)) {
        return (intval($r->CONFIRM) >= 1) ? true : false;
    }
    throw new Exception($m);
}
function error_handler($no, $msg, $file, $line) {
    DB::rollback();
    $dbg = print_r(debug_backtrace(), true);
    $msg = sprintf("%s (%s): %s <pre>%s</pre>", $file, $line, $msg, $dbg);
    dbg()->log(NS_ERROR, $msg);
    if (!req()->ajax()) {
        if (REQUEST_METHOD == "POST") $msg = alert($msg);
    }
    @file_put_contents("err.txt", $msg);
    die($msg);
}
set_error_handler("error_handler");
?>