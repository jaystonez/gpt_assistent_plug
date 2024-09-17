<?php

if (!class_exists('ChatGPT_Assistant')) {

class ChatGPT_Assistant {

    // Plugin initialization
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_rest_routes'));
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('wp_ajax_load_file', array(__CLASS__, 'load_file'));
        add_action('wp_ajax_save_file', array(__CLASS__, 'save_file'));
        add_action('wp_ajax_preview_file', array(__CLASS__, 'preview_file'));
        add_action('wp_ajax_list_themes', array(__CLASS__, 'list_themes'));
        add_action('wp_ajax_list_plugins', array(__CLASS__, 'list_plugins'));
        if (defined('WP_CLI') && WP_CLI) {
            self::register_wp_cli_commands();
        }
    }

    // Activation hook callback
    public static function activate() {
        // Generate API keys for remote access
        if (!get_option('chatgpt_assistant_api_key')) {
            update_option('chatgpt_assistant_api_key', bin2hex(random_bytes(32)));
        }
    }

    // Deactivation hook callback
    public static function deactivate() {
        // Remove API keys on deactivation
        delete_option('chatgpt_assistant_api_key');
    }

    // Add admin menu item
    public static function add_admin_menu() {
        add_menu_page(
            'ChatGPT Assistant',         // Page title
            'ChatGPT Assistant',         // Menu title
            'manage_options',            // Capability
            'chatgpt_assistant',         // Menu slug
            array(__CLASS__, 'admin_page') // Callback function
        );
    }

    // Admin page display
    public static function admin_page() {
        require_once plugin_dir_path(__FILE__) . 'admin-page.php';
    }

    // Validate file path to prevent directory traversal
    private static function validate_file_path($file_path) {
        $real_base = realpath(ABSPATH);
        $real_user_path = realpath($file_path);

        return ($real_user_path && strpos($real_user_path, $real_base) === 0);
    }

    // List installed themes
    public static function list_themes() {
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Unauthorized user');
            return;
        }

        $themes = wp_get_themes();
        ob_start();
        foreach ($themes as $theme_slug => $theme) {
            echo '<tr>';
            echo '<td>' . esc_html($theme->get('Name')) . '</td>';
            echo '<td>' . ($theme_slug == wp_get_theme()->get_stylesheet() ? 'Active' : 'Inactive') . '</td>';
            echo '<td><button class="activate-theme" data-theme="' . esc_attr($theme_slug) . '">Activate</button></td>';
            echo '</tr>';
        }
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }

    // List installed plugins
    public static function list_plugins() {
        if (!current_user_can('activate_plugins')) {
            wp_send_json_error('Unauthorized user');
            return;
        }

        $plugins = get_plugins();
        ob_start();
        foreach ($plugins as $plugin_file => $plugin_data) {
            $is_active = is_plugin_active($plugin_file);
            echo '<tr>';
            echo '<td>' . esc_html($plugin_data['Name']) . '</td>';
            echo '<td>' . ($is_active ? 'Active' : 'Inactive') . '</td>';
            echo '<td><button class="' . ($is_active ? 'deactivate-plugin' : 'activate-plugin') . '" data-plugin="' . esc_attr($plugin_file) . '">' . ($is_active ? 'Deactivate' : 'Activate') . '</button></td>';
            echo '</tr>';
        }
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }

    // Load file content
    public static function load_file() {
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Unauthorized user');
            return;
        }

        $file_path = sanitize_text_field($_POST['file_path']);

        if (empty($file_path)) {
            wp_send_json_error('File path cannot be empty.');
            return;
        }

        if (self::validate_file_path($file_path) && file_exists($file_path) && is_file($file_path)) {
            $content = file_get_contents($file_path);
            wp_send_json_success(array('content' => $content));
        } else {
            wp_send_json_error('File not found or not accessible');
        }
    }

    // Save file content
    public static function save_file() {
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Unauthorized user');
            return;
        }

        $file_path = sanitize_text_field($_POST['file_path']);
        $content = stripslashes($_POST['content']);

        if (empty($file_path)) {
            wp_send_json_error('File path cannot be empty.');
            return;
        }

        if (self::validate_file_path($file_path) && file_exists($file_path) && is_file($file_path) && is_writable($file_path)) {
            file_put_contents($file_path, $content);
            wp_send_json_success('File saved successfully');
        } else {
            wp_send_json_error('File not writable or not accessible');
        }
    }

    // Preview file content
    public static function preview_file() {
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Unauthorized user');
            return;
        }

        $file_path = sanitize_text_field($_POST['file_path']);
        $content = stripslashes($_POST['content']);

        set_transient('chatgpt_assistant_preview_content', $content, 60);

        add_filter('template_include', function($template) use ($content) {
            echo $content;
            exit;
        });

        wp_send_json_success('Preview generated successfully');
    }

    // Register WP-CLI commands
    public static function register_wp_cli_commands() {
        WP_CLI::add_command('chatgpt_assistant', array(__CLASS__, 'wp_cli_command_handler'));
    }

    // Register REST API routes
    public static function register_rest_routes() {
        register_rest_route('chatgpt-assistant/v1', '/remote-access', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_remote_access'),
            'permission_callback' => function() {
                $api_key = isset($_SERVER['HTTP_X_API_KEY']) ? sanitize_text_field($_SERVER['HTTP_X_API_KEY']) : '';
                return $api_key === get_option('chatgpt_assistant_api_key');
            }
        ));
    }

    // Handle remote access request
    public static function handle_remote_access(WP_REST_Request $request) {
        $params = $request->get_params();

        // Handle post creation task
        if (isset($params['task']) && $params['task'] === 'generate_post') {
            $title = isset($params['title']) ? sanitize_text_field($params['title']) : 'Untitled';
            $content = isset($params['content']) ? wp_kses_post($params['content']) : '';

            $post_data = array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'post'
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id) {
                return new WP_REST_Response(array('message' => 'Post created', 'post_id' => $post_id), 200);
            } else {
                return new WP_REST_Response(array('message' => 'Failed to create post'), 500);
            }
        }

        return new WP_REST_Response('Remote access granted.', 200);
    }

    // Handle WP-CLI commands
    public static function wp_cli_command_handler($args, $assoc_args) {
        WP_CLI::success("ChatGPT Assistant command executed.");
    }
}

} // End if class exists check
