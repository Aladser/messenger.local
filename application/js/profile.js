/**
 * скрытый элемент выбора файлов
 */
const selectFileInput = document.querySelector('#select-file-input');
const hideEmailInput = document.querySelector('#hide-email-input');
const saveBtn = document.querySelector('#save-profile-settings-btn');
const inputNickname = document.querySelector('#input-nickname');
const prgError = document.querySelector('#prg-error');
const editNicknameBtn = document.querySelector('#btn-edit-nickname');
const editPhotoBtn = document.querySelector('#edit-photo-btn');

/**
 * изменить видимость кнопки Сохранить при переключении чекбокса скрытия почты
*/ 
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

/**
 * Проверить введенный никнейм
*/ 
function writeNickname(input, btn){
    let startValue = input.value; // изначальный никнейм
    return function func(){
        if(input.value !== startValue){
            let data = new URLSearchParams();
            data.set('nickname', input.value);

            // проверить никнейм на пустое поле или кириллицу
            if(input.value === '' || input.value.search(/[А-яЁё]/) !== -1){
                btn.classList.add('hidden');
                inputNickname.classList.add('input-nickname-error');
                prgError.classList.remove('hidden'); 
                prgError.innerHTML = 'Логин не должен содержать кирриллицу или быть пустым';
                return;
            }
            // проверить уникальность никнейма
            else{
                inputNickname.classList.remove('input-nickname-error');
                prgError.classList.add('hidden');
                fetch('/is-unique-nickname', {method:'post', body:data}).then(r=>r.text().then(data => {
                    // никнейм уникален
                    if(data == 1){
                        btn.classList.remove('hidden');
                        inputNickname.classList.remove('input-nickname-error');
                        prgError.classList.add('hidden'); 
                    }
                    else{
                        btn.classList.add('hidden');
                        inputNickname.classList.add('input-nickname-error');
                        prgError.classList.remove('hidden');
                        prgError.innerHTML = 'Логин занят';   
                    }
                }));
            }
        }
        else{
            btn.classList.add('hidden');
        }
    }
}

// активирует поле ввода нового никнейма
editNicknameBtn.onclick = () => {
    inputNickname.disabled = false;
    inputNickname.focus();
}

// снять фокус с поля никнейма
inputNickname.onblur = function(){
    let originalNickname = inputNickname.value;
    return function(){
        if(inputNickname.classList.contains('input-nickname-error')){
            inputNickname.value = originalNickname;
            inputNickname.classList.remove('input-nickname-error');
            prgError.classList.add('hidden'); 
        }
        inputNickname.disabled = true;
    };
}();



// ИЗОБРАЖЕНИЕ ПРОФИЛЯ
editPhotoBtn.onclick = () => selectFileInput.click();
// оправка формы на сервер
selectFileInput.onchange = () => {
    saveBtn.classList.remove('hidden');
    document.querySelector('#upload-file-btn').click();
}

// установка фото профиля
document.querySelector('#upload-file-form').onsubmit = e => {
    e.preventDefault();
    if(selectFileInput.value !== ''){
        fetch('/upload-file', {method: 'POST', body: new FormData(e.target)}).then(response => response.text()).then(filename => {
            let imgFile = filename != '' ? `application/data/temp/${filename}` : 'application/images/ava_profile.png';
            document.querySelector('#profile-img').src = imgFile;
            selectFileInput.value = ''; // очистка элемента выбора файлов
        });
    }
}



// отправка изменений профиля на сервер
saveBtn.addEventListener('click', ()=>{
    let data = new URLSearchParams();
    data.set('user_nickname', inputNickname.value);
    data.set('user_hide_email', hideEmailInput.checked ? 1 : 0);
    fpathArr = document.querySelector('#profile-img').src.split('/');
    data.set('user_photo', fpathArr[fpathArr.length - 1]);

    fetch('/set-userdata', {method: 'POST', body: data}).then(r => r.text()).then(data => {
        if(data == 0){
            saveBtn.classList.remove('hidden');
            prgError.innerHTML = 'серверная ошибка';
        }
        else{
            saveBtn.classList.add('hidden');
        }
    });
});

// полная загрузка страницы
window.onload = () =>{
    hideEmailInput.onchange = changeHideEmailInputVisibility(hideEmailInput, saveBtn);
    inputNickname.oninput = writeNickname(inputNickname, saveBtn);
}