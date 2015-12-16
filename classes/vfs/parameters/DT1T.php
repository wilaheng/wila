<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_DT1T {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_ID_DT1", "width" => "50", "value" => "DT1", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_DT1_NAME", "width" => "250", "value" => "PROPINSI"),
                    array("name" => "T_BEGDA", "width" => "100", "value" => "BEGDA", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_ENDDA", "width" => "100", "value" => "ENDDA")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 300,
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        $rows = null;
        $rs = $conn->vfs_dt1()->orderBy("ID_DT1 ASC")->select();
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("ID_DT1", null, "center", "vfs/parameters/DT1/GET", $rs->httpQuery("ID_DT1")),
                $rs->get("DT1_NAME"),
                $rs->getCell("BEGDA", null, "center"),
                $rs->getCell("ENDDA", null, "center")
            );
        }

        return array(
            "rows" => $rows
        );
    }
}
?>