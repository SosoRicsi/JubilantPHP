<?php 
    $EmailServer = ['smtp.example.com','sb@example.com','email password','port(587)','sender email'];
    $DatabaseConnection = ['localhost','root','root','familymanager_v4'];

    $Appname = 'JubilantPHP';

                                                        ####################
                                                        // Texts & Errors \\
                                                        ####################

    /* ------ PasswordHash ------ */
    $passwordMinimumCharError       = "A jelszó hosszúságe nem megfelelő! A minimum hossz: ";
    $passwordLeastOneUppercase      = "A jelszónak legalább egy nagybetűt kell tartalmaznia!";
    $passwordLeastOneLowercase      = "A jelszónak legalább egy kisbetűt kell tartalmaznia!";
    $passwordLeastOneNumber         = "A jelszónak legalább egy számot tartalmaznia kell!";
    $passwordLeastOneSpecialChar    = "A jelszónak legalább egy speciális karaktert tartalmaznia kell!";

    /* ------ UserAuth ------ */
        /* registration */
    $cantCreateUsersTable           = "Nem sikerült létrehozni a <i>felhasználók</i> táblát!";
    $cantCreateLastloginsTable      = "Nem sikerült létrehozni az <i>új bejelentkezések</i> táblát!";
    $emptyUsernameInput             = "A <i>felhasználónév</i> mező nem lehet üres! <br>";
    $emptyEmailInput                = "Az <i>e-mail cím</i> mező nem lehet üres! <br>";
    $emptyPasswordInput             = "A <i>jelszó</i> mező nem lehet üres! <br>";
    $notValidEmail                  = "Nem érvényes <i>e-mail cím ($email)</i>! <br>";
    $emailAlredyUsed                = "Az <i>e-mail cím ($email)</i> már használatban van! <br>";
    $registeredSuccessfully         = "Sikeres regisztráció! <br>";
    $registeredUnsuccessfully       = "Sikertelen regisztráció! <br>";

        /* login */
    $invalidPassword                = "Helytelen jelszó! <br>";
    
        /* passwordchange */
    $passwordSuccessfullyChanged    = "A jelszó sikeresen megváltoztatva! <br>";
    $passwordUnsuccessfullyChanged  = "A jelszót nem sikerült megváltoztatni! Kérjük próbálja újra később! <br>";

        /* confirm user */
    $userConfirmedSuccessfully      = "A felhasználói fiók sikeresen megerősítésre került! <br>";
    $userConfirmedUnsuccessfully    = "A felhasználói fiókot nem sikerült megerősíteni!";
    
    $userNotFound                   = "Nem található felhasználói fiók! <br>";
    $invalidPHPsessionID            = "A küldött <i>php session id</i> nem egyezik meg a valós értékkel! <br>";

    /* ------ FileUpload ------ */
    $cantCreateFileUploadsTableError = "Nem sikerült létrehozni a <i>feltöltött fájlok</i> táblát!";

?>