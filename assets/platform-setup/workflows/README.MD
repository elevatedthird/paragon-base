# E3 Github Workflows
There are three different workflows each corresponding to a different platform.
1. Ensure you have invited (insert our general e3 user we made) and ensure a senior dev has accepted the invite.
2. Go: https://github.com/elevatedthird/<repo-name>/settings/variables/actions
3. Under "Repository variable" add a variable named "DESTINATION_REPOSITORY" and paste the ssh url of the destination repository.
4. In your project root create a .github/workflows directory and copy the workflows you want to use from the assets/platform-setup/workflows directory.
4. Remove the .disable extension from the file you want to use and commit it to your repository.

## Specific Platform Instructions

### Acquia
1. Ensure you have a hooks/dev/post-code-update/drush-deploy.sh

### Pantheon
1. You need to go to: https://github.com/elevatedthird/<repo-name>/settings/variables/actions
2. Set up a variable called "PANTHEON_SITE" and set it to the machine name of the site you want to deploy to.
3. "PANTHEON_ENV" will be set automatically for you depending on your branch name. The workflow will only run on branches that are allowed.
   4. master
   5. md-* (should effectively push to a multidev environment)
   6. branches should have 11 characters or less

### Platform
1. Ensure your .platform.app.yaml deploy hook looks like this
```yaml
  deploy: |
    set -e
    php ./drush/platformsh_generate_drush_yml.php
    cd docroot
    drush deploy
```
