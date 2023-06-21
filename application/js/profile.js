let hideEmailInput = document.querySelector('#hide-email-input');
let saveBtn = document.querySelector('#save-profile-settings-btn');
let selectFileInput = document.querySelector('#select-file-input');
let inputNickname = document.querySelector('#input-nickname');



// изменение видимости кнопки сохранить при переключении чекбокса
function changeHideEmailInputVisibility(input, btn){
    let startState = input.checked; // изначальное состояние чекбокса
    return function func(){
        if(input.checked !== startState){
            btn.classList.remove('hidden');
        }
        else{
            btn.classList.add('hidden');
        }
    }
}
hideEmailInput.onchange = changeHideEmailInputVisibility(hideEmailInput, saveBtn);



// загрузка изображения на сервер
document.querySelector('#edit-photo-btn').onclick = () => selectFileInput.click();

selectFileInput.onchange = () => {
    saveBtn.classList.remove('hidden');
    document.querySelector('#upload-file-btn').click();
}

document.querySelector('#upload-file-form').onsubmit = e => {
    e.preventDefault();
    if(selectFileInput.value !== ''){
        fetch('/upload-file', {method: 'POST', body: new FormData(e.target)}).then(response => response.text()).then(data => {
            document.querySelector('#profile-img').src = data;
        });
    }
}



// установка nickname
document.querySelector('#btn-profile').onclick = () => {
    inputNickname.disabled = false;
    inputNickname.focus();
}

function writeNickname(input, btn){
    let startValue = input.value;
    return function func(){
        if(input.value !== startValue){
            btn.classList.remove('hidden');
        }
        else{
            btn.classList.add('hidden');
        }
    }
}
inputNickname.oninput = writeNickname(inputNickname, saveBtn);
inputNickname.onblur = () => inputNickname.disabled = true;