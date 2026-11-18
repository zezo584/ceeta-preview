/* CEETA — interactions & animations (vanilla JS, no dependencies) */
(function () {
  'use strict';
  var d = document, w = window, html = d.documentElement;
  var RTL = html.dir === 'rtl';
  var reduce = w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches;
  html.classList.remove('no-js'); html.classList.add('js');
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

  /* ---------- preloader ---------- */
  var pre = $('.preloader');
  function hidePre() { if (pre) { pre.classList.add('done'); setTimeout(function () { pre && pre.remove(); }, 800); } }
  if (pre) { w.addEventListener('load', function () { setTimeout(hidePre, 250); }); setTimeout(hidePre, 2600); }

  /* ---------- header state, to-top ring, parallax ---------- */
  var header = $('.header'), toTop = $('.to-top'), ring = toTop && $('circle', toTop);
  var parallax = $$('[data-parallax]');
  var ticking = false;
  function onScroll() {
    var y = w.pageYOffset || html.scrollTop;
    if (header) header.classList.toggle('is-stuck', y > 40);
    if (toTop) {
      toTop.classList.toggle('show', y > 600);
      var h = d.body.scrollHeight - w.innerHeight;
      if (ring) ring.style.strokeDashoffset = 151 - (151 * Math.min(1, y / (h || 1)));
    }
    if (!reduce) parallax.forEach(function (el) {
      var r = el.parentElement.getBoundingClientRect();
      if (r.bottom < 0 || r.top > w.innerHeight) return;
      var sp = parseFloat(el.getAttribute('data-parallax')) || 0.15;
      el.style.transform = 'translate3d(0,' + ((r.top + r.height / 2 - w.innerHeight / 2) * -sp).toFixed(1) + 'px,0)';
    });
    ticking = false;
  }
  w.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener('click', function () { w.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });

  /* ---------- reveal on scroll ---------- */
  var revealEls = $$('[data-reveal], .split');
  if ('IntersectionObserver' in w && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.14, rootMargin: '0px 0px -6% 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else revealEls.forEach(function (el) { el.classList.add('in'); });

  /* ---------- counters ---------- */
  var counters = $$('[data-count]');
  function runCount(el) {
    var end = parseFloat(el.getAttribute('data-count')), dur = 1600, t0 = null;
    if (reduce) { el.textContent = end; return; }
    function step(t) { if (!t0) t0 = t; var p = Math.min(1, (t - t0) / dur); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); }
    requestAnimationFrame(step);
  }
  if ('IntersectionObserver' in w) {
    var cio = new IntersectionObserver(function (en) { en.forEach(function (e) { if (e.isIntersecting) { runCount(e.target); cio.unobserve(e.target); } }); }, { threshold: .6 });
    counters.forEach(function (c) { cio.observe(c); });
  } else counters.forEach(runCount);

  /* ---------- drawer (mobile menu) ---------- */
  var drawer = $('.drawer'), burger = $('.burger');
  function setDrawer(open) {
    if (!drawer) return;
    drawer.classList.toggle('open', open); drawer.setAttribute('aria-hidden', !open);
    if (burger) burger.setAttribute('aria-expanded', open);
    d.body.style.overflow = open ? 'hidden' : '';
    if (open) { var f = $('.drawer__close', drawer); f && f.focus(); } else if (burger) burger.focus();
  }
  if (burger) burger.addEventListener('click', function () { setDrawer(true); });
  $$('[data-close-drawer]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); }); });
  $$('.drawer__acc > button').forEach(function (b) {
    b.addEventListener('click', function () { var p = b.parentElement; p.classList.toggle('open'); b.setAttribute('aria-expanded', p.classList.contains('open')); });
  });
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setDrawer(false); closeLB(); } });

  /* ---------- hero slider ---------- */
  var hero = $('.hero[data-slider]');
  if (hero) {
    var slides = $$('.hero__slide', hero), items = $$('.rail__item', hero), countEl = $('.rail__count b', hero);
    var cur = 0, timer = null, DUR = 6500;
    hero.style.setProperty('--dur', DUR + 'ms');
    var go = function (i) {
      cur = (i + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('is-active', k === cur); });
      items.forEach(function (it, k) {
        it.classList.remove('is-active'); void it.offsetWidth;
        if (k === cur) it.classList.add('is-active');
        it.setAttribute('aria-current', k === cur ? 'true' : 'false');
      });
      if (countEl) countEl.textContent = ('0' + (cur + 1)).slice(-2);
      var nx = slides[(cur + 1) % slides.length]; var im = nx && $('img[data-src]', nx);
      if (im) { im.src = im.getAttribute('data-src'); im.removeAttribute('data-src'); }
      restart();
    };
    var restart = function () { clearTimeout(timer); if (!reduce) timer = setTimeout(function () { go(cur + 1); }, DUR); };
    items.forEach(function (it, k) { it.addEventListener('click', function () { go(k); }); });
    var pv = $('[data-hero-prev]', hero), nv = $('[data-hero-next]', hero);
    pv && pv.addEventListener('click', function () { go(cur - 1); });
    nv && nv.addEventListener('click', function () { go(cur + 1); });
    var sx = null;
    hero.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (sx === null) return; var dx = e.changedTouches[0].clientX - sx; sx = null;
      if (Math.abs(dx) > 50) go(cur + ((dx < 0) !== RTL ? 1 : -1));
    });
    d.addEventListener('visibilitychange', function () { if (d.hidden) clearTimeout(timer); else restart(); });
    go(0);
  }

  /* ---------- 3D carousel (training photos) ---------- */
  $$('[data-carousel]').forEach(function (root) {
    var stage = $('.carousel__stage', root), items = $$('.carousel__item', root), dotsWrap = $('.carousel__dots', root);
    var n = items.length, cur = 0, timer = null, DUR = 3800, dir = RTL ? -1 : 1;
    if (!n) return;
    items.forEach(function (_, i) {
      var b = d.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', (root.getAttribute('data-label') || 'Slide') + ' ' + (i + 1));
      b.addEventListener('click', function () { go(i); }); dotsWrap && dotsWrap.appendChild(b);
    });
    var dots = dotsWrap ? $$('button', dotsWrap) : [];
    function layout() {
      var small = w.innerWidth < 760;
      items.forEach(function (it, i) {
        var off = i - cur; if (off > n / 2) off -= n; if (off < -n / 2) off += n;
        var a = Math.abs(off), x = off * (small ? 62 : 44) * dir, z = -a * (small ? 220 : 260), ry = -off * (small ? 0 : 28) * dir;
        var sc = a === 0 ? 1 : (small ? .86 : .9);
        it.style.transform = 'translateX(' + x + '%) translateZ(' + z + 'px) rotateY(' + ry + 'deg) scale(' + sc + ')';
        it.style.opacity = a > 2 ? 0 : (a === 0 ? 1 : (a === 1 ? .85 : .45));
        it.style.filter = a === 0 ? 'none' : 'brightness(.6) saturate(.8)';
        it.style.zIndex = 100 - a; it.style.pointerEvents = a > 2 ? 'none' : 'auto';
        it.classList.toggle('is-active', a === 0); it.setAttribute('aria-hidden', a !== 0);
        var im = $('img[data-src]', it); if (a <= 2 && im) { im.src = im.getAttribute('data-src'); im.removeAttribute('data-src'); }
      });
      dots.forEach(function (b, i) { b.classList.toggle('is-active', i === cur); });
    }
    function go(i) { cur = (i + n) % n; layout(); restart(); }
    function restart() { clearTimeout(timer); if (!reduce && inView) timer = setTimeout(function () { go(cur + 1); }, DUR); }
    var inView = true;
    if ('IntersectionObserver' in w) new IntersectionObserver(function (en) { inView = en[0].isIntersecting; restart(); }, { threshold: .25 }).observe(root);
    items.forEach(function (it, i) {
      it.addEventListener('click', function () { if (moved) return; if (i === cur) openLB(items.map(function (x) { var im = $('img', x); return { src: im.getAttribute('data-full') || im.getAttribute('src') || im.getAttribute('data-src'), cap: im.alt }; }), i); else go(i); });
    });
    var pv = $('[data-prev]', root), nv = $('[data-next]', root);
    pv && pv.addEventListener('click', function () { go(cur - 1); });
    nv && nv.addEventListener('click', function () { go(cur + 1); });
    root.addEventListener('mouseenter', function () { clearTimeout(timer); });
    root.addEventListener('mouseleave', restart);
    /* drag / swipe */
    var sx = null, moved = false;
    stage.addEventListener('pointerdown', function (e) { sx = e.clientX; moved = false; });
    w.addEventListener('pointerup', function (e) {
      if (sx === null) return; var dx = e.clientX - sx; sx = null;
      if (Math.abs(dx) > 40) { moved = true; go(cur + ((dx < 0) !== RTL ? 1 : -1)); setTimeout(function () { moved = false; }, 50); }
    });
    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') go(cur + (RTL ? -1 : 1)); if (e.key === 'ArrowLeft') go(cur + (RTL ? 1 : -1));
    });
    w.addEventListener('resize', layout);
    layout(); restart();
  });

  /* ---------- lightbox ---------- */
  var lb = $('.lightbox'), lbImg = lb && $('img', lb), lbCap = lb && $('figcaption', lb), lbList = [], lbI = 0;
  function showLB() { var it = lbList[lbI]; if (!it) return; lbImg.src = it.src; lbImg.alt = it.cap || ''; lbCap.textContent = it.cap || ''; }
  function openLB(list, i) { if (!lb) return; lbList = list; lbI = i; showLB(); lb.classList.add('open'); lb.setAttribute('aria-hidden', 'false'); }
  function closeLB() { if (lb && lb.classList.contains('open')) { lb.classList.remove('open'); lb.setAttribute('aria-hidden', 'true'); } }
  if (lb) {
    $('.lb-close', lb).addEventListener('click', closeLB);
    $('.lb-prev', lb).addEventListener('click', function () { lbI = (lbI - 1 + lbList.length) % lbList.length; showLB(); });
    $('.lb-next', lb).addEventListener('click', function () { lbI = (lbI + 1) % lbList.length; showLB(); });
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLB(); });
  }
  $$('[data-gallery]').forEach(function (g) {
    var links = $$('a', g);
    links.forEach(function (a, i) {
      a.addEventListener('click', function (e) { e.preventDefault(); openLB(links.map(function (x) { var im = $('img', x); return { src: x.getAttribute('href'), cap: im ? im.alt : '' }; }), i); });
    });
  });

  /* ---------- tilt cards ---------- */
  if (!reduce && w.matchMedia('(hover:hover)').matches) {
    $$('[data-tilt]').forEach(function (c) {
      c.addEventListener('pointermove', function (e) {
        var r = c.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        c.style.transform = 'rotateX(' + (-y * 10).toFixed(2) + 'deg) rotateY(' + (x * 12).toFixed(2) + 'deg) translateY(-4px)';
      });
      c.addEventListener('pointerleave', function () { c.style.transform = ''; });
    });
  }

  /* ---------- academy: filter + search ---------- */
  var acad = $('[data-academy]');
  if (acad) {
    var tabs = $$('.tab', acad), input = $('.search input', acad), cats = $$('.course-cat', acad), empty = $('.empty', acad), active = 'all';
    var norm = function (s) { return (s || '').toLowerCase().replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي'); };
    var apply = function () {
      var q = norm(input ? input.value.trim() : ''), any = false;
      cats.forEach(function (cat) {
        var catOn = active === 'all' || cat.getAttribute('data-cat') === active, catAny = false;
        $$('.course-group', cat).forEach(function (g) {
          var gAny = false;
          $$('.course', g).forEach(function (c) {
            var ok = catOn && (!q || norm(c.getAttribute('data-q')).indexOf(q) > -1);
            c.classList.toggle('is-hidden', !ok); if (ok) gAny = true;
          });
          g.classList.toggle('is-hidden', !gAny); if (gAny) catAny = true;
        });
        cat.classList.toggle('is-hidden', !catAny); if (catAny) any = true;
      });
      if (empty) empty.classList.toggle('show', !any);
    };
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        active = t.getAttribute('data-filter'); tabs.forEach(function (x) { x.classList.toggle('is-active', x === t); x.setAttribute('aria-selected', x === t); }); apply();
      });
    });
    if (input) input.addEventListener('input', apply);
    if (location.hash) { var t0 = $('.tab[data-filter="' + location.hash.slice(1) + '"]', acad); t0 && t0.click(); }
  }

  /* ---------- inquiry forms → WhatsApp ---------- */
  $$('form[data-inquiry]').forEach(function (f) {
    var ok = f.parentElement.querySelector('.form-ok'), err = $('.form__err', f), wa = f.getAttribute('data-wa');
    var pre = new URLSearchParams(location.search).get('service');
    if (pre && f.service) { f.service.value = pre; }
    var server = f.getAttribute('data-mode') === 'server';
    var v = function (n) { return f[n] ? f[n].value.trim() : ''; };
    var valid = function () { var good = !!(v('name') && v('phone') && v('message')); if (err) err.classList.toggle('show', !good); return good; };
    var build = function () {
      var L = f.getAttribute('data-labels').split('|');
      return ['CEETA — ' + L[6], '', L[0] + ': ' + v('name'), L[1] + ': ' + (v('company') || '-'), L[2] + ': ' + (v('email') || '-'), L[3] + ': ' + v('phone'), L[4] + ': ' + (v('service') || '-'), '', L[5] + ':', v('message')].join('\n');
    };
    var waBtn = $('[data-wa-send]', f);
    if (waBtn) waBtn.addEventListener('click', function () {
      if (!valid()) return;
      w.open('https://wa.me/' + wa + '?text=' + encodeURIComponent(build()), '_blank', 'noopener');
    });
    f.addEventListener('submit', function (e) {
      if (server) { if (!valid()) e.preventDefault(); return; }
      e.preventDefault();
      if (!valid()) return;
      var msg = build();
      if (ok) {
        $('pre', ok).textContent = msg;
        $('[data-wa-link]', ok).setAttribute('href', 'https://wa.me/' + wa + '?text=' + encodeURIComponent(msg));
        ok.classList.add('show'); ok.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
      }
    });
    if (ok) {
      var cp = $('[data-copy]', ok);
      cp && cp.addEventListener('click', function () {
        var txt = $('pre', ok).textContent;
        try { navigator.clipboard.writeText(txt).then(function () { cp.querySelector('span').textContent = cp.getAttribute('data-done'); }).catch(function () {}); } catch (x) {}
      });
    }
  });

  /* ---------- year ---------- */
  $$('[data-year]').forEach(function (el) { el.textContent = new Date().getFullYear(); });
})();
