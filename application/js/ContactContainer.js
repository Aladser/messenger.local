class ContactContainer {
    /** контейнер контактов */
    container = document.querySelector('#contacts');
    /** поле поиска пользователя */
    findContactsInput = document.querySelector('#find-contacts-input');
    /** вебсокет */
    ws = new WebSocket('ws://localhost:8888');
    /** вебсокет сообщений */
    chatWebsocket = new ChatWebsocket(this.ws);

    /**
     * 
     * @param {*} contactsContainer контейнер контактов
     * @param {*} findContactsInput поле поиска контактов
     * @param {*} ws вебсокет
     * @param {*} chatWebsocket чат вебсокета 
     */
    constructor(container, findContactsInput, ws, chatWebsocket) {
        this.container = container;
        this.findContactsInput = findContactsInput;
        this.ws = ws;
        this.chatWebsocket = chatWebsocket;
    }

    /** показать контакты пользователя браузера */
    show = () => fetch('contact/get-contacts').then(resp => resp.text()).then(contacts => {
        contacts = parseJSONData(contacts);
        if (contacts !== undefined) {
            this.findContactsInput.value = '';
            this.container.innerHTML = '';
            contacts.forEach(contact => {
                this.chatWebsocket.addContact({'name': contact.name, 'chat': contact.chat, 'notice': contact.notice});
                ChatDOMElementCreator.contact(this.container, contact);
            });
        }
    });

    /** очистить контейнер */
    clear = () => this.container.innerHTML = '';
}