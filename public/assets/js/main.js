(function () {
    var items = document.querySelectorAll('.reveal, .reveal-up');

    if (!items.length) {
        return;
    }

    if (document.documentElement.dataset.motion === 'off' || !('IntersectionObserver' in window)) {
        items.forEach(function (el) {
            el.classList.add('is-visible');
        });

        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    items.forEach(function (el) {
        observer.observe(el);
    });
})();
