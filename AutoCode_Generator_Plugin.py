
import openai

class AutoCodeGenerator:
    def generate_code(self, task_description):
        response = openai.Completion.create(
            engine="text-davinci-003",
            prompt=f"Generate code to {task_description}",
            max_tokens=700
        )
        return response['choices'][0]['text']

# Usage:
auto_code = AutoCodeGenerator()
plugin_code = auto_code.generate_code('create a custom post type in WordPress called "Books"')
print(plugin_code)
    