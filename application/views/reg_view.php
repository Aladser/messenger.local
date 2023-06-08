<div class='container text-center'>
    <h3 class='mt-4 mb-4'>Регистрация нового пользователя</h3>
    
    <form class='reg-form' method="POST" action='/application/models/reg_model.php'>
        <div class='position-relative w-25 mx-auto'>
            <input type="email" class="w-100 mb-2" id="reg-form__email-input"  placeholder="email">
            <p class='input-clue' id='reg-form__emai-clue'>введите адрес электронной почты</p>
        </div>

        <div class='position-relative w-25 mx-auto'>
            <input type="password" class="w-100 mb-2" id="reg-form__password1-input" placeholder="пароль (минимум 6 символов)">
            <p class='input-clue' id='reg-form__password1-clue'>пароль должен обязательно содержать заглавные и прописные буквы, цифры</p>
        </div>

        <div class='position-relative w-25 mb-2 mx-auto'>
            <input type="password" class="w-100 mb-2" id="reg-form__password2-input" placeholder="подтвердите пароль">
            <p class='input-clue' id='reg-form__password2-clue'>пароли не совпадают</p>
        </div>

        <div>
            <input type="submit" class='btn btn-success w-25 mb-2' value="Регистрация" disabled id='reg-form__reg-btn'>
            <br>
            <input type="button" class='btn btn-success w-25 mb-2' value="Назад" id='reg-form__backBtn' >
        </div>
    </form>
</div>

<script type='text/javascript' src='application/js/reg.js'></script>