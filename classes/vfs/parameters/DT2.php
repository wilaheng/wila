<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_parameters_DT2 {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "KAB/KOTA BERHASIL DISIMPAN",
        "PUT" => "KAB/KOTA BERHASIL DIUPDATE",
        "DELETE" => "KAB/KOTA BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "DT2.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_dt2    {"only": ["ID_DT1", "ID_DT2"]}
     * @enve
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_dt2($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(dbm()->vfs_dt2->copy("ID_DT1", "ID_DT2")->map($rs));
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "KAB/KOTA %s %s DITEMUKAN", $argv["ID_DT2"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_dt2
     * @enve
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_dt2($argv)->insert();
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
     * @argv    vfs_dt2    {"copy": ["ID_DT1", "ID_DT2"]}
     * @enve
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_dt2($argv)->slice("ID_DT1", "ID_DT2")
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
     * @argv    vfs_dt2    {"copyOnly": ["ID_DT1", "ID_DT2"]}
     * @enve
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_dt2($argv)->delete();
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