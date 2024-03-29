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
const publicAuthUsername = clientNamePrg.getAttribute('data-clientuser-publicname');
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
const forwardBtnArticle = document.querySelector('#btn-resend-block');
/** кнопка пересылки сообщения */
const forwardMessageButton = document.querySelector('#btn-resend');
/** кнопка отмены пересылки сообщения */
const resetForwardtBtn = document.querySelector('#btn-resend-reset');

/** тип выбранного чата для пересылки сообщения */
let forwardedMessageRecipientChatType = null;
/** имя выбранного чата для пересылки сообщения */
let forwardedMessageRecipientChatName = null;

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
addContactClickListeners();

// --- контейнер групп --- 
const groupContainer = new GroupContainer(
    document.querySelector('#group-chats'), 
    errorFrame, 
    csrfElement
);
groupContainer.get().forEach(group => {
    group.addEventListener('click', setClick(group, 'group'));
});

// --- контейнер сообщений ---
const messageContainer = new MessageContainer(
    document.querySelector("#messages"),
    errorFrame,
    csrfElement,
    document.querySelector('.messages-container__title')
);

// --- вебсокеты ---
const websocketAddr = document.querySelector("meta[name='websocket']").content;
const chatWebsocket = new ChatWebsocket(websocketAddr, contactContainer, groupContainer, messageContainer);

//** контекстное меню сообщения */
const messageContexMenu = new MessageContexMenu(document.querySelector('#msg-context-menu'),  chatWebsocket);
//** контекстное меню группы */
const contactContexMenu = new ContactContexMenu(document.querySelector('#contact-context-menu'), chatWebsocket, publicAuthUsername, csrfElement, contactContainer, groupContainer);

// ----- ЗАГРУЗКА СТРАНИЦЫ -----
window.addEventListener('DOMContentLoaded', () => {
    // переслать сообщение
    forwardMessageButton.onclick = forwardMessage;
    // сбросить пересылку сообщения
    resetForwardtBtn.onclick = resetForwardMessage;
    // создать групповой чат
    createGroupOption.onclick = () => groupContainer.add();
    // искать пользователей по фразе
    findContactsInput.oninput = findContacts;
    // сбросить поиск пользователей
    resetFindContactsBtn.onclick = resetSearch;
    // отправить сообщение
    sendMsgBtn.onclick = sendMessage;

    document.oncontextmenu = () => false;
    chat.onscroll = hideContexMenu;

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

// ----- НАЖАТЬ ЛЕВОЙ КНОПКОЙ МЫШИ НА СТРАНИЦЕ -----
window.onclick = event => {
    if (event.target.className !== 'list-group-item') {
        hideContexMenu();
    }
};

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
        messageContexMenu.editBtn.style.display = authUsername !== publicAuthUsername ? 'none' : 'block';
        // отображение кнопки - удалить сообщение
        messageContexMenu.removeBtn.style.display = authUsername !== publicAuthUsername ? 'none' : 'block'; 
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

/** ----- НАЖАТИЕ МЫШИ НА КОНТАКТЕ ИЛИ ГРУППОВОМ ЧАТЕ -----
 * @param {*} DOMNode HTML-элемент контакта или чата
 * @param {*} name имя контакта или группового чата
 * @param {*} type тип диалога
 * @returns
 */
function setClick(DOMNode, type)
{
    return function (event) {
        // прекращение всплытия кнопки добавления пользователя в группу
        if (event) {
            if (event.target.classList.contains('btn-add-to-group')) {
                return;
            }
        }
    
        let chatName = DOMNode.title;
        // удаляется уведомление о новом сообщении
        DOMNode.classList.remove('isnewmessage');

        // если пересылается сообщение, то показать, кому пересылается
        if (messageContexMenu.option == 'FORWARD') {
            // изменение интерфейса
            forwardMessageButton.classList.remove('border-secondary');
            forwardMessageButton.classList.remove('text-black-50');
            forwardMessageButton.classList.add('text-light');
            forwardMessageButton.disabled = false;
            // данные
            forwardedMessageRecipientChatType = type;
            forwardedMessageRecipientChatName = chatName;
        }

        // скрытие или показ контактов
        if (type === 'group') {
            groupContainer.click(DOMNode, contactContainer);
        }

        // --- показ сообщений
        messageContainer.show(chatName, type);
        chatWebsocket.chatOpenedType = messageContainer.chatType;
        chatWebsocket.chatOpenedName = messageContainer.chatName;
        
        messageInput.disabled = false;
        sendMsgBtn.disabled = false;
    };
}

/** ----- ИСКАТЬ ПОЛЬЗОВАТЕЛЕЙ ПО ФРАЗЕ----- */
async function findContacts() {
    await contactContainer.findUsers(findContactsInput.value);
    // слушатели событий для найденных пользователей
    contactContainer.get().forEach(contact => {
        contact.addEventListener('click', async function(e){
            resetSearch();
            // добавление пользователя в контакты, если отсутствует
            let user_name = this.title;
            if (!contactContainer.nameList.includes(user_name)) {
                // добавление пользователя в контакты
                let newContactDBData = await contactContainer.add(user_name);
                // создание DOM-узла нового контакта
                let newContactHTMLElement = contactContainer.createNode(newContactDBData);
                // новые слушатели клика контакта
                newContactHTMLElement.addEventListener('click', setClick(this, 'personal'));
                // новая сохраненная копия после добавления нового контакта
                contactContainer.backup();
            }

            setClick(this, 'personal')();
        });
    });
}

/** добавить слушателей кликов контактов */
function addContactClickListeners() {
    contactContainer.get().forEach(contact => {
        contact.addEventListener('click', setClick(contact, 'personal'));
    });
}

/** сбросить поиск пользователей */
function resetSearch() {
    contactContainer.isSearch = false;
    findContactsInput.value = '';
    contactContainer.restore();
    addContactClickListeners();
}

/** Переотправить сообщение */
function forwardMessage()
{
    let chatType = forwardedMessageRecipientChatType;
    let chatName = forwardedMessageRecipientChatName;
    let message_id = messageContexMenu.getSelectedMessageId();

    chatWebsocket.sendData(message_id, 'FORWARD', chatType, chatName);

    // скрыть блок кнопок переотправки
    forwardBtnArticle.classList.remove('btn-resend-block_active');
}

/** Отменяет пересылку сообщения */
function resetForwardMessage()
{
    forwardBtnArticle.classList.remove('btn-resend-block_active');
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

/** скрыть контекстные меню */
function hideContexMenu()
{
    contactContexMenu.hide();
    messageContexMenu.hide();
}