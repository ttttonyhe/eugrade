<?php require 'pro_header.php'; ?>




<div class="main-container">
    <div class="left" spinning="loading.left">
        <div class="main-header">
            <h3>All Classes</h3>
            <p>All the classes you joined</p>
        </div>
        <div v-for="(joined,index) in user.joined_classes" :class="'class-item ' + class_super(index)" @click="open_class_info(index)">
            <div style="margin-right: 10px;">
                <template v-if="!!user.classes_info[index].img">
                    <img :src="user.classes_info[index].img" class="class-item-img" />
                </template>
                <template v-else>
                    <div class="class-img-default">
                        <p>{{ user.classes_info[index].name.substring(0,1) }}</p>
                    </div>
                </template>
            </div>
            <div>
                <h3 v-html="user.classes_info[index].name+'<em>'+user.classes_info[index].member.split(',').length+'</em>'"></h3>
                <p v-html="user.classes_info[index].des"></p>
            </div>
        </div>
        <div class="class-item" @click="add_class()">
            <?php if($type == 1){ ?>
                <p><a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Join a new Class</p>
            <?php }else{ ?>
                <p><a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Create a new Class</p>
            <?php } ?>
        </div>
    </div>


    <?php if((int)$type == 2){ ?>
    <!-- 新建班级 -->
    <a-modal
      title="Create a new Class"
      :visible="add.visible"
      @ok="handle_create_submit"
      :confirm-loading="add.confirm_create_loading"
      @cancel="handle_create_cancel"
    >
      <p>Hello</p>
    </a-modal>
    <!-- 新建班级结束 -->
    <?php }else{ ?>
        <!-- 加入班级 -->
    <a-modal
      title="Join a new Class"
      :visible="add.visible"
      @ok="handle_join_submit"
      :confirm-loading="add.confirm_join_loading"
      @cancel="handle_join_cancel"
    >
      <p>Hello</p>
    </a-modal>
    <!-- 加入班级结束 -->
    <?php } ?>


    <div class="center class-center" spinning="spinning.center">
        <template v-if="opened_class_info.status">
            <div class="class-info-header">
                <div style="margin-right: 12px;">
                    <template v-if="!!opened_class_info.img">
                        <img :src="opened_class_info.img" class="class-item-img class-info-img" />
                    </template>
                    <template v-else>
                        <div class="class-img-default class-info-img">
                            <p>{{ opened_class_info.name.substring(0,1) }}</p>
                        </div>
                    </template>
                </div>
                <div class="class-info-info">
                    <h2 v-html="opened_class_info.name"></h2>
                    <p>{{ opened_class_info.members.length }} Members</p>
                </div>
                <div class="class-settings">
                    <a-dropdown placement="bottomRight">
                        <a-button>Settings</a-button>
                        <a-menu slot="overlay">
                            <a-menu-item>
                                <a target="_blank" rel="noopener noreferrer" href="http://www.alipay.com/">1st menu item</a>
                            </a-menu-item>
                            <a-menu-item>
                                <a target="_blank" rel="noopener noreferrer" href="http://www.taobao.com/">2nd menu item</a>
                            </a-menu-item>
                            <a-menu-item>
                                <a target="_blank" rel="noopener noreferrer" href="http://www.tmall.com/">3rd menu item</a>
                            </a-menu-item>
                        </a-menu>
                    </a-dropdown>
                </div>
            </div>
            <div class="class-info-des">
                <p v-html="opened_class_info.des"></p>
            </div>
            <div class="class-info-admin">
                <p>{{ opened_class_info.supername }} is the admin</p>
            </div>
            <div>
                <div v-for="(member,index_info) in opened_class_info.members" class="class-item class-info-member">
                    <div style="margin-right: 15px;">
                        <template v-if="!!member.avatar">
                            <img :src="member.avatar" class="class-item-img" />
                        </template>
                        <template v-else>
                            <div class="class-img-default">
                                <p>{{ member.name.substring(0,1) }}</p>
                            </div>
                        </template>
                    </div>
                    <div style="width:100%">
                        <h3 v-html="member.name"></h3>
                        <p v-html="get_level(member.type)"></p>
                    </div>
                    <div class="class-info-member-icon">
                        <a-icon type="right-circle" />
                    </div>
                </div>
            </div>
        </template>
    </div>
    <div class="right"></div>
</div>
















</div>
<script type="text/javascript" src="../main/classes.js"></script>


<?php require 'pro_footer.php'; ?>