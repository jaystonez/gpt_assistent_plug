
<?php
/**
 * Plugin Name: ChatGPT Assistant
 * Description: A plugin that integrates AI functionalities with WordPress.
 * Version: 1.0
 * Author: Your Name
 */

// Add a new menu item to the WordPress admin dashboard
function chatgpt_add_admin_menu() {
    add_menu_page(
        'AI Actions',              // Page title
        'AI Actions',              // Menu title
        'manage_options',          // Capability
        'ai-actions',              // Menu slug
        'chatgpt_admin_page',      // Function to display the page
        'dashicons-admin-tools',   // Icon
        6                          // Position
    );
}
add_action('admin_menu', 'chatgpt_add_admin_menu');

// Function to display the admin page content
function chatgpt_admin_page() {
    echo '<div class="wrap"><h1>Execute AI Actions</h1>';
    echo '<button id="run-python-script" class="button button-primary">Run Python Script</button>';
    echo '<div id="output"></div>';
    echo '</div>';
    echo '<script>
            jQuery(document).ready(function($) {
                $("#run-python-script").click(function() {
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "run_python_script"
                        },
                        success: function(response) {
                            $("#output").html("<pre>" + response + "</pre>");
                        }
                    });
                });
            });
          </script>';
}

// Register AJAX action to run the Python script
function chatgpt_run_python_script() {
    // Ensure only authorized users can run the script
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }

    // Run the Python script and capture the output
    $output = shell_exec('python3 ' . plugin_dir_path(__FILE__) . 'ChatGPTAssistant.py');
    echo $output;
    wp_die(); // Required to terminate immediately and return a proper response
}
add_action('wp_ajax_run_python_script', 'chatgpt_run_python_script');
