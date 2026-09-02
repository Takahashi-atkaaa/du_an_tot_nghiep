<?php

if (!function_exists('userHasPermission')) {
    /**
     * Kiểm tra người dùng hiện tại có quyền hay không
     *
     * @param string $maQuyen Mã quyền cần kiểm tra
     * @return bool
     */
    function userHasPermission(string $maQuyen): bool
    {
        $user = auth()->user();
        
        if (!$user || !$user->vaiTro) {
            return false;
        }
        
        // Admin (id_vai_tro = 1) có toàn quyền
        if ($user->id_vai_tro === 1) {
            return true;
        }
        
        return $user->vaiTro->hasPermission($maQuyen);
    }
}

if (!function_exists('userHasAnyPermission')) {
    /**
     * Kiểm tra người dùng có ít nhất 1 trong các quyền
     *
     * @param array $maQuyens Danh sách mã quyền
     * @return bool
     */
    function userHasAnyPermission(array $maQuyens): bool
    {
        $user = auth()->user();
        
        if (!$user || !$user->vaiTro) {
            return false;
        }
        
        // Admin (id_vai_tro = 1) có toàn quyền
        if ($user->id_vai_tro === 1) {
            return true;
        }
        
        return $user->vaiTro->hasAnyPermission($maQuyens);
    }
}

if (!function_exists('userHasPermissionGroup')) {
    /**
     * Check whether the current user can access a module with at least one
     * permission from the central catalog.
     */
    function userHasPermissionGroup(string $group): bool
    {
        return userHasAnyPermission((array) config("permissions.groups.{$group}", []));
    }
}
