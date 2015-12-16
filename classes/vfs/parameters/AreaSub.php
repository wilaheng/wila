<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_parameters_AreaSub {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "SUB AREA BERHASIL DISIMPAN",
        "PUT" => "SUB AREA BERHASIL DIUPDATE",
        "DELETE" => "SUB AREA BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "AreaSub.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_area_sub    {"only": ["AREA", "AREA_SUB"]}
     * @enve
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_area_sub($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(dbm()->vfs_area_sub->copy("AREA", "AREA_SUB")->map($rs));
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "SUB AREA %s %s DITEMUKAN", $argv["AREA_SUB"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_area_sub
     * @enve
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_area_sub($argv)->insert();
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
     * @argv    vfs_area_sub    {"copy": ["AREA", "AREA_SUB"]}
     * @enve
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_area_sub($argv)->slice("AREA", "AREA_SUB")
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
     * @argv    vfs_area_sub    {"copyOnly": ["AREA", "AREA_SUB"]}
     * @enve
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_area_sub($argv)->delete();
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }
}
?>