<?php
/**
 * http://stackoverflow.com/questions/9262109/php-simplest-two-way-encryption/30189841#30189841
 */

class PPTECCustomCrypt
{
	const PPTEC_HASH_ALGO = 'sha256';
	const PPTEC_METHOD = 'aes-256-ctr';
	const PPTEC_KEY_AUTH = 'AUTH';
	const PPTEC_KEY_ENC = 'ENC';

	/**
	 * Encrypts (but does not authenticate) a message
	 *
	 * @param string $message - plaintext message
	 * @param string $key - encryption key (raw binary expected)
	 * @return string (raw binary)
	 */
	private static function pptec_internalEncrypt($message, $key)
	{
		$nonceSize = openssl_cipher_iv_length(self::PPTEC_METHOD);
		$nonce = openssl_random_pseudo_bytes($nonceSize);

		$ciphertext = openssl_encrypt(
			$message,
			self::PPTEC_METHOD,
			hash_hmac(self::PPTEC_HASH_ALGO, self::PPTEC_KEY_ENC, $key, true),
			OPENSSL_RAW_DATA,
			$nonce
		);

		return $nonce.$ciphertext;
	}

	/**
	 * Decrypts (but does not verify) a message
	 *
	 * @param string $message - ciphertext message
	 * @param string $key - encryption key (raw binary expected)
	 * @return string
	 */
	private static function pptec_internalDecrypt($message, $key)
	{
		$nonceSize = openssl_cipher_iv_length(self::PPTEC_METHOD);
		$nonce = mb_substr($message, 0, $nonceSize, '8bit');
		$ciphertext = mb_substr($message, $nonceSize, null, '8bit');

		return openssl_decrypt(
			$ciphertext,
			self::PPTEC_METHOD,
			hash_hmac(self::PPTEC_HASH_ALGO, self::PPTEC_KEY_ENC, $key, true),
			OPENSSL_RAW_DATA,
			$nonce
		);
	}

	/**
	 * Encrypts then MACs a message
	 *
	 * @param string $message - plaintext message
	 * @param string $key - encryption key (raw binary expected)
	 * @return string
	 */
	public static function pptec_encrypt($message, $key)
	{
		$ciphertext = self::pptec_internalEncrypt($message, $key);
		$mac = self::pptec_getMac($ciphertext, $key);

		return base64_encode($mac.$ciphertext);
	}

	/**
	 * Decrypts a message (after verifying integrity)
	 *
	 * @param string $message - ciphertext message
	 * @param string $key - encryption key (raw binary expected)
	 * @return string
	 */
	public static function pptec_decrypt($message, $key)
	{
		try {
			$message = base64_decode($message, true);
			if ($message === false) {
				throw new Exception('Decryption failure: decode');
			}

			$hs = mb_strlen(hash(self::PPTEC_HASH_ALGO, '', true), '8bit');
			$ciphertext = mb_substr($message, $hs, null, '8bit');

			if (!self::pptec_hashEquals(
				mb_substr($message, 0, $hs, '8bit'),
				self::pptec_getMac($ciphertext, $key)
			)) {
				throw new Exception('Decryption failure: hmac');
			}

			return self::pptec_internalDecrypt($ciphertext, $key);
		} catch (Exception $e) {
			error_log($e);
		}

		return false;
	}

	/**
	 * Get MAC
	 *
	 * @param string $ciphertext
	 * @param string $key
	 * @return string
	 */
	protected static function pptec_getMac($ciphertext, $key)
	{
		return hash_hmac(self::PPTEC_HASH_ALGO, $ciphertext, hash_hmac(self::PPTEC_HASH_ALGO, self::PPTEC_KEY_AUTH, $key, true), true);
	}

	/**
	 * Compare two strings without leaking timing information
	 *
	 * @param string $a
	 * @param string $b
	 * @return boolean
	 */
	protected static function pptec_hashEquals($a, $b)
	{
		if (function_exists('hash_equals'))
			return hash_equals($a, $b);

		$nonce = openssl_random_pseudo_bytes(32);
		return hash_hmac(self::PPTEC_HASH_ALGO, $a, $nonce) === hash_hmac(self::PPTEC_HASH_ALGO, $b, $nonce);
	}
}
