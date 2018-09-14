<?php

use yii\helpers\Html;
use yii\grid\GridView;


$this->title = 'Mails inbox';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php echo Html::a('Удалить выбранные', ['/mail/multi-delete'], [
            'id' => 'btn-multi-del',
            'class' => 'btn btn-default',
            'onclick' => 'setParams()',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить выбранные элементы?',
                'method' => 'post'
            ]
        ]); ?>
        <?php echo Html::a('Обновить входящие', ['/mail/inbox', 'update'=>'1'], ['class' => 'btn btn-default']); ?>
    </p>

    <?= GridView::widget([
        'id' => 'grid-inbox',
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
                    return date('Y-m-d H:i:s', $model['date']);
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
