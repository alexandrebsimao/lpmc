(function () {
  'use strict';

  function initConsentInterface() {
    const consent = window.ViaScripturaConsent;
    const banner = document.getElementById('cookie-consent-banner');
    const acceptBtn = document.getElementById('cookie-accept-btn');
    const rejectBtn = document.getElementById('cookie-reject-btn');
    const preferencesBtn = document.getElementById('cookie-preferences-btn');
    if (!consent || !banner || !acceptBtn || !rejectBtn) return;

    function showBanner() {
      banner.classList.add('active');
      banner.setAttribute('aria-hidden', 'false');
    }

    function hideBanner() {
      banner.classList.remove('active');
      banner.setAttribute('aria-hidden', 'true');
    }

    acceptBtn.addEventListener('click', () => {
      consent.accept();
      hideBanner();
    });

    rejectBtn.addEventListener('click', () => {
      consent.reject();
      hideBanner();
    });

    if (preferencesBtn) {
      preferencesBtn.addEventListener('click', () => {
        consent.clear();
        showBanner();
        acceptBtn.focus();
      });
    }

    if (!consent.getChoice()) showBanner();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConsentInterface);
  } else {
    initConsentInterface();
  }
})();
