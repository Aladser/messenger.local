<div class='container font-roboto mt-4 position-relative' id='container-profile'>
    <div class='profile-container'>
        <div class='profile-img-block'>
            <?php
            if (is_null($data['user_photo']) || $data['user_photo'] == 'ava_profile.png') {
                $photo = 'http://messenger.local/application/images/ava_profile.png';
            } else {
                $photo = 'http://messenger.local/application/data/profile_photos//' . $data['user_photo'];
            }
            ?>
            <img src="<?= $photo ?>" id='profile-img' class="rounded-circle img" alt="Avatar"/>
        </div>

        <div class='p-4'>
            <table class='table'>
                <tr>
                    <td>Почта:</td>
                    <td><?= $data['user-email'] ?></td>
                </tr>
                <tr>
                    <td data-bs-toggle="tooltip">Никнейм:</td>
                    <td><input type="text" class='input-nickname' id='input-nickname' value="<?= is_null($data['user_nickname']) ? '' : $data['user_nickname'] ?>" disabled>
                    </td>
                </tr>
            </table>
            <div class="form-check form-switch mb-3 d-flex justify-content-center d-none" id='hide-email-input-block'>
                <input class="form-check-input" type="checkbox"
                       id="hide-email-input" <?= $data['user_hide_email'] == 1 ? 'checked' : '' ?> >
                <label class="form-check-label" for="hide-email-input">&nbsp; скрыть почту</label>
            </div>
            <button class='btn btn-bg-C4C4C4 text-white w-100 d-none' id='save-profile-settings-btn'>Сохранить</button>
        </div>

        <div class='options mb-2'>
            <form enctype="multipart/form-data" method='post' id='upload-file-form'>
                <input type="file" id="select-file-input" accept="image/*" name='image' class='d-none'>
                <input type="submit" id='upload-file-btn' class='d-none'>
                <button class='btn btn-bg-C4C4C4 text-white w-100 mb-2' id='edit-photo-btn'>Изменить фото</button>
            </form>

            <button class='btn btn-bg-C4C4C4 text-white w-100 mb-2' id='btn-edit-nickname'>Установить nickname</button>
            <a href="\chat" class='text-decoration-none'>
                <div class='btn-bg-C4C4C4 text-white w-100 p-2 text-center'>Назад</div>
            </a>
        </div>
    </div>
    <br>
    <p class='text-center text-danger fs-5 fw-bolder d-none' id='prg-error'></p>
</div>

<script type='text/javascript' src="http://messenger.local/application/js/validation.js"></script>

<!-- CSRF страницы -->
<input type="hidden" id="input-csrf" value=<?= $data['csrfToken'] ?>>