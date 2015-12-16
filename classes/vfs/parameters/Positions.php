<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_parameters_Positions {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "POSITION BERHASIL DISIMPAN",
        "PUT" => "POSITION BERHASIL DIUPDATE",
        "DELETE" => "POSITION BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "Positions.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_positions    {"only": ["ID_POSITION"]}
     * @enve
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_positions($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(
                dbm()->vfs_positions->copy("ID_POSITION")->map($rs)
            );
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "PACKAGE %s %s DITEMUKAN", $argv["ID_POSITION"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_positions
     * @enve
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_positions($argv)->insert();
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
     * @argv    vfs_positions    {"copy": ["ID_POSITION"]}
     * @enve
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_positions($argv)->slice("ID_POSITION")
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
     * @argv    vfs_positions    {"copyOnly": ["ID_POSITION"]}
     * @enve
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_positions($argv)->delete();
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