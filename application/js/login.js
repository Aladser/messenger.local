// поле ввода почты
const emailInput = document.querySelector('#login-form__email-input');
// поле ввода пароля
const passwordInput = document.querySelector('#login-form__password-input');
// кнопка входа
const loginBtn = document.querySelector('#login-form__login-btn');
// поле ошибок
const loginErrorPrg = document.querySelector('#login-error');
// инпут CSRF-токена
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
    form.append('csrf', inputCsrf.value);

    fetch('/login', {method: 'POST', body: form}).then(response => response.json()).then(data => {
        if (data['result'] === 'login_user') {
            // вход
            window.open('/chats', '_self')
        } else {
            // ошибки входа
            loginErrorPrg.classList.remove('d-none');
            if (data['result'] === 'login_user_wrong_password') {
                loginErrorPrg.innerHTML = 'Неверный пароль';
            } else if (data['result'] === 'wrong_url') {
                loginErrorPrg.innerHTML = 'Подмена url-адреса запроса';
            } else {
                loginErrorPrg.innerHTML = 'Пользователь не существует';
            }
        }
    });
});