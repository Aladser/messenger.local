/** Контексное меню контакта */
class ContactContexMenu extends ContexMenu
{
    contactContexOption;
    selectedContact;

    /** Контексное меню контакта
     * 
     * @param {*} contexMenuHTMLElement DOM-элемент контекстного меню
     * @param {*} chatWS вебсокет
     * @param {*} clientUsername уимя аутентифицированного пользователя
     * @param {*} csrfInput CSRF
     * @param {*} contactContainer ContactContainer
     * @param {*} groupContainer GroupContainer
     */
    constructor(contexMenuHTMLElement, chatWS, clientUsername, csrfInput, contactContainer, groupContainer) {
        super(contexMenuHTMLElement);
        this.chatWS = chatWS;
        this.clientUsername = clientUsername;
        this.csrfInput = csrfInput;
        this.contactContainer = contactContainer;
        this.groupContainer = groupContainer;

        let menuItems = contexMenuHTMLElement.querySelectorAll('.list-group-item');
        // кнопка - изменить уведомления
        this.editNoticeShowBtn = menuItems[0];
        this.editNoticeShowBtn.onclick = () => this.editNoticeShow();
        // кнопка - удалить
        this.removeContactBtn = menuItems[1];
        this.removeContactBtn.onclick = () => this.removeContact();
    }

    /** изменить звуковые уведомления
     * @param {*} clientUsername имя килента браузера
     * @param {*} inputCsrf  CSRF
     */
    editNoticeShow() {
        // создание пакета с id чата, значением статуса показа уведомлений
        let data = {};

        if (this.selectedContact.classList.contains('group')) {
            // поиск выбранного группового чата
            data.chat = this.groupContainer.list.find(el => el.name === this.selectedContact.title).chat;
        } else {
            //  поиск выбранного контакта
            let name = this.selectedContact.querySelector('.contact__name').innerHTML;
            data.chat = this.contactContainer.list.find(el => el.name === name).chat;
        }
        data.notice = this.selectedContact.getAttribute('data-notice') == 1 ? 0 : 1; //инвертирование значения. Это значение будет записано в БД
        this.hide();

        // отправка данных на сервер
        let urlParams = new URLSearchParams();
        urlParams.set('chat_id', data.chat);
        urlParams.set('notice', data.notice);
        urlParams.set('username', this.clientUsername);
        urlParams.set('CSRF', this.csrfInput.content);

        let process = (notice) => {
            notice = JSON.parse(notice);

            if (notice === undefined) {
                return;
            } else {
                notice = notice.responce;
            }
            this.selectedContact.setAttribute('data-notice', notice);
            
            let elem;
            if (this.selectedContact.classList.contains('contact')) {
                // если контакт, то изменяем значение в массиве контактов
                elem = this.contactContainer.list.find(el => el.name === this.selectedContact.title);
            } else if (this.selectedContact.classList.contains('group')) {
                // если групповой чат, то изменяем значение в массиве групповых чатов
                elem = this.groupContainer.list.find(el => el.name === this.selectedContact.title);
            }
            elem.notice = notice;

            // изменение визуального уведомления
            if (notice === 1) {
                this.selectedContact.querySelector('.notice-soundless').remove();
            } else {
                this.selectedContact.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
            }
        };

        ServerRequest.execute(
            '/chat/edit-notice-show',
            process,
            "post",
            null,
            urlParams
        );
    }

    /** удалить контакт/групповой чат */
    removeContact() {
        if (this.selectedContact.classList.contains('contact')) {
            this.contactContainer.remove(this.selectedContact, this.clientUsername);
        } else if (this.selectedContact.classList.contains('group')){
            this.groupContainer.remove(this.selectedContact);
        } else {
            return 'неверный аргумент ContactContexMenu.removeContact()';
        }
        this.hide();
    }
}