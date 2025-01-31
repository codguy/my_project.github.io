<?php
use app\models\Message;
use app\models\Notification;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<style>
.ui-autocomplete {
	top: 47px;
	left: 226.517px;
	width: 224px;
	z-index: 99999;
	opacity: .9;
	color: white;
background-color: #495057;
border: 0px;
}
</style>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark navbar-dark">
	<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
		<li class="nav-item d-none d-sm-inline-block"><a href="<?=\yii\helpers\Url::home()?>" class="nav-link">Home</a></li>
		<?php if(true){ ?>
		<li class="nav-item d-none d-sm-inline-block"><a href="<?=Url::toRoute(['site/contact'])?>" class="nav-link">Contact</a></li>
		<?php } ?>
	</ul>
	<!-- SEARCH FORM -->
	<form class="form-inline ml-3">
		<div class="input-group input-group-sm">
			<?php
$model = new Users();
$form = ActiveForm::begin([
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]);
echo $form->field($model, 'username')
    ->widget(\yii\jui\AutoComplete::classname(), [
    'clientOptions' => [
        'source' => Users::find()->select('Username')
            ->limit(10)
            ->column(),
        'enabled' => true
    ],
    'options' => [
        'placeholder' => 'Search',
        'class' => 'form-control form-control-navbar',
    ]
])
    ->label(false);
ActiveForm::end();
?>
		</div>
	</form>
	<!-- Right navbar links -->
	<ul class="navbar-nav ml-auto">
		<!-- Navbar Search -->
		
		<!-- Messages Dropdown Menu -->
		<?php

if (true) {
    $msgs = Message::find()->where([
        'user_id' => Yii::$app->user->id
    ])
        ->groupBy([
        'created_by_id'
    ])
        ->orderBy([
        'id' => SORT_ASC
    ]);

    ?>
			<li class="nav-item dropdown"><a class="nav-link" data-toggle="dropdown" href="#"> <i class="far fa-comments"></i> <span class="badge badge-danger navbar-badge"><?php echo $msgs->count()?></span>
		</a>
			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
				<?php
    foreach ($msgs->each() as $msg) {

        $messenger = Users::find()->cache()
            ->where([
            'id' => $msg->created_by_id
        ])
            ->one();

        if (! empty($messenger)) {
            ?>
				<a href="#" class="dropdown-item" data-widget="chat-pane-toggle"> <!-- Message Start -->
					<div class="media">
						<img src="<?=$messenger->getImageUrl()?>" class="profile_pic mr-4" height="50px" width="50px" alt="User Avatar" class="img-size-50 mr-3 img-circle">
						<div class="media-body">
							<h3 class="dropdown-item-title">
								<?=$messenger->username?>
							</h3>
							<p class="text-sm badge btn-outline-secondary"><?=$msg->message?></p>
							<p class="text-sm text-muted">
								<i class="far fa-clock mr-1"></i><?php echo $msg->getTime() ?>
							</p>
						</div>
					</div> <!-- Message End -->
				</a>
				<div class="dropdown-divider"></div>	
				<?php } }?>
				
				<a href="<?= Url::toRoute(['user/chat'])?>" class="dropdown-item dropdown-footer">See All Messages</a>
			</div></li>
			<?php }?>
		<!-- Notifications Dropdown Menu -->
		<li class="nav-item dropdown">
		<?php

if (! Yii::$app->user->isGuest) {
    $notifications = Notification::find()->cache(10)
        ->where([
        '!=',
        'state_id',
        Notification::STATE_DELETED
    ])
        ->andWhere([
        'to_user_id' => Yii::$app->user->identity->id
    ])
        ->orderBy([
        'id' => SORT_DESC
    ])?>
		<a class="nav-link" data-toggle="dropdown" href="notification/index"> <i class="far fa-bell"></i> <span class="badge badge-warning navbar-badge"><?= $notifications->count() ?></span>
		</a>
			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="width: 400px;">
				<span class="dropdown-header"><?= $notifications->count() ?> Notifications</span>
				<div class="dropdown-item dropdown-body overflow-auto" style="scrollbar-width: none;"> 
              <?php

    foreach ($notifications->each() as $notification) {
        $color = $notification->getColor($notification->type_id);

        ?>
              <div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item"> <i class="fa fa-<?=$notification->icon ?> mr-1 ml-n2 text-<?=$color?>"></i> <?=$notification->title?>  
                  <span class="float-right text-muted text-sm" style="font-size: 0.6rem"><?= $notification->getTime() ?></span>
					</a>
              <?php
    }
    ?>
              </div>
				<div class="dropdown-divider"></div>  
              <?=Html::a('Clear All Notifications', ['notification/clear'], ['class' => "dropdown-item dropdown-footer"])?>
            </div>
		</li>
		<?php
}
?>
		<li class="nav-item"></li>
		<li class="nav-item dropdown"><a class="nav-link" data-toggle="dropdown" href="#"> <i class="far fa-user"></i>
		</a>
			<div class="dropdown-menu dropdown-menu-right">
                <?=Html::a('Logout <i class="fas fa-sign-out-alt"></i>', ['/site/logout'], ['data-method' => 'post','class' => 'nav-link text-black'])?>
            </div></li>
	</ul>
</nav>
<!-- /.navbar -->