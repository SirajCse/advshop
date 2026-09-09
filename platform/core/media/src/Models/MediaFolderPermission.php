<?php

namespace Botble\Media\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFolderPermission extends BaseModel
{
    protected $table = 'media_folder_permissions';

    protected $fillable = [
        'folder_id',
        'user_id',
        'permission',
        'granted_by',
    ];

    /**
     * Permission levels in ascending order of access.
     */
    public const LEVELS = ['view', 'upload', 'manage'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if this permission meets or exceeds the required level.
     */
    public function isAtLeast(string $level): bool
    {
        $currentIndex = array_search($this->permission, self::LEVELS);
        $requiredIndex = array_search($level, self::LEVELS);

        if ($currentIndex === false || $requiredIndex === false) {
            return false;
        }

        return $currentIndex >= $requiredIndex;
    }
}
