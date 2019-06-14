<?php require 'pro_header.php'; ?>
<div>
    <a-menu mode="horizontal">
        <a-menu-item>
            <a-icon type="project"></a-icon>Messages
        </a-menu-item>
        <a-menu-item>
            <a-icon type="save"></a-icon>Files
        </a-menu-item>
        <a-menu-item>
            <a-icon type="bank"></a-icon>Groups
        </a-menu-item>
        <a-menu-item style="float:right">
            <template v-if="user.status">
                <div style="display: flex">
                    <div>
                        <span style="letter-spacing: 1px;padding-top: 5px" v-html="user.name"></span>
                    </div>
                    <div>
                        <img style="width:26px;height:26px;border-radius: 50%;margin-left: 10px;margin-top: -4px" src="https://static.ouorz.com/tonyhe.jpg">
                    </div>
                </div>
            </template>

            <template v-else>
                <a-icon type="bulb"></a-icon>Get Started
            </template>
        </a-menu-item>
    </a-menu>
    </a-menu-item>
</div>
</body>
<script type="text/javascript" src="../main/messages.js"></script>

<?php require 'pro_footer.php'; ?>