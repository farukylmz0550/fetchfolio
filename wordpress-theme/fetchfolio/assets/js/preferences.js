(function () {
  'use strict';
  var themeKey = 'fetchfolio-theme';
  var consentKey = 'fetchfolio-consent';
  var root = document.documentElement;

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
    var settings = document.querySelector('[data-ff-consent-open]');
    if (!banner) return;
    var saved = null;
    try { saved = localStorage.getItem(consentKey); } catch (e) {}

    function showSettings() {
      banner.hidden = false;
      if (settings) settings.hidden = true;
      var first = banner.querySelector('[data-ff-consent]');
      if (first) first.focus();
    }
    function saveChoice(choice) {
      try { localStorage.setItem(consentKey, choice); } catch (e) {}
      applyStatisticsConsent(choice);
      banner.hidden = true;
      if (settings) settings.hidden = false;
    }

    if (saved === 'accept' || saved === 'reject' || saved === 'necessary') {
      applyStatisticsConsent(saved);
      banner.hidden = true;
      if (settings) settings.hidden = false;
    } else {
      banner.hidden = false;
    }
    banner.addEventListener('click', function (event) {
      var choice = event.target.closest('[data-ff-consent]');
      if (choice) saveChoice(choice.dataset.ffConsent);
    });
    if (settings) settings.addEventListener('click', showSettings);
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
