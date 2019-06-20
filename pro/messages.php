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
            <div class="mes-item">
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

    <div class="center class-center">
        <a-spin :spinning="spinning.center">
            <template>
            </template>
        </a-spin>
    </div>

    <div class="right">
        <a-spin :spinning="spinning.right">
            <template>
            </template>
        </a-spin>
    </div>



</div>







</div>
<script type="text/javascript" src="../main/messages.js"></script>


<?php require 'pro_footer.php'; ?>