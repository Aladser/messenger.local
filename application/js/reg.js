
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

const emailInput = document.querySelector('#regForm_emailInput');
const passwordInput1 = document.querySelector('#regForm__passwordInput1');
const passwordInput2 = document.querySelector('#regForm__passwordInput2');
const regBtn = document.querySelector('#regForm__regBtn');

// проверка ввода почты
emailInput.addEventListener('input', function(){
    if(validateEmail(this.value) && validatePassword(passwordInput1.value) && validatePassword(passwordInput2.value) && passwordInput1.value===passwordInput2.value){
        regBtn.disabled = false;
        this.style.outlineColor = 'green';
    }
    else{
        regBtn.disabled = true;
        this.style.outlineColor = 'red';
    }
});

// ввод пароля
passwordInput1.addEventListener('input', function(){
    if(validatePassword(this.value) && validateEmail(emailInput.value) && validatePassword(passwordInput2.value) && this.value===passwordInput2.value){
        regBtn.disabled = false;
        this.style.outlineColor = 'green';
    }
    else{
        regBtn.disabled = true;
        this.style.outlineColor = 'red';
    }
});

// ввод пароля второго поля
passwordInput2.addEventListener('input', function(){
    if(validatePassword(this.value) && validateEmail(emailInput.value) && validatePassword(passwordInput1.value) && this.value===passwordInput1.value){
        regBtn.disabled = false;
        this.style.outlineColor = 'green';
    }
    else{
        regBtn.disabled = true;
        this.style.outlineColor = 'red';
    }
});