const contacts = document.querySelector('#contacts');                                   // контейнер контактов
const chat = document.querySelector("#messages");                                       // контейнер сообщений
const findContactsInput = document.querySelector('#find-contacts-input');               // поле поиска пользователя
const messageInput = document.querySelector("#message-input");                          // поле ввода сообщения
const sendMsgBtn = document.querySelector("#send-msg-btn");                             // кнопка отправить сообщение
const resetFindContactsBtn = document.querySelector('#reset-find-contacts-btn');        // кнопка сброса поиска пользователей
const systemMessagePrg = document.querySelector("#message-system");                     // элемент для системных сообщений
const clientUsername = document.querySelector('#userhost-email').innerHTML.trim();      // почта пользователя-хоста
const publicClientUsername = document.querySelector('#publicUsername').value;           // публичное имя пользователя-хоста
const messagesContainerTitle = document.querySelector("#messages-container__title");    // заголовок чата
const contactUsernamePrg= messagesContainerTitle.querySelector('#contact-username');    // элемент названия контакта
const idChat = document.querySelector('#id-chat');                                      // id чата
const wsUri = 'ws://localhost:8888';                                                    // адрес вебсокета


//***** КОНТАКТЫ *****
// создать DOM-элемент контакта
function createContact(element){
    // контейнер контакта
    let contact = document.createElement('div');    // блок контакта
    let contactImgBlock = document.createElement('div'); // блок изображения профиля
    let img = document.createElement('img'); // фото профиля
    let name = document.createElement('span'); // имя контакта

    contact.className = 'contact position-relative mb-2';
    contactImgBlock.className = 'profile-img';
    img.className = 'img pe-2';
    name.className = 'contact__name';
    
    if(element['user_photo'] == 'ava_profile.png' || element['user_photo'] == null){
        img.src = 'application/images/ava.png';
    }
    else{
        img.src = `application/data/profile_photos/${element['user_photo']}`; 
    }

    name.innerHTML = element['username'];
    contact.onclick = setGetMessages(element['username']);

    contactImgBlock.appendChild(img);
    contact.appendChild(contactImgBlock);
    contact.appendChild(name);
    contacts.appendChild(contact);

    return contact;
}

// Установить событие: Открыть чат с контактом и добавить контакт, чат в БД, если не существуют
/**
 * Установить событие: Открыть чат с контактом и добавить контакт, чат в БД, если не существуют
 * 
 * @param mixed данные контакта из БД
 */
function setGetMessages(contact){
    return function(){
        fetch(`/get-messages?contact=${contact}`).then(r=>r.json()).then(data=>{
            //console.log(data);
            if(data.chat == 1){
                idChat.value = data.chatId; // запись id чата в скрытый элемент
                chat.innerHTML = '';
                messagesContainerTitle.classList.remove('invisible');
                contactUsernamePrg.innerHTML = contact;
                // >>------ ОТОБРАЖЕНИЕ ЧАТА ---------<<
            }
        });
    };
}

// показать контакты пользователя
function showContacts(findInput, contacts){
    fetch('/get-contacts').then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';
        if(data != null) data.forEach(element => createContact(element));
    }); 
}
showContacts(findContactsInput, contacts);


// сброс поиска пользователей и показ контактов
resetFindContactsBtn.onclick = () => showContacts(findContactsInput, contacts);

// поиск пользователей-контактов в БД
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?userphrase=${this.value}`).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';
        //  отображение найденных контактов в списке контактов
        if(data != null){data.forEach(element => createContact(element));}
    });
});

//***** СООБЩЕНИЯ *****
// вывести сообщение пользователя из вебсокета в браузере
function message(data){
    let msgBlock = document.createElement('div');
    let msgTable = document.createElement('table');
    let msgTextTr = document.createElement('tr');
    let msgTextTd = document.createElement('td');
    let msgTimeTr = document.createElement('tr');
    let msgTimeTd = document.createElement('td');

    msgBlock.className = data.fromuser !== publicClientUsername ? 'msg d-flex justify-content-end' : 'msg';
    msgTable.className = data.fromuser !== publicClientUsername ? 'msg-table msg-table-contact' : 'msg-table';
    msgTextTd.className = 'msg__text';
    msgTimeTd.className = 'msg__time';

    msgTextTd.innerHTML = data.message;
    msgTimeTd.innerHTML = data.time;

    msgTextTr.appendChild(msgTextTd);
    msgTimeTr.appendChild(msgTimeTd);
    msgTable.appendChild(msgTextTr);
    msgTable.appendChild(msgTimeTr);
    msgBlock.appendChild(msgTable);
    chat.appendChild(msgBlock);
}

/**
 * ВЕБСОКЕТ СООБЩЕНИЙ
 */
let webSocket = new WebSocket(wsUri);
webSocket.onerror = error => systemMessagePrg.innerHTML = `Ошибка подключения к серверу${error.message ? '. '+error.message : ''}`;
webSocket.onmessage = e => {
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
            let username = data.author===publicClientUsername ? 'Вы' : data.author;
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
    else{
        // показ сообщений открытого чата
        if((data.fromuser == publicClientUsername && data.touser == contactUsernamePrg.innerHTML) || (data.fromuser == contactUsernamePrg.innerHTML && data.touser == publicClientUsername)){
            // получение местного времени
            // 2023.07.11 12:00:00
            let timeInMs = Date.parse(data.time);
            let newDate = new Date(timeInMs);
            let timeZone = -newDate.getTimezoneOffset()/60; // текущий часовой пояс
            timeInMs += (timeZone-3)*3600000;
            newDate = new Date(timeInMs);
            data.time = newDate.toLocaleString("ru", {year: 'numeric',month: 'numeric',day: 'numeric',hour: 'numeric',minute: 'numeric'}).replace(',','');

            message(data);
        }
    }
};

/**
 * отправить сообщение на сервер
 *  */
function sendData(){
    // непустые сообщения, готовый к обмену сокет, открытй чат
    if(messageInput.value !== '' && webSocket.readyState === 1 && contactUsernamePrg.innerHTML!=''){
        webSocket.send(JSON.stringify({
            'message': messageInput.value,
            'fromuser' : publicClientUsername,
            'touser': contactUsernamePrg.innerHTML,
            'idChat' : idChat.value
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