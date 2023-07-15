const contacts = document.querySelector('#contacts');                                   // контейнер контактов
const chat = document.querySelector("#messages");                                       // контейнер сообщений

const contactNameTitle = document.querySelector('#contact-title');                      // элемент начальной подписи чата 
const contactNameLabel = document.querySelector('#contact-username');                   // элемент имени контакта
const systemMessagePrg = document.querySelector("#message-system");                     // элемент для системных сообщений
const findContactsInput = document.querySelector('#find-contacts-input');               // поле поиска пользователя
const messageInput = document.querySelector("#message-input");                          // поле ввода сообщения
const resetFindContactsBtn = document.querySelector('#reset-find-contacts-btn');        // кнопка сброса поиска пользователей
const sendMsgBtn = document.querySelector("#send-msg-btn");                             // кнопка отправить сообщение

const chatId = document.querySelector('#id-chat');                                      // id чата
const wsUri = 'ws://localhost:8888';                                                    // адрес вебсокета
const clientUsername = document.querySelector('#userhost-email').innerHTML.trim();      // почта пользователя-хоста
const publicClientUsername = document.querySelector('#publicUsername').value;           // публичное имя пользователя-хоста
const createGroupOption = document.querySelector('#create-group-option');               // кнопка создать групповой чат


//----- ВЕБСОКЕТ СООБЩЕНИЙ -----
let webSocket = new WebSocket(wsUri);
webSocket.onerror = () => systemMessagePrg.innerHTML = 'Ошибка подключения к серверу';
webSocket.onmessage = e => {
    let data = JSON.parse(e.data);
    //console.log(data);

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
        if((data.fromuser == publicClientUsername && data.touser == contactNameLabel.innerHTML) || (data.fromuser == contactNameLabel.innerHTML && data.touser == publicClientUsername)){
            message(data);
        }
    }
};


/** создать DOM-элемент контакта
 * @param {*} element данные контакта из БД
 * @returns 
 */
function createContact(element){
    // контейнер контакта
    let contact = document.createElement('div');    // блок контакта
    let contactImgBlock = document.createElement('div'); // блок изображения профиля
    let img = document.createElement('img'); // фото профиля
    let name = document.createElement('span'); // имя контакта

    contact.className = 'contact position-relative mb-2';
    contact.title = element['username'];
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


/** создать DOM-элемент сообщения 
 * 
*/
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

    // показ переводов строки на странице
    let brIndex = data.message.indexOf('\n');
    while(brIndex > -1){
        data.message = data.message.replace('\n', '<br>');
        brIndex = data.message.indexOf('\n');
    }

    msgTextTd.innerHTML = data.message;

    // показ местного времени
    // YYYY.MM.DD HH:ii:ss
    let timeInMs = Date.parse(data.time);
    let newDate = new Date(timeInMs);
    let timeZone = -newDate.getTimezoneOffset()/60; // текущий часовой пояс
    timeInMs += (timeZone-3)*3600000;
    newDate = new Date(timeInMs);
    let localTime = newDate.toLocaleString("ru", {year: 'numeric',month: 'numeric',day: 'numeric',hour: 'numeric',minute: 'numeric'}).replace(',','');
    msgTimeTd.innerHTML = localTime;

    msgTextTr.appendChild(msgTextTd);
    msgTimeTr.appendChild(msgTimeTd);
    msgTable.appendChild(msgTextTr);
    msgTable.appendChild(msgTimeTr);
    msgBlock.appendChild(msgTable);
    chat.appendChild(msgBlock);
}


/** показать контакты клиента-пользователя
 * @param {*} findInput поле поиска пользователей
 * @param {*} contacts поле контактов пользователя
 */
function showContacts(findInput, contacts){
    fetch('/get-contacts').then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';
        if(data != null) data.forEach(element => createContact(element));
    }); 
}


/** Открыть чат с контактом, добавить контакт и чат в БД, если не существуют, показать сообщения
 * 
 * @param mixed данные контакта из БД
 */
function setGetMessages(contact){
    return function(){
        fetch(`/get-messages?contact=${contact}`).then(r=>r.json()).then(data=>{
            if(data){
                chatId.value = data.chatId;  //сохранение id диалога на странице
                chat.innerHTML = '';
                
                // заголовок чата
                contactNameTitle.innerHTML = 'Чат с пользователем ';                 
                contactNameLabel.innerHTML =  contact;
                // дотсупность полей ввода                                                                                        
                messageInput.disabled = false;  
                sendMsgBtn.disabled = false;

                data.messages.forEach(elem => message(elem));// сообщения
                chat.scrollTo(0, chat.scrollHeight); // прокрутка сообщений в конец
            }
        });
    };
}


/** отправить сообщение на сервер */
function sendData(){
    // непустые сообщения, готовый к обмену сокет, открытй чат
    if(messageInput.value !== '' && webSocket.readyState === 1 && contactNameLabel.innerHTML!=''){
        webSocket.send(JSON.stringify({
            'message':   messageInput.value,
            'fromuser' : publicClientUsername,
            'touser':    contactNameLabel.innerHTML,
            'idChat' :   chatId.value
        }));
    }
    messageInput.value = '';
}


// ----- ЗАГРУЗКА СООБЩЕНИЙ -----
window.addEventListener('load', () => {
    showContacts(findContactsInput, contacts);                                      // показ контактов
    resetFindContactsBtn.onclick = () => showContacts(findContactsInput, contacts); // сброс поиска пользователей и показ контактов

    // поиск пользователей-контактов в БД по введенному слову
    findContactsInput.addEventListener('input', function(){
        fetch(`/find-contacts?userphrase=${this.value}`).then(r=>r.json()).then(data => {
            contacts.innerHTML = '';
            //  отображение найденных контактов в списке контактов
            if(data != null){data.forEach(element => createContact(element));}
        });
    });

    let pressedKeys = []; // массив нажатых клавиш
    messageInput.onkeydown = event => pressedKeys.push(event.code); // нажатие клавиши
    sendMsgBtn.onclick = sendData;      // отправка сообщения по кнопке

    // отпускание клавиши при вводе сообщения
    messageInput.onkeyup = event => {
        // перевод строки
        if(event.code === 'Enter' && pressedKeys.indexOf('ControlLeft') != -1){
            messageInput.value += '\n';
        }
        // отправка сообщения по Enter
        else if(event.code === 'Enter'){
            messageInput.value = messageInput.value.substring(0, messageInput.value.length-1);
            sendData();
        }
        pressedKeys.splice(pressedKeys.indexOf(event.code), 1);
    };

    // создать группу
    createGroupOption.onclick = () => console.log('createGroupOption клик');
});