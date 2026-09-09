<?php

namespace Botble\Media\Tests\Unit;

use Botble\ACL\Models\User;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Models\MediaFolderPermission;
use Botble\Media\Services\FolderPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FolderPermissionServiceTest extends TestCase
{
    use DatabaseTransactions;
    protected FolderPermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = app(FolderPermissionService::class);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    // ==================== Helper Methods ====================

    protected function createUser(array $attributes = []): User
    {
        $isSuperUser = $attributes['super_user'] ?? false;
        unset($attributes['super_user']);

        $user = new User();
        $user->forceFill(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'super_user' => $isSuperUser ? 1 : 0,
        ], $attributes));
        $user->save();

        return $user->fresh();
    }

    protected function createFolder($userId = null, $parentId = null): MediaFolder
    {
        $userId =  ($userId ?? $this->createUser()->id);
        $parentId = $parentId ?  $parentId : 0;

        return MediaFolder::create([
            'name' => 'Test Folder ' . uniqid(),
            'slug' => 'test-folder-' . uniqid(),
            'user_id' => $userId,
            'parent_id' => $parentId,
            'color' => null,
        ]);
    }

    // ==================== Super Admin Tests ====================

    public function test_super_admin_can_access_any_folder(): void
    {
        $admin = $this->createUser(['super_user' => 1]);
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertTrue(
            $this->service->userCanAccessFolder($admin->id, $folder->id, 'view')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($admin->id, $folder->id, 'upload')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($admin->id, $folder->id, 'manage')
        );
    }

    // ==================== Folder Owner Tests ====================

    public function test_folder_owner_has_full_access(): void
    {
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertTrue(
            $this->service->userCanAccessFolder($owner->id, $folder->id, 'view')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($owner->id, $folder->id, 'upload')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($owner->id, $folder->id, 'manage')
        );
    }

    // ==================== Direct Permission Tests ====================

    public function test_user_with_view_permission_can_access_folder(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'view');

        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );
    }

    public function test_user_with_upload_permission_can_access_folder(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'upload');

        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'upload')
        );
    }

    public function test_user_with_manage_permission_can_access_folder(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'manage');

        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'upload')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'manage')
        );
    }

    public function test_user_without_permission_cannot_access_folder(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );
    }

    public function test_view_permission_does_not_grant_upload_access(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'view');

        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );
        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'upload')
        );
    }

    // ==================== Inherited Permission Tests ====================

    public function test_child_folder_inherits_parent_permission(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $parent = $this->createFolder($owner->id);
        $child = $this->createFolder($owner->id, $parent->id);

        $this->service->grantPermission($parent->id, $user->id, 'view');

        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $child->id, 'view')
        );
    }

    public function test_deeply_nested_folder_inherits_ancestor_permission(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $root = $this->createFolder($owner->id);
        $level1 = $this->createFolder($owner->id, $root->id);
        $level2 = $this->createFolder($owner->id, $level1->id);
        $level3 = $this->createFolder($owner->id, $level2->id);

        $this->service->grantPermission($root->id, $user->id, 'view');

        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $level1->id, 'view')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $level2->id, 'view')
        );
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $level3->id, 'view')
        );
    }

    public function test_intermediate_folder_permission_grants_child_access(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $root = $this->createFolder($owner->id);
        $level1 = $this->createFolder($owner->id, $root->id);
        $level2 = $this->createFolder($owner->id, $level1->id);

        // Grant permission on intermediate folder
        $this->service->grantPermission($level1->id, $user->id, 'view');

        // Child should be accessible
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $level2->id, 'view')
        );
        // Root should not be accessible
        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $root->id, 'view')
        );
    }

    // ==================== Permission Revocation Tests ====================

    public function test_permission_revoke_removes_access(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'view');
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );

        $this->service->revokePermission($folder->id, $user->id);
        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );
    }

    public function test_revoking_parent_permission_revokes_child_access(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $parent = $this->createFolder($owner->id);
        $child = $this->createFolder($owner->id, $parent->id);

        $this->service->grantPermission($parent->id, $user->id, 'view');
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $child->id, 'view')
        );

        $this->service->revokePermission($parent->id, $user->id);
        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $child->id, 'view')
        );
    }

    // ==================== Cache Tests ====================

    public function test_cache_is_cleared_on_grant(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        // Prime the cache by calling getAccessibleFolderIds
        $before = $this->service->getAccessibleFolderIds($user->id);
        $this->assertFalse($before->contains($folder->id));

        // Grant permission (should clear cache)
        $this->service->grantPermission($folder->id, $user->id, 'view');

        // New cache should include the folder
        $after = $this->service->getAccessibleFolderIds($user->id);
        $this->assertTrue($after->contains($folder->id));
    }

    public function test_cache_is_cleared_on_revoke(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'view');
        $before = $this->service->getAccessibleFolderIds($user->id);
        $this->assertTrue($before->contains($folder->id));

        // Revoke permission (should clear cache)
        $this->service->revokePermission($folder->id, $user->id);

        // New cache should not include the folder
        $after = $this->service->getAccessibleFolderIds($user->id);
        $this->assertFalse($after->contains($folder->id));
    }

    public function test_cache_works_across_permission_levels(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'upload');

        // View level should include folder
        $viewLevel = $this->service->getAccessibleFolderIds($user->id, 'view');
        $this->assertTrue($viewLevel->contains($folder->id));

        // Upload level should include folder
        $uploadLevel = $this->service->getAccessibleFolderIds($user->id, 'upload');
        $this->assertTrue($uploadLevel->contains($folder->id));

        // Manage level should not include folder
        $manageLevel = $this->service->getAccessibleFolderIds($user->id, 'manage');
        $this->assertFalse($manageLevel->contains($folder->id));
    }

    // ==================== Accessible Folder IDs Tests ====================

    public function test_get_accessible_folder_ids_includes_owned_folders(): void
    {
        $user = $this->createUser();
        $folder1 = $this->createFolder($user->id);
        $folder2 = $this->createFolder($user->id);
        $other = $this->createUser();
        $otherFolder = $this->createFolder($other->id);

        $accessible = $this->service->getAccessibleFolderIds($user->id);

        $this->assertTrue($accessible->contains($folder1->id));
        $this->assertTrue($accessible->contains($folder2->id));
        $this->assertFalse($accessible->contains($otherFolder->id));
    }

    public function test_get_accessible_folder_ids_includes_permitted_folders(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder1 = $this->createFolder($owner->id);
        $folder2 = $this->createFolder($owner->id);

        $this->service->grantPermission($folder1->id, $user->id, 'view');

        $accessible = $this->service->getAccessibleFolderIds($user->id);

        $this->assertTrue($accessible->contains($folder1->id));
        $this->assertFalse($accessible->contains($folder2->id));
    }

    public function test_get_accessible_folder_ids_includes_descendants(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $parent = $this->createFolder($owner->id);
        $child = $this->createFolder($owner->id, $parent->id);
        $grandchild = $this->createFolder($owner->id, $child->id);

        $this->service->grantPermission($parent->id, $user->id, 'view');

        $accessible = $this->service->getAccessibleFolderIds($user->id);

        $this->assertTrue($accessible->contains($parent->id));
        $this->assertTrue($accessible->contains($child->id));
        $this->assertTrue($accessible->contains($grandchild->id));
    }

    // ==================== Is Descendant Tests ====================

    public function test_is_descendant_of_same_folder(): void
    {
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->assertTrue($this->service->isDescendantOf($folder->id, $folder->id));
    }

    public function test_is_descendant_of_parent(): void
    {
        $owner = $this->createUser();
        $parent = $this->createFolder($owner->id);
        $child = $this->createFolder($owner->id, $parent->id);

        $this->assertTrue($this->service->isDescendantOf($child->id, $parent->id));
        $this->assertFalse($this->service->isDescendantOf($parent->id, $child->id));
    }

    public function test_is_descendant_of_ancestor(): void
    {
        $owner = $this->createUser();
        $root = $this->createFolder($owner->id);
        $level1 = $this->createFolder($owner->id, $root->id);
        $level2 = $this->createFolder($owner->id, $level1->id);

        $this->assertTrue($this->service->isDescendantOf($level2->id, $root->id));
        $this->assertTrue($this->service->isDescendantOf($level2->id, $level1->id));
        $this->assertFalse($this->service->isDescendantOf($root->id, $level2->id));
    }

    public function test_is_descendant_of_unrelated_folder(): void
    {
        $owner = $this->createUser();
        $folder1 = $this->createFolder($owner->id);
        $folder2 = $this->createFolder($owner->id);

        $this->assertFalse($this->service->isDescendantOf($folder1->id, $folder2->id));
        $this->assertFalse($this->service->isDescendantOf($folder2->id, $folder1->id));
    }

    // ==================== Get Folder Permissions Tests ====================

    public function test_get_folder_permissions_returns_all_permissions(): void
    {
        $owner = $this->createUser();
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user1->id, 'view');
        $this->service->grantPermission($folder->id, $user2->id, 'upload');

        $permissions = $this->service->getFolderPermissions($folder->id);

        $this->assertCount(2, $permissions);
        $this->assertTrue($permissions->contains('user_id', $user1->id));
        $this->assertTrue($permissions->contains('user_id', $user2->id));
    }

    public function test_get_folder_permissions_for_empty_folder(): void
    {
        $owner = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $permissions = $this->service->getFolderPermissions($folder->id);

        $this->assertCount(0, $permissions);
    }

    // ==================== Permission Level Tests ====================

    public function test_permission_level_ordering(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $folder = $this->createFolder($owner->id);

        $this->service->grantPermission($folder->id, $user->id, 'view');

        // view permission should NOT grant upload
        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'upload')
        );

        // Upgrade to upload
        $this->service->grantPermission($folder->id, $user->id, 'upload');
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'upload')
        );

        // upload should still grant view
        $this->assertTrue(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'view')
        );

        // upload should NOT grant manage
        $this->assertFalse(
            $this->service->userCanAccessFolder($user->id, $folder->id, 'manage')
        );
    }
}
