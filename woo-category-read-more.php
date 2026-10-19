<?php
/**
 * Plugin Name:       Woo Category Read More & Extra Description Clamper
 * Plugin URI:        https://github.com/am333ni7y/woo-category-read-more
 * Description:       افزونه هوشمند، سبک و سئومحور جهت اعمال دکمه «مشاهده بیشتر / بستن» روی توضیحات تکمیلی (Extra Description وودمارت و رنک‌مث) یا توضیحات دسته‌بندی ووکامرس بدون دستکاری سورس HTML اولیه موتورهای جستجو.
 * Version:           1.0.0
 * Author:            Ameeen (Amirhossein)
 * Author URI:        https://ameeen.ir
 * License:           GPL v2 or later
 * Text Domain:       woo-category-read-more
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WCCRM_VERSION', '1.0.0' );
define( 'WCCRM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCCRM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

final class WC_Category_Read_More {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {
		// Enqueue scripts & styles on frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ), 20 );

		// Load admin settings if in admin
		if ( is_admin() ) {
			require_once WCCRM_PLUGIN_DIR . 'includes/class-wc-cat-readmore-admin.php';
			WC_Cat_Readmore_Admin::instance();
		}
	}

	/**
	 * Default plugin options
	 */
	public static function get_options() {
		$defaults = array(
			'collapsed_height' => 220,
			'btn_more_text'    => 'مشاهده بیشتر',
			'btn_less_text'    => 'نمایش کمتر',
			'btn_text_color'   => '#1e3a8a',
			'btn_bg_color'     => '#f8fafc',
			'btn_border_color' => '#e2e8f0',
			'fade_color'       => '#ffffff',
			'enable_scroll'    => 1,
			'scroll_offset'    => 80,
			'target_mode'      => 'auto', // 'auto' (prefer extra, fallback standard), 'extra_only', 'standard_only', 'both'
			'custom_selectors' => '',
		);

		$saved = get_option( 'wccrm_settings', array() );
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Enqueue frontend scripts & styles on WooCommerce taxonomy pages
	 */
	public function enqueue_frontend_assets() {
		if ( ! $this->is_target_archive_page() ) {
			return;
		}

		$options = self::get_options();

		// Frontend styles
		wp_enqueue_style(
			'wccrm-frontend',
			WCCRM_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			WCCRM_VERSION
		);

		// Dynamic custom CSS variables based on options
		$fade_rgb = $this->hex_to_rgb( $options['fade_color'] );
		$custom_css = "
			:root {
				--wccrm-btn-color: " . esc_attr( $options['btn_text_color'] ) . ";
				--wccrm-btn-bg: " . esc_attr( $options['btn_bg_color'] ) . ";
				--wccrm-btn-border: " . esc_attr( $options['btn_border_color'] ) . ";
				--wccrm-fade-rgb: {$fade_rgb};
			}
		";
		wp_add_inline_style( 'wccrm-frontend', $custom_css );

		// Frontend JS (Pure Vanilla JS, 0 dependencies)
		wp_enqueue_script(
			'wccrm-frontend',
			WCCRM_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			WCCRM_VERSION,
			true
		);

		wp_localize_script(
			'wccrm-frontend',
			'wcCatReadMoreSettings',
			array(
				'collapsedHeight' => (int) $options['collapsed_height'],
				'moreText'        => esc_html( $options['btn_more_text'] ),
				'lessText'        => esc_html( $options['btn_less_text'] ),
				'enableScroll'    => (bool) $options['enable_scroll'],
				'scrollOffset'    => (int) $options['scroll_offset'],
				'targetMode'      => sanitize_key( $options['target_mode'] ),
				'customSelectors' => sanitize_text_field( $options['custom_selectors'] ),
			)
		);
	}

	/**
	 * Check if the current page is a category, tag, or custom taxonomy archive
	 */
	private function is_target_archive_page() {
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			return true;
		}
		if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
			return true;
		}
		if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			return true;
		}
		if ( is_tax() ) {
			return true;
		}
		return false;
	}

	/**
	 * Helper: Convert hex color to rgb string
	 */
	private function hex_to_rgb( $hex ) {
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) === 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} elseif ( strlen( $hex ) === 6 ) {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		} else {
			return '255, 255, 255';
		}
		return "{$r}, {$g}, {$b}";
	}
}

function wccrm_init() {
	return WC_Category_Read_More::instance();
}
wccrm_init();
