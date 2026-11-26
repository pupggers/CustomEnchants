<?php

declare(strict_types=1);

namespace pup\customenchants\api;

enum ApplyStatus
{
	case OK;
	case UNKNOWN;
	case INCOMPATIBLE_ITEM;
	case LEVEL_TOO_HIGH;
	case CONFLICT;
	case MISSING_REQUIREMENT;

	public function isBypassable(): bool
	{
		return $this === self::CONFLICT || $this === self::MISSING_REQUIREMENT;
	}

	public function messageKey(): string
	{
		return match ($this) {
			self::OK => 'enchant_applied',
			self::UNKNOWN => 'invalid_enchantment',
			self::INCOMPATIBLE_ITEM => 'cannot_apply',
			self::LEVEL_TOO_HIGH => 'level_exceeds_max',
			self::CONFLICT => 'enchant_conflict',
			self::MISSING_REQUIREMENT => 'enchant_requires',
		};
	}
}
