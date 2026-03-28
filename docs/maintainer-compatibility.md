# Maintainer Compatibility Notes

This plugin has a small public surface but a lot of behavior hangs off it. Treat the items below as stable unless a change is explicitly planned, reviewed, and smoke-tested.

## Stable Contracts

- Keep the global `MenuElements()` helper as the public bootstrap entrypoint. It must continue to return the singleton `KMDG\MenuElements\Plugin` instance.
- Keep the public walker class names `KMDG\MenuElements\Walker` and `KMDG\MenuElements\EditWalker`.
- Keep the built-in menu element slugs `row`, `column`, `spacer`, and `title`.
- Keep the default walker swap behavior: when `wp_nav_menu()` receives no custom walker, the plugin should continue to supply `KMDG\MenuElements\Walker`.
- Keep the menu editor walker replacement behavior: the plugin should continue to return `KMDG\MenuElements\EditWalker` from the `wp_edit_nav_menu_walker` filter.
- Keep the admin/front-end style handles `kmdg-menu-elements-admin` and `kmdg-menu-elements`.
- Preserve the add-to-menu flow in `Appearance > Menus`, including the "Custom Elements" metabox title, the "Select All" link, and the "Add to Menu" submit action.
- Preserve the existing menu item prototype shape used by the metabox checklist. Saved menu data is in scope for compatibility, not migration.
- Preserve the current front-end class and wrapper semantics:
  - `row` items act as structural markers and do not add custom inner markup.
  - `column` items append a `menu-elements__column--{size}` class during pre-render and emit a `menu-elements__column-wrap` wrapper, optionally with `menu-elements__column-wrap--line`.
  - `spacer` items emit a `menu-elements__spacer` element, optionally with `menu-elements__spacer--has-line`.
  - `title` items emit a `menu-elements__title` element.
- Preserve the existing admin decoration semantics:
  - Custom element items receive `kmdg-custom-menu-item`.
  - Custom element items receive `menu-item-type--{slug}`.
  - The visible admin label is replaced with the registered type label.

## Known Contract Gaps

- The plugin currently loads ACF local JSON from the plugin `acf/` directory, but only `acf/spacer.json` is committed in-repo.
- Column behavior still depends on ACF field names such as `column_size` and `enable_line`, even though those definitions are not fully represented in the checked-in JSON.
- Treat the incomplete ACF JSON as a compatibility gap to normalize around during refactors. Do not use it as justification for a saved-data migration in conservative maintenance work.

## Manual Smoke Checklist

Run this checklist after any change that touches plugin bootstrap, walker behavior, ACF integration, or styles.

### Setup

1. Activate the plugin in WordPress.
2. Use a menu that contains at least one each of `row`, `column`, `spacer`, and `title`.
3. Use a front-end location rendered through `wp_nav_menu()` without a custom walker override so the plugin default walker path is exercised.

### Admin Checks

1. Open `Appearance > Menus`.
2. Confirm the "Custom Elements" metabox appears in the sidebar.
3. Confirm the metabox lists `Row`, `Column`, `Spacer`, and `Title`.
4. Add one of each custom element to a menu and save.
5. Confirm each custom element appears in the menu editor with the green custom-item styling from the admin CSS.
6. Confirm each custom element has the expected visible label:
   - `Row`
   - `Column`
   - `Spacer`
   - `Title`
7. Confirm the `Title` item still shows its description block in the admin UI.
8. Confirm the "Select All" link and "Add to Menu" button still work.

### ACF Checks

1. Open the settings for a saved `Spacer` item and confirm the `Size` and `Enable Line` fields still appear.
2. Save the `Spacer` item with line disabled and with line enabled.
3. If the site exposes `Column` settings through ACF, confirm those fields still load and save.
4. Re-open the menu editor and confirm the saved settings persist after refresh.

### Front-End Checks

1. Render the menu on the front end without passing a custom walker and confirm the output still uses `KMDG\MenuElements\Walker`.
2. Confirm a `row` item still behaves as a structural marker and does not add unexpected inner markup.
3. Confirm a `column` item still renders a `menu-elements__column-wrap` wrapper.
4. Confirm enabling the column line option still adds `menu-elements__column-wrap--line`.
5. Confirm column sizing still adds a `menu-elements__column--{size}` class to the menu item.
6. Confirm a `spacer` item still renders the `menu-elements__spacer` element and that enabling the line option still adds `menu-elements__spacer--has-line`.
7. Confirm a `title` item still renders a `menu-elements__title` element with the saved menu item title text.
8. Confirm existing menu layouts still match the current CSS semantics in `src/sass/menu-elements.scss`.

## Change Discipline

- Prefer small, compatibility-preserving slices.
- When refactoring, move logic behind collaborators first and keep the public hook/walker surface stable.
- Do not modify vendored code under `aesir/` unless the change is intentional and separately reviewed.
