## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Ensure you have added suitable tests and the test suite is passing (run `vendor/bin/phpunit`). Tests run against the `nonprod:sandbox` endpoint; set `ABLY_ENDPOINT` to use another.
5. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

## Release Process

This library uses [semantic versioning](http://semver.org/). For each release, the following needs to be done:

1. Create a new branch for the release, named like `release/2.0.1` (where `2.0.1` is what you're releasing, being the new version). Security and critical-bug fixes for `ably/ably-php` 1.x are released from the `1.x` branch instead of `main`; see [Releasing 1.x](#releasing-1x).
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

### Releasing 1.x

`ably/ably-php` 1.x gets security and critical-bug fixes until 28 October 2027, one year from the 2.0.0 release (see [UPDATING.md](./UPDATING.md)). They are made on the `1.x` branch, which starts from `main` just after the `1.1.12` release and before the 2.0 work, so its code is that of 1.1.12. Follow the steps above, with these differences:

- Make fixes as PRs against `1.x`. A fix that also applies to 2.x needs its own PR against `main`: the namespaces differ (`Ably\` and `Ably\PubSub\`), so commits can't be cherry-picked between the branches as they are. 1.x also supports PHP 7.2 and later, while 2.x needs 8.1, so a fix ported to 1.x must not use newer syntax.
- Release 1.x fixes as `1.1.x` patch versions only. `ably/ably-php-laravel` requires `~1.1.9`, so its users would not get a `1.2.0`.
- Branch the release from `1.x` (e.g. `release/1.1.13`). Make the release PR against `1.x`, and merge it into `1.x`.
- The version is `LIB_VERSION` in `src/Defaults.php`, in the `Ably` namespace. That branch has no `PackagingTest`, so check by hand that it matches the top `CHANGELOG.md` heading.
- Run the changelog generator with the previous 1.x tag and the branch, e.g. `--since-tag 1.1.12 --release-branch 1.x`, which limits pull requests to those merged into `1.x`. The output can still contain sections for the 2.x tags and closed 2.x issues; delete those, and keep only the new 1.x section. The links take the form `https://github.com/ably/ably-pubsub-php/compare/1.1.12...1.1.13`. Older 1.x entries link to `github.com/ably/ably-php`, the repository's name before it was renamed; those links still redirect, so leave them as they are.
- To tag, check out and update `1.x` instead of `main`: `git checkout 1.x && git pull origin 1.x && git log -1 --oneline`. Then tag it and push the tag - e.g.: `git tag 1.1.13 && git push origin 1.1.13`.
- When creating the GitHub release, untick "Set as the latest release" (or pass `--latest=false` to `gh release create`), so that 2.x stays marked as the latest release.
- Check https://packagist.org/packages/ably/ably-php instead of the `ably/pubsub-server` page. Both packages read this repository, so every tag is listed under both. On the `ably/ably-php` page, the new 1.1.x release appears below 2.0.x; that is expected, and users who require `^1.1` get the 1.1.x release. Under `ably/pubsub-server`, the 1.x tag doesn't affect users who require `^2.0`.
