const registerErrorPrg = document.querySelector('#reg-error');
const password1Input = document.querySelector('#reg-form__password1-input');
const password2Input = document.querySelector('#reg-form__password2-input');

/** форма регистрации */
const regForm = document.querySelector('#reg-form');

/*** Отправка запроса на регистрацию ***/
regForm.addEventListener('submit', function (e) {
    e.preventDefault();
    let form = new FormData(this);
    // Список пар ключ/значение
    fetch('user/store', {method: 'POST', body: form}).then(response => response.text()).then(data => {
        registerErrorPrg.classList.remove('d-none');
        try {
            data = JSON.parse(data);
            if (data['result'] === 'user_exists') {
                registerErrorPrg.innerHTML = 'пользователь уже существует';
                registerErrorPrg.classList.remove('text-success');
                registerErrorPrg.classList.add('text-danger');
                password1Input.value = '';
                password2Input.value = '';
            } else {
                registerErrorPrg.innerHTML = 'Пользователь создан. Подтвердите ваши регистрационные данные по ссылке, указанной в письме, направленном на вашу почту';
                registerErrorPrg.classList.remove('text-danger');
                registerErrorPrg.classList.add('text-success');
                e.target.reset(); // сбрасывает значения всех элементов в форме
            }
        } catch(err) {
            registerErrorPrg.innerHTML = data;
            console.log(err);
        }
    });
});
