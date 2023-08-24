/** путь к папке приложения */
const APP_PATH = "http://messenger.local/application/";
/** элемент CSRF-токена */
const inputCsrf = document.querySelector('#input-csrf');
/** окно ошибок*/
const frameError = document.querySelector('#frame-error');

/** элемент имени клиента-пользователя*/
const clientNameBlock = document.querySelector('#clientuser');
/** почта пользователя-хоста */
const clientUsername = clientNameBlock.innerHTML.trim();
/** публичное имя пользователя-хоста */
const publicClientUsername = clientNameBlock.getAttribute('data-clientuser-publicname');

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

/** блок кнопок пересылки сообщения  */
const forwardBtnBlock = document.querySelector('#btn-resend-block');
/** кнопка пересылки сообщения */
const forwardBtn = document.querySelector('#btn-resend');
/** кнопка отмены пересылки сообщения */
const resetForwardtBtn = document.querySelector('#btn-resend-reset');

/** контекстное меню сообщения*/
const msgContextMenu = document.querySelector('#msg-context-menu');
/** кнопка контекстное меню: Редактировать сообщение*/
const editMsgContextMenuBtn = document.querySelector('#edit-msg');
/** кнопка контекстное меню: Удалить сообщение*/
const removeMsgContextMenuBtn = document.querySelector('#remove-msg');
/** кнопка контекстное меню: Переслать сообщение*/
const forwardMsgContextMenuBtn = document.querySelector('#resend-msg');
/** кнопка контексное меню контакта*/
const contactContextMenu = document.querySelector('#contact-context-menu');
/** кнопка контекстное меню: изменить показ уведомлений*/
const editNoticeShowContextMenuBtn = document.querySelector('#contact-notice-edit');
/** кнопка контекстное меню: удалить контакт-группу*/
const removeContactContextMenuBtn = document.querySelector('#contact-remove-contact');

/** Выбранный контакт или группа*/
let selectedContact = null;
/** DOM-элемент получателя пересланного письма*/
let forwardedMessageRecipientElement = null;
/** текущий тип чата*/
let chatType = null;
/** флаг измененного сообщения */
let isEditMessage = false;
/** флаг пересылаемого сообщения*/
let isForwaredMessage = false;
/** список участников выбранной группы */
let groupContacts = [];
/** массив нажатых клавиш */
let pressedKeys = [];

/** ВЕБСОКЕТ СООБЩЕНИЙ */
ws = new WebSocket('ws://localhost:8888');
const chatWebsocket = new ChatWebsocket(ws);

/** создать DOM-элемент сообщения чата*/
function appendMessage(data)
{
    // показ местного времени
    // YYYY.MM.DD HH:ii:ss
    let timeInMs = Date.parse(data.time);
    let newDate = new Date(timeInMs);
    let localTime = newDate.toLocaleString("ru", {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
    }).replace(',', '');

    // показ переводов строки на странице
    let brIndex = data.message.indexOf('\n');
    while (brIndex > -1) {
        data.message = data.message.replace('\n', '<br>');
        brIndex = data.message.indexOf('\n');
    }

    let msgBlock = document.createElement('div');
    let msgTable = document.createElement('table');

    msgBlock.className = data.author !== publicClientUsername ? 'msg d-flex justify-content-end' : 'msg';
    msgTable.className = data.author !== publicClientUsername ? 'msg__table msg__table-contact' : 'msg__table';
    msgBlock.setAttribute('data-msg', data.msg);
    msgBlock.setAttribute('data-author', data.author);
    msgBlock.setAttribute('data-forward', data.forward);

    if (data.forward == 1 || data.messageType === 'FORWARD') {
        msgTable.innerHTML += `<tr><td class='msg__forward'>Переслано</td></tr>`;
    } // надпись о пересланном сообщении
    msgTable.innerHTML += `<tr><td class="msg__text">${data.message}</td></tr>`; // текст сообщения
    msgTable.innerHTML += `<tr><td class="msg__time">${localTime}</td></tr>`;   // время сообщения
    if (chatType === 'discussion') {
        msgTable.innerHTML += `<tr class='msg__tr-author'><td class='msg__author'>${data.author}</td></tr>`;
    }     // показ автора сообщения в групповом чате

    msgBlock.append(msgTable);
    chat.append(msgBlock);
}

/** создать DOM-элемент контакта списка контактов*/
function appendContactDOMElement(contact)
{
    // контейнер контакта
    let contactBlock = document.createElement('div');    // блок контакта
    let contactImgBlock = document.createElement('div'); // блок изображения профиля
    let img = document.createElement('img'); // фото профиля
    let name = document.createElement('span'); // имя контакта

    contactBlock.className = 'contact position-relative mb-2';
    contactBlock.title = contact.name;
    contactImgBlock.className = 'profile-img';
    img.className = 'contact__img img pe-2';
    name.className = 'contact__name';

    img.src = (contact.photo === 'ava_profile.png' || contact.photo == null) ? `${APP_PATH}images/ava.png` : `${APP_PATH}data/profile_photos/${contact.photo}`;
    name.innerHTML = contact.name;
    contactBlock.addEventListener('click', setContactOrGroupClick(contactBlock, contact.name, 'dialog'));
    contactBlock.setAttribute('data-notice', contact.notice);

    contactImgBlock.append(img);
    contactBlock.append(contactImgBlock);
    contactBlock.append(name);
    // добавление значка без уведомлений, если они отключены
    if (contact.notice == 0) {
        contactBlock.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
    }

    contactsContainer.append(contactBlock);
}

/** создать DOM-элемент группового чата списка групповых чатов
 * @param {*} group БД данные группы
 * @param {*} place куда добавить: START - начало списка, END - конец
 */
function appendGroupDOMElement(group, place = 'END')
{
    let groupsItem = document.createElement('div');
    groupsItem.className = 'group';
    groupsItem.title = group.name;
    groupsItem.innerHTML = group.name;
    groupsItem.addEventListener('click', setContactOrGroupClick(groupsItem, group.chat, 'discussion'));
    groupsItem.setAttribute('data-notice', group.notice);

    if (place === 'START') {
        groupChatsContainer.prepend(groupsItem);
    } else if (place === 'END') {
        groupChatsContainer.append(groupsItem);
    }

    if (group.notice == 0) {
        groupsItem.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
    }
}

/** парсинг JSON-данных */
function parseJSONData(data)
{
    try {
        data = JSON.parse(data);
        return data;
    } catch (err) {
        frameError.classList.add('frame-error--active');
        frameError.innerHTML = data;
        return  undefined;
    }
}

/** показать контакты пользователя-клиента*/
const showContacts = () => fetch('contact/get-contacts').then(r => r.text()).then(data => {
    data = parseJSONData(data);
    if (data !== undefined) {
        findContactsInput.value = '';
        contactsContainer.innerHTML = '';
        contactList = [];
        data.forEach(contact => {
            chatWebsocket.addContact({'name': contact.name, 'chat': contact.chat, 'notice': contact.notice});
            appendContactDOMElement(contact);
        });
    }
});

/** показать групповые чаты пользователя-клиента */
const showGroups = () => fetch('chat/get-groups').then(r => r.text()).then(data => {
    data = parseJSONData(data);
    if (data !== undefined) {
        groupList = [];
        data.forEach(group => {
            chatWebsocket.addGroup({'name': group.name, 'chat': group.chat, 'notice': group.notice});
            appendGroupDOMElement(group);
        });
    }
});

/** поиск пользователей-контактов в БД по введенному слову и отображение найденных контактов в списке контактов */
function findContacts()
{
    let urlParams = new URLSearchParams();
    urlParams.set('userphrase', this.value);
    urlParams.set('CSRF', inputCsrf.value);
    fetch('contact/find-contacts', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
        data = parseJSONData(data);
        if (data !== undefined) {
            contactsContainer.innerHTML = '';
            data.forEach(element => appendContactDOMElement(element));
        }
    });
}

/** показать участников группового чата*/
const showGroupRecipients = (domElement, discussionid) => {
    let urlParams = new URLSearchParams();
    urlParams.set('discussionid', discussionid);
    urlParams.set('CSRF', inputCsrf.value);
    fetch('contact/get-group-contacts', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
        data = parseJSONData(data);
        if (data === undefined) {
            return;
        }

        // создание DOM-списка участников группового чата
        let prtBlock = document.createElement('div'); // блок, где будут показаны участники группы
        prtBlock.className = 'group__contacts';
        domElement.append(prtBlock);
        groupContacts = [];
        // создается список участников группового чата
        data.participants.forEach(prt => {
            prtBlock.innerHTML += `<p class='group__contact'>${prt.publicname}</p>`;
            groupContacts.push(prt.publicname);
        });

        // добавить новые кнопки добавления в группу у контактов-неучастников выбранной группы
        contactsContainer.querySelectorAll('.contact').forEach(cnt => {
            let cntName = cnt.lastChild.innerHTML;
            if (!groupContacts.includes(cntName)) {
                let plus = document.createElement('div');
                plus.className = 'contact-addgroup';
                plus.innerHTML = '+';
                plus.title = 'добавить в групповой чат';

                // добавить пользователя в группу
                plus.onclick = e => {
                    let username = e.target.parentNode.childNodes[1].innerHTML; // имя пользователя
                    e.stopPropagation();    // прекратить всплытие событий

                    let urlParams2 = new URLSearchParams();
                    urlParams2.set('discussionid', discussionid);
                    urlParams2.set('username', username);
                    fetch('contact/create-group-contact', {method: 'POST', body: urlParams2}).then(r => r.text()).then(data => {
                        let isCreated = parseInt(data);
                        if (isCreated === 1) {
                            e.target.parentNode.lastChild.remove();
                            domElement.lastChild.innerHTML += `<p class='group__contact'>${username}</p>`;
                        } else {
                            alert(data);
                            console.log(data);
                        }
                    });
                }

                cnt.append(plus);
            }
        });
    });
};


/** показать сообщения */
const showChat = (urlParams, bdChatName, type) => {
    urlParams.set('CSRF', inputCsrf.value);
    fetch('chat/get-messages', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
        data = parseJSONData(data);
        if (data === undefined) {
            return;
        } else if (data) {
            chat.innerHTML = '';

            chatType = data.type;
            chatWebsocket.setOpenChatOpenChatId(data.current_chat);

            chatNameTitle.innerHTML = type === 'dialog' ? 'Чат с пользователем ' : 'Обсуждение ';
            chatNameLabel.innerHTML = bdChatName;

            messageInput.disabled = false;
            sendMsgBtn.disabled = false;

            data.messages.forEach(elem => appendMessage(elem));// сообщения
            chat.scrollTo(0, chat.scrollHeight); // прокрутка сообщений в конец
        }
    });
};


/** показать на странице получателя пересылаемого сообщения*/
function showForwardedMessageRecipient(contactDomElem)
{
    let contactNameElem = contactDomElem.querySelector('.contact__name');
    if (contactNameElem) {
        forwardedMessageRecipientElement = contactDomElem;
        chatWebsocket.forwardedMessageRecipientName = contactNameElem.innerHTML.trim();
        let contactRecipient = document.querySelector('.contact-recipient');
        if (contactRecipient) {
            contactRecipient.classList.remove('contact-recipient');
        }
        contactDomElem.classList.add('contact-recipient');
        forwardBtn.disabled = false;
    }
}

/** удаление DOM узлов участников текущего выбранного группового чата */
function removeGroupPatricipantDOMElements()
{
    let groupContactsElement = document.querySelector('.group__contacts');
    if (groupContactsElement) {
        groupContactsElement.remove();
    }
    // удаление кнопок добавления в группу у контактов-неучастников
    contactsContainer.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.remove());
}

/** НАЖАТИЕ МЫШИ НА КОНТАКТЕ ИЛИ ГРУППОВОМ ЧАТЕ
 * @param {*} domElement DOM-элемент контакта или чата
 * @param {*} urlArg что ищется: контакт или групповой чат
 * @param {*} type тип диалога
 * @returns
 */
function setContactOrGroupClick(domElement, urlArg, type)
{
    return function () {
        // если пересылается сообщение, показать, кому пересылается
        if (isForwaredMessage) {
            showForwardedMessageRecipient(domElement);
            isForwaredMessage = false;
            return;
        }

        // если открывается диалог или обсуждение для открытия переписки
        domElement.classList.remove('isnewmessage'); // если есть класс нового сообщения, удаляется

        let urlParams = new URLSearchParams();
        let groupChatName;
        if (type === 'dialog') {
            urlParams.set('contact', urlArg);
            urlParams.set('CSRF', inputCsrf.value);
            removeGroupPatricipantDOMElements();
            // поиск пользователя в массиве контактов на клиенте и добавление, если отсутствует
            fetch('/contact/get-contact', {method: 'POST', body: urlParams}).then(r => r.text()).then(dbContact => {
                dbContact = parseJSONData(dbContact);
                if (dbContact === undefined) {
                    return;
                }

                let contact = contactList.find(elem => elem.chat == dbContact.chat_id);
                if (contact === undefined) {
                    contactList.push(dbContact);
                }
            });
        } else if (type === 'discussion') {
            urlParams.set('discussionid', urlArg);
            removeGroupPatricipantDOMElements();
            showGroupRecipients(domElement, urlArg) // показать участников группового чата
            let groupChat = groupList.find(el => el.chat == urlArg);
            if (groupChat !== undefined) {
                groupChatName = groupChat.name;
            }
        } else {
            return;
        }

        showChat(urlParams, type === 'dialog' ? urlArg : groupChatName, type); // показать чат
    };
}


/** Переотправить сообщение */
function forwardMessage()
{
    chatWebsocket.sendData(chatWebsocket.getSelectedMessageText(), 'FORWARD');

    forwardBtnBlock.classList.remove('btn-resend-block_active');    // скрыть блок кнопок переотправки
    forwardedMessageRecipientElement.classList.remove('contact-recipient'); // убрать выделение

    isForwaredMessage = null;
    forwardedMessageRecipientElement = null;
}

/** Отменяет пересылку сообщения */
function resetForwardMessage()
{
    forwardBtnBlock.classList.remove('btn-resend-block_active');
    isForwaredMessage = null;
    chatWebsocket.setSelectedMessage(null);
    let contactRecipient = document.querySelector('.contact-recipient');
    if (contactRecipient) {
        contactRecipient.classList.remove('contact-recipient');
    }
    forwardBtn.disabled = true;
}


// ***** Контекстное меню *****
/** показать контекстное меню */
function showContextMenu(contextMenu, event)
{
    contextMenu.style.left = event.pageX + 'px';
    contextMenu.style.top = event.pageY + 'px';
    contextMenu.style.display = 'block';
}

/** скрыть контекстное меню*/
function hideContextMenu()
{
    msgContextMenu.style.left = '0px';
    msgContextMenu.style.top = '1000px';
    msgContextMenu.style.display = 'none';
    contactContextMenu.style.left = '100px';
    contactContextMenu.style.top = '1000px';
    contactContextMenu.style.display = 'none';
}

/** контекстное меню: изменить сообщение */
function editMessageContextMenu()
{
    isEditMessage = true;
    hideContextMenu();
    messageInput.value = chatWebsocket.getSelectedMessageText();
    messageInput.focus();
}

/** контекстное меню: удалить сообщение  */
function removeMessageContextMenu()
{
    let msg = chatWebsocket.getSelectedMessageText();
    chatWebsocket.sendData(msg, 'REMOVE');
    chatWebsocket.setSelectedMessage(null);
    hideContextMenu();
}

/** контекстное меню: переотправить сообщение */
function forwardMessageContextMenu()
{
    hideContextMenu();
    isForwaredMessage = true;
    forwardBtnBlock.classList.add('btn-resend-block_active');
}

/** контекстное меню: включить/отключить уведомления */
function editNoticeShowContextMenu()
{
    // создание пакета с id чата, значением статуса показа уведомлений
    let data = {};

    if (selectedContact.className === 'group') {
        // поиск выбранного группового чата
        data.chat = groupList.find(el => el.name === selectedContact.title).chat;
    } else {
        //  поиск выбранного контакта
        let name = selectedContact.querySelector('.contact__name').innerHTML;
        data.chat = contactList.find(el => el.name === name).chat;
    }
    data.notice = selectedContact.getAttribute('data-notice') == 1 ? 0 : 1; //инвертирование значения. Это значение будет записано в БД
    hideContextMenu();

    // отправка данных на сервер
    let urlParams = new URLSearchParams();
    urlParams.set('chat_id', data.chat);
    urlParams.set('notice', data.notice);
    urlParams.set('username', clientUsername);
    urlParams.set('CSRF', inputCsrf.value);
    // изменяет установленный флаг получения уведомлений
    fetch('/chat/edit-notice-show', {method: 'post', body: urlParams}).then(r => r.text()).then(notice => {
        notice = parseJSONData(notice);
        if (notice === undefined) {
            return;
        } else {
            notice = notice.responce;
        }
        console.log(notice);

        notice = parseInt(notice);
        selectedContact.setAttribute('data-notice', notice);  // меняем атрибут
        let elem;
        if (selectedContact.classList.contains('contact')) {
            // если контакт, то изменяем значение в массиве контактов

            elem = contactList.find(el => el.name === selectedContact.title);
        } else if (selectedContact.className === 'group') {
            // если групповой чат, то изменяем значение в массиве групповых чатов

            elem = groupList.find(el => el.name === selectedContact.title);
        }
        elem.notice = notice;

        // изменение визуального уведомления
        if (notice === 1) {
            selectedContact.querySelector('.notice-soundless').remove();
        } else {
            selectedContact.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
        }
    });
}

/** контекстное меню: удалить контакт/групповой чат */
function removeContactContextMenu()
{
    let urlParams = new URLSearchParams();
    urlParams.set('name', selectedContact.title);
    urlParams.set('type', selectedContact.className === 'group' ? 'group' : 'contact');
    urlParams.set('CSRF', inputCsrf.value);
    if (selectedContact.className !== 'group') {
        urlParams.set('clientName', clientUsername);
    }
    hideContextMenu();

    fetch('/contact/remove-contact', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
        try {
            data = JSON.parse(data);
        } catch (err) {
            console.log(data);
        }
        
        if (parseInt(data.response) > 0) {
            selectedContact.remove();
        }
    });
}

/** отправить сообщение на сервер*/
function sendMessage()
{
    if (isEditMessage) {
        chatWebsocket.sendData(messageInput.value, 'EDIT');
        isEditMessage = false;
    } else {
        chatWebsocket.sendData(messageInput.value, 'NEW');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    resetFindContactsBtn.onclick = showContacts;
    showContacts();
    showGroups();
    // создание группового чата
    createGroupOption.onclick = () => fetch('chat/create-group').then(r => r.json()).then(data => {
        groupList.push({'name': data.name, 'chat':data.chat, 'notice': 1});
        appendGroupDOMElement(data, 'START');
    });

    // запрет контекстного меню
    document.oncontextmenu = () => false;

    // поиск пользователей-контактов в БД по введенному слову и отображение найденных контактов в списке контактов
    findContactsInput.addEventListener('input', findContacts);

    //----- ОТПРАВКА СООБЩЕНИЯ -----
    sendMsgBtn.onclick = sendMessage;

    // нажатие клавиши в поле ввода сообщения
    messageInput.onkeydown = event => {
        if (event.code === 'Enter' || event.code === 'ControlLeft') {
            pressedKeys.push(event.code);
        }
    };

    // отпускание клавиши в поле ввода сообщения
    messageInput.onkeyup = event => {
        // перевод строки, если Ctrl+Enter
        if (event.code === 'Enter' && pressedKeys.indexOf('ControlLeft') !== -1) {
            messageInput.value += '\n';
        } else if (event.code === 'Enter') {
            // отправка сообщения, если Enter

            // если поседний символ - перевод строки
            if (messageInput.value[messageInput.value.length - 1] === '\n') {
                messageInput.value = messageInput.value.substring(0, messageInput.value.length - 1);
            }
            sendMessage();
        }
        pressedKeys.splice(pressedKeys.indexOf(event.code), 1);
    };
    chat.onscroll = hideContextMenu; // скрыть контекстное меню сообщения при прокрутке диалога

    editMsgContextMenuBtn.onclick = editMessageContextMenu;
    removeMsgContextMenuBtn.onclick = removeMessageContextMenu;
    forwardMsgContextMenuBtn.onclick = forwardMessageContextMenu;
    editNoticeShowContextMenuBtn.onclick = editNoticeShowContextMenu;
    removeContactContextMenuBtn.onclick = removeContactContextMenu;

    forwardBtn.onclick = forwardMessage;
    resetForwardtBtn.onclick = resetForwardMessage;
});


// нажатия правой кнопкой мыши на странице
window.oncontextmenu = event => {
    if (['msg__text', 'msg__time', 'msg__tr-author', 'msg__author', 'msg__forward'].includes(event.target.className)) {
        // клик на элементе сообщения
        let messageDOM = null;
        if (['msg__text', 'msg__time', 'msg__author'].includes(event.target.className)) {
            messageDOM = event.target.parentNode.parentNode.parentNode.parentNode;
        } else if (event.target.className === 'msg__forward') {
            messageDOM = event.target.parentNode.parentNode.parentNode.parentNode;
        } else {
            messageDOM = event.target.parentNode.parentNode.parentNode;
        }
        chatWebsocket.setSelectedMessage(messageDOM);

        showContextMenu(msgContextMenu, event);
        let msgUserhost = chatWebsocket.getSelectedMessageAuthor();
        // отображение кнопки - изменить сообщение
        editMsgContextMenuBtn.style.display = msgUserhost !== publicClientUsername ? 'none' : 'block';
        // отображение кнопки - удалить сообщение
        removeMsgContextMenuBtn.style.display = msgUserhost !== publicClientUsername ? 'none' : 'block';

        if (chatWebsocket.isForwardedSelectedMessage()) {
            editMsgContextMenuBtn.style.display = 'none';
        }
    } else if (['contact__name', 'contact__img img pe-2', 'contact position-relative mb-2', 'group', 'notice-soundless'].includes(event.target.className)) {
        // клик на элементе контакта
        if (event.target.className === 'contact__img img pe-2') {
            selectedContact = event.target.parentNode.parentNode;
        } else if (event.target.className === 'contact__name' || event.target.className === 'notice-soundless') {
            selectedContact = event.target.parentNode;
        } else {
            selectedContact = event.target;
        }

        let isNotice = selectedContact.getAttribute('data-notice');
        // показ кнопки - включение/выключение уведомлений
        editNoticeShowContextMenuBtn.innerHTML = isNotice == 1 ? 'Отключить уведомления' : 'Включить уведомления';
        // показ кнопки - удалить группу
        removeContactContextMenuBtn.innerHTML= event.target.className === 'group' ? 'Удалить группу' : 'Удалить контакт';

        showContextMenu(contactContextMenu, event);
    } else {
        hideContextMenu();
    }
};


// нажатия левой кнопкой мыши на странице
window.onclick = event => {
    if (event.target.className !== 'list-group-item') {
        hideContextMenu();
    }
};
