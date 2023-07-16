const contacts = document.querySelector('#contacts');                                   // контейнер контактов
const chat = document.querySelector("#messages");                                       // контейнер сообщений
const groupChatsContainer = document.querySelector("#group-chats");                     // контейнер групповых чатов

const chatNameTitle = document.querySelector('#chat-title');                            // элемент начальной подписи чата 
const chatNameLabel = document.querySelector('#chat-username');                         // элемент имени контакта

const systemMessagePrg = document.querySelector("#message-system");                     // элемент для системных сообщений
const findContactsInput = document.querySelector('#find-contacts-input');               // поле поиска пользователя
const messageInput = document.querySelector("#message-input");                          // поле ввода сообщения
const resetFindContactsBtn = document.querySelector('#reset-find-contacts-btn');        // кнопка сброса поиска пользователей
const sendMsgBtn = document.querySelector("#send-msg-btn");                             // кнопка отправить сообщение

const wsUri = 'ws://localhost:8888';                                                    // адрес вебсокета
const clientUsername = document.querySelector('#userhost-email').innerHTML.trim();      // почта пользователя-хоста
const publicClientUsername = document.querySelector('#publicUsername').value;           // публичное имя пользователя-хоста
const createGroupOption = document.querySelector('#create-group-option');               // кнопка создать групповой чат

let chatType = null;                                                                    // тип чата
let chatId = null;                                                                      // id Чата

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
        if((data.fromuser == publicClientUsername && data.touser == chatNameLabel.innerHTML) || (data.fromuser == chatNameLabel.innerHTML && data.touser == publicClientUsername)){
            message(data);
        }
    }
};

/** создать DOM-элемент сообщения */
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

    msgTextTr.append(msgTextTd);
    msgTimeTr.append(msgTimeTd);
    msgTable.append(msgTextTr);
    msgTable.append(msgTimeTr);

    // показ автора сообщения в групповом чате
    if(chatType === 'discussion'){
        let msgAuthorTr = document.createElement('tr');
        let msgAuthorTd = document.createElement('td');
        msgAuthorTd.className = 'msg__author';
        msgAuthorTd.innerHTML = data.fromuser;
        msgAuthorTr.append(msgAuthorTd);
        msgTable.append(msgAuthorTr);
    }

    msgBlock.append(msgTable);
    chat.append(msgBlock);
}

/** создать DOM-элемент контакта */
function createContactDOMElement(element){
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

    img.src = (element['user_photo'] == 'ava_profile.png' || element['user_photo'] == null) ? 'application/images/ava.png' : `application/data/profile_photos/${element['user_photo']}`;
    name.innerHTML = element['username'];
    contact.onclick = setGetMessages(element['username'], 'dialog');

    contactImgBlock.append(img);
    contact.append(contactImgBlock);
    contact.append(name);
    contacts.append(contact);
}


/** создать DOM-элемент группы в списке групп
 * 
 * @param {*} group БД данные группы
 * @param {*} place куда добавить: START - начало списка, END - конец
 */
function createGroupDOMElement(group, place='END'){
    let groupsItem = document.createElement('div');
    let groupsItemName = document.createElement('div');

    groupsItem.className = 'groups__item';

    groupsItem.setAttribute('data-id', group.chat_id);
    // groupsItem.setAttribute('data-creatorid', group.chat_creatorid); // создатель чата
    groupsItemName.innerHTML = group.chat_name;
    groupsItem.onclick = setGetMessages({'chat_id':group.chat_id, 'chat_name':group.chat_name}, 'discussion');

    groupsItem.append(groupsItemName);
    if(place === 'START') groupChatsContainer.prepend(groupsItem);
    else if(place === 'END') groupChatsContainer.append(groupsItem);
}


/** показать контакты клиента-пользователя
 * @param {*} findInput поле поиска пользователей
 * @param {*} contacts поле контактов пользователя
 */
function showContacts(findInput, contacts){
    fetch('/get-contacts').then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';
        if(data != null) data.forEach(element => createContactDOMElement(element));
    }); 
}


/** Открыть чат диалога с контактом или группы
 * 
 *  добавить контакт и диалог в БД, если не существуют
 * */
function setGetMessages(element, type){
    return function(){
        let url;
        switch(type){
            case 'dialog':
                url = '/get-messages?contact='+element;
                break;
            case 'discussion':
                url = '/get-messages?discussionid='+element.chat_id;
                break;
            default:
                return;
        }

        fetch(url).then(r=>r.json()).then(data=>{
            if(data){
                chatType = data.type;
                chat.innerHTML = '';
                chatId = data.chatId;
                chatNameTitle.innerHTML = type==='dialog' ? 'Чат с пользователем ' : 'Обсуждение '; 
                chatNameLabel.innerHTML = type==='dialog' ? element : element.chat_name;                                                                                      
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
    if(messageInput.value !== '' && webSocket.readyState === 1 && chatNameLabel.innerHTML!=''){
        console.log(chatId);
        webSocket.send(JSON.stringify({
            'message':   messageInput.value,
            'fromuser' : publicClientUsername,
            'touser':    chatNameLabel.innerHTML,
            'idChat' :   chatId
        }));
    }
    messageInput.value = '';
}


// ----- ЗАГРУЗКА СООБЩЕНИЙ -----
window.addEventListener('load', () => {
    showContacts(findContactsInput, contacts);                                                                                      // показ контактов
    fetch('/get-groups').then(r=>r.json()).then(data => data.forEach(elem => createGroupDOMElement(elem)));                         // показ групповых чатов                            
    resetFindContactsBtn.onclick = () => showContacts(findContactsInput, contacts);                                                 // сброс поиска пользователей и показ контактов 
    createGroupOption.onclick = () => fetch('/create-group').then(r=>r.json()).then(data => createGroupDOMElement(data, 'START'));  // кнопка "Создать группу"
    let pressedKeys = [];                                                                                                           // массив нажатых клавиш
    messageInput.onkeydown = event => pressedKeys.push(event.code);                                                                 // нажатие клавиши
    sendMsgBtn.onclick = sendData;                                                                                                  // отправка сообщения по кнопке

    // поиск пользователей-контактов в БД по введенному слову и отображение найденных контактов в списке контактов
    findContactsInput.addEventListener('input', function(){
        fetch(`/find-contacts?userphrase=${this.value}`).then(r=>r.json()).then(data => {
            contacts.innerHTML = '';
            if(data != null){data.forEach(element => createContactDOMElement(element));}
        });
    });

    // отпускание клавиши при вводе сообщения
    messageInput.onkeyup = event => {
        // перевод строки, если Ctrl+Enter
        if(event.code === 'Enter' && pressedKeys.indexOf('ControlLeft') != -1){
            messageInput.value += '\n';
        }
        // отправка сообщения, если Enter
        else if(event.code === 'Enter'){
            messageInput.value = messageInput.value.substring(0, messageInput.value.length-1);
            sendData();
        }
        pressedKeys.splice(pressedKeys.indexOf(event.code), 1);
    };
});