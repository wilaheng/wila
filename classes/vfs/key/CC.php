<?php
/**
 * @secure-service
 * @no-context
 */
final class vfs_key_CC {
    private $w = 200;
    private $h = 70;
    private $b = array(255, 255, 255);
    private $c = array(array(27, 78, 181), array(22, 163, 35), array(214, 36, 7), array(0, 0, 0), array(12, 200, 120));
    private $f = array(
        array("spacing" => -3, "minSize" => 27, "maxSize" => 30, "font" => "antykwa.ttf"),
        array("spacing" => -2, "minSize" => 30, "maxSize" => 38, "font" => "duality.ttf"),
        array("spacing" => -2, "minSize" => 28, "maxSize" => 32, "font" => "jura.ttf")
    );
    private $m = array(4, 6);
    private $r = 4;
    private $x = array(10, 10);
    private $y = array(5, 10);
    private function getText() {
        $r = array();
        $w = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "l", "m", "n", "p", "q", "r", "s", "t", "v", "w", "y", "z");
        $v = array("a", "i", "u", "e");
        $c = rand(0, 1);
        $j = rand($this->m[0], $this->m[1]);
        for($i = 0; $i < $j; $i++) {
            if ($c = !$c) {
                array_push($r, $v[mt_rand(0, 3)]);
            } else {
                array_push($r, $w[mt_rand(0, 21)]);
            }
        }
        $_SESSION["CC"] = implode("", $r);

        return $_SESSION["CC"];
    }

    /**
     * @contentType     image/png
     * @silent
     */
    public function HEAD() {
        $h = array(
            "Pragma: no-cache",
            "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT",
            "Expires: Mon, 26 Jul 1997 05:00:00 GMT",
            "Cache-Control: must-revalidate,post-check=0,pre-check=0"
        );
        foreach($h as $i) header($i);
        $a = imagecreatetruecolor($this->w * 3, $this->h * 3);
        imagefilledrectangle($a, 0, 0, $this->w * 3, $this->h * 3, imagecolorallocate($a, $this->b[0], $this->b[1], $this->b[2]));
        $c = $this->c[mt_rand(0, sizeof($this->c) - 1)];
        $f = $this->f[array_rand($this->f)];
        $g = imagecolorallocate($a, $c[0], $c[1], $c[2]);
        $t = $this->getText();
        $s = 1 + (($this->m[1] - strlen($t)) * 0.09);
        $x = 60;
        $y = round(($this->h * 27 / 40) * 3);
        $j = strlen($t);
        for($i = 0; $i < $j; $i++) {
            $z = imagettftext($a, rand($f["minSize"], $f["maxSize"]) * 3 * $s, rand($this->r * -1, $this->r), $x, $y, $g, ENV."api/META-INF/".$f["font"], substr($t, $i, 1));
            $x+= ($z[2] - $x) + ($f["spacing"] * 3);
        }
        $k = rand(0, 100);
        $l = 3 * $this->x[1] * rand(1, 3);
        for($i = 0; $i < ($this->w * 3); $i++) {
            imagecopy($a, $a, $i - 1, sin($k + $i / $l) * (3 * $this->x[0]), $i, 0, 1, $this->h * 3);
        }
        $k = rand(0, 100);
        $l = 3 * $this->y[1] * rand(1, 2);
        for($i = 0; $i < ($this->h * 3); $i++) {
            imagecopy($a, $a, sin($k + $i / $l) * (3 * $this->y[0]), $i - 1, 0, $i, $this->w * 3, 1);
        }
        $z = imagecreatetruecolor($this->w, $this->h);
        imagecopyresampled($z, $a, 0, 0, 0, 0, $this->w, $this->h, $this->w * 3, $this->h * 3);
        imagedestroy($a);
        imagepng($z);
        imagedestroy($z);
    }
}
?>