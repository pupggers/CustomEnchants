<?php

declare(strict_types=1);

namespace pup\customenchants\api;

final readonly class ApplyResult
{
	private function __construct(
		public ApplyStatus $status,
		public ?string     $other = null,
		public int         $level = 0,
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
