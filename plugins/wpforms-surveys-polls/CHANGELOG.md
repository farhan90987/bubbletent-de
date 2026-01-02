# Changelog
All notable changes to this project will be documented in this file, formatted via [this recommendation](https://keepachangelog.com/).

## [1.16.0] - 2025-10-28
### IMPORTANT
- Support for PHP 7.1 has been discontinued. If you are running PHP 7.1, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.

### Added
- Compatibility with the upcoming WPForms 1.9.8.3 release.

### Changed
- Likert Scale styling for mobile devices.

### Fixed
- Survey results could appear in a different (random) order than the original form field order.

## [1.15.1] - 2025-03-17
### Fixed
- A JS error was preventing Calculating Field Results from being viewed.

## [1.15.0] - 2025-02-25
### IMPORTANT
- Support for PHP 7.0 has been discontinued. If you are running PHP 7.0, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.

### Changed
- The minimum WPForms version supported is 1.9.4.
- Updated Chart.js library to v4.4.4.

### Fixed
- The compatibility with the Layout field was improved.
- Resolved W3C errors and warnings reported for the Likert Scale and Net Promoter Score fields.
- A negative value in S&P results on Entries Details page occurred when some entries were added to Spam.
- There were warnings in the debug log during the form submission in some cases.

## [1.14.0] - 2024-08-06
### Changed
- Minimum WPForms version supported is 1.9.0.

### Fixed
- Compatibility with WPForms 1.9.0.

## [1.13.0] - 2024-04-24
### Changed
- Improved field layout on the frontend for better user experience on mobile devices.
- Bring the frontend markup of the form more in line with the W3C standards to reduce validator errors.

### Fixed
- Net Promoter Score field labels were not editable.
- Error state of the Net Promoter Score field was improved in Classic frontend mode.
- Various RTL problems in the admin dashboard, form builder and a form preview page.
- PHP Warning on Entry Edit page when the Likert Scale field was used.

## [1.12.0] - 2023-11-21
### IMPORTANT
- Support for PHP 5.6 has been discontinued. If you are running PHP 5.6, you MUST upgrade PHP before installing WPForms Surveys and Polls 1.12.0. Failure to do that will disable WPForms Surveys and Polls functionality.
- Support for WordPress 5.4 and below has been discontinued. If you are running any of those outdated versions, you MUST upgrade WordPress before installing WPForms Surveys and Polls 1.12.0. Failure to do that will disable WPForms Surveys and Polls functionality.

### Added
- Compatibility with WPForms 1.8.5.

### Changed
- Minimum WPForms version supported is 1.8.5.
- The help tooltip was removed in the Form Builder for consistency.

## Fixed
- Improved handling of redirects on the Survey Result page with some configurations.
- When the form had no entries, a fetch error message was displayed instead of a proper informational message.
- The Focused state for the Likert Scale field was rendered incorrectly when unchecked.
- Compatibility with the Divi page builder.
- Compatibility with the Hello Elementor theme.
- Net promoter fields used with Divi did not respect field sizes.

## [1.11.0] - 2023-03-27
### Added
- Compatibility with the upcoming WPForms v1.8.1 release.
- Several new filters were added for developers to modify the output of survey results on the front end.

### Changed
- Improved the way various UI elements handle longer text in different languages.

### Fixed
- Poll results had the same ID when there was more than one field.
- The Likert Scale field values used new lines inconsistently on the Entry view page.
- The Survey Results page displayed an unstyled error message instead of an error page when the form contained no entries.
- The Likert Scale field with multiple responses per row was displaying incorrect values when editing an entry.
- Cache was not cleared for all fields that support Survey Reporting after editing or deleting entries.

## [1.10.0] - 2022-09-27
### Changed˚
- Minimum WPForms version supported is 1.7.7.

### Fixed
- Likert and Net Promoter fields were broken in Block Editor in WordPress 5.2-5.4.
- The compatibility with the Layout field was improved.
- The Likert field was displayed incorrectly on mobile devices.

## [1.9.0] - 2022-08-30
### Changed
- Minimum WPForms version supported is 1.7.5.5.
- Improve formatting of Likert Scale entries on the Entries List and Single Entry pages.

### Fixed
- Likert Scale field row/column labels are now updated in the Form Builder preview as you type.
- Reduced code complexity and replaced improperly used variable.
- Fallback value for the Likert Scale field wasn't populated on page refresh after a failed form submission.
- Survey results were broken on mobile.
- Poll results were not shown correctly when the "Enable Poll Results" option was enabled for dynamic choices for several fields: Dropdown, Checkbox, and Multiple Choice.

## [1.8.0] - 2022-05-26
### Changed
- Minimum WPForms version supported is 1.7.4.2.

### Fixed
- WordPress 6.0 compatibility: Likert Scale and Net Promoter Score fields styling fixed inside the Full Site Editor.
- Improved compatibility with WordPress Multisite installations.
- Survey results were shown even if a form was no longer available.

## [1.7.0] - 2022-03-16
### Added
- Compatibility with WPForms 1.6.8 and the updated Form Builder.
- Compatibility with WPForms 1.7.3 and Form Revisions.
- Compatibility with WPForms 1.7.3 and search functionality on the Entries page.

### Changed
- Minimum WPForms version supported is 1.7.3.

### Fixed
- Incorrect styling of Likert Scale field with long labels.

## [1.6.4] - 2021-03-31
### Changed
- Replaced `jQuery.ready()` function with recommended way since jQuery 3.0.

### Fixed
- The "Export Entries (CSV)" link on Survey Results page.

## [1.6.3] - 2020-12-17
### Fixed
- Poll results not displaying correctly with AJAX forms.
- Form scrolls to the top when clicking on the Likert Scale field option with some themes.
- Poll results incorrectly calculate a select field with multiple selections enabled.

## [1.6.2] - 2020-08-05
### Fixed
- Survey report cache not always clearing when it should.

## [1.6.1] - 2020-04-16
### Added
- Compatibility check for WPForms v1.6.0.1.

## [1.6.0] - 2020-04-15
### Added
- Entry editing support for Net Promoter Score and Likert Scale fields.

### Fixed
- Survey report image exports not containing white background color.

## [1.5.1] - 2020-03-03
### Changed
- Compatibility with a new version of Choices.js library in WPForms core plugin.

### Fixed
- Abandoned form entries increase survey "skipped" count.

## [1.5.0] - 2020-01-09
### Added
- Support for Access Control.

### Fixed
- PHP notice on a Print Survey results page.
- Properly display polls results votes count in a chart using `[wpforms_poll]` shortcode when there are thousands of replies.
- Question numbering on single question print page.

## [1.4.0] - 2019-07-23
### Added
- Complete translations for French and Portuguese (Brazilian).
- Display alert when entry storage is disabled and polls are enabled.

## [1.3.2] - 2019-02-25
### Fixed
- PHP notice when printing survey results.

## [1.3.1] - 2019-02-08
### Fixed
- Typos, grammar, and other i18n related issues.

## [1.3.0] - 2019-02-06
### Added
- Complete translations for Spanish, Italian, Japanese, and German.

### Fixed
- Typos, grammar, and other i18n related issues.

## [1.2.2] - 2018-12-27
### Changed
- Likert and NPS field display priority in the form builder.

## [1.2.1] - 2018-10-19
### Fixed
- Typos with NPS form templates.

## [1.2.0] - 2018-08-28
### Added
- Net Promoter Score survey form templates.

## [1.1.0] - 2018-06-07
### Added
- Net Promoter Score field and reporting.

### Changed
- Minor styling adjustments to Likert to improve theme compatibility.

### Fixed
- Survey report print preview issue hiding empty fields.
- Not Recognizing false poll shortcode attribute values

## [1.0.0] - 2018-02-13
### Added
- Initial release.
