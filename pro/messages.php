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
                    <div v-for="(joined,index) in user.joined_classes" class="class-item" @click="open_class(user.classes_info[index].id)">
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
            <div class="class-item" @click="add_class()">
                <?php if ($type == 1) { ?>
                    <p>
                        <a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Join a new Class
                    </p>
                <?php } else { ?>
                    <p>
                        <a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Create a new Class
                    </p>
                <?php } ?>
            </div>
        </a-spin>
    </div>

    <!-- 加入班级 -->
    <a-modal title="Join a new Class" :visible="add.visible" @ok="handle_join_submit" :confirm-loading="add.confirm_join_loading" @cancel="handle_create_cancel">
        <a-input placeholder="Class ID" v-model="add.join.id">
            <a-icon slot="prefix" type="team" />
        </a-input>
    </a-modal>
    <!-- 加入班级结束 -->

    <!-- 增加主题 -->
    <a-modal title="Add a new thread" :visible="add.visible_thread" @ok="handle_thread_submit(opened_class_info.id)" :confirm-loading="add.confirm_thread_loading" @cancel="handle_thread_cancel">
        <a-input placeholder="Thread Name" v-model="add.thread.name">
            <a-icon slot="prefix" type="appstore" />
        </a-input>
    </a-modal>
    <!-- 加入增加主题 -->

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
                    <div v-for="class_c in opened_mark_info.class_info" class="class-item" :id="'class_sub'+class_c.id" @click="open_class(class_c.id)">
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
                    <div v-for="user_c in opened_mark_info.user_info" class="class-item" :id="'member'+user_c.id" @click="open_user(user_c.id)">
                        <div style="margin-right: 15px;">
                            <template v-if="!!user_c.avatar">
                                <img :src="user_c.avatar" class="class-item-img" />
                            </template>
                            <template v-else>
                                <div class="class-img-default">
                                    <p>{{ user_c.name.substring(0,1) }}</p>
                                </div>
                            </template>
                        </div>
                        <div style="width:100%">
                            <h3 v-html="user_c.name"></h3>
                            <p v-html="get_level(user_c.type)"></p>
                        </div>
                    </div>
                </template>
            </template>
            <template v-else-if="status.thread">
                <div class="mes-header">
                    <p style="color:#666;">
                        <a-icon type="appstore"></a-icon>&nbsp;&nbsp;Thread
                        <a-button size="small" @click="add.visible_thread = true" style="right:20px;position:absolute">+ Add</a-button>
                    </p>
                </div>
                <div v-for="(thread_c,index) in opened_thread_info" class="class-item" :id="'thread_sub'+thread_c.id" @click="open_mes(index,thread_c.id,thread_c.belong_class)">
                    <div>
                        <h3 v-html="thread_c.name"></h3>
                        <p>{{ thread_c.message_count }} messages</p>
                    </div>
                </div>
            </template>
        </a-spin>
    </div>

    <div class="right">
        <a-spin :spinning="spinning.right">
            <template v-if="status.user">
                <div class="class-info-header class-member-header">
                    <div style="margin-right: 20px;">
                        <template v-if="!!opened_member_info.info.avatar">
                            <img :src="opened_member_info.info.avatar" class="class-item-img class-member-img" />
                        </template>
                        <template v-else>
                            <div class="class-img-default class-member-img">
                                <p>{{ opened_member_info.info.name.substring(0,1) }}</p>
                            </div>
                        </template>
                    </div>
                    <div class="class-info-info class-member-info" style="padding-top: 5px;">
                        <h2 v-html="opened_member_info.info.name"></h2>
                        <p>{{ get_level(opened_member_info.info.type) }} Account</p>
                    </div>
                    <div class="class-member-subscribe">
                        <template v-if="member_marked">
                            <a-button type="default" @click="demark_process(opened_member_info.info.id,'user')">
                                <a-icon type="star" style="color:#FFC125"></a-icon>
                            </a-button>
                        </template>
                        <template v-else>
                            <a-button type="default" @click="mark_process(opened_member_info.info.id,'user')">
                                <a-icon type="star"></a-icon>
                            </a-button>
                        </template>
                    </div>
                </div>
                <div class="class-member-content">
                    <a-button-group>
                        <a-button>
                            <a-icon type="heat-map"></a-icon>ID: jinitaimei{{ opened_member_info.info.id }}
                        </a-button>
                        <a-button>
                            <a-icon type="flag"></a-icon> Joined on: {{ get_date(opened_member_info.info.date) }}
                        </a-button>
                    </a-button-group>
                    <br /><br />
                    <a-button-group>
                        <a-button>
                            <a-icon type="mail"></a-icon> Email: {{ opened_member_info.info.email }}
                        </a-button>
                        <a-button type="primary"><a :href="'mailto:'+opened_member_info.info.email">Mail To</a></a-button>
                    </a-button-group>
                    <br /><br />
                    <a-button-group>
                        <a-button>
                            <a-icon type="team"></a-icon> Joined {{ opened_member_info.info.class.split(',').length }} Class
                        </a-button>
                    </a-button-group>
                </div>
            </template>



            <!-- 消息框开始 -->
            <template v-else-if="status.chat">
                <div class="mes-header">
                    <p style="color:#666;">
                        <a-icon type="appstore"></a-icon>&nbsp;&nbsp;{{ opened_mes_info.thread_info.name }}
                    </p>
                </div>
                <div :class="mes_input.container" id="mes-container">
                    <div id="mes-inner">
                        <div v-for="(mes,index) in opened_mes_info.meses" class="mes-stream">
                            <div class="mes-stream-avatar">
                                <template v-if="opened_mes_info.speakers[index].avatar !== null">
                                    <img :src="opened_mes_info.speakers[index].avatar" class="class-item-img" />
                                </template>
                                <template v-else>
                                    <div class="class-img-default">
                                        <p>{{ mes.speaker_name.substring(0,1) }}</p>
                                    </div>
                                </template>
                            </div>
                            <div class="mes-stream-content">
                                <h3 v-html="mes.speaker_name + '&nbsp;<em>' + get_mes_date(mes.date) + '</em>'"></h3>
                                <template v-if="!!mes.content && !mes.img_url">
                                    <p v-html="mes.content"></p>
                                </template>
                                <template v-else-if="!!mes.img_url">
                                    <template v-if="!!mes.content">
                                        <p>
                                            {{ mes.content }}
                                        </p>
                                    </template>
                                    <p>
                                        <img :src="mes.img_url" class="mes-stream-image" />
                                    </p>
                                </template>
                                <template v-else-if="!!mes.file_name && !!mes.file_url">
                                    <p>
                                        <div class="mes-stream-file">
                                            <div>
                                                <a-icon :type="'file-' + get_suffix(mes.file_name).substr(1)"></a-icon>
                                            </div>
                                            <div>
                                                <h3>{{ mes.file_name }}</h3>
                                                <p style=""><a :href="mes.file_url">Download</a></p>
                                            </div>
                                        </div>
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="mes_input.input">
                    <template v-if="mes_input.disable">
                        <a-textarea placeholder="Add a comment..." :rows="mes_input.rows" @focus="handle_input_up" v-model="mes_input.content" disabled></a-textarea>
                    </template>
                    <template v-else>
                        <a-textarea placeholder="Add a comment..." :rows="mes_input.rows" @focus="handle_input_up" v-model="mes_input.content"></a-textarea>
                    </template>
                    <div class="mes-input-op" v-show="mes_input.op_display">
                        <div>
                            <a-button size="small">
                                <a-icon type="line-height"></a-icon>
                            </a-button>

                            <a-popover title="Upload a image" trigger="click" v-model="mes_input.visible.picture" @blur="handle_cancel_upload">
                                <template slot="content">
                                    <input type="file" name="upload_img" id="upload_img" />
                                    <a-progress :percent="mes_input.progress_img" status="active" v-show="mes_input.img_progress"></a-progress>
                                    <br /><br />
                                    <a-button @click="handle_cancel_upload();mes_input.visible.picture = false;">Discard</a-button>
                                    <a-button type="primary" @click="upload_file">Upload</a-button>
                                </template>
                                <a-button size="small">
                                    <a-icon type="picture"></a-icon>
                                </a-button>
                            </a-popover>

                            <a-popover title="Upload a file" trigger="click" v-model="mes_input.visible.upload" @blur="handle_cancel_upload">
                                <template slot="content">
                                    <input type="file" name="upload_file" id="upload_file" />
                                    <a-progress :percent="mes_input.progress_file" status="active" v-show="mes_input.file_progress"></a-progress>
                                    <br /><br />
                                    <a-button @click="handle_cancel_upload();mes_input.visible.upload = false;">Discard</a-button>
                                    <a-button type="primary" @click="upload_file">Upload</a-button>
                                </template>
                                <a-button size="small">
                                    <a-icon type="cloud-upload"></a-icon>
                                </a-button>
                            </a-popover>

                        </div>
                        <div v-show="mes_input.visible.text">
                            <a-button @click="handle_input_down">Discard</a-button>
                            <a-button type="primary" @click="handle_input_send">Send</a-button>
                        </div>
                    </div>
                </div>
            </template>
            <!-- 消息框结束 -->




        </a-spin>
    </div>



</div>





</div>
<script type="text/javascript" src="../main/messages.js"></script>


<?php require 'pro_footer.php'; ?>