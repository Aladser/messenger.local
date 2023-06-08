//----- ПРОВЕРКА ПОЛЕЙ ВВОДА ПРИ РЕГИСТРАЦИИ -----
const regBtn = document.querySelector('#reg-form__reg-btn');

const emailInput = document.querySelector('#reg-form__email-input');
const password1Input = document.querySelector('#reg-form__password1-input');
const password2Input = document.querySelector('#reg-form__password2-input');

const emailClue = document.querySelector('#reg-form__emai-clue');
const password1Clue = document.querySelector('#reg-form__password1-clue');
const password2Clue = document.querySelector('#reg-form__password2-clue');

// валидация почты
function validateEmail(email){
    let reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    if(reg.test(email) == false) {
       return false;
    }
    else 
        return true;
}

// валидация пароля
function validatePassword(password){
    let passw = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,20}$/;
    if(passw.test(password) == false) {
        return false;
     }
     else 
         return true;
}

// проверка доступности кнопки регистрации
function checkRegButton(){
    regBtn.disabled = !(validateEmail(emailInput.value) && validatePassword(password1Input.value) && validatePassword(password2Input.value) && password1Input.value===password2Input.value);
}

// проверка ввода почты
emailInput.addEventListener('input', function(){
    let isValidEmail = validateEmail(this.value);
    if(!isValidEmail && this.value.length>3){
        this.style.outlineColor = 'red';
        emailClue.classList.add('input-clue--active');
    }
    else{
        this.style.outlineColor = 'black';
        emailClue.classList.remove('input-clue--active');
    }

    checkRegButton();
});

// проверка ввода пароля
password1Input.addEventListener('input', function(){
    if(this.value.length === 0){
        this.style.outlineColor = 'black';
        password1Clue.classList.remove('input-clue--active');
    }
    else if(!validatePassword(this.value)){
        this.style.outlineColor = 'red';
        password1Clue.classList.add('input-clue--active');
    }
    else{
        this.style.outlineColor = 'black';
        password1Clue.classList.remove('input-clue--active');
    }

    checkRegButton();
});

// ввод пароля второго поля
password2Input.addEventListener('input', function(){
    if(this.value !== password1Input.value){
        this.style.outlineColor = 'red';
        password2Clue.classList.add('input-clue--active');
    }
    else{
        this.style.outlineColor = 'black';
        password2Clue.classList.remove('input-clue--active');
    }

    checkRegButton();
});