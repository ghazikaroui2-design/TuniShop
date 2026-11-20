/* =====================================================================
   TuniShop — Couche d'interaction progressive (sans dépendance)
   Chargée avec defer depuis le layout : aucune ligne inline, aucun
   attribut on*, aucune donnée bancaire transmise (RG-07).
   Le préfixe est fourni par data-base-url sur <body>.
   CSP : script-src 'self' ; connect-src 'self'.
   ===================================================================== */
(function () {
  'use strict';

  var body = document.body;
  var baseUrl = (body && body.dataset.baseUrl) || '';
  var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /** Retourne l'URL absolue d'une ressource locale. */
  function url(path) {
    return baseUrl.replace(/\/$/, '') + path;
  }

  /** Crée un élément avec texte sûr (jamais d'innerHTML avec des données). */
  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) { node.className = className; }
    if (text !== undefined) { node.textContent = String(text); }
    return node;
  }

  /* ------------------------------------------------------------------
     1. Navigation responsive
     ------------------------------------------------------------------ */
  function initNavigation() {
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.getElementById('primary-nav');
    if (!toggle || !nav) { return; }

    var open = false;
    function setOpen(next) {
      open = next;
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    }

    toggle.addEventListener('click', function () { setOpen(!open); });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && open) {
        setOpen(false);
        toggle.focus();
      }
    });

    document.addEventListener('click', function (event) {
      if (open && !nav.contains(event.target) && !toggle.contains(event.target)) {
        setOpen(false);
      }
    });

    // Marque le lien courant pour les lecteurs d'écran.
    var path = window.location.pathname.replace(/\/$/, '') || '/';
    var links = nav.querySelectorAll('a[href]');
    Array.prototype.forEach.call(links, function (link) {
      var href = link.getAttribute('href').replace(/\/$/, '') || '/';
      if (href === path || (href !== '/' && path.indexOf(href) === 0)) {
        link.setAttribute('aria-current', 'page');
      }
    });
  }

  /* ------------------------------------------------------------------
     2. Notifications (toasts) — doublon accessible des messages flash
     ------------------------------------------------------------------ */
  function initToasts() {
    var stack = document.getElementById('toast-stack');
    if (!stack) { return; }

    function dismiss(toast) {
      if (!toast.parentNode) { return; }
      toast.style.opacity = '0';
      window.setTimeout(function () {
        if (toast.parentNode) { toast.parentNode.removeChild(toast); }
      }, reducedMotion ? 0 : 180);
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-toast]'), function (toast) {
      var kind = toast.dataset.toast || 'info';
      // Sans JavaScript, l'élément reste une alerte visible dans le contenu.
      // Avec JavaScript, on le déplace dans la pile de notifications.
      toast.className = 'toast ' + kind;
      toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');
      toast.classList.remove('alert', 'ok', 'err', 'error', 'success', 'info');

      var icon = el('span', 'toast-icon', kind === 'success' ? '✓' : (kind === 'error' ? '!' : 'i'));
      toast.insertBefore(icon, toast.firstChild);

      var close = el('button', 'toast-close', '×');
      close.type = 'button';
      close.setAttribute('aria-label', 'Fermer la notification');
      close.addEventListener('click', function () { dismiss(toast); });
      toast.appendChild(close);
      stack.appendChild(toast);

      if (toast.dataset.toastKeep !== 'true') {
        window.setTimeout(function () { dismiss(toast); }, 5000);
      }
    });
  }

  /* ------------------------------------------------------------------
     3. Quantités : + / - sans JavaScript, le formulaire POST reste le
        chemin nominal. Ici, simple amélioration de confort.
     ------------------------------------------------------------------ */
  function initQuantityControls() {
    document.addEventListener('click', function (event) {
      var button = event.target.closest('[data-qty-step]');
      if (!button) { return; }

      var wrapper = button.closest('[data-qty]') || button.parentNode;
      var input = wrapper.querySelector('input[type="number"]');
      if (!input) { return; }

      var step = parseInt(button.dataset.qtyStep, 10) || 1;
      var min = parseInt(input.min, 10) || 1;
      var max = parseInt(input.max, 10) || 99;
      var current = parseInt(input.value, 10);
      if (isNaN(current)) { current = min; }

      var next = Math.min(max, Math.max(min, current + step));
      input.value = String(next);
      input.dispatchEvent(new Event('input', { bubbles: true }));
      button.blur();
    });
  }

  /* ------------------------------------------------------------------
     4. Panneau de filtres (mobile)
     ------------------------------------------------------------------ */
  function initFilterPanel() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-filter-toggle]'), function (button) {
      var panel = document.getElementById(button.getAttribute('aria-controls'));
      if (!panel) { return; }

      button.addEventListener('click', function () {
        var willOpen = panel.hasAttribute('hidden');
        if (willOpen) { panel.removeAttribute('hidden'); } else { panel.setAttribute('hidden', ''); }
        button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        button.setAttribute('aria-label', willOpen ? 'Masquer les filtres' : 'Afficher les filtres');
      });
    });
  }

  /* ------------------------------------------------------------------
     5. Recherche instantanée via GET /api/produits (fetch, même origine)
     ------------------------------------------------------------------ */
  function initLiveSearch() {
    var input = document.querySelector('[data-live-search]');
    if (!input) { return; }

    var list = document.getElementById(input.getAttribute('aria-controls') || 'search-results');
    if (!list) { return; }

    var controller = null;
    var timer = null;

    function clear() {
      list.textContent = '';
      list.hidden = true;
      input.setAttribute('aria-expanded', 'false');
    }

    function render(products) {
      list.textContent = '';
      if (!products.length) {
        var empty = el('li', 'empty', 'Aucun produit trouvé. Appuyez sur Entrée pour rechercher.');
        list.appendChild(empty);
      } else {
        products.slice(0, 6).forEach(function (product) {
          var item = el('li');
          var link = el('a');
          link.href = url('/produit/' + encodeURIComponent(String(product.slug || '')));
          link.appendChild(el('span', '', product.name || 'Produit'));
          link.appendChild(el('strong', 'price', Number(product.price_ttc || 0).toFixed(2).replace('.', ',') + ' DT'));
          item.appendChild(link);
          list.appendChild(item);
        });
      }
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }

    function search(query) {
      if (controller) { controller.abort(); }
      controller = new AbortController();

      fetch(url('/api/produits?q=' + encodeURIComponent(query)), {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'fetch' },
        signal: controller.signal,
        credentials: 'same-origin'
      })
        .then(function (response) {
          if (!response.ok) { throw new Error('HTTP ' + response.status); }
          return response.json();
        })
        .then(function (payload) { render(Array.isArray(payload.products) ? payload.products : []); })
        .catch(function (error) {
          if (error.name !== 'AbortError') { clear(); }
        });
    }

    input.addEventListener('input', function () {
      var query = input.value.trim();
      window.clearTimeout(timer);
      if (query.length < 2) { clear(); return; }
      timer = window.setTimeout(function () { search(query); }, 220);
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') { clear(); input.blur(); }
    });

    document.addEventListener('click', function (event) {
      if (!input.contains(event.target) && !list.contains(event.target)) { clear(); }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'ArrowDown' || list.hidden) { return; }
      var first = list.querySelector('a');
      if (first) { event.preventDefault(); first.focus(); }
    });
  }

  /* ------------------------------------------------------------------
     6. Carte de test : formatage local, aucun name, aucun envoi réseau
     ------------------------------------------------------------------ */
  function initSandboxCard() {
    var card = document.querySelector('[data-card-input]');
    if (card) {
      card.addEventListener('input', function () {
        var digits = card.value.replace(/\D/g, '').slice(0, 16);
        card.value = digits.replace(/(.{4})/g, '$1 ').trim();
        var hint = document.querySelector('[data-card-hint]');
        if (hint) {
          hint.textContent = digits === '4242424242424242'
            ? '✓ Carte de test TuniShop reconnue.'
            : (digits.length === 16 ? 'Carte non reconnue : utilisez 4242 4242 4242 4242.' : '');
        }
      });
    }

    var expiry = document.querySelector('[data-expiry-input]');
    if (expiry) {
      expiry.addEventListener('input', function () {
        var digits = expiry.value.replace(/\D/g, '').slice(0, 4);
        expiry.value = digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits;
      });
    }

    var cvc = document.querySelector('[data-cvc-input]');
    if (cvc) {
      cvc.addEventListener('input', function () {
        cvc.value = cvc.value.replace(/\D/g, '').slice(0, 4);
      });
    }
  }

  /* ------------------------------------------------------------------
     7. Indicateur de force du mot de passe (le serveur reste autorité)
     ------------------------------------------------------------------ */
  function initPasswordMeter() {
    var input = document.querySelector('[data-password-meter-input]');
    if (!input) { return; }

    var bar = document.querySelector('[data-password-meter-bar]');
    var label = document.querySelector('[data-password-meter-label]');
    var labels = ['Très faible', 'Faible', 'Moyen', 'Bon', 'Excellent'];

    function update() {
      var value = input.value;
      var score = 0;
      if (value.length >= 8) { score++; }
      if (value.length >= 12) { score++; }
      if (/[a-z]/.test(value) && /[A-Z]/.test(value)) { score++; }
      if (/[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value)) { score++; }
      score = Math.min(4, score);

      if (bar) {
        bar.style.width = (value ? (score + 1) * 20 : 0) + '%';
        bar.dataset.score = String(score);
      }
      if (label) { label.textContent = value ? labels[score + 1] : ''; }
    }

    input.addEventListener('input', update);
    update();
  }

  /* ------------------------------------------------------------------
     8. Onglets accessibles (fiche produit)
     ------------------------------------------------------------------ */
  function initTabs() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-tabs]'), function (group) {
      var tabs = Array.prototype.slice.call(group.querySelectorAll('[role="tab"]'));
      if (!tabs.length) { return; }

      function activate(tab, focus) {
        tabs.forEach(function (other) {
          var selected = other === tab;
          other.setAttribute('aria-selected', selected ? 'true' : 'false');
          other.tabIndex = selected ? 0 : -1;
          var panel = document.getElementById(other.getAttribute('aria-controls'));
          if (panel) { panel.hidden = !selected; }
        });
        if (focus) { tab.focus(); }
      }

      tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () { activate(tab, false); });
        tab.addEventListener('keydown', function (event) {
          var next = null;
          if (event.key === 'ArrowRight') { next = tabs[(index + 1) % tabs.length]; }
          if (event.key === 'ArrowLeft') { next = tabs[(index - 1 + tabs.length) % tabs.length]; }
          if (event.key === 'Home') { next = tabs[0]; }
          if (event.key === 'End') { next = tabs[tabs.length - 1]; }
          if (next) { event.preventDefault(); activate(next, true); }
        });
      });

      var initial = tabs.filter(function (tab) { return tab.getAttribute('aria-selected') === 'true'; })[0] || tabs[0];
      activate(initial, false);
    });
  }

  /* ------------------------------------------------------------------
     9. Confirmation des actions destructives
     ------------------------------------------------------------------ */
  function initConfirmations() {
    document.addEventListener('submit', function (event) {
      var form = event.target;
      var message = form.getAttribute('data-confirm');
      if (!message) { return; }
      if (!window.confirm(message)) { event.preventDefault(); }
    });
  }

  /* ------------------------------------------------------------------
     10. Tableaux scrollables accessibles au clavier
     ------------------------------------------------------------------ */
  function initScrollRegions() {
    Array.prototype.forEach.call(document.querySelectorAll('.table-scroll'), function (region) {
      if (!region.hasAttribute('tabindex')) {
        region.setAttribute('tabindex', '0');
        region.setAttribute('role', 'region');
        region.setAttribute('aria-label', 'Tableau défilant');
      }
    });
  }

  function init() {
    initNavigation();
    initToasts();
    initQuantityControls();
    initFilterPanel();
    initLiveSearch();
    initSandboxCard();
    initPasswordMeter();
    initTabs();
    initConfirmations();
    initScrollRegions();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
