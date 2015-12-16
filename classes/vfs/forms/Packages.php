<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_Packages {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "PACKAGE BERHASIL DISIMPAN",
        "PUT" => "PACKAGE BERHASIL DIUPDATE",
        "DELETE" => "PACKAGE BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "Packages.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_packages    {"only": ["PKG"]}
     * @env
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_packages($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(dbm()->vfs_packages->map($rs));
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "PACKAGE %s %s DITEMUKAN", $argv["PKG"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_packages    {"slice": ["PKG"]}
     * @env
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_packages($argv)->insert();
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
     * @argv    vfs_packages
     * @env
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_packages($argv)->slice("PKG")
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
     * @argv    vfs_packages    {"only": ["PKG"]}
     * @env
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_packages($argv)->delete();
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