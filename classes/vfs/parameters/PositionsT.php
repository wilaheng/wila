<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_PositionsT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_NO", "width" => "60", "value" => "NO"),
                    array("name" => "T_ID_POSITION", "width" => "100", "value" => "ID", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_NAME", "width" => "500", "value" => "NAME"),
                    array("name" => "T_BP_KP", "width" => "80", "value" => "BP/KP", "column-backgroundcolor" => "#ffc"),
                    array("name" => "BEGDA", "width" => "100", "value" => "BEGDA"),
                    array("name" => "ENDDA", "width" => "100", "value" => "ENDDA", "column-backgroundcolor" => "#ffc")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 200,
                "limit" => 50, "total" => 10
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        extract(dbm()->argv(inf()->rows->getMap()));
        $re = $rows = null;
        $no = $offset + 1;
        $rs = $conn->vfs_positions()->orderBy("ID_POSITION ASC")->limit($limit, $offset)->select();
        while($rs->next()) {
            $rows[] = array(
                $rs->setCell($no++, "right"),
                $rs->getCell("ID_POSITION", null, "center", "vfs/parameters/Positions/GET", $rs->httpQuery("ID_POSITION")),
                $rs->get("NAME"),
                $rs->setCell(sprintf("%s/%s", $rs->get("BP"), $rs->get("KP")), "center"),
                $rs->getCell("BEGDA", null, "center"),
                $rs->getCell("ENDDA", null, "center")
            );
        }
        $re["rows"] = $rows;
        if ($offset == 0) {
            $rs = $conn->executeQuery("SELECT COUNT(*) T FROM vfs_positions");
            $re["total"] = ($rs->next()) ? $rs->getInt("T") : 0;
            $re["limit"] = $limit;
        }

        return $re;
    }
}
?>