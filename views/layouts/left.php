<aside class="main-sidebar" style="overflow-y: scroll; height:100%;" <?php if(Yii::$app->user->isGuest){ echo "style=display:none";}?>>

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?=Yii::$app->user->identity->username?>
                    <?php

                    $s =Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);
                    foreach($s as $v){
                        echo '['.$v->name.']';
                    }
                    ?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> <?=Yii::t('app','欢迎您')?></a>
            </div>
        </div>

        <!-- search form -->
        <!--<form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>-->

        <!-- /.search form -->
        <?php
       use mdm\admin\components\MenuHelper;
        $callback = function($menu){
            //$data = eval($menu['data']);
            //if have syntax error, unexpected 'fa' (T_STRING)  Errorexception,can use
            $data = $menu['data'];
            return [
                'label' => $menu['name'],
                'url' => [$menu['route']],
                'option' => $data,
                'icon' => $menu['data'] ?$menu['data'] :'fa fa-circle-o' ,
                'items' => $menu['children'],
            ];
        };

       $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
        ?>

                <?php echo  dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu'],
                        'items' => $items
                    ]
                ) ?>

    </section>

</aside>
