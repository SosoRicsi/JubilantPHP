<!DOCTYPE html>
<html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{title}}</title>
    </head>
    <body>
        
        @template('login.blade.php', array('title'=>'főoldal'))

        @template('register.blade.php')
    </body>
</html>