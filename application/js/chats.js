const findContactsInput = document.querySelector('#find-contacts-input');
const contacts = document.querySelector('#contacts');
const username = document.querySelector('#username');
const chat = document.querySelector("#messages");
const wsUri = 'ws://localhost:8888';
const messageInput = document.querySelector("#message-input");
const sendMsgBtn = document.querySelector("#send-msg-btn");
const userHost = document.querySelector('#userhost-email').innerHTML; // имя пользователя-хоста




//***** КОНТАКТЫ *****
// ПОКАЗ КОНТАКТОВ-ЧАТОВ ПОЛЬЗОВАТЕЛЯ
function showContacts(findInput, contacts){
    fetch(`/get-contacts`, {method: 'get'}).then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';
        if(data != null) data.forEach(element => createContact(element));
    }); 
}
showContacts(findContactsInput, contacts); // показ контактов-чатов при загрузке страницы
document.querySelector('#reset-find-contacts-btn').onclick = () => showContacts(findContactsInput, contacts); // отмена поиска контакта и отображение контактов


// ДОБАВИТЬ КОНТАКТ-ЧАТ ПОЛЬЗОВАТЕЛЮ В БД
function setAddContact(contact){
    return function(){
        fetch(`/add-contact?contact=${contact}`, {method: 'get'}).then(r=>r.text()).then(data=>{
            if(data == 1){
                chat.innerHTML = '';
                username.innerHTML = contact;
            }
        });
    };
}


// ПОИСК КОНТАКТОВ В БД
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?userphrase=${this.value}`, {method: 'get'}).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';

        //  отображение найденных контактов в списке контактов
        if(data != null){
            data.forEach(element => createContact(element));
        }
    });
});


// ОТРИСОВКА КОНТАКТА-ЧАТА
function createContact(element){
    // контейнер контакта
    let contact = document.createElement('div');
    contact.className = 'contact position-relative mb-2 pb-0dot5';

    // контейнер изображения
    let contactImgDiv = document.createElement('div');
    contactImgDiv.className = 'img-div';

    // фото профиля
    let img = document.createElement('img');
    img.className = 'pe-2 img';
    if(element['user_photo'] == 'ava_profile.png'){
        img.src = 'application/images/ava.png';
    }
    else if(element['user_photo'] == null){
        img.src = 'application/images/ava.png';
    }
    else{
        img.src = `application/data/profile_photos/${element['user_photo']}`; 
    }

    contactImgDiv.appendChild(img);
    contact.appendChild(contactImgDiv);

    // имя контакта
    let name = document.createElement('span');
    name.className = 'text-break';
    name.innerHTML = element['username'];
    contact.appendChild(name);

    contact.onclick = setAddContact(element['username']);

    contacts.appendChild(contact);
}




//***** СООБЩЕНИЯ *****
// вывод сообщения пользователя на экран
function message(data){
    let msgBlock = document.createElement('div');
    let msgTable = document.createElement('table');
    let msgTextTr = document.createElement('tr');
    let msgTextTd = document.createElement('td');
    let msgTimeTr = document.createElement('tr');
    let msgTimeTd = document.createElement('td');

    msgBlock.className = data.author !== userHost ? 'msg d-flex justify-content-end' : 'msg';
    msgTable.className = data.author !== userHost ? 'msg-table msg-table-contact' : 'msg-table';
    msgTextTd.className = 'msg__text';
    msgTimeTd.className = 'msg__time';

    msgTextTd.innerHTML = data.message;
    msgTimeTd.innerHTML = '20.06.203 10.30';

    msgTextTr.appendChild(msgTextTd);
    msgTimeTr.appendChild(msgTimeTd);
    msgTable.appendChild(msgTextTr);
    msgTable.appendChild(msgTimeTr);
    msgBlock.appendChild(msgTable);
    chat.appendChild(msgBlock);
}


// вебсокет сообщений
let webSocket = new WebSocket(wsUri);
webSocket.onerror = error => chat.innerHTML += `<p class="message-system"> Ошибка подключения к серверу${error.message ? '. '+error.message : ''}</p>`;
webSocket.onmessage = function(e) {
    let data = JSON.parse(e.data);
    console.log(data);

    // сообщение от сервера о подключении пользователя. Передача имени пользователя и ID подключения
    if(data['onсonnection']){
        webSocket.send(JSON.stringify({
            'messageOnconnection': 1,
            'author' : userHost,
            'userId' : data['onсonnection']
        }));
    }
    // сообщение пользователям о подключении
    else if(data['messageOnconnection'] && data.author !== userHost){
        chat.innerHTML += `<p class="message-system">${data.author} в сети</p>`;
    }
    // сообщения пользователей
    else if(data['message']){
        message(data);
    }
};


// отправка данных на сервер
function sendData(){
    // непустые сообщения
    if(messageInput.value !== '' && webSocket.readyState === 1){
        webSocket.send(JSON.stringify({
            'message': messageInput.value,
            'author' : userHost
        }));
    }
    messageInput.value = '';
}
// событие отправки сообщения
messageInput.onkeyup = event => {
    if(event.code === 'Enter'){
        sendData();
    }
};
sendMsgBtn.onclick = sendData;
