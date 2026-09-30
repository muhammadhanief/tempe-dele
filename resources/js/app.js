import './bootstrap';

// Simple collapse toggle (Flowbite-like)
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-collapse-toggle]');
  if (!btn) return;

  const targetId = btn.getAttribute('data-collapse-toggle');
  const target = document.getElementById(targetId);
  if (!target) return;

  target.classList.toggle('hidden');

  const expanded = btn.getAttribute('aria-expanded') === 'true';
  btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
});

// Modal Loading
    const overlay = document.getElementById('loadingOverlay');

    function showLoading() {
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
    }

    function hideLoading() {
        overlay.classList.add('opacity-0', 'pointer-events-none');
        overlay.classList.remove('opacity-100');
    }

    document.addEventListener('submit', showLoading);

    document.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (a && a.href && !a.href.startsWith('#')
            && !a.href.startsWith('javascript')
            && !a.target) {
            showLoading();
        }
    });

    window.addEventListener('pageshow', hideLoading);

// =========================================================================
// Universal Touch & Drag-to-Scroll Enhancement for Tables (simantik-inspired)
// =========================================================================
function initDragAndSwipeScroll() {
    const containers = document.querySelectorAll('.overflow-x-auto, .table-responsive');

    containers.forEach((slider) => {
        if (slider.dataset.dragScrollInit) return;
        slider.dataset.dragScrollInit = 'true';

        let isDown = false;
        let startX = 0;
        let scrollLeft = 0;
        let isMoved = false;

        slider.classList.add('drag-scrollable');

        slider.addEventListener('mousedown', (e) => {
            if (e.target.closest('a, button, input, select, textarea, label, [role="button"], .no-drag')) {
                return;
            }
            isDown = true;
            isMoved = false;
            slider.classList.add('active-dragging');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.35;
            if (Math.abs(walk) > 4) {
                isMoved = true;
                e.preventDefault();
            }
            slider.scrollLeft = scrollLeft - walk;
        });

        window.addEventListener('mouseup', () => {
            if (isDown) {
                isDown = false;
                slider.classList.remove('active-dragging');
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDragAndSwipeScroll);
} else {
    initDragAndSwipeScroll();
}

window.initDragAndSwipeScroll = initDragAndSwipeScroll;


