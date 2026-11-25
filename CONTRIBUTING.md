## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Ensure you have added suitable tests and the test suite is passing (run `vendor/bin/phpunit`). Tests run against the `nonprod:sandbox` endpoint; set `ABLY_ENDPOINT` to use another.
5. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

## Release Process

This library uses [semantic versioning](http://semver.org/). For each release, the following needs to be done:

1. Create a new branch for the release, named like `release/2.0.1` (where `2.0.1` is what you're releasing, being the new version). Security and critical-bug fixes for `ably/ably-php` 1.x go to the `maintenance/1.x` branch instead of `main` (see [UPDATING.md](./UPDATING.md)).
2. Update `LIB_VERSION` in [`src/Defaults.php`](./src/Defaults.php). It is the only place the version is declared: Composer takes the package version from the git tag, so `composer.json` has no `version` field.
3. Run [`github_changelog_generator`](https://github.com/github-changelog-generator/github-changelog-generator) to automate the update of the [CHANGELOG.md](./CHANGELOG.md). This may require some manual intervention, both in terms of how the command is run and how the change log file is modified. Your mileage may vary:
    - The command you will need to run will look something like this: `github_changelog_generator -u ably -p ably-pubsub-php --since-tag 2.0.0 --output delta.md --token $GITHUB_TOKEN_WITH_REPO_ACCESS`. Generate token [here](https://github.com/settings/tokens/new?description=GitHub%20Changelog%20Generator%20token).
    - Using the command above, `--output delta.md` writes changes made after `--since-tag` to a new file.
    - The contents of that new file (`delta.md`) then need to be manually inserted at the top of the `CHANGELOG.md`, changing the "Unreleased" heading and linking with the current version numbers.
    - Also ensure that the "Full Changelog" link points to the new version tag instead of the `HEAD`. Tags have no `v` prefix (e.g. `2.0.1`), so the links take the form `https://github.com/ably/ably-pubsub-php/tree/2.0.1` and `https://github.com/ably/ably-pubsub-php/compare/2.0.0...2.0.1`.
    - Keep only the entries that matter to users of the SDK, such as code changes, fixes and new features. Leave out pull requests and issues that only touch documentation, tests or CI.
    - Add a short summary of the user-facing changes under the heading, and delete `delta.md` rather than committing it.
4. Commit the version bump and the generated [CHANGELOG.md](./CHANGELOG.md) together. `PackagingTest` fails unless `LIB_VERSION` matches the version in the top `CHANGELOG.md` heading.
5. Make a PR against `main`, titled like `Release 2.0.1`.
6. Once the PR is approved and CI passes, merge it into `main`.
7. Check out and update `main`, and check that the latest commit is the release PR's merge commit: `git checkout main && git pull origin main && git log -1 --oneline`. Then tag it and push the tag - e.g.: `git tag 2.0.1 && git push origin 2.0.1`.
8. Visit https://github.com/ably/ably-pubsub-php/tags and create a release for the tag, named after the version, using the new `CHANGELOG.md` entry as the release notes. Use absolute links: relative links such as `./UPDATING.md` don't resolve on the release page.
9. Visit https://packagist.org/packages/ably/pubsub-server and check that the new version is listed. If it isn't, log in to Packagist and click the "Update" button.
10. Create the entry on the [Ably Changelog](https://changelog.ably.com/) (via [headwayapp](https://headwayapp.co/)).
