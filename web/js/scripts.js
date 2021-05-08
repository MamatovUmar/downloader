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

    if(url[url.length - 1] !== '/'){
        url += '/'
    }
    $('#loading').show()
    $.post('/site/download', {url}, (res) =>{
        $('#loading').hide()
        if(res.status){
            download(res.link)
        }

    })
})