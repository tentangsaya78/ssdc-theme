/**
 * Block editor modifications
 *
 * This file is loaded only by the block editor. Use it to modify the block
 * editor via its APIs.
 *
 * The JavaScript code you place here will be processed by esbuild, and the
 * output file will be created at `../theme/js/block-editor.min.js` and
 * enqueued in `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */

/**
 * This import adds your front-end post title and Tailwind Typography classes
 * to the block editor. It also adds some helper classes so you can access the
 * post type when modifying the block editor’s appearance.
 */
import '@_tw/typography/block-editor-classes';

wp.domReady(() => {
    /**
     * Add support for Tailwind Typography’s `lead` class via a block style.
     */
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'lead',
        label: 'Lead',
    });

    /* ==========================================================================
       PILIHAN GAYA LIST KUSTOM (GUTENBERG BLOCK STYLES)
       ========================================================================== */

    // 1. Gaya Unordered List (Simbol)
    wp.blocks.registerBlockStyle('core/list', {
        name: 'list-square',
        label: 'Kotak (Square)',
    });

    wp.blocks.registerBlockStyle('core/list', {
        name: 'list-circle',
        label: 'Lingkaran (Circle)',
    });

    // 2. Gaya Ordered List (Angka & Huruf)
    wp.blocks.registerBlockStyle('core/list', {
        name: 'list-roman-upper',
        label: 'Romawi Besar (I, II, III)',
    });

    wp.blocks.registerBlockStyle('core/list', {
        name: 'list-alpha-upper',
        label: 'Abjad Besar (A, B, C)',
    });

    wp.blocks.registerBlockStyle('core/list', {
        name: 'list-decimal-zero',
        label: 'Angka Nol Depan (01, 02)',
    });
});
