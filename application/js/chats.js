const findContactsInput = document.querySelector('#find-contacts-input');
const contacts = document.querySelector('#contacts');



// ПОИСК КОНТАКТОВ
// добавить контакт в БД
function setAddContact(){
    return function(){
        console.log('клик');
    };
}

// событие добавления контакта
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?userphrase=${this.value}`, {method: 'get'}).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';
        //  отображение найденных контактов в списке контактов
        data.forEach(element => {
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

            // кнопка добавить контакт
            let addContactBtn = document.createElement('div');
            addContactBtn.className = 'add-contact-btn position-absolute top-0 end-0';
            addContactBtn.title = 'добавить в контакты';
            addContactBtn.innerHTML = '&#43';
            addContactBtn.onclick = setAddContact();
            contact.appendChild(addContactBtn);

            contacts.appendChild(contact);
        });
    });
});



// снятие фокус с элемента поиска контактов
findContactsInput.onblur = () => {
    contacts.innerHTML = '';
};