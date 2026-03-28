# Repository Guidelines

## Project Structure & Module Organization
`kmdg-menu-elements.php` is the plugin bootstrap and autoloader entrypoint. Core plugin logic lives in `classes/` under the `KMDG\MenuElements` namespace (`Plugin.php`, `Walker.php`, `EditWalker.php`). ACF local JSON is stored in `acf/`. Author Sass in `src/sass/`; compiled assets are committed to `dist/css/`. The embedded `aesir/framework/v1/` directory is third-party framework code and should only be changed intentionally.

## Build, Test, and Development Commands
Run `npm install` once to restore the Gulp toolchain.

- `npx gulp sass` compiles `src/sass/*.scss` into `dist/css/` with sourcemaps.
- `npx gulp watch` watches Sass and any future `src/js/` files during development.
- `php -l kmdg-menu-elements.php` checks the bootstrap for syntax errors.
- `find classes -name '*.php' -print0 | xargs -0 -n1 php -l` lint-checks plugin classes.

There is no working automated test suite in `package.json`; `npm test` currently exits with an error by design.

## Coding Style & Naming Conventions
Match the existing code style: 4-space indentation in PHP and SCSS, short array syntax only if the touched file already uses it, and keep method braces and spacing consistent with surrounding code. Use `PascalCase` file names for PHP classes in `classes/`, and keep namespace paths aligned with the autoloader. For styles, follow the existing BEM-like pattern such as `.menu-elements__column-wrap--line`. Edit `src/sass/*` first, then rebuild `dist/css/*`; do not hand-edit compiled CSS only.

## Testing Guidelines
Testing here is mostly manual. After each change, rebuild Sass if needed, lint changed PHP files, then verify behavior in WordPress:

- `Appearance > Menus` still shows the custom element metabox.
- Front-end menus still render correctly with `KMDG\MenuElements\Walker`.
- ACF-driven menu element settings still load from `acf/`.

## Commit & Pull Request Guidelines
No Git metadata is available in this checkout, so follow a conservative convention: short, imperative commit subjects with a single scope, for example `fix: preserve spacer markup` or `docs: add repository guidelines`. Keep commits atomic. PRs should explain the WordPress behavior changed, list manual verification steps, and include screenshots for admin or front-end menu UI changes. Call out any updates to committed build output in `dist/css/` or ACF JSON in `acf/`.

## WordPress-Specific Notes
Prefer hooks and filters over hard-coded behavior. Preserve backward compatibility with existing menu item types (`row`, `column`, `spacer`, `title`), and treat `aesir/framework/v1/vendor/` as vendored code.
