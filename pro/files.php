<?php require 'pro_header.php'; ?>




<div class="main-container" id="main-container" style="opacity:0">

    <div class="left">
        <a-spin :spinning="spinning.left">
            <div class="main-header">
                <h3>Files</h3>
                <p>All files in classes you joined</p>
            </div>
            <template v-if="!!user.joined_classes">
                <div v-for="(joined,index) in user.joined_classes" :class="'class-item ' + class_super(index)" @click="open_class(user.classes_info[index].id,index)" :id="'class_left'+user.classes_info[index].id">
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
            <template v-else>
                <div class="class-item">
                    <p>
                        No joined class yet
                    </p>
                </div>
            </template>
        </a-spin>
    </div>

    <div class="center class-center mes-column">
        <a-spin :spinning="spinning.center">
            <template v-if="status.thread">
                <div class="mes-header">
                    <p style="color:#666;">
                        <a-icon type="folder"></a-icon>&nbsp;&nbsp;Thread
                    </p>
                </div>
                <div v-for="(thread_c,index) in opened_thread_info" class="class-item files-folder" :id="'thread_sub'+thread_c.id" @click="open_mes(index,thread_c.id,thread_c.belong_class)">
                    <div>
                        <h3 v-html="thread_c.name"></h3>
                        <p>Created on {{ get_mes_date(thread_c.date) }}</p>
                    </div>
                </div>
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

        <!-- 文件名修改 -->
        <a-modal title="Edit FileName" :visible="edit.file.visible" @ok="handle_edit_file_submit" :confirm-loading="edit.file.confirm_edit_file_loading" @cancel="handle_edit_file_cancel">
        <a-input defaultValue="edit.file.content" v-model="edit.file.content" addonAfter="edit.file.type">
            <a-icon slot="prefix" type="align-center" />
        </a-input>
    </a-modal>
    <!-- 文件名修改结束 -->

    <div class="right">
        <a-spin :spinning="spinning.right">
            <!-- 消息框开始 -->
            <template v-if="status.files">
                <div class="mes-header">
                    <p style="color:#333;font-weight:600">
                        <a-icon type="folder-open"></a-icon>&nbsp;&nbsp;{{ opened_mes_info.thread_info.name }}
                    </p>
                </div>
                <div class="mes_container">
                    <div id="mes-inner" style="padding-top:5px">
                        <template v-if="spinning.loading">
                            <div style="padding:5px 30px">
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                                <a-skeleton avatar :paragraph="{rows: 1}" active></a-skeleton>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="(file,index) in opened_mes_info.files" class="mes-stream-file-div">
                                <div class="mes-stream-file-div-sub">
                                    <div :style="'font-size: 40px;margin-top: -6px;color:'+ get_file_icon(get_suffix(file.file_name).substr(1))[1]">
                                        <a-icon :type="'file-' + get_file_icon(get_suffix(file.file_name).substr(1))[0]"></a-icon>
                                    </div>
                                    <div style="margin-left: 10px;">
                                        <h3>{{ file.file_name }}</h3>
                                        <p style="margin: 0px;">
                                            <a :href="'../extension/download.php?filename='+file.file_url" target="_blank">Download</a>
                                            <template v-if="get_suffix(file.file_name).substr(1) == 'pdf'">
                                            <a-divider type="vertical"></a-divider><a :href="file.file_url" target="_blank">Preview</a>
                                            </template>
                                            <template v-else-if="if_office(get_suffix(file.file_name).substr(1))">
                                            <a-divider type="vertical"></a-divider><a @click="open_office_preview(file.file_url,file.file_name)">Preview</a>
                                            </template>
                                            <template v-if="file.speaker == user.id || opened_class_info.superid == user.id">
                                            <a-divider type="vertical"></a-divider><a style="color: rgb(233, 30, 99);" @click="remove_file(file.id)">Delete</a><a-divider type="vertical"></a-divider><a style="color:#333" @click="open_file_edit(file.id,file.file_name)">Edit</a>
                                            </template>
                                        </p>
                                    </div>
                                </div>

                                <div class="mes-stream-file-profile" style="margin:0px">
                                    <div style="margin-right: 10px;">
                                        <template v-if="opened_mes_info.speakers[0][file.speaker.toString()] !== null">
                                            <img :src="opened_mes_info.speakers[0][file.speaker.toString()]" class="class-item-img" />
                                        </template>
                                        <template v-else>
                                            <div class="class-img-default">
                                                <p>{{ opened_mes_info.speakers[1][file.speaker.toString()].substring(0,1) }}</p>
                                            </div>
                                        </template>
                                    </div>
                                    <div>
                                        <h3 v-html="opened_mes_info.speakers[1][file.speaker.toString()]"></h3>
                                        <p style="color:#999;white-space: nowrap;" v-html="get_mes_date(file.date)"></p>
                                    </div>
                                </div>


                            </div>
                            <p class="mes-end">- EOF -</p>
                        </template>
                    </div>
                </div>
            </template>
            <!-- 消息框结束 -->




        </a-spin>
        <!-- 占位 -->
        <template v-if="!spinning.right && !status.files">
            <div style="padding:20px 30px">
                <a-skeleton avatar :paragraph="{rows: 1}" v-for="i in 9"></a-skeleton>
            </div>
        </template>
        <!-- 占位 -->
    </div>



</div>


<!-- office 内容预览 -->
<a-modal :footer="null" :title="office.title" centered v-model="office.visible" @cancel="handle_office_close" width="80%">
    <iframe :src="'https://view.officeapps.live.com/op/embed.aspx?src=' + office.url" width="100%" height="600px" frameborder="0"></iframe>
</a-modal>
<!-- office 内容预览结束 -->


</div>
<script type="text/javascript" src="../main/files.js"></script>


<?php require 'pro_footer.php'; ?>