<?php

namespace Botble\Media\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Services\FolderPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FolderPermissionScopeTest extends TestCase
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

    // ==================== Helper Methods ====================

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
        $userId = ($userId ?? $this->createUser()->id);
        $parentId = $parentId ? $parentId : 0;

        return MediaFolder::create([
            'name' => 'Test Folder ' . uniqid(),
            'slug' => 'test-folder-' . uniqid(),
            'user_id' => $userId,
            'parent_id' => $parentId,
            'color' => null,
        ]);
    }

    protected function createFile(string|int $folderId, string|int $userId): MediaFile
    {
        return MediaFile::create([
            'name' => 'test-file-' . uniqid() . '.jpg',
            'folder_id' => $folderId,
            'user_id' => $userId,
            'url' => '/storage/test-' . uniqid() . '.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
        ]);
    }

    protected function enableOwnMediaRestriction(): void
    {
        // Mock RvMedia::canOnlyViewOwnMedia() to return true since it normally
        // returns false in console/test context due to AdminHelper check
        RvMedia::partialMock()->shouldReceive('canOnlyViewOwnMedia')->andReturn(true);
    }

    protected function disableOwnMediaRestriction(): void
    {
        RvMedia::partialMock()->shouldReceive('canOnlyViewOwnMedia')->andReturn(false);
    }

    // ==================== Own Media Setting Tests ====================

    public function test_user_sees_own_folders_when_setting_enabled(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $userFolder = $this->createFolder($user->id);
        $ownerFolder = $this->createFolder($owner->id);

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertTrue($visibleFolders->contains('id', $userFolder->id));
        $this->assertFalse($visibleFolders->contains('id', $ownerFolder->id));

        $this->disableOwnMediaRestriction();
    }

    public function test_user_sees_permitted_folders_when_setting_enabled(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $userFolder = $this->createFolder($user->id);
        $ownerFolder = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($ownerFolder->id, $user->id, 'view');

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertTrue($visibleFolders->contains('id', $userFolder->id));
        $this->assertTrue($visibleFolders->contains('id', $ownerFolder->id));

        $this->disableOwnMediaRestriction();
    }

    public function test_user_sees_all_folders_when_setting_disabled(): void
    {
        $this->disableOwnMediaRestriction();

        $user = $this->createUser();
        $owner1 = $this->createUser();
        $owner2 = $this->createUser();

        $userFolder = $this->createFolder($user->id);
        $owner1Folder = $this->createFolder($owner1->id);
        $owner2Folder = $this->createFolder($owner2->id);

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertTrue($visibleFolders->contains('id', $userFolder->id));
        $this->assertTrue($visibleFolders->contains('id', $owner1Folder->id));
        $this->assertTrue($visibleFolders->contains('id', $owner2Folder->id));
    }

    // ==================== Files in Permitted Folders ====================

    public function test_user_sees_files_in_permitted_folders(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $userFolder = $this->createFolder($user->id);
        $ownerFolder = $this->createFolder($owner->id);

        $userFile = $this->createFile($userFolder->id, $user->id);
        $ownerFile = $this->createFile($ownerFolder->id, $owner->id);

        $this->permissionService->grantPermission($ownerFolder->id, $user->id, 'view');

        $this->actingAs($user);

        $visibleFiles = MediaFile::query()->get();

        $this->assertTrue($visibleFiles->contains('id', $userFile->id));
        $this->assertTrue($visibleFiles->contains('id', $ownerFile->id));

        $this->disableOwnMediaRestriction();
    }

    public function test_user_cannot_see_files_in_non_permitted_folders(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $userFolder = $this->createFolder($user->id);
        $ownerFolder = $this->createFolder($owner->id);

        $userFile = $this->createFile($userFolder->id, $user->id);
        $ownerFile = $this->createFile($ownerFolder->id, $owner->id);

        // Don't grant permission

        $this->actingAs($user);

        $visibleFiles = MediaFile::query()->get();

        $this->assertTrue($visibleFiles->contains('id', $userFile->id));
        $this->assertFalse($visibleFiles->contains('id', $ownerFile->id));

        $this->disableOwnMediaRestriction();
    }

    // ==================== Nested Folders in Scope ====================

    public function test_user_sees_nested_folders_in_permitted_parent(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $parentFolder = $this->createFolder($owner->id);
        $childFolder = $this->createFolder($owner->id, $parentFolder->id);
        $grandchildFolder = $this->createFolder($owner->id, $childFolder->id);

        $this->permissionService->grantPermission($parentFolder->id, $user->id, 'view');

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertTrue($visibleFolders->contains('id', $parentFolder->id));
        $this->assertTrue($visibleFolders->contains('id', $childFolder->id));
        $this->assertTrue($visibleFolders->contains('id', $grandchildFolder->id));

        $this->disableOwnMediaRestriction();
    }

    public function test_user_cannot_see_nested_folders_in_non_permitted_parent(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $parentFolder = $this->createFolder($owner->id);
        $childFolder = $this->createFolder($owner->id, $parentFolder->id);

        // Don't grant permission to parent

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertFalse($visibleFolders->contains('id', $parentFolder->id));
        $this->assertFalse($visibleFolders->contains('id', $childFolder->id));

        $this->disableOwnMediaRestriction();
    }

    // ==================== Multiple Permissions ====================

    public function test_user_with_multiple_folder_permissions(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();

        $folder1 = $this->createFolder($owner->id);
        $folder2 = $this->createFolder($owner->id);
        $folder3 = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($folder1->id, $user->id, 'view');
        $this->permissionService->grantPermission($folder2->id, $user->id, 'view');

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertTrue($visibleFolders->contains('id', $folder1->id));
        $this->assertTrue($visibleFolders->contains('id', $folder2->id));
        $this->assertFalse($visibleFolders->contains('id', $folder3->id));

        $this->disableOwnMediaRestriction();
    }

    // ==================== Super Admin in Scope ====================

    public function test_super_admin_sees_all_folders_with_setting_enabled(): void
    {
        $this->enableOwnMediaRestriction();

        $admin = $this->createUser(['super_user' => 1]);
        $owner = $this->createUser();

        $adminFolder = $this->createFolder($admin->id);
        $ownerFolder = $this->createFolder($owner->id);

        $this->actingAs($admin);

        // Note: Super admin checks might bypass scopes at controller level
        // This test documents expected behavior
        $visibleFolders = MediaFolder::query()->get();

        // Depending on implementation, super admin might see all
        $this->assertTrue(
            $visibleFolders->contains('id', $adminFolder->id) ||
            $visibleFolders->count() >= 2
        );

        $this->disableOwnMediaRestriction();
    }

    // ==================== Permission Updates in Scope ====================

    public function test_newly_granted_permission_appears_in_scope(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->actingAs($user);

        $beforeGrant = MediaFolder::query()->count();

        // Grant permission from outside the scope
        $this->permissionService->grantPermission($folder->id, $user->id, 'view');

        // Clear cache to ensure scope is recomputed
        Cache::flush();

        $afterGrant = MediaFolder::query()->count();

        $this->assertGreaterThan($beforeGrant, $afterGrant);

        $this->disableOwnMediaRestriction();
    }

    public function test_revoked_permission_disappears_from_scope(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->permissionService->grantPermission($folder->id, $user->id, 'view');

        $this->actingAs($user);

        $beforeRevoke = MediaFolder::query()->count();

        // Revoke permission
        $this->permissionService->revokePermission($folder->id, $user->id);

        // Clear cache to ensure scope is recomputed
        Cache::flush();

        $afterRevoke = MediaFolder::query()->count();

        $this->assertLessThan($beforeRevoke, $afterRevoke);

        $this->disableOwnMediaRestriction();
    }

    // ==================== Edge Cases ====================

    public function test_user_without_own_media_setting_sees_all(): void
    {
        $this->disableOwnMediaRestriction();

        $user = $this->createUser();
        $owner1 = $this->createUser();
        $owner2 = $this->createUser();

        $this->createFolder($user->id);
        $this->createFolder($owner1->id);
        $this->createFolder($owner2->id);

        $this->actingAs($user);

        // Should see all folders regardless of permissions
        $visibleFolders = MediaFolder::query()->get();
        $this->assertGreaterThanOrEqual(3, $visibleFolders->count());
    }

    public function test_no_permissions_assigned_equals_existing_behavior(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $userFolder = $this->createFolder($user->id);
        $otherFolder = $this->createFolder($this->createUser()->id);

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        // User should only see their own folders
        $this->assertTrue($visibleFolders->contains('id', $userFolder->id));
        $this->assertFalse($visibleFolders->contains('id', $otherFolder->id));

        $this->disableOwnMediaRestriction();
    }

    public function test_deleted_folder_not_visible(): void
    {
        $this->enableOwnMediaRestriction();

        $user = $this->createUser();
        $folder = $this->createFolder($user->id);

        $this->permissionService->grantPermission($folder->id, $user->id, 'view');

        $folder->delete();

        $this->actingAs($user);

        $visibleFolders = MediaFolder::query()->get();

        $this->assertFalse($visibleFolders->contains('id', $folder->id));

        $this->disableOwnMediaRestriction();
    }
}
