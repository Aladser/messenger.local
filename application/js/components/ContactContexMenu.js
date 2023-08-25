/** контексное меню контакта*/
class ContactContexMenu
{
    constructor(contextMenuDOM, option, selectedContac, chatWSt) {
        suprer(contextMenuDOM, option);
        this.selectedContact = selectedContact;
        this.chatWS = chatWSt;
    }

    /** контекстное меню: включить/отключить уведомления */
    editNoticeShowContextMenu()
    {
        // создание пакета с id чата, значением статуса показа уведомлений
        let data = {};

        if (this.selectedContact.className === 'group') {
            // поиск выбранного группового чата
            data.chat = groupList.find(el => el.name === this.selectedContact.title).chat;
        } else {
            //  поиск выбранного контакта
            let name = this.selectedContact.querySelector('.contact__name').innerHTML;
            data.chat = chatWebsocket.contactList.find(el => el.name === name).chat;
        }
        data.notice = this.selectedContact.getAttribute('data-notice') == 1 ? 0 : 1; //инвертирование значения. Это значение будет записано в БД
        hideContextMenu();

        // отправка данных на сервер
        let urlParams = new URLSearchParams();
        urlParams.set('chat_id', data.chat);
        urlParams.set('notice', data.notice);
        urlParams.set('username', clientUsername);
        urlParams.set('CSRF', inputCsrf.value);
        // изменяет установленный флаг получения уведомлений
        fetch('/chat/edit-notice-show', {method: 'post', body: urlParams}).then(r => r.text()).then(notice => {
            notice = parseJSONData(notice);
            if (notice === undefined) {
                return;
            } else {
                notice = notice.responce;
            }

            notice = parseInt(notice);
            this.selectedContact.setAttribute('data-notice', notice);  // меняем атрибут
            let elem;
            if (this.selectedContact.classList.contains('contact')) {
                // если контакт, то изменяем значение в массиве контактов

                elem = chatWebsocket.contactList.find(el => el.name === this.selectedContact.title);
            } else if (this.selectedContact.className === 'group') {
                // если групповой чат, то изменяем значение в массиве групповых чатов

                elem = groupList.find(el => el.name === this.selectedContact.title);
            }
            elem.notice = notice;

            // изменение визуального уведомления
            if (notice === 1) {
                this.selectedContact.querySelector('.notice-soundless').remove();
            } else {
                this.selectedContact.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
            }
        });
    }

    /** контекстное меню: удалить контакт/групповой чат */
    removeContactContextMenu()
    {
        let urlParams = new URLSearchParams();
        urlParams.set('name', selectedContact.title);
        urlParams.set('type', selectedContact.className === 'group' ? 'group' : 'contact');
        urlParams.set('CSRF', inputCsrf.value);
        if (selectedContact.className !== 'group') {
            urlParams.set('clientName', clientUsername);
        }
        hideContextMenu();

        fetch('/contact/remove-contact', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
            try {
                data = JSON.parse(data);
            } catch (err) {
                console.log(data);
            }
            
            if (parseInt(data.response) > 0) {
                selectedContact.remove();
            }
        });
    }
}