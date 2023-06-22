const hideEmailInput = document.querySelector('#hide-email-input');
const saveBtn = document.querySelector('#save-profile-settings-btn');
const selectFileInput = document.querySelector('#select-file-input');
const inputNickname = document.querySelector('#input-nickname');
const profileImg = document.querySelector('#profile-img');
const prgError = document.querySelector('#prg-error');

// Исходные пользовательские данные
const originalNickName = '';
const originalIsHideEmail = false;
const originalPhoto = '';

document.querySelector('#btn-back-profile').onclick = () => window.open('/chats', '_self');
document.querySelector('#btn-exit-profile').onclick = () => window.open('/quit', '_self');


// изменение видимости кнопки сохранить при переключении чекбокса
function changeHideEmailInputVisibility(input, btn){
    let startState = input.checked; // изначальное состояние чекбокса скрытия почты
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
document.querySelector('#btn-edit-nickname').onclick = () => {
    inputNickname.disabled = false;
    inputNickname.focus();
}

function writeNickname(input, btn){
    let startValue = input.value; // изначальный никнейм
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



// отправка изменений профиля на сервер
saveBtn.addEventListener('click', ()=>{
    let data = new URLSearchParams();
    data.set('user_nickname', originalNickName === inputNickname.value ? '' : inputNickname.value);
    data.set('user_hide_email', originalIsHideEmail === hideEmailInput.checked ? '' : hideEmailInput.checked);
    if(originalPhoto !== profileImg.src){
        fpathArr = profileImg.src.split('/');
        data.set('user_photo', originalPhoto === profileImg.src ? '' : fpathArr[fpathArr.length - 1]);
    }

    fetch('/set-userdata', {method: 'POST', body: data}).then(r => r.text()).then(data => {
        console.log(data);
        if(data == 0){
            prgError.classList.remove('hidden');
            prgError.innerHTML = 'серверная ошибка';
        }
        else{
            prgError.classList.add('hidden');
        }
    });
});