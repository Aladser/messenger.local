const findContactsInput = document.querySelector('#find-contacts-input');
const contacts = document.querySelector('#contacts');

// поиск контактов
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?user=${this.value}`, {method: 'get'}).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';
        //  отображение найденных контактов в списке контактов
        console.log(data);
        data.forEach(element => {
            let contact = document.createElement('div');
            contact.className = 'split-word';

            let contactImgDiv = document.createElement('div');
            contactImgDiv.className = 'contact-img-div';

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

            let name = document.createElement('span');
            name.className = 'contact img';
            name.innerHTML = element['username'];
            contact.appendChild(name);

            contacts.appendChild(contact);
        });
    });
});

// снятие фокус с элемента поиска контактов
findContactsInput.onblur = () => {
    contacts.innerHTML = '';
};