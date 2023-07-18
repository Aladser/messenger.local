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
/** элемент начальной подписи чата */
const chatNameTitle = document.querySelector('#chat-title');
/** элемент имени контакта */
const chatNameLabel = document.querySelector('#chat-username');
/** кнопка создать групповой чат */
const createGroupOption = document.querySelector('#create-group-option'); 
/** поле поиска пользователя */
const findContactsInput = document.querySelector('#find-contacts-input');
/** контейнер групповых чатов */
const groupChatsContainer = document.querySelector("#group-chats");
/** поле ввода сообщения */
const messageInput = document.querySelector("#message-input");
/** кнопка сброса поиска пользователей */
const resetFindContactsBtn = document.querySelector('#reset-find-contacts-btn');
/** кнопка отправить сообщение */
const sendMsgBtn = document.querySelector("#send-msg-btn");
/** элемент для системных сообщений */
const systemMessagePrg = document.querySelector("#message-system");
/** адрес вебсокета */
const wsUri = 'ws://localhost:8888';

/** контекстное меню */
const contextMenu = document.querySelector('#context-menu');
/** элементы контекстного меню*/
const contextMenuElements = ['msg__text', 'msg__time', 'msg__tr-author', 'msg__author'];
/** кнопка контекстного меню редактировать*/
const editMsgBtn = document.querySelector('#edit-msg');
/** кнопка контекстного меню удалить*/
const removeMsgBtn = document.querySelector('#remove-msg');
/** кнопка контекстного меню переслать */
const resendMsgBtn = document.querySelector('#resend-msg');

/** выбранное сообщение */
let selectedMessageData = null;

/** текущий тип чата*/
let chatType = null;
/** текущий id чата*/
let chatId = null;
/** список участников выбранной группы */
let groupContacts = [];
/** создатель группового чата {заготовка на будущее}*/
let discussionCreatorName = null;
/** тип отправляемого сообщения*/
let messageType = 'NEW';


/** ----- ВЕБСОКЕТ СООБЩЕНИЙ -----*/
let webSocket = new WebSocket(wsUri);
webSocket.onerror = () => systemMessagePrg.innerHTML = 'Ошибка подключения к серверу';
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
    // показ сообщений открытого чата
    else{
        if(chatId === data.chatId) appendMessage(data);
    }
};


/** создать DOM-элемент сообщения */
function appendMessage(data){
    // показ местного времени
    // YYYY.MM.DD HH:ii:ss
    let timeInMs = Date.parse(data.time);
    let newDate = new Date(timeInMs);
    let timeZone = -newDate.getTimezoneOffset()/60; // текущий часовой пояс
    timeInMs += (timeZone-3)*3600000;
    newDate = new Date(timeInMs);
    let localTime = newDate.toLocaleString("ru", {year: 'numeric',month: 'numeric',day: 'numeric',hour: 'numeric',minute: 'numeric'}).replace(',','');
    // показ переводов строки на странице
    let brIndex = data.message.indexOf('\n');
    while(brIndex > -1){
        data.message = data.message.replace('\n', '<br>');
        brIndex = data.message.indexOf('\n');
    }

    let msgBlock = document.createElement('div');
    let msgTable = document.createElement('table');
    msgBlock.className = data.fromuser !== publicClientUsername ? 'msg d-flex justify-content-end' : 'msg';
    msgTable.className = data.fromuser !== publicClientUsername ? 'msg__table msg__table-contact' : 'msg__table';
    msgBlock.setAttribute('data-chat_message_id', data.chat_message_id);

    msgTable.innerHTML += `<tr><td class="msg__text">${data.message}</td></tr>`;
    msgTable.innerHTML += `<tr><td class="msg__time">${localTime}</td></tr>`;
    if(chatType === 'discussion') msgTable.innerHTML += `<tr class='msg__tr-author'><td class='msg__author'>${data.fromuser}</td></tr>`;     // показ автора сообщения в групповом чате

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


/** отправить сообщение на сервер */
function sendData(){
    // непустые сообщения, готовый к обмену сокет, открытй чат
    if(messageInput.value !== '' && webSocket.readyState === 1 && chatNameLabel.innerHTML!=''){
        webSocket.send(JSON.stringify({
            'message':   messageInput.value,
            'fromuser' : publicClientUsername,
            'touser':    chatNameLabel.innerHTML,
            'chatId' :   chatId,
            'chatType': chatType,
            'messageType' : messageType
        }));
    }
    messageInput.value = '';
}


/** ОТКРЫТЬ ЧАТ ДИАЛОГА ИЛИ ГРУППОВОГО ЧАТА
 * 
 * @param {*} domElement DOM-элемент контакта или чата
 * @param {*} bdData данные элемента из БД
 * @param {*} type тип диалога
 * @returns 
 */
function setGetMessages(domElement, bdData, type){
    return function(){
        const urlParams = new URLSearchParams();
        if(type === 'dialog'){
            urlParams.set('contact', bdData);
            removeDOMGroupPatricipants();
        }
        else if(type === 'discussion'){
            urlParams.set('discussionid', bdData.chat_id);
            // показ участников группового чата
            fetch('/get-group-contacts', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data => {
                removeDOMGroupPatricipants();
                // создание DOM-списка участников группового чата
                let prtBlock = document.createElement('div'); // блок, где будут показаны участники группы
                prtBlock.className = 'group__contacts';
                domElement.append(prtBlock);
                groupContacts = [];
                data.participants.forEach(prt => {
                    prtBlock.innerHTML += `<p class='group__contact'>${prt.publicname}</p>`;
                    groupContacts.push(prt.publicname);
                });

                discussionCreatorName = data.creatorName; // {заготовка на будущее}

                // добавить новые кнопки добавления в группу у контактов-неучастников выбранной группы
                let contacts = [];
                contactsContainer.querySelectorAll('.contact').forEach(cnt => {
                    cntName = cnt.lastChild.innerHTML;
                    if(!groupContacts.includes(cntName)){
                        let plus = document.createElement('div');
                        plus.className = 'contact-addgroup position-absolute top-0 end-0';
                        plus.innerHTML = '+';
                        plus.title = 'добавить в групповой чат';

                        // добавить пользователя в группу
                        plus.onclick = e =>{
                            let username = e.target.parentNode.childNodes[1].innerHTML; // имя пользователя
                            e.stopPropagation();    // прекратить всплытие событий

                            const urlParams2 = new URLSearchParams();
                            urlParams2.set('discussionid', bdData.chat_id);
                            urlParams2.set('username', username);
                            fetch('add-group-contact', {method: 'POST', body: urlParams2}).then(r=>r.text()).then(data => {
                                if(data == 1){
                                    e.target.parentNode.lastChild.remove();
                                    domElement.lastChild.innerHTML += `<p class='group__contact'>${username}</p>`;
                                }
                                else{
                                    console.log(data);
                                }
                            });
                        }

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
                chat.innerHTML = '';

                chatType = data.type;
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


/** удаление DOM участников предыдущего выбранного группового чата */
function removeDOMGroupPatricipants(){
    let groupContactsElement = document.querySelector('.group__contacts'); // поиск существующего списка контактов групы
    if(groupContactsElement) groupContactsElement.parentNode.removeChild(groupContactsElement);  // удаление существующего списка контактов группы
    // удаление кнопок добавления в группу у контактов-неучастников предыдущей группы
    contactsContainer.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.parentNode.removeChild(cnt));
}


/** скрыть контекстное меню*/
function hideContextMenu(){
    contextMenu.style.left = '0px';
    contextMenu.style.top = '1000px';
    contextMenu.style.display = 'none';
}


/** изменить сообщение */
function editMessage(){
    console.log('edit');
    console.log(selectedMessageData);
    messageType = 'EDIT';
    hideContextMenu();
}


/** удалить сообщение  */
function removeMessage(){
    console.log('remove');
    console.log(selectedMessageData);
    messageType = 'REMOVE';
    hideContextMenu();
}


/** переотправить сообщение */
function resendMessage(){
    console.log('resend');
    console.log(selectedMessageData);
    messageType = 'RESEND';
    hideContextMenu();
}


// ----- загрузка DOM дерева -----
window.addEventListener('DOMContentLoaded', () => {
    resetFindContactsBtn.onclick = showContacts;
    showContacts();
    showGroups();

    createGroupOption.onclick = () => fetch('/create-group').then(r=>r.json()).then(data => appendGroupDOMElement(data, 'START'));
    let pressedKeys = [];                                           // массив нажатых клавиш
    messageInput.onkeydown = event => pressedKeys.push(event.code); // нажатие клавиши
    sendMsgBtn.onclick = sendData;
    document.oncontextmenu = function() {return false;}; // запрет контекстного меню

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

    // потеря фокуса с элемента ввода сообщения
    messageInput.onblur = function(){
        this.value = '';
        messageType = 'NEW';
    };

    // показать контекстное меню сообщения
    window.oncontextmenu = event => {
        // клик на элементах сообщения
        if(contextMenuElements.includes(event.target.className)){
            // координаты меню
            contextMenu.style.left = event.pageX+'px';
            contextMenu.style.top = event.pageY+'px';
            contextMenu.style.display = 'block';
             
            if(['msg__text', 'msg__time', 'msg__author'].includes(event.target.className)){
                selectedMessageData = event.target.parentNode.parentNode;
            }
            else{
                selectedMessageData = event.target.parentNode;
            }
        }
        else{
            hideContextMenu();
        }
    };
    // нажать пункт контекстного меню сообщения
    window.onclick = event => {
        if(event.target.className !== 'list-group-item') hideContextMenu();
    };
    // удаление контекстного меню сообщения при прокрутке диалога
    chat.onscroll = hideContextMenu;

    editMsgBtn.onclick = editMessage;
    removeMsgBtn.onclick = removeMessage;
    resendMsgBtn.onclick = resendMessage;
});