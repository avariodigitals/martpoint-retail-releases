# MartPoint Mobile UI Rules

## No "shoot-out" dropdowns
All `<select>` dropdowns in mobile views must be replaced with an in-app custom select component (e.g. `mp-select`).

- Native OS/browser `<select>` option lists must not pop over or outside the mobile app container.
- Custom selects must expand inline inside the form, capped with `max-height` and `overflow-y: auto`.
- The hidden `<select>` still holds the form `name` and value so server-side logic remains unchanged.

## Footer menu and chat on every mobile page
Every mobile view must include the footer menu and the MartPoint Assist chat.

- Add `<?php $this->load->view('mobile/bottom_nav', ['active' => 'home|pos|sale|more']); ?>` to the page footer.
- Add `<?php $this->load->view('mobile/chat'); ?>` before `</body>` on every mobile screen.
- The `mobile/chat.php` partial loads `assist.js` and `assist/panel` so the chat launcher appears on all pages.

## Consistent header on every mobile page
Every mobile view must display the store name before the screen name in the top header.

- Use `<?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?>` as the store name.
- Render the store name above or before the screen title in the topbar (e.g. `<div class="store-name">` above `<h1>`).
- The screen `<title>` must follow `Store Name — Screen Name` order.

## Screen-mode layout rule

The MartPoint desktop shell is **only** for desktop and tablet landscape. Mobile and tablet portrait must use the dedicated mobile views.

| Screen / mode | Layout |
|---|---|
| Desktop (1920–1280) | `mp_layout` / new desktop shell |
| Tablet landscape (≥1024) | `mp_layout` / new desktop shell |
| Tablet portrait (≤1024) | Dedicated `mobile/` views |
| Phone (≤768) | Dedicated `mobile/` views |

- Use `mp_layout` for every desktop route and every tablet-landscape route.
- Do **not** load `mp_layout` for `mobile/` views.
- Do **not** shrink or adapt `mp_layout` to serve phones; the `mobile/` shell already satisfies the mobile rules above.

## Screen-wide copyright footer on every mobile page
Every mobile view must include a screen-wide copyright footer that appears after the menu.

- The `mobile/bottom_nav` partial renders the menu and a full-width copyright strip.
- The copyright must span the full width of the screen, use a muted color, and sit directly after the menu.
- No mobile screen may go live without the header and footer fully implemented.

## PHP version requirement
MartPoint runs on CodeIgniter 3 and requires **PHP 7.4** on the server.

- Do **not** deploy to PHP 8.x. The legacy CI3 core and several dashboard model queries (e.g. `Dashboard_model::get_branch_performance`, the `mp_header` subscription query) fatal under PHP 8's stricter undefined-key / `array_column` handling, producing a 500 on `/dashboard` (HTML truncates mid-render at the "Branch Performance" card).
- Symptom: `/dashboard` returns HTTP 500 while `/dashboard/dashboard_values`, `/pos`, `/items`, `/customers`, `/sales` all return 200.
- Fix: set the server PHP handler to 7.4 (e.g. cPanel → MultiPHP Manager → `pharmademo` → PHP 7.4).
- Run migrations from `updates/migrations/` in version order after upgrading an existing install; verify columns exist with `SHOW COLUMNS FROM db_items LIKE 'is_new_arrival'` etc.
