<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_parameters_SAP {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "AREA BERHASIL DISIMPAN",
        "PUT" => "AREA BERHASIL DIUPDATE",
        "DELETE" => "AREA BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "SAP.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_sap    {"only": ["AREA", "AREA_SUB"]}
     * @enve
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_sap($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(dbm()->vfs_sap->map($rs));
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "AREA %s.%s %s DITEMUKAN", $argv["AREA"], $argv["AREA_SUB"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_sap
     * @enve
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_sap($argv)->insert();
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
     * @argv    vfs_sap
     * @enve
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_sap($argv)->slice("AREA", "AREA_SUB")
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
     * @argv    vfs_sap    {"only": ["AREA", "AREA_SUB"]}
     * @enve
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_sap($argv)->delete();
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