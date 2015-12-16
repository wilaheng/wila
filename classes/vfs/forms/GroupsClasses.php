<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_GroupsClasses {
    /* resources */
    private $res = array("TABLE", "PKC");

    /* messages */
    private $msg = array(
        "POST" => "GROUP CLASS BERHASIL DISIMPAN",
        "PUT" => "GROUP CLASS BERHASIL DIUPDATE",
        "DELETE" => "GROUP CLASS BERHASIL DIDELETE"
    );

    /**
     * @conn    vfs
     */
    public function groups($conn) {
        extract(dbm()->argv(array(
            "PKG" => array("req" => true, "type" => "digit", "minv" => 1)
        )));
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_groups WHERE GRP IN (SELECT DISTINCT GRP FROM vfs_groups_classes WHERE PKG='%s' AND BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL))", $PKG
        );
        $ds = array();
        while($rs->next()) {
            $ds[$rs->get("GRP")] = sprintf("%s. %s", $rs->get("GRP"), $rs->get("NAME"));
        }
        if (sizeof($ds) == 0) {
            $rs = $conn->executeQuery("SELECT * FROM vfs_groups WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL)");
            while($rs->next()) {
                $ds[$rs->get("GRP")] = sprintf("%s. %s", $rs->get("GRP"), $rs->get("NAME"));
            }
        }
        $ds[""] = array("value" => "PILIH", "selected" => true);

        return $ds;
    }

    /**
     * @conn    vfs
     */
    public function classes($conn) {
        $ds = array("" => "PILIH");
        if (empty(req()->PKG) || empty(req()->GRP)) return $ds;
        $rs = $conn->executeQuery(
            "SELECT * FROM vfs_packages_classes WHERE PKG='%s' AND PKC NOT IN (SELECT PKC FROM vfs_groups_classes WHERE GRP='%s' AND PKG='%s')",
            req()->PKG, req()->GRP, req()->PKG
        );
        while($rs->next()) {
            $ds[$rs->get("PKC")] = sprintf("%s. %s", $rs->get("PKC"), $rs->get("NAME"));
        }

        return $ds;
    }

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "GroupsClasses.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_groups_classes  {"slice": ["ACL"]}
     * @env
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_groups_classes($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(
                dbm()->vfs_groups_classes->copy("GRP", "PKG", "PKC")->map($rs)
            );
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "GROUP %d PKG %d CLASS %d %s DITEMUKAN", $argv["GRP"], $argv["PKG"], $argv["PKC"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_groups_classes
     * @env
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_groups_classes($argv)->insert();
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_groups_classes  {"copy": ["GRP", "PKG", "PKC"]}
     * @env
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_groups_classes($argv)->slice("GRP", "PKG", "PKC")
            );
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_groups_classes  {"copy": ["GRP", "PKG", "PKC"], "slice": ["ACL"]}
     * @env
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_groups_classes($argv)->delete();
            $conn->commit();
            $data = dbm()->vfs_groups_classes->nullify($argv);
            $data["GRP"]["editable"] = $data["PKG"]["editable"] = $data["PKC"]["editable"] = true;
            $_env->data($data);
            $_env->push($this->msg[__FUNCTION__], $this->res);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }
}
?>