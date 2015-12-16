<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_PackagesT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_PKG", "width" => "50", "value" => "PKG", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_CODE", "width" => "50", "value" => "CODE"),
                    array("name" => "T_NAME", "width" => "200", "value" => "NAME", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_CHECKED", "width" => "50", "value" => "STAT")
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
        $rows = null;
        $rs = $conn->vfs_packages()->orderBy("PKG ASC")->select();
        while($rs->next()) {
            $rows[] = array(
                $rs->getCell("PKG", null, "right", "vfs/forms/Packages/GET", $rs->httpQuery("PKG")),
                $rs->getCell("CODE", null, "center"),
                $rs->get("NAME"),
                $rs->getCell("CHECKED", null, "center")
            );
        }

        return array(
            "rows" => $rows
        );
    }
}
?>