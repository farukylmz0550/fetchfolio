(function () {
  'use strict';
  var themeKey = 'fetchfolio-theme';
  var consentKey = 'fetchfolio-consent';
  var root = document.documentElement;

  function readConsent() {
    var saved = null;
    try { saved = localStorage.getItem(consentKey); } catch (e) {}
    if (saved === 'accept' || saved === 'reject' || saved === 'necessary') return saved;
    var match = document.cookie.match(new RegExp('(?:^|; )' + consentKey + '=([^;]*)'));
    if (!match) return null;
    try { saved = decodeURIComponent(match[1]); } catch (e) { saved = match[1]; }
    return saved === 'accept' || saved === 'reject' || saved === 'necessary' ? saved : null;
  }

  function storeConsent(choice) {
    try { localStorage.setItem(consentKey, choice); } catch (e) {}
    var secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = consentKey + '=' + encodeURIComponent(choice) + '; Max-Age=31536000; Path=/; SameSite=Lax' + secure;
  }

  function setTheme(theme, save) {
    root.dataset.theme = theme;
    var button = document.querySelector('[data-ff-theme-toggle]');
    if (!button) return;
    var light = theme === 'light';
    button.setAttribute('aria-pressed', String(light));
    button.setAttribute('aria-label', light ? 'Switch to dark theme' : 'Switch to light theme');
    var label = button.querySelector('.theme-toggle-label');
    var icon = button.querySelector('[aria-hidden="true"]');
    if (label) label.textContent = light ? 'Dark' : 'Light';
    if (icon) icon.textContent = light ? '☾' : '☼';
    if (save) { try { localStorage.setItem(themeKey, theme); } catch (e) {} }
  }

  function applyStatisticsConsent(choice) {
    if (typeof window.wp_set_consent === 'function') {
      window.wp_set_consent('functional', 'allow');
      window.wp_set_consent('statistics', choice === 'accept' ? 'allow' : 'deny');
    }
    window.dispatchEvent(new CustomEvent('fetchfolio:consent', {
      detail: { functional: true, statistics: choice === 'accept' }
    }));
  }

  function initConsent() {
    var banner = document.querySelector('[data-ff-consent-banner]');
    if (!banner) return;
    var saved = readConsent();
    function saveChoice(choice) {
      if (choice !== 'accept' && choice !== 'reject' && choice !== 'necessary') return;
      storeConsent(choice);
      applyStatisticsConsent(choice);
      banner.hidden = true;
    }

    if (saved === 'accept' || saved === 'reject' || saved === 'necessary') {
      applyStatisticsConsent(saved);
      banner.hidden = true;
    } else {
      banner.hidden = false;
    }
    banner.addEventListener('click', function (event) {
      var choice = event.target.closest('[data-ff-consent]');
      if (choice) saveChoice(choice.dataset.ffConsent);
    });
  }

  document.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-ff-theme-toggle]');
    if (toggle) setTheme(root.dataset.theme === 'light' ? 'dark' : 'light', true);
  });

  function initialize() {
    setTheme(root.dataset.theme || 'dark', false);
    initConsent();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
  } else {
    initialize();
  }
}());
