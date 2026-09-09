<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static ReviewBadgeEnum AUTO()
 * @method static ReviewBadgeEnum NONE()
 * @method static ReviewBadgeEnum PURCHASED()
 * @method static ReviewBadgeEnum COMMUNITY_REVIEW()
 * @method static ReviewBadgeEnum EXPERT_REVIEWER()
 * @method static ReviewBadgeEnum TOP_CONTRIBUTOR()
 * @method static ReviewBadgeEnum VERIFIED_BUYER()
 */
class ReviewBadgeEnum extends Enum
{
    public const AUTO = 'auto';
    public const NONE = 'none';
    public const PURCHASED = 'purchased';
    public const COMMUNITY_REVIEW = 'community_review';
    public const EXPERT_REVIEWER = 'expert_reviewer';
    public const TOP_CONTRIBUTOR = 'top_contributor';
    public const VERIFIED_BUYER = 'verified_buyer';

    public static $langPath = 'plugins/ecommerce::review.badge_types';
}
