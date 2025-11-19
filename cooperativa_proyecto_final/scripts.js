// Helper genérico para consumir APIs
async function apiRequest(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        ...options
    });
    let data = {};
    try { data = await res.json(); } catch (e) {}
    if (!res.ok) {
        throw data;
    }
    return data;
}

// Pequeña animación de aparición en landing
document.addEventListener('DOMContentLoaded', () => {
    const animated = document.querySelectorAll('.info-card, .step-card, .stat-card');
    animated.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        const obs = new IntersectionObserver(entries => {
            entries.forEach(en => {
                if (en.isIntersecting) {
                    en.target.style.opacity = '1';
                    en.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        obs.observe(el);
    });
});
