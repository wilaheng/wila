<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_parameters_DT1 {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "PROPINSI BERHASIL DISIMPAN",
        "PUT" => "PROPINSI BERHASIL DIUPDATE",
        "DELETE" => "PROPINSI BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "DT1.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_dt1    {"only": ["ID_DT1"]}
     * @enve
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_dt1($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(dbm()->vfs_dt1->map($rs));
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "PROPINSI %s %s DITEMUKAN", $argv["CID"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_dt1
     * @enve
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_dt1($argv)->insert();
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
     * @argv    vfs_dt1
     * @enve
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_dt1($argv)->slice("ID_DT1")
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
     * @argv    vfs_dt1    {"only": ["ID_DT1"]}
     * @enve
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_dt1($argv)->delete();
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