<div class='newUserForm container text-center'>

    <h3 class=''>Регистрация нового пользователя</h3>

    <form class='regForm' method="POST" action=''>
        <input type="email" class="w-25 mb-2" id="regForm_emailInput"  placeholder="email">
        <br>
        <input type="password" class="w-25 mb-2" id="regForm__passwordInput1" placeholder="пароль">
        <br>
        <input type="password" class="w-25 mb-2" id="regForm__passwordInput2" placeholder="подтвердите пароль">

        <div>
            <input type="submit" class='btn btn-success w-25 mb-2' value="Регистрация" disabled id='regForm__regBtn'>
            <br>
            <input type="button" class='btn btn-success w-25 mb-2' value="Назад" id='regForm__backBtn' >
        </div>
    </form>
</div>

<script type='text/javascript' src='application/js/reg.js'></script>