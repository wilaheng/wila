<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_PackagesFeaturesT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_FTR", "width" => "80", "value" => "FTR"),
                    array("name" => "T_FTR_NAME", "width" => "250", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_FTR_TYPE", "width" => "60", "value" => "TYPE"),
                    array("name" => "T_FTR_DISP", "width" => "200", "value" => "DISP", "column-backgroundcolor" => "#ffc")
                )
            ),
            "info" => array(
                "initialize" => false, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 250,
                "depends" => "PKG;PKC",
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        extract(dbm()->argv(array(
            "PKG" => array("type" => "int", "minv" => 1, "maxv" => 255),
            "PKC" => array("type" => "int")
        )));
        $rows = null;
        if (isset($PKG) && isset($PKC)) {
            $rs = $conn->executeQuery(sprintf(
                "SELECT a.PKG, b.PKC, b.FTR, a.NAME, a.DISP, a.TYPE FROM vfs_packages_classes a, vfs_packages_classes_features b where a.PKC=b.FTR AND a.PKG='%s' AND b.PKC='%s' ORDER BY a.PKG, a.PKC, a.PID",
                $PKG, $PKC
            ));
            while($rs->next()) {
                $rows[] = array(
                    $rs->getCell("FTR", null, "center", "vfs/forms/PackagesFeatures/GET", $rs->httpQuery("PKG", "PKC", "FTR")),
                    $rs->get("NAME"),
                    $rs->getCell("TYPE", null, "center"),
                    $rs->get("DISP")
                );
            }
        }

        return array(
            "rows" => $rows
        );
    }
}
?>