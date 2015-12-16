<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_ClientsT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_CID", "width" => "60", "value" => "CID"),
                    array("name" => "T_ADDR", "width" => "200", "value" => "ADDR", "column-backgroundcolor" => "#ffc"),
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
        $rs = $conn->vfs_clients()->orderBy("CID ASC")->limit($limit, $offset)->select();
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("CID", null, "right", "vfs/parameters/Clients/GET", $rs->httpQuery("CID")),
                $rs->get("ADDR"),
                $rs->getCell("BEGDA", null, "center"),
                $rs->getCell("ENDDA", null, "center")
            );
        }
        $re["rows"] = $rows;
        if ($offset == 0) {
            $rs = $conn->executeQuery("SELECT COUNT(*) T FROM vfs_clients");
            $re["total"] = ($rs->next()) ? $rs->getInt("T") : 0;
            $re["limit"] = $limit;
        }

        return $re;
    }
}
?>