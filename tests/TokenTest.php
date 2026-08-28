<?php
/**
 * Regression tests for flyer token generation.
 *
 * These exist because of a shipped bug: tokens were generated with
 * wp_generate_password( 16, false ) (charset a-zA-Z0-9) but read back through
 * sanitize_key(), which lowercases. 99.98% of tokens were therefore written
 * under one key and looked up under another.
 *
 * It appeared to work in testing because get_transient() only consults the
 * options table when there is no persistent object cache, and that column
 * collates case-insensitively. On any host running Redis or Memcached,
 * get_transient() takes the wp_cache_get() branch with a case-sensitive key
 * and never falls back — so the flyer failed for nearly every real user.
 *
 * No WordPress bootstrap is needed: the invariant is a property of the token
 * itself, not of WordPress.
 *
 * @package event-flyer-generator
 */

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;

/**
 * Token generation contract.
 *
 * @covers EFG_Shortcode::generate_token
 */
final class TokenTest extends TestCase {

	private const SAMPLE_SIZE = 2000;

	/**
	 * Mirrors the transform WordPress core's sanitize_key() applies:
	 * strtolower(), then strip everything outside [a-z0-9_-].
	 *
	 * Kept local so these tests need no WordPress install. If core ever changes
	 * this, the contract asserted here is still the one the plugin relies on.
	 *
	 * @param string $key Raw key.
	 * @return string
	 */
	private static function sanitize_key_like_core( string $key ): string {
		return (string) preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
	}

	/**
	 * The contract: a token must survive sanitize_key() unchanged.
	 */
	public function test_token_is_a_fixed_point_of_sanitize_key(): void {
		for ( $i = 0; $i < self::SAMPLE_SIZE; $i++ ) {
			$token = EFG_Shortcode::generate_token();

			$this->assertSame(
				$token,
				self::sanitize_key_like_core( $token ),
				'Token was altered by sanitize_key(), so it would be stored under one key and read under another.'
			);
		}
	}

	/**
	 * Tokens are lowercase hex of a fixed, unguessable length.
	 */
	public function test_token_shape(): void {
		for ( $i = 0; $i < self::SAMPLE_SIZE; $i++ ) {
			$this->assertMatchesRegularExpression( '/\A[a-f0-9]{32}\z/', EFG_Shortcode::generate_token() );
		}
	}

	/**
	 * Tokens must not collide.
	 */
	public function test_tokens_are_unique(): void {
		$tokens = array();
		for ( $i = 0; $i < self::SAMPLE_SIZE; $i++ ) {
			$tokens[] = EFG_Shortcode::generate_token();
		}

		$this->assertCount( self::SAMPLE_SIZE, array_unique( $tokens ), 'Duplicate token generated.' );
	}

	/**
	 * Guards the reason this file exists: proves the previous approach really
	 * did violate the contract, so nobody reintroduces it believing it was fine.
	 *
	 * Reproduces wp_generate_password( 16, false )'s charset rather than calling
	 * it, so the test needs no WordPress.
	 */
	public function test_previous_mixed_case_approach_would_fail_the_contract(): void {
		$chars   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$broken  = 0;
		$samples = 2000;

		for ( $i = 0; $i < $samples; $i++ ) {
			$token = '';
			for ( $c = 0; $c < 16; $c++ ) {
				$token .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
			}
			if ( self::sanitize_key_like_core( $token ) !== $token ) {
				++$broken;
			}
		}

		// Theoretical survival rate is (36/62)^16, about 0.017%.
		$this->assertGreaterThan(
			$samples * 0.99,
			$broken,
			'Expected the mixed-case scheme to fail the round trip almost always; if this no longer holds, re-check why.'
		);
	}
}
