const emailInput = document.querySelector('#login-form__email-input');
const passwordInput = document.querySelector('#login-form__password-input');
const loginBtn = document.querySelector('#login-form__login-btn');
const loginErrorPrg = document.querySelector('#login-error');

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
    // Список пар ключ/значение
    fetch('/login-user', {method: 'POST', body: form}).then(response => response.json()).then(data => {
        if (data['result'] === 'login_user') {
            window.open('/chats', '_self')
        } else if (data['result'] === 'login_user_wrong_password') {
            loginErrorPrg.classList.remove('d-none');
            loginErrorPrg.innerHTML = 'Неверный пароль';
        } else {
            loginErrorPrg.classList.remove('d-none');
            loginErrorPrg.innerHTML = 'Пользователь не существует';
        }
    });
});