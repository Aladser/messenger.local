/** Контейнер контактов */
class ContactContainer {
    siteAddr = "http://messenger.local/application/";
    errorDomElement = document.querySelector("#message-system");

    /** DOM-контейнер контактов */
    container = document.querySelector('#contacts');
    /** CSRF */
    CSRFElement;

    isSearch = false;
    contactList = [];

    /** Контейнер контактов
     * @param {*} contactsContainer контейнер контактов
     * @param {*} findContactsInput поле поиска контактов
     * @param {*} ws вебсокет
     * @param {*} chatWebsocket чат вебсокета 
     */
    constructor(container, CSRFElement) {
        this.container = container;
        this.CSRFElement = CSRFElement;
    }

    /** показать контакты пользователя браузера */
    show() {
        fetch('contact/get-contacts').then(resp => resp.text()).then(contacts => {
            contacts = parseJSONData(contacts);
            if (contacts !== undefined) {
                this.container.innerHTML = '';
                contacts.forEach(contact => {
                    this.addContactToList({'name': contact.name, 'chat': contact.chat, 'notice': contact.notice});
                    this.add(contact);
                });
            }
        });
    }

    /** поиск пользователей-контактов в БД по введенному слову и отображение найденных контактов в списке контактов */
    find(userphrase)
    {
        this.isSearch = true;
        let urlParams = new URLSearchParams();
        urlParams.set('userphrase', userphrase);
        urlParams.set('CSRF', this.CSRFElement.value);
        fetch('contact/find-contacts', {method: 'POST', body: urlParams}).then(resp => resp.text()).then(data => {
            data = parseJSONData(data);
            if (data !== undefined) {
                this.clear();
                data.forEach(contact => this.add(contact));
            }
        });
    }

    /** создать DOM-элемент контакта в списке контактов */
    add(contact)
    {
        // контейнер контакта
        let contactBlock = document.createElement('div');    // блок контакта
        let contactImgBlock = document.createElement('div'); // блок изображения профиля
        let img = document.createElement('img'); // фото профиля
        let name = document.createElement('span'); // имя контакта

        contactBlock.className = 'contact position-relative mb-2';
        contactBlock.title = contact.name;
        contactImgBlock.className = 'profile-img';
        img.className = 'contact__img img pe-2';
        name.className = 'contact__name';

        img.src = (contact.photo === 'ava_profile.png' || contact.photo == null) ? `${this.siteAddr}images/ava.png` : `${this.siteAddr}data/profile_photos/${contact.photo}`;
        name.innerHTML = contact.name;
        contactBlock.addEventListener('click', setContactOrGroupClick(contactBlock, contact.name, 'dialog'));
        contactBlock.setAttribute('data-notice', contact.notice);

        contactImgBlock.append(img);
        contactBlock.append(contactImgBlock);
        contactBlock.append(name);
        // добавление значка без уведомлений, если они отключены
        if (contact.notice == 0) {
            contactBlock.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
        }

        this.container.append(contactBlock);
    }

    /** удалить контакт из списка контактов */
    remove(contact, clientUsername)
    {
        let urlParams = new URLSearchParams();
        urlParams.set('name', contact.title);
        urlParams.set('type', contact.className === 'group' ? 'group' : 'contact');
        urlParams.set('CSRF', this.CSRFElement.value);
        urlParams.set('clientName', clientUsername);

        fetch('/contact/remove-contact', {method: 'POST', body: urlParams}).then(resp => resp.text()).then(data => {
            try {
                data = JSON.parse(data);
                if (parseInt(data.response) > 0) {
                    contact.remove();
                }
            } catch (err) {
                this.errorDomElement.innerHTML = data;
            }
        });
    }

    /** очистить контейнер */
    clear = () => this.container.innerHTML = '';
    /** добавить в фронт-список контактов */
    addContactToList = contact => this.contactList.push({'name': contact.name, 'chat': contact.chat, 'notice': contact.notice});
}