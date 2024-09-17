
import os

class PluginPack:
    def __init__(self, repo_url):
        self.repo_url = repo_url

    def clone_repository(self):
        os.system(f'git clone {self.repo_url}')

    def push_changes(self, commit_message):
        os.system(f'git add . && git commit -m "{commit_message}" && git push')

# Usage:
plugin_pack = PluginPack('https://github.com/user/wordpress-plugin-repo.git')
plugin_pack.clone_repository()
plugin_pack.push_changes('Initial plugin setup')
    