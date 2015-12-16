<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_PackagesClasses {
    /* resources */
    private $res = array("TABLE");

    /* messages */
    private $msg = array(
        "POST" => "PACKAGE CLASS BERHASIL DISIMPAN",
        "PUT" => "PACKAGE CLASS BERHASIL DIUPDATE",
        "DELETE" => "PACKAGE CLASS BERHASIL DIDELETE"
    );

    /**
     * @ajax
     */
    public function HEAD() {
        require_once "PackagesClasses.html";
    }

    /**
     * @conn    vfs
     * @argv    vfs_packages_classes    {"only": ["PKG", "PID", "PKC"]}
     * @env
     */
    public function GET($conn, $argv, $_env) {
        $rs = $conn->vfs_packages_classes($argv)->select();
        if ($_chk = $rs->next()) {
            $_env->data(
                dbm()->vfs_packages_classes->copy("PKG", "PID")->map($rs)
            );
        }
        $_env->ctrl(false, $_chk, $_chk);

        return $_env->info(
            "PACKAGE %s.%s.%s %s DITEMUKAN", $argv["PKG"], $argv["PID"], $argv["PKC"], ($_chk) ? "BERHASIL" : "TIDAK"
        );
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_packages_classes
     * @env
     */
    public function POST($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_packages_classes($argv)->insert();
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
     * @argv    vfs_packages_classes    {"copy": ["PKG", "PID"]}
     * @env
     */
    public function PUT($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->update(
                $conn->vfs_packages_classes($argv)->slice("PKG", "PID", "PKC")
            );
            $conn->commit();
            $_env->push($this->msg[__FUNCTION__], $this->res);
            $_env->data(array(
                "PKG" => array("value" => $argv["PKG"], "editable" => true),
                "PID" => array("value" => $argv["PID"], "editable" => true)
            ));
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @rest
     * @conn    vfs
     * @argv    vfs_packages_classes    {"copy": ["PKG", "PID"], "only": ["PKG", "PID", "PKC"]}
     * @env
     */
    public function DELETE($conn, $argv, $_env) {
        try {
            $conn->begin();
            $conn->vfs_packages_classes($argv)->delete();
            $conn->commit();
            $data = dbm()->vfs_packages_classes->nullify($argv);
            $data["PKG"]["editable"] = $data["PID"]["editable"] = true;
            $_env->data($data);
            $_env->push($this->msg[__FUNCTION__], $this->res);
        } catch(Exception $e) {
            $conn->rollback();
            $_env = $e->getMessage();
        }

        return $_env;
    }

    /**
     * @conn    vfs
     */
    public function DOWNLOAD($conn) {
        $_out = "# packages_classes\r\n";
        $rs = $conn->vfs_packages_classes->select();
        while($rs->next()) {
            $_out.= $rs->insertStatement();
        }
        $_out.= "\r\n";
        $_out.= "# groups_classes\r\n";
        $rz = $conn->vfs_groups_classes->select();
        while($rz->next()) {
            $_out.= $rz->insertStatement();
        }
        send_attach("packages_classes.txt", $_out);
    }

    /**
     * @conn    vfs
     * @argv    vfs_packages_classes    {"only": ["PKG", "PID", "PKC"]}
     */
    public function SQL($conn, $argv) {
        $rs = $conn->vfs_packages_classes($argv)->select();
        if ($rs->next()) {
            print "<pre>";
            print "# packages_classes\r\n";
            print $rs->insertStatement();
            unset($argv["PKG"]);
            unset($argv["PID"]);
            print "\r\n";
            print "# packages_classes_features\r\n";
            $rc = $conn->vfs_packages_classes_features($argv)->select();
            $in = array($argv["PKC"]);
            while($rc->next()) {
                array_push($in, $rc->get("FTR"));
                print $rc->insertStatement();
            }
            print "\r\n";
            print "# groups_classes\r\n";
            $conn->vfs_groups_classes;
            $rz = $conn->executeQuery("SELECT * FROM vfs_groups_classes WHERE PKC IN (%s)", implode(",", $in));
            while($rz->next()) {
                print $rz->insertStatement();
            }
            print "</pre>";
        }
    }
}
?>