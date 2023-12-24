/** Контейнер контактов */
class ContactContainer extends TemplateContainer{
    #backupContainer;
    siteAddr = this.baseSiteName + "/application/";
    isSearch = false;
    nameList = [];

    constructor(container, errorPrg, CSRFElement) {
        super(container, errorPrg, CSRFElement);
        this.get().forEach(contact => {
            let element = {
                'name': contact.title, 
                'chat': contact.id.substring(contact.id.lastIndexOf('-')+1), 
                'notice': contact.getAttribute('data-notice')
            };
            this.list.push(element);
            this.nameList.push(contact.title);
        });
        this.backup();
    }

    get() {
        return this.container.querySelectorAll('.contact');
    }

    show() {
        let process = (contacts) => {
            contacts = JSON.parse(contacts);
            if (contacts !== undefined) {
                this.removeElements();
                contacts.forEach(contact => {
                    let element = {'name': contact.name, 'chat': contact.chat, 'notice': contact.notice};
                    this.list.push(element);
                    this.create(contact);
                });
            }
        };

        ServerRequest.execute(
            'contact/get-contacts',
            process,
            "get"
        );
    }

    /** поиск пользователей по фразе
     * 
     * @param {*} userphrase часть имени пользователя
     */
    async findUsers(userphrase) {
        this.isSearch = true;

        let urlParams = new URLSearchParams();
        urlParams.set('userphrase', userphrase);
        urlParams.set('CSRF', this.CSRFElement.content);

        // показ найденных пользователей
        let process = data => {
            data = JSON.parse(data);
            this.removeElements();
            data.forEach(contact => this.create(contact));
        }

        await ServerRequest.execute(
            'contact/find',
            process,
            "post",
            null,
            urlParams
        );
    }

    /** поиск контакта и добавление, если отсутствует */
    find(username) {
        let urlParams = new URLSearchParams();
        urlParams.set('contact', username);
        urlParams.set('CSRF', this.CSRFElement.content);

        let process = (dbContact) => {
            dbContact = JSON.parse(dbContact);
            let contact = this.list.find(elem => elem.chat == dbContact.chat_id);
            if (contact === undefined) {
                let element = {'name': dbContact.name, 'chat': dbContact.chat, 'notice': dbContact.notice};
                this.list.push(element);
            }
        }

        ServerRequest.execute(
            '/contact/get-contact',
            process,
            "post",
            null,
            urlParams
        );
    }

    /** создать HTML-код контакта  */
    /*
        <article class="contact position-relative mb-2" title="Barashka" data-notice="1" id="chat-107">
            <div class="profile-img">
                <img class="contact__img img pe-2" src="http://messenger.local/application/data/profile_photos/senddlyamobille@gmail.com.1.jpg">
            </div>
            <span class="contact__name">Barashka</span>
        </article>
    */
    create(contact) {
        // контейнер контакта
        let contactHTMLElement = document.createElement('article');    // блок контакта
        let contactImgBlock = document.createElement('div'); // блок изображения профиля
        let img = document.createElement('img'); // фото профиля
        let name = document.createElement('span'); // имя контакта

        contactHTMLElement.className = 'contact position-relative mb-2';
        contactHTMLElement.id = 'chat-' + contact.id;
        contactHTMLElement.title = contact.name;
        contactImgBlock.className = 'profile-img';
        img.className = 'contact__img img pe-2';
        name.className = 'contact__name';

        img.src = (contact.photo === 'ava_profile.png' || contact.photo == null) ? `${this.siteAddr}/images/ava.png` : `${this.siteAddr}/data/profile_photos/${contact.photo}`;
        name.innerHTML = contact.name;
        contactHTMLElement.setAttribute('data-notice', contact.notice);

        contactImgBlock.append(img);
        contactHTMLElement.append(contactImgBlock);
        contactHTMLElement.append(name);
        // добавление значка без уведомлений, если они отключены
        if (contact.notice == 0) {
            contactHTMLElement.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
        }

        this.container.append(contactHTMLElement);
        return contactHTMLElement;
    }

    async add(username) {
        let requestData = new URLSearchParams();
        requestData.set('username', username);
        requestData.set('CSRF', this.CSRFElement.content);

        let process = (data) => {
            data = JSON.parse(data);
            data.photo = null;
            let contact = {
                'name': data.username,
                'photo': null,
                'notice': 1,
                'id': data.chat_id

            };
            return contact;
        }

        return await ServerRequest.execute(
            '/contact/add',
            process,
            "post",
            null,
            requestData
        );
    }

    remove(contact, clientUsername) {
        let urlParams = new URLSearchParams();
        urlParams.set('name', contact.title);
        urlParams.set('type', contact.className === 'group' ? 'group' : 'contact');
        urlParams.set('CSRF', this.CSRFElement.content);
        urlParams.set('clientName', clientUsername);
        
        fetch('/contact/remove', {method: 'POST', body: urlParams}).then(resp => resp.text()).then(data => {
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

    backup = () => this.#backupContainer = this.container.innerHTML;
    restore = () => this.container.innerHTML = this.#backupContainer;
}