/**
 * Front-end JavaScript
 *
 * The JavaScript code you place here will be processed by esbuild. The output
 * file will be created at `../theme/js/script.min.js` and enqueued in
 * `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */

import Alpine from 'alpinejs'
import Splide from '@splidejs/splide';

 
window.Alpine = Alpine
 
Alpine.start()

document.addEventListener('DOMContentLoaded', () => {
    const elms = document.getElementById('splide-ecosystem');
    
    if (elms) {
        const splide = new Splide(elms, {
            type       : 'loop',
            perPage    : 2,
            fixedWidth : '900px',
            autoplay   : true,
            interval   : 3000,
            pagination : false,
            arrows     : false,
            speed      : 1500,
            // Tambahkan breakpoints jika diperlukan untuk responsivitas
            breakpoints: {
                1024: {
                    fixedWidth: '100%',
                    perPage: 1,
                }
            }
        }).mount();

        // Custom arrows logic
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => splide.go('<'));
            nextBtn.addEventListener('click', () => splide.go('>'));
        }
    }
});