<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- меты -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (!empty($head)) {
        echo $head;
    } ?>

    <title><?php echo $page_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="icon" href="http://<?php echo $app_name; ?>/public/images/icon.png">
    <link rel="stylesheet" href="http://<?php echo $app_name; ?>/public/css/reset_styles.css">
    <link rel="stylesheet" href="http://<?php echo $app_name; ?>/public/css/template.css">
    <?php if (!empty($content_css)) { ?>
        <link rel="stylesheet" href="http://<?php echo $app_name; ?>/public/css/<?php echo $content_css; ?>">
    <?php } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- js скрипты -->
    <?php if (!empty($js_script_list)) { ?>
        <?php foreach ($js_script_list as $script) { ?>
            <script type='text/javascript' src="http://<?php echo $app_name; ?>/public/js/<?php echo $script; ?>" defer></script>
        <?php } ?>
    <?php } ?>
</head>

<body>
<header>
    <h3 class='text-center p-4 text-white bg-theme'>
        <?php echo $page_name; ?>
        <?php if (isset($data['publicUsername'])) { ?>
            <span id='clientuser' data-clientuser-publicname=<?php echo $data['publicUsername']; ?>>
                <?php echo $data['user-email']; ?>
            </span>
        <?php } ?>
    </h3>
</header>

<?php require_once $content_view; ?>

</body>
</html>
