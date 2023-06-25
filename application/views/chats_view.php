<div class='container font-roboto h-80 bg-c4c4c4 p-0 d-flex'>
    <div class='contacts pt-2 ps-2 pe-2'>
        <input type="text" class='w-100 mb-2 form-control' placeholder='поиск контакта' id='find-contacts-input'>
        <div id='contacts'>
            <div class='position-relative'> 
                <div class='img-div'>
                    <img class='pe-2 pb-2 img' src="application/data/profile_photos/aladser@gmail.com.1.png" alt="фото">
                </div>
                <span class='contact text-break'>Andrei Avramenko</span>
            </div>
            <div class='position-relative'> 
                <div class='img-div'>
                    <img class='pe-2 pb-2 img' src="application/images/ava.png" alt="фото">
                </div>
                <span class='contact text-break'>Denis Demin</span>
            </div>
            <div class='position-relative'> 
                <div class='img-div'>
                    <img class='pe-2 pb-2 img' src="application/images/ava.png" alt="фото">
                </div>
                <span class='contact text-break'>Евгения Городецкая</span>
            </div>
            <div class='position-relative'> 
                <div class='img-div'>
                    <img class='pe-2 pb-2 img' src="application/images/ava.png" alt="фото">
                </div>
                <span class='contact text-break'>Богдан Барабанщиков</span>
            </div>
        </div>
    </div>
    
    <div class='messages-container pt-2 pe-2 border-start border-end border-dark position-relative padding-left-1dot5prcts padding-right-0dot75'>
        <p class='mb-2'>Чат с пользователем sendlyamobile@gmail.com</p>

        <div class='messages m-0'>
            <div class='msg'>
                <table class='msg-table'>
                    <tr><td class='msg__text'>
                        Штиль – ветер молчит. <br>
                        Упал белой чайкой на дно.<br>
                        Штиль – наш корабль забыт. <br>
                        Один, в мире скованном сном...
                    </td></tr>
                    <tr><td class='msg__time'>
                        20.06.203 10.30
                    </td></tr>
                </table>
            </div>

            <div class='msg d-flex justify-content-end'>
                <table class='msg-table msg-table-contact'>
                    <tr><td class='msg__text'>
                        Между всех времён без имён и лиц<br>
                        мы уже не ждём, что проснётся бриз!
                    </td></tr>
                    <tr><td  class='msg__time'>
                        20.06.203 10.40
                    </td></tr>
                </table>
            </div>

            <div class='msg'>
                <table class='msg-table'>
                    <tr><td class='msg__text'>
                    Штиль сводит с ума. <br>
                    Жара пахнет черной смолой.<br>
                    Смерть одному лишь нужна. <br>
                    И мы вернемся домой
                    </td></tr>
                    <tr><td  class='msg__time'>
                        20.06.203 10.50
                    </td></tr>
                </table>
            </div>

            <div class='msg'>
                <table class='msg-table'>
                    <tr><td class='msg__text'>
                    Его кровь и плоть вновь насытит нас.<br>
                    И за смерть ему может бог воздаст.
                    </td></tr>
                    <tr><td  class='msg__time'>
                        20.06.203 10.50
                    </td></tr>
                </table>
            </div>

            <div class='msg d-flex justify-content-end'>
                <table class='msg-table msg-table-contact'>
                    <tr><td class='msg__text'>
                    Что нас ждёт, море хранит молчанье. <br>
                    Жажда жить сушит сердца до дна.<br>
                    Только жизнь здесь ничего не стоит. <br>
                    Жизнь других, но не твоя!
                    </td></tr>
                    <tr><td class='msg__time'>
                        20.06.203 11.00
                    </td></tr>
                </table>
            </div>

            <div class='msg'>
                <table class='msg-table'>
                    <tr><td class='msg__text'>
                    Нет, гром не грянул с небес.<br>
                    Когда пили кровь как зверье.<br>
                    Но нестерпимы стал блеск.<br>
                    Крест, что мы Южным зовем.
                    </td></tr>
                    <tr><td  class='msg__time'>
                        20.06.203 10.50
                    </td></tr>
                </table>
            </div>
            
            <div class='msg'>
                <table class='msg-table'>
                    <tr><td class='msg__text'>
                    И в последний миг полнялась волна. <br>
                    И раздался крик: Впереди Земля!
                    </td></tr>
                    <tr><td  class='msg__time'>
                        20.06.203 10.50
                    </td></tr>
                </table>
            </div>
        </div>

        <div class='send-msg position-absolute bottom-0 mb-2'>
            <form id='add-msg' action="">
                <div class='input-group d-flex justify-content-between'>
                    <textarea class="input-group-prepend resize-none border-0 form-control" rows='2' placeholder='Наберите ваше сообщение здесь'></textarea>
                    <button type="submit" class='img-btn'><img src="application/images/sendbtn.png"></button>
                </div>
            </form>
        </div>

    </div>
    <div class='options-container pt-2'>
        <div class='option'>
            <a href="/profile" class='option-link contact-img-div'>
            <div class='option-img-div'><img src="application/images/profile.png" class='img'></div>
            <span class='option-name'>Профиль</span>
            </a>
        </div>
        <div class='option'>
            <a href="" class='option-link contact-img-div'>
            <div class='option-img-div'><img src="application/images/settings.png" class='img'></div>
            <span class='option-name'>Настройки</span>
            </a>
        </div>
        <div class='option'>
            <a href="/quit" class='option-link contact-img-div'>
            <div class='option-img-div'><img src="application/images/exit.png" class='img'></div>
            <span class='option-name'>Выйти</span>
            </a>
        </div>

    </div>
</div>