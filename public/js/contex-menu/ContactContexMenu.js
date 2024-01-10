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
        let isGroup = this.selectedContact.classList.contains('group');
        let type = isGroup ? 'group' : 'personal';
        //инвертирование значения уведомления. Это значение будет записано в БД
        let noticeAttr = this.selectedContact.getAttribute('data-notice');
        let notice = noticeAttr == 1 ? 0 : 1;  

        this.hide();

        // отправка данных на сервер
        let urlParams = new URLSearchParams();
        urlParams.set('type', type);
        urlParams.set('notice', notice);
        urlParams.set('chat_name', this.selectedContact.title);
        urlParams.set('CSRF', this.csrfInput.content);

        let process = (data) => {
            try{
                let isUpdated = JSON.parse(data).result == 1;
                if(isUpdated) {
                    this.selectedContact.setAttribute('data-notice', notice);
                }
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
            } catch (err) {
                console.log(data);
                console.log(err);
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