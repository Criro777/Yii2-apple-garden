<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Яблоневый сад';
$this->params['breadcrumbs'][] = $this->title;

?>

    <div class="apple-index">

        <h1><?= Html::encode($this->title) ?></h1>

        <?= Html::beginForm(['generate'], 'post', ['class' => 'form-inline mb-3']) ?>
        <?= Html::submitButton('🎲 Создать (от 1 до 10)', [
            'class' => 'btn btn-success btn-lg'
        ]) ?>
        <?= Html::endForm() ?>

        <?php $allApples = $dataProvider->models; ?>

        <?php Pjax::begin(['id' => 'apple-container', 'timeout' => false]); ?>

        <?php if (empty($allApples)): ?>
            <div class="alert alert-info">
                <h4>🍃 В саду нет яблок</h4>
                <p>Нажмите кнопку выше, чтобы сгенерировать случайные яблоки.</p>
            </div>
        <?php else: ?>
            <div class="apple-container">
                <?php foreach ($allApples as $apple): ?>
                    <div class="apple-card">
                        <?php
                        $remainingPercent = 100 - $apple->eaten_percent;
                        $rottenSpots = $apple->status === $apple::STATUS_ROTTEN ? 'apple-rotten-spots' : '';
                        ?>
                        <div class="apple-visual <?= $apple->getStatusClass() ?>">
                            <div class="apple-icon"><?= $apple->getStatusIcon() ?></div>
                            <div class="apple-body <?= $rottenSpots ?>"
                                 style="background: <?= $apple->getColorHex() ?>">
                                <?php if ($apple->eaten_percent > 0): ?>
                                    <div class="apple-eaten" style="width: <?= $apple->eaten_percent ?>%"></div>
                                <?php endif; ?>
                                <div class="apple-percent"><?= round($remainingPercent) ?>%</div>
                            </div>
                            <div class="apple-info">
                                <div class="apple-id"><?= $apple->id ?></div>
                                <div class="apple-apple-color"><?= $apple->color ?></div>
                                <div class="apple-date-appear"><?= Yii::$app->formatter->asDatetime($apple->date_appear, 'php:d-m-Y H:i:s') ?></div>
                                <div class="apple-status"><?= $apple->getStatusText() ?></div>
                            </div>
                        </div>

                        <?php if ($apple->getTimeToRotText()): ?>
                            <div class="rot-progress">
                                <?= $apple->getTimeToRotText() ?>
                                <?php if ($apple->getTimeToRot() > 0): ?>
                                    <?php $rottenPercent = 100 - (($apple->getTimeToRot() / (5 * 3600)) * 100); ?>
                                    <div class="rot-progress-bar">
                                        <div class="rot-progress-fill" style="width: <?= $rottenPercent ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="apple-actions">
                            <?php if ($apple->status == \backend\models\Apple::STATUS_ON_TREE): ?>
                                <?= Html::beginForm(['fall', 'id' => $apple->id], 'post', [
                                    'class' => 'apple-action-form',
                                    'data-pjax' => 1
                                ]) ?>
                                <?= Html::submitButton('⬇ Упасть', [
                                    'class' => 'btn btn-primary apple-action-btn',
                                    'title' => 'Упасть с дерева на землю'
                                ]) ?>
                                <?= Html::endForm() ?>
                            <?php endif; ?>

                            <?php $maxPercent = 100 - $apple->eaten_percent; ?>
                            <?= Html::beginForm(['eat', 'id' => $apple->id], 'post', [
                                'class' => 'apple-action-form',
                                'data-pjax' => 1
                            ]) ?>
                            <div class="apple-eat-form">
                                <?= Html::input('number', 'percent',
                                    min(25, $maxPercent),
                                    [
                                        'class' => 'apple-eat-input',
                                        'min' => 1,
                                        'max' => $maxPercent,
                                        'title' => 'Процент откусывания'
                                    ]
                                ) ?>
                                <?= Html::submitButton('🍽', [
                                    'class' => 'btn btn-warning apple-action-btn',
                                    'title' => 'Съесть указанный процент яблока'
                                ]) ?>
                            </div>
                            <?= Html::endForm() ?>

                            <?= Html::beginForm(['delete', 'id' => $apple->id], 'post', [
                                'class' => 'apple-action-form',
                                'data-pjax' => 1
                            ]) ?>
                            <?= Html::submitButton('🗑 Удалить', [
                                'class' => 'btn btn-danger apple-action-btn',
                                'title' => 'Удалить яблоко'
                            ]) ?>
                            <?= Html::endForm() ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php Pjax::end(); ?>
    </div>