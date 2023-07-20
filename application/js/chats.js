/** элемент имени клиента-пользователя*/
const clientnameBlock = document.querySelector('#clientuser');
/** почта пользователя-хоста */
const clientUsername = clientnameBlock.innerHTML.trim();
/** публичное имя пользователя-хоста */
const publicClientUsername = clientnameBlock.getAttribute('data-clientuser-publicname');

/** контейнер контактов */
const contactsContainer = document.querySelector('#contacts');
/** контейнер сообщений */
const chat = document.querySelector("#messages");
/** элемент начальной подписи чата */
const chatNameTitle = document.querySelector('#chat-title');
/** С кем открыт чат */
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

/** блок кнопок пересылки сообщения  */
const forwardBtnBlock = document.querySelector('#btn-resend-block');
/** кнопка пересылки сообщения */
const forwardBtn = document.querySelector('#btn-resend');
/** кнопка отмены пересылки сообщения */
const resetForwardtBtn = document.querySelector('#btn-resend-reset');

/** контекстное меню сообщения*/
const msgContextMenu = document.querySelector('#msg-context-menu');
/** кнопка контекстного меню: Редактировать сообщение*/
const editMsgContextMenuBtn = document.querySelector('#edit-msg');
/** кнопка контекстного меню: Удалить сообщение*/
const removeMsgContextMenuBtn = document.querySelector('#remove-msg');
/** кнопка контекстного меню: Переслать сообщение*/
const forwardMsgContextMenuBtn = document.querySelector('#resend-msg');
/** контексное меню контакта*/
const contactContextMenu = document.querySelector('#contact-context-menu');
/** контекстное меню: изменить показ уведомлений*/
const editNoticeShowContextMenuBtn = document.querySelector('#contact-notice-edit');

/** Выбранное сообщение */
let selectedMessage = null;
/** Выбранный контакт или группа*/
let selectedContact = null;
/** DOM-элемент получателя пересланного письма*/
let forwardedMessageRecipientElement = null;
/** имя получателя пересланного письма*/
let forwardedMessageRecipientName = null;
/** текущий тип чата*/
let chatType = null;
/** id открытого чата*/
let openChatId = null;
/** флаг измененного сообщения */
let isEditMessage = false;
/** флаг пересылаемого сообщения*/
let isForwaredMessage = false;
/** список контактов*/
let contactList = [];
/** список групп */
let groupList = [];
/** список участников выбранной группы */
let groupContacts = [];


/** ----- ВЕБСОКЕТ СООБЩЕНИЙ -----*/
let webSocket = new WebSocket(wsUri);
webSocket.onerror = () => systemMessagePrg.innerHTML = 'Ошибка подключения к серверу';
webSocket.onmessage = e => {
    let data = JSON.parse(e.data);
    //console.clear();
    //console.log(e.data);


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
    // сообщения
    else{   
        // уведомления о новых сообщениях чатов контактов и групп
        // Веб-сервер широковещательно рассылает все сообщения. Поэтому ищутся сообщения для чатов из контактов и групп пользователя-клиента
        if( (data.messageType === 'NEW' || data.messageType === 'FORWARD') && data.fromuser != publicClientUsername){
            foundedContactChat = contactList.find(el => el.chat_id == data.chatId);
            foundedGroupChat = groupList.find(el => el.chat_id == data.chatId);
            let isChatInContacts = (foundedContactChat!=undefined) || (foundedGroupChat!=undefined);
            // сделано специально множественное создание объектов звука
            if(isChatInContacts){
                let audio = new Audio('application/views/notice.wav');
                audio.play();
            }
        }

        // сообщения открытого чата
        if(openChatId == data.chatId){
            // изменение сообщения
            if(data.messageType === 'EDIT'){
                let messageDOMElem = document.querySelector(`[data-chat_message_id="${data.chat_message_id}"]`);
                messageDOMElem.querySelector('.msg__text').innerHTML = data.chat_message_text;
            }
            // удаление сообщения
            else if(data.messageType === 'REMOVE'){
                let messageDOMElem = document.querySelector(`[data-chat_message_id="${data.chat_message_id}"]`);
                messageDOMElem.remove();
            }
            // новое сообщение   
            else{
                appendMessage(data);
            } 
        }
        else{
            console.log(data);
        } 
    }
};


/** Отправить сообщение на сервер
 * @param message текст сообщения
 * @param messageType тип сообщения: NEW, EDIT, REMOVE или FORWARD
 */
function sendData(message, messageType){
    // проверка типа сообщения
    if( !['NEW', 'EDIT', 'REMOVE', 'FORWARD'].includes(messageType) ){
        alert('sendData(msgType): неверный аргумент msgType');
        throw 'sendData(msgType): неверный аргумент msgType';
    }
    // проверка сокета
    if(webSocket.readyState !== 1){
        alert('sendData(msgType): вебсокет не готов к обмену сообщениями');
        throw 'sendData(msgType): вебсокет не готов к обмену сообщениями';
    }
    // изменение типа сообщения для редактированных сообщений
    if(isEditMessage){
        messageType = 'EDIT';
        isEditMessage = false;
    }  
    // отправка сообщения на сервер
    if(message!==''){
        data = {'message':message, 'fromuser':publicClientUsername, 'chatId':openChatId,'chatType':chatType,'messageType':messageType};
         // для старых сообщений добавляется id сообщения
        if(['EDIT', 'REMOVE', 'FORWARD'].includes(messageType)){
            data.msgId = selectedMessage.getAttribute('data-chat_message_id');
        }
        // пересылка сообщения
        if(messageType == 'FORWARD'){
            data.touser = forwardedMessageRecipientName;
            data.creator = selectedMessage.getAttribute('data-fromuser');
            delete data['chatId'];
            delete data['msgId'];
        }
        webSocket.send(JSON.stringify(data));
    }
    messageInput.value = '';
}


/** удаление DOM узлов участников текущего выбранного группового чата */
function removeGroupPatricipantDOMElements(){
    let groupContactsElement = document.querySelector('.group__contacts');
    if(groupContactsElement) groupContactsElement.remove();
    // удаление кнопок добавления в группу у контактов-неучастников
    contactsContainer.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.remove());
}
/** создать DOM-элемент сообщения чата*/
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
    msgBlock.setAttribute('data-fromuser', data.fromuser);

    if(chatType==='dialog' && data.fromuser!==publicClientUsername && data.fromuser!==chatNameLabel.innerHTML){
        msgTable.innerHTML += `<tr><td class='msg__forwarding'>Переслано</td></tr>`;
    }
    msgTable.innerHTML += `<tr><td class="msg__text">${data.message}</td></tr>`;
    msgTable.innerHTML += `<tr><td class="msg__time">${localTime}</td></tr>`;
    if(chatType === 'discussion') msgTable.innerHTML += `<tr class='msg__tr-author'><td class='msg__author'>${data.fromuser}</td></tr>`;     // показ автора сообщения в групповом чате

    msgBlock.append(msgTable);
    chat.append(msgBlock);
}
/** создать DOM-элемент контакта списка контактов*/
function appendContactDOMElement(element){
    contactList.push({'username': element.username, 'chat_id': element.chat_id, 'user_id': element.user_id});

    // контейнер контакта
    let contact = document.createElement('div');    // блок контакта
    let contactImgBlock = document.createElement('div'); // блок изображения профиля
    let img = document.createElement('img'); // фото профиля
    let name = document.createElement('span'); // имя контакта

    contact.className = 'contact position-relative mb-2';
    contact.title = element.username;
    contactImgBlock.className = 'profile-img';
    img.className = 'contact__img img pe-2';
    name.className = 'contact__name';

    img.src = (element.user_photo == 'ava_profile.png' || element.user_photo == null) ? 'application/images/ava.png' : `application/data/profile_photos/${element.user_photo}`;
    name.innerHTML = element.username;

    contact.addEventListener('click', setGetMessages(contact, element.username, 'dialog'));
    contact.setAttribute('data-notice', element.notice);

    contactImgBlock.append(img);
    contact.append(contactImgBlock);
    contact.append(name);
    // добавление значка без уведомлений, если они отключены
    if(element.notice == 0){
        contact.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
    }

    contactsContainer.append(contact);
}
/** создать DOM-элемент группы списка групп
 * 
 * @param {*} group БД данные группы
 * @param {*} place куда добавить: START - начало списка, END - конец
 */
function appendGroupDOMElement(group, place='END'){
    groupList.push({'chat_name': group.chat_name, 'chat_id': group.chat_id});

    let groupsItem = document.createElement('div');
    groupsItem.className = 'group';
    groupsItem.title = group.chat_name;
    groupsItem.innerHTML = group.chat_name;
    groupsItem.addEventListener('click', setGetMessages(groupsItem, {'chat_id':group.chat_id, 'chat_name':group.chat_name}, 'discussion'));
    groupsItem.setAttribute('data-notice', group.chat_isnotice);

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
const showGroups = () => fetch('/get-groups').then(r=>r.json()).then(data => data.forEach(elem => appendGroupDOMElement(elem))); 


/** ОТКРЫТЬ ЧАТ ДИАЛОГА ИЛИ ГРУППОВОГО ЧАТА
 * 
 * @param {*} domElement DOM-элемент контакта или чата
 * @param {*} bdData данные элемента из БД
 * @param {*} type тип диалога
 * @returns 
 */
function setGetMessages(domElement, bdData, type){
    return function(){
        // если пересылается сообщение
        if(isForwaredMessage){
            // выбран контакт, кому переадресуется сообщение
            let contactNameElem = domElement.querySelector('.contact__name');
            if(contactNameElem){
                forwardedMessageRecipientElement = domElement;
                forwardedMessageRecipientName = contactNameElem.innerHTML.trim();      
                let contactRecipient = document.querySelector('.contact-recipient');
                if(contactRecipient) contactRecipient.classList.remove('contact-recipient');
                domElement.classList.add('contact-recipient');
            } 
            return;
        }

        // если открывается диалог или обсуждение для открытия переписки
        const urlParams = new URLSearchParams();
        if(type === 'dialog'){
            urlParams.set('contact', bdData);
            removeGroupPatricipantDOMElements();
        }
        else if(type === 'discussion'){
            urlParams.set('discussionid', bdData.chat_id);
            // показ участников группового чата
            fetch('/get-group-contacts', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data => {
                removeGroupPatricipantDOMElements();
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
                        plus.className = 'contact-addgroup';
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
       
        // показ сообщений диалога или чата
        fetch('/get-messages', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data=>{
            if(data){
                chat.innerHTML = '';

                chatType = data.type;
                openChatId = data.chatId;

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


/** Переотправить сообщение */
function forwardMessage(){
    forwardBtnBlock.classList.remove('btn-resend-block_active');    // скрыть блок кнопок переотправки
    sendData(selectedMessage.querySelector('.msg__text').innerHTML, 'FORWARD');
    forwardedMessageRecipientElement.classList.remove('contact-recipient'); // убрать выделение

    isForwaredMessage = null;
    forwardedMessageRecipientElement = null;
}
/** Отменяет пересылку сообщения */
function resetForwardMessage(){
    forwardBtnBlock.classList.remove('btn-resend-block_active');
    isForwaredMessage = null;
    forwardedMessageRecipient = null;
    selectedMessage = null;
    let contactRecipient = document.querySelector('.contact-recipient');
    if(contactRecipient) contactRecipient.classList.remove('contact-recipient');
}

// ----- Контекстное меню
/** показать контекстное меню */
function showContextMenu(contextMenu, event){
    contextMenu.style.left = event.pageX+'px';
    contextMenu.style.top = event.pageY+'px';
    contextMenu.style.display = 'block';
}
/** скрыть контекстное меню*/
function hideContextMenu(){
    msgContextMenu.style.left = '0px';
    msgContextMenu.style.top = '1000px';
    msgContextMenu.style.display = 'none';
    contactContextMenu.style.left = '100px';
    contactContextMenu.style.top = '1000px';
    contactContextMenu.style.display = 'none';
}
/** контекстное меню: изменить сообщение */
function editMessageContextMenu(){
    isEditMessage = true;
    hideContextMenu();
    messageInput.value = selectedMessage.querySelector('.msg__text').innerHTML;
    messageInput.focus();
}
/** контекстное меню: удалить сообщение  */
function removeMessageContextMenu(){
    let msg = selectedMessage.querySelector('.msg__text').innerHTML;
    sendData(msg, 'REMOVE');
    selectedMessage = null;
    hideContextMenu();
}
/** контекстное меню: переотправить сообщение */
function forwardMessageContextMenu(){
    hideContextMenu();
    isForwaredMessage = true;
    forwardBtnBlock.classList.add('btn-resend-block_active');
}
/** контекстное меню: включить/отключить уведомления */
function editNoticeShowContextMenu(){
    let data = {};
    data.notice = selectedContact.getAttribute('data-notice');
    if(selectedContact.className==='group'){
        data.name = selectedContact.innerHTML;
        data.type = 'discussion';
    }
    else{
        data.name = selectedContact.title;
        data.type = 'dialog';
    }
    console.clear();
    console.log(data);
    hideContextMenu();
}


window.addEventListener('DOMContentLoaded', () => {
    resetFindContactsBtn.onclick = showContacts;
    showContacts();
    showGroups();

    createGroupOption.onclick = () => fetch('/create-group').then(r=>r.json()).then(data => appendGroupDOMElement(data, 'START'));
    let pressedKeys = [];                                           // массив нажатых клавиш
    messageInput.onkeydown = event => pressedKeys.push(event.code); // нажатие клавиши
    document.oncontextmenu = function() {return false;}; // запрет контекстного меню

    // поиск пользователей-контактов в БД по введенному слову и отображение найденных контактов в списке контактов
    findContactsInput.addEventListener('input', function(){
        const urlParams = new URLSearchParams();
        urlParams.set('userphrase', this.value);
        fetch('/find-contacts', {method: 'POST', body: urlParams}).then(r=>r.json()).then(data => {
            contactsContainer.innerHTML = '';
            data.forEach(element => appendContactDOMElement(element));
        });
    });

    //----- ОТПРАВКА СООБЩЕНИЯ -----
    sendMsgBtn.onclick = () => sendData(messageInput.value, 'NEW');
    // отпускание клавиши при вводе сообщения
    messageInput.onkeyup = event => {
        // перевод строки, если Ctrl+Enter
        if(event.code === 'Enter' && pressedKeys.indexOf('ControlLeft') != -1){
            messageInput.value += '\n';
        }
        // отправка сообщения, если Enter
        else if(event.code === 'Enter'){
            sendData(messageInput.value.substring(0, messageInput.value.length-1), 'NEW');
        }
        pressedKeys.splice(pressedKeys.indexOf(event.code), 1);
    };
    chat.onscroll = hideContextMenu; // скрыть контекстное меню сообщения при прокрутке диалога

    editMsgContextMenuBtn.onclick = editMessageContextMenu;
    removeMsgContextMenuBtn.onclick = removeMessageContextMenu;
    forwardMsgContextMenuBtn.onclick = forwardMessageContextMenu;
    editNoticeShowContextMenuBtn.onclick = editNoticeShowContextMenu;

    forwardBtn.onclick = forwardMessage;
    resetForwardtBtn.onclick = resetForwardMessage;
});


// нажатия правой кнопкой мыши на странице
window.oncontextmenu = event => {
    // клик на элементе сообщения
    if(['msg__text', 'msg__time', 'msg__tr-author', 'msg__author', 'msg__forwarding'].includes(event.target.className)){  
        if(['msg__text', 'msg__time', 'msg__author'].includes(event.target.className)){
            selectedMessage = event.target.parentNode.parentNode.parentNode.parentNode;
        }
        else if(event.target.className === 'msg__forwarding'){
            selectedMessage = event.target.parentNode.parentNode.parentNode.parentNode.parentNode;
        }
        else{
            selectedMessage = event.target.parentNode.parentNode.parentNode;
        }

        showContextMenu(msgContextMenu, event);
        let msgUserhost = selectedMessage.getAttribute('data-fromuser');
        editMsgContextMenuBtn.style.display = msgUserhost !== publicClientUsername ? 'none' : 'block';
        removeMsgContextMenuBtn.style.display = msgUserhost !== publicClientUsername ? 'none' : 'block';
    }
    // клик на элементе контакта
    else if(['contact__name','contact__img img pe-2','contact position-relative mb-2','group'].includes(event.target.className)){
        if(event.target.className == 'contact__img img pe-2'){
            selectedContact = event.target.parentNode.parentNode;
        }
        else if(event.target.className == 'contact__name'){
            selectedContact = event.target.parentNode;
        }
        else{
            selectedContact = event.target;
        }

        let isNotice = selectedContact.getAttribute('data-notice');
        editNoticeShowContextMenuBtn.innerHTML = isNotice==1 ? 'Отключить уведомления' : 'Включить уведомления';
        showContextMenu(contactContextMenu, event);
    }
    else{
        hideContextMenu();
    }
};


// нажатия левой кнопкой мыши на странице
window.onclick = event => {
    if(event.target.parentNode.id !== 'send-msg-btn' && event.target.className !== 'list-group-item') messageInput.value = ''; // очистка поля ввода сообщения, если не нажата кнопка отправки сообщения
    if(event.target.className !== 'list-group-item') hideContextMenu();
};