<?php
/*
Plugin Name: ChatGPT Assistant Plugin
Plugin URI:  http://example.com
Description: A simple ChatGPT assistant plugin for WordPress to manipulate themes and plugins in real-time.
Version:     1.0
Author:      AutoGPT
Author URI:  http://example.com
License:     GPL2
*/

defined('ABSPATH') or die('No script kiddies please!');

// Debugging output to check file inclusion
error_log("ChatGPT Assistant Plugin: Including class-chatgpt-assistant.php");

// Include core plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-chatgpt-assistant.php';

// Check if class exists after inclusion
if (!class_exists('ChatGPT_Assistant')) {
    error_log("ChatGPT Assistant Plugin: ChatGPT_Assistant class not found after inclusion.");
} else {
    error_log("ChatGPT Assistant Plugin: ChatGPT_Assistant class loaded successfully.");
}

// Unlock all features by default
$features = array('advanced', 'developer', 'logs', 'diagnostics', 'performance', 'beta', 'vip', 'debug', 'safe', 'api', 'pro', 'config', 'compatibility', 'maintenance', 'secret', 'cleanup', 'security', 'usage', 'remote', 'updates', 'hidden');
foreach ($features as $feature) {
    update_option('chatgpt_assistant_' . $feature, true);
}

// Register activation hook
register_activation_hook(__FILE__, array('ChatGPT_Assistant', 'activate'));

// Register deactivation hook
register_deactivation_hook(__FILE__, array('ChatGPT_Assistant', 'deactivate'));

// Initialize the plugin
add_action('plugins_loaded', array('ChatGPT_Assistant', 'init'));
