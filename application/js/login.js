// поле ввода почты
const emailInput = document.querySelector('#login-form__email-input');
// поле ввода пароля
const passwordInput = document.querySelector('#login-form__password-input');
// кнопка входа
const loginBtn = document.querySelector('#login-form__login-btn');
// поле ошибок
const loginErrorPrg = document.querySelector('#login-error');
// элемент CSRF-токена
const inputCsrf = document.querySelector('#input-csrf');

// проверка ввода почты
emailInput.addEventListener('input', function () {
    this.style.outlineColor = validateEmail(this.value) ? 'black' : 'red';
    loginBtn.disabled = !(validateEmail(this.value) && passwordInput.value !== '');
});

// проверка ввода пароля
passwordInput.addEventListener('input', function () {
    this.style.outlineColor = this.value !== '';
    loginBtn.disabled = !(validateEmail(emailInput.value) && this.value !== '');
});


//***** авторизация и аутентификация *****/
document.querySelector('#login-form').addEventListener('submit', function (e) {
    e.preventDefault();
    let form = new FormData(this);
    form.append('CSRF', inputCsrf.value);

    fetch('user/login', {method: 'POST', body: form}).then(response => response.text()).then(data => {
        try {
            JSON.parse(data);
            window.open('/chat', '_self');
        } catch (SyntaxError) {
            loginErrorPrg.classList.remove('d-none');
            loginErrorPrg.innerHTML = data;
        }
    });
});
