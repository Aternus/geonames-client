# Welcome to GeoNames Client contributing guide <!-- omit in toc -->

Thank you for investing your time in contributing to our project! Any
contribution you make will be reflected
on [geonames-client](https://packagist.org/packages/aternus/geonames-client)
:sparkles:.

In this guide you will get an overview of the contribution workflow from opening
an issue, creating a PR, reviewing, and merging the PR.

Use the table of contents icon
<img alt="Table of contents icon" src="https://raw.githubusercontent.com/github/docs/aca38f817073690d281ae609bc963832027751d1/contributing/images/table-of-contents.png" width="25" height="25" />
in the top left corner of this document to get to a specific section of this
guide quickly.

## New contributor guide

To get an overview of the project, read the [README](README.md) file.
Here are some resources to help you get started with open source contributions:

- [Finding ways to contribute to open source on GitHub](https://docs.github.com/en/get-started/exploring-projects-on-github/finding-ways-to-contribute-to-open-source-on-github)
- [Set up Git](https://docs.github.com/en/get-started/getting-started-with-git/set-up-git)
- [GitHub flow](https://docs.github.com/en/get-started/using-github/github-flow)
- [Collaborating with pull requests](https://docs.github.com/en/github/collaborating-with-pull-requests)

## Getting started

### Issues

#### Create a new issue

If you spot a problem with GeoNames Client,
[search if an issue already exists](https://docs.github.com/en/github/searching-for-information-on-github/searching-on-github/searching-issues-and-pull-requests#search-by-the-title-body-or-comments).
If a related issue doesn't exist, you can open a new issue using a
relevant [issue form](https://github.com/Aternus/geonames-client/issues/new).

#### Solve an issue

Scan through
our [existing issues](https://github.com/Aternus/geonames-client/issues) to
find one that interests you. You can narrow down the search using `labels` as
filters.
As a general rule, we don’t assign issues to anyone. If you find an issue to
work on, you are welcome to open a PR with a fix.

### Make Changes

#### Make changes in a Codespace

For more information about using a Codespace for working on GeoNames Client,
see "[Creating a Codespace for a repository](https://docs.github.com/en/codespaces/developing-in-a-codespace/creating-a-codespace-for-a-repository)."

#### Make changes locally

1. Fork the repository.
    - Using GitHub Desktop:
        - [Getting started with GitHub Desktop](https://docs.github.com/en/desktop/installing-and-configuring-github-desktop/getting-started-with-github-desktop)
          will guide you through setting up Desktop.
        - Once Desktop is set up, you can use it
          to [fork the repo](https://docs.github.com/en/desktop/contributing-and-collaborating-using-github-desktop/cloning-and-forking-repositories-from-github-desktop)!

    - Using the command line:
        - [Fork the repo](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo#forking-a-repository)
          so that you can make your changes without affecting the original
          project until you're ready to merge them.

2. Install **Docker**, so you can run the local development environment (
   see `docker-compose.yml`).

3. Create a working branch and start with your changes!

### Commit your update

Commit the changes once you are happy with them. :zap:.

### Pull Request

When you're finished with the changes, create a pull request, also known as a
PR.

- Fill in the details of your changes including context and possible alternative
  considered. This helps reviewers understand your changes as well as the
  purpose of your pull request.
- Don't forget
  to [link PR to issue](https://docs.github.com/en/issues/tracking-your-work-with-issues/linking-a-pull-request-to-an-issue)
  if you are solving one.
- Enable the checkbox
  to [allow maintainer edits](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/allowing-changes-to-a-pull-request-branch-created-from-a-fork)
  so the branch can be updated for a merge.
  Once you submit your PR, a GeoNames Client team member will review your
  proposal. We may ask questions or request additional information.
- We may ask for changes to be made before a PR can be merged, either
  using [suggested changes](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/incorporating-feedback-in-your-pull-request)
  or pull request comments. You can apply suggested changes directly through the
  UI. You can make any other changes in your fork, then commit them to your
  branch.
- As you update your PR and apply changes, mark each conversation
  as [resolved](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/commenting-on-a-pull-request#resolving-conversations).
- If you run into any merge issues, checkout
  this [git tutorial](https://github.com/skills/resolve-merge-conflicts) to help
  you resolve merge conflicts and other issues.

### Your PR is merged!

Congratulations :tada::tada: The GeoNames Client team thanks you :sparkles:.

Once your PR is merged, your contributions will be publicly available to anyone
using
the [geonames-client](https://packagist.org/packages/aternus/geonames-client)
Packagist package.
