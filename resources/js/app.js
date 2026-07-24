import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import Swiper from 'swiper';
import { A11y, Keyboard, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const header = document.querySelector('[data-site-header]');
const menuButton = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('[data-site-navigation]');
const adminMenuButton = document.querySelector('[data-admin-menu-toggle]');
const adminSidebar = document.querySelector('.admin-sidebar');
const pageContent = document.querySelector('[data-page-content]');

if (!reducedMotion && pageContent && !document.startViewTransition) {
    let pageTransitioning = false;

    gsap.from(pageContent, {
        y: 14,
        opacity: 0,
        duration: 0.36,
        ease: 'power2.out',
        clearProps: 'transform,opacity',
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const link = target?.closest('a[href]');
        if (
            pageTransitioning
            || !link
            || event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
            || link.target === '_blank'
            || link.hasAttribute('download')
        ) {
            return;
        }

        const nextUrl = new URL(link.href, window.location.href);
        const isSameDocumentHash = nextUrl.pathname === window.location.pathname
            && nextUrl.search === window.location.search
            && nextUrl.hash;

        if (nextUrl.origin !== window.location.origin || isSameDocumentHash) {
            return;
        }

        event.preventDefault();
        pageTransitioning = true;

        gsap.to(pageContent, {
            y: -8,
            opacity: 0,
            duration: 0.18,
            ease: 'power1.in',
            onComplete: () => window.location.assign(nextUrl.href),
        });
    });
}

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

adminMenuButton?.addEventListener('click', () => {
    const isOpen = adminSidebar?.classList.toggle('is-open');
    adminMenuButton.setAttribute('aria-expanded', String(Boolean(isOpen)));
});

adminSidebar?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
        adminSidebar.classList.remove('is-open');
        adminMenuButton?.setAttribute('aria-expanded', 'false');
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.parentElement?.querySelector('input');
        if (!input) return;
        const shouldShow = input.type === 'password';
        input.type = shouldShow ? 'text' : 'password';
        button.textContent = shouldShow ? 'Sembunyi' : 'Lihat';
        button.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
    });
});

document.querySelectorAll('[data-profile-upload]').forEach((input) => {
    input.addEventListener('change', () => {
        const label = input.closest('label')?.querySelector('[data-profile-upload-label]');
        if (!label) return;
        label.textContent = input.files?.[0]?.name || 'Pilih file';
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
        slidesPerView: 1,
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
            640: { slidesPerView: 2 },
            1024: { slidesPerView: 4 },
        },
    });
}

const catalogResults = document.querySelector('[data-catalog-results]');
const catalogIntentKey = 'dayatgames:catalog-navigation';

catalogResults?.querySelectorAll('.dg-pagination a[data-pagination-direction]').forEach((link) => {
    link.addEventListener('click', (event) => {
        if (
            event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        try {
            sessionStorage.setItem(catalogIntentKey, JSON.stringify({
                direction: link.dataset.paginationDirection,
                timestamp: Date.now(),
            }));
        } catch {
            // Storage is optional; normal pagination navigation still proceeds.
        }
    });
});

if (catalogResults) {
    try {
        const intent = JSON.parse(sessionStorage.getItem(catalogIntentKey) || 'null');
        const isFresh = intent
            && ['previous', 'next'].includes(intent.direction)
            && Date.now() - intent.timestamp < 10000;

        sessionStorage.removeItem(catalogIntentKey);

        if (isFresh) {
            history.scrollRestoration = 'manual';
            catalogResults.dataset.paginationMotion = intent.direction;

            requestAnimationFrame(() => {
                const headerOffset = header?.offsetHeight || 0;
                const targetTop = catalogResults.getBoundingClientRect().top + window.scrollY - headerOffset - 20;
                window.scrollTo({ top: Math.max(0, targetTop), behavior: 'auto' });
                catalogResults.focus({ preventScroll: true });
                history.scrollRestoration = 'auto';
            });
        }
    } catch {
        sessionStorage.removeItem(catalogIntentKey);
    }
}

if (!reducedMotion && finePointer) {
    const auroraOne = document.querySelector('[data-aurora-layer="one"]');
    const auroraTwo = document.querySelector('[data-aurora-layer="two"]');

    if (auroraOne && auroraTwo) {
        const moveAuroraOneX = gsap.quickTo(auroraOne, '--aurora-x', { duration: 1.2, ease: 'power3.out' });
        const moveAuroraOneY = gsap.quickTo(auroraOne, '--aurora-y', { duration: 1.35, ease: 'power3.out' });
        const moveAuroraTwoX = gsap.quickTo(auroraTwo, '--aurora-x', { duration: 1.45, ease: 'power3.out' });
        const moveAuroraTwoY = gsap.quickTo(auroraTwo, '--aurora-y', { duration: 1.6, ease: 'power3.out' });

        window.addEventListener('pointermove', (event) => {
            const normalizedX = (event.clientX / window.innerWidth - 0.5) * 2;
            const normalizedY = (event.clientY / window.innerHeight - 0.5) * 2;

            moveAuroraOneX(`${normalizedX * 3}%`);
            moveAuroraOneY(`${normalizedY * 2.4}%`);
            moveAuroraTwoX(`${normalizedX * -1.8}%`);
            moveAuroraTwoY(`${normalizedY * -1.4}%`);
        }, { passive: true });
    }

    document.querySelectorAll('[data-game-card]').forEach((card) => {
        const rotateXTo = gsap.quickTo(card, '--card-rx', { duration: 0.16, ease: 'power2.out' });
        const rotateYTo = gsap.quickTo(card, '--card-ry', { duration: 0.16, ease: 'power2.out' });

        card.addEventListener('pointermove', (event) => {
            const bounds = card.getBoundingClientRect();
            const normalizedX = (event.clientX - bounds.left) / bounds.width;
            const normalizedY = (event.clientY - bounds.top) / bounds.height;

            rotateXTo(`${(0.5 - normalizedY) * 8}deg`);
            rotateYTo(`${(normalizedX - 0.5) * 8}deg`);
            card.style.setProperty('--spot-x', `${normalizedX * 100}%`);
            card.style.setProperty('--spot-y', `${normalizedY * 100}%`);
        });

        card.addEventListener('pointerleave', () => {
            rotateXTo('0deg');
            rotateYTo('0deg');
            gsap.to(card, {
                '--spot-x': '50%',
                '--spot-y': '50%',
                duration: 0.45,
                ease: 'power3.out',
                overwrite: true,
            });
        });
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

}
