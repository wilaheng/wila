<?php
/**
 * @no-context
 * @service
 * @session-service
 * @singleton
 */
final class vfs_SSO {
    private $TDA = array();
    private $ACL = array();
    private $CID = array();
    private $SPC = array();
    private $node;
    private $_map = array();
    private $features = array();
    private function getFeatures($a) {
        $f = array();
        $c = DB::lookup("vfs", true);
        $r = $c->executeQuery("SELECT * FROM vfs_packages_classes_features WHERE PKC='%s'", $a);
        while($r->next()) {
            array_push($f, $r->get("FTR"));
        }
        return (count($f)) ? implode(";", $f) : "";
    }
    private function namespace_($a) {
        $r = array();
        $b = explode("/", $a);
        $c = count($b);
        for($i = 0; $i < $c; $i++) {
            $g = explode("|", $b[$i]);
            array_push($r, $g[0]);
        }
        return implode("/", $r);
    }
    private function configure($a) {
        $n = $a["P"];
        $b = explode("/", $n);
        $c = count($b) - 1;
        $g = $this->node;
        for($i = 0; $i < $c; $i++) $g = $g->{$b[$i]};
        $q = preg_replace("/(@[A-Za-z])|([^0-9a-zA-Z|:\/_])/", "", $n);
        $x = $this->namespace_($q);
        $this->_map[$a["C"]] = $a["C"].":".$a["H"]."@".$x;
        $g->{$b[$c]} = $this->_map[$a["C"]]."#".$this->getFeatures($a["C"]);
        $c = _2cn($x);
        return $c;
    }
    private function keysProccess($g) {
        $r = array();
        foreach($g as $k => $v) {
            if (is_array($v)) {
                if (!strpos($k, "|")) $r[$k] = $this->keysProccess($v);
            } else {
                $u = explode(":", $v);
                if (!in_array($u[0], $this->features)) $r[$k] = array_pop($u);
            }
        }
        return $r;
    }
    private function postProccess($g) {
        $r = array();
        foreach($g as $k => $v) {
            if (is_string($k)) {
                $z = (strpos($k, "|") > 0) ? explode("|", $k) : array($k, $k);
                if (is_array($v)) {
                    $r[$z[1]] = $r[$k] = $this->postProccess($v);
                } else {
                    $m = explode("#", $v);
                    $m[0].= "/";
                    $q = explode("@", $z[1]);
                    $n = substr($m[0], strpos($m[0], "@") + 1);
                    $a = substr($v, 0, strpos($v, ":"));
                    if (!isset($this->ACL[$a])) $this->ACL[$a] = null;
                    $this->SPC[$n] = array("name" => $q[0], "type" => substr($m[0], strpos($m[0], ":") + 1, 1), "acl" => $this->ACL[$a], "info" => $this->TDA[$a]);
                    if (!empty($m[1])) {
                        $n = explode(";", $m[1]);
                        foreach($n as $i => $j) {
                            if (!isset($this->_map[$j])) throw new Exception(sprintf("PKG CLASS %s HAVE NO GROUP", $j));
                            $f = explode(":", $this->_map[$j]);
                            $n[$i] = $f[1]."/";
                        }
                        $m[1] = implode(";", $n);
                    }
                    $r[$z[1]] = implode("#", $m);
                }
            }
        }
        return $this->keysProccess($r);
    }
    private function mergeACL($acl1, $acl2) {
        $ret = array();
        foreach($acl1 as $k => $v) {
            $ret[$k] = ($v || $acl2[$k]);
        }
        return $ret;
    }
    private function toACL($hex) {
        $acl = str_split($hex);
        $ret = array();
        foreach($acl as $v) {
            array_push($ret, ($v === "1") ? true : false);
        }
        return $ret;
    }
    private function createNamespace($_USR, $NAME, $AREA_CODE, $AREA_SUB_CODE, $POSITION) {
        $c0 = DB::lookup("vfs", true);
        $_GID = array();
        $_PKG = array();
        $_CID = array();
        $_ACL = array();
        $r2 = $c0->executeQuery("SELECT GRP, NAME FROM vfs_groups WHERE GRP='1100'");
        while($r2->next()) {
            $_GID[$r2->getInt("GRP")] = $r2->get("NAME");
        }
        // only if not expired
        if ($_SESSION["EXP"] == false) {
            $r2 = $c0->executeQuery("SELECT GRP, NAME FROM vfs_users_groups WHERE USR='%s'", $_USR);
            while($r2->next()) {
                $_GID[$r2->getInt("GRP")] = $r2->get("NAME");
            }
        }
        $r3 = $c0->executeQuery(
            "SELECT * FROM vfs_users_groups_classes WHERE GRP IN (%s)", implode(",", array_keys($_GID))
        );
        while($r3->next()) {
            $PKG = $r3->getInt("PKG");
            $PID = $r3->getInt("PID");
            $CID = $r3->getInt("PKC");
            if ($r3->getInt("FTR")) $this->features[] = $CID;
            if (!isset($this->CID[$PKG])) $this->CID[$PKG] = array();
            if (!isset($_PKG[$r3->get("CODE")])) $_PKG[$r3->get("CODE")] = $r3->get("PKG_NAME");
            if (!isset($_ACL[$PKG])) $_ACL[$PKG] = array();
            $_ACL[$PKG][$CID] = $r3->get("ACL");
            $Ga = $r3->get("DISP");
            $this->CID[$PKG][$CID] = array(
                "B" => $PID,
                "C" => $CID,
                "D" => $_ACL[$PKG][$CID],
                "G" => $r3->get("PKC_NAME"),
                "Ga" => $Ga,
                "H" => $r3->get("TYPE"),
                "PT" => $r3->get("TITLE"),
                "PD" => $r3->get("DESC"),
                "P" => (($PID == 0) ? $r3->get("CODE") : $r3->get("PKC_NAME"))
            );
        }
        foreach($this->CID as $a => $b) {
        foreach($b as $k => $v) {
        if ($v["B"]) {
        if (isset($this->CID[$a][$v["B"]])) {
            if (is_null($v["Ga"]) || empty($v["Ga"])) $v["Ga"] = $v["G"];
            $this->CID[$a][$k]["P"] = $this->CID[$a][$v["B"]]["P"]."/".$v["G"]."|".$v["Ga"]."@".$v["H"];
        } else {
            unset($this->CID[$a][$k]);
        }}}}
        $this->node = new api_TreeHelper();
        foreach($this->CID as $a => $thiz) {
        foreach($thiz as $k => $v) {
            $key = $v["C"];
            $this->TDA[$key] = array("T" => $v["PT"], "D" => $v["PD"]);
            $clz = $this->configure($v);
            if ($v["H"] != "C" && $v["H"] != "O") {
                $acl = $this->toACL($v["D"]);
                if (isset($this->ACL[$key])) {
                    $tmp = $this->mergeACL($this->ACL[$key], $acl);
                    $acl = $tmp;
                }
                $this->ACL[$clz] = $acl;
            }
        }}
        $_SESSION["ACL"] = $this->ACL;
        $_SESSION["TrH"] = $this->postProccess($this->node->pack());
        $_SESSION["SPC"] = $this->SPC;
        $_SESSION["context"] = $_PKG;
        $_SESSION["ATR"] = array(
            "NAME" => $NAME,
            "LOKER" => sprintf("%s: %s, %s: %s (%s)", inf()->term->AREA, $AREA_CODE, inf()->term->AREA_SUB, $AREA_SUB_CODE, vfs_Service::subAreaName($AREA_CODE, $AREA_SUB_CODE)),
            "UNIT" => $POSITION
        );
        $_SESSION["USR"] = $_USR;
        $_SESSION["EXT"] = null;
        $ex = $c0->executeQuery("SELECT * FROM vfs_users_ext WHERE USR='%s'", $_USR);
        while($ex->next()) {
            $_SESSION["EXT"][$ex->get("AREA")][] = $ex->get("AREA_SUB");
        }
    }
    private function match($key, $name) {
        $m = 0;
        foreach($key as $val) {
            if (isset($name[$val]) && ($name[$val] === true)) {
                $name[$val] = false;
                $m+= 1;
            }
        }
        return ($m == 3) ? true : false;
    }
    public function getToken() {
        $USR = self::random();
        $USR_ = self::mix(8, array("U", "S", "R"));
        $PWD = self::random();
        $PWD_ = self::mix(8, array("P", "W", "D"));
        $CRC = md5($USR_.$PWD_);
        setcookie("CRC", $CRC);
        return array(
            "USR" => $USR, "USR_" => $USR_,
            "PWD" => $PWD, "PWD_" => $PWD_
        );
    }

    /**
     * TO-DOC
     */
    public function HEAD() {
        if (isset($_SESSION["USR"])) {
            if (isset($_COOKIE["CRC"])) setcookie("CRC", "");
            require_once sprintf("GUI.%s.html", ($_SESSION["MOBILE"]) ? "mobile" : "default");
            return;
        }
        $message = "gunakan Firefox/Chrome/Safari terbaru";
        $comply = false;
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            if (self::mobile()) $comply = true;
            else {
                if (preg_match("((Firefox|Chrome|Safari)/([0-9.]+))", $_SERVER["HTTP_USER_AGENT"], $m)) {
                    $version = floatval($m[2]);
                    $message = sprintf("minimum %s version: %s", $m[1], $version);
                    switch($m[1]) {
                        case "Firefox": if ($version >= 18) $comply = true; break;
                        case "Chrome": if ($version >= 24) $comply = true; break;
                        case "Safari": if ($version >= 5) $comply = true; break;
                    }
                }
            }
        }
        extract($this->getToken());
        require_once "SSO.html";
    }

    /**
     * @ignore
     * @ajax
     */
    public function POST() {
        if (isset($_SESSION["USR"])) return "OK";
        $Qa = "Authorization failed";
        $_USR = null;
        $USR_ = array("U" => true, "S" => true, "R" => true);
        $_PWD = null;
        $PWD_ = array("P" => true, "W" => true, "D" => true);
        $CRC = null;
        foreach($_POST as $key => $val) {
            $sp = str_split($key);
            if ($this->match($sp, $USR_)) {
                if (strlen($val) == 32 && strlen($key) >= 5) {
                    $CRC = $key;
                    $_USR = $val;
                }
            }
            if ($this->match($sp, $PWD_)) {
                if (strlen($val) == 32 && !is_null($CRC)) {
                    $CRC.= $key;
                    $CRC = md5($CRC);
                    $_PWD = $val;
                }
            }
        }
        if (is_null($_USR) || is_null($_PWD) || (sizeof($_COOKIE) == 0)) return $Qa;
        if ($_COOKIE["CRC"] != $CRC) return "Invalid CRC";
        $c0 = DB::lookup("vfs", true);
        $r0 = $c0->executeQuery(
            "SELECT v.*, p.NAME POSITION FROM vfs_users v LEFT JOIN vfs_positions p ON (v.ID_POSITION=p.ID_POSITION) WHERE v.BEGDA<=CURRENT_DATE AND v.BEGDA IS NOT NULL AND (v.ENDDA>=CURRENT_DATE OR v.ENDDA IS NULL) AND MD5(v.USR)='%s' AND v.PWD='%s'",
            $_USR, $_PWD
        );
        if ($r0->next()) {
            $SHR = $r0->getInt("SHR");
            if ($SHR >= 1) {
                $rc = ssn()->ch($r0->get("USR"));
                if ($rc > $SHR) die("This account already have a SESSION");
            }
            $CHK = $r0->getInt("CHK");
            if ($CHK >= 1) {
                //
                // CHECK ADDR FROM CLIENTS TABLE
                //
            }
            $_SESSION["MOBILE"] = self::mobile();
            /*$_SESSION["BUS_AREA"] = $r0->get("BUS_AREA");
            $_SESSION["COST_CTR"] = $r0->get("COST_CTR");
            $_SESSION["PRFT_CTR"] = $r0->get("PRFT_CTR");*/
            $_SESSION["EXP"] = false;
            if ($r0->get("EXP")) {
                $time = strtotime($r0->get("EXP"));
                $_now = time();
                if ($time <= $_now) $_SESSION["EXP"] = true;
                $_SESSION["ELAPSED"] = ceil(($time - $_now)/(60 * 60 * 24));
            }
            $this->createNamespace($r0->get("USR"), $r0->get("NAME"), $r0->get("AREA"), $r0->get("AREA_SUB"), $r0->get("POSITION"));
            if ($r0->get("DEF")) {
                if ($_SESSION["EXP"]) {
                    req()->context = $_SESSION["CTX"] = "vfs";
                } else {
                    if (isset($_SESSION["context"][$r0->get("DEF")])) req()->context = $_SESSION["CTX"] = $r0->get("DEF");
                }
            }
            session_regenerate_id();
            $Qa = "OK";
        }
        return $Qa;
    }
    public function destroy() {
        session_destroy();
        header("Location: "._2lp(dirname($_SERVER["SCRIPT_NAME"])));
    }
    static private $chars = array("a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z");
    static public function random($j = 8) {
        $randt = "";
        srand((double)microtime() * 1000000);
        for($i = 0, $k = sizeof(self::$chars) - 1; $i < $j; $i++) {
            $randt.= self::$chars[rand(0, $k) ];
        }
        return $randt;
    }
    static public function mix($j = 8, array $name) {
        srand((double)microtime() * 1000000);
        for($i = 0, $k = sizeof(self::$chars) - 1; $i < $j; $i++) {
            $char = self::$chars[rand(0, $k) ];
            if (in_array($char, $name)) continue;
            $name[] = $char;
        }
        shuffle($name);
        $out = implode("", $name);
        return $out;
    }
    public static function mobile() {
        $ua = strtolower($_SERVER["HTTP_USER_AGENT"]);
        $ac = $_SERVER["HTTP_ACCEPT"];
        $ma = array(
            "1207", "3gso", "4thp", "501i", "502i", "503i", "504i", "505i", "506i",
            "6310", "6590", "770s", "802s", "a wa", "acer", "acs-", "airn", "alav",
            "asus", "attw", "au-m", "aur ", "aus ", "abac", "acoo", "aiko", "alco",
            "alca", "amoi", "anex", "anny", "anyw", "aptu", "arch", "argo", "bell",
            "bird", "bw-n", "bw-u", "beck", "benq", "bilb", "blac", "c55/", "cdm-",
            "chtm", "capi", "cond", "craw", "dall", "dbte", "dc-s", "dica", "ds-d",
            "ds12", "dait", "devi", "dmob", "doco", "dopo", "el49", "erk0", "esl8",
            "ez40", "ez60", "ez70", "ezos", "ezze", "elai", "emul", "eric", "ezwa",
            "fake", "fly-", "fly_", "g-mo", "g1 u", "g560", "gf-5", "grun", "gene",
            "go.w", "good", "grad", "hcit", "hd-m", "hd-p", "hd-t", "hei-", "hp i",
            "hpip", "hs-c", "htc ", "htc-", "htca", "htcg", "htcp", "htcs", "htct",
            "htc_", "haie", "hita", "huaw", "hutc", "i-20", "i-go", "i-ma", "i230",
            "iac", "iac-", "iac/", "ig01", "im1k", "inno", "iris", "jata", "java",
            "kddi", "kgt", "kgt/", "kpt ", "kwc-", "klon", "lexi", "lg g", "lg-a",
            "lg-b", "lg-c", "lg-d", "lg-f", "lg-g", "lg-k", "lg-l", "lg-m", "lg-o",
            "lg-p", "lg-s", "lg-t", "lg-u", "lg-w", "lg/k", "lg/l", "lg/u", "lg50",
            "lg54", "lge-", "lge/", "lynx", "leno", "m1-w", "m3ga", "m50/", "maui",
            "mc01", "mc21", "mcca", "medi", "meri", "mio8", "mioa", "mo01", "mo02",
            "mode", "modo", "mot ", "mot-", "mt50", "mtp1", "mtv ", "mate", "maxo",
            "merc", "mits", "mobi", "motv", "mozz", "n100", "n101", "n102", "n202",
            "n203", "n300", "n302", "n500", "n502", "n505", "n700", "n701", "n710",
            "nec-", "nem-", "newg", "neon", "netf", "noki", "nzph", "o2 x", "o2-x",
            "opwv", "owg1", "opti", "oran", "p800", "pand", "pg-1", "pg-2", "pg-3",
            "pg-6", "pg-8", "pg-c", "pg13", "phil", "pn-2", "pt-g", "palm", "pana",
            "pire", "pock", "pose", "psio", "qa-a", "qc-2", "qc-3", "qc-5", "qc-7",
            "qc07", "qc12", "qc21", "qc32", "qc60", "qci-", "qwap", "qtek", "r380",
            "r600", "raks", "rim9", "rove", "s55/", "sage", "sams", "sc01", "sch-",
            "scp-", "sdk/", "se47", "sec-", "sec0", "sec1", "semc", "sgh-", "shar",
            "sie-", "sk-0", "sl45", "slid", "smb3", "smt5", "sp01", "sph-", "spv ",
            "spv-", "sy01", "samm", "sany", "sava", "scoo", "send", "siem", "smar",
            "smit", "soft", "sony", "t-mo", "t218", "t250", "t600", "t610", "t618",
            "tcl-", "tdg-", "telm", "tim-", "ts70", "tsm-", "tsm3", "tsm5", "tx-9",
            "tagt", "talk", "teli", "topl", "hiba", "up.b", "upg1", "utst", "v400",
            "v750", "veri", "vk-v", "vk40", "vk50", "vk52", "vk53", "vm40", "vx98",
            "virg", "vite", "voda", "vulc", "w3c ", "w3c-", "wapj", "wapp", "wapu",
            "wapm", "wig ", "wapi", "wapr", "wapv", "wapy", "wapa", "waps", "wapt",
            "winc", "winw", "wonu", "x700", "xda2", "xdag", "yas-", "your", "zte-",
            "zeto", "aste", "audi", "avan", "blaz", "brew", "brvw", "bumb", "ccwa",
            "cell", "cldc", "cmd-", "dang", "eml2", "fetc", "hipt", "http", "ibro",
            "idea", "ikom", "ipaq", "jbro", "jemu", "jigs", "keji", "kyoc", "kyok",
            "libw", "m-cr", "midp", "mmef", "moto", "mwbp", "mywa", "newt", "nok6",
            "o2im", "pant", "pdxg", "play", "pluc", "port", "prox", "rozo", "sama",
            "seri", "smal", "symb", "tosh", "treo", "upsi", "vx52", "vx53", "vx60",
            "vx61", "vx70", "vx80", "vx81", "vx83", "vx85", "wap-", "webc", "whit",
            "wmlb", "xda-"
        );
        $mc = array(
            "/ipad/", "/ipod/", "/iphone/", "/android/", "/opera mini/", "/blackberry/",
            "/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/",
            "/(htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc)/",
            "/(kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola)/",
            "/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/",
            "/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/",
            "/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380)/",
            "/(lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99)/",
            "/(d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9)/",
            "/(a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800)/",
            "/(471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810)/",
            "/(m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three)/",
            "/(8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i)/",
            "/(s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100)/",
            "/(bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88)/"
        );
        foreach($mc as $uc) {
            if (preg_match($uc, $ua)) return true;
        }
        return (isset($_SERVER["HTTP_X_WAP_PROFILE"]) || isset($_SERVER["HTTP_PROFILE"]) || strpos($ac, "text/vnd.wap") || in_array(substr($ua, 0, 4), $ma)) ? true : false;
    }
}
?>