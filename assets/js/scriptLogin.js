(() => {
  const form = document.querySelector('form[action*="c=auth"]');
  const submitButton = document.getElementById('btnSubmit');

  if (!form || !submitButton) {
    return;
  }

  window.onRecaptchaSuccess = () => {
    submitButton.disabled = false;
  };

  window.onRecaptchaExpired = () => {
    submitButton.disabled = true;
  };

  if (typeof window.JustValidate !== 'function') {
    console.error('No se pudo cargar JustValidate; se usará la validación nativa del navegador.');
    form.noValidate = false;
    return;
  }

  const validator = new window.JustValidate(form, {
    errorLabelCssClass: 'error-message',
    errorLabelStyle: {},
    focusInvalidField: true,
    submitFormAutomatically: true,
    validateBeforeSubmitting: true,
  });

  validator
    .addField('#usuario', [
      {
        rule: 'required',
        errorMessage: 'Ingresá tu nombre de usuario.',
      },
      {
        rule: 'maxLength',
        value: 50,
        errorMessage: 'El nombre de usuario no puede superar los 50 caracteres.',
      },
    ])
    .addField('#password', [
      {
        rule: 'required',
        errorMessage: 'Ingresá tu contraseña.',
      },
    ]);

  form.noValidate = true;
})();
