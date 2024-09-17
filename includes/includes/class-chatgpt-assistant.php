<?php

if (!class_exists('ChatGPT_Assistant')) {

class ChatGPT_Assistant {

    // Plugin initialization
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('wp_ajax_load_file', array(__CLASS__, 'load_file'));
        add_action('wp_ajax_save_file', array(__CLASS__, 'save_file'));
        add_action('wp_ajax_preview_file', array(__CLASS__, 'preview_file'));
        add_action('wp_ajax_list_themes', array(__CLASS__, 'list_themes'));
        add_action('wp_ajax_list_plugins', array(__CLASS__, 'list_plugins'));
        add_action('rest_api_init', array(__CLASS__, 'register_rest_routes'));  // Add REST API initialization

        if (defined('WP_CLI') && WP_CLI) {
            self::register_wp_cli_commands();
        }
    }

    // Activation hook callback
    public static function activate() {
        // Code to run on plugin activation
    }

    // Deactivation hook callback
    public static function deactivate() {
        // Code to run on plugin deactivation
    }

    // Add REST routes
    public static function register_rest_routes() {
        register_rest_route('chatgpt-assistant/v1', '/remote-access', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_remote_access'),
            'permission_callback' => '__return_true'
        ));
    }

    // Handle API access tasks
    public static function handle_remote_access( $request ) {
        $params = $request->get_params();
        $task = isset($params['task']) ? $params['task'] : '';

        if ($task === 'generate_content') {
            $topic = isset($params['topic']) ? $params['topic'] : 'General topic';
            $generated_content = "This is generated content about " . $topic;
            return new WP_REST_Response(array('message' => 'Content generated', 'content' => $generated_content), 200);
        } elseif ($task === 'test_remote_access') {
            return new WP_REST_Response(array('message' => 'Test successful', 'input' => $params), 200);
        } else {
            return new WP_REST_Response(array('message' => 'Unknown task', 'task' => $task), 400);
        }
    }

    // Add admin menu item
    public static function add_admin_menu() {
        add_menu_page(
            'ChatGPT Assistant',         // Page title
            'ChatGPT Assistant',         // Menu title
            'manage_options',            // Capability
            'chatgpt-assistant',         // Menu slug
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

        // Save changes temporarily in a transient or in a temporary file
        set_transient('chatgpt_assistant_preview_content', $content, 60);

        // Apply temporary changes for preview
        add_filter('template_include', function($template) use ($content) {
            // Temporarily override template with the content
            echo $content;
            exit;
        });

        wp_send_json_success('Preview generated successfully');
    }

    // Register WP-CLI commands
    public static function register_wp_cli_commands() {
        WP_CLI::add_command('chatgpt_assistant', array(__CLASS__, 'wp_cli_command_handler'));
    }

    // Handle WP-CLI commands
    public static function wp_cli_command_handler($args, $assoc_args) {
        WP_CLI::success("ChatGPT Assistant command executed.");
        // Add custom command handling logic here
    }
}

} // End if class exists check