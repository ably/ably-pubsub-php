## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Ensure you have added suitable tests and the test suite is passing (run `vendor/bin/phpunit`). Tests run against the `nonprod:sandbox` endpoint; set `ABLY_ENDPOINT` to use another.
5. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

## Release Process

This library uses [semantic versioning](http://semver.org/). For each release, the following needs to be done. For an `ably/ably-php` 1.x release, also see [Releasing 1.x](#releasing-1x).

1. Create a new branch for the release, named like `release/2.0.1` (where `2.0.1` is what you're releasing, being the new version).
2. Update `LIB_VERSION` in [`src/Defaults.php`](./src/Defaults.php). It is the only place the version is declared: Composer takes the package version from the git tag, so `composer.json` has no `version` field.
3. Run [`github_changelog_generator`](https://github.com/github-changelog-generator/github-changelog-generator) to automate the update of the [CHANGELOG.md](./CHANGELOG.md). This may require some manual intervention, both in terms of how the command is run and how the change log file is modified. Your mileage may vary:
    - The command you will need to run will look something like this: `github_changelog_generator -u ably -p ably-pubsub-php --since-tag 2.0.1 --exclude-tags-regex '^1\.' --output delta.md --token $GITHUB_TOKEN_WITH_REPO_ACCESS`. Generate token [here](https://github.com/settings/tokens/new?description=GitHub%20Changelog%20Generator%20token).
    - Using the command above, `--output delta.md` writes changes made after `--since-tag` (the previous 2.x release) to a new file. `--exclude-tags-regex '^1\.'` keeps 1.x release tags out of it, so a 1.x release made in the meantime doesn't show up as a section.
    - The contents of that new file (`delta.md`) then need to be manually inserted at the top of the `CHANGELOG.md`, changing the "Unreleased" heading and linking with the current version numbers. Leave out its `# Changelog` title and the "automatically generated" note at the end.
    - Also ensure that the "Full Changelog" link points to the new version tag instead of the `HEAD`. Tags have no `v` prefix (e.g. `2.0.1`), so the links take the form `https://github.com/ably/ably-pubsub-php/tree/2.0.1` and `https://github.com/ably/ably-pubsub-php/compare/2.0.0...2.0.1`.
    - Keep only the entries that matter to users of the SDK, such as code changes, fixes and new features. Leave out pull requests and issues that only touch documentation, tests or CI.
    - Add a short summary of the user-facing changes under the heading, and delete `delta.md` rather than committing it.
4. Commit the version bump and the generated [CHANGELOG.md](./CHANGELOG.md) together. `PackagingTest` fails unless `LIB_VERSION` matches the version in the top `CHANGELOG.md` heading.
5. Make a PR against `main`, titled like `Release 2.0.1`.
6. Once the PR is approved and CI passes, merge it into `main`.
7. Check out and update `main`, and check that the latest commit is the release PR's merge commit: `git checkout main && git pull origin main && git log -1 --oneline`. Then tag it and push the tag - e.g.: `git tag 2.0.1 && git push origin 2.0.1`. Never move or re-push a published tag: Packagist locks a stable version to the commit it first saw. If a release is wrong, release the next patch.
8. Visit https://github.com/ably/ably-pubsub-php/tags and create a release for the tag, named after the version. Use the new `CHANGELOG.md` entry, without its `##` heading line, as the release notes. Use absolute links: relative links such as `./UPDATING.md` don't resolve on the release page.
9. Visit https://packagist.org/packages/ably/pubsub-server and check that the new version is listed. If it isn't, log in to Packagist and click the "Update" button.
10. Create the entry on the [Ably Changelog](https://changelog.ably.com/) (via [headwayapp](https://headwayapp.co/)).

### Releasing 1.x

`ably/ably-php` 1.x gets security and critical-bug fixes until 28 October 2027, one year after the 2.0.0 release (see [UPDATING.md](./UPDATING.md)). They are made on the `1.x` branch. It starts from the last commit on `main` before the 2.0 work, so its code is that of 1.1.12. This guide lives on `main` only; the `1.x` branch has an older `CONTRIBUTING.md`.

Fixes for 1.x:

- Make them as PRs against `1.x`.
- If a fix also applies to 2.x, open a separate PR against `main`. The namespaces differ (`Ably\` and `Ably\PubSub\`), so commits can't be cherry-picked as they are.
- 1.x supports PHP 7.2 and later, and 2.x needs 8.1, so code ported to 1.x must not use newer syntax.
- On `1.x`, the tests read `ABLY_ENV` (default `sandbox`), not `ABLY_ENDPOINT`.

A 1.x release follows steps 1 to 10 above, with these changes:

- **Branch (step 1):** branch from `1.x`, e.g. `release/1.1.13`.
- **Version (step 2):** release only `1.1.x` patches. `ably/ably-php-laravel` requires `~1.1.9`, so its users would not get a `1.2.0`. `LIB_VERSION` is in `src/Defaults.php`, in the `Ably` namespace.
- **Changelog (step 3):** start from the previous 1.x tag, and leave out the tags of 2.x and later:

    ```sh
    github_changelog_generator -u ably -p ably-pubsub-php --since-tag 1.1.12 --release-branch 1.x --exclude-tags-regex '^[2-9]\.' --output delta.md --token $GITHUB_TOKEN_WITH_REPO_ACCESS
    ```

    - `--release-branch 1.x` is needed, so that pull requests merged only into `1.x` are listed.
    - The output can still list closed 2.x issues. Delete those.
    - The first release after 1.1.12 also lists the CI and repository-rename pull requests that `1.x` has after 1.1.12. Leave them out, as in step 3.
    - The links take the form `https://github.com/ably/ably-pubsub-php/tree/1.1.13` and `https://github.com/ably/ably-pubsub-php/compare/1.1.12...1.1.13`. Older 1.x entries link to `github.com/ably/ably-php`, the repository's name before it was renamed. Those links still redirect, so leave them as they are.
- **Check (step 4):** `1.x` has no `PackagingTest`, so check by hand that `LIB_VERSION` matches the version in the top `CHANGELOG.md` heading.
- **PR (steps 5 and 6):** open the release PR against `1.x`, and merge it into `1.x`.
- **Tag (step 7):** check out and update `1.x` instead of `main`, and check that the latest commit is the release PR's merge commit: `git checkout 1.x && git pull origin 1.x && git log -1 --oneline`. Then tag it and push the tag: `git tag 1.1.13 && git push origin 1.1.13`.
- **GitHub release (step 8):** untick "Set as the latest release", or pass `--latest=false --verify-tag` to `gh release create`. Otherwise GitHub may show 1.1.13, rather than the newest 2.x release, as "Latest". Without `--verify-tag`, a tag that isn't pushed yet is created from `main`, which holds 2.x code.
- **Packagist (step 9):** check https://packagist.org/packages/ably/ably-php, not the `ably/pubsub-server` page. Both packages read this repository, so a tag shows up under both. On the `ably/ably-php` page, 1.1.13 is listed below 2.0.x. That is expected: users who require `^1.1` still get it, and users who require `^2.0` aren't affected.

Keep 1.x releases off `main`: don't merge a 1.x release commit into `main`, and don't add its changelog entry to the top of `main`'s `CHANGELOG.md`. `PackagingTest` on `main` requires the top entry to match the 2.x `LIB_VERSION`.
