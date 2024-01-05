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

/** css классы элементов контакта */
let contactClassnameList = [
    'contact__name', 
    'contact__img', 
    'contact', 
    'group', 
    'group__contact'
];
/** css классы элементов сообщения */
let messageClassnameList = [
    'msg__text',
    'msg__time',
    'msg__author', 
    'msg__forward'
];

/** ----- контейнер контактов ----- */
const contactContainer = new ContactContainer(
    document.querySelector('#contacts'), 
    errorFrame, 
    csrfElement
);
contactContainer.get().forEach(contact => {
    contact.addEventListener('click', setClick(contact, 'dialog'));
});

// --- контейнер групп --- 
const groupContainer = new GroupContainer(
    document.querySelector('#group-chats'), 
    errorFrame, 
    csrfElement
);
groupContainer.get().forEach(group => {
    group.addEventListener('click', setClick(group, 'discussion'));
});

// --- вебсокет ---
const websocketAddr = document.querySelector("meta[name='websocket']").content;
const chatWebsocket = new ChatWebsocket(websocketAddr, contactContainer, groupContainer);

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
const contactContexMenu = new ContactContexMenu(document.querySelector('#contact-context-menu'), chatWebsocket, publicClientUsername, csrfElement, contactContainer, groupContainer);

// ----- ЗАГРУЗКА СТРАНИЦЫ -----
window.addEventListener('DOMContentLoaded', () => {
    // пересылка сообщения
    forwardMessageButton.onclick = forwardMessage;
    // сброс пересылки сообщения
    resetForwardtBtn.onclick = resetForwardMessage;
    // создание группового чата
    createGroupOption.onclick = () => groupContainer.add();
    
    // ----- Поиск пользователей -----
    findContactsInput.oninput = findContacts;
    
    document.oncontextmenu = () => false;
    sendMsgBtn.onclick = sendMessage;
    chat.onscroll = hideContexMenu;

    resetFindContactsBtn.onclick = () => {
        contactContainer.isSearch = false;
        findContactsInput.value = '';
        contactContainer.show();
    }

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
    return function (event) {
        // прекращение всплытия кнопки добавления пользователя в группу
        if (event) {
            if (event.target.classList.contains('btn-add-to-group')) {
                return;
            }
        }
        
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
        switch(type) {
            case 'dialog':
                urlParams.set('contact', name);
                urlParams.set('CSRF', csrfElement.content);
                break;
            case 'discussion':
                let id = domElement.id;
                urlParams.set('discussionid', id.substring(id.indexOf('-')+1));
                groupContainer.click(domElement, contactContainer);
                break;
            default:
                return;
        }

        // показ сообщений
        messages.show(urlParams, name, type, publicClientUsername);
        messageInput.disabled = false;
        sendMsgBtn.disabled = false;
    };
}

/** ----- ПОИСК ПОЛЬЗОВАТЕЛЕЙ ----- */
async function findContacts() {
    await contactContainer.findUsers(findContactsInput.value);
    // слушатели событий для найденных пользователей
    contactContainer.get().forEach(contact => {
        contact.addEventListener('click', async function(e){
            findContactsInput.value = '';
            // очистить результаты поиска
            contactContainer.restore();
            // новое навешивание слушателей событий
            contactContainer.get().forEach(contact => {
                contact.addEventListener('click', setClick(contact, 'dialog'));
            });
            // добавление пользователя в контакты, если отсутствует
            let user_name = this.title;
            if (!contactContainer.nameList.includes(user_name)) {
                let newContactDBData = await contactContainer.add(user_name);
                let newContactHTMLElement = contactContainer.create(newContactDBData);
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

// ----- НАЖАТЬ ПРАВОЙ КНОПКОЙ МЫШИ НА СТРАНИЦЕ -----
window.oncontextmenu = function(event) {
    let classNameArray = [... event.target.classList];
    // найденные классы контакта
    let foundContactClassnameList = contactClassnameList.filter(className => classNameArray.includes(className));
    // найденные контакты сообщения
    let foundMessageClassnameList = messageClassnameList.filter(className => classNameArray.includes(className));

    if (foundMessageClassnameList.length != 0) {
        // поиск элемента, по которому кликнули
        chatWebsocket.selectedMessage = event.target.closest('article');
        // аутентифицированный пользователь
        let authUsername = chatWebsocket.getSelectedMessageAuthor();
        // отображение кнопки - изменить сообщение
        messageContexMenu.editBtn.style.display = authUsername !== publicClientUsername ? 'none' : 'block';
        // отображение кнопки - удалить сообщение
        messageContexMenu.removeBtn.style.display = authUsername !== publicClientUsername ? 'none' : 'block'; 
        if (chatWebsocket.isForwardedSelectedMessage()) {
            messageContexMenu.editBtn.style.display = 'none';
        }

        messageContexMenu.show(event);
    } else if (foundContactClassnameList.length != 0 || classNameArray.includes('notice-soundless')) {
        // поиск элемента, по которому кликнули
        contactContexMenu.selectedContact = event.target.closest('article');
        let isNotice = contactContexMenu.selectedContact.getAttribute('data-notice');
        // показ кнопки - включение/выключение уведомлений
        contactContexMenu.editNoticeShowBtn.innerHTML = isNotice == 1 ? 'Отключить уведомления' : 'Включить уведомления';
        // показ кнопки - удалить группу
        contactContexMenu.removeContactBtn.innerHTML = event.target.className === 'group' ? 'Удалить группу' : 'Удалить контакт';

        contactContexMenu.show(event);
    } else {
        hideContexMenu();
    }
};

// нажать левой кнопкой мыши на странице
window.onclick = event => {
    if (event.target.className !== 'list-group-item') {
        hideContexMenu();
    }
};

/** скрыть контекстные меню */
function hideContexMenu()
{
    contactContexMenu.hide();
    messageContexMenu.hide();
}