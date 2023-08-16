//----- ПРОВЕРКА ПОЛЕЙ ВВОДА ПРИ РЕГИСТРАЦИИ -----
// для уменьшения числа запросов к серверу валидация происходит на клиенте
const regBtn = document.querySelector('#reg-form__reg-btn');
const regErrorPrg = document.querySelector('#reg-error');

const emailInput = document.querySelector('#reg-form__email-input');
const password1Input = document.querySelector('#reg-form__password1-input');
const password2Input = document.querySelector('#reg-form__password2-input');

const emailClue = document.querySelector('#reg-form__emai-clue');
const password1Clue = document.querySelector('#reg-form__password1-clue');
const password2Clue = document.querySelector('#reg-form__password2-clue');
/** форма регистрации */
const regForm = document.querySelector('#reg-form');
/** элемент CSRF-токена */
const inputCsrf = document.querySelector('#input-csrf');

//***** событие клика поля ввода данных *****/
function clickInputElement(input, clue, isPassword)
{
    regErrorPrg.classList.add('d-none');
    // убирание выделения
    emailClue.classList.remove('input-clue--active');
    password1Clue.classList.remove('input-clue--active');
    password2Clue.classList.remove('input-clue--active');
    // валидация данных
    let isValid = isPassword ? validatePassword(input.value) : validateEmail(input.value);
    if (!isValid) {
        clue.classList.add('input-clue--active');
    }
}

emailInput.onclick = function () {
    clickInputElement(this, emailClue, false);
    password2Input.value = '';
    regBtn.disabled = true;
};
password1Input.onclick = function () {
    clickInputElement(this, password1Clue, true);
    password2Input.value = '';
    regBtn.disabled = true;
};
password2Input.onclick = function () {
    clickInputElement(this, password2Clue, true)
};


//***** событие ввода данных *****/
function inputData(input, clue, isPassword)
{
    // валидация данных
    let isValid = isPassword ? validatePassword(input.value) : validateEmail(input.value);
    if (isValid) {
        input.style.outlineColor = 'black';
        clue.classList.remove('input-clue--active');
    } else {
        input.style.outlineColor = 'red';
        clue.classList.add('input-clue--active');
    }
    // проверка доступности кнопки
    let regBtnEnabled = validateEmail(emailInput.value)
        && validatePassword(password1Input.value)
        && validatePassword(password2Input.value)
        && password1Input.value === password2Input.value;
    regBtn.disabled = !regBtnEnabled;
}

emailInput.addEventListener('input', function () {
    inputData(this, emailClue, false);
});
password1Input.addEventListener('input', function () {
    inputData(this, password1Clue, true);
});
password2Input.addEventListener('input', function () {
    inputData(this, password2Clue, true);
});


/***** Отправка запроса на регистрацию *****/
regForm.addEventListener('submit', function (e) {
    e.preventDefault();
    let form = new FormData(this);
    form.append('CSRF', inputCsrf.value);
    // Список пар ключ/значение
    fetch('user/register', {method: 'POST', body: form}).then(response => response.text()).then(data => {
        regErrorPrg.classList.remove('d-none');
        try {
            data = JSON.parse(data);
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
