/** Контейнер сообщений */
class MessageContainer extends TemplateContainer{
    chatType = false;
    chatName = false;

    constructor(container, errorPrg, CSRFElement, chatWebsocket, msgContainerTitle) {
        super(container, errorPrg, CSRFElement);
        this.chatWebsocket = chatWebsocket;
        this.title = msgContainerTitle;
        this.chatOpened = false;
    }

    // показать сообщения текущего чата
    show(chatName, type) {
        this.chatType = chatName;
        this.chatName = type;
        let urlParams = new URLSearchParams();
        urlParams.set('CSRF', this.getCSRF());
        urlParams.set('chat_name', chatName);
        urlParams.set('type', type);

        let process = (data) => {
            let chat;
            try {
                chat = JSON.parse(data);
            } catch(err) {
                console.log(err);
                console.log(data)
                return;
            }
            this.removeElements();
    
            let authUserName = chat.public_auth_username;
            let chatHeader = type === 'personal' ? 'Чат с пользователем ' : 'Обсуждение ';
            this.title.innerHTML = `
                <p class='messages-container__title'>
                    <span id='chat-title' class='text-white'>${chatHeader}</span>
                    <span class='chat-username text-white' id='chat-username'>${chatName}</span>
                </p>
            `;

            // сообщения
            chat.message_arr.forEach(message => this.createDOMNode(type, message, authUserName));
            // прокрутка сообщений в конец
            this.container.scrollTo(0, this.container.scrollHeight);
        };

        ServerRequest.execute(
            'chat/get-messages',
            process,
            "post",
            null,
            urlParams
        );
    };

    createDOMNode(chatType, data, username) {
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

        let msgBlock = document.createElement('article');
        let msgTable = document.createElement('table');

        msgBlock.className = data.author !== username ? 'msg d-flex justify-content-end' : 'msg';
        msgTable.className = data.author !== username ? 'msg__table msg__table-contact text-white' : 'msg__table';
        msgBlock.setAttribute('data-msg', data.msg);
        msgBlock.setAttribute('data-author', data.author);
        msgBlock.setAttribute('data-forward', data.forward ? data.forward : 0);

        // надпись о пересланном сообщении
        if (data.forward == 1 || data.messageType === 'FORWARD') {
            msgTable.innerHTML += `<tr><td class='msg__forward'>Переслано</td></tr>`;
        }
        // текст сообщения
        msgTable.innerHTML += `<tr><td class="msg__text">${data.message}</td></tr>`;
        // время сообщения
        let timeClassname = data.author !== username ? "msg__time text-theme-gray" : "msg__time";
        msgTable.innerHTML += `<tr><td class="${timeClassname}">${localTime}</td></tr>`;
        if (chatType === 'group') {
            // показ автора сообщения в групповом чате
            msgTable.innerHTML += `<tr class='msg__tr-author'><td class='msg__author'>${data.author}</td></tr>`;
        }

        msgBlock.append(msgTable);
        this.container.append(msgBlock);
    }

    /** показать на странице получателя пересылаемого сообщения
     * @param {*} contactDomElem
     * @returns получатель
     */
    showForwardedMessageRecipient(contactDomElem)
    {
        let contactNameElem = contactDomElem.querySelector('.contact__name');
        if (contactNameElem) {
            this.chatWebsocket.forwardedMessageRecipientName = contactNameElem.innerHTML.trim();
            let contactRecipient = document.querySelector('.contact-recipient');
            if (contactRecipient) {
                contactRecipient.classList.remove('contact-recipient');
            }
            contactDomElem.classList.add('contact-recipient');
            return contactDomElem;
        }
        return false;
    }

}