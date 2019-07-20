<?php require 'pro_header.php'; ?>




<div class="main-container" id="main-container" style="opacity:0">

    <div class="left">
        <a-spin :spinning="spinning.left">
            <div class="main-header">
                <h3>Grades</h3>
                <p>Manage students' grades</p>
            </div>
            <template v-if="Object.keys(user.joined_classes).length">
                <div class="mes-item">
                    <p>
                        <a-icon type="team"></a-icon>&nbsp;&nbsp;Classes
                        <a-button size="small" @click="reverse_order('classes')" style="font-size:14px;">
                            <a-icon type="sort-descending" />
                        </a-button>
                    </p>
                </div>
                <div v-for="(joined,index) in user.joined_classes" :class="'class-item ' + class_super(index)" @click="open_class_info(index)" :id="'class'+index">
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
            </template>
        </a-spin>
    </div>

    <!-- 增加系列 -->
    <a-modal title="Add a new series" :visible="add.visible.series" @ok="handle_series_submit(opened_class_info.id)" :confirm-loading="add.confirm.series" @cancel="handle_series_cancel()">
        <a-input placeholder="Series Name" v-model="add.info.series.name">
            <a-icon slot="prefix" type="bar-chart" />
        </a-input>
    </a-modal>
    <!-- 增加系列结束 -->
    <!-- 增加主题 -->
    <a-modal title="Add a new topic" :visible="add.visible.topic" @ok="handle_topic_submit()" :confirm-loading="add.confirm.topic" @cancel="handle_topic_cancel()">
        <a-input placeholder="Topic Name" v-model="add.info.topic.name">
            <a-icon slot="prefix" type="bar-chart" />
        </a-input>
    </a-modal>
    <!-- 增加主题结束 -->

    <div class="center">
        <a-spin :spinning="spinning.center">
            <template v-if="opened_series_info.status">
                <div :style="opened_series_info.info.length ? 'background: #f8f9fa;' : ''">
                    <div class="mes-header">
                        <p style="color:#666;">
                            <a-icon type="bar-chart"></a-icon>&nbsp;&nbsp;Series
                            <a-button size="small" style="right:83px;position:absolute">
                                <a-icon type="sort-descending" />
                            </a-button>
                            <a-button size="small" @click="add.visible.series = true" style="right:20px;position:absolute">+ Add</a-button>
                        </p>
                    </div>

                    <template v-if="opened_series_info.info.length">

                        <template v-for="(series_c,index) in opened_series_info.info">
                            <div class="class-item series-item" :id="'series_sub'+series_c.id">
                                <h3>
                                    <a-icon type="fork"></a-icon>&nbsp;&nbsp;{{ series_c.name }}
                                </h3>
                                <a-button size="small" type="primary" @click="open_topic_submit(series_c.id,series_c.name)" style="right:20px;position:absolute;margin-top:1px">+ New</a-button>
                            </div>

                            <template v-if="series_c.topics_info.length">
                                <div v-for="(topic_c,index) in series_c.topics_info" class="class-item topic-item" :id="'topic_sub'+topic_c.id" @click="open_topic_info(topic_c.id)">
                                    <h3>
                                        <a-icon type="branches"></a-icon>&nbsp;&nbsp;{{ topic_c.name }}
                                    </h3>
                                </div>
                            </template>
                            <template v-else>
                                <div class="class-item topic-item">
                                    <h3>
                                        <a-icon type="branches"></a-icon>&nbsp;&nbsp;No Topics Yet
                                    </h3>
                                </div>
                            </template>

                        </template>

                    </template>
                    <template v-else>
                        <p class="mes-end">- EOF -</p>
                    </template>
                </div>
            </template>
        </a-spin>
        <!-- 占位 -->
        <template v-if="!spinning.center && !opened_series_info.status">
            <div style="margin-top:-10px">
                <a-skeleton :paragraph="{rows: 2}" v-for="i in 6"></a-skeleton>
            </div>
        </template>
        <!-- 占位 -->
    </div>

    <div class="right">
        <a-spin :spinning="spinning.right">
            <template v-if="opened_topic_info.status">

                <div class="topic-header">
                    <div :style="opened_topic_info.section ? 'background:#f7f8f9' : 'background:#fff'" @click="opened_topic_info.section = !opened_topic_info.section">Table</div>
                    <div :style="opened_topic_info.section ? 'background:#fff' : 'background:#f7f8f9'" @click="opened_topic_info.section = !opened_topic_info.section">Chart</div>
                </div>

                <div class="topic-container">

                    <template v-if="opened_topic_info.section">

                        <div class="table">
                        <div class="table-header">
                                    <div>Index</div>
                                    <div>Date</div>
                                    <div>Name</div>
                                    <div @click="sortBy('score')">Score&nbsp;&nbsp;<a-icon type="sort-ascending" v-if="sort"></a-icon><a-icon type="sort-descending" v-else></a-icon></div>
                                    <div>Total</div>
                                    <div>Level</div>
                                </div>
                            <template v-for="(data,index) in cdata">
                                <div class="table-item">
                                    <div>{{ index }}</div>
                                    <div>{{ data.date }}</div>
                                    <div>{{ data.name }}</div>
                                    <div>{{ data.score }}</div>
                                    <div>{{ data.total }}</div>
                                    <div>{{ data.level }}</div>
                                </div>
                            </template>
                        </div>

                    </template>

                    <template v-else>
                        <ve-histogram :data="chartData"></ve-histogram>
                    </template>

                </div>

            </template>
        </a-spin>
        <!-- 占位 -->
        <template v-if="!spinning.right && !opened_topic_info.status">
            <div style="padding:20px 30px">
                <a-skeleton avatar :paragraph="{rows: 1}" v-for="i in 9"></a-skeleton>
            </div>
        </template>
        <!-- 占位 -->
    </div>



</div>







</div>
<link type="text/css" rel="stylesheet" href="../statics/css/chart.min.css">
<script type="text/javascript" src="../statics/js/chart.min.js"></script>
<script type="text/javascript" src="../statics/js/chart.index.min.js"></script>
<script type="text/javascript" src="../src/grades.js"></script>


<?php require 'pro_footer.php'; ?>