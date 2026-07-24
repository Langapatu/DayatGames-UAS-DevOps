import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import Swiper from 'swiper';
import { A11y, Keyboard, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const header = document.querySelector('[data-site-header]');
const menuButton = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('[data-site-navigation]');

menuButton?.addEventListener('click', () => {
    const isOpen = navigation?.dataset.open === 'true';
    if (navigation) {
        navigation.dataset.open = String(!isOpen);
    }
    menuButton.setAttribute('aria-expanded', String(!isOpen));
});

navigation?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
        navigation.dataset.open = 'false';
        menuButton?.setAttribute('aria-expanded', 'false');
    });
});

const updateHeader = () => {
    header?.classList.toggle('is-scrolled', window.scrollY > 18);
};
updateHeader();
window.addEventListener('scroll', updateHeader, { passive: true });

document.querySelectorAll('[data-flash]').forEach((flash) => {
    flash.querySelector('[data-flash-close]')?.addEventListener('click', () => flash.remove());
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) {
            event.preventDefault();
        }
    });
});

if (document.querySelector('.featured-swiper')) {
    new Swiper('.featured-swiper', {
        modules: [A11y, Keyboard, Navigation],
        slidesPerView: 1.15,
        spaceBetween: 16,
        keyboard: { enabled: true, onlyInViewport: true },
        navigation: {
            nextEl: '.featured-next',
            prevEl: '.featured-prev',
        },
        a11y: {
            enabled: true,
            prevSlideMessage: 'Game sebelumnya',
            nextSlideMessage: 'Game berikutnya',
        },
        breakpoints: {
            640: { slidesPerView: 2.2 },
            1024: { slidesPerView: 4 },
        },
    });
}

if (!reducedMotion) {
    gsap.registerPlugin(ScrollTrigger);

    const lenis = new Lenis({
        duration: 1.05,
        smoothWheel: true,
        wheelMultiplier: 0.9,
    });

    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    const hero = document.querySelector('[data-hero]');
    if (hero) {
        gsap.from(hero.querySelectorAll('[data-hero-item]'), {
            y: 24,
            opacity: 0,
            duration: 0.7,
            stagger: 0.09,
            ease: 'power2.out',
        });

        const heroImage = hero.querySelector('[data-hero-image]');
        if (heroImage) {
            gsap.to(heroImage, {
                yPercent: 8,
                ease: 'none',
                scrollTrigger: {
                    trigger: hero,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });
        }
    }

    document.querySelectorAll('[data-reveal]').forEach((section) => {
        gsap.from(section, {
            y: 28,
            opacity: 0,
            duration: 0.65,
            ease: 'power2.out',
            scrollTrigger: { trigger: section, start: 'top 88%', once: true },
        });
    });

    ScrollTrigger.batch('[data-game-card]', {
        start: 'top 92%',
        once: true,
        onEnter: (cards) => gsap.from(cards, {
            y: 22,
            opacity: 0,
            duration: 0.5,
            stagger: 0.06,
            ease: 'power2.out',
        }),
    });
}
