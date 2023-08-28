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
/** контейнер сообщений */
const chat = document.querySelector("#messages");
/** элемент начальной подписи чата */
const chatNameTitle = document.querySelector('#chat-title');
/** С кем открыт чат */
const chatNameLabel = document.querySelector('#chat-username');
/** кнопка создать групповой чат */
const createGroupOption = document.querySelector('#create-group-option');
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
/** DOM-элемент получателя пересланного письма*/
let forwardedMessageRecipientElement = null;

/** список участников выбранной группы */
let groupContacts = [];
/** массив нажатых клавиш */
let pressedKeys = [];
/** флаг поиска */
let isSearch = false;

/** поле поиска пользователя */
const findContactsInput = document.querySelector('#find-contacts-input');
/** контейнер контактов */
const contacts = new ContactContainer(document.querySelector('#contacts'), findContactsInput, inputCsrf);
/** вебсокет */
const ws = new WebSocket('ws://localhost:8888');
/** вебсокет сообщений */
const chatWebsocket = new ChatWebsocket(ws, contacts.contactList);

/** контекстные меню */
const messageContexMenu = new MessageContexMenu(document.querySelector('#msg-context-menu'),  chatWebsocket);
const contactContexMenu = new ContactContexMenu(document.querySelector('#contact-context-menu'), chatWebsocket, publicClientUsername, inputCsrf, contacts);

window.addEventListener('DOMContentLoaded', () => {
    findContactsInput.oninput = () => contacts.find();
    resetFindContactsBtn.onclick = () => {
        isSearch = false;
        contacts.show();
    }

    chat.onscroll = () => hide();

    forwardBtn.onclick = forwardMessage;
    resetForwardtBtn.onclick = resetForwardMessage;
    // запрет контекстного меню
    document.oncontextmenu = () => false;

    contacts.show();
    showGroups();

    // создание группового чата
    createGroupOption.onclick = () => fetch('chat/create-group').then(r => r.json()).then(data => {
        groupList.push({'name': data.name, 'chat':data.chat, 'notice': 1});
        ChatDOMElementCreator.group(groupChatsContainer, data, 'START');
    });

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
        if (event.code === 'Enter') {
            if (pressedKeys.indexOf('ControlLeft') !== -1) {
                messageInput.value += '\n';
            } else {
                // отправка сообщения, если Enter
                // если поседний символ - перевод строки
                if (messageInput.value[messageInput.value.length - 1] === '\n') {
                    messageInput.value = messageInput.value.substring(0, messageInput.value.length - 1);
                }
                sendMessage();
            }
        }
        pressedKeys.splice(pressedKeys.indexOf(event.code), 1);
    };
});

/** показать групповые чаты пользователя-клиента */
const showGroups = () => fetch('chat/get-groups').then(r => r.text()).then(data => {
    data = parseJSONData(data);
    if (data !== undefined) {
        groupList = [];
        data.forEach(group => {
            chatWebsocket.addGroup({'name': group.name, 'chat': group.chat, 'notice': group.notice});
            ChatDOMElementCreator.group(groupChatsContainer, group);
        });
    }
});

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
        contacts.container.querySelectorAll('.contact').forEach(cnt => {
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

            chatWebsocket.chatType = data.type;
            chatWebsocket.openChatId = data.current_chat;

            chatNameTitle.innerHTML = type === 'dialog' ? 'Чат с пользователем ' : 'Обсуждение ';
            chatNameLabel.innerHTML = bdChatName;

            messageInput.disabled = false;
            sendMsgBtn.disabled = false;

            // сообщения
            data.messages.forEach(elem => {
                ChatDOMElementCreator.message(chat, type, elem, publicClientUsername);
            });
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
    contacts.container.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.remove());
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
        if (messageContexMenu.option == 'FORWARD') {
            showForwardedMessageRecipient(domElement);
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

                let contact = chatWebsocket.contactList.find(elem => elem.chat == dbContact.chat_id);
                if (contact === undefined) {
                    contacts.addContactList(dbContact);
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

        // если поиск контакта
        if (contacts.isSearch) {
            contacts.isSearch = false;
            contacts.show();
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

    messageContexMenu.option = false;
    forwardedMessageRecipientElement = null;
}

/** Отменяет пересылку сообщения */
function resetForwardMessage()
{
    forwardBtnBlock.classList.remove('btn-resend-block_active');
    messageContexMenu.option = false;

    chatWebsocket.selectedMessage = null;
    let contactRecipient = document.querySelector('.contact-recipient');
    if (contactRecipient) {
        contactRecipient.classList.remove('contact-recipient');
    }
    forwardBtn.disabled = true;
}

/** отправить сообщение на сервер*/
function sendMessage()
{
    if (messageContexMenu.option === 'EDIT') {
        chatWebsocket.sendData(messageInput.value, 'EDIT');
        messageContexMenu.option = false;
    } else {
        chatWebsocket.sendData(messageInput.value, 'NEW');
    }
}

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
        chatWebsocket.selectedMessage = messageDOM;

        messageContexMenu.show(event);
        let msgUserhost = chatWebsocket.getSelectedMessageAuthor();
        // отображение кнопки - изменить сообщение
        messageContexMenu.editBtn.style.display = msgUserhost !== publicClientUsername ? 'none' : 'block';
        // отображение кнопки - удалить сообщение
        messageContexMenu.removeBtn.style.display = msgUserhost !== publicClientUsername ? 'none' : 'block'; 
        if (chatWebsocket.isForwardedSelectedMessage()) {
            messageContexMenu.editBtn.style.display = 'none';
        }
    } else if (['contact__name', 'contact__img img pe-2', 'contact position-relative mb-2', 'group', 'notice-soundless'].includes(event.target.className)) {
        // клик на элементе контакта
        if (event.target.className === 'contact__img img pe-2') {
            contactContexMenu.selectedContact = event.target.parentNode.parentNode;
        } else if (event.target.className === 'contact__name' || event.target.className === 'notice-soundless') {
            contactContexMenu.selectedContact = event.target.parentNode;
        } else {
            contactContexMenu.selectedContact = event.target;
        }

        let isNotice = contactContexMenu.selectedContact.getAttribute('data-notice');
        // показ кнопки - включение/выключение уведомлений
        contactContexMenu.editNoticeShowBtn.innerHTML = isNotice == 1 ? 'Отключить уведомления' : 'Включить уведомления';
        // показ кнопки - удалить группу
        contactContexMenu.removeContactBtn.innerHTML = event.target.className === 'group' ? 'Удалить группу' : 'Удалить контакт';

        contactContexMenu.show(event);
    } else {
        hide();
    }
};

// нажатия левой кнопкой мыши на странице
window.onclick = event => {
    if (event.target.className !== 'list-group-item') {
        hide();
    }
};

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

/** скрыть контекстные меню */
function hide()
{
    contactContexMenu.hide();
    messageContexMenu.hide();
}