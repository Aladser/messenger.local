<div class='container font-roboto h-80 bg-c4c4c4 p-0 d-flex'>
    <div class='contacts pt-2 ps-2 pe-2'>
        <div class='position-relative'>
            <input type="text" class='w-100 mb-2 form-control' placeholder='поиск контакта' id='find-contacts-input'>
            <button class='position-absolute top-20prcts end-2prcts border-0 bg-white' id='reset-find-contacts-btn' title='сбросить фильтр'>&#9747;</button>
        </div>
        <div id='contacts'></div>
    </div>
    
    <div class='messages-container pt-2 pe-2 border-start border-end border-dark position-relative padding-left-1dot5prcts padding-right-0dot75'>
        <p class='mb-2'>Чат с пользователем <span id='contact-username'>xxxxx</span></p>
        <div class='messages m-0' id='messages'>
            <div class='msg'>
                <table class='msg-table'>
                    <tr><td class='msg__text'>
                        Штиль – ветер молчит.
                        Упал белой чайкой на дно.
                        Штиль – наш корабль забыт.
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
        </div>

        <div class='send-msg position-absolute bottom-0 mb-2'>
            <div class='input-group d-flex justify-content-between'>
                <textarea class="input-group-prepend resize-none border-0 form-control" rows='2' placeholder='Наберите ваше сообщение здесь' id='message-input'></textarea>
                <button type="submit" class='img-btn' title='Отправить' id="send-msg-btn"><img src="application/images/sendbtn.png"></button>
            </div>
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
            <a href="/Main?logout=true" class='option-link contact-img-div'>
            <div class='option-img-div'><img src="application/images/exit.png" class='img'></div>
            <span class='option-name'>Выйти</span>
            </a>
        </div>

    </div>
</div>

<script type='text/javascript' src="application/js/TimeInfo.js"></script>