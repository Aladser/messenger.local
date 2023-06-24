const findContactsInput = document.querySelector('#find-contacts-input');
const contacts = document.querySelector('#contacts');

// поиск контактов
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?user=${this.value}`, {method: 'get'}).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';
        //  отображение найденных контактов в списке контактов
        data.forEach(element => {
            let contact = document.createElement('div');
            contact.className = 'split-word';

            let img = document.createElement('img');
            img.className = 'pe-2 pb-2';
            img.src = 'application/images/ava.png';
            contact.appendChild(img);

            let name = document.createElement('span');
            name.className = 'contact';
            name.innerHTML = element['username'];
            contact.appendChild(name);

            contacts.appendChild(contact);
        });
    });
});
