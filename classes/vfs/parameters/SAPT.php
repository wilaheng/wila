<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_SAPT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_AREA", "width" => "50", "value" => "AREA", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_AREA_SUB", "width" => "350", "value" => "SUB AREA"),
                    array("name" => "T_BUS_AREA", "width" => "100", "value" => "BUS AREA", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_COST_CTR", "width" => "100", "value" => "COST CTR"),
                    array("name" => "T_PRFT_CTR", "width" => "100", "value" => "PRFT CTR", "column-backgroundcolor" => "#ffc")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 250,
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
        $rs = $conn->vfs_sap()->orderBy("AREA, AREA_SUB")->select();
        if ($rs->getRecordCount() > 0) {
            $rs->setMap("AREA_SUB", vfs_DS::subAreaAll());
        }
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("AREA", null, "center", "vfs/parameters/SAP/GET", $rs->httpQuery("AREA", "AREA_SUB")),
                $rs->withMap("AREA_SUB"),
                $rs->getCell("BUS_AREA", null, "center"),
                $rs->getCell("COST_CTR", null, "center"),
                $rs->getCell("PRFT_CTR", null, "center")
            );
        }

        return array(
            "rows" => $rows
        );
    }
}
?>