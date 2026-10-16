class ValidadorDatos {
  constructor() {
    this.usuarioInput = document.getElementById('usuario') || document.getElementById('nombre');
    this.passwordInput = document.getElementById('password');
    this.rolSelect = document.getElementById('rol');
    this.btnSubmit = document.getElementById('btnSubmit');
    this.form = document.querySelector('form');

    this.contieneLetraONumero = /[a-zA-Z0-9]/;
    this.iniciarEventos();
    this.validarCampos();
  }

  validarCampos() {
    const usuarioValido = this.usuarioInput ? this.contieneLetraONumero.test(this.usuarioInput.value.trim()) : true;
    const passwordValida = this.passwordInput ? this.contieneLetraONumero.test(this.passwordInput.value.trim()) : true;
    const rolValido = this.rolSelect ? (this.rolSelect.value.trim() !== '') : true;
    const captchaValido = typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length > 0;

    if (this.btnSubmit) {
      this.btnSubmit.disabled = !(usuarioValido && passwordValida && rolValido && captchaValido);
    }
  }

  onRecaptchaSuccess() {
    this.validarCampos();
  }

  onRecaptchaExpired() {
    if (this.btnSubmit) {
      this.btnSubmit.disabled = true;
    }
  }

  iniciarEventos() {
    if (this.usuarioInput) {
      this.usuarioInput.addEventListener('input', () => this.validarCampos());
    }
    if (this.passwordInput) {
      this.passwordInput.addEventListener('input', () => this.validarCampos());
    }
    if (this.rolSelect) {
      this.rolSelect.addEventListener('change', () => this.validarCampos());
    }

    if (this.form) {
      this.form.addEventListener('submit', (event) => {
        const usuarioValido = this.usuarioInput ? this.contieneLetraONumero.test(this.usuarioInput.value.trim()) : true;
        const passwordValida = this.passwordInput ? this.contieneLetraONumero.test(this.passwordInput.value.trim()) : true;
        const rolValido = this.rolSelect ? (this.rolSelect.value.trim() !== '') : true;
        const captchaValido = typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length > 0;

        if (!usuarioValido || !passwordValida || !rolValido || !captchaValido) {
          event.preventDefault();
          if (this.btnSubmit) {
            this.btnSubmit.disabled = true;
          }
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