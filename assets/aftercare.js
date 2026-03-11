(function() {
    'use strict';

    var config = window.ptbaConfig || {};
    var phases = config.phases || [];
    var root = document.getElementById('ptba-aftercare-root');

    if (!root || !phases.length) return;

    // Apply accent color
    if (config.accentColor) {
        root.style.setProperty('--ptba-accent', config.accentColor);
    }

    // Build HTML
    var html = '';

    // Header
    html += '<div class="ptba-header">';
    html += '<h2>Tattoo Aftercare Guide</h2>';
    html += '<p>Follow these steps for the best healing results</p>';
    html += '</div>';

    // Day Tracker
    if (config.showTracker) {
        html += '<div class="ptba-tracker">';
        html += '<label for="ptba-date-input">When did you get your tattoo?</label>';
        html += '<input type="date" id="ptba-date-input" max="' + formatDate(new Date()) + '">';
        html += '<div class="ptba-tracker-result" id="ptba-tracker-result">';
        html += '<span id="ptba-day-count"></span>';
        html += '<div class="ptba-tracker-phase" id="ptba-current-phase"></div>';
        html += '</div>';
        html += '</div>';
    }

    // Timeline
    html += '<div class="ptba-timeline">';
    for (var i = 0; i < phases.length; i++) {
        var phase = phases[i];
        var dayLabel = getDayLabel(phase.day_start, phase.day_end);

        html += '<div class="ptba-phase" data-index="' + i + '" data-start="' + phase.day_start + '" data-end="' + phase.day_end + '">';
        html += '<div class="ptba-phase-dot"></div>';
        html += '<button class="ptba-phase-toggle" type="button" aria-expanded="false">';
        html += '<span class="ptba-phase-icon">' + escapeHtml(phase.icon) + '</span>';
        html += '<div class="ptba-phase-info">';
        html += '<div class="ptba-phase-title">' + escapeHtml(phase.title) + '</div>';
        html += '<div class="ptba-phase-days">' + escapeHtml(dayLabel) + '</div>';
        html += '</div>';
        html += '<span class="ptba-phase-arrow">&#9662;</span>';
        html += '</button>';
        html += '<div class="ptba-phase-body">';
        html += '<ul class="ptba-phase-instructions">';
        for (var j = 0; j < phase.instructions.length; j++) {
            html += '<li>' + escapeHtml(phase.instructions[j]) + '</li>';
        }
        html += '</ul>';
        html += '</div>';
        html += '</div>';
    }
    html += '</div>';

    // Emergency note
    if (config.emergencyNote) {
        html += '<div class="ptba-emergency">';
        html += '<span class="ptba-emergency-icon">⚠️</span>';
        html += '<p>' + escapeHtml(config.emergencyNote) + '</p>';
        html += '</div>';
    }

    // Studio contact
    if (config.studioName || config.studioPhone || config.studioEmail) {
        html += '<div class="ptba-studio">';
        if (config.studioName) {
            html += '<div class="ptba-studio-name">' + escapeHtml(config.studioName) + '</div>';
        }
        html += '<div class="ptba-studio-contact">';
        if (config.studioPhone) {
            html += '<a href="tel:' + escapeHtml(config.studioPhone) + '">📞 ' + escapeHtml(config.studioPhone) + '</a>';
        }
        if (config.studioEmail) {
            html += '<a href="mailto:' + escapeHtml(config.studioEmail) + '">✉️ ' + escapeHtml(config.studioEmail) + '</a>';
        }
        html += '</div>';
        html += '</div>';
    }

    // Print button
    if (config.showPrint) {
        html += '<div class="ptba-actions">';
        html += '<button class="ptba-btn-print" type="button" id="ptba-print-btn">🖨️ Print Instructions</button>';
        html += '</div>';
    }

    root.innerHTML = html;

    // Toggle phase accordion
    root.addEventListener('click', function(e) {
        var toggle = e.target.closest('.ptba-phase-toggle');
        if (!toggle) return;

        var phase = toggle.closest('.ptba-phase');
        var body = phase.querySelector('.ptba-phase-body');
        var isOpen = phase.classList.contains('open');

        // Close all
        var allPhases = root.querySelectorAll('.ptba-phase.open');
        for (var k = 0; k < allPhases.length; k++) {
            allPhases[k].classList.remove('open');
            allPhases[k].querySelector('.ptba-phase-toggle').setAttribute('aria-expanded', 'false');
            allPhases[k].querySelector('.ptba-phase-body').style.maxHeight = '0';
        }

        // Open clicked (if wasn't open)
        if (!isOpen) {
            phase.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            body.style.maxHeight = body.scrollHeight + 'px';
        }
    });

    // Auto-open first phase
    var firstToggle = root.querySelector('.ptba-phase-toggle');
    if (firstToggle) {
        firstToggle.click();
    }

    // Day tracker
    var dateInput = document.getElementById('ptba-date-input');
    if (dateInput) {
        // Check localStorage for saved date
        try {
            var savedDate = localStorage.getItem('ptba_tattoo_date');
            if (savedDate) {
                dateInput.value = savedDate;
                updateTracker(savedDate);
            }
        } catch (e) { /* ignore */ }

        dateInput.addEventListener('change', function() {
            var val = this.value;
            try { localStorage.setItem('ptba_tattoo_date', val); } catch (e) { /* ignore */ }
            updateTracker(val);
        });
    }

    // Print
    var printBtn = document.getElementById('ptba-print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            // Open all phases for print
            var allPhases = root.querySelectorAll('.ptba-phase');
            for (var k = 0; k < allPhases.length; k++) {
                allPhases[k].classList.add('open');
                allPhases[k].querySelector('.ptba-phase-body').style.maxHeight = 'none';
            }
            window.print();
        });
    }

    function updateTracker(dateStr) {
        var tattooDate = new Date(dateStr + 'T00:00:00');
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var diffMs = today - tattooDate;
        var dayNum = Math.floor(diffMs / (1000 * 60 * 60 * 24));

        if (dayNum < 0) return;

        var resultEl = document.getElementById('ptba-tracker-result');
        var countEl = document.getElementById('ptba-day-count');
        var phaseEl = document.getElementById('ptba-current-phase');

        var dayText = dayNum === 0 ? "You got your tattoo today!" : "Day " + dayNum + " of healing";
        countEl.textContent = dayText;

        // Find current phase
        var currentPhase = null;
        var currentIndex = -1;
        for (var p = 0; p < phases.length; p++) {
            if (dayNum >= phases[p].day_start && dayNum <= phases[p].day_end) {
                currentPhase = phases[p];
                currentIndex = p;
                break;
            }
        }

        if (currentPhase) {
            phaseEl.textContent = 'Current phase: ' + currentPhase.title;
        } else if (dayNum > phases[phases.length - 1].day_end) {
            phaseEl.textContent = 'Your tattoo is fully healed! Keep protecting it from the sun.';
        }

        resultEl.classList.add('visible');

        // Update timeline dot states
        var phaseEls = root.querySelectorAll('.ptba-phase');
        for (var q = 0; q < phaseEls.length; q++) {
            phaseEls[q].classList.remove('active', 'completed');
            var start = parseInt(phaseEls[q].dataset.start, 10);
            var end = parseInt(phaseEls[q].dataset.end, 10);

            if (dayNum > end) {
                phaseEls[q].classList.add('completed');
            } else if (dayNum >= start && dayNum <= end) {
                phaseEls[q].classList.add('active');
                // Auto-open current phase
                if (!phaseEls[q].classList.contains('open')) {
                    phaseEls[q].querySelector('.ptba-phase-toggle').click();
                }
            }
        }
    }

    function getDayLabel(start, end) {
        if (start === 0 && end === 0) return 'Day 0';
        if (end >= 999) return 'Day ' + start + '+';
        if (start === end) return 'Day ' + start;
        return 'Days ' + start + '–' + end;
    }

    function formatDate(d) {
        var m = d.getMonth() + 1;
        var day = d.getDate();
        return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
})();
