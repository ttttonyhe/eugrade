<?php require 'pro_header.php'; ?>

<script>
    if (cookie.get('eugrade_lang') == 'zh_cn') {
        var lang_json = {
            title: {
                1: '消息',
                2: '全部已加入班级消息主题'
            },
            tab: {
                1: '星标收藏',
                2: '班级列表',
                3: '加入新班级',
                4: '班级 ID',
                5: '主题',
                6: '增加新主题',
                7: '主题名',
                8: '用户',
                9: '条消息',
                10: '增加'
            },
            chat: {
                1: '条消息',
                2: '位参与者',
                3: '关闭',
                4: '移除',
                5: '置顶消息',
                6: '置顶',
                7: '删除',
                8: '编辑',
                9: '编辑消息',
                10: '编辑主题',
                11: '班级日志',
                12: '消息日志',
                13: '新消息',
                14: '取消',
                15: '上传',
                16: '上传图像',
                17: '上传文件',
                18: '发送方式'
            },
        }
    } else {
        var lang_json = {
            title: {
                1: 'Messages',
                2: 'All threads in classes you joined'
            },
            tab: {
                1: 'Starred',
                2: 'Classes',
                3: 'Join a new Class',
                4: 'Class ID',
                5: 'Thread',
                6: 'Add a new thread',
                7: 'Thread Name',
                8: 'Users',
                9: 'messages',
                10: 'Add'
            },
            chat: {
                1: 'Messages',
                2: 'Followers',
                3: 'Close',
                4: 'Remove',
                5: 'Pinned Message',
                6: 'Pin',
                7: 'Delete',
                8: 'Edit',
                9: 'Edit Message',
                10: 'Edit Thread',
                11: 'Class Logs',
                12: 'Thread Logs',
                13: 'New Messages',
                14: 'Discard',
                15: 'Upload',
                16: 'Upload an Image',
                17: 'Upload a File',
                18: 'Sending Method'
            },
        }
    }
</script>


<div class="main-container" id="main-container" style="opacity:0">

    <div class="left">
        <a-spin :spinning="spinning.left">
            <div class="main-header">
                <h3>{{ lang.title[1] }}</h3>
                <p>{{ lang.title[2].substr(1,lang.title[2].length) }}</p>
            </div>
            <div class="mes-item" @click="open_marks">
                <p style="color:rgb(255, 193, 37)">
                    <a-icon type="star"></a-icon>&nbsp;&nbsp;{{ lang.tab[1] }}
                </p>
            </div>
            <template v-if="!!user.joined_classes">
                <div class="mes-item">
                    <p style="color:rgb(90, 148, 241)">
                        <a-icon type="team"></a-icon>&nbsp;&nbsp;{{ lang.tab[2] }}
                        <a-button size="small" @click="reverse_order('classes')" style="font-size:14px;right:80px">
                            <a-icon type="sort-descending"></a-icon>
                        </a-button>
                        <a-button size="small" @click="display_class" v-html="display_classes_text"></a-button>
                    </p>
                </div>
                <div class="items-count">
                    <p>- {{ (user.joined_classes).length }} items in total -</p>
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
                <p class="class-item-join-btn">
                    <a-icon type="plus-circle"></a-icon>&nbsp;&nbsp;{{ lang.tab[3] }}
                </p>
            </div>
        </a-spin>
    </div>


    <!-- 加入班级 -->
    <a-modal :title="lang.tab[3]" :visible="add.visible" @ok="handle_join_submit" :confirm-loading="add.confirm_join_loading" @cancel="handle_create_cancel">
        <a-input :placeholder="lang.tab[4]" v-model="add.join.id">
            <a-icon slot="prefix" type="team" />
        </a-input>
    </a-modal>
    <!-- 加入班级结束 -->

    <!-- 增加主题 -->
    <a-modal :title="lang.tab[6]" :visible="add.visible_thread" @ok="handle_thread_submit(opened_class_info.id)" :confirm-loading="add.confirm_thread_loading" @cancel="handle_thread_cancel">
        <a-input :placeholder="lang.tab[7]" v-model="add.thread.name">
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
    <a-modal :title="lang.chat[10]" :visible="edit.visible" @ok="handle_edit_submit" :confirm-loading="edit.confirm_edit_loading" @cancel="handle_edit_cancel">
        <a-input placeholder="Thread Subject" v-model="edit.name">
            <a-icon slot="prefix" type="appstore" />
        </a-input>
    </a-modal>
    <!-- 主题信息修改结束 -->
    <!-- 内容段修改 -->
    <a-modal :title="lang.chat[9]" :visible="edit.mes.visible" @ok="handle_edit_mes_submit" :confirm-loading="edit.mes.confirm_edit_mes_loading" @cancel="handle_edit_mes_cancel">
        <a-input placeholder="Content" v-model="edit.mes.content">
            <a-icon slot="prefix" type="align-center" />
        </a-input>
    </a-modal>
    <!-- 内容段修改结束 -->
    <!-- 日志查看 -->
    <a-modal :title="lang.chat[12]" :visible="log.visible" :footer="null" @cancel="log.visible = false">
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
    <!-- 日志查看结束 -->
    <!-- 日志查看 -->
    <a-modal :title="lang.chat[11]" :visible="log.visible_class" :footer="null" @cancel="log.visible_class = false">
        <template v-if="!!opened_class_info.logs.length">
            <div style="max-height: 60vh;overflow-y: auto;">
                <div v-for="(log,index) in opened_class_info.logs" class="logs-info" v-if="log.divide == null">
                    <div>
                        <h3>{{ log.speaker_name }} <em>{{ get_mes_date(log.date) }}</em></h3>
                    </div>
                    <div>
                        <p>{{ log.content }}</p>
                    </div>
                </div>
                <div :style="index ? 'margin: 40px 0 0 0;' : 'margin: 0;'" v-else>
                    <a-divider>{{ log.divide }}</a-divider>
                </div>
            </div>
        </template>
        <template v-else>
            <p class="mes-end" style="margin-bottom: 0px;margin-top: 0px;">- EOF -</p>
        </template>
    </a-modal>
    <!-- 日志查看结束 -->
    <!-- office 内容预览 -->
    <a-modal :footer="null" :title="office.title" centered v-model="office.visible" @cancel="handle_office_close" width="80%">
        <iframe :src="'https://view.officeapps.live.com/op/embed.aspx?src=' + office.url" width="100%" height="600px" frameborder="0"></iframe>
    </a-modal>
    <!-- office 内容预览结束 -->

    <!-- 星标收藏列 -->
    <div class="center class-center mes-column">
        <a-spin :spinning="spinning.center">
            <template v-if="status.mark">
                <div class="mes-header">
                    <p style="color:#666;">
                        <a-icon type="star"></a-icon>&nbsp;&nbsp;{{ lang.tab[1] }}
                    </p>
                </div>
                <template v-if="Object.keys(opened_mark_info.class_c).length - 1">
                    <p class="mes-sub-header">{{ lang.tab[2] }}</p>
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
                    <div class="items-count">
                        <p>- {{ (opened_mark_info.class_info).length }} items in total -</p>
                    </div>
                </template>
                <template v-if="Object.keys(opened_mark_info.user).length - 1">
                    <p class="mes-sub-header">{{ lang.tab[8] }}</p>
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
                    <div class="items-count">
                        <p>- {{ (opened_mark_info.user_info).length }} items in total -</p>
                    </div>
                </template>
                <template v-if="!Object.keys(opened_mark_info.class_c).length - 1 && !Object.keys(opened_mark_info.user).length - 1">
                    <p class="mes-end">- EOF -</p>
                </template>
            </template>


            <template v-else-if="status.thread">
                <div class="mes-header">
                    <p style="color:#666;">
                        <a-icon type="appstore"></a-icon>&nbsp;&nbsp;{{ lang.tab[5] }}
                        <a-button size="small" @click="view_logs('class')" style="margin-right:10px;font-size:16px;right:112px;position:absolute" v-if="user.info.type == 2">
                            <a-icon type="database"></a-icon>
                        </a-button>
                        <a-button size="small" @click="reverse_order('threads')" style="right:83px;position:absolute">
                            <a-icon type="sort-descending" />
                        </a-button>
                        <a-button size="small" @click="add.visible_thread = true" style="right:20px;position:absolute">+ {{ lang.tab[10] }}</a-button>
                    </p>
                </div>
                <template v-if="opened_thread_info.length">
                    <div class="items-count">
                        <p>- {{ (opened_thread_info).length }} items in total -</p>
                    </div>
                    <div v-for="(thread_c,index) in opened_thread_info" class="class-item" :id="'thread_sub'+thread_c.id" @click="open_mes(index,thread_c.id,thread_c.belong_class)">
                        <div class="thread-notify" v-if="thread_c.id == push.thread"></div>
                        <div>
                            <h3 v-html="thread_c.name"></h3>
                            <p>{{ thread_c.message_count }} {{ lang.tab[9] }}</p>
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
            <img v-if="!!opened_member_info.info.avatar" :src="opened_member_info.info.avatar" class="class-item-img class-member-img profile-blur-bg">
                <div class="class-info-header class-member-header" style="margin-top: -30px;">
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
                            <a-button style="border: none;box-shadow: 0 1px 4px 0 rgba(0,0,0,.1);" type="default" @click="demark_process(opened_member_info.info.id,'user')">
                                <a-icon type="star" style="color:#FFC125"></a-icon>
                            </a-button>
                        </template>
                        <template v-else>
                            <a-button style="border: none;box-shadow: 0 1px 4px 0 rgba(0,0,0,.1);" type="default" @click="mark_process(opened_member_info.info.id,'user')">
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
                        <p>{{ opened_mes_info.thread_info.message_count }} {{ lang.chat[1] }} | {{ opened_mes_info.unique_speakers.length }} {{ lang.chat[2] }}</p>
                    </div>
                    <div>
                        <a-button type="default" @click="reverse_order('meses')" style="margin-right:10px;font-size:16px">
                            <a-icon type="sort-descending"></a-icon>
                        </a-button>
                        <a-button type="default" @click="view_logs('thread')" style="margin-right:10px;font-size:16px" v-if="user.info.type == 2">
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

                <!-- 消息顶置 -->
                <div class="mes-pin" v-if="!!thread_info.pinned.mes">
                    <p>
                        <template v-if="opened_class_info.superid !== user.id">
                            <a @click="close_pin()">
                                <a-icon type="close"></a-icon>
                            </a>
                        </template>
                        <template v-else>
                            <a-dropdown :trigger="['click']">
                                <a class="ant-dropdown-link" href="#">
                                    <a-icon type="close"></a-icon>
                                </a>
                                <a-menu slot="overlay">
                                    <a-menu-item key="0">
                                        <a @click="close_pin()">
                                            <a-icon type="close"></a-icon> {{ lang.chat[3] }}
                                        </a>
                                    </a-menu-item>
                                    <a-menu-item key="0">
                                        <a @click="pin_mes(thread_info.pinned.id,1,'remove')">
                                            <a-icon type="delete"></a-icon> {{ lang.chat[4] }}
                                        </a>
                                    </a-menu-item>
                                </a-menu>
                            </a-dropdown>
                        </template>
                    </p>
                    <p>{{ lang.chat[5] }}</p>
                    <p><a :href="'#'+thread_info.pinned.id" v-html="thread_info.pinned.mes"></a></p>
                </div>
                <!-- 消息顶置结束 -->

                <div :class="mes_input.container" id="mes-container" :style="!!thread_info.pinned.mes ? pin_mes_container() : ''">
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
                            <div v-for="(mes,index) in opened_mes_info.meses" class="mes-stream" @mouseenter="comment_action($event)" @mouseleave="comment_action_leave($event)" :style="same_speaker(mes.speaker,index)" :id="mes.id">
                                <div class="mes-stream-avatar" @click="view_user_info(mes.speaker)">
                                    <template v-if="opened_mes_info.speakers[0][mes.speaker + ''] !== null">
                                        <img :src="opened_mes_info.speakers[0][mes.speaker + '']" class="class-item-img" />
                                    </template>
                                    <template v-else>
                                        <div class="class-img-default">
                                            <p>{{ opened_mes_info.speakers[1][mes.speaker + ''].substring(0,1) }}</p>
                                        </div>
                                    </template>
                                </div>
                                <div class="mes-stream-content">
                                    <h3 v-html="opened_mes_info.speakers[1][mes.speaker + ''] + check_leaved(mes.speaker) + '&nbsp;<em>' + get_mes_date(mes.date) + '</em>'"></h3>
                                    <template v-if="!!mes.content && !mes.img_url && mes.content !== 'null'">
                                        <div class="mes-content" v-html="mes.id == thread_info.pinned.id ? '<em class=\'pin-tag\'>Pinned</em>' + process_content(mes.content) : process_content(mes.content)"></div>
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
                                                    <p><a style="text-decoration:none;color:#888">{{ mes.file_size }}</a>
                                                        <a-divider type="vertical"></a-divider><a :href="'../extension/download.php?filename='+mes.file_url" target="_blank">Download</a>
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
                                            <a-tag color="pink" v-if="mes.emoji_1" @click="remove_emoji(1,mes.id,index)">
                                                <a-icon type="smile"></a-icon> x{{ parseInt(mes.emoji_1) }}
                                            </a-tag>
                                            <a-tag color="orange" v-if="mes.emoji_2" @click="remove_emoji(2,mes.id,index)">
                                                <a-icon type="meh"></a-icon> x{{ parseInt(mes.emoji_2) }}
                                            </a-tag>
                                            <a-tag color="blue" v-if="mes.emoji_3" @click="remove_emoji(3,mes.id,index)">
                                                <a-icon type="frown"></a-icon> x{{ parseInt(mes.emoji_3) }}
                                            </a-tag>
                                        </div>
                                    </template>
                                </div>
                                <div class="mes-stream-emoji">
                                    <a class="a-1">
                                        <a-icon type="smile" @click="add_emoji(1,mes.id,index)"></a-icon>
                                    </a>
                                    <a class="a-2">
                                        <a-icon type="meh" @click="add_emoji(2,mes.id,index)"></a-icon>
                                    </a>
                                    <a class="a-3">
                                        <a-icon type="frown" @click="add_emoji(3,mes.id,index)"></a-icon>
                                    </a>
                                    <template v-if="mes.speaker == user.id || opened_class_info.superid == user.id">
                                        <a-dropdown :trigger="['click']">
                                            <a class="ant-dropdown-link" href="#">
                                                <a-icon type="ellipsis"></a-icon>
                                            </a>
                                            <a-menu slot="overlay">
                                                <template v-if="opened_class_info.superid == user.id">
                                                    <a-menu-item key="0">
                                                        <a @click="pin_mes(mes.id,index,'add')">
                                                            <a-icon type="pushpin"></a-icon>{{ lang.chat[6] }}
                                                        </a>
                                                    </a-menu-item>
                                                </template>
                                                <a-menu-item key="1">
                                                    <a class="a-d" @click="remove_mes(mes.id,index)">
                                                        <a-icon type="delete"></a-icon> {{ lang.chat[7] }}
                                                    </a>
                                                </a-menu-item>
                                                <template v-if="mes.type !== 'file'">
                                                    <a-menu-item key="2">
                                                        <a class="a-e" @click="open_mes_edit(mes.id,mes.content)">
                                                            <a-icon type="edit"></a-icon> {{ lang.chat[8] }}
                                                        </a>
                                                    </a-menu-item>
                                                </template>
                                            </a-menu>
                                        </a-dropdown>
                                    </template>
                                </div>
                            </div>
                            <p class="mes-end">- EOF -</p>
                        </template>
                    </div>
                </div>
                <div class="mes-unread-notify" v-show="unread.visible" @click="bottom_mes()">{{ lang.chat[13] }}</div>
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
                                        <a-button @click="mes_input.visible.picture = false;">{{ lang.chat[14] }}</a-button>
                                        <a-button type="primary" @click="upload_img">{{ lang.chat[15] }}</a-button>
                                    </template>
                                </template>
                                <a-tooltip placement="top">
                                    <template slot="title">
                                        <span>{{ lang.chat[16] }}</span>
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
                                        <a-button @click="handle_cancel_upload();mes_input.visible.upload = false;">{{ lang.chat[14] }}</a-button>
                                        <a-button type="primary" @click="upload_file">{{ lang.chat[15] }}</a-button>
                                    </template>
                                </template>
                                <a-tooltip placement="top">
                                    <template slot="title">
                                        <span>{{ lang.chat[17] }}</span>
                                    </template>
                                    <a-button>
                                        <a-icon type="cloud-upload"></a-icon>
                                    </a-button>
                                </a-tooltip>
                            </a-popover>

                        </div>
                        <div v-show="mes_input.visible.text">
                            <a-button @click="handle_input_down" style="margin-right:10px">Discard</a-button>
                            <a-dropdown-button type="primary" @click="handle_input_send(mes_input.type)" :trigger="['click']">
                                {{ mes_input.send_text }}
                                <a-menu slot="overlay">
                                    <a-menu-item key="1" @click="enter_send('click')" style="text-align:right"><a-icon type="check" v-if="enter.text == 1"></a-icon>Click Button to Send</a-menu-item>
                                    <a-menu-item key="2" @click="enter_send('enter')" style="text-align:right"><a-icon type="check" v-if="enter.text == 2"></a-icon>Press Enter to Send</a-menu-item>
                                </a-menu>
                            </a-dropdown-button>
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
<script type="text/javascript" src="../dist/js/messages.js"></script>

<?php require 'pro_footer.php'; ?>