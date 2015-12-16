<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_Users {
    /* resources */
    private $res = array("GRP");

    /* messages */
    private $msg = array(
        "POST" => "USER BERHASIL DISIMPAN",
        "PUT" => "USER BERHASIL DIUPDATE",
        "DELETE" => "USER BERHASIL DIDELETE",
        "DELETE_ERR" => "FATAL ERROR: LOG ACTIVITY USER HARUS DIHAPUS TERLEBIH DAHULU OLEH SYSADMIN."
    );

    /* TO-DOC */
    private function CLEAR() {
        if (isset($_SESSION["vfs_forms_Users_Group"])) unset($_SESSION["vfs_forms_Users_Group"]);
    }

    /**
     * @ajax
     */
    public function HEAD() {
        $this->CLEAR();
        require_once "Users.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_users    {"only": ["USR"]}
     * @env
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_users u WHERE u.USR='%s' ORDER BY ID_USR LIMIT 1", $argv["USR"]
        );
        $dt = null;
        $gr = array();
        if ($_chk = $rs->next()) {
            $mt = dbm()->vfs_users->copy("USR");
            $mt->slice("PWD", "PICT");
            $dt = $mt->map($rs);
            $r2 = $conn->executeQuery(
                "SELECT g.* FROM vfs_groups g, vfs_users_groups u WHERE g.BEGDA<=CURRENT_DATE AND (g.ENDDA>=CURRENT_DATE OR g.ENDDA IS NULL) AND g.GRP=u.GRP AND u.USR='%s'", $argv["USR"]
            );
            $_SESSION["vfs_forms_Users_Group"] = array();
            while($r2->next()) {
                $_SESSION["vfs_forms_Users_Group"][$r2->get("GRP")] = $r2->get("NAME");
                $GRP = $r2->getInt("GRP");
                $_gr = array(
                    array("value" => $GRP, "align" => "center"), $r2->get("NAME")
                );
                array_push($_gr, ($GRP == 1100) ? STR_EMPTY : array(
                    "value" => "DELETE",
                    "callback" => "vfs/forms/UsersGroupsT/delClick",
                    "params" => "GRP=" . $GRP,
                    "align" => "center"
                ));
                $gr[] = $_gr;
            }
            $_env->call($this->res);
        }
        $dt["USER_GROUPS"] = $gr;
        $_env->data($dt)->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "USER %s %s DITEMUKAN", $argv["USR"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_users    {"slice": ["C_USER", "C_DATE", "U_USER", "U_DATE", "ENDDA"], "push": ["PWD_CONFIRM", {"req": true}]}
     * @env
     */
    public function POST($conn, $argv, $_env) {
        if ($argv["PWD"] != $argv["PWD_CONFIRM"]) return "INVALID PASSWORD CONFIRM";
        unset($argv["PWD_CONFIRM"]);
        $argv["EXP"] = DB::constant("CURRENT_DATE");
        $argv["PWD"] = md5($argv["PWD"]);
        $argv["C_DATE"] = DB::constant("CURRENT_TIMESTAMP");
        $argv["C_USER"] = $_SESSION["USR"];
        try {
            $conn->begin();
            $conn->vfs_users($argv)->insert();
            $conn->executeUpdate(
                "INSERT INTO vfs_users_logs (`USR`, `PWD`, `C_DATE`) VALUES ('%s', '%s', NOW())", $argv["USR"], $argv["PWD"]
            );
            if (!isset($_SESSION["vfs_forms_Users_Group"][1100])) $_SESSION["vfs_forms_Users_Group"][1100] = "Kypass";
            foreach($_SESSION["vfs_forms_Users_Group"] as $GRP => $GRP_NAME)
            {
                $conn->executeUpdate("INSERT INTO vfs_groups_users (`USR`, `GRP`) VALUES ('%s', '%s')", $argv["USR"], $GRP);
            }
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
            $this->CLEAR();
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_users    {"copy": ["USR"], "slice": ["PWD", "C_USER", "C_DATE", "U_USER", "U_DATE"], "push": ["PWD", {"req": false}], "push": ["PWD_CONFIRM", {"req": false}]}
     * @env
     */
    public function PUT($conn, $argv, $_env) {
        if (isset($argv["PWD"])) {
            $message = "INVALID PASSWORD CONFIRM";
            if (!isset($argv["PWD_CONFIRM"])) return $message;
            if ($argv["PWD"] !== $argv["PWD_CONFIRM"]) return $message;
            $argv["PWD"] = md5($argv["PWD_CONFIRM"]);
            $argv["EXP"] = (isset($argv["F2CPWD"])) ? DB::constant("CURRENT_DATE") : date("Y-m-d", time() + 60 * 60 * 24 * 30 * 3);
        }
        if (isset($argv["PWD_CONFIRM"])) unset($argv["PWD_CONFIRM"]); // no copy
        $argv["U_DATE"] = DB::constant("CURRENT_TIMESTAMP");
        $argv["U_USER"] = $_SESSION["USR"];
        if (!isset($argv["ENDDA"])) $argv["ENDDA"] = DB::constant("NULL");
        $conn = DB::lookup("vfs");
        try {
            $conn->begin();
            $conn->update($conn->vfs_users($argv)->slice("USR"));
            $conn->executeUpdate("DELETE FROM vfs_groups_users WHERE USR='%s'", $argv["USR"]);
            if (!isset($_SESSION["vfs_forms_Users_Group"][1100])) $_SESSION["vfs_forms_Users_Group"][1100] = "Kypass";
            foreach($_SESSION["vfs_forms_Users_Group"] as $GRP => $GRP_NAME)
            {
                $conn->executeUpdate("INSERT INTO vfs_groups_users (`USR`, `GRP`) VALUES ('%s', '%s')", $argv["USR"], $GRP);
            }
            $conn->commit();
            $_env->warn("DATA BERHASIL DIUPDATE");
            $this->CLEAR();
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_users    {"copyOnly": ["USR"]}
     * @env
     */
    public function DELETE($conn, $argv, $_env) {
        $rs = $conn->vfs_users_logs($argv)->select("*");
        if ($rs->next()) {
            return $this->msg["DELETE_ERR"];
        }
        try {
            $conn->begin();
            $conn->vfs_users($argv)->delete();
            $conn->vfs_groups_users($argv)->delete();
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
            $_env->reset();
            $this->CLEAR();
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @ajax
     */
    public function RESET() {
        $this->CLEAR();
  
        return env()->call($this->res);
    }
}
?>