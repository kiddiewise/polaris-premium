<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function () {
    add_editor_style('assets/css/main.css');
});
