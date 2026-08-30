(function () {
    var orbit = document.querySelector('.orbit');

    if (!orbit) {
        return;
    }

    var chips = orbit.querySelectorAll('.orbit__chip');

    var STAGGER = 260;
    var SETTLE = 620;
    var HOLD = 260;

    function start() {
        chips.forEach(function (chip, i) {
            setTimeout(function () {
                chip.classList.add('is-in');
            }, i * STAGGER);
        });

        setTimeout(function () {
            orbit.classList.add('is-orbiting');
        }, (chips.length - 1) * STAGGER + SETTLE + HOLD);
    }

    document.addEventListener('phone:ready', start, { once: true });
})();
