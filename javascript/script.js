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
import { animateHero } from './animate/hero';
import { animation } from './animate/animation';



window.Alpine = Alpine



Alpine.start()

document.addEventListener('DOMContentLoaded', () => {

    animateHero();
    animation();

    const elms = document.getElementById('splide-ecosystem');

    if (elms) {
        const splide = new Splide(elms, {
            type: 'loop',
            perPage: 2,
            fixedWidth: '900px',
            autoplay: true,
            interval: 3000,
            pagination: false,
            arrows: false,
            speed: 1500,
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

    const elms2 = document.getElementById('supported');
    if (elms2) {
        const splide2 = new Splide(elms2, {
            type: 'loop',
            perPage: 5,
            autoplay: true,
            interval: 3000,
            pagination: false,
            arrows: true,
            speed: 1500,
            focus: 'center',
            // Tambahkan breakpoints jika diperlukan untuk responsivitas
            breakpoints: {
                1024: {
                    fixedWidth: '100%',
                    perPage: 1,
                }
            }
        }).mount();
    }

    // tambahan splide
    const videoSlider = new Splide('#video-slider', {
        type: 'loop',
        perPage: 2,
        perMove: 1,
        autoplay: true,
        interval: 3000,
        focus: 'center',
        gap: '1.5rem',
        pauseOnHover: false,
        pagination: true,
        arrows: true,
        autoplay: false,
        breakpoints: {
            768: { perPage: 1 },
            1024: { perPage: 2 },
        },
    });

    videoSlider.on('mounted', function () {
    document.querySelectorAll('#video-slider [x-data]').forEach(function (el) {
        Alpine.initTree(el);
    });

    document.querySelectorAll('#video-slider button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            console.log('button clicked');
            console.log('Alpine data:', btn.closest('[x-data]')?._x_dataStack);
        });
    });
});

    videoSlider.mount();
    // end tambahan splide

});


