<?php require 'pro_header.php'; ?>




<div class="main-container">

    <div class="left">
        <a-spin :spinning="spinning.left">
            <div class="main-header">
                <h3>Messages</h3>
                <p>All threads you involved</p>
            </div>
            <div class="mes-item">
                <p style="color:rgb(90, 148, 241)">
                    <a-icon type="inbox"></a-icon>&nbsp;&nbsp;Inbox
                </p>
            </div>
            <div class="mes-item" @click="open_marks">
                <p style="color:rgb(255, 193, 37)">
                    <a-icon type="star"></a-icon>&nbsp;&nbsp;Starred
                </p>
            </div>
            <template v-if="!!user.joined_classes">
                <div class="mes-item">
                    <p>
                        <a-icon type="team"></a-icon>&nbsp;&nbsp;Classes
                        <a-button size="small" @click="display_class" v-html="display_classes_text"></a-button>
                    </p>
                </div>
                <template v-if="display_classes">
                    <div v-for="(joined,index) in user.joined_classes" class="class-item">
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
                            <h3 v-html="user.classes_info[index].name"></h3>
                            <p v-html="user.classes_info[index].des"></p>
                        </div>
                    </div>
                </template>
            </template>
        </a-spin>
    </div>

    <div class="center class-center mes-column">
        <a-spin :spinning="spinning.center">
            <template v-if="status.mark">
                <div class="mes-header">
                    <p style="color:#666;">
                    <a-icon type="star"></a-icon>&nbsp;&nbsp;Starred
                    </p>
                </div>
                <template v-if="Object.keys(opened_mark_info.class_c).length - 1">
                <p class="mes-sub-header">Classes</p>
                    <div v-for="class_c in opened_mark_info.class_info" class="class-item" :id="'class_sub'+user.id">
                        <div style="margin-right: 10px;">
                            <template v-if="!!class_c.img">
                                <img :src="class_c.img" class="class-item-img" />
                            </template>
                            <template v-else>
                                <div class="class-img-default">
                                    <p>{{ class_c.name.substring(0,1) }}</p>
                                </div>
                            </template>
                        </div>
                        <div>
                            <h3 v-html="class_c.name"></h3>
                            <p v-html="class_c.des"></p>
                        </div>
                    </div>
                </template>
                <template v-if="Object.keys(opened_mark_info.user).length - 1">
                <p class="mes-sub-header">Users</p>
                <div v-for="user in opened_mark_info.user_info" class="class-item" :id="'user'+user.id">
                    <div style="margin-right: 15px;">
                        <template v-if="!!user.avatar">
                            <img :src="user.avatar" class="class-item-img" />
                        </template>
                        <template v-else>
                            <div class="class-img-default">
                                <p>{{ user.name.substring(0,1) }}</p>
                            </div>
                        </template>
                    </div>
                    <div style="width:100%">
                        <h3 v-html="user.name"></h3>
                        <p v-html="get_level(user.type)"></p>
                    </div>
                </div>
                </template>
            </template>
        </a-spin>
    </div>

    <div class="right">
        <a-spin :spinning="spinning.right">
            <template v-if="status.chat">
            </template>
        </a-spin>
    </div>



</div>







</div>
<script type="text/javascript" src="../main/messages.js"></script>


<?php require 'pro_footer.php'; ?>