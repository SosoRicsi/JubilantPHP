<!DOCTYPE html>
<html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{title}}</title>
    </head>
    <body>
        <center>
            <h1>{{title}}</h1>
            <div style="width: 350px">
                <form action="/execute/login" method="post">
                    <label for="email">E-mail cím</label><br>
                    <input type="email" name="email" id="email" required>
                    <hr><br>
                    <label for="password">Jelszó</label><br>
                    <input type="password" name="password" id="password" required>
                    <br><br><br>
                    <input type="hidden" name="session_id" value="{{session_id}}">
                    <input type="submit" value="{{title}}">
                </form>
            </div>
        </center>
    </body>
</html>