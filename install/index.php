<?php

chdir(dirname(__DIR__));

if (file_exists('.env')) {
    header('Location: index.php');
    exit;
}

if (!empty($_POST['install'])) {



    $dbHost = $_POST['db-host'];
    $dbPort = $_POST['db-port'];
    $dbName = $_POST['db-name'];
    $dbUser = $_POST['db-user'];
    $dbPassword = $_POST['db-password'];

    $mailHost = $_POST['mail-host'];
    $mailPort = $_POST['mail-port'];
    $mailUser = $_POST['mail-user'];
    $mailPassword = $_POST['mail-password'];

    $smsUser = $_POST['sms-user'];
    $smsPassword = $_POST['sms-password'];

    // Copy config files
    $files = array(
        'config/bundles.php.dist' => 'config/bundles.php',
        'config/routes.yaml.dist' => 'config/routes.yaml',
        'config/services.yaml.dist' => 'config/services.yaml',
    );
    foreach($files as $dist => $file) {
        if (!file_exists($file)) copy($dist, $file);
    }

    // Create .env file
    $envFile = fopen(".env", "w");
    fwrite($envFile, "# .env\r\n");
    if ($sitemode == 'prod') fwrite($envFile, "APP_ENV=prod\r\n");
    fwrite($envFile, "\r\n");
    fwrite($envFile, "# Database\r\n");
    fwrite($envFile, "DATABASE_URL=\"mysql://" . $dbUser . ":" . $dbPassword . "@" . $dbHost . ":" . $dbPort . "/" . $dbName . "\"\r\n");
    fwrite($envFile, "\r\n");
    fwrite($envFile, "# Delivery is disabled by default via null://localhost\r\n");
    fwrite($envFile, "#MAILER_URL=null://localhost\r\n");
    fwrite($envFile, "\r\n");
    fwrite($envFile, "# use this to configure a traditional SMTP server\r\n");
    fwrite($envFile, "MAILER_URL=smtp://" . $mailHost . ":" . $mailPort . "?encryption=ssl&auth_mode=login&username=" . $mailUser . "&password=" . $mailPassword . "\r\n");
    fwrite($envFile, "\r\n");
    fclose($envFile);

    if (!file_exists('vendor/autoload.php')) {

        exec('php composer.phar install --no-dev --optimize-autoloader');

    }

    header('Location: /install/settings/');

    // Create tables

    // Create admin user

    // Add settings=

    // Run composer.phar

    // Compile CSS files
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <title>Install</title>
        <style>
            @import url('https://fonts.googleapis.com/css?family=Montserrat:300,400,500,700');
            html, body {
                height: 100%;
                padding: 0;
                margin: 0;
                font-family: 'Montserrat';
            }
            body {
                background: #eeeeee;
                background: -moz-linear-gradient(top, #eeeeee 0%, #eeeeee 100%);
                background: -webkit-linear-gradient(top, #eeeeee 0%,#eeeeee 100%);
                background: linear-gradient(to bottom, #eeeeee 0%,#eeeeee 100%);
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eeeeee', endColorstr='#eeeeee',GradientType=0 );
            }
            .page {
                width: 100%;
                max-width: 940px;
                margin: 0 auto;
                margin-top: 15px;
                padding: 15px;
                background-color: rgba(255,255,255,1);
                border-radius: 4px;
                -webkit-box-shadow: 0px 4px 13px -4px rgba(0,0,0,0.75);
                -moz-box-shadow: 0px 4px 13px -4px rgba(0,0,0,0.75);
                box-shadow: 0px 4px 13px -4px rgba(0,0,0,0.75);
            }

            .form-group {
                margin-bottom: 10px;
            }

            label {
                display: block;
                font-size: 12px;
                font-weight: bold;
            }

            .form-control {
                display: block;
                width: 100%;
                max-width: 300px;
                padding: 6px 4px;
                border: 1px solid #999;
                border-radius: 4px;
            }

            .btn {
                padding: 8px 15px;
                background-color: #ccc;
                border: 1px solid #999;
                border-radius: 4px;
                font-size: 14px;
                text-transform: uppercase;
            }

            .row {
                vertical-align: top;
            }

            .col-sm-4 {
                display: inline-block;
                vertical-align: top;
                width: 33%;
            }
        </style>
    </head>
    <body>
        <div class="page">
            <h1>Install CMS</h1>
            <form action="" method="post">
                <input type="hidden" name="install" value="1">
                <div class="row">
                    <div class="col-sm-4">
                        <h3>Database</h3>
                        <div class="form-group">
                            <label>Host</label>
                            <input type="text" name="db-host" class="form-control" value="localhost">
                        </div>
                        <div class="form-group">
                            <label>Port</label>
                            <input type="text" name="db-port" class="form-control" value="3306">
                        </div>
                        <div class="form-group">
                            <label>Database Name</label>
                            <input type="text" name="db-name" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>User</label>
                            <input type="text" name="db-user" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="db-password" class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <h3>Mail</h3>
                        <div class="form-group">
                            <label>Host</label>
                            <input type="text" name="mail-host" class="form-control" value="localhost">
                        </div>
                        <div class="form-group">
                            <label>Port</label>
                            <input type="text" name="mail-port" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>User</label>
                            <input type="text" name="mail-user" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="mail-password" class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <h3>SMS</h3>
                        <div class="form-group">
                            <label>User</label>
                            <input type="text" name="sms-user" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="sms-password" class="form-control" value="">
                        </div>
                    </div>
                </div>
                <!--
                <div class="row">
                    <div class="col-sm-4">
                        <h3>Admin user</h3>
                        <div class="form-group">
                            <label>Firstname</label>
                            <input type="text" name="admin-firstname" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Lastname</label>
                            <input type="text" name="admin-lastname" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" name="admin-email" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Phonenumber</label>
                            <input type="text" name="admin-phone" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="admin-password" class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <h3>Website</h3>
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="site-name" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Site Domain</label>
                            <input type="text" name="site-domain" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="comapny-name" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>Site mode</label>
                            <select name="site-name" class="form-control">
                                <option value="prod">Production</option>
                                <option value="dev">Development</option>
                            </select>
                        </div>
                    </div>
                </div>
                -->
                <input type="submit" name="submit" class="btn" value="Install">
            </form>
        </div>
        <script>




        </script>
    </body>
</html>
