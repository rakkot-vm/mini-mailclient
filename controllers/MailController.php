<?php

namespace app\controllers;

use Yii;
use app\models\Mail;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MailController implements the CRUD actions for Mail model.
 */
class MailController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists outbox.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Mail::find()->where(['mailfrom' => Yii::$app->params['mailParams']['email']]),
        ]);

        return $this->render('outbox', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all outbox.
     * @return mixed
     */
    public function actionOutbox()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Mail::find(),
        ]);

        return $this->render('Outbox', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Mail model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Mail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Mail();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()){
                Yii::$app->session->addFlash('success', 'Письмо сохранено');
                return $this->redirect(['view']);
            } else {
                Yii::$app->session->addFlash('error', 'Ошибка сохранения письма');
            }
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Send a Mail.
     * @return mixed
     */
    public function actionSend()
    {
        $model = new Mail();

        if ($model->load(Yii::$app->request->post())) {
            if($model->send()) {
                Yii::$app->session->addFlash('success', 'Письмо отправлено');
                return $this->redirect(['oubox']);
            }else{
                Yii::$app->session->addFlash('error', 'Ошибка сохранения письма, писмо не отправлено');
            }
        }

        return $this->render('send', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Mail model.
     * If deletion is successful, the browser will be redirected to the 'oubox' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->addFlash('success', 'Письмо удалено');

        return $this->redirect(['oubox']);
    }

    public function actionMultiDelete()
    {
        if($ids = Yii::$app->request->post('ids'))
        {
            $arrId = explode(',', $ids);
            foreach ($arrId as $id){
                $this->findModel($id)->delete();
            }
            Yii::$app->session->addFlash('success', 'Письма удалены');
            return $this->redirect(['oubox']);
        }
        return false;
    }

    /**
     * Finds the Mail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Mail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Mail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Lists all inbox.
     * @return mixed
     */
    public function actionInbox($update = '')
    {
        if($update) $this->actionUpdateInbox();

        $dataProvider = new ActiveDataProvider([
            'query' => Mail::find()->where(['mailto' => Yii::$app->params['mailParams']['email']]),
        ]);

        return $this->render('inbox', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateInbox()
    {
        $imap = Mail::getImap();

        $msg_num = imap_num_msg($imap);

        for($i = 1; $i <= $msg_num; $i++){
            $model = new Mail();
            $model->getImapMail($imap, $i);
            $model->save();
        }
        imap_close($imap);
    }

//    /**
//     * Updates an existing Mail model.
//     * If update is successful, the browser will be redirected to the 'view' page.
//     * @param integer $id
//     * @return mixed
//     * @throws NotFoundHttpException if the model cannot be found
//     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//        ]);
//    }
}
