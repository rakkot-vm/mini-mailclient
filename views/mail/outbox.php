<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mails outbox';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Удалить выбранные', ['/mail/multi-delete'], [
            'id' => 'btn-multi-del',
            'class' => 'btn btn-default',
            'onclick' => 'setParams()',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить выбранные элементы?',
                'method' => 'post'
            ]
        ]); ?>
    </p>

    <?= GridView::widget([
        'id' => 'grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name' => 'ReqRoutForm[req_ids][]',
                'checkboxOptions' => function ($model, $key, $index, $column) use ($reqRoutForm) {
                    $checked = in_array($key,$reqRoutForm->req_ids);
                    return ['form'=>'req-rout-form','value' => $key, 'checked'=>$checked];
                }
            ],
            'mailfrom',
            [
                'attribute' => 'date',
                'label' => 'Дата',
                'format' => 'text',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->date);
                }
            ],
            'subject',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{delete}',
            ],
        ],
    ]); ?>
</div>
