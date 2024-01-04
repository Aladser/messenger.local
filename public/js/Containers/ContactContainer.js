/** Контейнер контактов */
class ContactContainer extends TemplateContainer{
    #backupContainer;
    siteAddr = this.baseSiteName;
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
            // родительский метод
            this.removeElements();
            
            data = JSON.parse(data);
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

    /** создать HTML-код контакта  */
    create(contact) {
        let contactContent = `
            <article class="contact position-relative mb-2 text-white" title="${contact.username}" data-notice="1">
            <div class="profile-img">
                <img class="contact__img img pe-2" src="${contact.photo}">
            </div>
            <span class="contact__name">${contact.username}</span>
        `;
        // значок отключенных уведомлений
        if (contact.notice === 0) {
            contactContent += "<div class='notice-soundless'>&#128263;</div>";
        }
        contactContent += '</article>';
        this.container.innerHTML += contactContent;
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
            this.nameList.push(data.username);
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

    remove(contactDOMElement) {
        let urlParams = new URLSearchParams();
        urlParams.set('contact_name', contactDOMElement.title);
        urlParams.set('type', contactDOMElement.className === 'group' ? 'group' : 'contact');
        urlParams.set('CSRF', this.CSRFElement.content);
        
        fetch('/contact/remove', {method: 'POST', body: urlParams}).then(resp => resp.text()).then(data => {
            try {
                data = JSON.parse(data);
                if (parseInt(data.result) > 0) {
                    contactDOMElement.remove();
                }
            } catch (err) {
                alert(data);
            }
        });
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

    backup = () => this.#backupContainer = this.container.innerHTML;
    restore = () => this.container.innerHTML = this.#backupContainer;
}