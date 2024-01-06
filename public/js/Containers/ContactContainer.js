/** Контейнер контактов */
class ContactContainer extends TemplateContainer{
    #backupContainer;
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

    /** возвращает DOM-узлы контактов */
    get = () => this.container.querySelectorAll('.contact');
    /** сделать резервную копию DOM содержания контейнера */
    backup = () => this.#backupContainer = this.container.innerHTML;
    /** восстановить DOM содержание контейнера из резервной копии */
    restore = () => this.container.innerHTML = this.#backupContainer;

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
            
            let userDataList = JSON.parse(data);
            userDataList.forEach(userData => this.createNode(userData));
        }

        await ServerRequest.execute(
            'user/find',
            process,
            "post",
            null,
            urlParams
        );
    }

    /** добавить контакт */
    async add(username) {
        let requestData = new URLSearchParams();
        requestData.set('username', username);
        requestData.set('CSRF', this.CSRFElement.content);
        requestData.set('type', 'dialog');

        let process = (data) => {
            let contactData = JSON.parse(data);
            let contact = {
                'username': contactData.username,
                'photo': contactData.photo,
                'notice': 1,
                'id': contactData.chat_id

            };
            this.nameList.push(contactData.username);
            return contact;
        }

        return await ServerRequest.execute(
            '/chat/add',
            process,
            "post",
            null,
            requestData
        );
    }

    /** создать DOM-узел контакта  */
    createNode(contactData) {
        let contactArticle = document.createElement('article');
        contactArticle.className = "contact position-relative mb-2 text-white";
        contactArticle.title = contactData.username;
        contactArticle.innerHTML = `
            <div class="profile-img">
                <img class="contact__img img pe-2" src="${contactData.photo}">
            </div>
            <span class="contact__name">${contactData.username}</span>
        `;
        // значок отключения уведомлений
        if (contactData.notice === 0) {
            contactArticle.setAttribute('data-notice', 0);
            contactArticle.innerHTML += '<div class="notice-soundless">🔇</div>';
        } else {
            contactArticle.setAttribute('data-notice', 1);
        }

        this.container.append(contactArticle);
        return contactArticle;
    }

    // удалить контакт
    remove(contactDOMElement) {
        let requestData = new URLSearchParams();
        requestData.set('contact_name', contactDOMElement.title);
        requestData.set('type', contactDOMElement.className === 'group' ? 'group' : 'contact');
        requestData.set('CSRF', this.CSRFElement.content);
        
        let process = (data) => {
            try {
                data = JSON.parse(data);
                if (parseInt(data.result) > 0) {
                    contactDOMElement.remove();
                    // новая сохраненная копия после удаления контакта
                    this.backup();
                }
            } catch (err) {
                alert(err);
            }
        }

        ServerRequest.execute(
            '/chat/remove',
            process,
            "post",
            null,
            requestData
        );
    }
}
