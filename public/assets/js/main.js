var App = (function () {
    function motionAllowed() {
        var param = new URLSearchParams(location.search).get('motion');

        if (param === 'on' || param === 'off') {
            return param === 'on';
        }

        try {
            var saved = localStorage.getItem('motion-preference');

            if (saved === 'on' || saved === 'off') {
                return saved === 'on';
            }
        } catch (err) {
        }

        if (isLocal()) {
            return true;
        }

        return !matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function isLocal() {
        return /^(localhost|127\.0\.0\.1|\[::1\])$/.test(location.hostname);
    }

    function checkGpu() {
        var renderer = '';
        var available = false;
        var webgl2 = false;

        try {
            var canvas = document.createElement('canvas');
            var gl2 = canvas.getContext('webgl2');
            var gl = gl2 || canvas.getContext('webgl');

            if (gl) {
                available = true;
                webgl2 = !!gl2;

                var info = gl.getExtension('WEBGL_debug_renderer_info');
                renderer = String(info ? gl.getParameter(info.UNMASKED_RENDERER_WEBGL) : gl.getParameter(gl.RENDERER) || '');

                var lose = gl.getExtension('WEBGL_lose_context');

                if (lose) {
                    lose.loseContext();
                }
            }
        } catch (err) {
            available = false;
        }

        var params = new URLSearchParams(location.search);
        var software = /swiftshader|llvmpipe|softwarerasterizer|basic render|microsoft basic/i.test(renderer);
        var heavy = available && !params.has('lite') && (params.has('heavy') || isLocal() || !software);

        if (software) {
            console.info('[gpu] Software rendering, no GPU behind WebGL (' + renderer + '). '
                + (heavy ? 'Running everything anyway, expect a low frame rate. Add ?lite to see the light version.'
                         : 'Background and 3D phone are off. Add ?heavy to force them on.'));
        }

        return { available: available, webgl2: webgl2, renderer: renderer, software: software, heavy: heavy };
    }

    var motionOn = motionAllowed();
    var gpu = checkGpu();

    document.documentElement.dataset.motion = motionOn ? 'on' : 'off';

    var lenis = null;

    if (motionOn && typeof Lenis !== 'undefined') {
        lenis = new Lenis({
            lerp: 0.085,
            wheelMultiplier: 1,
            touchMultiplier: 1.6,
            syncTouch: false
        });

        requestAnimationFrame(function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        });
    }

    function scrollTo(target, immediate) {
        if (lenis) {
            lenis.scrollTo(target, {
                duration: immediate ? 0 : 1.15,
                immediate: immediate,
                easing: function (t) {
                    return 1 - Math.pow(1 - t, 3);
                }
            });

            return;
        }

        var top = typeof target === 'number' ? target : target.getBoundingClientRect().top + window.scrollY;

        window.scrollTo({ top: top, behavior: immediate ? 'auto' : 'smooth' });
    }

    return {
        motionOn: motionOn,
        gpu: gpu,
        isLocal: isLocal,
        scrollTo: scrollTo
    };
})();

(function () {
    var items = document.querySelectorAll('.reveal, .reveal-up');

    if (!items.length) {
        return;
    }

    if (!App.motionOn || !('IntersectionObserver' in window)) {
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

(function () {
    var hero = document.querySelector('.hero');

    if (!hero) {
        return;
    }

    function sync() {
        var phone = hero.querySelector('.hero__phone');

        if (!phone) {
            return;
        }

        var heroBox = hero.getBoundingClientRect();
        var phoneBox = phone.getBoundingClientRect();

        if (!heroBox.height) {
            return;
        }

        var axis = ((phoneBox.top + phoneBox.height / 2 - heroBox.top) / heroBox.height) * 100;

        hero.style.setProperty('--corridor-axis', axis.toFixed(2) + '%');
    }

    sync();

    if ('ResizeObserver' in window) {
        new ResizeObserver(sync).observe(hero);
    } else {
        window.addEventListener('resize', sync);
    }
})();

(function () {
    var form = document.getElementById('contact-form');

    if (!form) {
        return;
    }

    var status = document.getElementById('form-status');
    var button = form.querySelector('button[type="submit"]');
    var area = form.querySelector('.textarea');

    var messages = {
        'Empty fields': 'Please fill in every field.',
        'Name too long': 'That name is longer than the form allows.',
        'Contact too long': 'That contact is longer than the form allows.',
        'Message too long': 'That message is longer than the form allows.',
        'Too fast': 'One message at a time, please — try again in a moment.',
        'Send failed': 'Could not send the message. Please try again.',
        'Bad method': 'Something went wrong. Please try again.'
    };

    var rules = {
        name: function (value) {
            return value.trim().length >= 2 ? '' : 'Please tell me your name.';
        },
        contact: function (value) {
            return value.trim().length >= 3 ? '' : 'Leave an email or a Telegram handle.';
        },
        message: function (value) {
            return value.trim().length >= 10 ? '' : 'A little more detail, please.';
        }
    };

    function fitArea() {
        if (!area) {
            return;
        }

        area.style.height = 'auto';
        area.style.height = area.scrollHeight + 'px';
    }

    function showError(field, text) {
        var box = form.querySelector('[data-error-for="' + field + '"]');
        var input = form.elements[field];

        if (box) {
            box.textContent = text;
            box.hidden = text === '';
        }

        if (input) {
            if (text === '') {
                input.removeAttribute('aria-invalid');
            } else {
                input.setAttribute('aria-invalid', 'true');
            }
        }
    }

    function setStatus(text, kind) {
        status.textContent = text;
        status.className = 'form-status' + (text ? ' form-status--' + kind : '');
    }

    if (area) {
        area.addEventListener('input', fitArea);
    }

    Object.keys(rules).forEach(function (field) {
        var input = form.elements[field];

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            if (input.getAttribute('aria-invalid') === 'true') {
                showError(field, rules[field](input.value));
            }
        });
    });

    form.addEventListener('submit', function (event) {
        var firstBad = '';

        Object.keys(rules).forEach(function (field) {
            var input = form.elements[field];
            var error = input ? rules[field](input.value) : '';

            showError(field, error);

            if (error && !firstBad) {
                firstBad = field;
            }
        });

        if (firstBad) {
            event.preventDefault();
            setStatus('', 'error');
            form.elements[firstBad].focus();
            return;
        }

        if (!window.fetch) {
            return;
        }

        event.preventDefault();

        button.disabled = true;
        button.textContent = 'Sending…';
        setStatus('', 'ok');

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data && data.ok) {
                    form.reset();
                    fitArea();
                    setStatus('Thanks — your message is on its way.', 'ok');
                    return;
                }

                var error = data && data.error ? data.error : '';

                setStatus(messages[error] || 'Something went wrong. Please try again.', 'error');
            })
            .catch(function () {
                setStatus('Could not reach the server. Please try again.', 'error');
            })
            .then(function () {
                button.disabled = false;
                button.textContent = 'Send message';
            });
    });
})();
