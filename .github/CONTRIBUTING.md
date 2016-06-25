# Joomla! Weblinks Contribution Guide

Thanks for your interest in this extension for Joomla. Please review the following notes about contributing and maintaining this package.

## Version Strategy

* When in doubt, refer to the new Joomla development strategy modelled on Semver (link).
* The major version of this package is synchronised with the major version of Joomla it is designed to run under.
  For example, Weblinks 3.x is designed to run under Joomla 3.x; Weblinks 4.x under Joomla 4.x; and so on.
* A new minor version will be released each time there is a new feature or a significant change made to the package.
* A new patch version will be released each time there is a bug or cosmetic fix made to the package.

## Pull Requests

First fork this repository under your own account and make the changes to the code that you want to make.
Then make a Pull Request against the `master` branch of this repository.

### @since tags

If adding new PHP class methods or properties, the `@since` tags should be given the value of `__DEPLOY_VERSION__`.
This special tag will be replaced when the new version is built.

## Bugs and Issues

You can browse existing issues [here](https://github.com/joomla-extensions/weblinks/issues).

If you find any new bugs, or want to raise any type of support issue, please use raise a
[new issue](https://github.com/joomla-extensions/weblinks/issues/new).

## Release Procedure

This is the procedure and checklist for creating a new package.

* Update the version number in `build.ini`.
* Run `phing set-version`.
* Pun `phing` to make the new package for the version.
* Run `phing tag`.
* Push the commits and tags up to Github.
* Go to the releases page on Github.
* Click on the new tag.
  - Click the `Edit Tag` button.
  - Fill in the title and description for the release.
  - Attach the zip-file for the package to the release. Wait for the file to complete uploading!
  - Click the `Publish Release` button.
* Create a new `<update>` tag in the `manifest.xml` file.
  - Change the `<version>` tag to the new version.
  - Change the `<downloadurl>` tag to match the URL of the new release.
  - Commit the change.
