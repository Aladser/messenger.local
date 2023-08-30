/** контексное меню контакта*/
class ContactContexMenu extends ContexMenu
{
    contactContexOption;
    selectedContact;

    constructor(contexMenuDOM, chatWS, clientUsername, inputCsrf, contacts, groups) {
        super(contexMenuDOM);
        this.contacts = contacts;
        this.chatWS = chatWS;
        this.clientUsername = clientUsername;
        this.inputCsrf = inputCsrf;
        this.groups = groups;
        
        this.editNoticeShowBtn = contexMenuDOM.childNodes[1].childNodes[1]; 
        this.editNoticeShowBtn.onclick = () => this.editNoticeShow();
        this.removeContactBtn = contexMenuDOM.childNodes[1].childNodes[3]; 
        this.removeContactBtn.onclick = () => this.removeContact();
    }

    /** изменить звуковые уведомления
     * @param {*} clientUsername имя килента браузера
     * @param {*} inputCsrf  CSRF
     */
    editNoticeShow() {
        // создание пакета с id чата, значением статуса показа уведомлений
        let data = {};

        if (this.selectedContact.className === 'group') {
            // поиск выбранного группового чата
            data.chat = this.groups.groupList.find(el => el.name === this.selectedContact.title).chat;
        } else {
            //  поиск выбранного контакта
            let name = this.selectedContact.querySelector('.contact__name').innerHTML;
            data.chat = this.contacts.list.find(el => el.name === name).chat;
        }
        data.notice = this.selectedContact.getAttribute('data-notice') == 1 ? 0 : 1; //инвертирование значения. Это значение будет записано в БД
        this.hide();

        // отправка данных на сервер
        let urlParams = new URLSearchParams();
        urlParams.set('chat_id', data.chat);
        urlParams.set('notice', data.notice);
        urlParams.set('username', this.clientUsername);
        urlParams.set('CSRF', this.inputCsrf.value);
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
                elem = this.contacts.list.find(el => el.name === this.selectedContact.title);
            } else if (this.selectedContact.className === 'group') {
                // если групповой чат, то изменяем значение в массиве групповых чатов
                elem = this.groups.groupList.find(el => el.name === this.selectedContact.title);
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

    /** удалить контакт/групповой чат */
    removeContact() {
        if (this.selectedContact.classList.contains('contact')) {
            this.contacts.remove(this.selectedContact, this.clientUsername);
        } else {
            this.groups.remove(this.selectedContact);
        }
        this.hide();
    }
}