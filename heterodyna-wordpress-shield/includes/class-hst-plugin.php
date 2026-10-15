<?php
if (!defined('ABSPATH')) {
    exit;
}

class Heterodyna_WordPress_Shield_Plugin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('send_headers', [__CLASS__, 'send_security_headers']);
        add_action('wp_head', [__CLASS__, 'render_meta_tags'], 1);
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('rest_endpoints', [__CLASS__, 'harden_rest_endpoints']);
        add_action('template_redirect', [__CLASS__, 'block_author_enumeration']);
        add_action('init', [__CLASS__, 'cleanup_wp_head']);
        add_action('admin_notices', [__CLASS__, 'dmarc_notice']);
        add_filter('plugin_action_links_' . plugin_basename(HST_FILE), [__CLASS__, 'plugin_links']);
    }

    public static function defaults() {
        return [
            'enable_hsts' => 0,
            'hsts_max_age' => 31536000,
            'hsts_include_subdomains' => 0,
            'hsts_preload' => 0,
            'enable_csp' => 0,
            'csp_value' => "default-src 'self' https: data: blob:; img-src 'self' https: data: blob:; media-src 'self' https: data: blob:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; font-src 'self' https: data:; connect-src 'self' https: wss:; frame-ancestors 'self'; base-uri 'self'; form-action 'self' https:; upgrade-insecure-requests;",
            'enable_permissions_policy' => 1,
            'permissions_policy_value' => 'accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), geolocation=(), gyroscope=(), hid=(), idle-detection=(), magnetometer=(), microphone=(), midi=(), payment=(), publickey-credentials-get=(self), serial=(), usb=(), xr-spatial-tracking=()',
            'default_meta_description' => '',
            'fallback_og_image' => '',
            'twitter_card_type' => 'summary_large_image',
            'disable_user_rest' => 0,
            'disable_oembed_routes' => 0,
            'disable_author_scan' => 1,
            'remove_generator' => 1,
            'remove_rsd_links' => 1,
            'remove_wlwmanifest' => 1,
            'remove_shortlink' => 1,
        ];
    }

    public static function get_options() {
        $saved = get_option(HST_OPTION, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return wp_parse_args($saved, self::defaults());
    }

    public static function admin_menu() {
        add_options_page(
            'Heterodyna WordPress Shield',
            'Heterodyna WordPress Shield',
            'manage_options',
            'heterodyna-wordpress-shield',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function register_settings() {
        register_setting(HST_OPTION, HST_OPTION, [__CLASS__, 'sanitize_options']);
    }

    public static function sanitize_options($input) {
        $defaults = self::defaults();
        $output = [];

        $checkboxes = [
            'enable_hsts', 'hsts_include_subdomains', 'hsts_preload',
            'enable_csp', 'enable_permissions_policy',
            'disable_user_rest', 'disable_oembed_routes', 'disable_author_scan',
            'remove_generator', 'remove_rsd_links', 'remove_wlwmanifest', 'remove_shortlink'
        ];

        foreach ($checkboxes as $key) {
            $output[$key] = !empty($input[$key]) ? 1 : 0;
        }

        $output['hsts_max_age'] = isset($input['hsts_max_age']) ? max(0, intval($input['hsts_max_age'])) : $defaults['hsts_max_age'];
        $output['csp_value'] = isset($input['csp_value']) ? str_replace(["\r", "\n"], '', trim((string) $input['csp_value'])) : $defaults['csp_value'];
        $output['permissions_policy_value'] = isset($input['permissions_policy_value']) ? sanitize_text_field((string) $input['permissions_policy_value']) : $defaults['permissions_policy_value'];
        $output['default_meta_description'] = isset($input['default_meta_description']) ? sanitize_textarea_field((string) $input['default_meta_description']) : '';
        $output['fallback_og_image'] = isset($input['fallback_og_image']) ? esc_url_raw((string) $input['fallback_og_image']) : '';
        $output['twitter_card_type'] = isset($input['twitter_card_type']) && in_array($input['twitter_card_type'], ['summary', 'summary_large_image'], true)
            ? $input['twitter_card_type']
            : $defaults['twitter_card_type'];

        return $output;
    }

    public static function plugin_links($links) {
        $url = admin_url('options-general.php?page=heterodyna-wordpress-shield');
        array_unshift($links, '<a href="' . esc_url($url) . '">Ustawienia</a>');
        return $links;
    }

    public static function send_security_headers() {
        if (headers_sent() || is_admin() || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $options = self::get_options();

        if (!empty($options['enable_hsts']) && is_ssl()) {
            $hsts = 'max-age=' . max(0, intval($options['hsts_max_age']));
            if (!empty($options['hsts_include_subdomains'])) {
                $hsts .= '; includeSubDomains';
            }
            if (!empty($options['hsts_preload'])) {
                $hsts .= '; preload';
            }
            header('Strict-Transport-Security: ' . $hsts, true);
        }

        if (!empty($options['enable_csp']) && !empty($options['csp_value'])) {
            header('Content-Security-Policy: ' . str_replace(["\r", "\n"], '', trim($options['csp_value'])), true);
        }

        if (!empty($options['enable_permissions_policy']) && !empty($options['permissions_policy_value'])) {
            header('Permissions-Policy: ' . str_replace(["\r", "\n"], '', trim($options['permissions_policy_value'])), true);
        }

        header('Referrer-Policy: strict-origin-when-cross-origin', true);
        header('X-Content-Type-Options: nosniff', true);
        header('X-Frame-Options: SAMEORIGIN', true);
    }

    public static function cleanup_wp_head() {
        $options = self::get_options();

        if (!empty($options['remove_generator'])) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
        }
        if (!empty($options['remove_rsd_links'])) {
            remove_action('wp_head', 'rsd_link');
        }
        if (!empty($options['remove_wlwmanifest'])) {
            remove_action('wp_head', 'wlwmanifest_link');
        }
        if (!empty($options['remove_shortlink'])) {
            remove_action('wp_head', 'wp_shortlink_wp_head');
        }

        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('template_redirect', 'rest_output_link_header', 11);
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }

    public static function harden_rest_endpoints($endpoints) {
        $options = self::get_options();

        if (!empty($options['disable_user_rest'])) {
            foreach ($endpoints as $route => $handlers) {
                if (strpos($route, '/wp/v2/users') !== false || strpos($route, '/wp/v2/users/(?P<id>') !== false) {
                    unset($endpoints[$route]);
                }
            }
        }

        if (!empty($options['disable_oembed_routes'])) {
            foreach ($endpoints as $route => $handlers) {
                if (strpos($route, 'oembed') !== false) {
                    unset($endpoints[$route]);
                }
            }
        }

        return $endpoints;
    }

    public static function block_author_enumeration() {
        $options = self::get_options();
        if (empty($options['disable_author_scan'])) {
            return;
        }

        if (isset($_GET['author']) && !is_admin()) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
    }

    public static function current_description() {
        if (is_singular()) {
            global $post;
            if ($post instanceof WP_Post) {
                $excerpt = trim(wp_strip_all_tags(get_the_excerpt($post)));
                if ($excerpt !== '') {
                    return $excerpt;
                }
                $content = trim(wp_strip_all_tags($post->post_content));
                if ($content !== '') {
                    return wp_html_excerpt($content, 160, '...');
                }
            }
        }

        $options = self::get_options();
        if (!empty($options['default_meta_description'])) {
            return $options['default_meta_description'];
        }

        return get_bloginfo('description', 'display');
    }

    public static function current_image() {
        if (is_singular() && has_post_thumbnail()) {
            $thumb = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
            if ($thumb) {
                return $thumb;
            }
        }

        $options = self::get_options();
        if (!empty($options['fallback_og_image'])) {
            return $options['fallback_og_image'];
        }

        return '';
    }

    public static function current_title() {
        if (is_front_page() || is_home()) {
            return get_bloginfo('name');
        }
        return wp_get_document_title();
    }

    public static function current_url() {
        if (is_singular()) {
            return get_permalink();
        }
        return home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
    }

    public static function render_meta_tags() {
        if (is_admin() || is_feed() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $options = self::get_options();
        $title = self::current_title();
        $description = self::current_description();
        $url = self::current_url();
        $image = self::current_image();
        $site_name = get_bloginfo('name');
        $type = is_singular() ? 'article' : 'website';

        echo "\n<!-- Heterodyna WordPress Shield -->\n";
        if ($description !== '') {
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
        }
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        if ($description !== '') {
            echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        }
        echo '<meta property="og:type" content="' . esc_attr($type) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '" />' . "\n";
        if ($image !== '') {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
        }
        echo '<meta name="twitter:card" content="' . esc_attr($options['twitter_card_type']) . '" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        if ($description !== '') {
            echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
        }
        if ($image !== '') {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
        }
    }

    public static function dmarc_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'settings_page_heterodyna-wordpress-shield') {
            return;
        }
        echo '<div class="notice notice-warning"><p><strong>DMARC:</strong> tego elementu nie naprawi sama wtyczka, bo to rekord DNS dla poczty. Dodaj w DNS rekord TXT dla <code>_dmarc.twojadomena.pl</code>, np. <code>v=DMARC1; p=quarantine; rua=mailto:dmarc@twojadomena.pl; adkim=s; aspf=s</code>. Wtyczka tylko przypomina o konfiguracji.</p></div>';
    }

    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Brak uprawnień.');
        }

        $o = self::get_options();
        ?>
        <div class="wrap">
            <h1>Heterodyna WordPress Shield</h1>
            <p>Wtyczka umożliwia konfigurację wybranych nagłówków i opcji WordPress. Włączanie HSTS/CSP wymaga przetestowania strony i subdomen.</p>

            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px 20px;margin:16px 0;max-width:1100px;box-shadow:0 2px 8px rgba(0,0,0,.04);">
                <h2 style="margin-top:0;">Co ta wtyczka zrobi</h2>
                <ul style="list-style:disc;padding-left:20px;">
                    <li>doda HSTS, CSP i Permissions-Policy,</li>
                    <li>doda meta description, OG:image i Twitter card,</li>
                    <li>wyłączy XML-RPC i ukryje wybrane endpointy REST,</li>
                    <li>utrudni prostą enumerację autorów i ukryje część śladów WordPressa.</li>
                </ul>
                <p><strong>Uwaga:</strong> CSP może zablokować skrypty i formularze. Domyślny profil jest zgodnościowy, nie rygorystyczny (zawiera unsafe-inline/unsafe-eval). Przetestuj na stronie testowej.</p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(HST_OPTION); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr><th colspan="2"><h2>Nagłówki bezpieczeństwa</h2></th></tr>
                        <tr>
                            <th scope="row">Włącz HSTS</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[enable_hsts]" value="1" <?php checked(!empty($o['enable_hsts'])); ?> /> Tak</label>
                                <p class="description">Działa tylko po HTTPS. Uwaga: HSTS jest zapamiętywany przez przeglądarki; przetestuj HTTPS przed włączeniem. includeSubDomains może wpłynąć na wszystkie subdomeny.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">HSTS max-age</th>
                            <td><input type="number" min="0" step="1" class="regular-text" name="<?php echo esc_attr(HST_OPTION); ?>[hsts_max_age]" value="<?php echo esc_attr($o['hsts_max_age']); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row">Opcje HSTS</th>
                            <td>
                                <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[hsts_include_subdomains]" value="1" <?php checked(!empty($o['hsts_include_subdomains'])); ?> /> includeSubDomains</label>
                                <label style="display:block;"><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[hsts_preload]" value="1" <?php checked(!empty($o['hsts_preload'])); ?> /> preload</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Włącz Content-Security-Policy</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[enable_csp]" value="1" <?php checked(!empty($o['enable_csp'])); ?> /> Tak</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Wartość CSP</th>
                            <td><textarea class="large-text code" rows="8" name="<?php echo esc_attr(HST_OPTION); ?>[csp_value]"><?php echo esc_textarea($o['csp_value']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row">Włącz Permissions-Policy</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[enable_permissions_policy]" value="1" <?php checked(!empty($o['enable_permissions_policy'])); ?> /> Tak</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Wartość Permissions-Policy</th>
                            <td><input type="text" class="large-text code" name="<?php echo esc_attr(HST_OPTION); ?>[permissions_policy_value]" value="<?php echo esc_attr($o['permissions_policy_value']); ?>" /></td>
                        </tr>

                        <tr><th colspan="2"><h2>SEO i social</h2></th></tr>
                        <tr>
                            <th scope="row">Domyślny meta description</th>
                            <td><textarea class="large-text" rows="4" name="<?php echo esc_attr(HST_OPTION); ?>[default_meta_description]"><?php echo esc_textarea($o['default_meta_description']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row">Domyślny OG:image</th>
                            <td>
                                <input type="url" class="large-text" name="<?php echo esc_attr(HST_OPTION); ?>[fallback_og_image]" value="<?php echo esc_attr($o['fallback_og_image']); ?>" placeholder="https://twojadomena.pl/wp-content/uploads/og-image.jpg" />
                                <p class="description">Jeśli wpis nie ma obrazka wyróżniającego, użyty zostanie ten adres.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Typ Twitter card</th>
                            <td>
                                <select name="<?php echo esc_attr(HST_OPTION); ?>[twitter_card_type]">
                                    <option value="summary" <?php selected($o['twitter_card_type'], 'summary'); ?>>summary</option>
                                    <option value="summary_large_image" <?php selected($o['twitter_card_type'], 'summary_large_image'); ?>>summary_large_image</option>
                                </select>
                            </td>
                        </tr>

                        <tr><th colspan="2"><h2>Utwardzenie endpointów i śladów technicznych</h2></th></tr>
                        <tr>
                            <th scope="row">Ukryj REST users</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[disable_user_rest]" value="1" <?php checked(!empty($o['disable_user_rest'])); ?> /> Tak</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Wyłącz trasy oEmbed</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[disable_oembed_routes]" value="1" <?php checked(!empty($o['disable_oembed_routes'])); ?> /> Tak</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Blokuj prosty author scan</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[disable_author_scan]" value="1" <?php checked(!empty($o['disable_author_scan'])); ?> /> Tak</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Ukryj ślady WordPressa</th>
                            <td>
                                <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[remove_generator]" value="1" <?php checked(!empty($o['remove_generator'])); ?> /> Usuń generator</label>
                                <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[remove_rsd_links]" value="1" <?php checked(!empty($o['remove_rsd_links'])); ?> /> Usuń RSD</label>
                                <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[remove_wlwmanifest]" value="1" <?php checked(!empty($o['remove_wlwmanifest'])); ?> /> Usuń wlwmanifest</label>
                                <label style="display:block;"><input type="checkbox" name="<?php echo esc_attr(HST_OPTION); ?>[remove_shortlink]" value="1" <?php checked(!empty($o['remove_shortlink'])); ?> /> Usuń shortlink</label>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button('Zapisz ustawienia'); ?>
            </form>

            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px 20px;margin:24px 0 0;max-width:1100px;box-shadow:0 2px 8px rgba(0,0,0,.04);">
                <h2 style="margin-top:0;">Roadmapa</h2>
                <p><strong>Zrobione:</strong> HSTS, CSP, Permissions-Policy, meta description, OG:image, Twitter card, XML-RPC off, ukrycie części endpointów, usunięcie części śladów WP.</p>
                <p><strong>Do zrobienia później:</strong> generator gotowych rekordów DNS (DMARC/SPF/DKIM), test nagłówków na żywo z panelu, automatyczne reguły .htaccess/Nginx, tryb raportu zgodności SEO/security.</p>
                <p><strong>Moja sugestia:</strong> po wdrożeniu zrób jeszcze rekord DMARC w DNS i przejrzyj CSP na front-endzie, bo to najczęściej wywala zewnętrzne skrypty lub fonty.</p>
            </div>
        </div>
        <?php
    }
}
