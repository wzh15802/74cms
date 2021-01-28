<?php
namespace app\index\controller;

\think\Loader::import('wechat.wxBizMsgCrypt');
class Weixin extends \app\common\controller\Base
{
    protected $timestamp;
    protected $nonce;
    protected $msg_signature;
    protected $encrypt_type;
    protected $pc;
    public function _initialize()
    {
        parent::_initialize();
        $this->msg_signature = input('param.msg_signature', '', 'trim');
        $this->timestamp = input('param.timestamp', '', 'trim');
        $this->nonce = input('param.nonce', '', 'trim');
        $this->encrypt_type = 'aes' == input('param.encrypt_type', '', 'trim') ? 'aes' : 'raw';
    }
    public function index()
    {
        $echoStr = input('param.echostr', '', 'trim');
        if($echoStr){
            $this->validConnectIn($echoStr);
        }else{
            $this->responseMsg();
        }
    }
    /**
     * 微信接入认证
     */
    protected function validConnectIn($echoStr) {
        if ($this->checkSignature()){
            exit($echoStr);
        }
        exit('false');
    }
    /**
     * 验名认证
     */
    protected function checkSignature() {
        $signature = input('param.signature', '', 'trim');
        $timestamp = input('param.timestamp', '', 'trim');
        $nonce = input('param.nonce', '', 'trim');
        $tmpArr = array(config('global_config.wechat_token'), $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    //检查网站微信接口是否开启
    protected function checkWeixinOpen($object) {
        if (!config('global_config.wechat_open')) {
            $this->outputText($object,"网站微信接口已经关闭");
            exit;
        }
    }
    /**
     * 输出前加密
     */
    protected function outputByEncryptMsg($content){
        $exitContent = $content;
        //加密
        if ($this->encrypt_type == 'aes') {
            $encryptMsg = ''; //加密后的密文
            $errCode = $this->pc->encryptMsg($content, $this->timestamp, $this->nonce, $encryptMsg);
            if ($errCode != 0) {
                $exitContent = '';
            }else{
                $exitContent = $encryptMsg;
            }
        }
        echo $exitContent;
    }
    /**
     * 消息类
     */
    protected function responseMsg() {
        if (!$this->checkSignature()) exit('false');
        if ($postStr = file_get_contents("php://input")) {
            //解密
            if ($this->encrypt_type == 'aes') {
                $this->pc = new \WXBizMsgCrypt(config('global_config.wechat_token'), config('global_config.wechat_encodingaeskey'), config('global_config.wechat_appid'));
                $decryptMsg = "";//解密后的明文
                $errCode = $this->pc->decryptMsg($this->msg_signature, $this->timestamp, $this->nonce, $postStr, $decryptMsg);
                if ($errCode != 0) {
                    echo "";
                    exit;
                }
                $postStr = $decryptMsg;
            }
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $rxType = trim($postObj->MsgType);
        //消息类型分离
            switch ($rxType) {
                case "event":
                    $this->receiveEvent($postObj);
                    break;
                case "text":
                    $this->receiveText($postObj);
                    break;
                case "voice":
                    $this->receiveVoice($postObj);
                    break;
                default:
                    $this->outputText($postObj,"unknown msg type: " . $rxType);
                    break;
            }
        } else {
            echo "";
            exit;
        }
    }
    /**
     * 关注公众号
     */
    protected function subscribe($object){
        $data['openid'] = addslashes($object->FromUserName);
        model('WechatFans')->save($data);
        $checkBindInfo = model('MemberBind')
                        ->where('is_subscribe',0)
                        ->where('openid',$data['openid'])
                        ->where('type','weixin')
                        ->find();
        if($checkBindInfo!==null){
            $checkBindInfo->is_subscribe = 1;
            $checkBindInfo->save();
            $member = model('Member')->where('uid',$checkBindInfo['uid'])->find();
            if($member!==null){
                model('Task')->doTask($member['uid'], $member['utype'], 'bind_weixin');
            }
        }
    }
    /**
     * 取消关注公众号
     */
    protected function unsubscribe($object){
        $openid = addslashes($object->FromUserName);
        model('WechatFans')->where('openid',$openid)->delete();
        $checkBindInfo = model('MemberBind')
                        ->where('is_subscribe',1)
                        ->where('openid',$openid)
                        ->where('type','weixin')
                        ->find();
        if($checkBindInfo!==null){
            $checkBindInfo->is_subscribe = 0;
            $checkBindInfo->save();
        }
    }
    /**
     * 接收事件消息
     */
    protected function receiveEvent($object) 
    { 
		$this->checkWeixinOpen($object);
        switch ($object->Event) {
            case "subscribe"://用户未关注时，进行关注后的事件推送
                $this->outputWelcome($object);
                $this->subscribe($object);
                if ($object->EventKey) {
                    $this->actionScan($object);
                }
                break;
            case "SCAN":
                $this->actionScan($object);
                break;
            case "CLICK":
                switch ($object->EventKey) {
                    case "binding"://绑定
                        $this->clickBinding($object);
                        break;
                    case "contact"://联系客服
                        $this->clickContact($object);
                        break;
                    default:
                        break;
                }
                break;
            case "unsubscribe":
                $this->unsubscribe($object);
                break;
            default:
                $this->subscribe($object);
                // $this->outputWelcome($object);
                break;
        }
        exit;
    }
    /**
     * 接收文本消息
     */
    protected function receiveText($object)
    {
        $keyword = trim($object->Content);
        $keyword = addslashes($keyword);
		$this->checkWeixinOpen($object);
        //自动回复模式
        $this->enterSearch($object, $keyword);
    }
    /**
     * 接收语音消息
     */
    protected function receiveVoice($object) {
        $Recognition = trim($object->Recognition);
        $keyword = rtrim($object->Recognition,'。');
		$this->checkWeixinOpen($object);
        //自动回复模式
        $this->enterSearch($object, $keyword);
    }
    protected function outputKeyword($object,$keywordInfo){
        if($keywordInfo['return_text']!=''){
            $this->outputMessage($object,$keywordInfo['return_text'],'text');
        }
        if($keywordInfo['return_img_mediaid']!=''){
            $this->outputMessage($object,$keywordInfo['return_img_mediaid'],'image');
        }
        if($keywordInfo['return_link']!=''){
            $content = "<a href='".$keywordInfo['return_link']."'>".$keywordInfo['return_link']."</a>";
            $this->outputMessage($object,$content,'text');
        }
    }
    /**
     * 根据关键字返回数据
     */
    protected function enterSearch($object, $keyword) {
        if ($keyword == "绑定") {
            $this->clickBinding($object);
        }else if ($keyword == "解绑") {
            $usinfo = $this->getUserInfo($object->FromUserName);
            if ($usinfo) {
                $this->actionUnbind($object);
            } else {
                $this->outputText($object,'您还没有绑定帐号！');
                exit;
            }
        } else {
            //检查关键词表
            $check_keyword = model('WechatKeyword')->where('word',$keyword)->find();
            if($check_keyword!==null){
                $this->outputKeyword($object,$check_keyword);
                exit;
            }else{
                $params = [
                    'count_total'=>0,
                    'current_page'=>1,
                    'pagesize'=>1,
                    'keyword'=>$keyword
                ];
                $instance = new \app\common\lib\JobSearchEngine($params);
                $searchResult = $instance->run();
                if (count($searchResult['items'])>0) {
                    $wechat_info_img = model('Uploadfile')->getFileUrl(config('global_config.wechat_info_img'));
                    $wechat_info_img = $wechat_info_img?$wechat_info_img:make_file_url('resource/wechat_info_img.jpg');
                    $link_url = config('global_config.mobile_domain').'joblist?keyword='.$keyword;
                    $content_arr = [
                        'Title'=>'符合“' . $keyword . '”的职位',
                        'Description'=>'点击查看符合条件的最新信息',
                        'PicUrl'=>$wechat_info_img,
                        'Url'=>$link_url
                    ];
                    $this->outputArticle($object,$content_arr);
                    exit;
                }else{
                    $this->outputText($object,"没有找到包含关键字 " . $keyword . " 的信息，试试其他关键字");
                    exit;
                }
            }
        }
    }
    /**
     * 读取用户绑定信息
     */
    protected function getUserInfo($fromUsername) {
        $fromUsername = addslashes($fromUsername);
        $userBindInfo = model('MemberBind')->where('type','weixin')->where('openid',$fromUsername)->where('is_subscribe',1)->find();
        if($userBindInfo===null){
            return null;
        }
        $memberinfo = model('Member')->where('uid',$userBindInfo['uid'])->find();
        return $memberinfo;
    }
    /**
     * 闸机开门
     */
    public function openGate($event_key, $object) {
        if (stripos($event_key, 'gate_') !== false) {
            $usinfo = $this->getUserInfo($object->FromUserName);
            $event_key_arr = explode("_", $event_key);
            if ($event_key_arr[1] == 'in') {
                $status = 'opendoorin1';
            } else {
                $status = 'opendoorout1';
            }
            $url = "http://gate.74cms.com/Pad/Index/index";
            $data['userinfo'] = json_encode($usinfo);
            $data['status'] = $status;
            $data['tag'] = $event_key_arr[2];
            $data['secret'] = $event_key_arr[3];
            https_request($url, $data);
        }
    }
    /**
     * 扫描事件
     */
    protected function actionScan($object) {
        //用户未关注时，关注后的推送数据包中，EventKey含有“qrscene_”前缀
        $event_key = stripos($object->EventKey, 'qrscene_') === false ? $object->EventKey : ltrim($object->EventKey, 'qrscene_');
        $this->openGate($event_key, $object);
        parse_str($event_key,$event);
        switch ($event['alias']) {
            case 'mapQrcode':
                $jobid = intval($event['jobid']);
                if($jobid==0){
                    $this->outputText($object,'职位不存在或已删除');
                    break;
                }
                $jobinfo = model('Job')->alias('a')
                        ->join(config('database.prefix').'company b','a.uid=b.uid','LEFT')
                        ->where('b.id','NOT NULL')
                        ->where('a.id',$jobid)
                        ->field('a.jobname,a.map_lat,a.map_lng,a.address,b.companyname')
                        ->find();
                if($jobinfo===null){
                    $this->outputText($object,'职位不存在或已删除');
                    break;
                }
                $content = $jobinfo['companyname'].'高薪诚聘'.$jobinfo['jobname']."<a href='" . config('global_config.mobile_domain').'job/'.$jobid . "'>点击详情</a>";
                $this->outputMessage($object,$content,'text');
                $url ='http://api.map.baidu.com/marker?location='.$jobinfo['map_lat'].','.$jobinfo['map_lng'].'&title='.$jobinfo['companyname'].'&content='.$jobinfo['address'].'&output=html';
                $wechat_info_img = model('Uploadfile')->getFileUrl(config('global_config.wechat_info_img'));
                $wechat_info_img = $wechat_info_img?$wechat_info_img:make_file_url('resource/wechat_info_img.jpg');
                $content_arr = [
                    'Title'=>$jobinfo['companyname'].'高薪诚聘'.$jobinfo['jobname'],
                    'Description'=>'点击查看详细地图',
                    'PicUrl'=>$wechat_info_img,
                    'Url'=>$url
                ];
                $this->outputArticle($object,$content_arr);    
                break;
            case 'subscribe_job':
                $jobid = intval($event['jobid']);
                if($jobid==0){
                    $this->outputText($object,'职位不存在或已删除');
                    break;
                }
                $jobinfo = model('Job')->alias('a')
                        ->join(config('database.prefix').'company b','a.uid=b.uid','LEFT')
                        ->where('b.id','NOT NULL')
                        ->where('a.id',$jobid)
                        ->field('a.id,a.jobname,a.amount,a.minwage,a.maxwage,a.negotiable,a.address,a.content,b.companyname,b.logo')
                        ->find();
                if($jobinfo===null){
                    $this->outputText($object,'职位不存在或已删除');
                    break;
                }
                $jobinfo['wage_text'] = model('BaseModel')->handle_wage(
                    $jobinfo['minwage'],
                    $jobinfo['maxwage'],
                    $jobinfo['negotiable']
                );
                $jobinfo['amount_text'] = $jobinfo['amount'] == 0 ? '若干' : $jobinfo['amount'];
                $jobinfo['logo_src'] = $jobinfo['logo'] > 0 ? model('Uploadfile')->getFileUrl($jobinfo['logo']) : default_empty('logo');
                $content_arr = [
                    "Title" => '【'.$jobinfo['companyname'].'】招聘 '.$jobinfo['jobname'].' 职位 '.$jobinfo['amount_text'].' 人，待遇 '.$jobinfo['wage_text'].',工作地点：'.$jobinfo['address'], 
                    "Description" => $jobinfo['content'], 
                    "PicUrl" => $jobinfo['logo_src'], 
                    "Url" => config('global_config.mobile_domain').'job/'.$jobinfo['id']
                ];
                $this->outputArticle($object,$content_arr);
                break;
            case 'subscribe_resume':
                $resumeid = intval($event['resumeid']);
                if($resumeid==0){
                    $this->outputText($object,'简历不存在或已删除');
                    break;
                }
                $resumeinfo = model('Resume')->where('id',$resumeid)->find();
                if($resumeinfo===null){
                    $this->outputText($object,'简历不存在或已删除');
                    break;
                }
                $category_data = model('Category')->getCache();
                $category_job_data = model('CategoryJob')->getCache();
                $resumeinfo['fullname'] = $resumeinfo['fullname'];
                if ($resumeinfo['display_name'] == 0) {
                    if ($resumeinfo['sex'] == 1) {
                        $resumeinfo['fullname'] = cut_str(
                            $resumeinfo['fullname'],
                            1,
                            0,
                            '先生'
                        );
                    } elseif ($resumeinfo['sex'] == 2) {
                        $resumeinfo['fullname'] = cut_str(
                            $resumeinfo['fullname'],
                            1,
                            0,
                            '女士'
                        );
                    } else {
                        $resumeinfo['fullname'] = cut_str(
                            $resumeinfo['fullname'],
                            1,
                            0,
                            '**'
                        );
                    }
                }
                $resumeinfo['sex_text'] = model('Resume')->map_sex[$resumeinfo['sex']];
                $resumeinfo['age'] = date('Y') - intval($resumeinfo['birthday']);
                $resumeinfo['education_text'] = isset(
                    model('BaseModel')->map_education[$resumeinfo['education']]
                )
                    ? model('BaseModel')->map_education[$resumeinfo['education']]
                    : '';
                $resumeinfo['experience_text'] =
                    $resumeinfo['enter_job_time'] == 0
                        ? '无经验'
                        : format_date($resumeinfo['enter_job_time']) . '经验';
                
                $resumeinfo['current_text'] = isset(
                    $category_data['QS_current'][$resumeinfo['current']]
                )
                    ? $category_data['QS_current'][$resumeinfo['current']]
                    : '';
                $resumeinfo['photo_img_src'] = $resumeinfo['photo_img'] > 0 ? model('Uploadfile')->getFileUrl($resumeinfo['photo_img']) : default_empty('photo');
                //求职意向
                $intention_data = model('ResumeIntention')
                    ->field('id,rid,uid', true)
                    ->where(['rid' => ['eq', $resumeinfo['id']]])
                    ->select();
                $intention_jobs_text = [];
                foreach ($intention_data as $key => $value) {
                    $category_text = isset($category_job_data[$value['category']]) ? $category_job_data[$value['category']] : '';
                    $intention_jobs_text[] = $category_text;
                }
                if(!empty($intention_jobs_text)){
                    $intention_jobs_text = array_unique($intention_jobs_text);
                    $resumeinfo['intention_jobs_text'] = implode(",",$intention_jobs_text);
                }else{
                    $resumeinfo['intention_jobs_text'] = '';
                }
                
                $content_arr = [
                    "Title" => '【'.$resumeinfo['fullname'].'】在找'.$resumeinfo['intention_jobs_text'].'相关的工作-'.config('global_config.sitename'), 
                    "Description" => $resumeinfo['sex_text'].'|'.$resumeinfo['age'].'岁|'.$resumeinfo['education_text'].'|'.$resumeinfo['experience_text'].','.$resumeinfo['current_text'], 
                    "PicUrl" => $resumeinfo['photo_img_src'], 
                    "Url" => config('global_config.mobile_domain').'resume/'.$resumeinfo['id']
                ];
                $this->outputArticle($object,$content_arr);
                break;
            case 'subscribe_company':
                if($company_id = $event['comid']) $company = model('Company')->find($company_id);
                if (isset($company)) {
                    $companyInfo = model('CompanyInfo')->where('comid',$company_id)->find();
                    $companycontents=$companyInfo['content'];
                    $logo = $company['logo'] > 0 ? model('Uploadfile')->getFileUrl($company['logo']) : default_empty('logo');
                    
                    $content_arr = [
                        "Title" => '【'.$company['companyname'].'】招聘-'.config('global_config.sitename'),
                        "Description" => $companycontents, 
                        "PicUrl" => $logo,
                        "Url" => config('global_config.mobile_domain').'company/'.$company['id']
                    ];
                    $this->outputArticle($object,$content_arr);
                }else{
                    $this->outputText($object,'企业不存在或已删除');
                }
                break;
            default:
                break;
        }
        exit;
    }
    /**
     * 获取微信用户信息
     */
    protected function getWeixinUserinfo($openid){
        $instance = new \app\common\lib\Wechat;
        $userinfo = $instance->getUserinfoByOpenid($openid);
        return $userinfo===false?null:$userinfo;
    }
    /**
     * 绑定事件
     */
    protected function clickBinding($object) {
        $memberinfo = $this->getUserInfo($object->FromUserName);
        if ($memberinfo!==null) {
            $content = "您已绑定过" . config('global_config.sitename') . ($memberinfo['utype']==1?'企业':'个人')."帐号【" . $memberinfo['mobile'] . "】,如需解绑,请回复'解绑'";
        } else {
            $weixin_userinfo = $this->getWeixinUserinfo($object->FromUserName);
            if($weixin_userinfo===null){
                $content = "获取用户信息失败，请稍候再试";
            }else{
                $openid = $weixin_userinfo['openid'];
                $unionid = isset($weixin_userinfo['unionid'])?$weixin_userinfo['unionid']:'';
                $nickname = $weixin_userinfo['nickname'];
                $avatar = $weixin_userinfo['headimgurl'];
                $query = '?bindType=weixin&openid='.$openid.'&unionid='.$unionid.'&nickname='.$nickname.'&avatar='.$avatar;
                $content = "您还未绑定" . config('global_config.sitename') . "帐号，现在开始绑定：<a href='" . config('global_config.mobile_domain') . "member/bind".$query."'>点击开始注册/绑定帐号</a>";
            }
        }
        $this->outputText($object,$content);
        exit;
    }
    /**
     * 联系客服
     */
    protected function clickContact($object) {
        $content = '客服电话：'.config('global_config.contact_tel');
        $this->outputText($object,$content);
        exit;
    }
    /**
     * 取消关注-解绑
     */
    protected function actionUnbind($object){
        $fromUsername = addslashes($object->FromUserName);
        $bindinfo = model('MemberBind')->where('type','weixin')->where('openid',$fromUsername)->find();
        if($bindinfo!==null){
            model('MemberBind')->where('type','weixin')->where('uid',$bindinfo['uid'])->delete();
            $this->outputText($object,'解绑成功');
        }else{
            $this->outputText($object,'您还没有绑定帐号！');
        }
        exit;
    }
    /**
     * 输出文字消息
     */
    protected function outputText($object,$content){
        $xmlTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
        </xml>';
        $result = sprintf($xmlTpl, addslashes($object->FromUserName), $object->ToUserName, time(), $content);
        $this->outputByEncryptMsg($result);
    }
    /**
     * 输出图片消息
     */
    protected function outputImage($object,$mediaId){
        $xmlTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
                <MediaId><![CDATA[%s]]></MediaId>
            </Image>
            </xml>';
        $result = sprintf($xmlTpl, addslashes($object->FromUserName), $object->ToUserName, time(), $mediaId);
        $this->outputByEncryptMsg($result);
    }
    //输出图文消息
    private function outputArticle($object, $article) {
        $itemTpl = "<item>
	        <Title><![CDATA[%s]]></Title>
	        <Description><![CDATA[%s]]></Description>
	        <PicUrl><![CDATA[%s]]></PicUrl>
	        <Url><![CDATA[%s]]></Url>
			</item>";
        $item_str = sprintf($itemTpl, $article['Title'], $article['Description'], $article['PicUrl'], $article['Url']);
        $xmlTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[news]]></MsgType>
			<ArticleCount>%s</ArticleCount>
			<Articles>
			$item_str</Articles>
			</xml>";
        $result = sprintf($xmlTpl, addslashes($object->FromUserName), $object->ToUserName, time(), 1);
        $this->outputByEncryptMsg($result);
    }
    /**
     * 输出客服消息
     */
    protected function outputMessage($object,$content,$type="text"){
        $openid = addslashes($object->FromUserName);
        $instance = new \app\common\lib\Wechat;
        $access_token = $instance->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        $datatext = [];
        if($type=='text'){
            $datatext = [
                "touser"=>$openid,
                "msgtype"=>"text",
                "text"=>[
                    "content"=>$content
                ]
            ];
        }else if($type=='image'){
            $datatext = [
                "touser"=>$openid,
                "msgtype"=>"image",
                "image"=>[
                    "media_id"=>$content
                ]
            ];
        }
        if(empty($datatext)){
            exit;
        }
        $datatext = json_encode($datatext,JSON_UNESCAPED_UNICODE);
        https_request($url , $datatext);
    }
    /**
     * 输出欢迎信息
     */
    protected function outputWelcome($object){
        $content = htmlspecialchars_decode(config('global_config.wechat_welcome_text'),ENT_QUOTES);
        $openid = addslashes($object->FromUserName);
        $weixin_userinfo = $this->getWeixinUserinfo($openid);
        if($weixin_userinfo!==null){
            $openid = $weixin_userinfo['openid'];
            $unionid = isset($weixin_userinfo['unionid'])?$weixin_userinfo['unionid']:'';
            $nickname = $weixin_userinfo['nickname'];
            $avatar = $weixin_userinfo['headimgurl'];
        }else{
            $openid = $openid;
            $unionid = '';
            $nickname = '';
            $avatar = '';
        }
        $content = str_replace("{domain}",config('global_config.mobile_domain'),$content);
        $content = str_replace("{openid}",$openid,$content);
        $content = str_replace("{unionid}",$unionid,$content);
        $content = str_replace("{nickname}",$nickname,$content);
        $content = str_replace("{avatar}",$avatar,$content);
        $this->outputMessage($object,$content,'text');
        $this->outputMessage($object,config('global_config.wechat_welcome_img_mediaid'),'image');
    }
}
