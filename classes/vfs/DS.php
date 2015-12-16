<?php
/**
 * @singleton
 * @secure-service
 * @no-context
 */
final class vfs_DS {
    /**
     * @ajax
     * @ignore
     */
    public function getNamespace() {
        $cx = req()->context;
        $ns = array();
        foreach($_SESSION["SPC"] as $k => $v) {
            if (strpos($k, $cx) === 0) $ns[$v["type"]."@".$k] = array($v["name"], $v["info"]);
        }

        return $ns;
    }

    /**
     * @ajax
     * @ignore
     */
    public function menu() {return $_SESSION["TrH"][req()->context];}

    /**
     * @ajax
     * @ignore
     */
    public function gender() {
        return array("" => array("value" => "PILIH", "selected" => true), "L" => "1. LAKI-LAKI", "P" => "2. PEREMPUAN");
    }

    /**
     * @ajax
     * @ignore
     */
    public function status() {
        return array("" => array("value" => "PILIH", "selected" => true), "1" => "Y", "0" => "N");
    }

    /**
     * @ajax
     * @ignore
     */
    public function modules() {
        $conn = DB::lookup("vfs");
        $rs = $conn->vfs_packages->orderBy("PKG ASC")->select();
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("CODE")] = sprintf("%s. %s", $rs->get("PKG"), $rs->get("NAME"));
        }

        return $ds;
    }

    /**
     * @ajax
     * @ignore
     */
    public function packages() {
        $conn = DB::lookup("vfs");
        $rs = $conn->vfs_packages->orderBy("PKG ASC")->select();
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("PKG")] = sprintf("%s. %s", $rs->get("PKG"), $rs->get("NAME"));
        }

        return $ds;
    }

    /**
     * @ajax
     * @ignore
     */
    public function parentPackages() {
        extract(dbm()->argv(array(
            "PKG" => array("req" => true, "type" => "alnum")
        )));
        $conn = DB::lookup("vfs");
        $conn->vfs_packages_classes(
            sprintf("PKG='%s' AND PKG<>'0' AND TYPE IN ('C', 'O')", $PKG)
        );
        $rs = $conn->orderBy("PKG ASC")->select();
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("PKC")] = sprintf("%s. %s", $rs->get("PKC"), $rs->get("NAME"));
        }

        return $ds;
    }

    /**
     * @ajax
     * @ignore
     */
    public function parentClassPackages() {
        extract(dbm()->argv(array(
            "PKG" => array("req" => true, "type" => "alnum")
        )));
        $conn = DB::lookup("vfs");
        $conn->vfs_packages_classes(
            sprintf("PKG='%s' AND PKG<>'0' AND FTR='0' AND TYPE NOT IN ('C', 'O')", $PKG)
        );
        $rs = $conn->orderBy("PKG ASC")->select();
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("PKC")] = sprintf("%s. %s", $rs->get("PKC"), $rs->get("NAME"));
        }

        return $ds;
    }

    /**
     * @ajax
     * @ignore
     */
    public function packagesClassesFeatures() {
        extract(dbm()->argv(array(
            "PKG" => array("req" => true, "type" => "alnum")
        )));
        $conn = DB::lookup("vfs");
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_packages_classes WHERE PKG='%s' AND FTR='1' AND PKC NOT IN (SELECT FTR FROM vfs_packages_classes_features) ORDER BY PKC ASC",
            $PKG
        );
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("PKC")] = sprintf("%s. %s", $rs->get("PKC"), $rs->get("NAME"));
        }

        return $ds;
    }

    static private $_profiles = null;

    /**
     * @ajax
     * @ignore
     */
    public function profiles() {
        $ky = "vfs_DS_profiles";
        $ds = apc_fetch($ky);
        if ($ds !== false) {
            return (self::$_profiles = $ds);
        }
        $conn = DB::lookup("vfs");
        $rs = $conn->vfs_profiles->orderBy("PID ASC")->select();
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("PID")] = sprintf("%s. %s", $rs->get("PID"), $rs->get("NAME"));
        }
        apc_store($ky, $ds);

        return $ds;
    }

    static private $_groups = null;

    /**
     * @ajax
     * @ignore
     */
    public function groups() {
        $ky = "vfs_DS_groups";
        $ds = apc_fetch($ky);
        if ($ds !== false) {
            return (self::$_groups = $ds);
        }
        $conn = DB::lookup("vfs");
        $rs = $conn->vfs_groups->orderBy("GRP ASC")->select();
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("GRP")] = sprintf("%s. %s", $rs->get("GRP"), $rs->get("NAME"));
        }
        apc_store($ky, $ds);

        return $ds;
    }

    static private $_positions = null;

    /**
     * @ajax
     * @ignore
     */
    public function positions() {
        if (!is_null(self::$_positions)) return self::$_positions;
        $ky = "vfs_DS_positions";
        $ds = apc_fetch($ky);
        if ($ds !== false) {
            return (self::$_positions = $ds);
        }
        $conn = DB::lookup("vfs");
        $rs = $conn->executeQuery(
            "SELECT ID_POSITION, NAME FROM vfs_positions WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL) ORDER BY ID_POSITION ASC"
        );
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("ID_POSITION")] = sprintf("%s. %s", $rs->get("ID_POSITION"), $rs->get("NAME"));
        }
        apc_store($ky, $ds);

        return $ds;
    }

    static private $_area = null;

    /**
     * @ajax
     * @ignore
     */
    public function area($_raw = false) {
        if (!is_null(self::$_area)) return self::$_area;
        $ky = "vfs_DS_area";
        $ds = apc_fetch($ky);
        if ($ds !== false) {
            return (self::$_area = $ds);
        }
        $ds = ($_raw) ? null : array("" => array("value" => "PILIH", "selected" => true));
        $conn = DB::lookup("vfs");
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_area WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL)"
        );
        while($rs->next()) {
            $ds[$rs->get("AREA")] = ($_raw) ? $rs->get("NAME") : sprintf("%s. %s", $rs->get("AREA"), strtoupper($rs->get("NAME")));
        }
        apc_store($ky, $ds);

        return $ds;
    }

    /**
     * @ajax
     * @ignore
     */
    public function subArea() {
        extract(dbm()->argv(array(
            "AREA" => array("type" => "digit")
        )));
        $ds = array("" => array("value" => "PILIH", "selected" => true));
        if (!isset($AREA)) return $ds;
        $conn = DB::lookup("vfs");
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_area_sub WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL) AND AREA='%s'",
            $AREA
        );
        while($rs->next()) {
            $ds[$rs->get("AREA_SUB")] = sprintf("%s. %s", $rs->get("AREA_SUB"), strtoupper($rs->get("NAME")));
        }

        return $ds;
    }

    static public function subAreaAll() {
        $conn = DB::lookup("vfs");
        $ds = array();
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_area_sub WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL)"
        );
        while($rs->next()) {
            $ds[$rs->get("AREA_SUB")] = sprintf("%s. %s", $rs->get("AREA_SUB"), strtoupper($rs->get("NAME")));
        }

        return $ds;
    }

    /**
     * @ajax
     * @ignore
     */
    public function name() {
        extract(dbm()->argv(array(
            "GET_NAME" => array("req" => true)
        )));
        $ref = array();
        $map = explode(";", $GET_NAME);
        $val = array();
        foreach($map as $key) {
            if (!empty(req()->$key)) $val[$key] = addslashes(req()->$key);
        }
        if (sizeof($val) > 0) {
            $conn = DB::lookup("if");
            $key = array_unique(array_values($val));
            $dat = implode("','", $key);
            $_row = $conn->executeQuery(sprintf("SELECT `USR`, `NAME` FROM vfs_users WHERE USR IN ('%s')", $dat));
            while($_row->next()) {
                foreach($val as $k => $v) {
                    if ($_row->get("USR") == $v) $ref[$k."_TEXT"] = $_row->get("NAME");
                }
            }
        }

        return $ref;
    }

    static private $_dt1 = null;

    /**
     * @ajax
     * @ignore
     */
    public function dt1($_raw = false) {
        if (!is_null(self::$_dt1)) return self::$_dt1;
        $ky = "vfs_DS_dt1";
        $ds = apc_fetch($ky);
        if ($ds !== false) {
            return (self::$_dt1 = $ds);
        }
        $ds = ($_raw) ? null : array("" => array("value" => "PILIH", "selected" => true));
        $conn = DB::lookup("vfs");
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_dt1 WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL)"
        );
        while($rs->next()) {
            $ds[$rs->get("ID_DT1")] = ($_raw) ? $rs->get("DT1_NAME") : sprintf("%s. %s", $rs->get("ID_DT1"), strtoupper($rs->get("DT1_NAME")));
        }
        apc_store($ky, $ds);

        return $ds;
    }

    static public function dt1All() {
        $conn = DB::lookup("vfs");
        $ds = array();
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_dt1 WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL)"
        );
        while($rs->next()) {
            $ds[$rs->get("ID_DT1")] = ($_raw) ? $rs->get("DT1_NAME") : sprintf("%s. %s", $rs->get("ID_DT1"), strtoupper($rs->get("DT1_NAME")));
        }

        return $ds;
    }
}
?>