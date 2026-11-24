const themeTransitionDuration = 600;
let themeTransitionTimeout;

function applyTheme(isDark) {
    document.documentElement.classList.toggle('dark', isDark);
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

document.addEventListener('click', (event) => {
    const target = event.target;
    const themeToggleButton = target instanceof Element ? target.closest('#theme-toggle-btn') : null;

    if (!themeToggleButton) {
        return;
    }

    const root = document.documentElement;
    const isDarkNext = !root.classList.contains('dark');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const transitionRect = themeToggleButton.getBoundingClientRect();
    const transitionX = event.detail > 0 ? event.clientX : transitionRect.left + transitionRect.width / 2;
    const transitionY = event.detail > 0 ? event.clientY : transitionRect.top + transitionRect.height / 2;
    const transitionRadius = Math.hypot(
        Math.max(transitionX, window.innerWidth - transitionX),
        Math.max(transitionY, window.innerHeight - transitionY)
    );

    window.clearTimeout(themeTransitionTimeout);

    if (!prefersReducedMotion && typeof document.startViewTransition === 'function') {
        root.style.setProperty('--theme-transition-x', `${transitionX}px`);
        root.style.setProperty('--theme-transition-y', `${transitionY}px`);
        root.style.setProperty('--theme-transition-radius', `${transitionRadius}px`);
        root.classList.remove('theme-transitioning');

        document.startViewTransition(() => applyTheme(isDarkNext));
        return;
    }

    if (!prefersReducedMotion) {
        root.classList.add('theme-transitioning');
    }

    applyTheme(isDarkNext);

    if (prefersReducedMotion) {
        return;
    }

    themeTransitionTimeout = window.setTimeout(
        () => root.classList.remove('theme-transitioning'),
        themeTransitionDuration
    );
});
