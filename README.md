# Ninja Photo Feed

Source for the [Ninja Photo Feed](https://wordpress.org/plugins/ninja-photo-feed/) WordPress plugin, which shows an
Instagram feed on a WordPress site.

The compiled admin app, front-end scripts and stylesheets in `assets/` are built from `resources/`:

```bash
npm install
npm run build   # production build into assets/
npm run dev     # unminified, with source maps
```

Requires Node.js 18 or later. `vendor/` is committed and is not managed by Composer, so don't run `composer install`.

## License

GPLv2 or later.
