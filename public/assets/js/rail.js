(function () {
    var rail = document.querySelector('.rail');

    if (!rail) {
        return;
    }

    var track = rail.querySelector('.rail__track');
    var grip = rail.querySelector('.rail__grip');
    var fill = rail.querySelector('.rail__fill');
    var bubble = document.getElementById('rail-bubble');
    var stops = Array.prototype.slice.call(rail.querySelectorAll('.rail__stop'));

    var CATCHUP = 0.16;
    var RADIUS = 8;
    var MAX_STRETCH = 62;

    var ARRIVE_AT = 0.35;

    var marks = [];
    var progress = 0;

    function maxScroll() {
        return document.documentElement.scrollHeight - window.innerHeight;
    }

    function measure() {
        var max = maxScroll();

        if (max <= 0) {
            return;
        }

        marks = stops.map(function (stop) {
            var section = document.getElementById(stop.dataset.target);
            var top = section ? section.getBoundingClientRect().top + window.scrollY : 0;
            var at = (top - window.innerHeight * ARRIVE_AT) / max;

            at = Math.min(1, Math.max(0, at));
            stop.style.top = (at * 100) + '%';

            return at;
        });

        rail.classList.add('is-ready');
    }

    measure();
    window.addEventListener('resize', measure);

    if ('ResizeObserver' in window) {
        new ResizeObserver(measure).observe(document.body);
    }

    var trail = -1;
    var lastActive = -1;

    function tick() {
        requestAnimationFrame(tick);

        var height = track.clientHeight;
        var max = maxScroll();

        progress = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;

        var lead = progress * height;

        if (trail < 0) {
            trail = lead;
        }

        trail += (lead - trail) * CATCHUP;

        var gap = Math.min(MAX_STRETCH, Math.abs(lead - trail));
        var ry = RADIUS + gap * 0.5;
        var rx = RADIUS * Math.sqrt(RADIUS / ry);

        bubble.setAttribute('cy', String((lead + trail) * 0.5));
        bubble.setAttribute('rx', rx.toFixed(2));
        bubble.setAttribute('ry', ry.toFixed(2));

        grip.style.translate = '0 ' + lead + 'px';
        fill.style.scale = '1 ' + progress;

        var index = 0;

        for (var i = 0; i < marks.length; i++) {
            if (progress + 0.0005 >= marks[i]) {
                index = i;
            }
        }

        if (index !== lastActive) {
            lastActive = index;

            stops.forEach(function (stop, i) {
                stop.classList.toggle('is-active', i === index);
            });

            var label = stops[index] ? stops[index].textContent.trim() : '';

            grip.setAttribute('aria-valuetext', label);
        }

        grip.setAttribute('aria-valuenow', String(Math.round(progress * 100)));
    }

    requestAnimationFrame(tick);

    function fractionAt(clientY) {
        var box = track.getBoundingClientRect();

        return Math.min(1, Math.max(0, (clientY - box.top) / box.height));
    }

    function goToFraction(fraction, immediate) {
        App.scrollTo(fraction * maxScroll(), immediate);
    }

    var dragging = false;

    function onMove(event) {
        goToFraction(fractionAt(event.clientY), true);
    }

    function onUp() {
        dragging = false;
        rail.classList.remove('is-dragging');
        window.removeEventListener('pointermove', onMove);
        window.removeEventListener('pointerup', onUp);
        window.removeEventListener('pointercancel', onUp);
        document.body.style.userSelect = '';
    }

    grip.addEventListener('pointerdown', function (event) {
        event.preventDefault();
        event.stopPropagation();

        dragging = true;
        rail.classList.add('is-dragging');
        document.body.style.userSelect = 'none';

        goToFraction(fractionAt(event.clientY), true);

        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup', onUp);
        window.addEventListener('pointercancel', onUp);
    });

    track.addEventListener('pointerdown', function (event) {
        if (dragging) {
            return;
        }

        goToFraction(fractionAt(event.clientY), false);
    });

    stops.forEach(function (stop) {
        stop.addEventListener('click', function (event) {
            event.stopPropagation();

            var section = document.getElementById(stop.dataset.target);

            if (section) {
                App.scrollTo(section, false);
            }
        });

        stop.addEventListener('pointerdown', function (event) {
            event.stopPropagation();
        });
    });

    grip.addEventListener('keydown', function (event) {
        var step = event.shiftKey ? 0.1 : 0.04;

        if (event.key === 'ArrowDown' || event.key === 'PageDown') {
            event.preventDefault();
            goToFraction(Math.min(1, progress + step), false);
        } else if (event.key === 'ArrowUp' || event.key === 'PageUp') {
            event.preventDefault();
            goToFraction(Math.max(0, progress - step), false);
        } else if (event.key === 'Home') {
            event.preventDefault();
            goToFraction(0, false);
        } else if (event.key === 'End') {
            event.preventDefault();
            goToFraction(1, false);
        }
    });
})();
