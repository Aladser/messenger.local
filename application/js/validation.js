// валидация почты
function validateEmail(email)
{
    let emailSymbols = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    return emailSymbols.test(email);
}

// валидация пароля
function validatePassword(password)
{
    let passSymbols = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,1000}$/;
    return passSymbols.test(password);
}