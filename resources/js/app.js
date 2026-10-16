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
    let btnPrevMobile = null;
    let btnNextMobile = null;
    const parentContainer = wrapper.parentElement;

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

      // 1b. Enhance hintBlock with interactive quick-nav arrow pills if not already present
      let navGroup = hintBlock.querySelector('.table-scroll-nav-group');
      if (!navGroup && hintLabel && hintLabel.parentElement) {
        navGroup = document.createElement('div');
        navGroup.className = 'table-scroll-nav-group inline-flex items-center gap-1 shrink-0 select-none';

        btnPrevMobile = document.createElement('button');
        btnPrevMobile.type = 'button';
        btnPrevMobile.className = 'table-nav-pill-prev inline-flex items-center justify-center w-5.5 h-5.5 rounded-md bg-slate-100/90 hover:bg-amber-100 active:bg-amber-200 text-slate-600 hover:text-amber-800 transition-colors cursor-pointer shadow-2xs';
        btnPrevMobile.setAttribute('aria-label', 'Geser ke kiri');
        btnPrevMobile.setAttribute('title', 'Geser ke kiri');
        btnPrevMobile.innerHTML = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>';

        btnNextMobile = document.createElement('button');
        btnNextMobile.type = 'button';
        btnNextMobile.className = 'table-nav-pill-next inline-flex items-center justify-center w-5.5 h-5.5 rounded-md bg-slate-100/90 hover:bg-amber-100 active:bg-amber-200 text-slate-600 hover:text-amber-800 transition-colors cursor-pointer shadow-2xs';
        btnNextMobile.setAttribute('aria-label', 'Geser ke kanan');
        btnNextMobile.setAttribute('title', 'Geser ke kanan');
        btnNextMobile.innerHTML = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>';

        const parentOfLabel = hintLabel.parentElement;
        parentOfLabel.replaceChild(navGroup, hintLabel);
        navGroup.appendChild(btnPrevMobile);
        navGroup.appendChild(hintLabel);
        navGroup.appendChild(btnNextMobile);

        hintLabel.classList.add('cursor-pointer', 'hover:text-amber-700', 'px-1', 'py-0.5', 'rounded', 'hover:bg-amber-50');
        hintLabel.setAttribute('title', 'Ketuk untuk beralih posisi');

        btnPrevMobile.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const step = Math.max(wrapper.clientWidth * 0.7, 180);
          wrapper.scrollBy({ left: -step, behavior: 'smooth' });
        });

        btnNextMobile.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const step = Math.max(wrapper.clientWidth * 0.7, 180);
          wrapper.scrollBy({ left: step, behavior: 'smooth' });
        });

        hintLabel.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const maxS = wrapper.scrollWidth - wrapper.clientWidth;
          if (wrapper.scrollLeft > maxS / 2) {
            wrapper.scrollTo({ left: 0, behavior: 'smooth' });
          } else {
            wrapper.scrollTo({ left: maxS, behavior: 'smooth' });
          }
        });
      } else if (navGroup) {
        btnPrevMobile = navGroup.querySelector('.table-nav-pill-prev');
        btnNextMobile = navGroup.querySelector('.table-nav-pill-next');
      }
    }

    // 2. Metric caching to eliminate forced reflows during continuous scroll
    let cachedClientW = wrapper.clientWidth;
    let cachedScrollW = wrapper.scrollWidth;
    let cachedMaxScroll = Math.max(0, cachedScrollW - cachedClientW);
    let cachedTrackW = track ? (track.clientWidth || track.getBoundingClientRect().width) : 0;
    let cachedThumbW = 32;

    function refreshMetrics() {
      cachedClientW = wrapper.clientWidth;
      cachedScrollW = wrapper.scrollWidth;
      cachedMaxScroll = Math.max(0, cachedScrollW - cachedClientW);

      if (track) {
        cachedTrackW = track.clientWidth || track.getBoundingClientRect().width;
        if (cachedTrackW > 0 && cachedScrollW > 0) {
          const thumbRatio = Math.max(cachedClientW / cachedScrollW, 0.16);
          cachedThumbW = Math.max(thumbRatio * cachedTrackW, 36);
          if (thumb) thumb.style.width = `${cachedThumbW}px`;
        }
      }
    }

    refreshMetrics();

    // 3. Synchronize visual scrollbar thumb with table scrollLeft (Zero-Reflow)
    function updateScrollIndicator() {
      if (cachedMaxScroll <= 5) {
        if (hintBlock) hintBlock.classList.add('hidden');
        if (parentContainer) {
          parentContainer.classList.remove('has-scroll-left', 'has-scroll-right');
        }
        return;
      } else {
        if (hintBlock && hintBlock.classList.contains('sm:hidden')) {
          hintBlock.classList.remove('hidden');
        }
      }

      const currentScroll = Math.max(0, Math.min(wrapper.scrollLeft, cachedMaxScroll));
      const scrollPercent = cachedMaxScroll > 0 ? (currentScroll / cachedMaxScroll) : 0;

      // Update mobile edge gradient shadow cue
      if (parentContainer) {
        if (currentScroll > 10) {
          parentContainer.classList.add('has-scroll-left');
        } else {
          parentContainer.classList.remove('has-scroll-left');
        }

        if (currentScroll < cachedMaxScroll - 10) {
          parentContainer.classList.add('has-scroll-right');
        } else {
          parentContainer.classList.remove('has-scroll-right');
        }
      }

      if (track && thumb && cachedTrackW > 0) {
        const maxThumbTranslate = Math.max(0, cachedTrackW - cachedThumbW);
        const thumbTranslate = scrollPercent * maxThumbTranslate;
        thumb.style.transform = `translateX(${thumbTranslate}px)`;
      }

      if (hintLabel) {
        if (scrollPercent <= 0.04) {
          hintLabel.textContent = 'Geser kanan »';
        } else if (scrollPercent >= 0.96) {
          hintLabel.textContent = '« Geser kiri';
        } else {
          hintLabel.textContent = `${Math.round(scrollPercent * 100)}%`;
        }
      }

      if (btnPrevMobile) {
        btnPrevMobile.style.opacity = scrollPercent <= 0.02 ? '0.35' : '1';
        btnPrevMobile.style.pointerEvents = scrollPercent <= 0.02 ? 'none' : 'auto';
      }
      if (btnNextMobile) {
        btnNextMobile.style.opacity = scrollPercent >= 0.98 ? '0.35' : '1';
        btnNextMobile.style.pointerEvents = scrollPercent >= 0.98 ? 'none' : 'auto';
      }
    }

    // 4. High-performance scroll listener on wrapper
    wrapper.addEventListener('scroll', () => {
      window.requestAnimationFrame(updateScrollIndicator);
    }, { passive: true });

    // 5. Interactive click & touch-drag on the track (1:1 instant tracking, zero stutter)
    if (track) {
      let isTrackDown = false;

      const handleTrackSeek = (clientX, isContinuousDrag) => {
        refreshMetrics();
        if (cachedTrackW <= 0 || cachedMaxScroll <= 0) return;
        const rect = track.getBoundingClientRect();
        const clickX = clientX - rect.left;
        const ratio = Math.max(0, Math.min(clickX / cachedTrackW, 1));
        const targetScroll = ratio * cachedMaxScroll;

        if (isContinuousDrag) {
          wrapper.scrollLeft = targetScroll;
        } else {
          wrapper.scrollTo({
            left: targetScroll,
            behavior: 'smooth'
          });
        }
      };

      track.addEventListener('click', (e) => {
        handleTrackSeek(e.clientX, false);
      });

      track.addEventListener('touchstart', (e) => {
        isTrackDown = true;
        if (thumb) thumb.classList.add('is-dragging');
        if (e.touches && e.touches[0]) {
          handleTrackSeek(e.touches[0].clientX, true);
        }
      }, { passive: true });

      track.addEventListener('touchmove', (e) => {
        if (!isTrackDown) return;
        if (e.touches && e.touches[0]) {
          handleTrackSeek(e.touches[0].clientX, true);
        }
      }, { passive: true });

      track.addEventListener('touchend', () => {
        isTrackDown = false;
        if (thumb) thumb.classList.remove('is-dragging');
      });
      track.addEventListener('touchcancel', () => {
        isTrackDown = false;
        if (thumb) thumb.classList.remove('is-dragging');
      });
    }

    // 6. Direct Touch Gesture with Natural Momentum Glide on Mobile
    let touchStartX = 0;
    let touchStartY = 0;
    let touchStartScroll = 0;
    let isHorizontalTouch = false;
    let lastTouchX = 0;
    let lastTouchTime = 0;
    let touchVelocity = 0;
    let momentumAnimId = null;

    wrapper.addEventListener('touchstart', (e) => {
      if (e.touches.length !== 1) return;
      if (momentumAnimId) {
        cancelAnimationFrame(momentumAnimId);
        momentumAnimId = null;
      }
      refreshMetrics();
      const t = e.touches[0];
      touchStartX = t.clientX;
      touchStartY = t.clientY;
      lastTouchX = t.clientX;
      lastTouchTime = Date.now();
      touchStartScroll = wrapper.scrollLeft;
      isHorizontalTouch = false;
      touchVelocity = 0;
    }, { passive: true });

    wrapper.addEventListener('touchmove', (e) => {
      if (e.touches.length !== 1) return;
      const t = e.touches[0];
      const dx = t.clientX - touchStartX;
      const dy = t.clientY - touchStartY;
      const now = Date.now();

      if (!isHorizontalTouch) {
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 6) {
          isHorizontalTouch = true;
        } else if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 6) {
          return;
        }
      }

      if (isHorizontalTouch) {
        const dt = Math.max(now - lastTouchTime, 1);
        touchVelocity = (t.clientX - lastTouchX) / dt;
        lastTouchX = t.clientX;
        lastTouchTime = now;
      }
    }, { passive: true });

    wrapper.addEventListener('touchend', () => {
      if (!isHorizontalTouch) return;
      if (Math.abs(touchVelocity) > 0.25) {
        let v = touchVelocity * 13;
        const friction = 0.93;

        const glideStep = () => {
          if (Math.abs(v) < 0.4) {
            momentumAnimId = null;
            return;
          }
          wrapper.scrollLeft -= v;
          v *= friction;
          momentumAnimId = requestAnimationFrame(glideStep);
        };
        momentumAnimId = requestAnimationFrame(glideStep);
      }
    }, { passive: true });

    // 7. Desktop Table Usability: Floating Navigation & Effortless Panning without Scrollbar
    wrapper.classList.add('drag-scrollable');

    // 7a. Floating Navigation Arrows on Parent Container (Desktop Hover)
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

    // 8. Handle resize with ResizeObserver
    if (window.ResizeObserver) {
      const ro = new ResizeObserver(() => {
        refreshMetrics();
        updateScrollIndicator();
        updateNavButtons();
      });
      ro.observe(wrapper);
      ro.observe(table);
      if (track) ro.observe(track);
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
