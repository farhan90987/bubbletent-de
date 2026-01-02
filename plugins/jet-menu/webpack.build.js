const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,

    entry: {
        // JS:

        'assets/public/js/jet-menu-public-scripts': path.resolve(
            __dirname,
            '_dev-public/src/js/jet-menu-public-scripts.js'
        ),

        'assets/public/js/legacy/jet-menu-public-scripts': path.resolve(
            __dirname,
            '_dev-public/src/js/legacy/jet-menu-public-scripts.js'
        ),

        'includes/elementor/assets/public/js/widgets-scripts': path.resolve(
            __dirname,
            '_dev-public/src/elementor/widgets-scripts.js'
        ),

        'includes/elementor/assets/public/js/legacy/widgets-scripts': path.resolve(
            __dirname,
            '_dev-public/src/elementor/legacy/widgets-scripts.js'
        ),

        // CSS / SCSS:

        // admin.css
        'assets/admin/css/admin': path.resolve(
            __dirname,
            'assets/admin/scss/admin.scss'
        ),

        // gutenberg.css
        'assets/admin/css/gutenberg': path.resolve(
            __dirname,
            'assets/admin/scss/gutenberg.scss'
        ),

        // public.css
        'assets/public/css/public': path.resolve(
            __dirname,
            'assets/public/scss/public.scss'
        ),

        // editor.css ( Elementor editor )
        'includes/elementor/assets/editor/css/editor': path.resolve(
            __dirname,
            'includes/elementor/assets/editor/scss/editor.scss'
        ),

    },

    output: {
        ...defaultConfig.output,

        path: path.resolve( __dirname, '.' ),
        filename: '[name].js',
        clean: false,
    },
};
