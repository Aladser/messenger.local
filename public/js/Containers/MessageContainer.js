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
        let brIndex = data.message_text.indexOf('\n');
        while (brIndex > -1) {
            data.message_text = data.message_text.replace('\n', '<br>');
            brIndex = data.message_text.indexOf('\n');
        }

        let articleClassname = data.author_name !== username ? 'msg d-flex justify-content-end' : 'msg';
        let tableClassname = data.author_name !== username ? 'msg__table msg__table-contact text-white' : 'msg__table';
        let forwardValue = data.forward ? data.forward : 0;
        let timeClassname = data.author_name !== username ? "msg__time text-theme-gray" : "msg__time";

        let articleContent = `
            <article class="${articleClassname}" data-msg=${data.message_id} data-author="${data.author_name}" data-forward=${forwardValue}>
                <table class="${tableClassname}">
        `;

        if (data.forward == 1 || data.message_type === 'FORWARD') {
            // надпись о пересланном сообщении
            articleContent += `<tr><td class='msg__forward'>Переслано</td></tr>`;
        }

        articleContent += `
                    <tr><td class="msg__text">${data.message_text}</td></tr>
                    <tr><td class="${timeClassname}">${localTime}</td></tr>
        `;

        if (chatType === 'group') {
            // показ автора сообщения в групповом чате
            articleContent += `<tr class='msg__tr-author'><td class='msg__author'>${data.author_name}</td></tr>`;
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