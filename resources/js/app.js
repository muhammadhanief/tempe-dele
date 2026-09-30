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
  if (!overlay) return;
  overlay.classList.remove('opacity-0', 'pointer-events-none');
  overlay.classList.add('opacity-100');
}

function hideLoading() {
  if (!overlay) return;
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
// Universal Mobile Scrollbar & Drag-to-Scroll (simantik-inspired)
// =========================================================================
function initTableScrollbars() {
  const tableWrappers = document.querySelectorAll('.overflow-x-auto, .table-responsive');

  tableWrappers.forEach((wrapper) => {
    const table = wrapper.querySelector('table');
    if (!table) return;

    if (wrapper.dataset.scrollBarInit) return;
    wrapper.dataset.scrollBarInit = 'true';

    // 1. Locate or enhance preceding mobile hint block
    let hintBlock = wrapper.previousElementSibling;
    let track = null;
    let thumb = null;
    let hintLabel = null;

    if (hintBlock && (hintBlock.classList.contains('sm:hidden') || hintBlock.classList.contains('table-scroll-hint') || hintBlock.textContent.includes('Geser tabel'))) {
      hintBlock.classList.add('table-scroll-hint');
      track = hintBlock.querySelector('.table-scroll-track');
      thumb = hintBlock.querySelector('.table-scroll-thumb');
      hintLabel = hintBlock.querySelector('.table-scroll-pct');

      // If track doesn't exist yet, build it dynamically
      if (!track) {
        hintBlock.classList.remove('flex-row', 'items-center');
        hintBlock.classList.add('flex', 'flex-col', 'gap-1.5', 'mb-2.5');

        const topRow = document.createElement('div');
        topRow.className = 'flex items-center justify-between w-full text-[11px] font-medium text-slate-500';

        const leftSide = document.createElement('span');
        leftSide.className = 'inline-flex items-center gap-1.5';
        while (hintBlock.firstChild) {
          leftSide.appendChild(hintBlock.firstChild);
        }
        topRow.appendChild(leftSide);

        hintLabel = document.createElement('span');
        hintLabel.className = 'table-scroll-pct text-[10px] font-mono text-slate-400 shrink-0';
        hintLabel.textContent = 'Geser »';
        topRow.appendChild(hintLabel);

        hintBlock.appendChild(topRow);

        track = document.createElement('div');
        track.className = 'table-scroll-track';

        thumb = document.createElement('div');
        thumb.className = 'table-scroll-thumb';
        thumb.style.width = '30%';
        thumb.style.transform = 'translateX(0px)';

        track.appendChild(thumb);
        hintBlock.appendChild(track);
      }
    }

    // 2. Synchronize visual scrollbar thumb with table scrollLeft
    function updateScrollIndicator() {
      const clientW = wrapper.clientWidth;
      const scrollW = wrapper.scrollWidth;
      const maxScroll = scrollW - clientW;

      if (maxScroll <= 5) {
        if (hintBlock) hintBlock.classList.add('hidden');
        return;
      } else {
        if (hintBlock && hintBlock.classList.contains('sm:hidden')) {
          hintBlock.classList.remove('hidden');
        }
      }

      if (!track || !thumb) return;

      const trackW = track.clientWidth || track.getBoundingClientRect().width;
      if (trackW <= 0) return;

      const thumbRatio = Math.max(clientW / scrollW, 0.18);
      const thumbW = Math.max(thumbRatio * trackW, 32);
      thumb.style.width = `${thumbW}px`;

      const currentScroll = Math.max(0, Math.min(wrapper.scrollLeft, maxScroll));
      const scrollPercent = currentScroll / maxScroll;
      const maxThumbTranslate = trackW - thumbW;
      const thumbTranslate = scrollPercent * maxThumbTranslate;

      thumb.style.transform = `translateX(${thumbTranslate}px)`;

      if (hintLabel) {
        if (scrollPercent <= 0.05) {
          hintLabel.textContent = 'Geser kanan »';
        } else if (scrollPercent >= 0.95) {
          hintLabel.textContent = '« Geser kiri';
        } else {
          hintLabel.textContent = `${Math.round(scrollPercent * 100)}%`;
        }
      }
    }

    // 3. Listen to scroll events on wrapper
    wrapper.addEventListener('scroll', () => {
      window.requestAnimationFrame(updateScrollIndicator);
    }, { passive: true });

    // 4. Interactive click/drag on the track
    if (track) {
      let isTrackDown = false;

      const handleTrackSeek = (clientX) => {
        const rect = track.getBoundingClientRect();
        const clickX = clientX - rect.left;
        const ratio = Math.max(0, Math.min(clickX / rect.width, 1));
        const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
        wrapper.scrollTo({
          left: ratio * maxScroll,
          behavior: 'smooth'
        });
      };

      track.addEventListener('click', (e) => {
        handleTrackSeek(e.clientX);
      });

      track.addEventListener('touchstart', (e) => {
        isTrackDown = true;
        if (e.touches && e.touches[0]) {
          handleTrackSeek(e.touches[0].clientX);
        }
      }, { passive: true });

      track.addEventListener('touchmove', (e) => {
        if (!isTrackDown) return;
        if (e.touches && e.touches[0]) {
          handleTrackSeek(e.touches[0].clientX);
        }
      }, { passive: true });

      track.addEventListener('touchend', () => {
        isTrackDown = false;
      });
    }

    // 5. Desktop drag-to-scroll support
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    wrapper.classList.add('drag-scrollable');

    wrapper.addEventListener('mousedown', (e) => {
      if (e.target.closest('a, button, input, select, textarea, label, [role="button"], .no-drag')) {
        return;
      }
      isDown = true;
      wrapper.classList.add('active-dragging');
      startX = e.pageX - wrapper.offsetLeft;
      scrollLeft = wrapper.scrollLeft;
    });

    window.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      const x = e.pageX - wrapper.offsetLeft;
      const walk = (x - startX) * 1.35;
      if (Math.abs(walk) > 4) {
        e.preventDefault();
      }
      wrapper.scrollLeft = scrollLeft - walk;
    });

    window.addEventListener('mouseup', () => {
      if (isDown) {
        isDown = false;
        wrapper.classList.remove('active-dragging');
      }
    });

    // 6. Handle resize with ResizeObserver
    if (window.ResizeObserver) {
      const ro = new ResizeObserver(() => {
        updateScrollIndicator();
      });
      ro.observe(wrapper);
      ro.observe(table);
    }

    // Initial indicator sync
    setTimeout(updateScrollIndicator, 100);
    setTimeout(updateScrollIndicator, 500);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initTableScrollbars);
} else {
  initTableScrollbars();
}

window.initTableScrollbars = initTableScrollbars;
window.initDragAndSwipeScroll = initTableScrollbars;
