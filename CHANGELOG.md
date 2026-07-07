# Simple RP Menu Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.1.0 - 2024-03-22
### Added
- Dynamic dropdown menus with automatic structure and entry fetching.
- Added `dynamicSource`, `maxLevel`, and `dynamicPosition` settings to the Control Panel UI.
- Recursive hierarchy generation for nested structure elements.
- Plugin-native support for rendering `customShortContent` in the frontend HTML.
- Implemented robust caching using `TagDependency` to prevent N+1 queries during menu rendering.

## 1.0.0 - 2022-12-28
### Added
- Initial release
