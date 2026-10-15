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
let loadingTimeout = null;

function getLoadingOverlay() {
  return document.getElementById('loadingOverlay');
}

function showLoading() {
  const el = getLoadingOverlay();
  if (!el) return;
  el.classList.remove('opacity-0', 'pointer-events-none');
  el.classList.add('opacity-100');

  clearTimeout(loadingTimeout);
  loadingTimeout = setTimeout(() => {
    hideLoading();
  }, 12000);
}

function hideLoading() {
  clearTimeout(loadingTimeout);
  const el = getLoadingOverlay();
  if (!el) return;
  el.classList.add('opacity-0', 'pointer-events-none');
  el.classList.remove('opacity-100');
}

window.showLoading = showLoading;
window.hideLoading = hideLoading;

document.addEventListener('submit', (e) => {
  if (e.defaultPrevented) {
    hideLoading();
    return;
  }

  if (e.target && typeof e.target.checkValidity === 'function' && !e.target.checkValidity()) {
    hideLoading();
    return;
  }

  setTimeout(() => {
    if (e.defaultPrevented) {
      hideLoading();
    } else {
      showLoading();
    }
  }, 0);
});

document.addEventListener('click', (e) => {
  const a = e.target.closest('a');
  if (a && a.href && !a.href.startsWith('#')
      && !a.href.startsWith('javascript')
      && !a.target
      && !a.hasAttribute('download')
      && !a.getAttribute('href')?.startsWith('blob:')
      && !a.getAttribute('href')?.startsWith('data:')) {
    setTimeout(() => {
      if (!e.defaultPrevented) {
        showLoading();
      }
    }, 0);
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
    if (!hintBlock && wrapper.parentElement) {
      hintBlock = wrapper.parentElement.previousElementSibling;
      if (!hintBlock || !hintBlock.classList.contains('table-scroll-hint')) {
        hintBlock = wrapper.parentElement.querySelector(':scope > .table-scroll-hint') || hintBlock;
      }
    }
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

    // 5. Desktop Table Usability: Floating Navigation & Effortless Panning without Scrollbar
    wrapper.classList.add('drag-scrollable');

    // 5a. Floating Navigation Arrows on Parent Container
    const parentContainer = wrapper.parentElement;
    let btnLeft = null;
    let btnRight = null;

    if (parentContainer && !parentContainer.querySelector('.table-nav-left')) {
      parentContainer.classList.add('table-scroll-parent');

      btnLeft = document.createElement('button');
      btnLeft.type = 'button';
      btnLeft.className = 'table-nav-btn table-nav-left';
      btnLeft.setAttribute('aria-label', 'Geser tabel ke kiri');
      btnLeft.innerHTML = '<svg class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>';

      btnRight = document.createElement('button');
      btnRight.type = 'button';
      btnRight.className = 'table-nav-btn table-nav-right';
      btnRight.setAttribute('aria-label', 'Geser tabel ke kanan');
      btnRight.innerHTML = '<svg class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>';

      btnLeft.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        wrapper.scrollBy({ left: -320, behavior: 'smooth' });
      });

      btnRight.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        wrapper.scrollBy({ left: 320, behavior: 'smooth' });
      });

      parentContainer.appendChild(btnLeft);
      parentContainer.appendChild(btnRight);
    } else if (parentContainer) {
      btnLeft = parentContainer.querySelector('.table-nav-left');
      btnRight = parentContainer.querySelector('.table-nav-right');
    }

    const updateNavButtons = () => {
      const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
      if (maxScroll <= 10) {
        if (btnLeft) btnLeft.classList.remove('can-scroll');
        if (btnRight) btnRight.classList.remove('can-scroll');
        return;
      }
      if (btnLeft) {
        if (wrapper.scrollLeft > 15) {
          btnLeft.classList.add('can-scroll');
        } else {
          btnLeft.classList.remove('can-scroll');
        }
      }
      if (btnRight) {
        if (wrapper.scrollLeft < maxScroll - 15) {
          btnRight.classList.add('can-scroll');
        } else {
          btnRight.classList.remove('can-scroll');
        }
      }
    };

    wrapper.addEventListener('scroll', updateNavButtons, { passive: true });

    // 5b. Right-Click Drag & Middle-Click Pan (Left-click stays 100% for text selection!)
    let isPanDown = false;
    let panStartX = 0;
    let panScrollLeft = 0;
    let hasPanMoved = false;

    wrapper.addEventListener('mousedown', (e) => {
      if (e.button === 2 || e.button === 1 || (e.button === 0 && (window.__tableSpacePressed || e.altKey))) {
        if (e.target.closest('a, button, input, select, textarea, [role="button"]')) {
          return;
        }
        isPanDown = true;
        hasPanMoved = false;
        panStartX = e.pageX - wrapper.offsetLeft;
        panScrollLeft = wrapper.scrollLeft;
        wrapper.classList.add('pan-active');
        if (e.button === 1) e.preventDefault();
      }
    });

    window.addEventListener('mousemove', (e) => {
      if (!isPanDown) return;
      const x = e.pageX - wrapper.offsetLeft;
      const walk = (x - panStartX) * 1.4;
      if (Math.abs(walk) > 4) {
        hasPanMoved = true;
      }
      wrapper.scrollLeft = panScrollLeft - walk;
    });

    window.addEventListener('mouseup', () => {
      if (isPanDown) {
        isPanDown = false;
        wrapper.classList.remove('pan-active');
      }
    });

    wrapper.addEventListener('contextmenu', (e) => {
      if (hasPanMoved) {
        e.preventDefault();
        hasPanMoved = false;
      }
    });

    // 5c. Selection Edge Auto-Scroll: When dragging text selection to table borders, scroll automatically
    let isLeftDown = false;
    let edgeScrollTimer = null;

    wrapper.addEventListener('mousedown', (e) => {
      if (e.button === 0 && !window.__tableSpacePressed && !e.altKey) {
        isLeftDown = true;
      }
    });

    window.addEventListener('mousemove', (e) => {
      if (!isLeftDown) return;
      const rect = wrapper.getBoundingClientRect();
      const edgeThreshold = 35;

      if (e.clientX > rect.right - edgeThreshold && e.clientX < rect.right + 80) {
        if (!edgeScrollTimer) {
          edgeScrollTimer = setInterval(() => {
            wrapper.scrollLeft += 16;
          }, 20);
        }
      } else if (e.clientX < rect.left + edgeThreshold && e.clientX > rect.left - 80) {
        if (!edgeScrollTimer) {
          edgeScrollTimer = setInterval(() => {
            wrapper.scrollLeft -= 16;
          }, 20);
        }
      } else {
        if (edgeScrollTimer) {
          clearInterval(edgeScrollTimer);
          edgeScrollTimer = null;
        }
      }
    });

    window.addEventListener('mouseup', () => {
      isLeftDown = false;
      if (edgeScrollTimer) {
        clearInterval(edgeScrollTimer);
        edgeScrollTimer = null;
      }
    });

    // 6. Handle resize with ResizeObserver
    if (window.ResizeObserver) {
      const ro = new ResizeObserver(() => {
        updateScrollIndicator();
        updateNavButtons();
      });
      ro.observe(wrapper);
      ro.observe(table);
    }

    // Initial indicator sync
    setTimeout(() => {
      updateScrollIndicator();
      updateNavButtons();
    }, 100);
    setTimeout(() => {
      updateScrollIndicator();
      updateNavButtons();
    }, 500);
  });
}

// Global listener for Spacebar hold
window.__tableSpacePressed = false;
window.addEventListener('keydown', (e) => {
  if (e.code === 'Space' && !e.target.closest('input, textarea, select, [contenteditable="true"]')) {
    window.__tableSpacePressed = true;
    document.querySelectorAll('.overflow-x-auto, .table-responsive').forEach((w) => {
      w.classList.add('pan-ready');
    });
  }
});
window.addEventListener('keyup', (e) => {
  if (e.code === 'Space') {
    window.__tableSpacePressed = false;
    document.querySelectorAll('.overflow-x-auto, .table-responsive').forEach((w) => {
      w.classList.remove('pan-ready');
    });
  }
});

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initTableScrollbars);
} else {
  initTableScrollbars();
}

window.initTableScrollbars = initTableScrollbars;
window.initDragAndSwipeScroll = initTableScrollbars;
