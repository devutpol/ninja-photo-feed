/**
 * Compiles resources/ into assets/. Run with: npm run build (production) or npm run dev.
 */

const mix = require('laravel-mix');
const webpack = require('webpack');
const path = require('path');
const fs = require('fs-extra');

mix.babelConfig({
    presets: [
        ['@babel/preset-env', {
            targets: { esmodules: true },
            useBuiltIns: false,
            modules: false
        }]
    ]
});

mix.webpackConfig({
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            '@': path.resolve('resources/assets')
        }
    },
    module: {
        rules: [
            {
                test: /\.mjs$/,
                resolve: { fullySpecified: false },
                include: /node_modules/,
                type: 'javascript/auto'
            }
        ]
    },
    stats: {
        children: true
    },
    performance: {
        hints: false
    },
    // The block editor provides these; bundling them would load a second copy of WordPress packages.
    externals: {
        '@wordpress/blocks':       'wp.blocks',
        '@wordpress/components':   'wp.components',
        '@wordpress/element':      'wp.element',
        '@wordpress/i18n':         'wp.i18n',
        '@wordpress/block-editor': 'wp.blockEditor',
        '@wordpress/editor':       'wp.editor',
    },
    plugins: [
        // The admin never calls moment.locale(), so its bundled locales are dead weight.
        new webpack.IgnorePlugin({ resourceRegExp: /^\.\/locale$/, contextRegExp: /moment$/ })
    ],
});

if (!mix.inProduction()) {
    mix.webpackConfig({
        devtool: 'source-map'
    }).sourceMaps(true, 'source-map');
} else {
    fs.removeSync('assets');
}

mix
    .setPublicPath('assets')
    .setResourceRoot('../')

    .js('resources/admin/Boot.js', 'assets/js/ninja-photo-feed-boot.js').vue({ version: 3 })
    .js('resources/admin/instagram-main.js', 'assets/js/ninja-photo-feed-admin.js').vue({ version: 3 })

    .js('resources/public/wp-social-review.js', 'assets/js/ninja-photo-feed.js')
    .js('resources/public/image_resizer.js', 'assets/js/image_resizer.js')

    .js('resources/admin/gutenblocks/shortcodeBlock.js', 'assets/js/npfeed-shortcode-block.js')
    .react()

    .sass('resources/scss/admin/app.scss', 'assets/css/ninja-photo-feed-admin.css')
    .sass('resources/scss/public/ig.scss', 'assets/css/ninja-photo-feed.css')
    .sass('resources/scss/admin/gutenBlock.scss', 'assets/css/ninja-photo-feed-gutenblock.css')

    .copy('resources/images', 'assets/images')
    .copy('resources/fonts', 'assets/fonts');

mix.options({
    legacyNodePolyfills: false
});
