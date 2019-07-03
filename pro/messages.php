<?php require 'pro_header.php'; ?>




<div class="main-container" id="main-container" style="opacity:0">

    <div class="left">
        <a-spin :spinning="spinning.left">
            <div class="main-header">
                <h3>Messages</h3>
                <p>All threads in classes you joined</p>
            </div>
            <div class="mes-item" @click="open_marks">
                <p style="color:rgb(255, 193, 37)">
                    <a-icon type="star"></a-icon>&nbsp;&nbsp;Starred
                </p>
            </div>
            <template v-if="!!user.joined_classes">
                <div class="mes-item">
                    <p style="color:rgb(90, 148, 241)">
                        <a-icon type="team"></a-icon>&nbsp;&nbsp;Classes
                        <a-button size="small" @click="reverse_order('classes')" style="font-size:14px;right:80px">
                            <a-icon type="sort-descending"></a-icon>
                        </a-button>
                        <a-button size="small" @click="display_class" v-html="display_classes_text"></a-button>
                    </p>
                </div>
                <template v-if="display_classes">
                    <div :style="class_push(user.classes_info[index].id)" v-for="(joined,index) in user.joined_classes" :class="'class-item ' + class_super(index)" @click="open_class(user.classes_info[index].id,index)" :id="'class_left'+user.classes_info[index].id">
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
                <p>
                    <a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Join a new Class
                </p>
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
    <!-- 增加主题结束 -->

    <!-- 新用户提示 -->
    <a-modal :title="guide.title" v-model="guide.visible" @ok="doneGuide(guide.step)" okText="Next" cancelText="Skip">
        <template v-if="guide.step == 1">
            <p>To give you more control and transparency around the data we collect, we’ve updated our Terms of Service and Privacy Policy. Here’s a brief summary of the changes:</p>
            <h3>Transparency</h3>
            <p>You can see what type of data we collect from you, where we store it, how long we keep it, and how it’s safeguarded.</p>
            <h3>Control</h3>
            <p>We own Pokers, but everything you put in it is (and has always been) 100% property of you. You have the ability to opt-in and out of emails and correct inaccurate data whenever you’d like, and teacher accounts have the ability to delete the team account and data. If you agree with the changes, click below.</p>
            <br />
            <p>By clicking 「OK」, you’re saying that you understand and agree to Pokers’s <a href="https://www.ouorz.com/pokers-privacy-policy.html" target="_blank">Privacy Policy</a> and <a href="https://www.ouorz.com/pokers-terms-of-service.html" target="_blank">Terms of Service</a>.</p>
        </template>
        <template v-if="guide.step == 2">
            <h2 style="letter-spacing:.5px">Collaborate in Threads</h2>
            <div class="intro-p">
                <p>
                    <a-icon type="appstore"></a-icon> Threads keep conversations organized by topic.
                </p>
                <p>
                    <a-icon type="form"></a-icon> Anyone can add a comment to give feedback or discuss an idea.
                </p>
                <p>
                    <a-icon type="file"></a-icon> All files in a class/thread can be found within 「Files」 Section
                </p>
            </div>
        </template>
        <template v-if="guide.step == 3">
            <h2 style="letter-spacing:.5px">Create / Join a class now</h2>
            <div class="intro-p">
                <p>Only teacher account can create / manage classes</p>
                <p>Students can join a class by clicking 「Join a new Class」 in the first column and enter the class ID code within 「Classes」 section</p>
            </div>
        </template>
    </a-modal>
    <!-- 新用户提示结束 -->


    <!-- 消息列表用户信息 -->
    <a-drawer width=640 placement="right" :closable="false" @close="view_close" :visible="view.visible">
        <a-spin :spinning="spinning.drawer">
            <div v-if="status.info">
                <div class="class-info-header class-member-header">
                    <div style="margin-right: 20px;">
                        <template v-if="!!opened_member_info.info.avatar">
                            <img :src="opened_member_info.info.avatar" class="class-item-img class-member-img" />
                        </template>
                        <div v-else>
                            <div class="class-img-default class-member-img">
                                <p>{{ opened_member_info.info.name.substring(0,1) }}</p>
                            </div>
                        </div>
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
            </div>
        </a-spin>
    </a-drawer>
    <!-- 结束消息列表用户信息 -->
    <!-- 主题信息修改 -->
    <a-modal title="Edit Thread" :visible="edit.visible" @ok="handle_edit_submit" :confirm-loading="edit.confirm_edit_loading" @cancel="handle_edit_cancel">
        <a-input placeholder="Thread Subject" v-model="edit.name">
            <a-icon slot="prefix" type="appstore" />
        </a-input>
    </a-modal>
    <!-- 主题信息修改结束 -->
    <!-- 内容段修改 -->
    <a-modal title="Edit Message" :visible="edit.mes.visible" @ok="handle_edit_mes_submit" :confirm-loading="edit.mes.confirm_edit_mes_loading" @cancel="handle_edit_mes_cancel">
        <a-input placeholder="Content" v-model="edit.mes.content">
            <a-icon slot="prefix" type="align-center" />
        </a-input>
    </a-modal>
    <!-- 内容段修改结束 -->
    <!-- 日志查看 -->
    <a-modal title="Logs" :visible="log.visible" :footer="null" @cancel="log.visible = false">
        <template v-if="!!opened_mes_info.logs.length">
            <div style="max-height: 60vh;overflow-y: auto;">
                <div v-for="log in opened_mes_info.logs" class="logs-info">
                    <div>
                        <h3>{{ log.speaker_name }} <em>{{ get_mes_date(log.date) }}</em></h3>
                    </div>
                    <div>
                        <p>{{ log.content }}</p>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <p class="mes-end" style="margin-bottom: 0px;margin-top: 0px;">- EOF -</p>
        </template>
    </a-modal>
    <!-- 内容段修改结束 -->
    <!-- office 内容预览 -->
    <a-modal :footer="null" :title="office.title" centered v-model="office.visible" @cancel="handle_office_close" width="80%">
        <iframe :src="'https://view.officeapps.live.com/op/embed.aspx?src=' + office.url" width="100%" height="600px" frameborder="0"></iframe>
    </a-modal>
    <!-- office 内容预览结束 -->

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
                    <div v-for="(class_c,index) in opened_mark_info.class_info" class="class-item" :id="'class_sub'+class_c.id" @click="open_class(class_c.id,index)">
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
                <template v-if="!Object.keys(opened_mark_info.class_c).length - 1 && !Object.keys(opened_mark_info.user).length - 1">
                    <p class="mes-end">- EOF -</p>
                </template>
            </template>
            <template v-else-if="status.thread">
                <div class="mes-header">
                    <p style="color:#666;">
                        <a-icon type="appstore"></a-icon>&nbsp;&nbsp;Thread
                        <a-button size="small" @click="reverse_order('threads')" style="right:85px;position:absolute">
                            <a-icon type="sort-descending" />
                        </a-button>
                        <a-button size="small" @click="add.visible_thread = true" style="right:20px;position:absolute">+ Add</a-button>
                    </p>
                </div>
                <template v-if="opened_thread_info.length">
                    <div v-for="(thread_c,index) in opened_thread_info" class="class-item" :id="'thread_sub'+thread_c.id" @click="open_mes(index,thread_c.id,thread_c.belong_class)">
                        <div class="thread-notify" v-if="thread_c.id == push.thread"></div>
                        <div>
                            <h3 v-html="thread_c.name"></h3>
                            <p>{{ thread_c.message_count }} messages</p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <p class="mes-end">- EOF -</p>
                </template>
            </template>
        </a-spin>
        <!-- 占位 -->
        <template v-if="!spinning.center && !status.thread && !status.mark">
            <div style="padding:20px 30px">
                <a-skeleton :paragraph="{rows: 2}" v-for="i in 6"></a-skeleton>
            </div>
        </template>
        <!-- 占位 -->
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
                <div class="mes-header mes-list">
                    <div>
                        <p style="color:#333;" v-html="opened_mes_info.thread_info.name"></p>
                        <p>{{ opened_mes_info.thread_info.message_count }} Messages | {{ opened_mes_info.unique_speakers.length }} Followers</p>
                    </div>
                    <div>
                        <a-button type="default" @click="reverse_order('meses')" style="margin-right:10px;font-size:16px">
                            <a-icon type="sort-descending"></a-icon>
                        </a-button>
                        <a-button type="default" @click="view_logs()" style="margin-right:10px;font-size:16px" v-if="user.info.type == 2">
                            <a-icon type="database"></a-icon>
                        </a-button>
                        <template v-if="parseInt(opened_class_info.superid) == parseInt(user.id)">
                            <a-button type="default" @click="delete_thread(opened_mes_info.thread_id)" style="margin-right:10px">
                                <a-icon style="color:#FF4040" type="delete"></a-icon>
                            </a-button>
                        </template>
                        <a-button type="default" @click="edit.visible = true">
                            <a-icon type="edit"></a-icon>
                        </a-button>
                    </div>
                </div>
                <div :class="mes_input.container" id="mes-container">
                    <div id="mes-inner">
                        <template v-if="spinning.loading">
                            <div style="padding:10px 40px">
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="(mes,index) in opened_mes_info.meses" class="mes-stream" @mouseenter="comment_action($event)" @mouseleave="comment_action_leave($event)" :style="same_speaker(mes.speaker,index)">
                                <div class="mes-stream-avatar" @click="view_user_info(mes.speaker)">
                                    <template v-if="opened_mes_info.speakers[0][mes.speaker.toString()] !== null">
                                        <img :src="opened_mes_info.speakers[0][mes.speaker.toString()]" class="class-item-img" />
                                    </template>
                                    <template v-else>
                                        <div class="class-img-default">
                                            <p>{{ opened_mes_info.speakers[1][mes.speaker.toString()].substring(0,1) }}</p>
                                        </div>
                                    </template>
                                </div>
                                <div class="mes-stream-content">
                                    <h3 v-html="opened_mes_info.speakers[1][mes.speaker.toString()] + '&nbsp;<em>' + get_mes_date(mes.date) + '</em>'"></h3>
                                    <template v-if="!!mes.content && !mes.img_url && mes.content !== 'null'">
                                        <div class="mes-content" v-html="process_content(mes.content)"></div>
                                    </template>
                                    <template v-else-if="!!mes.img_url">
                                        <template v-if="!!mes.content && mes.content !== 'null'">
                                            <div class="mes-content" v-html="process_content(mes.content)"></div>
                                        </template>
                                        <p>
                                            <a :href="mes.img_url" target="_blank"><img :src="mes.img_url" class="mes-stream-image" /></a>
                                        </p>
                                    </template>
                                    <template v-else-if="!!mes.file_name && !!mes.file_url">
                                        <p>
                                            <div class="mes-stream-file">
                                                <div :style="'color:'+ get_file_icon(get_suffix(mes.file_name).substr(1))[1]">
                                                    <a-icon :type="'file-' + get_file_icon(get_suffix(mes.file_name).substr(1))[0]"></a-icon>
                                                </div>
                                                <div>
                                                    <h3>{{ mes.file_name }}</h3>
                                                    <p><a :href="'../extension/download.php?filename='+mes.file_url" target="_blank">Download</a>
                                                        <template v-if="get_suffix(mes.file_name).substr(1) == 'pdf'">
                                                            <a-divider type="vertical"></a-divider><a :href="mes.file_url" target="_blank">Preview</a>
                                                        </template>
                                                        <template v-else-if="if_office(get_suffix(mes.file_name).substr(1))">
                                                            <a-divider type="vertical"></a-divider><a @click="open_office_preview(mes.file_url,mes.file_name)">Preview</a>
                                                        </template>
                                                    </p>
                                                </div>
                                            </div>
                                        </p>
                                    </template>
                                    <template v-if="mes.emoji_1 || mes.emoji_2 || mes.emoji_3">
                                        <div class="mes-stream-emoji-display">
                                            <a-tag color="pink" v-if="mes.emoji_1" @click="remove_emoji(1,mes.id)">
                                                <a-icon type="smile"></a-icon> x{{ parseInt(mes.emoji_1) }}
                                            </a-tag>
                                            <a-tag color="orange" v-if="mes.emoji_2" @click="remove_emoji(2,mes.id)">
                                                <a-icon type="meh"></a-icon> x{{ parseInt(mes.emoji_2) }}
                                            </a-tag>
                                            <a-tag color="blue" v-if="mes.emoji_3" @click="remove_emoji(3,mes.id)">
                                                <a-icon type="frown"></a-icon> x{{ parseInt(mes.emoji_3) }}
                                            </a-tag>
                                        </div>
                                    </template>
                                </div>
                                <div class="mes-stream-emoji">
                                    <template v-if="mes.speaker == user.id || opened_class_info.superid == user.id">
                                        <a class="a-d" @click="remove_mes(mes.id)">
                                            <a-icon type="delete"></a-icon>
                                        </a>
                                        <template v-if="mes.type !== 'file'">
                                            <a class="a-e" @click="open_mes_edit(mes.id,mes.content)">
                                                <a-icon type="edit"></a-icon>
                                            </a>
                                        </template>
                                    </template>
                                    <a class="a-1">
                                        <a-icon type="smile" @click="add_emoji(1,mes.id)"></a-icon>
                                    </a>
                                    <a class="a-2">
                                        <a-icon type="meh" @click="add_emoji(2,mes.id)"></a-icon>
                                    </a>
                                    <a class="a-3">
                                        <a-icon type="frown" @click="add_emoji(3,mes.id)"></a-icon>
                                    </a>
                                </div>
                            </div>
                            <p class="mes-end">- EOF -</p>
                        </template>
                    </div>
                </div>
                <div class="mes-unread-notify" v-show="unread.visible" @click="bottom_mes()">New Messages</div>
                <div :class="mes_input.input">
                    <div class="mes-stream-avatar" @click="view_user_info(user.id)">
                        <template v-if="user.info.avatar !== null">
                            <img :src="user.info.avatar" class="class-item-img" style="width: 30px !important;height: 30px !important;" />
                        </template>
                        <template v-else>
                            <div class="class-img-default" style="width: 30px !important;height: 30px !important;">
                                <p style="margin-top: 18px !important;">{{ user.info.name.substring(0,1) }}</a>
                            </div>
                        </template>
                    </div>
                    <template v-if="mes_input.disable">
                        <a-textarea :placeholder="mes_input.text" :rows="mes_input.rows" @focus="handle_input_up" v-model="mes_input.content" disabled></a-textarea>
                    </template>
                    <template v-else>
                        <a-textarea :placeholder="mes_input.text" :rows="mes_input.rows" @focus="handle_input_up" v-model="mes_input.content" id="message_input"></a-textarea>
                    </template>
                    <div class="mes-input-op" v-show="mes_input.op_display">
                        <div>
                            <a-tooltip placement="top">
                                <template slot="title">
                                    <span>MarkDown <a href="https://www.markdownguide.org/basic-syntax" target="_blank" style="color:#fff">
                                            <a-icon type="info-circle"></a-icon>
                                        </a></span>
                                </template>
                                <a-button :type="mes_input.markdown.btn" @click="handle_markdown">
                                    <a-icon type="down-square"></a-icon>
                                </a-button>

                            </a-tooltip>
                            <a-popover title="Upload a image" trigger="click" v-model="mes_input.visible.picture">
                                <template slot="content">
                                    <input type="file" name="upload_img" id="upload_img" @click="check_image_selected()" />
                                    <a-progress :percent="mes_input.progress_img" size="small" v-show="mes_input.img_progress"></a-progress>
                                    <br /><br />
                                    <template v-if="check.img.status">
                                        <a-button @click="mes_input.visible.picture = false;">Discard</a-button>
                                        <a-button type="primary" @click="upload_img">Upload</a-button>
                                    </template>
                                </template>
                                <a-tooltip placement="top">
                                    <template slot="title">
                                        <span>Upload an Image</span>
                                    </template>
                                    <a-button>
                                        <a-icon type="picture"></a-icon>
                                    </a-button>
                                </a-tooltip>
                            </a-popover>

                            <a-popover title="Upload a file" trigger="click" v-model="mes_input.visible.upload" @blur="handle_cancel_upload">
                                <template slot="content">
                                    <input type="file" name="upload_file" id="upload_file" @click="check_file_selected()" />
                                    <a-progress :percent="mes_input.progress_file" status="active" v-show="mes_input.file_progress" size="small"></a-progress>
                                    <br /><br />
                                    <template v-if="check.file.status">
                                        <a-button @click="handle_cancel_upload();mes_input.visible.upload = false;">Discard</a-button>
                                        <a-button type="primary" @click="upload_file">Upload</a-button>
                                    </template>
                                </template>
                                <a-tooltip placement="top">
                                    <template slot="title">
                                        <span>Upload a File</span>
                                    </template>
                                    <a-button>
                                        <a-icon type="cloud-upload"></a-icon>
                                    </a-button>
                                </a-tooltip>
                            </a-popover>

                        </div>
                        <div v-show="mes_input.visible.text">
                            <template v-if="check_able_send()">
                                <a-tooltip placement="top">
                                    <template slot="title">
                                        <span>Sending Method</span>
                                    </template>
                                    <a-button @click="enter_send()" style="margin-right:0px">{{ enter.text }}</a-button>
                                </a-tooltip>
                            </template>
                            <a-button @click="handle_input_down" style="margin-right:10px">Discard</a-button>
                            <template v-if="check_able_send()">
                                <a-button type="primary" @click="handle_input_send(mes_input.type)">{{ mes_input.send_text }}</a-button>
                            </template>
                            <template v-else>
                                <a-button type="primary" disabled>{{ mes_input.send_text }}</a-button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
            <!-- 消息框结束 -->




        </a-spin>
        <!-- 占位 -->
        <template v-if="!spinning.right && !status.chat && !status.user">
            <div style="padding:20px 30px">
                <a-skeleton avatar :paragraph="{rows: 1}" v-for="i in 9"></a-skeleton>
            </div>
        </template>
        <!-- 占位 -->
    </div>



</div>





</div>
<script src="../statics/js/md.js"></script>
<script type="text/javascript" src="../main/messages.js"></script>

<?php require 'pro_footer.php'; ?>