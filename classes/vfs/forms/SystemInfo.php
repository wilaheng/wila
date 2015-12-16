<?php
/**
 * @secure-service
 * @singleton
 */
final class vfs_forms_SystemInfo {
    /**
     * @ajax
     */
    public function HEAD() {
        require_once "SystemInfo.html";
    }
}
?>