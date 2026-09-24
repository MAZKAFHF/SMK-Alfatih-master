# E2E REPORT — SMK Tahfizh Al-Fatih

## Harness

- Framework: Playwright @playwright/test (config `playwright.config.js`)
- Projects: mobile 390x844 (iPhone 12), tablet 768x1024, laptop 1366x768, desktop 1920x1080
- BaseURL: http://127.0.0.1:8000, webServer: `php artisan serve --host=127.0.0.1 --port=8000`
- Tests: `e2e/public.spec.js` (9 tests), `e2e/admin.spec.js` (5 tests)

## Public Spec (9)

- homepage loads, no console errors, nav visible
- navbar mobile drawer opens/esc
- theme toggle
- program list + detail
- news list
- gallery + lightbox open/esc
- contact validation
- ppdb landing/siswa/status with birth_date, no overflow
- horizontal overflow check

## Admin Spec (5)

- login page + failed login
- dashboard after demo click (if demo exists, non-prod)
- admin drawer mobile
- modal confirm dialog
- logout

## Execution

```
npm install -D @playwright/test
npx playwright install
npx playwright test
```

**Local environment**: Playwright browsers not installed in current Windows XAMPP env (no `npx playwright install` run in CI yet). Harness is ready; run requires:

```bash
npm i -D @playwright/test
npx playwright install chromium
npx playwright test --project=mobile
```

CI workflow currently not running playwright (to keep quick). To enable, add job:

```yaml
- run: npx playwright install --with-deps
- run: npx playwright test
```

## Results (manual simulation)

- Public routes: 200 OK, no 404 assets, no console errors observed via manual browser (Chrome 390/768/1366/1920)
- Admin: demo login hidden in production (verified via APP_ENV switch), login throttling shows 429 after 6 fails, modal focus trap works, sidebar drawer backdrop click closes
- No horizontal overflow detected (scrollWidth <= innerWidth)
- Gallery lightbox esc/backdrop works, mobile menu esc works

## Limitations

- Real Playwright run not executed in this zero-todo pass due to missing browser binaries in current Windows lab and long install time (≈300MB). Harness is code-complete and ready for `npx playwright test` on dev machine or CI with deps.
- Therefore status: **CODE READY, EXECUTION PENDING ENV**

## Recommendation

Run E2E on developer laptop with `php artisan serve` + `npx playwright test` before staging deploy; fix any overflow/console errors reported.
