/** Контейнер контактов */
class MessageContainer extends TemplateContainer{
    constructor(container, errorPrg, CSRFElement, chatWebsocket, msgContainerTitle) {
        super(container, errorPrg, CSRFElement);
        this.chatWebsocket = chatWebsocket;
        this.title = msgContainerTitle;
    }

    show(urlParams, dbChatName, type, publicClientUsername) {
        urlParams.set('CSRF', inputCsrf.value);
        fetch('chat/get-messages', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
            data = parseJSONData(data);
            if (data === undefined) {
                return;
            } else if (data) {
                this.clear();
    
                this.chatWebsocket.chatType = data.type;
                this.chatWebsocket.openChatId = data.current_chat;
    
                let chatHeader = type === 'dialog' ? 'Чат с пользователем ' : 'Обсуждение ';
                let chatName = dbChatName;
                this.title.innerHTML = `<p class='messages-container__title'><span id='chat-title'>${chatHeader}</span><span class='chat-username' id='chat-username'>${chatName}</span></p>`;
    
                // сообщения
                data.messages.forEach(elem => {
                    this.add(type, elem, publicClientUsername);
                });
                this.container.scrollTo(0, this.container.scrollHeight); // прокрутка сообщений в конец
            }
        });
    };

    add(chatType, data, clientUsername) {
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

        msgBlock.className = data.author !== clientUsername ? 'msg d-flex justify-content-end' : 'msg';
        msgTable.className = data.author !== clientUsername ? 'msg__table msg__table-contact' : 'msg__table';
        msgBlock.setAttribute('data-msg', data.msg);
        msgBlock.setAttribute('data-author', data.author);
        msgBlock.setAttribute('data-forward', data.forward);

        if (data.forward == 1 || data.messageType === 'FORWARD') {
            msgTable.innerHTML += `<tr><td class='msg__forward'>Переслано</td></tr>`;
        } // надпись о пересланном сообщении
        msgTable.innerHTML += `<tr><td class="msg__text">${data.message}</td></tr>`; // текст сообщения
        msgTable.innerHTML += `<tr><td class="msg__time">${localTime}</td></tr>`;   // время сообщения
        if (chatType === 'discussion') {
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