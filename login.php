<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Page</title>
</head>
<body>
    <form method="post">
        <label for="usr">User:<input type="text" id="usr"></label>
        <label for="pwd">Pass:<input type="password" id="pwd"></label>
        <button type="button" id="login_btn">Login</button>
        <button type="button" id="reg_btn">Register</button>
    </form>

    <script src="common.js"></script>
    <script>
        var usr = el('#usr');
        var pwd = el('#pwd');
        el('#login_btn').onclick = function(e){
            ajax('server.php',{
                method:'POST',
                data:{
                    a:'login',
                    u:usr.value,
                    p:pwd.value
                }
            },function(res){
                if(res.code == 0){
                    location.href='index.php';
                }else{
                    alert(res.res);
                }
            });
        }

        el('#reg_btn').onclick = function(e){
            location.href = 'reg.php';
        };
    </script>
</body>
</html>