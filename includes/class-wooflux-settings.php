<?php
/**
 * Admin settings page under WooCommerce → WooFlux.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the WooFlux settings page.
 */
class WooFlux_Settings {

	const OPTION_GROUP = 'wooflux_settings';
	const MENU_SLUG    = 'wooflux';

	/**
	 * Default color values for light and dark mode.
	 */
	const COLOR_DEFAULTS = array(
		'light' => array(
			'surface' => '#ffffff',
			'text'    => '#1e1e1e',
			'muted'   => '#767676',
			'accent'  => '#0073aa',
			'border'  => '#e2e2e2',
		),
		'dark'  => array(
			'bg'      => '#1e1e1e',
			'surface' => '#2a2a2a',
			'text'    => '#f0f0f0',
			'muted'   => '#a0a0a0',
			'accent'  => '#6db8e8',
			'border'  => '#3a3a3a',
		),
	);

	/**
	 * Adds the submenu page under WooCommerce and registers settings.
	 */
	public function add_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'WooFlux Settings', 'live-product-filter-wooflux' ),
			__( 'WooFlux', 'live-product-filter-wooflux' ),
			'manage_woocommerce',
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);

		$this->register_settings();
	}

	/**
	 * Registers all WooFlux wp_options settings with sanitize callbacks.
	 */
	private function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			'wooflux_default_per_page',
			array(
				'type'              => 'integer',
				'default'           => 12,
				'sanitize_callback' => 'absint',
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'wooflux_cache_ttl',
			array(
				'type'              => 'integer',
				'default'           => 300,
				'sanitize_callback' => 'absint',
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'wooflux_enable_url_sync',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'wooflux_no_results_message',
			array(
				'type'              => 'string',
				'default'           => __( 'No products found.', 'live-product-filter-wooflux' ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'wooflux_loading_indicator',
			array(
				'type'              => 'string',
				'default'           => 'spinner',
				'sanitize_callback' => array( $this, 'sanitize_loading_indicator' ),
			)
		);

		foreach ( self::COLOR_DEFAULTS as $mode => $colors ) {
			foreach ( $colors as $key => $default ) {
				register_setting(
					self::OPTION_GROUP,
					"wooflux_color_{$mode}_{$key}",
					array(
						'type'              => 'string',
						'default'           => $default,
						'sanitize_callback' => 'sanitize_hex_color',
					)
				);
			}
		}
	}

	/**
	 * Sanitises the loading indicator option to one of the allowed values.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public function sanitize_loading_indicator( string $value ): string {
		return in_array( $value, array( 'spinner', 'skeleton', 'none' ), true ) ? $value : 'spinner';
	}

	/**
	 * Renders the settings page HTML.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'live-product-filter-wooflux' ) );
		}

		if ( isset( $_POST['wooflux_flush_cache'] ) && check_admin_referer( 'wooflux_flush_cache' ) ) {
			$this->flush_cache();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Cache flushed.', 'live-product-filter-wooflux' ) . '</p></div>';
		}

		$per_page          = (int) get_option( 'wooflux_default_per_page', 12 );
		$cache_ttl         = (int) get_option( 'wooflux_cache_ttl', 300 );
		$url_sync          = (bool) get_option( 'wooflux_enable_url_sync', true );
		$no_results        = get_option( 'wooflux_no_results_message', __( 'No products found.', 'live-product-filter-wooflux' ) );
		$loading_indicator = get_option( 'wooflux_loading_indicator', 'spinner' );

		$color_labels = array(
			'bg'      => __( 'Background', 'live-product-filter-wooflux' ),
			'surface' => __( 'Cards & inputs', 'live-product-filter-wooflux' ),
			'text'    => __( 'Text', 'live-product-filter-wooflux' ),
			'muted'   => __( 'Secondary text', 'live-product-filter-wooflux' ),
			'accent'  => __( 'Accent (buttons, prices)', 'live-product-filter-wooflux' ),
			'border'  => __( 'Borders', 'live-product-filter-wooflux' ),
		);

		?>
		<div class="wrap wooflux-admin">
			<h1><?php esc_html_e( 'WooFlux Settings', 'live-product-filter-wooflux' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="wooflux_default_per_page"><?php esc_html_e( 'Products per page', 'live-product-filter-wooflux' ); ?></label>
						</th>
						<td>
							<input type="number" id="wooflux_default_per_page" name="wooflux_default_per_page"
								value="<?php echo esc_attr( $per_page ); ?>" min="1" max="100" class="small-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wooflux_cache_ttl"><?php esc_html_e( 'Cache duration (seconds)', 'live-product-filter-wooflux' ); ?></label>
						</th>
						<td>
							<input type="number" id="wooflux_cache_ttl" name="wooflux_cache_ttl"
								value="<?php echo esc_attr( $cache_ttl ); ?>" min="0" class="small-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Sync filters to URL', 'live-product-filter-wooflux' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="wooflux_enable_url_sync" value="1"
									<?php checked( $url_sync ); ?> />
								<?php esc_html_e( 'Push active filters to browser URL', 'live-product-filter-wooflux' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wooflux_no_results_message"><?php esc_html_e( 'No results message', 'live-product-filter-wooflux' ); ?></label>
						</th>
						<td>
							<input type="text" id="wooflux_no_results_message" name="wooflux_no_results_message"
								value="<?php echo esc_attr( $no_results ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wooflux_loading_indicator"><?php esc_html_e( 'Loading indicator', 'live-product-filter-wooflux' ); ?></label>
						</th>
						<td>
							<select id="wooflux_loading_indicator" name="wooflux_loading_indicator">
								<option value="spinner" <?php selected( $loading_indicator, 'spinner' ); ?>><?php esc_html_e( 'Spinner', 'live-product-filter-wooflux' ); ?></option>
								<option value="skeleton" <?php selected( $loading_indicator, 'skeleton' ); ?>><?php esc_html_e( 'Skeleton screen', 'live-product-filter-wooflux' ); ?></option>
								<option value="none" <?php selected( $loading_indicator, 'none' ); ?>><?php esc_html_e( 'None', 'live-product-filter-wooflux' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Colors', 'live-product-filter-wooflux' ); ?></h2>
				<p><?php esc_html_e( 'Customize WooFlux colors to match your theme. Dark mode activates automatically when the visitor\'s system uses dark mode.', 'live-product-filter-wooflux' ); ?></p>

				<h3><?php esc_html_e( 'Light mode', 'live-product-filter-wooflux' ); ?></h3>
				<table class="form-table" role="presentation">
					<?php foreach ( self::COLOR_DEFAULTS['light'] as $key => $default ) : ?>
					<tr>
						<th scope="row">
							<label for="wooflux_color_light_<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $color_labels[ $key ] ); ?>
							</label>
						</th>
						<td>
							<input type="color"
								id="wooflux_color_light_<?php echo esc_attr( $key ); ?>"
								name="wooflux_color_light_<?php echo esc_attr( $key ); ?>"
								value="<?php echo esc_attr( get_option( "wooflux_color_light_{$key}", $default ) ); ?>" />
							<button type="button" class="button-link wooflux-color-reset"
								data-target="wooflux_color_light_<?php echo esc_attr( $key ); ?>"
								data-default="<?php echo esc_attr( $default ); ?>"
								title="<?php esc_attr_e( 'Reset to default', 'live-product-filter-wooflux' ); ?>">↩</button>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>

				<h3><?php esc_html_e( 'Dark mode', 'live-product-filter-wooflux' ); ?></h3>
				<table class="form-table" role="presentation">
					<?php foreach ( self::COLOR_DEFAULTS['dark'] as $key => $default ) : ?>
					<tr>
						<th scope="row">
							<label for="wooflux_color_dark_<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $color_labels[ $key ] ); ?>
							</label>
						</th>
						<td>
							<input type="color"
								id="wooflux_color_dark_<?php echo esc_attr( $key ); ?>"
								name="wooflux_color_dark_<?php echo esc_attr( $key ); ?>"
								value="<?php echo esc_attr( get_option( "wooflux_color_dark_{$key}", $default ) ); ?>" />
							<button type="button" class="button-link wooflux-color-reset"
								data-target="wooflux_color_dark_<?php echo esc_attr( $key ); ?>"
								data-default="<?php echo esc_attr( $default ); ?>"
								title="<?php esc_attr_e( 'Reset to default', 'live-product-filter-wooflux' ); ?>">↩</button>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>
				<script>
				document.querySelectorAll('.wooflux-color-reset').forEach(function(btn) {
					btn.addEventListener('click', function() {
						document.getElementById(btn.dataset.target).value = btn.dataset.default;
					});
				});
				</script>

				<?php submit_button(); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Cache', 'live-product-filter-wooflux' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wooflux_flush_cache' ); ?>
				<p><?php esc_html_e( 'Manually clear all cached filter results.', 'live-product-filter-wooflux' ); ?></p>
				<?php submit_button( __( 'Flush Cache', 'live-product-filter-wooflux' ), 'secondary', 'wooflux_flush_cache' ); ?>
			</form>

			<?php if ( ! wooflux_is_pro() ) : ?>
			<hr />
			<h2><?php esc_html_e( 'Pro Features', 'live-product-filter-wooflux' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				$pro_fields = array(
					__( 'Color swatch taxonomy', 'live-product-filter-wooflux' ),
					__( 'Size swatch taxonomy', 'live-product-filter-wooflux' ),
					__( 'Enable price slider', 'live-product-filter-wooflux' ),
					__( 'Enable rating filter', 'live-product-filter-wooflux' ),
				);
				foreach ( $pro_fields as $label ) :
					?>
				<tr>
					<th scope="row"><?php echo esc_html( $label ); ?></th>
					<td>
						<span class="wooflux-pro-badge">
							<?php esc_html_e( 'Pro', 'live-product-filter-wooflux' ); ?>
						</span>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Triggers cache invalidation via the shared action hook.
	 */
	private function flush_cache(): void {
		do_action( 'wooflux_flush_cache' );
	}
}
