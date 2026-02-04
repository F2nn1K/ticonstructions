<?php

namespace App\Services;

class LicenseService
{
	public function getPublicKey(): ?string
	{
		$path = config('license.public_key_path');
		return (is_string($path) && file_exists($path)) ? file_get_contents($path) : null;
	}

	public function savePublicKey(string $pem): bool
	{
		$dir = dirname(config('license.public_key_path'));
		if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
		return false !== file_put_contents(config('license.public_key_path'), trim($pem));
	}

	public function getLicenseRaw(): ?string
	{
		$path = config('license.license_path');
		return (is_string($path) && file_exists($path)) ? file_get_contents($path) : null;
	}

	public function saveLicense(string $json): bool
	{
		$dir = dirname(config('license.license_path'));
		if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
		return false !== file_put_contents(config('license.license_path'), trim($json));
	}

	public function status(): array
	{
		if (!config('license.enabled')) {
			return ['valid' => true, 'reason' => 'disabled'];
		}

		$pub = $this->getPublicKey();
		if (!$pub) {
			return ['valid' => false, 'reason' => 'no_public_key'];
		}

		$raw = $this->getLicenseRaw();
		if (!$raw) {
			return ['valid' => false, 'reason' => 'no_license'];
		}

		$data = json_decode($raw, true);
		if (!is_array($data)) {
			return ['valid' => false, 'reason' => 'invalid_json'];
		}

		$signatureB64 = $data['signature'] ?? null;
		if (!$signatureB64) {
			return ['valid' => false, 'reason' => 'no_signature'];
		}

		$dataNoSig = $data;
		unset($dataNoSig['signature']);
		$toSign = json_encode($dataNoSig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$sig = base64_decode($signatureB64, true);
		if ($sig === false) {
			return ['valid' => false, 'reason' => 'signature_decode_fail'];
		}

		$pubKey = openssl_pkey_get_public($pub);
		if (!$pubKey) {
			return ['valid' => false, 'reason' => 'public_key_invalid'];
		}

		$ok = openssl_verify($toSign, $sig, $pubKey, OPENSSL_ALGO_SHA256) === 1;
		if (!$ok) {
			return ['valid' => false, 'reason' => 'signature_invalid'];
		}

		$domainFromEnv = parse_url(config('app.url'), PHP_URL_HOST);
		$allowed = config('license.allowed_domain') ?: $domainFromEnv;
		$licenseDomain = $data['domain'] ?? null;

		if ($allowed && $licenseDomain && strtolower($allowed) !== strtolower($licenseDomain)) {
			return ['valid' => false, 'reason' => 'domain_mismatch', 'expected' => $allowed, 'got' => $licenseDomain];
		}

		$now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
		$expiresAtStr = $data['expires_at'] ?? null;
		$graceDays = intval($data['grace_days'] ?? 0);

		if (!$expiresAtStr) {
			return ['valid' => false, 'reason' => 'no_expires_at'];
		}

		try {
			$expiresAt = new \DateTimeImmutable($expiresAtStr, new \DateTimeZone('UTC'));
		} catch (\Throwable $e) {
			return ['valid' => false, 'reason' => 'expires_at_invalid'];
		}

		$graceUntil = $expiresAt->modify("+{$graceDays} days");
		$inGrace = $now > $expiresAt && $now <= $graceUntil;

		if ($now > $graceUntil) {
			return [
				'valid' => false,
				'reason' => 'expired',
				'expires_at' => $expiresAt->format('Y-m-d\TH:i:s\Z'),
				'grace_days' => $graceDays,
			];
		}

		return [
			'valid' => true,
			'reason' => $inGrace ? 'grace' : 'ok',
			'domain' => $licenseDomain,
			'expires_at' => $expiresAt->format('Y-m-d\TH:i:s\Z'),
			'grace_days' => $graceDays,
			'plan' => $data['plan'] ?? null,
			'users_max' => $data['users_max'] ?? null,
			'key' => $data['key'] ?? null,
		];
	}
}


