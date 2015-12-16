<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_UsersListT {
    /**
     * @ajax
     */
    public function HEAD() {
        require_once "UsersListT.html";
    }

    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_NO", "width" => "50", "value" => "NO", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_USR", "width" => "80", "value" => "USR"),
                    array("name" => "T_AREA_CODE", "width" => "60", "value" => "CDA", "column-backgroundcolor" => "#ffc", "align" => "center"),
                    array("name" => "T_AREA_SUB_CODE", "width" => "80", "value" => "CDSA", "align" => "center"),
                    array("name" => "T_NAME", "width" => "300", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_SHR", "width" => "40", "value" => "SHR"),
                    array("name" => "T_DEF", "width" => "60", "value" => "DEF", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_EXP", "width" => "100", "value" => "EXPIRED"),
                    array("name" => "T_C_USER", "width" => "80", "value" => "C_USER", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_C_DATE", "width" => "180", "value" => "C_DATE")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 300,
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
        $_sql = "SELECT * FROM vfs_users";
        if (isset($USR)) {
            $_arg = array();
            array_push($_arg, sprintf("USR LIKE '%s'", "%".$USR."%"));
            array_push($_arg, sprintf("NAME LIKE '%s'", "%".$USR."%"));
            $_sql.= sprintf(" WHERE %s", implode(" OR ", $_arg));
        }
        $_sql.= sprintf(" LIMIT %s, %s", $offset, $limit);
        $rs = $conn->executeQuery($_sql);
        $rows = null;
        $no = $offset + 1;
        while($rs->next()) {
            $rows[] = array(
                array("value" => $no++, "align" => "right"),
                array(
                    "value" => $rs->get("USR"),
                    "lookup" => "vfs/forms/Users/",
                    "observer" => "SEARCH",
                    "publish" => sprintf("%s=>USR", $rs->get("USR")),
                    "align" => "center"
                ),
                array("value" => $rs->get("AREA"), "align" => "center"),
                array("value" => $rs->get("AREA_SUB"), "align" => "center"),
                $rs->get("NAME"),
                array("value" => ($rs->getInt("SHR") == 1) ? "Y" : "N", "align" => "center"),
                array("value" => $rs->get("DEF"), "align" => "center"),
                array("value" => $rs->get("EXP"), "align" => "center"),
                array("value" => $rs->get("C_USER"), "align" => "center"),
                array("value" => $rs->get("C_DATE"), "align" => "center")
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