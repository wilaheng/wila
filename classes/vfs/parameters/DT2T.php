<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_DT2T {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_ID_DT2", "width" => "50", "value" => "DT2", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_DT2_NAME", "width" => "250", "value" => "KABUPATEN"),
                    array("name" => "T_BEGDA", "width" => "100", "value" => "BEGDA", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_ENDDA", "width" => "100", "value" => "ENDDA")
                )
            ),
            "info" => array(
                "initialize" => false, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 300,
                "limit" => 0, "total" => 0,
                "depends" => "ID_DT1"
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        $argv = dbm()->argv(array(
            "ID_DT1" => array("type" => "numeric", "minl" => 2, "maxl" => 2)
        ));
        $rows = null;
        if (sizeof($argv)) {
            $rs = $conn->vfs_dt2($argv)->orderBy("ID_DT1, ID_DT2")->select();
            while($rs->next()) {
                $rows[] = array(
                    $rs->getCell("ID_DT2", null, "center", "vfs/parameters/DT2/GET", $rs->httpQuery("ID_DT1", "ID_DT2")),
                    $rs->get("DT2_NAME"),
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