<?php

namespace Botble\Media\Http\Controllers;

use Botble\ACL\Models\User;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Services\FolderPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderPermissionController extends BaseController
{
    public function __construct(protected FolderPermissionService $permissionService)
    {
    }

    /**
     * Get current permissions for a folder.
     */
    public function index(string $folder): JsonResponse
    {
        $this->authorizeManageAccess($folder);

        $permissions = $this->permissionService->getFolderPermissions($folder);

        return response()->json([
            'data' => $permissions->map(fn ($p) => [
                'user_id' => $p->user_id,
                'user_name' => $p->user ? trim($p->user->first_name . ' ' . $p->user->last_name) : 'Unknown',
                'user_email' => $p->user?->email,
                'permission' => $p->permission,
                'granted_at' => $p->created_at->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Grant permission on a folder to a user.
     */
    public function store(string $folder, Request $request): JsonResponse
    {
        $this->authorizeManageAccess($folder);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permission' => ['required', 'in:view,upload,manage'],
        ]);

        $permission = $this->permissionService->grantPermission(
            $folder,
            $request->input('user_id'),
            $request->input('permission'),
            auth()->id()
        );

        return response()->json([
            'message' => trans('core/media::media.folder_permission_granted'),
            'data' => $permission,
        ]);
    }

    /**
     * Revoke a user's permission on a folder.
     */
    public function destroy(string $folder, string $user): JsonResponse
    {
        $this->authorizeManageAccess($folder);

        $this->permissionService->revokePermission($folder, $user);

        return response()->json([
            'message' => trans('core/media::media.folder_permission_revoked'),
        ]);
    }

    /**
     * Get list of users for the permission modal select dropdown.
     */
    public function users(Request $request): JsonResponse
    {
        $search = $request->input('search', '');

        $users = User::query()
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json([
            'data' => $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => trim($u->first_name . ' ' . $u->last_name),
                'email' => $u->email,
            ]),
        ]);
    }

    /**
     * Only super admins or users with manage permission on the folder can modify access.
     */
    protected function authorizeManageAccess(string|int $folderId): void
    {
        $user = auth()->user();

        if ($user->isSuperUser()) {
            return;
        }

        $folder = MediaFolder::query()->withoutGlobalScope('ownMedia')->find($folderId);

        if ($folder && $folder->user_id == $user->getKey()) {
            return;
        }

        if ($this->permissionService->userCanAccessFolder($user->getKey(), $folderId, 'manage')) {
            return;
        }

        abort(403);
    }
}
