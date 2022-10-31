<?php
use app\models\Message;
use app\models\Notification;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark navbar-dark">
	<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item"><a class="nav-link" data-widget="pushmenu"
			href="#" role="button"><i class="fas fa-bars"></i></a></li>
		<li class="nav-item d-none d-sm-inline-block"><a
			href="<?=\yii\helpers\Url::home()?>" class="nav-link">Home</a></li>
		<?php if(false){ ?>
		<li class="nav-item d-none d-sm-inline-block"><a href="#"
			class="nav-link">Contact</a></li>
		<?php } ?>
	</ul>

	<!-- SEARCH FORM -->
	<form class="form-inline ml-3">
		<div class="input-group input-group-sm">
			<input class="form-control form-control-navbar" type="search"
				placeholder="Search" aria-label="Search">
			<div class="input-group-append">
				<button class="btn btn-navbar" type="submit">
					<i class="fas fa-search"></i>
				</button>
			</div>
		</div>
	</form>

	<!-- Right navbar links -->
	<ul class="navbar-nav ml-auto">
		<!-- Navbar Search -->
		<li class="nav-item"><a class="nav-link" data-widget="navbar-search"
			href="#" role="button"> <i class="fas fa-search"></i>
		</a>
			<div class="navbar-search-block">
				<form class="form-inline">
					<div class="input-group input-group-sm">
						<input class="form-control form-control-navbar" type="search"
							placeholder="Search" aria-label="Search">
						<div class="input-group-append">
							<button class="btn btn-navbar" type="submit">
								<i class="fas fa-search"></i>
							</button>
							<button class="btn btn-navbar" type="button"
								data-widget="navbar-search">
								<i class="fas fa-times"></i>
							</button>
						</div>
					</div>
				</form>
			</div></li>

		<!-- Messages Dropdown Menu -->
		<?php

if (true) {
    $msgs = Message::find()->where([
        'user_id' => Yii::$app->user->id
    ])
        ->groupBy([
        'created_by'
    ])
        ->orderBy([
        'id' => SORT_ASC
    ]);

    ?>
			<li class="nav-item dropdown"><a class="nav-link"
			data-toggle="dropdown" href="#"> <i class="far fa-comments"></i> <span
				class="badge badge-danger navbar-badge"><?php echo $msgs->count()?></span>
		</a>
			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
				<?php
    foreach ($msgs->each() as $msg) {

        $messenger = Users::find()->cache()
            ->where([
            'id' => $msg->created_by
        ])
            ->one();

        if (! empty($messenger)) {
            ?>
				<a href="#" class="dropdown-item" data-widget="chat-pane-toggle"> <!-- Message Start -->
					<div class="media">
						<img src="<?=$messenger->getImageUrl()?>" class="profile_pic mr-4"
							height="50px" width="50px" alt="User Avatar"
							class="img-size-50 mr-3 img-circle">
						<div class="media-body">
							<h3 class="dropdown-item-title">
								<?=$messenger->username?>
							</h3>
							<p class="text-sm badge btn-outline-secondary"><?=$msg->message?></p>
							<p class="text-sm text-muted">
								<i class="far fa-clock mr-1"></i><?php echo Users::getTime($msg->created_on)?>
							</p>
						</div>
					</div> <!-- Message End -->
				</a>
				<div class="dropdown-divider"></div>	
				<?php } }?>
				
				<a href="<?= Url::toRoute(['user/chat'])?>"
					class="dropdown-item dropdown-footer">See All Messages</a>
			</div></li>
			<?php }?>
		<!-- Notifications Dropdown Menu -->
		<li class="nav-item dropdown">
		<?php if (!Yii::$app->user->isGuest) {?>
		<?php $notifications = Notification::find()->cache(10)->where(['!=','state_id', Notification::STATE_DELETED])->andWhere(['to_user_id'=>Yii::$app->user->identity->id])->orderBy(['id'=>SORT_DESC])?>
		<a class="nav-link" data-toggle="dropdown" href="notification/index">
				<i class="far fa-bell"></i> <span
				class="badge badge-warning navbar-badge"><?= $notifications->count() ?></span>
		</a>
			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right"
				style="width: 400px;">
				<span class="dropdown-header"><?= $notifications->count() ?> Notifications</span>
				<div class="dropdown-item dropdown-body overflow-auto"
					style="scrollbar-width: none;"> 
              <?php

    foreach ($notifications->each() as $notification) {
        $color = $notification->getColor($notification->type_id);
        ?>
              <div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item"> <i
						class="fa fa-<?=$notification->icon ?> mr-1 ml-n2 text-<?=$color?>"></i> <?= $notification->title ?>  
                  <span class="float-right text-muted text-sm"
						style="font-size: 0.6rem"><?= $notification->getTime() ?></span>
					</a>
              <?php } ?>
              </div>
				<div class="dropdown-divider"></div>  
              <?= Html::a('Clear All Notifications', ['notification/clear'], ['class'=>"dropdown-item dropdown-footer"]) ?>
            </div>
		</li>
		<?php }?>
		<li class="nav-item"></li>

		<li class="nav-item dropdown"><a class="nav-link"
			data-toggle="dropdown" href="#"> <i class="far fa-user"></i>
		</a>
			<div class="dropdown-menu dropdown-menu-right">
                <?= Html::a('Logout <i class="fas fa-sign-out-alt"></i>', ['/site/logout'], ['data-method' => 'post', 'class' => 'nav-link']) ?>
            </div></li>
	</ul>
</nav>
<!-- /.navbar -->