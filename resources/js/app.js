import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import Swiper from 'swiper';
import {
    A11y,
    Autoplay,
    EffectFade,
    Keyboard,
    Navigation,
    Pagination,
    Thumbs,
} from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const header = document.querySelector('[data-site-header]');
const menuButton = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('[data-site-navigation]');
const adminMenuButton = document.querySelector('[data-admin-menu-toggle]');
const adminSidebar = document.querySelector('.admin-sidebar');
const pageContent = document.querySelector('[data-page-content]');

const syncPaymentMethods = () => {
    document.querySelectorAll('[data-payment-method]').forEach((card) => {
        const input = card.querySelector('input[type="radio"]');
        card.classList.toggle('is-selected', input?.checked === true);
    });
};

document.querySelectorAll('[data-payment-method] input[type="radio"]').forEach((input) => {
    input.addEventListener('change', syncPaymentMethods);
});
syncPaymentMethods();

document.querySelectorAll('[data-copy-payment]').forEach((button) => {
    button.addEventListener('click', async () => {
        const value = button.dataset.copyPayment || '';
        let copied = false;

        try {
            await navigator.clipboard.writeText(value);
            copied = true;
        } catch {
            const text = document.createElement('textarea');
            text.value = value;
            text.setAttribute('readonly', '');
            text.style.position = 'fixed';
            text.style.opacity = '0';
            document.body.append(text);
            text.select();
            copied = document.execCommand('copy');
            text.remove();
        }

        if (!copied) {
            return;
        }

        const originalLabel = button.textContent;
        button.dataset.copied = 'true';
        button.textContent = 'Tersalin';
        window.setTimeout(() => {
            delete button.dataset.copied;
            button.textContent = originalLabel;
        }, 1600);
    });
});

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

const bindAutoplayPause = (container, swiper, onStateChange = () => {}) => {
    if (reducedMotion || !swiper.autoplay) {
        return { setForced: () => {} };
    }

    const pauseState = {
        hovered: false,
        focused: false,
        hidden: document.hidden,
        forced: false,
    };

    const sync = () => {
        const shouldPause = Object.values(pauseState).some(Boolean);

        if (shouldPause) {
            swiper.autoplay.stop();
        } else {
            swiper.autoplay.start();
        }

        onStateChange(shouldPause);
    };

    container.addEventListener('pointerenter', () => {
        pauseState.hovered = true;
        sync();
    });
    container.addEventListener('pointerleave', () => {
        pauseState.hovered = false;
        sync();
    });
    container.addEventListener('focusin', () => {
        pauseState.focused = true;
        sync();
    });
    container.addEventListener('focusout', () => {
        requestAnimationFrame(() => {
            pauseState.focused = container.contains(document.activeElement);
            sync();
        });
    });
    document.addEventListener('visibilitychange', () => {
        pauseState.hidden = document.hidden;
        sync();
    });

    return {
        setForced(value) {
            pauseState.forced = value;
            sync();
        },
    };
};

const homeHero = document.querySelector('[data-home-hero]');

if (homeHero) {
    const heroSlides = homeHero.querySelectorAll('[data-hero-slide]');
    const hasMultipleHeroes = heroSlides.length > 1;
    const animateActiveHero = (swiper) => {
        if (reducedMotion) return;

        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide) return;

        gsap.fromTo(
            activeSlide.querySelectorAll('[data-hero-item]'),
            { y: 18, opacity: 0 },
            {
                y: 0,
                opacity: 1,
                duration: 0.65,
                stagger: 0.08,
                ease: 'power3.out',
                overwrite: true,
            },
        );
        gsap.fromTo(
            activeSlide.querySelector('[data-hero-image]'),
            { scale: 1.035, opacity: 0.32 },
            {
                scale: 1,
                opacity: 0.58,
                duration: 1,
                ease: 'power2.out',
                overwrite: true,
            },
        );
    };

    const heroSwiper = new Swiper(homeHero.querySelector('.home-hero-swiper'), {
        modules: [A11y, Autoplay, EffectFade, Keyboard, Navigation, Pagination],
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: reducedMotion ? 0 : 720,
        loop: hasMultipleHeroes,
        allowTouchMove: hasMultipleHeroes,
        keyboard: { enabled: hasMultipleHeroes, onlyInViewport: true },
        autoplay: !reducedMotion && hasMultipleHeroes
            ? { delay: 6000, disableOnInteraction: false }
            : false,
        navigation: {
            nextEl: homeHero.querySelector('[data-hero-next]'),
            prevEl: homeHero.querySelector('[data-hero-prev]'),
        },
        pagination: {
            el: homeHero.querySelector('[data-hero-pagination]'),
            clickable: true,
            bulletElement: 'button',
        },
        a11y: {
            enabled: true,
            prevSlideMessage: 'Game hero sebelumnya',
            nextSlideMessage: 'Game hero berikutnya',
            paginationBulletMessage: 'Buka game hero {{index}}',
        },
        on: {
            init: animateActiveHero,
            slideChangeTransitionStart: animateActiveHero,
        },
    });

    if (hasMultipleHeroes) {
        bindAutoplayPause(homeHero, heroSwiper);
    }
}

document.querySelectorAll('[data-preview-gallery]').forEach((gallery) => {
    const previewCount = Number(gallery.dataset.previewCount || 1);
    const hasMultiplePreviews = previewCount > 1;
    const mainElement = gallery.querySelector('[data-preview-main]');
    const thumbsElement = gallery.querySelector('[data-preview-thumbs]');
    const currentLabel = gallery.querySelector('[data-preview-current]');
    const progress = gallery.querySelector('[data-preview-progress]');

    if (!mainElement) return;

    const thumbSwiper = thumbsElement
        ? new Swiper(thumbsElement, {
            modules: [A11y, Keyboard],
            slidesPerView: 2.35,
            spaceBetween: 10,
            watchSlidesProgress: true,
            slideToClickedSlide: true,
            keyboard: { enabled: true, onlyInViewport: true },
            breakpoints: {
                640: { slidesPerView: 3.25, spaceBetween: 12 },
                1024: { slidesPerView: 4, spaceBetween: 14 },
            },
        })
        : null;

    const restartProgress = (paused = false) => {
        if (!progress) return;

        progress.classList.remove('is-running', 'is-paused');
        void progress.offsetWidth;

        if (!reducedMotion) {
            progress.classList.add('is-running');
            progress.classList.toggle('is-paused', paused);
        }
    };

    const mainSwiper = new Swiper(mainElement, {
        modules: [A11y, Autoplay, Keyboard, Navigation, Thumbs],
        speed: reducedMotion ? 0 : 450,
        loop: hasMultiplePreviews,
        allowTouchMove: hasMultiplePreviews,
        keyboard: { enabled: hasMultiplePreviews, onlyInViewport: true },
        autoplay: !reducedMotion && hasMultiplePreviews
            ? { delay: 5000, disableOnInteraction: false }
            : false,
        navigation: {
            nextEl: gallery.querySelector('.preview-next'),
            prevEl: gallery.querySelector('.preview-prev'),
        },
        thumbs: thumbSwiper ? { swiper: thumbSwiper } : undefined,
        a11y: {
            enabled: true,
            prevSlideMessage: 'Screenshot sebelumnya',
            nextSlideMessage: 'Screenshot berikutnya',
        },
        on: {
            init(swiper) {
                if (currentLabel) currentLabel.textContent = String(swiper.realIndex + 1);
                restartProgress();
            },
            slideChange(swiper) {
                if (currentLabel) currentLabel.textContent = String(swiper.realIndex + 1);
                restartProgress();
            },
        },
    });

    const autoplayControl = hasMultiplePreviews
        ? bindAutoplayPause(gallery, mainSwiper, restartProgress)
        : { setForced: () => {} };
    const lightbox = gallery.querySelector('[data-preview-lightbox]');
    const lightboxImage = lightbox?.querySelector('[data-lightbox-image]');
    const lightboxCurrent = lightbox?.querySelector('[data-lightbox-current]');
    const lightboxThumbnails = [...(lightbox?.querySelectorAll('[data-lightbox-thumbnail]') || [])];
    const sourceImages = [...gallery.querySelectorAll('[data-preview-slide] img')].slice(0, previewCount);
    let lightboxIndex = 0;
    let lightboxTrigger = null;

    const updateLightbox = (index) => {
        if (!lightboxImage || sourceImages.length === 0) return;

        lightboxIndex = (index + sourceImages.length) % sourceImages.length;
        const source = sourceImages[lightboxIndex];
        lightboxImage.src = source.currentSrc || source.src;
        lightboxImage.alt = source.alt;
        if (lightboxCurrent) lightboxCurrent.textContent = String(lightboxIndex + 1);

        lightboxThumbnails.forEach((thumbnail, thumbnailIndex) => {
            if (thumbnailIndex === lightboxIndex) {
                thumbnail.setAttribute('aria-current', 'true');
            } else {
                thumbnail.removeAttribute('aria-current');
            }
        });

        if (hasMultiplePreviews && mainSwiper.realIndex !== lightboxIndex) {
            mainSwiper.slideToLoop(lightboxIndex, reducedMotion ? 0 : 450);
        }
    };

    const openLightbox = (trigger, index) => {
        if (!lightbox?.showModal) return;

        lightboxTrigger = trigger;
        updateLightbox(index);
        autoplayControl.setForced(true);
        document.documentElement.classList.add('preview-lightbox-open');
        lightbox.showModal();
        lightbox.querySelector('[data-lightbox-close]')?.focus({ preventScroll: true });
    };

    gallery.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const trigger = target?.closest('[data-preview-open]');
        if (!trigger) return;

        openLightbox(trigger, Number(trigger.dataset.previewIndex || mainSwiper.realIndex));
    });

    lightbox?.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => updateLightbox(lightboxIndex - 1));
    lightbox?.querySelector('[data-lightbox-next]')?.addEventListener('click', () => updateLightbox(lightboxIndex + 1));
    lightbox?.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox.close());
    lightboxThumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => updateLightbox(Number(thumbnail.dataset.previewIndex)));
    });
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) lightbox.close();
    });
    lightbox?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            lightbox.close();
            return;
        }
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            updateLightbox(lightboxIndex - 1);
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            updateLightbox(lightboxIndex + 1);
        }
    });
    lightbox?.addEventListener('close', () => {
        document.documentElement.classList.remove('preview-lightbox-open');
        autoplayControl.setForced(false);
        lightboxTrigger?.focus({ preventScroll: true });
    });
});

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
        const auroraMotion = { oneX: 0, oneY: 0, twoX: 0, twoY: 0 };
        const renderAurora = () => {
            auroraOne.style.setProperty('--aurora-x', `${auroraMotion.oneX}%`);
            auroraOne.style.setProperty('--aurora-y', `${auroraMotion.oneY}%`);
            auroraTwo.style.setProperty('--aurora-x', `${auroraMotion.twoX}%`);
            auroraTwo.style.setProperty('--aurora-y', `${auroraMotion.twoY}%`);
        };
        const moveAuroraOneX = gsap.quickTo(auroraMotion, 'oneX', { duration: 1.2, ease: 'power3.out', onUpdate: renderAurora });
        const moveAuroraOneY = gsap.quickTo(auroraMotion, 'oneY', { duration: 1.35, ease: 'power3.out', onUpdate: renderAurora });
        const moveAuroraTwoX = gsap.quickTo(auroraMotion, 'twoX', { duration: 1.45, ease: 'power3.out', onUpdate: renderAurora });
        const moveAuroraTwoY = gsap.quickTo(auroraMotion, 'twoY', { duration: 1.6, ease: 'power3.out', onUpdate: renderAurora });

        window.addEventListener('pointermove', (event) => {
            const normalizedX = (event.clientX / window.innerWidth - 0.5) * 2;
            const normalizedY = (event.clientY / window.innerHeight - 0.5) * 2;

            moveAuroraOneX(normalizedX * 3);
            moveAuroraOneY(normalizedY * 2.4);
            moveAuroraTwoX(normalizedX * -1.8);
            moveAuroraTwoY(normalizedY * -1.4);
        }, { passive: true });
    }

    document.querySelectorAll('[data-game-card]').forEach((card) => {
        const cardMotion = { rotateX: 0, rotateY: 0 };
        const renderCardTilt = () => {
            card.style.setProperty('--card-rx', `${cardMotion.rotateX}deg`);
            card.style.setProperty('--card-ry', `${cardMotion.rotateY}deg`);
        };
        const rotateXTo = gsap.quickTo(cardMotion, 'rotateX', { duration: 0.16, ease: 'power2.out', onUpdate: renderCardTilt });
        const rotateYTo = gsap.quickTo(cardMotion, 'rotateY', { duration: 0.16, ease: 'power2.out', onUpdate: renderCardTilt });

        card.addEventListener('pointermove', (event) => {
            const bounds = card.getBoundingClientRect();
            const normalizedX = (event.clientX - bounds.left) / bounds.width;
            const normalizedY = (event.clientY - bounds.top) / bounds.height;

            card.dataset.interactiveActive = 'true';
            rotateXTo((0.5 - normalizedY) * 8);
            rotateYTo((normalizedX - 0.5) * 8);
            card.style.setProperty('--spot-x', `${normalizedX * 100}%`);
            card.style.setProperty('--spot-y', `${normalizedY * 100}%`);
        });

        card.addEventListener('pointerleave', () => {
            delete card.dataset.interactiveActive;
            rotateXTo(0);
            rotateYTo(0);
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

    const hero = document.querySelector('[data-hero]:not([data-home-hero])');
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
