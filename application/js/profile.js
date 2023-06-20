let hideEmailInput = document.querySelector('#hide-email-input');
let saveBtn = document.querySelector('#save-profile-settings-btn');

// изменение видимости кнопки сохранить при переключении чекбокса
function changeHideEmailInputState(input, btn){
    let startState = input.checked; // изначальное состояние чекбокса
    return function func(){
        if(input.checked !== startState){
            btn.classList.remove('hidden');
        }
        else{
            btn.classList.add('hidden');
        }
        console.log(input.checked);
    }
}
hideEmailInput.onchange = changeHideEmailInputState(hideEmailInput, saveBtn);