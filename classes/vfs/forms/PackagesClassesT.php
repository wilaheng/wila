<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_PackagesClassesT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_PID", "width" => "80", "value" => "PID"),
                    array("name" => "T_CID", "width" => "80", "value" => "CID", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_FTR", "width" => "50", "value" => "FTR"),
                    array("name" => "T_NAME", "width" => "200", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_DISP", "width" => "200", "value" => "DISPLAY"),
                    array("name" => "T_CHECKED", "width" => "50", "value" => "STAT", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_SQL", "width" => "60")
                )
            ),
            "info" => array(
                "initialize" => false, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 250,
                "depends" => "PKG",   
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        $argv = dbm()->argv(array(
            "PKG" => array("type" => "int", "minv" => 1, "maxv" => 255)
        ));
        $rows = null;
        if (sizeof($argv)) {
            $conn->vfs_packages_classes($argv);
            $rs = $conn->orderBy("PKG ASC, PKC ASC, PID ASC")->select();
            while($rs->next()) {
                $rows[] = array(
                    $rs->getCell("PID", null, "center"),
                    $rs->getCell("PKC", null, "center", "vfs/forms/PackagesClasses/GET", $rs->httpQuery("PKG", "PID", "PKC")),
                    $rs->getCell("FTR", null, "center"),
                    $rs->get("NAME"),
                    $rs->get("DISP"),
                    $rs->getCell("CHECKED", null, "center"),
                    $rs->setCell("SQL", "center", "vfs/forms/PackagesClasses/SQL", $rs->httpQuery("PKG", "PID", "PKC"))
                );
            }
        }

        return array(
            "rows" => $rows
        );
    }
}
?>