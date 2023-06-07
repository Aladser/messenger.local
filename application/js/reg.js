
function validateEmail(email){
    let reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    if(reg.test(email) == false) {
       return false;
    }
    else 
        return true;
}

function validatePassword(password){
    let passw = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,20}$/;
    if(passw.test(password) == false) {
        return false;
     }
     else 
         return true;
}

const regBtn = document.querySelector('#reg-form__reg-btn');

const emailInput = document.querySelector('#reg-form__email-input');
const password1Input = document.querySelector('#reg-form__password1-input');
const password2Input = document.querySelector('#reg-form__password2-input');

const emailClue = document.querySelector('#reg-form_email-clue');
const password1Clue = document.querySelector('#reg-form_password1-clue');
const password2Clue = document.querySelector('#reg-form_password2-clue');

// проверка ввода почты
emailInput.addEventListener('input', function(){
    if(validateEmail(this.value) && validatePassword(password1Input.value) && validatePassword(password2Input.value) && password1Input.value===password2Input.value){
        regBtn.disabled = false;
        this.style.outlineColor = 'green';
    }
    else{
        regBtn.disabled = true;
        this.style.outlineColor = 'red';
    }
});

// ввод пароля
password1Input.addEventListener('input', function(){
    if(validatePassword(this.value) && validateEmail(emailInput.value) && validatePassword(password2Input.value) && this.value===password2Input.value){
        regBtn.disabled = false;
        this.style.outlineColor = 'green';
    }
    else{
        regBtn.disabled = true;
        this.style.outlineColor = 'red';
    }
});

// ввод пароля второго поля
password2Input.addEventListener('input', function(){
    if(validatePassword(this.value) && validateEmail(emailInput.value) && validatePassword(password1Input.value) && this.value===password1Input.value){
        regBtn.disabled = false;
        this.style.outlineColor = 'green';
    }
    else{
        regBtn.disabled = true;
        this.style.outlineColor = 'red';
    }
});