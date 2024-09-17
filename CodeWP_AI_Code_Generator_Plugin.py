
import openai

class CodeWPGenerator:
    def __init__(self, api_key):
        openai.api_key = api_key

    def generate_plugin(self, plugin_name, description):
        prompt = f"Create a WordPress plugin called {plugin_name} that {description}."
        response = openai.Completion.create(
            engine="text-davinci-003",
            prompt=prompt,
            max_tokens=700
        )
        return response['choices'][0]['text']

# Usage:
code_wp = CodeWPGenerator(api_key='your_openai_api_key')
plugin_code = code_wp.generate_plugin('Hello World Plugin', 'displays a Hello World message in the footer.')
print(plugin_code)
    