<?php

namespace Botble\Media\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Models\MediaFolderPermission;
use Botble\Media\Services\FolderPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Tests the FolderPermissionController authorization logic via the service layer.
 * Direct HTTP tests are skipped due to AdminHelper middleware complexity in test context.
 */
class FolderPermissionControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected FolderPermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->permissionService = app(FolderPermissionService::class);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    protected function createUser(array $attributes = []): User
    {
        $user = new User();
        $user->forceFill(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'super_user' => 0,
        ], $attributes));
        $user->save();

        return $user->fresh();
    }

    protected function createFolder($userId = null, $parentId = null): MediaFolder
    {
        $userId = $userId ?? $this->createUser()->id;
        $parentId = $parentId ?: 0;

        return MediaFolder::create([
            'name' => 'Test Folder ' . uniqid(),
            'slug' => 'test-folder-' . uniqid(),
            'user_id' => $userId,
            'parent_id' => $parentId,
            'color' => null,
        ]);
    }

    public function test_grant_permission_creates_record(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $permission = $this->permissionService->grantPermission(
            $folder->id,
            $user->id,
            'view',
            $owner->id
        );

        $this->assertEquals($folder->id, $permission->folder_id);
        $this->assertEquals($user->id, $permission->user_id);
        $this->assertEquals('view', $permission->permission);
        $this->assertEquals($owner->id, $permission->granted_by);
    }

    public function test_grant_permission_updates_existing_record(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($folder->id, $user->id, 'view');
        $this->permissionService->grantPermission($folder->id, $user->id, 'manage');

        $perms = MediaFolderPermission::query()
            ->where('folder_id', $folder->id)
            ->where('user_id', $user->id)
            ->get();

        $this->assertCount(1, $perms);
        $this->assertEquals('manage', $perms->first()->permission);
    }

    public function test_revoke_permission_removes_record(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($folder->id, $user->id, 'view');

        $this->assertTrue($this->permissionService->revokePermission($folder->id, $user->id));

        $this->assertDatabaseMissing('media_folder_permissions', [
            'folder_id' => $folder->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_get_folder_permissions_returns_all_for_folder(): void
    {
        $owner = $this->createUser();
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($folder->id, $user1->id, 'view');
        $this->permissionService->grantPermission($folder->id, $user2->id, 'upload');

        $permissions = $this->permissionService->getFolderPermissions($folder->id);

        $this->assertCount(2, $permissions);
    }

    public function test_super_admin_can_manage_any_folder(): void
    {
        $admin = $this->createUser(['super_user' => 1]);
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertTrue(
            $this->permissionService->userCanAccessFolder($admin->id, $folder->id, 'manage')
        );
    }

    public function test_owner_can_manage_own_folder(): void
    {
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertTrue(
            $this->permissionService->userCanAccessFolder($owner->id, $folder->id, 'manage')
        );
    }

    public function test_regular_user_cannot_manage_without_permission(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertFalse(
            $this->permissionService->userCanAccessFolder($user->id, $folder->id, 'manage')
        );
    }

    public function test_user_with_manage_can_manage(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($folder->id, $user->id, 'manage');

        $this->assertTrue(
            $this->permissionService->userCanAccessFolder($user->id, $folder->id, 'manage')
        );
    }

    public function test_granted_by_is_tracked(): void
    {
        $admin = $this->createUser(['super_user' => 1]);
        $user = $this->createUser();
        $folder = $this->createFolder($admin->id);

        $permission = $this->permissionService->grantPermission(
            $folder->id,
            $user->id,
            'view',
            $admin->id
        );

        $this->assertEquals($admin->id, $permission->granted_by);
    }
}
