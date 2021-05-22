<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="text-center">
        <h1>Congratulationss!</h1>

        <form action="<?= \yii\helpers\Url::to(['site/download']) ?>" method="post">
            <?php $form = \yii\widgets\ActiveForm::begin() ?>
            <div class="row mt-30">
                <div class="col-md-6 col-md-offset-3">
                    <div class="input-group">
                        <input
                            type="text" name="url"
                            value=""
                            class="form-control"
                            placeholder="URL of landing page"
                            id="url"
                        >
                        <span class="input-group-btn">
                        <button class="btn btn-default" id="parse" type="button">Go!</button>
                    </span>
                    </div>

                    <h2 id="loading" style="display: none">Идет скачивание...</h2>
                </div>
            </div>
        <?php  \yii\widgets\ActiveForm::end() ?>

    </div>

</div>
