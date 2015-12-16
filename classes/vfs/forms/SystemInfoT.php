<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_SystemInfoT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_NAME", "width" => "300", "value" => "NAME", "column-backgroundcolor" => "#ffc", "align" => "right"),
                    array("name" => "T_GLOBAL", "width" => "250", "value" => "GLOBAL VALUE"),
                    array("name" => "T_LOCAL", "width" => "250", "value" => "LOCAL VALUE")
                )
            ),
            "info" => array(
                "initialize" => true, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 350,
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     */
    public function POST() {
        $rows = null;
        $rows[] = array(
            array("value" => "DATASOURCE", "colspan" => 3, "style" => array("fontWeight" => "bold"))
        );
        $rs = inf()->datasource->getMap();
        foreach($rs as $k => $v) {
            $rows[] = array(
                array("value" => $k, "align" => "right"),
                array("value" => $v, "colspan" => 2)
            );
        }
        $rows[] = array(
            array("value" => "SAP CONFIG", "colspan" => 3, "style" => array("fontWeight" => "bold"))
        );
        $rs = inf()->sap->getMap();
        foreach($rs["conf"] as $k => $v) {
            $rows[] = array(
                array("value" => $k, "align" => "right"),
                array("value" => $v, "colspan" => 2)
            );
        }
        foreach($rs["conn"] as $k => $v) {
            $rows[] = array(
                array("value" => $k, "align" => "right"),
                array("value" => $v, "colspan" => 2)
            );
        }
        $rs = ini_get_all();
        unset($rs["url_rewriter.tags"]);
        $rows[] = array(
            array("value" => "PHP INI", "colspan" => 3, "style" => array("fontWeight" => "bold"))
        );
        foreach($rs as $k => $v) {
            $rows[] = array(
                array("value" => $k, "align" => "right"),
                $v["global_value"], $v["local_value"]
            );
        }

        return array(
            "rows" => $rows
        );
    }
}
?>