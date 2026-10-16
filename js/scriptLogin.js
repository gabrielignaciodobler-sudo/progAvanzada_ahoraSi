
class ValidadorDatos {
  constructor() {
    this.usuarioInput = document.getElementById('usuario');
    this.passwordInput = document.getElementById('password');
    this.btnSubmit = document.getElementById('btnSubmit');
    this.form = document.querySelector('form');

    this.contieneLetraONumero = /[a-zA-Z0-9]/;
    this.iniciarEventos();
    this.validarCampos();
  }

  validarCampos() {
    const usuarioValido = this.contieneLetraONumero.test(this.usuarioInput.value.trim());
    const passwordValida = this.contieneLetraONumero.test(this.passwordInput.value.trim());
    const captchaValido = typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length > 0;

    this.btnSubmit.disabled = !(usuarioValido && passwordValida && captchaValido);
  }

  onRecaptchaSuccess() {
    this.validarCampos();
  }

  onRecaptchaExpired() {
    this.btnSubmit.disabled = true;
  }

  iniciarEventos() {
    this.usuarioInput.addEventListener('input', () => this.validarCampos());
    this.passwordInput.addEventListener('input', () => this.validarCampos());

    if (this.form) {
      this.form.addEventListener('submit', (event) => {
        const captchaValido = typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length > 0;

        if (!captchaValido) {
          event.preventDefault();
          this.btnSubmit.disabled = true;
          return;
        }
      });
    }

    window.onRecaptchaSuccess = () => this.onRecaptchaSuccess();
    window.onRecaptchaExpired = () => this.onRecaptchaExpired();
  }
}


document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  if (form) {
    new ValidadorDatos();
  }
});