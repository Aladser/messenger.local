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

// валидация без кириллицы
function validateNickname(password){
    let passwSymbols = /^[а-яА-ЯёЁ]+$/;
    return passwSymbols.test(password);
}