(function () {
  var MSG = {
    en: {
      sending: 'Sending…',
      ok: 'Thank you! We have received your message and will get back to you soon.',
      invalid: 'Please check the highlighted fields and try again.',
      rate: 'Too many submissions from your connection. Please try again later.',
      server: 'Sorry, something went wrong on our side. Please try again later or call us.',
      network: 'We could not reach the server. Please check your connection and try again.'
    },
    fr: {
      sending: 'Envoi en cours…',
      ok: 'Merci ! Nous avons bien reçu votre message et vous répondrons rapidement.',
      invalid: 'Veuillez vérifier les champs en surbrillance puis réessayer.',
      rate: 'Trop d’envois depuis votre connexion. Veuillez réessayer plus tard.',
      server: 'Désolé, une erreur est survenue de notre côté. Veuillez réessayer plus tard ou nous appeler.',
      network: 'Impossible de joindre le serveur. Vérifiez votre connexion puis réessayez.'
    }
  };

  function lang() {
    return document.body.classList.contains('lang-fr') ? 'fr' : 'en';
  }

  function show(form, key, kind) {
    var box = form.querySelector('.form-status');
    box.dataset.key = key;
    box.className = 'form-status ' + kind;
    box.textContent = MSG[lang()][key];
  }

  document.addEventListener('ccf-lang', function () {
    document.querySelectorAll('.form-status[data-key]').forEach(function (box) {
      box.textContent = MSG[lang()][box.dataset.key];
    });
  });

  document.querySelectorAll('form[data-ccf-form]').forEach(function (form) {
    form.noValidate = true;
    var button = form.querySelector('button[type=submit]');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });

      var data = new FormData(form);
      data.set('lang', lang());
      button.disabled = true;
      show(form, 'sending', 'busy');

      fetch('api/submit.php', { method: 'POST', body: data, headers: { Accept: 'application/json' } })
        .then(function (res) {
          return res.json().catch(function () { return { ok: false, error: 'server' }; })
            .then(function (body) { return { status: res.status, body: body }; });
        })
        .then(function (r) {
          if (r.body.ok) {
            form.reset();
            prefill(form);
            show(form, 'ok', 'ok');
            return;
          }
          if (r.body.error === 'invalid' && r.body.fields) {
            var first = null;
            r.body.fields.forEach(function (name) {
              var el = form.querySelector('[name="' + name + '"]');
              if (el) {
                el.setAttribute('aria-invalid', 'true');
                first = first || el;
              }
            });
            if (first) first.focus();
            show(form, 'invalid', 'error');
          } else if (r.body.error === 'rate') {
            show(form, 'rate', 'error');
          } else {
            show(form, 'server', 'error');
          }
        })
        .catch(function () { show(form, 'network', 'error'); })
        .then(function () { button.disabled = false; });
    });
  });

  // ?project=chemotherapy (from the Projects page) pre-selects the project in donation/partner forms.
  function prefill(form) {
    var project = new URLSearchParams(location.search).get('project');
    var sel = form.querySelector('select[name="project"], select[name="interest"]');
    if (project && sel && sel.querySelector('option[value="' + project + '"]')) sel.value = project;
  }
  document.querySelectorAll('form[data-ccf-form]').forEach(prefill);
})();
