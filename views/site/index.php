<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="text-center">
        <h1>Congratulations!</h1>

        <form action="<?= \yii\helpers\Url::to(['site/download']) ?>" method="post">
            <?php $form = \yii\widgets\ActiveForm::begin() ?>
            <div class="row mt-30">
                <div class="col-md-6 col-md-offset-3">
                    <div class="input-group">
                        <input
                            type="text" name="url"
                            value="https://preview.colorlib.com/theme/startup2/"
                            class="form-control"
                            placeholder="URL of landing page"
                            id="url"
                        >
                        <span class="input-group-btn">
                        <button class="btn btn-default" id="parse" type="button">Go!</button>
                    </span>
                    </div>
                </div>
            </div>
        <?php  \yii\widgets\ActiveForm::end() ?>

    </div>

</div>

<?php

$js = <<<JS

$('#parse').click(function(){
    let url = $('#url').val()
    
    if(url[url.length - 1] !== '/'){
        url += '/'
    }

    $.post('/site/download', {url}, (res) =>{
        console.log(res)
    })
})


JS;
$this->registerJs($js);

?>
