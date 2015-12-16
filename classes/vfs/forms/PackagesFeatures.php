<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_PackagesFeatures {
    /* resources */
    private $res = array("TABLE", "FTR");

    /* messages */
    private $msg = array(
        "POST" => "PACKAGE FEATURE BERHASIL DISIMPAN",
        "PUT" => "PACKAGE FEATURE BERHASIL DIUPDATE",
        "DELETE" => "PACKAGE FEATURE BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "PackagesFeatures.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_packages_classes_features
     * @env
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_packages_classes_features($argv)->select("*");
        if ($_chk = $rs->next()) {
            $_env->data(
                dbm()->vfs_packages_classes_features->copy("FTR")->map($rs)
            );
        }
        $_env->ctrl(false, false, $_chk);

        return $_env->info(
            "FITUR %s.%s %s DITEMUKAN", $argv["PKC"], $argv["FTR"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_packages_classes_features
     * @env
     */
    public function POST($conn, $argv, $_env) {
        extract(dbm()->argv(array(
            "PKG" => array("req" => true, "type" => "int", "minv" => 1, "maxv" => 255)
        )));
        try {
            $conn->begin();
            $conn->vfs_packages_classes_features($argv)->insert();
            $rs = $conn->executeQuery("SELECT GRP FROM vfs_groups_classes WHERE PKG='%s' AND PKC='%s'", $PKG, $argv["PKC"]);
            while($rs->next()) {
                $rc = $conn->executeQuery("SELECT COUNT(*) T FROM vfs_groups_classes WHERE GRP='%s' AND PKG='%s' AND PKC='%s'", $rs->get("GRP"), $PKG, $argv["FTR"]);
                $tt = ($rc->next()) ? $rc->getInt("T") : 0;
                if ($tt === 0) {
                    $conn->vfs_groups_classes(array(
                        "GRP" => $rs->get("GRP"), "PKG" => $PKG, "PKC" => $argv["FTR"]
                    ))->insert();
                }
            }
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
     */
    public function PUT() {
        return "UPDATE TIDAK TERSEDIA";
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_packages_classes_features   {"copy": ["FTR"]}
     * @env
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_packages_classes_features($argv)->delete();
            $argv["PKC"] = $argv["FTR"];
            unset($argv["FTR"]);
            $conn->vfs_groups_classes($argv)->delete();
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
            $data = dbm()->vfs_packages_classes_features->nullify($argv);
            $data["FTR"]["editable"] = true;
            $_env->data($data);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }
}
?>