<?php
/**
 * Admin Settings Handler for Woo Category Read More
 */

defined( 'ABSPATH' ) || exit;

class WC_Cat_Readmore_Admin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_menu() {
		add_options_page(
			'تنظیمات مشاهده بیشتر دسته‌بندی',
			'مشاهده بیشتر دسته‌بندی',
			'manage_options',
			'wccrm-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( 'wccrm_settings_group', 'wccrm_settings', array( $this, 'sanitize_settings' ) );
	}

	public function sanitize_settings( $input ) {
		$output = array();

		$output['collapsed_height'] = isset( $input['collapsed_height'] ) ? max( 80, min( 1200, (int) $input['collapsed_height'] ) ) : 220;
		$output['btn_more_text']    = isset( $input['btn_more_text'] ) ? sanitize_text_field( $input['btn_more_text'] ) : 'مشاهده بیشتر';
		$output['btn_less_text']    = isset( $input['btn_less_text'] ) ? sanitize_text_field( $input['btn_less_text'] ) : 'نمایش کمتر';

		$output['btn_text_color']   = isset( $input['btn_text_color'] ) ? sanitize_hex_color( $input['btn_text_color'] ) : '#1e3a8a';
		$output['btn_bg_color']     = isset( $input['btn_bg_color'] ) ? sanitize_hex_color( $input['btn_bg_color'] ) : '#f8fafc';
		$output['btn_border_color'] = isset( $input['btn_border_color'] ) ? sanitize_hex_color( $input['btn_border_color'] ) : '#e2e8f0';
		$output['fade_color']       = isset( $input['fade_color'] ) ? sanitize_hex_color( $input['fade_color'] ) : '#ffffff';

		$output['enable_scroll']    = ! empty( $input['enable_scroll'] ) ? 1 : 0;
		$output['scroll_offset']    = isset( $input['scroll_offset'] ) ? max( 0, (int) $input['scroll_offset'] ) : 80;

		$valid_modes = array( 'auto', 'extra_only', 'standard_only', 'both' );
		$output['target_mode']      = ( isset( $input['target_mode'] ) && in_array( $input['target_mode'], $valid_modes, true ) ) ? $input['target_mode'] : 'auto';

		$output['custom_selectors'] = isset( $input['custom_selectors'] ) ? sanitize_textarea_field( $input['custom_selectors'] ) : '';

		return $output;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = WC_Category_Read_More::get_options();
		?>
		<div class="wrap wccrm-admin-wrap" style="max-width: 900px; direction: rtl; text-align: right;">
			<h1>تنظیمات دکمه مشاهده بیشتر دسته‌بندی ووکامرس (Ameeen)</h1>
			<p class="description">
				این افزونه به صورت خودکار توضیحات تکمیلی (Extra Description وودمارت، رنک‌مث یا المنتور) و در صورت عدم وجود، توضیحات اصلی دسته‌بندی را با افکت Fade و دکمه روان کلمپ می‌کند.
			</p>

			<form method="post" action="options.php" style="background:#fff; padding:24px; border:1px solid #ccd0d4; border-radius:8px; margin-top:20px;">
				<?php
				settings_fields( 'wccrm_settings_group' );
				?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="wccrm_target_mode">اولویت هدف‌گیری محتوا</label></th>
							<td>
								<select id="wccrm_target_mode" name="wccrm_settings[target_mode]" style="min-width: 300px;">
									<option value="auto" <?php selected( $options['target_mode'], 'auto' ); ?>>
										هوشمند / خودکار (اگر اکسترا دیسکریپشن بود همان، وگرنه توضیحات معمولی)
									</option>
									<option value="extra_only" <?php selected( $options['target_mode'], 'extra_only' ); ?>>
										فقط توضیحات تکمیلی (Extra Description وودمارت/رنک‌مث)
									</option>
									<option value="standard_only" <?php selected( $options['target_mode'], 'standard_only' ); ?>>
										فقط توضیحات استاندارد دسته‌بندی
									</option>
									<option value="both" <?php selected( $options['target_mode'], 'both' ); ?>>
										هر دو (در صورت وجود هر دو باکس، هر دو ریدمور شوند)
									</option>
								</select>
								<p class="description">حالت خودکار برای اکثر فروشگاه‌ها مانند مستر اسمگ و سایت‌های وودمارت/المنتور بهترین حالت است.</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="wccrm_collapsed_height">ارتفاع در حالت بسته (پیکسل)</label></th>
							<td>
								<input type="number" id="wccrm_collapsed_height" name="wccrm_settings[collapsed_height]" value="<?php echo esc_attr( $options['collapsed_height'] ); ?>" min="80" max="800" step="10" class="regular-text" style="width:120px;" /> پیکسل
								<p class="description">معمولاً مقدار ۲۲۰px معادل حدود ۵ تا ۶ خط متن به همراه تیتر است.</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="wccrm_btn_more_text">عنوان دکمه در حالت بسته</label></th>
							<td>
								<input type="text" id="wccrm_btn_more_text" name="wccrm_settings[btn_more_text]" value="<?php echo esc_attr( $options['btn_more_text'] ); ?>" class="regular-text" />
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="wccrm_btn_less_text">عنوان دکمه در حالت باز</label></th>
							<td>
								<input type="text" id="wccrm_btn_less_text" name="wccrm_settings[btn_less_text]" value="<?php echo esc_attr( $options['btn_less_text'] ); ?>" class="regular-text" />
							</td>
						</tr>

						<tr>
							<th scope="row">رنگ‌بندی دکمه و محو شدگی</th>
							<td>
								<div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
									<div>
										<label style="display:block; margin-bottom:4px; font-weight:600;">رنگ متن و آیکون</label>
										<input type="color" name="wccrm_settings[btn_text_color]" value="<?php echo esc_attr( $options['btn_text_color'] ); ?>" />
									</div>
									<div>
										<label style="display:block; margin-bottom:4px; font-weight:600;">رنگ پس‌زمینه دکمه</label>
										<input type="color" name="wccrm_settings[btn_bg_color]" value="<?php echo esc_attr( $options['btn_bg_color'] ); ?>" />
									</div>
									<div>
										<label style="display:block; margin-bottom:4px; font-weight:600;">رنگ بوردر دکمه</label>
										<input type="color" name="wccrm_settings[btn_border_color]" value="<?php echo esc_attr( $options['btn_border_color'] ); ?>" />
									</div>
									<div>
										<label style="display:block; margin-bottom:4px; font-weight:600;">رنگ محو شدگی (Fade)</label>
										<input type="color" name="wccrm_settings[fade_color]" value="<?php echo esc_attr( $options['fade_color'] ); ?>" />
									</div>
								</div>
								<p class="description">رنگ محو شدگی (Fade) باید همرنگ زمینه کارت یا سکشن باشد (معمولاً سفید #ffffff).</p>
							</td>
						</tr>

						<tr>
							<th scope="row">اسکرول خودکار هنگام بستن</th>
							<td>
								<label>
									<input type="checkbox" name="wccrm_settings[enable_scroll]" value="1" <?php checked( $options['enable_scroll'], 1 ); ?> />
									اگر کاربر به پایین صفحه رفته باشد، هنگام بستن دکمه، صفحه با انیمیشن نرم به بالای باکس هدایت شود.
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="wccrm_custom_selectors">سلکتورهای CSS سفارشی (اختیاری)</label></th>
							<td>
								<input type="text" id="wccrm_custom_selectors" name="wccrm_settings[custom_selectors]" value="<?php echo esc_attr( $options['custom_selectors'] ); ?>" class="large-text" placeholder=".my-custom-cat-box, #extra-seo-text" />
								<p class="description">اگر قالبتان از کلاس غیراستانداردی استفاده می‌کند، می‌توانید با کاما سلکتورهای آن را جدا کنید.</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( 'ذخیره تغییرات' ); ?>
			</form>
		</div>
		<?php
	}
}
