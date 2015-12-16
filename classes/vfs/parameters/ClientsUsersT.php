<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_parameters_ClientsUsersT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_CID", "width" => "80", "value" => "CID"),
                    array("name" => "T_USR", "width" => "100", "value" => "USER", "column-backgroundcolor" => "#ffc"),
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
        $rs = $conn->vfs_clients_users()->orderBy("ID_USR, CID")->limit($limit, $offset)->select();
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("CID", null, "right", "vfs/parameters/ClientsUsers/GET", $rs->httpQuery("CID")),
                $rs->get("ID_USR"),
                $rs->getCell("BEGDA", null, "center"),
                $rs->getCell("ENDDA", null, "center")
            );
        }
        $re["rows"] = $rows;
        if ($offset == 0) {
            $rs = $conn->executeQuery("SELECT COUNT(*) T FROM vfs_clients_users");
            $re["total"] = ($rs->next()) ? $rs->getInt("T") : 0;
            $re["limit"] = $limit;
        }

        return $re;
    }
}
?>