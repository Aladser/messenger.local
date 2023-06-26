const findContactsInput = document.querySelector('#find-contacts-input');
const contacts = document.querySelector('#contacts');



// отрисовка контакта
// onlyContacts: true - отображение только контактов
function createContact(element, onlyContacts){
        // контейнер контакта
        let contact = document.createElement('div');
        contact.className = 'position-relative';

        // контейнер изображения
        let contactImgDiv = document.createElement('div');
        contactImgDiv.className = 'img-div';

        // фото профиля
        let img = document.createElement('img');
        img.className = 'pe-2 pb-2 img';
        if(element['user_photo'] == 'ava_profile.png'){
            img.src = 'application/images/ava.png';
        }
        else if(element['user_photo'] == null){
            img.src = 'application/images/ava.png';
        }
        else{
            img.src = `application/data/profile_photos/${element['user_photo']}`; 
        }

        contactImgDiv.appendChild(img);
        contact.appendChild(contactImgDiv);

        // имя контакта
        let name = document.createElement('span');
        name.className = 'contact text-break';
        name.innerHTML = element['username'];
        contact.appendChild(name);

        // кнопка добавить контакт, если пользователь не является контактом
        if(onlyContacts === false){
            if(element['is_contact'] == 0){
                let addContactBtn = document.createElement('div');
                addContactBtn.className = 'add-contact-btn position-absolute top-0 end-0';
                addContactBtn.title = 'добавить чат';
                addContactBtn.innerHTML = '&#43';
                addContactBtn.onclick = setAddContact(addContactBtn, element['username']); //добавление контакта в БД
                contact.appendChild(addContactBtn);
            }
        }

        contacts.appendChild(contact);
}



// Показ контактов
function showContacts(findInput, contacts){
    fetch(`/get-contacts`, {method: 'get'}).then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';

        //  отображение найденных контактов в списке контактов
        if(data != null){
            data.forEach(element => createContact(element, true));
        }
    }); 
}
showContacts(findContactsInput, contacts); // показ контактов при загрузке страницы
document.querySelector('#reset-find-contacts-input').onclick = () => showContacts(findContactsInput, contacts); // отмена поиска и отображение контактов


// ПОИСК КОНТАКТОВ
// добавить контакт в БД
function setAddContact(btn, contact){
    return function(){
        fetch(`/add-contact?contact=${contact}`, {method: 'get'}).then(r=>r.text()).then(data=>{
            if(data == 1){
                btn.style.display = 'none';
            }
        });
    };
}


// поиск контактов в БД
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?userphrase=${this.value}`, {method: 'get'}).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';

        //  отображение найденных контактов в списке контактов
        if(data != null){
            data.forEach(element => createContact(element, false));
        }
    });
});