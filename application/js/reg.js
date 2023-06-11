//----- ПРОВЕРКА ПОЛЕЙ ВВОДА ПРИ РЕГИСТРАЦИИ -----
const regBtn = document.querySelector('#reg-form__reg-btn');
const regErrorPrg = document.querySelector('#reg-error');

const emailInput = document.querySelector('#reg-form__email-input');
const password1Input = document.querySelector('#reg-form__password1-input');
const password2Input = document.querySelector('#reg-form__password2-input');

const emailClue = document.querySelector('#reg-form__emai-clue');
const password1Clue = document.querySelector('#reg-form__password1-clue');
const password2Clue = document.querySelector('#reg-form__password2-clue');

document.querySelector('#reg-form__back-btn').onclick = () => window.open('/main', '_self'); // кнопка назад

// валидация почты
function validateEmail(email){
    let emailSymbols = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    return emailSymbols.test(email);
}

// валидация пароля
function validatePassword(password){
    let passwSymbols = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,1000}$/;
    return passwSymbols.test(password);
}


//***** событие клика поля ввода данных *****/
function clickInputElement(input, clue, isPassword){
    if(input.value === '') return;
    emailClue.classList.remove('input-clue--active');
    password1Clue.classList.remove('input-clue--active');
    password2Clue.classList.remove('input-clue--active');
    let clickRslt = isPassword ? validatePassword(input.value) : validateEmail(input.value);
    if(!clickRslt){
        clue.classList.add('input-clue--active');
    }
}

emailInput.onclick = function(){
    clickInputElement(this, emailClue, false);
    password2Input.value = '';
    regBtn.disabled = true;
};
password1Input.onclick = function(){
    clickInputElement(this, password1Clue, true);
    password2Input.value = '';
    regBtn.disabled = true;
};
password2Input.onclick = function(){clickInputElement(this, password2Clue, true)};


//***** событие ввода данных *****/
function inputData(input, clue, isPassword){
    let inputRslt = isPassword ? validatePassword(input.value) : validateEmail(input.value);
    if(inputRslt){
        input.style.outlineColor = 'black';
        clue.classList.remove('input-clue--active');
    }
    else{
        input.style.outlineColor = 'red';
        clue.classList.add('input-clue--active');
    }
    regBtnEnabled = validateEmail(emailInput.value) && validatePassword(password1Input.value) && validatePassword(password2Input.value) && password1Input.value===password2Input.value;
    regBtn.disabled = !regBtnEnabled;
}


//***** проверка существования пользователя и регистрация *****/
document.querySelector('#reg-form').addEventListener('submit', function(e){
    e.preventDefault();
    let form = new FormData(this);
    e.target.reset(); // сбрасывает значения всех элементов в форме
    fetch('/application/models/reg_model.php', {method: 'POST', body: form}).then(response => response.text()).then(data => {
        regErrorPrg.classList.remove('hidden');
        regErrorPrg.innerHTML = data;
    });
});

emailInput.addEventListener('input', function(){inputData(this, emailClue, false);});
password1Input.addEventListener('input', function(){inputData(this, password1Clue, true);});
password2Input.addEventListener('input', function(){inputData(this, password2Clue, true);});