
import subprocess
import openai

class WPCLIIntegration:
    def run_command(self, command):
        result = subprocess.run(command, shell=True, capture_output=True, text=True)
        return result.stdout

    def gpt_assist(self, prompt):
        response = openai.Completion.create(
            engine="text-davinci-003",
            prompt=prompt,
            max_tokens=500
        )
        return response['choices'][0]['text']

# Usage:
cli_tool = WPCLIIntegration()
cli_output = cli_tool.run_command('wp plugin list')
gpt_advice = cli_tool.gpt_assist('Optimize the following WordPress plugin list output:\n' + cli_output)
print(gpt_advice)
    