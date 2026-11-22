<?php

declare(strict_types=1);

namespace pup\customenchants\api;

final class ApplyResult
{
	private function __construct(
		public readonly ApplyStatus $status,
		public readonly ?string $other = null,
		public readonly int $level = 0,
	) {}

	public static function ok(): self { return new self(ApplyStatus::OK); }

	public static function fail(ApplyStatus $status, ?string $other = null, int $level = 0): self
	{
		return new self($status, $other, $level);
	}

	public function isOk(): bool
	{
		return $this->status === ApplyStatus::OK;
	}
}
