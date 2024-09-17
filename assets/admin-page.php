
<div class="wrap">
    <h1>ChatGPT Assistant - Theme and Plugin Management with Live Preview</h1>
    <p>Use the ChatGPT Assistant to manage your WordPress themes and plugins, and preview changes in real-time.</p>

    <!-- Theme Management Section -->
    <div style="margin-top: 20px;">
        <h2>Themes</h2>
        <table id="theme-list" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Theme Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Themes will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Plugin Management Section -->
    <div style="margin-top: 20px;">
        <h2>Plugins</h2>
        <table id="plugin-list" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Plugins will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Live Preview Section -->
    <div style="margin-top: 20px;">
        <h2>Live Preview</h2>
        <input type="text" id="file-path" placeholder="Enter the file path" />
        <textarea id="code-editor" name="code-editor" rows="20" cols="100"></textarea>
        <button id="load-button">Load File</button>
        <button id="save-button">Save Changes</button>
        <button id="preview-button">Live Preview</button>
        <iframe id="live-preview" src="<?php echo site_url(); ?>" style="width: 100%; height: 500px; border: 1px solid #ccc;"></iframe>
    </div>
</div>

<script>
// Load themes and plugins via AJAX
jQuery(document).ready(function($) {
    function loadThemes() {
        $.post(ajaxurl, { action: 'list_themes' }, function(response) {
            if (response.success) {
                $('#theme-list tbody').html(response.data.html);
            } else {
                console.error('Error loading themes: ', response.data);
            }
        });
    }

    function loadPlugins() {
        $.post(ajaxurl, { action: 'list_plugins' }, function(response) {
            if (response.success) {
                $('#plugin-list tbody').html(response.data.html);
            } else {
                console.error('Error loading plugins: ', response.data);
            }
        });
    }

    loadThemes();
    loadPlugins();

    // Initialize CodeMirror
    var editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
        lineNumbers: true,
        mode: "php",
        theme: "default"
    });

    // Load button functionality
    document.getElementById('load-button').addEventListener('click', function() {
        var filePath = document.getElementById('file-path').value;
        if (!filePath) {
            alert('Please enter a file path.');
            return;
        }
        console.log('Loading file from path: ', filePath);
        $.post(ajaxurl, { action: 'load_file', file_path: filePath }, function(response) {
            if (response.success) {
                editor.setValue(response.data.content);
            } else {
                console.error('Error loading file: ', response.data);
                alert('Error loading file: ' + response.data);
            }
        });
    });

    // Save button functionality
    document.getElementById('save-button').addEventListener('click', function() {
        var filePath = document.getElementById('file-path').value;
        var code = editor.getValue();
        if (!filePath) {
            alert('Please enter a file path.');
            return;
        }
        console.log('Saving file to path: ', filePath);
        $.post(ajaxurl, { action: 'save_file', file_path: filePath, content: code }, function(response) {
            if (response.success) {
                alert('File saved successfully');
            } else {
                console.error('Error saving file: ', response.data);
                alert('Error saving file: ' + response.data);
            }
        });
    });

    // Live Preview button functionality
    document.getElementById('preview-button').addEventListener('click', function() {
        var filePath = document.getElementById('file-path').value;
        var code = editor.getValue();
        if (!filePath) {
            alert('Please enter a file path.');
            return;
        }
        console.log('Previewing file from path: ', filePath);
        $.post(ajaxurl, { action: 'preview_file', file_path: filePath, content: code }, function(response) {
            if (response.success) {
                document.getElementById('live-preview').src = document.getElementById('live-preview').src;
            } else {
                console.error('Error generating preview: ', response.data);
                alert('Error generating preview: ' + response.data);
            }
        });
    });
});
</script>
