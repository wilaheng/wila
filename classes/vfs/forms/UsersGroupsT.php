<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_UsersGroupsT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "USER_GROUPs", "value" => "USER GROUPs", "colspan" => "3")
                ),
                array(
                    array("name" => "T_GRP", "width" => "80", "value" => "GROUP"),
                    array("name" => "T_NAME", "width" => "300", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_DEL", "width" => "80", "value" => "*")
                )
            ),
            "info" => array(
                "initialize" => false, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 150,
                "depends" => "USR",
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     */
    public function POST() {
        $rows = null;
        if (isset($_SESSION["vfs_forms_Users_Group"])) {
        foreach($_SESSION["vfs_forms_Users_Group"] as $GRP => $NAME) {
            $_row = array(
                array("value" => $GRP, "align" => "center"), $NAME
            );
            array_push($_row, ($GRP == 1100) ? STR_EMPTY : array(
                "value" => "DELETE",
                "callback" => "vfs/forms/UsersGroupsT/delClick",
                "params" => "GRP=" . $GRP,
                "align" => "center"
            ));
            $rows[] = $_row;
        }}

        return array(
            "rows" => $rows
        );
    }

    /**
     * @conn    vfs
     */
    public function groups() {
        $conn = DB::lookup("vfs");
        $sq = "SELECT * FROM vfs_groups WHERE BEGDA<=CURRENT_DATE AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL)";
        if (isset($_SESSION["vfs_forms_Users_Group"])) {
            $sq.= sprintf(
                " AND GRP NOT IN ('%s')", implode("','", array_keys($_SESSION["vfs_forms_Users_Group"]))
            );
        }
        $rs = $conn->executeQuery($sq);
        $ds = array("" => "PILIH");
        while($rs->next()) {
            $ds[$rs->get("GRP")] = sprintf("%s. %s", $rs->get("GRP"), $rs->get("NAME"));
        }

        return $ds;
    }

    /**
     * @ajax
     */
    public function addClick() {
        extract(dbm()->argv(array(
            "GRP" => array("req" => true, "type" => "digit")
        )));
        if (isset($_SESSION["vfs_forms_Users_Group"][$GRP])) return;
        $_SESSION["vfs_forms_Users_Group"][$GRP] = vfs_Service::getGroupName($GRP);
        ksort($_SESSION["vfs_forms_Users_Group"]);
        $gr = null;
        foreach($_SESSION["vfs_forms_Users_Group"] as $GRP => $NAME) {
            $gr[] = array(
                array("value" => $GRP, "align" => "center"),
                $NAME,
                array(
                    "value" => "DELETE",
                    "callback" => "vfs/forms/UsersGroupsT/delClick",
                    "params" => "GRP=" . $GRP,
                    "align" => "center"
                )
            );
        }
        $_env = env();
        $_env->callback()->call(array("GRP"));
        $_env->data(array(
            "USER_GROUPS" => $gr
        ));

        return $_env;
    }

    /**
     * @ajax
     */
    public function delClick() {
        extract(dbm()->argv(array(
            "GRP" => array("req" => true, "type" => "digit")
        )));
        unset($_SESSION["vfs_forms_Users_Group"][$GRP]);
        ksort($_SESSION["vfs_forms_Users_Group"]);
        $gr = null;
        foreach($_SESSION["vfs_forms_Users_Group"] as $GRP => $NAME) {
            $gr[] = array(
                array("value" => $GRP, "align" => "center"),
                $NAME,
                array(
                    "value" => "DELETE",
                    "callback" => "vfs/forms/UsersGroupsT/delClick",
                    "params" => "GRP=" . $GRP,
                    "align" => "center"
                )
            );
        }
        $_env = env();
        $_env->callback();
        $call = array("GRP");
        if (is_null($gr)) $call[] = "USER_GROUPS";
        else {
            $_env->data(array("USER_GROUPS" => $gr));
        }
        $_env->call($call);

        return $_env;
    }
}
?>