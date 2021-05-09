function download(link) {
    let element = document.createElement('a');
    element.setAttribute('href', link);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

$('#parse').click(function(){
    let url = $('#url').val()
    let file_name = ''

    if(url.substr(-5) === '.html'){
        let arr = url.split('/')
        file_name = arr[arr.length - 1]
        url = url.replace(file_name, '')
    }else if(url[url.length - 1] !== '/'){
        url += '/'
    }
    $('#loading').show()
    $.post('/site/download', {url, file_name}, (res) =>{
        $('#loading').hide()
        if(res.status){
            download(res.link)
        }

    })
})