<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Catalogs;
use yii\base\DynamicModel;
use yii\base\UserException;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Отрисовывает страницу с каталогами
     * @return string
     */
    public function actionCatalogs()
    {
        $catalogs = Catalogs::getAllCatalogsArray();
        return $this->render('catalogs', ['catalogs' => $catalogs]);
    }

    /**
     * Позволяет работать с каталогами (Получать, добавлять, удалять и редактировать)
     * @return array|\yii\db\ActiveRecord[]
     * @throws UserException
     */
    public function actionWorkWithCatalog ()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isGet){
            return Catalogs::getAllCatalogsArray();
        }

        if (Yii::$app->request->isPost){
            try {
                $data = Yii::$app->request->post('item');
                $validateData = DynamicModel::validateData($data,
                    [
                        ['TITLE', 'string', 'min' => 0, 'max' => 100, 'message' => 'Слишком длинное описание'],
                        ['DEPTH', 'integer', 'min' => 0, 'max' => 11, 'message' => 'Некорректное значение глубины'],
                        ['PARENTID', 'exist', 'targetClass' => Catalogs::class, 'targetAttribute' => 'ID'],
                    ]);
                Catalogs::AddCatalog($validateData);
                return Catalogs::getAllCatalogsArray();
            } catch (\Exception $e) {
                throw new UserException('Ошибка входных данных', 0, $e);
            }

        }

        if (Yii::$app->request->isDelete){
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $id = Yii::$app->request->post('Id');
                $validateId = DynamicModel::validateData(['ID' => $id],
                    [
                        ['ID', 'exist', 'targetClass' => Catalogs::class, 'targetAttribute' => 'ID'],
                    ]);
                Catalogs::DeleteCatalog($validateId->ID);
                $transaction->commit();
                return Catalogs::getAllCatalogsArray();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw new UserException('Ошибка входных данных', 0, $e);
            }

        }

        if (Yii::$app->request->isPatch){
            try {
                $data = Yii::$app->request->post('item');
                $validateData = DynamicModel::validateData($data,
                    [
                        ['TITLE', 'string', 'min' => 0, 'max' => 100, 'message' => 'Слишком длинное описание'],
                        ['DEPTH', 'integer', 'min' => 0, 'max' => 11, 'message' => 'Некорректное значение глубины'],
                        ['PARENTID', 'exist', 'targetClass' => Catalogs::class, 'targetAttribute' => 'ID'],
                    ]);
                Catalogs::EditCatalog($validateData);
                return Catalogs::getAllCatalogsArray();
            } catch (\Exception $e) {
                throw new UserException('Ошибка входных данных', 0, $e);
            }
        }
    }
}
