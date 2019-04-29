<!DOCTYPE html>
<?php
session_start();

if(!isset($_SESSION['uid'])){
    header('Location: login.php');
}
$uid = $_SESSION['uid'];
$name = $_SESSION['user'];
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MUD</title>
    <style>
        .msg_area,.user_area,.man_area{
            margin-bottom:10px;
        }

        .recv_msg,.send_msg{
            overflow-y:auto;
        }
    </style>
</head>
<body>
    <div class="user_area">
        User:<?php echo $name;?>
        Uid:<?php echo $uid;?>
        <button type="button" id="exit_btn">Logout</button>
    </div>
    <div class="boxs">
        <div class="info_box" style="float:left;padding:10px;">
            <div class="msg_area" id="say_area">
                <input type="text" id="say">
                <button id="say_btn">Say</button>
                <select name="grp" id="say_to">
                </select>
            </div>
            <div class="msg_area" id="tell_area">
                <input type="text" id="tell">
                <button id="tell_btn">Tell</button>
                <select name="grp" id="tell_to">
                </select>
            </div>
            <div class="msg_area" id="yell_area">
                <input type="text" id="yell">
                <button id="yell_btn">Yell</button>
            </div>
        </div>
        <div class="man_box" style="padding:10px;">
            <div class="man_area">
                Groups:<select name="groups" id="groups"></select>
                <button type="button" id="join_grp">Join</button>
            </div>
            <div class="man_area">
                Create Group:<input type="text" id="group">
                <button type="button" id="create_grp">create</button>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>

    Recieve Messages:
    <div class="recv_msg" style="height:200px;border:solid 1px #000;padding:10px;box-sizing:border-box;">
    </div>
    Send Messages:
    <div class="send_msg" style="height:200px;border:solid 1px #000;padding:10px;box-sizing:border-box;">
    
    </div>
    <script src="common.js"></script>
    <script>
        var groups = el('#groups');
        var sayto = el('#say_to');
        var tellto = el('#tell_to');
        function getGroups(){
            ajax('server.php',{
                method:'post',
                data:{
                    a:'groups'
                }
            },function(res){
                let gs = res.res;
                groups.innerHTML = '';
                for(let i in gs){
                    let elem = gs[i];
                    groups.innerHTML += '<option value="'+elem['id']+'">'+elem['name']+'</option>';
                }
            })
        }

        function getUsers(){
            ajax('server.php',{
                method:'post',
                data:{
                    a:'users'
                }
            },function(res){
                let gs = res.res;
                tellto.innerHTML = '';
                for(let i in gs){
                    let elem = gs[i];
                    tellto.innerHTML += '<option value="'+elem['id']+'">'+elem['name']+'</option>';
                }
            })
        }

        function getGroup(){
            ajax('server.php',{
                method:'post',
                data:{
                    a:'group'
                }
            },function(res){
                let gs = res.res;
                sayto.innerHTML = '';
                for(let i in gs){
                    let elem = gs[i];
                    sayto.innerHTML += '<option value="'+elem['gid']+'">'+elem['group']+'</option>';
                }
            })
        }

        window.onload = function(){
            getGroups();
            getGroup();
            getUsers();
        }
        el('#exit_btn').onclick = function(e){
            ajax('server.php',{
                method:'POST',
                data:{
                    a:'logout'
                }
            },function(res){
                if(res.code == 0){
                    location.href = 'login.php';
                }
            });
        }

        el('#join_grp').onclick = function(){
            ajax('server.php',{
                method:'post',
                data:{
                    a:'join',
                    g:el('#groups').value,
                }
            },function(res){
                getGroup();
            });
        }

        el('#create_grp').onclick = function(){
            let group = el('#group');
            ajax('server.php',{
                method:'post',
                data:{
                    a:'create_group',
                    n:group.value
                }
            },function(res){
                getGroups();
                getGroup();
                group.value = '';
            });
        }

        var sendMsg = el('.send_msg');
        el('#say_btn').onclick = function(e){
            let say = el('#say');
            ajax('server.php',{
                method:'post',
                data:{
                    a:'say',
                    t:sayto.value,
                    m:say.value
                }
            },function(res){
                console.log(res);
                sendMsg.innerHTML = '<p>To Group ['+sayto.options[sayto.selectedIndex].text+']:'+say.value+'</p>'+sendMsg.innerHTML;
                say.value = '';
            });
        }

        el('#tell_btn').onclick = function(e){
            let tell = el('#tell');
            ajax('server.php',{
                method:'post',
                data:{
                    a:'tell',
                    t:tellto.value,
                    m:tell.value
                }
            },function(res){
                console.log(res);
                sendMsg.innerHTML = '<p>To user ['+tellto.options[tellto.selectedIndex].text+']:'+tell.value+'</p>'+sendMsg.innerHTML;
                say.value = '';
            });
        }

        el('#yell_btn').onclick = function(e){
            let yell = el('#yell');
            ajax('server.php',{
                method:'post',
                data:{
                    a:'yell',
                    t:0,
                    m:yell.value
                }
            },function(res){
                console.log(res);
                sendMsg.innerHTML = '<p>yell:'+yell.value+'</p>'+sendMsg.innerHTML;
                say.value = '';
            });
        }

        function getRecv(){
            ajax('server.php',{
                method:'post',
                data:{
                    a:'recv',
                    t:1,
                    p:0,
                }
            },function(res){
                let msgs = res.res;
                let recv_msg = el('.recv_msg');
                for(let i in msgs){
                    let msg = msgs[i];
                    recv_msg.innerHTML = '<p>'+msg['content']+'</p>'+recv_msg.innerHTML;
                }
            });
        }

        setInterval(() => {
            getRecv();
        }, 5000);
    </script>
</body>
</html>