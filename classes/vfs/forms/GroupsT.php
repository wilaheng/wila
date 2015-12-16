<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_GroupsT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_GRP", "width" => "80", "value" => "GRP", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_NAME", "width" => "250", "value" => "NAME"),
                    array("name" => "BEGDA", "width" => "100", "value" => "BEGDA", "column-backgroundcolor" => "#ffc"),
                    array("name" => "ENDDA", "width" => "100", "value" => "ENDDA"),
                    array("name" => "C_USER", "width" => "100", "value" => "C_USER", "column-backgroundcolor" => "#ffc"),
                    array("name" => "C_DATE", "width" => "180", "value" => "C_DATE")
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
        $rs = $conn->vfs_groups()->orderBy("GRP ASC")->select();
        $rows = null;
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("GRP", null, "center", "vfs/forms/Groups/GET", $rs->httpQuery("GRP")),
                $rs->get("NAME"),
                $rs->getCell("BEGDA", null, "center"),
                $rs->getCell("ENDDA", null, "center"),
                $rs->getCell("C_USER", null, "center"),
                $rs->getCell("C_DATE", null, "center")
            );
        } 

        return array(
            "rows" => $rows
        );
    }
}
?>