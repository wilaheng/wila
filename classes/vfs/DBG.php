<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_DBG {
    /**
     * @ajax
     */
    public function HEAD() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_PKG", "value" => "PARAMETERs DEBUGGER", "colspan" => 9),
                ),
                array(
                    array("name" => "T_CHECK", "width" => "60", "value" => "CHECK"),
                    array("name" => "T_NAME", "width" => "200", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_VALUE", "width" => "250", "value" => "VALUE"),
                    array("name" => "T_REQ.", "width" => "60", "value" => "REQ.", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_TYPE", "width" => "60", "value" => "TYPE"),
                    array("name" => "T_MINL", "width" => "60", "value" => "MINL", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_MAXL", "width" => "60", "value" => "MAXL"),
                    array("name" => "T_MINV", "width" => "60", "value" => "MINV", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_MAXV", "width" => "60", "value" => "MAXV")
                )
            ),
            "info" => array(
                "initialize" => false, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 200,
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     */
    public function GET() {
        return array(
            "rows" => null
        );
    }
}
?>