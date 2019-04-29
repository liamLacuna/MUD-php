//get elem
function el(sel){
    return document.querySelector(sel);
}

//ajax
function ajax(url,data={},callback=null,error = null){
    let req = {
        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },
    };


    for(let i in data){
        req[i] = data[i];
    }

    if(typeof(req.body) === 'undefined'){
        req.body = data.data;
        req.data = undefined;
    }

    let body = [];
    for(let i in req.body){
        body.push(''+i+'='+req.body[i]);
    }
    req.body = body.join('&');

    fetch(url,req).then(resp=>resp.json()).then(callback).catch(error);
}