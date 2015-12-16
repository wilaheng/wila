<?php
/**
 * @no-context
 * @secure-service
 * @singleton
 */
final class vfs_forms_GroupsClassesT {
    /**
     * @ajax
     */
    public function GET() {
        return array(
            "cols" => array(
                array(
                    array("name" => "T_PKC", "width" => "80", "value" => "CID", "column-backgroundcolor" => "#ffc"),
                    array("name" => "T_NAME", "width" => "200", "value" => "GROUP NAME"),
                    array("name" => "T_ACL", "width" => "60", "value" => "ACL", "column-backgroundcolor" => "#ffc")
                )
            ),
            "info" => array(
                "initialize" => false, "callback" => true, "export" => false, "auto-refresh" => true, "height" => 250,
                "depends" => "GRP",   
                "limit" => 0, "total" => 0
            )
        );
    }

    /**
     * @ajax
     * @conn    vfs
     */
    public function POST($conn) {
        extract(dbm()->argv(array(
            "GRP" => array("type" => "digit")
        )));
        $rows = null;
        if (isset($GRP)) {
            $rs = $conn->executeQuery(
                "SELECT a.*, b.NAME FROM vfs_groups_classes a, vfs_packages_classes b WHERE a.GRP='%s' AND a.PKG=b.PKG AND a.PKC=b.PKC ORDER BY a.GRP, a.PKG, a.PKC", $GRP
            );
            while($rs->next()) {
                $rows[] = array(
                    $rs->getCell("PKC", null, "center", "vfs/forms/GroupsClasses/GET", $rs->httpQuery("GRP", "PKG", "PKC")),
                    $rs->get("NAME"),
                    $rs->getCell("ACL", null, "center")
                );
            }
        }

        return array(
            "rows" => $rows
        );
    }
}
?>