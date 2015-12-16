<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_AreaT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_AREA", "width" => "50", "value" => "AREA", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_NAME", "width" => "200", "value" => "NAME"),
                    array("name" => "T_ADDR", "width" => "400", "value" => "ADDR", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_BEGDA", "width" => "100", "value" => "BEGDA"),
                    array("name" => "T_ENDDA", "width" => "100", "value" => "ENDDA")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 200,
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
        $rs = $conn->vfs_area()->orderBy("AREA ASC")->select();
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("AREA", null, "center", "vfs/parameters/Area/GET", $rs->httpQuery("AREA")),
                $rs->get("NAME"),
                $rs->get("ADDR"),
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