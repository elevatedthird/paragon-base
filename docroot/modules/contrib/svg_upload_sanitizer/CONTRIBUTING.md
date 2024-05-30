# Contributing to this project

## Installation

 * To install the project run `composer install`
 * To test the quality run `composer quality-analysis`

## Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include
as much relevant information as you can. A step by step guide on how to
reproduce the issue will greatly increase the chances of your issue being
resolved in a timely manner.

Please use one of the provided issue templates.

## Merge Request (instead of patches)

Since this project does not use the drupal.org issue queue
we also don't use patches. Instead we'll use a Merge Request (MR) process.

You could find more information about MR [on the gitlab documentation](https://docs.gitlab.com/ee/user/project/merge_requests/).

TL;DR:

 * Fork this repo
 * Clone the fork on your local machine (your remote repo on Gitlab
   is called `origin` by default)
 * Create a new branch to work on (for instance `issue/ISSUE_ID`)
 * Implement your feature/fix etc.. (follow the [Contributing Guidelines](https://gitlab.com/beram-drupal/svg-upload-sanitizer/blob/8.x-1.x/CONTRIBUTING.md))
 * Commit you work (try to follow [Karma for the git commit message](http://karma-runner.github.io/4.0/dev/git-commit-msg.html))
 * Push your branch to your fork on Gitlab (the remote `origin`)
 * From your fork open a merge request in the correct branch
   (choose the default branch i.e. `8.x-1.xdefault`)

Please use one of the provided MR templates.

## Further reading

 * PHP
   * [Clean Code PHP](https://github.com/jupeter/clean-code-php)
   * [Stories about good software architecture and how to create it with design patterns](https://sourcemaking.com/)
