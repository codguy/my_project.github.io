<?php
namespace app\controllers;

use app\components\NewBaseController;
use app\models\ContactForm;
use app\models\EmailTemplate;
use app\models\Feed;
use app\models\LoginForm;
use app\models\Notification;
use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

class SiteController extends NewBaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'create',
                    'login',
                    'logout',
                    'contact',
                    'about',
                    'sign-up',
                    'create-email-template',
                    'update-email-template',
                    'delete-email-template'
                ],
                'rules' => [
                    [
                        'actions' => [
                            'login',
                            'logout',
                            'contact',
                            'about',
                            'sign-up',
                            'create-email-template',
                            'update-email-template',
                            'delete-email-template'
                        ],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return true;
                        }
                    ],
                    [
                        'actions' => [
                            'create'
                        ],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Users::isManager();
                        }
                    ]
                ]
            ],
            
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => [
                        'post'
                    ],
                    'delete-email-template' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ]
        ];
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            // change layout for error action after
            // checking for the error action name
            // so that the layout is set for errors only
            if ($action->id == 'error') {
                $this->layout = 'blank2';
            }
            return true;
        }
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            $this->layout = 'blank2';
            return $this->render('home');
        }
        $model = new Feed();
        return $this->render('index', [
            'model' => $model
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = 'blank';
        if (! Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            \Yii::$app->session->setFlash('success', 'Welcome Back !');
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model
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
        $this->layout = 'blank2';
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model
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

    public function actionSignUp()
    {
        $this->layout = 'blank';
        $model = new Users();
        $obj = rand(10, 99);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->created_on = date('Y-m-d H:i:s');
                $model->updated_on = date('Y-m-d H:i:s');
                $model->created_by_id = ! empty(\Yii::$app->user->id) ? \Yii::$app->user->id : Users::ROLE_ADMIN;
                $model->state_id = Users::STATE_ACTIVE;
                $model->authKey = 'test' . $obj . '.key';
                $model->accessToken = $obj . '-token';
                if (UploadedFile::getInstance($model, 'profile_picture') != null) {
                    $model->profile_picture = UploadedFile::getInstance($model, 'profile_picture');
                    $model->profile_picture = $model->upload();
                }
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        $title = 'New ' . $model->getRole($model->roll_id);
                        $type = Notification::TYPE_NEW;
                        $users = Users::find()->where([
                            '<=',
                            'roll_id',
                            Users::ROLE_TRAINER
                        ]);
                        foreach ($users->each() as $user) {
                            Notification::createNofication($title, $type, $model, $user->id, 'user');
                        }
                        Notification::createNofication('Welcome', Notification::TYPE_SUCCESS, $model, $model->id, 'user');
                        $login = new LoginForm();
                        $login->setAttributes($model->attributes);
                        Yii::$app->user->login($model, 3600 * 24 * 30);
                        return $this->redirect([
                            'user/view',
                            'id' => $model->id
                        ]);
                    } else {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return $model->getErrors();
                    }
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('sign-up', [
            'model' => $model
        ]);
    }

    public function actionCreateEmailTemplate()
    {
        $email_template = new EmailTemplate();
        $post = \Yii::$app->request->post();
        if (! empty($post)) {
            $email_template->title = $post['title'];
            $email_template->html = $post['html'];
            $email_template->json = $post['json'];
            $email_template->created_by_id = \Yii::$app->user->id;
            $email_template->created_on = date('Y-m-d H:i:s');
            $email_template->updated_on = date('Y-m-d H:i:s');
            if ($email_template->save()) {
                return $this->refresh();
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $email_template->getErrors();
            }
        }
        $query = EmailTemplate::find();
        $dataProvder = new ActiveDataProvider([
            'query' => $query
        ]);
        return $this->render('_email_template', [
            'dataProvider' => $dataProvder
        ]);
    }
    
    public function actionSeeEmailTemplate($id)
    {
        $template = EmailTemplate::findOne($id);
        return $template->html;
    }

    public function actionUpdateEmailTemplate($id)
    {}
    
    public function actionCloneEmailTemplate($id)
    {
        $new_template = new EmailTemplate();
        $old_template = EmailTemplate::findOne($id);
        if (! empty($old_template)) {
            $new_template->title = $old_template->title;
            $new_template->html = $old_template->html;
            $new_template->json = $old_template->json;
            $new_template->created_by_id = \Yii::$app->user->id;
            $new_template->created_on = date('Y-m-d H:i:s');
            $new_template->updated_on = date('Y-m-d H:i:s');
            if ($new_template->save()) {
                return $this->redirect([
                    'create-email-template'
                ]);
            } else {
                return print_r($new_template->getErrors());
            }
        }
    }

    public function actionDeleteEmailTemplate($id)
    {
        $model = EmailTemplate::findOne($id);

        $model->delete();

        return $this->redirect([
            'create-email-template'
        ]);
    }
}
