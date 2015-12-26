<?php  
/**
 * @package Push Baidu Link(百度链接推送)
 * @version 1.0
 */
/*
Plugin Name: 百度链接推送
Plugin URI: https://github.com/moai2010/push-baidu-link
Description: 当有新文章添加时，向百度主动实时的推动新文章连接，有利于百度收录
Author: moai
Version: 1.0
Author URI: http://www.wz-gzh.com
*/
/* 注册激活插件时要调用的函数 */ 
register_activation_hook( __FILE__, 'push_baidulink_install');   

/* 注册停用插件时要调用的函数 */ 
register_deactivation_hook( __FILE__, 'push_baidulink_remove' ); 

/* 注册激活插件进行部分处理*/
function push_baidulink_install(){
	/* 在数据库的 wp_options 表中添加一条记录，第二个参数为默认值 */ 
	add_option("push_baidulink_url", "http://data.zz.baidu.com/urls?site=XXXXXXXX&token=XXXXXXXX&type=original", '', 'yes'); 
	add_option("push_baidulink_state", "未知", '', 'yes');  
}
/* 注册停用插件进行部分处理*/
function push_baidulink_remove(){
	/* 删除 wp_options 表中的对应记录 */ 
    delete_option('push_baidulink_url');  
	delete_option('push_baidulink_state');  
}



class PushBaiduLink{
	
	public function __Construct(){
        //add_filter('the_content', array($this,'add_pay'));
        add_action('admin_menu', array($this,'add_menu_page'));
		add_action('publish_post', array($this,'push_bd_link'));
        //add_filter('plugin_action_links', array($this,'wechat_reward_plugin_setting'), 10, 2);
    }
	
	function push_bd_link(){
		$url = get_permalink($post_ID);
		$urls = array($url);
		$api = get_option('push_baidulink_url');
		$ch = curl_init();
		$options =  array(
			CURLOPT_URL => $api,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => implode("\n", $urls),
			CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
		);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		
		//更新提交状态	
		update_option('push_baidulink_state', empty($result["success"]) ? "失败" : "成功");
			
	}
	
	
	//百度链接推送设置菜单
    function add_menu_page() {
        add_options_page( '百度链接推送', '百度链接推送', 'manage_options', 'push_baidulink_page', array($this,'push_baidulink_page'));
    }
	
	
	
	function push_baidulink_page(){
		if(isset($_POST['submit']) && $_SERVER['REQUEST_METHOD']=='POST'){
            update_option('push_baidulink_url',$_POST['push_baidulink_url'] ? $_POST['push_baidulink_url'] : '');
            $this->update_success();
        }
		
?>		
	 <div>
        <h2>设置百度链接推送</h2>
        <p>
            使用百度站长获得接口调用地址，操作步骤：
        </p>
        <p>
            1.打开百度站长,进行注册<br>
            2.填写和提交站点信息<br>
            3.进入网页抓取->链接提交<br>
            4.下拉网页获得"接口调用地址"<br>
            提示：有任何疑问请发邮件到zhen.tengjun@qq.com，将第一时间回复，谢谢！
        </p>
		 <p>
            暂时推送状态：<?php echo get_option('push_baidulink_state'); ?>
        </p>
		
        <form action="<?php echo admin_url( 'options-general.php?page=push_baidulink_page');?>" method="post">
		
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label >接口调用地址</label></th>
                    <td><input type="text"  value="<?php echo get_option('push_baidulink_url'); ?>" id="push_baidulink_url" name="push_baidulink_url"></td>
                </tr>
                </tbody>
            </table>
            <p><input type="submit" value="保存更改"  name="submit" id="submit" ></p>
        </form>
        <hr>
    </div>	
		
<?php		
	}
	
	 //保存成功提示
    public function update_success()
    {
        echo '<div class="updated "><p>更新成功!写篇文章试试吧！</p></div>';
    }
	
}

new PushBaiduLink();


?>