/** элемент CSRF-токена */
const inputCsrf = document.querySelector('#input-csrf');
/** поле поиска пользователя */
const findContactsInput = document.querySelector('#find-contacts-input');

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

const contacts = new ContactContainer(document.querySelector('#contacts'), frameError, inputCsrf);
const groups = new GroupContainer(document.querySelector('#group-chats'), frameError, inputCsrf);

const ws = new WebSocket('ws://localhost:8888');
const chatWebsocket = new ChatWebsocket(ws, contacts, groups);

const messages = new MessageContainer(
    document.querySelector("#messages"),
    frameError,
    inputCsrf,
    chatWebsocket,
    document.querySelector('.messages-container__title')
    );

const messageContexMenu = new MessageContexMenu(document.querySelector('#msg-context-menu'),  chatWebsocket);
const contactContexMenu = new ContactContexMenu(document.querySelector('#contact-context-menu'), chatWebsocket, publicClientUsername, inputCsrf, contacts, groups);

window.addEventListener('DOMContentLoaded', () => {
    contacts.show();
    groups.show();

    forwardBtn.onclick = forwardMessage;
    resetForwardtBtn.onclick = resetForwardMessage;

    findContactsInput.oninput = () => contacts.find(findContactsInput.value);
    document.oncontextmenu = () => false;
    sendMsgBtn.onclick = sendMessage;
    chat.onscroll = hide;

    resetFindContactsBtn.onclick = () => {
        contacts.isSearch = false;
        findContactsInput.value = '';
        contacts.show();
    }

    // создание группового чата
    createGroupOption.onclick = () => fetch('chat/create-group').then(resp => resp.json()).then(data => {
        groups.addGroupToList({'name': data.name, 'chat':data.chat, 'notice': 1});
        groups.add(data, 'START');
    });

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
            forwardedMessageRecipientElement = messages.showForwardedMessageRecipient(domElement);
            if (forwardedMessageRecipientElement) {
                forwardBtn.disabled = false;
            }
            return;
        }

        // если открывается диалог или обсуждение для открытия переписки
        domElement.classList.remove('isnewmessage'); // если есть класс нового сообщения, удаляется

        let urlParams = new URLSearchParams();
        let groupChatName;
        groups.removeGroupPatricipants();
        if (type === 'dialog') {
            urlParams.set('contact', urlArg);
            urlParams.set('CSRF', inputCsrf.value);
            contacts.check(urlArg);
        } else if (type === 'discussion') {
            urlParams.set('discussionid', urlArg);
            groups.showGroupRecipients(domElement, urlArg) // показать участников группового чата
            let groupChat = groups.list.find(el => el.chat == urlArg);
            if (groupChat !== undefined) {
                groupChatName = groupChat.name;
            }
        } else {
            return;
        }

        // если поиск контакта
        if (contacts.isSearch) {
            contacts.isSearch = false;
            findContactsInput.value = '';
            contacts.show();
        }
        
        messages.show(urlParams, type === 'dialog' ? urlArg : groupChatName, type, publicClientUsername);
        messageInput.disabled = false;
        sendMsgBtn.disabled = false;
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