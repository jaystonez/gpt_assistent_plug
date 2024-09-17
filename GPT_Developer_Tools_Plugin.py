
import openai

class GPTDeveloperTools:
    def __init__(self, api_key):
        openai.api_key = api_key

    def generate_code(self, prompt):
        response = openai.Completion.create(
            engine="text-davinci-003",
            prompt=prompt,
            max_tokens=500
        )
        return response['choices'][0]['text']

    def debug_code(self, code):
        prompt = f"Debug the following code:
{code}"
        response = openai.Completion.create(
            engine="text-davinci-003",
            prompt=prompt,
            max_tokens=500
        )
        return response['choices'][0]['text']

# Usage:
developer_tools = GPTDeveloperTools(api_key='your_openai_api_key')
code_snippet = developer_tools.generate_code('Write a WordPress plugin to display a hello world message.')
print(code_snippet)
    