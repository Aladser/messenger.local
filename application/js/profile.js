let hideEmailInput = document.querySelector('#hide-email-input');
let saveBtn = document.querySelector('#save-profile-settings-btn');
let editPhotoBtn = document.querySelector('#edit-photo-btn');
let selectFileInput = document.querySelector('#select-file-input');
let uploadFileForm = document.querySelector('#upload-file-form');
let uploadFileBtn = document.querySelector('#upload-file-btn');
let file;


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
    }
}
hideEmailInput.onchange = changeHideEmailInputState(hideEmailInput, saveBtn);


// загрузка изображения на сервер
editPhotoBtn.onclick = () => selectFileInput.click();

selectFileInput.onchange = () => {
    saveBtn.classList.remove('hidden');
    uploadFileBtn.click();
}

uploadFileForm.onsubmit = e => {
    e.preventDefault();
    if(selectFileInput.value !== ''){
        fetch('/upload-file', {method: 'POST', body: new FormData(e.target)}).then(response => response.text()).then(data => {
            document.querySelector('#profile-img').src = data;
        });
    }
}