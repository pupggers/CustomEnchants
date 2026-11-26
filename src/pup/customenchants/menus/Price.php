<?php

declare(strict_types=1);

namespace pup\customenchants\menus;

use pocketmine\player\Player;
final readonly class Price
{
	private function __construct(
		public string $type,
		public int    $amount,
	) {}

	public static function parse(mixed $value): ?self
	{
		if (is_int($value)) {
			$value = 'level:' . $value;
		}
		if (!is_string($value) || preg_match('/^\s*(exp|level)\s*:\s*(\d+)\s*$/i', $value, $m) !== 1) {
			return null;
		}
		$amount = (int) $m[2];
		return $amount > 0 ? new self(strtolower($m[1]), $amount) : null;
	}

	public function format(): string
	{
		return match ($this->type) {
			'level' => $this->amount . ($this->amount === 1 ? ' level' : ' levels'),
			default => $this->amount . ' XP',
		};
	}

	public function take(Player $player): bool
	{
		$xp = $player->getXpManager();
		return match ($this->type) {
			'level' => $xp->getXpLevel() >= $this->amount && $xp->subtractXpLevels($this->amount),
			default => $xp->getCurrentTotalXp() >= $this->amount && $xp->subtractXp($this->amount),
		};
	}
}
