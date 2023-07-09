const hideEmailInput = document.querySelector('#hide-email-input');
const saveBtn = document.querySelector('#save-profile-settings-btn');
const selectFileInput = document.querySelector('#select-file-input');
const inputNickname = document.querySelector('#input-nickname');
const prgError = document.querySelector('#prg-error');
document.querySelector('#btn-back-profile').onclick = () => window.open('/chats', '_self'); // кпнопка назад



// ЧЕКБОКС
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



// НИКНЕЙМ
// активирует поле ввода нового никнейма
document.querySelector('#btn-edit-nickname').onclick = () => {
    inputNickname.disabled = false;
    inputNickname.focus();
}

// ввод никнейма

// Проверка введенного никнейма
function writeNickname(input, btn){
    let startValue = input.value; // изначальный никнейм
    return function func(){
        if(input.value !== startValue){
            let data = new URLSearchParams();
            data.set('nickname', input.value);

            // НЕ пустое поле или кириллица
            if(input.value === '' || validateNickname(input.value)){
                btn.classList.add('hidden');
                inputNickname.classList.add('input-nickname-error');
                prgError.classList.remove('hidden'); 
                prgError.innerHTML = 'логин не должен содержать кирриллицу или быть пустым';
                return;
            }
            else{
                inputNickname.classList.remove('input-nickname-error');
                prgError.classList.add('hidden'); 
            }

            fetch('/is-unique-nickname', {method:'post', body:data}).then(r=>r.text().then(data => {
                if(data == 1){
                    btn.classList.remove('hidden');
                    inputNickname.classList.remove('input-nickname-error');
                    prgError.classList.add('hidden'); 
                }
                else{
                    btn.classList.add('hidden');
                    inputNickname.classList.add('input-nickname-error');
                    prgError.classList.remove('hidden');
                    prgError.innerHTML = 'логин занят';   
                }
            }));
        }
        else{
            btn.classList.add('hidden');
        }
    }
}
inputNickname.oninput = writeNickname(inputNickname, saveBtn);

// снятие фокуса с поля никнейма
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
document.querySelector('#edit-photo-btn').onclick = () => selectFileInput.click();
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