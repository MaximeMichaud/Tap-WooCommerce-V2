<?php
/**
 * Tests for WC_Tap_Gateway class.
 *
 * @package TapWooCommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for WC_Tap_Gateway.
 */
class TapGatewayTest extends TestCase {

	/**
	 * Gateway instance.
	 *
	 * @var WC_Tap_Gateway
	 */
	private WC_Tap_Gateway $gateway;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		reset_all();

		// Set up default gateway settings.
		set_wp_option(
			'woocommerce_tap_settings',
			array(
				'enabled'         => 'yes',
				'title'           => 'Credit Card',
				'description'     => 'Pay with your credit card via Tap.',
				'testmode'        => 'yes',
				'test_secret_key' => 'sk_test_123',
				'test_public_key' => 'pk_test_123',
				'live_secret_key' => 'sk_live_123',
				'live_public_key' => 'pk_live_123',
				'merchant_id'     => 'merchant_123',
				'payment_mode'    => 'charge',
				'ui_mode'         => 'redirect',
				'ui_language'     => 'english',
				'failer_page_id'  => '10',
				'success_page_id' => '11',
				'save_card'       => 'no',
			)
		);

		// Load the gateway class if not already loaded.
		if ( ! class_exists( 'WC_Tap_Gateway' ) ) {
			require_once dirname( __DIR__ ) . '/tap.php';
			tapwc_init_gateway_class();
		}

		$this->gateway = new WC_Tap_Gateway();
	}

	/**
	 * Test gateway initialization.
	 */
	public function testGatewayInitialization(): void {
		$this->assertSame( 'tap', $this->gateway->id );
		$this->assertSame( 'Tap Gateway', $this->gateway->method_title );
		$this->assertContains( 'products', $this->gateway->supports );
		$this->assertContains( 'refunds', $this->gateway->supports );
	}

	/**
	 * Test gateway settings are loaded correctly.
	 */
	public function testSettingsLoaded(): void {
		$this->assertSame( 'Credit Card', $this->gateway->title );
		$this->assertSame( 'Pay with your credit card via Tap.', $this->gateway->description );
		$this->assertSame( 'charge', $this->gateway->payment_mode );
		$this->assertSame( 'redirect', $this->gateway->ui_mode );
		$this->assertSame( 'english', $this->gateway->ui_language );
	}

	/**
	 * Test testmode setting is boolean.
	 */
	public function testTestmodeIsBoolean(): void {
		$this->assertIsBool( $this->gateway->testmode );
		$this->assertTrue( $this->gateway->testmode );
	}

	/**
	 * Test country code mapping for common countries.
	 */
	public function testCountryCodeMapping(): void {
		$this->assertSame( '1', $this->gateway->getStorePhoneCountryCode( 'US' ) );
		$this->assertSame( '44', $this->gateway->getStorePhoneCountryCode( 'GB' ) );
		$this->assertSame( '971', $this->gateway->getStorePhoneCountryCode( 'AE' ) );
		$this->assertSame( '966', $this->gateway->getStorePhoneCountryCode( 'SA' ) );
		$this->assertSame( '965', $this->gateway->getStorePhoneCountryCode( 'KW' ) );
		$this->assertSame( '973', $this->gateway->getStorePhoneCountryCode( 'BH' ) );
		$this->assertSame( '968', $this->gateway->getStorePhoneCountryCode( 'OM' ) );
		$this->assertSame( '974', $this->gateway->getStorePhoneCountryCode( 'QA' ) );
	}

	/**
	 * Test form fields are initialized.
	 */
	public function testFormFieldsInitialized(): void {
		$this->assertNotEmpty( $this->gateway->form_fields );
		$this->assertArrayHasKey( 'enabled', $this->gateway->form_fields );
		$this->assertArrayHasKey( 'title', $this->gateway->form_fields );
		$this->assertArrayHasKey( 'testmode', $this->gateway->form_fields );
		$this->assertArrayHasKey( 'test_secret_key', $this->gateway->form_fields );
		$this->assertArrayHasKey( 'live_secret_key', $this->gateway->form_fields );
		$this->assertArrayHasKey( 'payment_mode', $this->gateway->form_fields );
		$this->assertArrayHasKey( 'ui_mode', $this->gateway->form_fields );
	}

	/**
	 * Test payment mode options.
	 */
	public function testPaymentModeOptions(): void {
		$options = $this->gateway->form_fields['payment_mode']['options'];
		$this->assertArrayHasKey( 'charge', $options );
		$this->assertArrayHasKey( 'authorize', $options );
	}

	/**
	 * Test UI mode options.
	 */
	public function testUiModeOptions(): void {
		$options = $this->gateway->form_fields['ui_mode']['options'];
		$this->assertArrayHasKey( 'redirect', $options );
		$this->assertArrayHasKey( 'popup', $options );
	}

	/**
	 * Test UI language options.
	 */
	public function testUiLanguageOptions(): void {
		$options = $this->gateway->form_fields['ui_language']['options'];
		$this->assertArrayHasKey( 'english', $options );
		$this->assertArrayHasKey( 'arabic', $options );
	}

	/**
	 * Test tap_get_pages returns array.
	 */
	public function testGetPagesReturnsArray(): void {
		$pages = $this->gateway->tap_get_pages( 'Select Page' );
		$this->assertIsArray( $pages );
		$this->assertContains( 'Select Page', $pages );
	}

	/**
	 * Test tapwc_add_gateway_class function adds gateway to array.
	 */
	public function testAddGatewayClassFunction(): void {
		$gateways = tapwc_add_gateway_class( array() );
		$this->assertContains( 'WC_Tap_Gateway', $gateways );
	}
}
