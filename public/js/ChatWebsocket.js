class ChatWebsocket
{
    errorPrg = document.querySelector("#message-system");
    publicUsername = document.querySelector('#clientuser').getAttribute('data-clientuser-publicname');
    chat = document.querySelector("#messages");
    messageInput = document.querySelector("#message-input");
    selectedMessage = null;
    forwardedMessageRecipientName = null;
    chatType = null;
    openChatId = -1;
    openChatName = null;

    constructor(websocketAddr, contacts, groups, messages)
    {
        this.websocket = new WebSocket(websocketAddr);
        this.websocket.onerror = this.onError;
        this.websocket.onmessage = e => this.onMessage(e);
        this.contacts = contacts;
        this.groups = groups;
        this.messages = messages;
        this.baseSiteName= window.location.origin;
    }

    // получение ошибок вебсокета
    onError() {
        this.errorPrg.innerHTML = 'Ошибка подключения к серверу';
    }

    onMessage(e)
    {
        let data = JSON.parse(e.data);
        //console.clear();
        //console.log(data);
        
        if (data.onconnection) {
            // --- сообщение от сервера о подключении текущего пользователя

            // Передача имени пользователя и ID подключения текущего пользователя серверу
            this.websocket.send(JSON.stringify({
                'messageOnconnection': 1,
                'author': clientUsername,
                'wsId': data.onconnection
            }));
        } else if (data.messageOnconnection) {
            // --- сообщение себе и контактам пользователя о подключении клиента

            if (data.author) {
                let username = data.author === this.publicUsername ? 'Вы' : data.author;
                this.errorPrg.innerHTML = `${username} в сети`;
            } else {
                // ошибки подключения
                this.errorPrg.innerHTML = `${data.systeminfo}`;
            }
        } else if (data.offconnection && data.user != null) {
            // --- сообщение контактам пользователя об отключении

            this.errorPrg.innerHTML = `${data.user} не в сети`;
        } else {
            // --- сообщение с сервера

            // уведомления о новых сообщениях чатов
            if ((data.messageType === 'NEW' || data.messageType === 'FORWARD') && data.author !== this.publicUsername) {
                let senderDOMNode = document.querySelector(`article[title='${data.author}']`);
                // визуальное уведомление
                if (this.openChatId !== data.chat) {
                    senderDOMNode.classList.add('isnewmessage');
                }
                // звуковое уведомление
                // сделано специально множественное создание объектов звука
                let senderChatNotice = senderDOMNode.getAttribute('data-notice');
                if (senderChatNotice == 1) {
                    let notice = new Audio(this.baseSiteName + '/public/notice.wav');
                    notice.autoplay = true;
                }
            }
    
            // сообщения открытого чата
            if (this.openChatId == data.chat) {
                // изменение сообщения
                if (data.messageType === 'EDIT') {
                    let messageDOMElem = document.querySelector(`[data-msg="${data.msg}"]`);
                    messageDOMElem.querySelector('.msg__text').innerHTML = data.message;
                } else if (data.messageType === 'REMOVE') {
                    // удаление сообщения
                    let messageDOMElem = document.querySelector(`[data-msg="${data.msg}"]`);
                    messageDOMElem.remove();
                } else {
                    // новое сообщение
                    messages.createDOMNode(this.chatType, data, this.publicUsername);
                }
            }
        }
    }

    /** Отправить сообщение на сервер
     * @param message текст сообщения
     * @param messageType тип сообщения: NEW, EDIT, REMOVE или FORWARD
     */
    sendData(message, messageType)
    {
        // проверка сокета
        if (this.websocket.readyState !== 1) {
            alert('sendData(msgType): вебсокет не готов к обмену сообщениями');
            throw 'sendData(msgType): вебсокет не готов к обмену сообщениями';
        }
    
        // отправка сообщения на сервер
        if (message !== '') {
            let data = {
                'messageType': messageType,
                'message': message,
                'author': this.publicUsername,
                'chat': this.openChatId,
                'chatType': this.chatType
            };
            // для старых сообщений добавляется id сообщения
            if (['EDIT', 'REMOVE'].includes(messageType)) {
                data.msgId = parseInt(this.selectedMessage.getAttribute('data-msg'));
            }
    
            if (messageType === 'FORWARD') {
                data.chat = this.contacts.list.find(el => el.name === this.forwardedMessageRecipientName).chat; // чат, куда пересылается
                delete data['chatType'];
            }
            console.clear();
            console.log(data);
            this.websocket.send(JSON.stringify(data));
        }
        this.messageInput.value = '';
    }

    // получить текст выбранного сообщения
    getSelectedMessageText = () => this.selectedMessage.querySelector('.msg__text').innerHTML;
    // получить автора выбранного сообщения
    getSelectedMessageAuthor = () => this.selectedMessage.getAttribute('data-author');
    // проверить - сообщение переслано?
    isForwardedSelectedMessage = () => this.selectedMessage.getAttribute('data-forward') == 1;
}