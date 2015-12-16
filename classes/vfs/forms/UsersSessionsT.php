<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_UsersSessionsT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_PILIH", "width" => "50", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_USR", "width" => "80", "value" => "USER"),
                    array("name" => "T_NAME", "width" => "300", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_SES_ADDR", "width" => "150", "value" => "ADDR"),
                    array("name" => "T_SES_TIME", "width" => "120", "value" => "UTIME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_SES_DATE", "width" => "180", "value" => "DATETIME")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 250,
                "depends" => "USR",
                "limit" => 20, "total" => 1
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        extract(dbm()->argv(
            inf()->rows->push("USR", array("type" => "alnum"))->getMap()
        ));
        $_sql = "SELECT s.*, u.NAME FROM vfs_users_sessions s, vfs_users u WHERE s.USR=u.USR";
        if (isset($USR)) {
            $_usr = $USR."%";
            $_sql.= sprintf(" AND s.USR LIKE '%s' OR u.NAME LIKE '%s'", $_usr, $_usr);
        }
        $_sql.= sprintf(" ORDER BY s.SES_TIME LIMIT %s, %s", $offset, $limit);
        $rs = $conn->executeQuery($_sql);
        $rows = null;
        while($rs->next()) {
            $rows[] = array(
                array(
                    "name" => "SID_".$rs->get("SES"),
                    "value" => "1",
                    "type" => "checkbox"
                ),
                array("value" => $rs->get("USR"), "align" => "center"),
                $rs->get("NAME"),
                array("value" => $rs->get("SES_ADDR"), "align" => "center"),
                array("value" => $rs->get("SES_TIME"), "align" => "center"),
                array("value" => $rs->get("SES_DATE"), "align" => "center")
            );
        }
        $re = array(
            "rows" => $rows
        );
        if ($offset == 0) {
            $rs = $conn->executeQuery(sprintf("SELECT COUNT(*) T FROM (%s) SQLT", $_sql));
            $re["total"] = ($rs->next()) ? $rs->getInt("T") : 0;
            $re["limit"] = $limit;
        }

        return $re;
    }
}
?>