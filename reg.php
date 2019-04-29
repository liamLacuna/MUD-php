<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register Page</title>
</head>
<body>
    <form method="post">
        <label for="usr">User:<input type="text" id="usr"></label>
        <label for="pwd">Pass:<input type="password" id="pwd"></label>
        <button type="button" id="reg_btn">Register</button>
    </form>

    <script src="common.js"></script>
    <script>
        var usr = el('#usr');
        var pwd = el('#pwd');
        el('#reg_btn').onclick = function(e){
            ajax('server.php',{
                method:'post',
                data:{
                    a:'reg',
                    u:usr.value,
                    p:pwd.value
                }
            },function(e){
                if(e.code == 0)
                    location.href = 'login.php';
                else
                    alert(e.res);
            });
        };
    </script>
</body>
</html>