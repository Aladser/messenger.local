/** CSRF-токен */
const csrfElement = document.querySelector("meta[name='csrf']");
/** поле поиска пользователя */
const findContactsInput = document.querySelector('#find-contacts-input');
/** окно ошибок*/
const errorFrame = document.querySelector('#frame-error');
/** элемент имени клиента-пользователя*/
const clientNamePrg = document.querySelector('#clientuser');
/** почта пользователя-хоста */
const clientUsername = clientNamePrg.innerHTML.trim();
/** публичное имя пользователя-хоста */
const publicClientUsername = clientNamePrg.getAttribute('data-clientuser-publicname');
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
const forwardMessageButton = document.querySelector('#btn-resend');
/** кнопка отмены пересылки сообщения */
const resetForwardtBtn = document.querySelector('#btn-resend-reset');
/** DOM-элемент получателя пересланного письма*/
let forwardedMessageRecipientElement = null;

/** список участников выбранной группы */
let groupContacts = [];
/** массив нажатых клавиш */
let pressedKeys = [];

/** ----- контейнер контактов ----- */
const contacts = new ContactContainer(
    document.querySelector('#contacts'), 
    errorFrame, 
    csrfElement
);
contacts.get().forEach(contact => {
    contact.addEventListener('click', setClick(contact, 'dialog'));
});

// --- контейнер групп --- 
const groups = new GroupContainer(
    document.querySelector('#group-chats'), 
    errorFrame, 
    csrfElement
);
groups.get().forEach(group => {
    group.addEventListener('click', setClick(group, 'discussion'));
});

// --- вебсокет ---
const websocketAddr = document.querySelector("meta[name='websocket']").content;
const chatWebsocket = new ChatWebsocket(websocketAddr, contacts, groups);

// --- контейнер сообщений ---
const messages = new MessageContainer(
    document.querySelector("#messages"),
    errorFrame,
    csrfElement,
    chatWebsocket,
    document.querySelector('.messages-container__title')
);

//** контекстное меню сообщения */
const messageContexMenu = new MessageContexMenu(document.querySelector('#msg-context-menu'),  chatWebsocket);
//** контекстное меню группы */
const contactContexMenu = new ContactContexMenu(document.querySelector('#contact-context-menu'), chatWebsocket, publicClientUsername, csrfElement, contacts, groups);

// ----- ЗАГРУЗКА СТРАНИЦЫ -----
window.addEventListener('DOMContentLoaded', () => {
    // пересылка сообщения
    forwardMessageButton.onclick = forwardMessage;
    // сброс пересылки сообщения
    resetForwardtBtn.onclick = resetForwardMessage;
    // ----- Поиск пользователей -----
    findContactsInput.oninput = findContacts;
    
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
 * @param {*} domElement HTML-элемент контакта или чата
 * @param {*} name имя контакта или группового чата
 * @param {*} type тип диалога
 * @returns
 */
function setClick(domElement, type)
{
    return function () {
        let name = domElement.title;
        // удаляется уведомление о новом сообщении
        domElement.classList.remove('isnewmessage');

        // если пересылается сообщение, показать, кому пересылается
        if (messageContexMenu.option == 'FORWARD') {
            forwardedMessageRecipientElement = messages.showForwardedMessageRecipient(domElement);
            if (forwardedMessageRecipientElement) {
                forwardMessageButton.disabled = false;
            }
            return;
        }

        let urlParams = new URLSearchParams();
        if (type === 'dialog') {
            urlParams.set('contact', name);
            urlParams.set('CSRF', csrfElement.content);
            contacts.find(name);
        } else if (type === 'discussion') {
            let id = domElement.id;
            urlParams.set('discussionid', id.substring(id.indexOf('-')+1));
        } else {
            return;
        }

        messages.show(urlParams, name, type, publicClientUsername);
        messageInput.disabled = false;
        sendMsgBtn.disabled = false;
    };
}

/** ----- ПОИСК ПОЛЬЗОВАТЕЛЕЙ ----- */
async function findContacts() {
    await contacts.findUsers(findContactsInput.value);
    // слушатели событий для найденных пользователей
    contacts.get().forEach(contact => {
        contact.addEventListener('click', async function(){
            findContactsInput.value = '';
            // показ контактов пользователя
            contacts.restore();
            // новое навешивание слушателей событий
            contacts.get().forEach(contact => {
                contact.addEventListener('click', setClick(contact, 'dialog'));
            });
            // добавление пользователя в контакты, если отсутствует
            let userName = this.title;
            if (!contacts.nameList.includes(userName)) {
                let newContactDBData = await contacts.add(userName);
                let newContactHTMLElement = contacts.create(newContactDBData);
                newContactHTMLElement.addEventListener('click', setClick(this, 'dialog'));
            }

            setClick(this, 'dialog')();
        });
    });
}

/** Переотправить сообщение */
function forwardMessage()
{
    chatWebsocket.sendData(chatWebsocket.getSelectedMessageText(), 'FORWARD');
    // скрыть блок кнопок переотправки
    forwardBtnBlock.classList.remove('btn-resend-block_active');
     // убрать выделение
    forwardedMessageRecipientElement.classList.remove('contact-recipient');

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
    forwardMessageButton.disabled = true;
}

/** отправить сообщение на сервер*/
function sendMessage()
{
    if(messageInput.value == '') {
        return;
    }
    if (messageContexMenu.option === 'EDIT') {
        chatWebsocket.sendData(messageInput.value, 'EDIT');
        messageContexMenu.option = false;
    } else {
        chatWebsocket.sendData(messageInput.value, 'NEW');
    }
}

// нажатия правой кнопкой мыши на странице
window.oncontextmenu = event => {
    console.log('клик:');
    console.log(event.target);
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
    } else if (['contact__name', 'contact__img img pe-2', 'contact position-relative mb-2', 'group text-white', 'group__contact', 'notice-soundless'].includes(event.target.className)) {
        // клик на элементе контакта
        if (event.target.className === 'contact__img img pe-2') {
            contactContexMenu.selectedContact = event.target.parentNode.parentNode;
        } else if (event.target.className === 'contact__name' || event.target.className === 'notice-soundless') {
            contactContexMenu.selectedContact = event.target.parentNode;
        } else {
            contactContexMenu.selectedContact = event.target;
        }
        console.log('цель:');
        console.log(contactContexMenu.selectedContact);

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
        errorFrame.classList.add('frame-error--active');
        errorFrame.innerHTML = data;
        return  undefined;
    }
}

/** скрыть контекстные меню */
function hide()
{
    contactContexMenu.hide();
    messageContexMenu.hide();
}