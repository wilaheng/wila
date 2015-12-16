<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_AreaSubT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_AREA", "width" => "60", "value" => "SUB.A", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_NAME", "width" => "300", "value" => "NAME"),
                    array("name" => "T_ADDR", "width" => "400", "value" => "ADDR", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_BEGDA", "width" => "100", "value" => "BEGDA"),
                    array("name" => "T_ENDDA", "width" => "100", "value" => "ENDDA")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 200,
                "limit" => 0, "total" => 0,
                "depends" => "AREA"
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        $argv = dbm()->argv(array(
            "AREA" => array("type" => "numeric", "minl" => 2, "maxl" => 2)
        ));
        $rows = null;
        if (sizeof($argv)) {
            $rs = $conn->vfs_area_sub($argv)->orderBy("AREA_SUB ASC")->select();
            while($rs->next()) {
                $rows[] = array(
                    $rs->getCell("AREA_SUB", null, "center", "vfs/parameters/AreaSub/GET", $rs->httpQuery("AREA", "AREA_SUB")),
                    $rs->get("NAME"),
                    $rs->get("ADDR"),
                    $rs->getCell("BEGDA", null, "center"),
                    $rs->getCell("ENDDA", null, "center")
                );
            }
        }

        return array(
            "rows" => $rows
        );
    }
}
?>