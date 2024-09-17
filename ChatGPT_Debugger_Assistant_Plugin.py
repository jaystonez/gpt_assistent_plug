
import openai

class DebuggerAssistant:
    def debug_code(self, code_snippet):
        prompt = f"Debug this WordPress plugin code:\n{code_snippet}"
        response = openai.Completion.create(
            engine="text-davinci-003",
            prompt=prompt,
            max_tokens=500
        )
        return response['choices'][0]['text']

# Usage:
debugger = DebuggerAssistant()
code = '''
<?php
/**
 * Plugin Name: Custom Post Type Plugin
 * Description: Registers a custom post type called Books.
 */
function create_books_post_type() {
    register_post_type('books',
        array(
            'labels' => array(
                'name' => __('Books'),
                'singular_name' => __('Book')
            ),
            'public' => true,
            'has_archive' => true,
        )
    );
}
add_action('init', 'create_books_post_type');
?>
'''
debug_report = debugger.debug_code(code)
print(debug_report)
    