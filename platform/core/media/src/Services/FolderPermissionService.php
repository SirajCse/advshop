<?php

namespace Botble\Media\Services;

use Botble\ACL\Models\User;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Models\MediaFolderPermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FolderPermissionService
{
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Check if a user can access a specific folder at the given permission level.
     */
    public function userCanAccessFolder(string|int $userId, string|int $folderId, string $level = 'view'): bool
    {
        // Super admins bypass all checks
        $user = User::query()->find($userId);
        if ($user && $user->isSuperUser()) {
            return true;
        }

        // Folder owner always has full access
        $folder = MediaFolder::query()->withoutGlobalScope('ownMedia')->find($folderId);
        if (! $folder) {
            return false;
        }

        if ($folder->user_id == $userId) {
            return true;
        }

        // Check direct permission on this folder
        $permission = MediaFolderPermission::query()
            ->where('folder_id', $folderId)
            ->where('user_id', $userId)
            ->first();

        if ($permission && $permission->isAtLeast($level)) {
            return true;
        }

        // Walk up parent chain checking inherited permissions
        $parentId = $folder->parent_id;
        while ($parentId && $parentId != 0) {
            $parentFolder = MediaFolder::query()->withoutGlobalScope('ownMedia')->find($parentId);
            if (! $parentFolder) {
                break;
            }

            if ($parentFolder->user_id == $userId) {
                return true;
            }

            $parentPermission = MediaFolderPermission::query()
                ->where('folder_id', $parentId)
                ->where('user_id', $userId)
                ->first();

            if ($parentPermission && $parentPermission->isAtLeast($level)) {
                return true;
            }

            $parentId = $parentFolder->parent_id;
        }

        return false;
    }

    /**
     * Get all folder IDs accessible by a user at the given permission level.
     * Includes owned folders, directly permitted folders, and their descendants.
     */
    public function getAccessibleFolderIds(string|int $userId, string $level = 'view'): Collection
    {
        $cacheKey = "folder_perms:{$userId}:{$level}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $level) {
            $accessibleIds = collect();

            // Get all folders the user owns
            $ownedFolderIds = MediaFolder::query()
                ->withoutGlobalScope('ownMedia')
                ->where('user_id', $userId)
                ->pluck('id');

            $accessibleIds = $accessibleIds->merge($ownedFolderIds);

            // Get folders with direct permissions at or above the required level
            $levelIndex = array_search($level, MediaFolderPermission::LEVELS);
            $validLevels = array_slice(MediaFolderPermission::LEVELS, $levelIndex);

            $permittedFolderIds = MediaFolderPermission::query()
                ->where('user_id', $userId)
                ->whereIn('permission', $validLevels)
                ->pluck('folder_id');

            $accessibleIds = $accessibleIds->merge($permittedFolderIds);

            // Include all descendants of accessible folders
            $descendantIds = $this->getDescendantFolderIds($accessibleIds);
            $accessibleIds = $accessibleIds->merge($descendantIds)->unique()->values();

            return $accessibleIds;
        });
    }

    /**
     * Get all descendant folder IDs for a collection of folder IDs.
     */
    protected function getDescendantFolderIds(Collection $folderIds): Collection
    {
        if ($folderIds->isEmpty()) {
            return collect();
        }

        $descendants = collect();
        $currentIds = $folderIds;

        // Iterative breadth-first traversal to avoid deep recursion
        while ($currentIds->isNotEmpty()) {
            $childIds = MediaFolder::query()
                ->withoutGlobalScope('ownMedia')
                ->whereIn('parent_id', $currentIds)
                ->whereNotIn('id', $descendants->merge($folderIds))
                ->pluck('id');

            $descendants = $descendants->merge($childIds);
            $currentIds = $childIds;
        }

        return $descendants;
    }

    /**
     * Grant a permission on a folder to a user.
     */
    public function grantPermission(string|int $folderId, string|int $userId, string $permission, string|int|null $grantedBy = null): MediaFolderPermission
    {
        $record = MediaFolderPermission::query()->updateOrCreate(
            ['folder_id' => $folderId, 'user_id' => $userId],
            ['permission' => $permission, 'granted_by' => $grantedBy]
        );

        $this->clearCache($userId);

        return $record;
    }

    /**
     * Revoke a user's permission on a folder.
     */
    public function revokePermission(string|int $folderId, string|int $userId): bool
    {
        $deleted = MediaFolderPermission::query()
            ->where('folder_id', $folderId)
            ->where('user_id', $userId)
            ->delete();

        $this->clearCache($userId);

        return $deleted > 0;
    }

    /**
     * Get all permissions for a specific folder.
     */
    public function getFolderPermissions(string|int $folderId): Collection
    {
        return MediaFolderPermission::query()
            ->where('folder_id', $folderId)
            ->with('user:id,first_name,last_name,email')
            ->get();
    }

    /**
     * Check if a folder is a descendant of another folder.
     */
    public function isDescendantOf(string|int $folderId, string|int $ancestorId): bool
    {
        if ($folderId == $ancestorId) {
            return true;
        }

        $folder = MediaFolder::query()->withoutGlobalScope('ownMedia')->find($folderId);
        if (! $folder) {
            return false;
        }

        $parentId = $folder->parent_id;
        while ($parentId && $parentId != 0) {
            if ($parentId == $ancestorId) {
                return true;
            }

            $parent = MediaFolder::query()->withoutGlobalScope('ownMedia')->find($parentId);
            if (! $parent) {
                break;
            }

            $parentId = $parent->parent_id;
        }

        return false;
    }

    /**
     * Clear cached folder permissions for a user.
     */
    public function clearCache(string|int $userId): void
    {
        foreach (MediaFolderPermission::LEVELS as $level) {
            Cache::forget("folder_perms:{$userId}:{$level}");
        }
    }
}
