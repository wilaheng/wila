<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_UsersSessions {
    /**
     * @ajax
     */
    public function HEAD() {
        require_once "UsersSessions.html";
    }

    /**
     * ubah menjadi delete berdasarkan parameter, bukan iterasi tabel
     *
     * @rest
     * @conn    vfs
     * @env
     */
    public function DELETE($conn, $_env) {
        $_del = 0;
        $_map = array();
        $rs = $conn->executeQuery("SELECT * FROM vfs_users_sessions");
        try {
            while($rs->next()) {
                $check = sprintf("SID_%s", $rs->get("SES"));
                if (!empty(req()->$check)) {
                    array_push($_map, $rs->get("SES"));
                }
            }
            $_del = sizeof($_map);
            $conn->begin();
            $conn->executeUpdate("DELETE FROM vfs_users_sessions WHERE SES IN ('%s')", implode("','", $_map));
            $conn->commit();
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }
        $_env->warn("%d SESSION BERHASIL DIDELETE", $_del);
        $_env->call(array("TABLE"));

        return $_env;
    }
}
?>