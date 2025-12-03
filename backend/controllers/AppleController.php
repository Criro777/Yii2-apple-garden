<?php

namespace backend\controllers;

use backend\models\Apple;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AppleController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'fall' => ['POST'],
                    'eat' => ['POST'],
                    'generate' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Apple::find(),
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionFall($id): Response
    {
        $apple = $this->findModel($id);

        try {
            if ($apple->fallToGround()) {
                Yii::$app->session->setFlash('success', 'Яблоко упало на землю');
            } else {
                Yii::$app->session->setFlash('error', 'Не удалось уронить яблоко');
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * @throws Throwable
     * @throws NotFoundHttpException
     */
    public function actionEat($id): Response
    {
        $apple = $this->findModel($id);
        $percent = (float)Yii::$app->request->post('percent', 0);

        try {
            if ($apple->eat($percent)) {
                $flashMessage = $apple->eaten_percent == 100 ? "Яблоко полностью съедено" : "Съедено {$percent}% яблока";

                Yii::$app->session->setFlash('success', $flashMessage);
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * @throws Exception
     */
    public function actionGenerate(): Response
    {
        $count = mt_rand(1, 10);
        $generated = Apple::generateMultiple($count);

        if ($generated > 0) {
            Yii::$app->session->setFlash('success', "Сгенерировано {$generated} яблок");
        } else {
            Yii::$app->session->setFlash('warning', 'Не удалось сгенерировать ни одного яблока');
        }

        return $this->redirect(['index']);
    }

    public function actionDelete($id): Response
    {
        try {
            $apple = $this->findModel($id);
            if ($apple->delete() !== false) {
                Yii::$app->session->setFlash('success', 'Яблоко удалено');
            } else {
                Yii::$app->session->setFlash('error', 'Не удалось удалить яблоко');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel($id): ?Apple
    {
        if (($model = Apple::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Яблоко не найдено');
    }

}