<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageName ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="icon" href="application/images/icon.png">
    <link rel="stylesheet" href="application/css/reset_styles.css">
    <link rel="stylesheet" href="application/css/template.css">
    <?php if (!empty($content_css)) : ?>
        <link rel="stylesheet" href="application/css/<?= $content_css ?>">
    <?php endif; ?>
</head>
<body>

<header>
    <?php if (!is_null($data)) : ?>
        <?php if (isset($data['publicUsername'])) : ?>
            <h3 class='text-center p-4 text-white bg-c4c4c4'>
                Месенджер
                <span id='clientuser' data-clientuser-publicname=<?= $data['publicUsername'] ?>>
                            <?= $data['user-email'] ?>
                        </span>
            </h3>
        <?php elseif (isset($data['user-email'])) : ?>
            <h3 class='text-center p-4 text-white bg-c4c4c4'>
                Месенджер<span id='userhost'> <?= $data['user-email'] ?></span>
            </h3>
        <?php elseif (isset($data['csrfToken'])) : ?>
            <h3 class='text-center p-4 text-white bg-c4c4c4'>Месенджер</h3>
        <?php endif; ?>
    <?php else : ?>
        <h3 class='text-center p-4 text-white bg-c4c4c4'>Месенджер</h3>
    <?php endif; ?>
</header>

<?php include $content_view ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
<?php if (!empty($content_js)) : ?>
    <script type='text/javascript' src="application/js/<?= $content_js ?>"></script>
<?php endif; ?>
</body>
</html>
