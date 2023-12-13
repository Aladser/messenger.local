//----- ПРОВЕРКА ПОЛЕЙ ВВОДА ПРИ РЕГИСТРАЦИИ -----
const regErrorPrg = document.querySelector('#reg-error');
const password1Input = document.querySelector('#reg-form__password1-input');
const password2Input = document.querySelector('#reg-form__password2-input');

/** форма регистрации */
const regForm = document.querySelector('#reg-form');

/***** Отправка запроса на регистрацию *****/
regForm.addEventListener('submit', function (e) {
    e.preventDefault();
    let form = new FormData(this);
    // Список пар ключ/значение
    fetch('user/store', {method: 'POST', body: form}).then(response => response.text()).then(data => {
        regErrorPrg.classList.remove('d-none');
        try {
            data = JSON.parse(data);
            console.log(data);
            if (data['result'] === 'user_exists') {
                regErrorPrg.innerHTML = 'пользователь уже существует';
                regErrorPrg.classList.remove('text-success');
                regErrorPrg.classList.add('text-danger');
                password1Input.value = '';
                password2Input.value = '';
            } else if (data['result'] === 'add_user_error') {
                regErrorPrg.innerHTML = 'серверная ошибка создания пользователя';
                regErrorPrg.classList.remove('text-success');
                regErrorPrg.classList.add('text-danger');
                password1Input.value = '';
                password2Input.value = '';
            } else {
                regErrorPrg.innerHTML = 'Пользователь создан. Подтвердите ваши регистрационные данные по ссылке, указанной в письме, направленном на вашу почту';
                regErrorPrg.classList.remove('text-danger');
                regErrorPrg.classList.add('text-success');
                e.target.reset(); // сбрасывает значения всех элементов в форме
            }
        } catch(err) {
            regErrorPrg.innerHTML = data;
        }
    });
});
