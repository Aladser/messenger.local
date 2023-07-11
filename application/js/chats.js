const contacts = document.querySelector('#contacts');
const chat = document.querySelector("#messages");

const findContactsInput = document.querySelector('#find-contacts-input');
const messageInput = document.querySelector("#message-input");
const sendMsgBtn = document.querySelector("#send-msg-btn");
const systemMessagePrg = document.querySelector("#message-system");

const clientUsername = document.querySelector('#userhost-email').innerHTML.trim(); // имя пользователя-клиента
const publicClientUsername = document.querySelector('#publicUsername').value; // публичное имя пользователя-клиента
const messagesContainerTitle = document.querySelector("#messages-container__title"); // заголовок контейнера сообщений
const contactUsernamePrg= messagesContainerTitle.querySelector('#contact-username'); // элемент для показа имени контакта отображаемого чата

const wsUri = 'ws://localhost:8888';


//***** КОНТАКТЫ *****
/**
 * показать контакт на странице
 * @param {*} element данные контакта из БД
 * @returns DOM-элемент контакта
 */
function createContact(element){
    // контейнер контакта
    let contact = document.createElement('div');    // блок контакта
    let contactImgBlock = document.createElement('div'); // блок изображения профиля
    let img = document.createElement('img'); // фото профиля
    let name = document.createElement('span'); // имя контакта

    contact.className = 'contact position-relative mb-2';
    contactImgBlock.className = 'img-div';
    img.className = 'img pe-2';
    name.className = 'contact__name';
    
    if(element['user_photo'] == 'ava_profile.png' || element['user_photo'] == null){
        img.src = 'application/images/ava.png';
    }
    else{
        img.src = `application/data/profile_photos/${element['user_photo']}`; 
    }

    name.innerHTML = element['username'];
    contact.onclick = setAddContact(element['username']);

    contactImgBlock.appendChild(img);
    contact.appendChild(contactImgBlock);
    contact.appendChild(name);
    contacts.appendChild(contact);

    return contact;
}
// ФУНКЦИЯ ДОБАВИТЬ КОНТАКТ В БД для события
function setAddContact(contact){
    return function(){
        fetch(`/add-contact?contact=${contact}`).then(r=>r.text()).then(data=>{
            if(data == 1){
                chat.innerHTML = '';
                messagesContainerTitle.classList.remove('invisible');
                contactUsernamePrg.innerHTML = contact;
                // ОТОБРАЖЕНИЕ ЧАТА ---------<<
            }
        });
    };
}

// ПОКАЗ КОНТАКТОВ-ЧАТОВ ПОЛЬЗОВАТЕЛЯ
function showContacts(findInput, contacts){
    fetch('/get-contacts').then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';
        if(data != null) data.forEach(element => {
            createContact(element)
        });
    }); 
}


showContacts(findContactsInput, contacts); // показ контактов-чатов при загрузке страницы
document.querySelector('#reset-find-contacts-btn').onclick = () => showContacts(findContactsInput, contacts); // отмена поиска контакта и отображение контактов

// ПОИСК КОНТАКТОВ В БД
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?userphrase=${this.value}`).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';
        //  отображение найденных контактов в списке контактов
        if(data != null){
            data.forEach(element => createContact(element));
        }
    });
});
 

//***** СООБЩЕНИЯ *****
// вывод сообщения пользователя на экран
function message(data){
    let msgBlock = document.createElement('div');
    let msgTable = document.createElement('table');
    let msgTextTr = document.createElement('tr');
    let msgTextTd = document.createElement('td');
    let msgTimeTr = document.createElement('tr');
    let msgTimeTd = document.createElement('td');

    msgBlock.className = data.author !== clientUsername ? 'msg d-flex justify-content-end' : 'msg';
    msgTable.className = data.author !== clientUsername ? 'msg-table msg-table-contact' : 'msg-table';
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

// ВЕБСОКЕТ
// вебсокет сообщений
let webSocket = new WebSocket(wsUri);
webSocket.onerror = error => systemMessagePrg.innerHTML = `Ошибка подключения к серверу${error.message ? '. '+error.message : ''}`;
webSocket.onmessage = function(e) {
    let data = JSON.parse(e.data);
    console.log(data);

    // сообщение от сервера о подключении пользователя. Передача имени пользователя и ID подключения серверу текущего пользователя
    if(data.onсonnection){
        webSocket.send(JSON.stringify({
            'messageOnconnection': 1,
            'author' : clientUsername,
            'userId' : data.onсonnection
        }));
    }
    // сообщение пользователям о подключении клиента
    else if(data.messageOnconnection){
        // подключение клиента
        if(data.author){
            let username = data.author===clientUsername || data.author===publicClientUsername ? 'Вы' : data.author;
            systemMessagePrg.innerHTML = `${username} в сети`;
        }
        // ошибки подключения
        else{
            systemMessagePrg.innerHTML = `${data.systeminfo}`;
        }
    }
    // сообщение пользователям об отключении
    else if(data.offсonnection){
        systemMessagePrg.innerHTML = `${data.user} не в сети`;
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
            'author' : clientUsername
        }));
    }
    messageInput.value = '';
}
// событие отправки сообщения
messageInput.onkeyup = event => {
    if(event.code === 'Enter'){
        messageInput.value = messageInput.value.replace(/\n/g, '')
        sendData();
    }
};
sendMsgBtn.onclick = sendData;