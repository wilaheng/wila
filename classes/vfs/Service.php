<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_Service {
    static public function isCorporate()
    {
        return ($_SESSION["ATR"]["WILAYAH"] == "00") ? true : false;
    }

    static public function isArea()
    {
        return (!self::isCorporate() && substr($_SESSION["ATR"]["CABANG"], -2) == "00") ? true : false;
    }

    static public function getAreaBySub($sa)
    {
        return substr($sa, 0, -2) . "00";
    }

    static public function getGroupName($GRP)
    {
        $conn = DB::lookup("vfs", true);
        $rs = $conn->executeQuery(
            "SELECT NAME FROM vfs_groups WHERE GRP='%s'", $GRP
        );
        return ($rs->next()) ? $rs->get("NAME", STR_EMPTY) : null;
    }

    static public function subAreaName($AREA, $AREA_SUB) {
        $rs = DB::lookup("vfs", true)->executeQuery(sprintf(
            "SELECT `NAME` FROM vfs_area_sub WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL) AND AREA='%s' AND AREA_SUB='%s'",
            $AREA, $AREA_SUB
        ));
        return ($rs->next()) ? $rs->get("NAME") : STR_EMPTY;
    }

    static public function positionName($ID_POSITION) {
        $rs = DB::lookup("vfs", true)->executeQuery(sprintf(
            "SELECT * FROM vfs_positions WHERE BEGDA<=CURRENT_DATE AND BEGDA IS NOT NULL AND (ENDDA>=CURRENT_DATE OR ENDDA IS NULL) AND ID_POSITION='%s'",
            $ID_POSITION
        ));
        return ($rs->next()) ? $rs->get("NAME") : STR_EMPTY;
    }
}
?>