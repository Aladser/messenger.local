// поле ошибок
const loginErrorPrg = document.querySelector('#login-error');

//***** авторизация и аутентификация *****/
document.querySelector('#login-form').addEventListener('submit', function (e) {
    e.preventDefault();
    let form = new FormData(this);

    fetch('user/auth', {method: 'POST', body: form}).then(response => response.text()).then(data => {
        try {
            JSON.parse(data);
            window.open('/dialogs', '_self');
        } catch (SyntaxError) {
            loginErrorPrg.classList.remove('d-none');
            loginErrorPrg.innerHTML = data;
        }
    });
});
