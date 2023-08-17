/** инпут CSRF-токена */
const inputCsrf = document.querySelector('#input-csrf');

const uploadForm = document.querySelector('#upload-file-form');
/** скрытый элемент выбора файлов */
const selectFileInput = document.querySelector('#select-file-input');
/** блок чекбокса скрытия почты */
const hideEmailInputBlock = document.querySelector('#hide-email-input-block');
/** чекбокс скрытия почты */
const hideEmailInput = document.querySelector('#hide-email-input');
/** кнопка сохранения изменений */
const saveBtn = document.querySelector('#save-profile-settings-btn');
/** элемент отображения никнейма */
const inputNickname = document.querySelector('#input-nickname');

const prgError = document.querySelector('#prg-error');
const editNicknameBtn = document.querySelector('#btn-edit-nickname');
const editPhotoBtn = document.querySelector('#edit-photo-btn');

/** изображение профиля */
const profileImageField = document.querySelector('#profile-img');
/** случайное число*/
let randomNumber = Math.round(Math.random() * 100000);

function parseJSONData(data)
{
    try {
        data = JSON.parse(data);
        return data;
    } catch(err) {
        prgError.classList.remove('d-none');
        prgError.innerHTML = data;
        return undefined;
    }
}

/** изменить видимость кнопки Сохранить при переключении чекбокса скрытия почты */
function changeHideEmailInputVisibility(input, btn)
{
    let startState = input.checked; // изначальное состояние чекбокса скрытия почты
    return function func()
    {
        if (input.checked !== startState) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
    }
}

/** проверить введенный никнейм */
function writeNickname(input, btn)
{
    let startValue = input.value; // изначальный никнейм
    return function func()
    {
        if (input.value !== startValue) {
            let data = new URLSearchParams();
            data.set('nickname', input.value);
            data.set('CSRF', inputCsrf.value);

            // проверить никнейм на пустое поле или кириллицу
            if (input.value === '' || input.value.search(/[А-яЁё]/) !== -1) {
                btn.classList.add('d-none');
                inputNickname.classList.add('input-nickname-error');
                prgError.classList.remove('d-none');
                prgError.innerHTML = 'Логин не должен содержать кирриллицу или быть пустым';
            } else {
                // проверить уникальность никнейма
                inputNickname.classList.remove('input-nickname-error');
                prgError.classList.add('d-none');
                fetch('user/is-unique-nickname', {method: 'post', body: data}).then(r => r.text().then(data => {
                    data = parseJSONData(data);
                    if (data == undefined) {
                        btn.classList.add('d-none');
                        inputNickname.classList.add('input-nickname-error');
                        return;             
                    }

                    data = parseInt(data.response);
                    // никнейм уникален
                    if (data === 1) {
                        btn.classList.remove('d-none');
                        inputNickname.classList.remove('input-nickname-error');
                        prgError.classList.add('d-none');
                    } else {
                        btn.classList.add('d-none');
                        inputNickname.classList.add('input-nickname-error');
                        prgError.classList.remove('d-none');
                        prgError.innerHTML = 'Логин занят';
                    }
                }));
            }
        } else {
            btn.classList.add('d-none');
        }
    }
}

/** активирует поле ввода нового никнейма */
editNicknameBtn.onclick = () => {
    inputNickname.disabled = false;
    inputNickname.focus();
}

/** снять фокус с поля никнейма */
inputNickname.onblur = function () {
    let originalNickname = inputNickname.value;
    return function () {
        if (inputNickname.classList.contains('input-nickname-error')) {
            inputNickname.value = originalNickname;
            inputNickname.classList.remove('input-nickname-error');
            prgError.classList.add('d-none');
        }
        inputNickname.disabled = true;
    };
}();


// ИЗОБРАЖЕНИЕ ПРОФИЛЯ
editPhotoBtn.onclick = () => selectFileInput.click();
/** оправка формы на сервер */
selectFileInput.onchange = () => {
    saveBtn.classList.remove('d-none');
    document.querySelector('#upload-file-btn').click();
}

// показ выбранного изображения в проводнике как фото профиля
uploadForm.onsubmit = e => {
    e.preventDefault();
    let file = e.target.image.files[0];
    if (file !== undefined) {
        let formData = new FormData(e.target);
        formData.set('CSRF', inputCsrf.value);
        fetch('/upload-file', {method: 'POST', body: formData})
            .then(response => response.text())
            .then(data => {
                try {
                    data = JSON.parse(data);
                    data.image = data.image.trim();
                    if (data.image !== '') {
                        profileImageField.src = `application/data/temp/${data.image}?r=${randomNumber++}`;
                    } else {
                        profileImageField.src = 'application/images/ava_profile.png';
                    }
                    // очистка элемента выбора файлов
                    selectFileInput.value = '';
                    prgError.classList.add('d-none');
                } catch (err) {
                    prgError.classList.remove('d-none');
                    saveBtn.classList.add('d-none');
                    prgError.innerHTML = data;
                }
            });
    }
}

// сохранение измененных данных, и отправка изменений профиля на сервер
saveBtn.addEventListener('click', () => {
    let data = new URLSearchParams();
    data.set('user_nickname', inputNickname.value);
    data.set('user_hide_email', hideEmailInput.checked ? '1' : '0');
    data.set('CSRF', inputCsrf.value);
    let filepathArr = document.querySelector('#profile-img').src.split('/');
    data.set('user_photo', filepathArr[filepathArr.length - 1]);

    fetch('user/update', {method: 'POST', body: data}).then(r => r.text()).then(data => {
        data = parseJSONData(data);
        if (data !== undefined) {
            saveBtn.classList.add('d-none');             
        }
    });

if (inputNickname.value.trim() !== '') {
    hideEmailInputBlock.classList.remove('d-none');
}
});

window.addEventListener('DOMContentLoaded', () => {
    profileImageField.src = `${profileImageField.src}?r=${randomNumber++}`;
    hideEmailInput.onchange = changeHideEmailInputVisibility(hideEmailInput, saveBtn);
    inputNickname.oninput = writeNickname(inputNickname, saveBtn);
    // скрыть кнопку скрытия почты, если пустой никнейм
    if (inputNickname.value.trim() !== '') {
        hideEmailInputBlock.classList.remove('d-none');
    }
});