<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_Groups {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "GROUP BERHASIL DISIMPAN",
        "PUT" => "GROUP BERHASIL DIUPDATE",
        "DELETE" => "GROUP BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "Groups.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_groups    {"only": ["GRP"]}
     * @env
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_groups($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(
                dbm()->vfs_groups->copy("GRP")->map($rs)
            );
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "GROUP %d %s DITEMUKAN", $argv["GRP"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_groups
     * @env
     */
    public function POST($conn, $argv, $_env) {
        $argv["C_DATE"] = DB::constant("CURRENT_TIMESTAMP");
        $argv["C_USER"] = $_SESSION["USR"];
        try {
            $conn->begin();
            $conn->vfs_groups($argv)->insert();
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
     * @argv    vfs_groups    {"copy": ["GRP"]}
     * @env
     */
    public function PUT($conn, $argv, $_env) {
        if (!isset($argv["ENDDA"])) $argv["ENDDA"] = DB::constant("NULL");
        try {
            $conn->begin();
            $conn->update($conn->vfs_groups($argv)->slice("GRP"));
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
     * @argv    vfs_groups    {"copyOnly": ["GRP"]}
     * @env
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_groups($argv)->delete();
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res)->reset(false);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }
}
?>