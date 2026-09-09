<?php

namespace Botble\Ecommerce\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\Avatar;
use Botble\Media\Facades\RvMedia;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReviewReply extends BaseModel
{
    protected $table = 'ec_review_replies';

    protected $fillable = [
        'review_id',
        'user_id',
        'customer_id',
        'message',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    protected function responderName(): Attribute
    {
        return Attribute::get(function () {
            if ($this->user_id && $this->user) {
                return $this->user->name;
            }

            if ($this->customer_id && $this->customer) {
                return $this->customer->store?->name ?? $this->customer->name;
            }

            return trans('plugins/ecommerce::ecommerce.admin');
        });
    }

    protected function responderAvatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->user_id && $this->user) {
                return $this->user->avatar_url;
            }

            if ($this->customer_id && $this->customer) {
                $store = $this->customer->store;
                if ($store && $store->logo) {
                    return RvMedia::getImageUrl($store->logo, 'thumb');
                }

                if ($this->customer->avatar) {
                    return RvMedia::getImageUrl($this->customer->avatar, 'thumb');
                }

                try {
                    return (new Avatar())->create(Str::ucfirst($this->customer->name))->toBase64();
                } catch (Exception) {
                    return RvMedia::getDefaultImage();
                }
            }

            return RvMedia::getDefaultImage();
        });
    }
}
