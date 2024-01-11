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
        this.chatType = type;
        this.chatName = chatName;
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
            chat.message_arr.forEach(messageData => this.createDOMNode(messageData, authUserName));
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

    // создать DOM-узел сообщения
    createDOMNode(messageData, username) {
        // показ местного времени
        // YYYY.MM.DD HH:ii:ss
        let timeInMs = Date.parse(messageData.time);
        let newDate = new Date(timeInMs);
        let localTime = newDate.toLocaleString("ru", {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
        }).replace(',', '');

        // показ переводов строки на странице
        let brIndex = messageData.message_text.indexOf('\n');
        while (brIndex > -1) {
            messageData.message_text = messageData.message_text.replace('\n', '<br>');
            brIndex = messageData.message_text.indexOf('\n');
        }

        let articleClassname, tableClassname, timeClassname;
        if (messageData.author_name === username) {
            articleClassname = 'msg';
            tableClassname = 'msg__table';
            timeClassname = 'msg__time';
        } else {
            articleClassname = 'msg d-flex justify-content-end';
            tableClassname = 'msg__table msg__table-contact text-white';
            timeClassname = 'msg__time text-theme-gray';
        }
        let forwardValue = messageData.forward ? messageData.forward : 0;

        let articleContent = `
            <article class="${articleClassname}" data-msg=${messageData.message_id} data-author=${messageData.author_name} data-forward=${forwardValue}>
                <table class="${tableClassname}">
        `;

        if (messageData.forward == 1 || messageData.message_type === 'FORWARD') {
            // надпись о пересланном сообщении
            articleContent += `<tr><td class='msg__forward'>Переслано</td></tr>`;
        }

        articleContent += `
                    <tr><td class="msg__text">${messageData.message_text}</td></tr>
                    <tr><td class="${timeClassname}">${localTime}</td></tr>
        `;

        if (messageData.chat_type === 'group') {
            // показ автора сообщения в групповом чате
            articleContent += `<tr class='msg__tr-author'><td class='msg__author'>${messageData.author_name}</td></tr>`;
        }

        articleContent += `       
                </table>
            </article>
        `;

        this.container.innerHTML += articleContent;
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