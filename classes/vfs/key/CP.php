<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_key_CP {
    static private $PWD_PATTERN = "#.*^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).*$#";

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "CP.html";
    }

    /**
     * @ajax
     */
    public function check() {
        extract(dbm()->argv(array(
            "PWD" => array("req" => true)
        )));
        $src = sprintf("asset/16/actions/dialog-%s.gif?%s", (preg_match(self::$PWD_PATTERN, $PWD)) ? "apply" : "cancel", time());

        return array(
            "PWD_IMG" => array("src" => $src)
        );
    }

    /**
     * @ajax
     */
    public function reload() {
        return array(
            "CAP_IMG" => array("src" => "./vfs/key/CC/?" . time())
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @enve
     */
    public function PUT($conn, $_env) {
        if (!isset($_SESSION["CC"])) return "NO CAPTCHA AVAILABLE.";
        extract(dbm()->argv(array(
            "PWD" => array("req" => true),
            "PWD_CONFIRM" => array("req" => true),
            "CAPTCHA" => array("req" => true)
        )));
        if ($PWD !== $PWD_CONFIRM) return "KONFIRMASI PASSWORD TIDAK SAMA";
        if ($CAPTCHA !== $_SESSION["CC"]) return "CAPTCHA YANG ANDA MASUKKAN SALAH";
        if (!preg_match(self::$PWD_PATTERN, $PWD)) return "PASSWORD MINIMAL 8 KARAKTER, TERDIRI DARI HURUF KECIL + BESAR DAN ANGKA";
        $rc = $conn->executeQuery(
            "SELECT * FROM vfs_users_logs WHERE USR='%s' ORDER BY UID DESC LIMIT 0,3", $_SESSION["USR"]
        );
        $PWD = md5($PWD_CONFIRM);
        while($rc->next()) {
            if ($PWD == $rc->get("PWD")) {
                return "PASSWORD BARU TIDAK BOLEH SAMA DENGAN 3 PASSWORD SEBELUMNYA";
            }
        }
        $_now = date("Y-m-d H:i:s");
        $argv = array(
            "USR" => $_SESSION["USR"],
            "PWD" => $PWD,
            "EXP" => date("Y-m-d", time() + 60 * 60 * 24 * 90),
            "U_USER" => $_SESSION["USR"],
            "U_DATE" => $_now
        );
        try {
            $conn->begin();
            $conn->update($conn->vfs_users($argv)->slice("USR"));
            $conn->vfs_users_logs(array(
                "USR" => $_SESSION["USR"],
                "PWD" => $PWD,
                "C_DATE" => $_now
            ))->insert();
            $conn->commit();
            $_env->warn("PASSWORD BERHASIL DIUBAH")->reset();
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @ajax
     */
    public function RESET() {
        return array(
            "PWD_IMG" => array("src" => "asset/16/actions/decrypt.gif"),
            "CAP_IMG" => array("src" => "./vfs/key/CC/?" . time())
        );
    }
}
?>