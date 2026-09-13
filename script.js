(function () {
  var STORAGE_KEY = 'ccf-lang';

  function applyLang(lang) {
    document.body.classList.toggle('lang-fr', lang === 'fr');

    document.querySelectorAll('[data-en]').forEach(function (el) {
      var text = lang === 'fr' ? el.getAttribute('data-fr') : el.getAttribute('data-en');
      if (text !== null) el.textContent = text;
    });

    document.querySelectorAll('.lang button').forEach(function (btn) {
      btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
    });

    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}
  }

  var initialLang = 'en';
  try {
    var saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'en' || saved === 'fr') initialLang = saved;
  } catch (e) {}

  applyLang(initialLang);

  document.querySelectorAll('.lang button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      applyLang(btn.getAttribute('data-lang'));
    });
  });

  document.querySelectorAll('[data-year]').forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });
})();
