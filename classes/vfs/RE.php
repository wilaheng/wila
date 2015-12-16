<?php
/**
 * @service
 * @no-context
 */
final class vfs_RE {
    /**
     * @ajax
     * @ignore
     */
    public function HEAD() {
        $r = req();
        $z = array();
        $j = array();
        foreach($r->objs as $i => $k)
        try {
            if (!empty($r->args[$i])) $r->setParameter($r->args[$i]);
            $p = strrpos($k, "/");
            $c = _2cn(substr($k, 0, $p));
            $m = substr($k, $p + 1);
            if (!isset($j[$c])) $j[$c] = new $c();
            array_push($z, $j[$c]->$m());
        } catch(Exception $e) {
            array_push($z, $e->getMessage());
        }
        return $z;
    }
    public function mobile() {
        $_SESSION["MOBILE"] = !$_SESSION["MOBILE"];
        header("Location: "._2lp(dirname($_SERVER["SCRIPT_NAME"])));
    }
}
?>