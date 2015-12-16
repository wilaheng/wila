<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_parameters_ClientsUsers {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "CLIENT ADDR BERHASIL DISIMPAN",
        "PUT" => "CLIENT ADDR BERHASIL DIUPDATE",
        "DELETE" => "CLIENT ADDR BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "ClientsUsers.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_clients_users    {"only": ["CID"]}
     * @enve
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_clients_users($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(dbm()->vfs_clients_users->copy("CID")->map($rs));
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "CLIENT USER %s %s DITEMUKAN", $argv["CID"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_clients_users    {"slice": ["CID"]}
     * @enve
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_clients_users($argv)->insert();
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
     * @argv    vfs_clients_users    {"copy": ["CID"]}
     * @enve
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_clients_users($argv)->slice("CID")
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
     * @argv    vfs_clients_users    {"copyOnly": ["CID"]}
     * @enve
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_clients_users($argv)->delete();
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