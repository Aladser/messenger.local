/** элемент имени клиента-пользователя*/
const clientnameBlock = document.querySelector('#userhost');
/** почта пользователя-хоста */
const clientUsername = clientnameBlock.innerHTML.trim();
/** публичное имя пользователя-хоста */
const publicClientUsername = clientnameBlock.getAttribute('data-user-publicname');

/** контейнер контактов */
const contactsContainer = document.querySelector('#contacts');
/** контейнер сообщений */
const chat = document.querySelector("#messages");
/** контейнер групповых чатов */
const groupChatsContainer = document.querySelector("#group-chats");
/** элемент начальной подписи чата */
const chatNameTitle = document.querySelector('#chat-title');
/** элемент имени контакта */
const chatNameLabel = document.querySelector('#chat-username');
/** элемент для системных сообщений */
const systemMessagePrg = document.querySelector("#message-system");
/** поле поиска пользователя */
const findContactsInput = document.querySelector('#find-contacts-input');
/** поле ввода сообщения */
const messageInput = document.querySelector("#message-input");
/** кнопка сброса поиска пользователей */
const resetFindContactsBtn = document.querySelector('#reset-find-contacts-btn');
/** кнопка отправить сообщение */
const sendMsgBtn = document.querySelector("#send-msg-btn");
/** адрес вебсокета */
const wsUri = 'ws://localhost:8888';
/** кнопка создать групповой чат */
const createGroupOption = document.querySelector('#create-group-option'); 

/** текущий тип чата*/
let chatType = null;
/** текущий id чата*/
let chatId = null;
/** список участников выбранной группы */
let groupContacts = [];
/** создатель группового чата*/
let discussionCreatorName = null;


/** ----- ВЕБСОКЕТ СООБЩЕНИЙ -----*/
let webSocket = new WebSocket(wsUri);
webSocket.onerror = () => systemMessagePrg.innerHTML = 'Ошибка подключения к серверу';
webSocket.onmessage = e => {
    let data = JSON.parse(e.data);

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
    // показ сообщений открытого чата
    else{
        if(chatId === data.chatId) appendMessage(data);
    }
};


/** создать DOM-элемент сообщения */
function appendMessage(data){
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
function appendContactDOMElement(element){
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
    contact.addEventListener('click', setGetMessages(contact, element['username'], 'dialog'));

    contactImgBlock.append(img);
    contact.append(contactImgBlock);
    contact.append(name);

    contactsContainer.append(contact);
}


/** добавить DOM участника группового чата */
function appendGroupContactDOMElement(parent, child){
    let contact = document.createElement('p');
    contact.className = 'group__contact';
    contact.innerHTML = child.publicname;
    contact.setAttribute('data-id', child.user_id);
    parent.append(contact);
}


/** создать DOM-элемент группы в списке групп
 * 
 * @param {*} group БД данные группы
 * @param {*} place куда добавить: START - начало списка, END - конец
 */
function appendGroupDOMElement(group, place='END'){
    let groupsItem = document.createElement('div');
    let groupsItemName = document.createElement('div');

    groupsItem.className = 'group';
    groupsItemName.className = 'group__title';

    groupsItem.setAttribute('data-id', group.chat_id);
    groupsItemName.innerHTML = group.chat_name;
    groupsItem.addEventListener('click', setGetMessages(groupsItem, {'chat_id':group.chat_id, 'chat_name':group.chat_name}, 'discussion'));

    groupsItem.append(groupsItemName);
    if(place === 'START') groupChatsContainer.prepend(groupsItem);
    else if(place === 'END') groupChatsContainer.append(groupsItem);
}


/** показать контакты пользователя-клиента*/
function showContacts(){
    fetch('/get-contacts').then(r=>r.json()).then(data => {
        findContactsInput.value = '';
        contactsContainer.innerHTML = '';
        data.forEach(element => appendContactDOMElement(element));
    });
}


/** показать групповые чаты пользователя-клиента */
function showGroups(){
    fetch('/get-groups').then(r=>r.json()).then(data => data.forEach(elem => appendGroupDOMElement(elem))); 
}


/** ОТКРЫТЬ ЧАТ ДИАЛОГА ИЛИ ГРУППОВОГО ЧАТА
 * 
 *  добавить контакт и диалог в БД, если не существуют
 * */
function setGetMessages(domElement, bdData, type){
    return function(){
        const urlParams = new URLSearchParams();
        if(type === 'dialog'){
            urlParams.set('contact', bdData);
            // удаление участников группового чата, если до этого был выбран чат
            let groupContactsElement = document.querySelector('.group__contacts'); // поиск существующего списка контактов групы
            if(groupContactsElement) groupContactsElement.parentNode.removeChild(groupContactsElement);  // удаление существующего списка контактов группы
            // удаление кнопок добавления в группу у контактов-неучастников предыдущей группы
            contactsContainer.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.parentNode.removeChild(cnt));
        }
        else if(type === 'discussion'){
            urlParams.set('discussionid', bdData.chat_id);
            // показ участников группового чата
            fetch('/get-group-contacts', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data => {
                let groupContactsElement = document.querySelector('.group__contacts'); // поиск существующего списка контактов групы
                if(groupContactsElement) groupContactsElement.parentNode.removeChild(groupContactsElement);  // удаление существующего списка контактов группы

                // создание DOM-списка участников группового чата
                let prtBlock = document.createElement('div'); // блок, где будут показаны участники группы
                prtBlock.className = 'group__contacts';
                domElement.append(prtBlock);
                groupContacts = [];
                data.participants.forEach(prt => {
                    appendGroupContactDOMElement(prtBlock, prt);
                    groupContacts.push(prt.publicname);
                });

                discussionCreatorName = data.creatorName;

                // удаление кнопок добавления в группу у контактов-неучастников предыдущей группы
                contactsContainer.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.parentNode.removeChild(cnt));
                // добавить новые кнопки добавления в группу у контактов-неучастников выбранной группы
                let contacts = [];
                contactsContainer.querySelectorAll('.contact').forEach(cnt => {
                    cntName = cnt.lastChild.innerHTML;
                    if(!groupContacts.includes(cntName)){
                        let plus = document.createElement('div');
                        plus.className = 'contact-addgroup position-absolute top-0 end-0';
                        plus.innerHTML = '+';
                        plus.title = 'добавить в групповой чат';
                        cnt.append(plus);
                    }
                    contacts.push(cntName);
                });
            });
        }
        else{
            return;
        }
       
        // показ сообщений
        fetch('/get-messages', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data=>{
            if(data){
                chatType = data.type;
                chat.innerHTML = '';
                chatId = data.chatId;
                chatNameTitle.innerHTML = type==='dialog' ? 'Чат с пользователем ' : 'Обсуждение '; 
                chatNameLabel.innerHTML = type==='dialog' ? bdData : bdData.chat_name;                                                                                      
                messageInput.disabled = false;  
                sendMsgBtn.disabled = false;
                data.messages.forEach(elem => appendMessage(elem));// сообщения
                chat.scrollTo(0, chat.scrollHeight); // прокрутка сообщений в конец
            }
        });
    };
}


/** отправить сообщение на сервер */
function sendData(){
    // непустые сообщения, готовый к обмену сокет, открытй чат
    if(messageInput.value !== '' && webSocket.readyState === 1 && chatNameLabel.innerHTML!=''){
        webSocket.send(JSON.stringify({
            'message':   messageInput.value,
            'fromuser' : publicClientUsername,
            'touser':    chatNameLabel.innerHTML,
            'chatId' :   chatId,
            'chatType': chatType
        }));
    }
    messageInput.value = '';
}


// ----- ЗАГРУЗКА СООБЩЕНИЙ -----
window.addEventListener('load', () => {
    resetFindContactsBtn.onclick = () => showContacts();
    showContacts();                                                                                                                 
    showGroups();                                                                                                                   

    createGroupOption.onclick = () => fetch('/create-group').then(r=>r.json()).then(data => appendGroupDOMElement(data, 'START'));
    let pressedKeys = [];                                           // массив нажатых клавиш
    messageInput.onkeydown = event => pressedKeys.push(event.code); // нажатие клавиши
    sendMsgBtn.onclick = sendData;

    // поиск пользователей-контактов в БД по введенному слову и отображение найденных контактов в списке контактов
    findContactsInput.addEventListener('input', function(){
        const urlParams = new URLSearchParams();
        urlParams.set('userphrase', this.value);
        fetch('/find-contacts', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data => {
            contactsContainer.innerHTML = '';
            if(data != null){data.forEach(element => appendContactDOMElement(element));}
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