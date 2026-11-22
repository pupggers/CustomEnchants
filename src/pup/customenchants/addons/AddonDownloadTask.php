<?php

declare(strict_types=1);

namespace pup\customenchants\addons;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pup\customenchants\Main;

final class AddonDownloadTask extends AsyncTask
{
	private const string USER_AGENT = 'CustomEnchants-AddonInstaller';
	private const int MAX_BYTES = 32 * 1024 * 1024;

	public function __construct(
		// Plain (non-readonly) scalars: AsyncTask is a thread-safe object.
		private string $name,
		private string $repository,
		private string $target,
		private string $requestedBy,
	) {}

	public function onRun(): void
	{
		try {
			$this->setResult($this->download());
		} catch (InternetException $e) {
			$this->setResult(['error' => 'Network error: ' . $e->getMessage()]);
		}
	}

	/** @return array{error: string}|array{file: string, tag: string, asset: string, sha256: string, verified: bool} */
	private function download(): array
	{
		$headers = ['User-Agent: ' . self::USER_AGENT, 'Accept: application/vnd.github+json'];
		$api = 'https://api.github.com/repos/' . $this->repository . '/releases/latest';

		$response = Internet::simpleCurl($api, 15, $headers);
		if ($response->getCode() !== 200) {
			return ['error' => "GitHub returned HTTP {$response->getCode()} for $this->repository (no published release?)."];
		}

		$release = json_decode($response->getBody(), true);
		if (!is_array($release) || !is_array($release['assets'] ?? null)) {
			return ['error' => 'GitHub sent an unexpected release format.'];
		}

		$asset = null;
		foreach ($release['assets'] as $candidate) {
			if (is_array($candidate) && str_ends_with(strtolower((string) ($candidate['name'] ?? '')), '.phar')) {
				$asset = $candidate;
				break;
			}
		}
		if ($asset === null || !is_string($asset['browser_download_url'] ?? null)) {
			return ['error' => "The latest release of $this->repository has no .phar asset."];
		}
		if (($asset['size'] ?? 0) > self::MAX_BYTES) {
			return ['error' => 'The addon is larger than ' . (self::MAX_BYTES >> 20) . ' MB; refusing to download.'];
		}

		$file = Internet::simpleCurl($asset['browser_download_url'], 60, ['User-Agent: ' . self::USER_AGENT]);
		if ($file->getCode() !== 200 || $file->getBody() === '') {
			return ['error' => "Download failed with HTTP {$file->getCode()}."];
		}

		$body = $file->getBody();
		$sha256 = hash('sha256', $body);

		$verified = false;
		$digest = $asset['digest'] ?? null;
		if (is_string($digest) && str_starts_with($digest, 'sha256:')) {
			if (!hash_equals(substr($digest, 7), $sha256)) {
				return ['error' => 'Checksum mismatch: the download does not match the digest GitHub published.'];
			}
			$verified = true;
		}

		if (!str_starts_with($body, '<?php') && !str_contains(substr($body, 0, 4096), '__HALT_COMPILER')) {
			return ['error' => 'The asset does not look like a PHP phar.'];
		}

		$tmp = $this->target . '.new';
		if (file_put_contents($tmp, $body, LOCK_EX) === false) {
			return ['error' => "Could not write $tmp."];
		}

		return [
			'file' => $tmp,
			'tag' => (string) ($release['tag_name'] ?? '?'),
			'asset' => (string) $asset['name'],
			'sha256' => $sha256,
			'verified' => $verified,
		];
	}

	public function onCompletion(): void
	{
		$main = Main::getInstance();
		if (!$main->isEnabled()) {
			return;
		}
		/** @var array<string, mixed> $result */
		$result = $this->getResult();
		$main->getAddonManager()->onDownloaded($this->name, $this->repository, $this->target, $this->requestedBy, $result);
	}
}
