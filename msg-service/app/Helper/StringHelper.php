<?php

declare(strict_types=1);

namespace App\Helper;

class StringHelper
{
    /**
     * 半角字符集
     * @var array
     */
    public static $DBCChars = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
        '-',
        ' ',
        ':',
        '.',
        ',',
        '/',
        '%',
        '#',
        '!',
        '@',
        '&',
        '(',
        ')',
        '<',
        '>',
        '"',
        '\'',
        '?',
        '[',
        ']',
        '{',
        '}',
        '\\',
        '|',
        '+',
        '=',
        '_',
        '^',
        '$',
        '~',
        '`'
    ];

    /**
     * 全角字符集
     * @var array
     */
    public static $SBCChars = [
        '０',
        '１',
        '２',
        '３',
        '４',
        '５',
        '６',
        '７',
        '８',
        '９',
        'Ａ',
        'Ｂ',
        'Ｃ',
        'Ｄ',
        'Ｅ',
        'Ｆ',
        'Ｇ',
        'Ｈ',
        'Ｉ',
        'Ｊ',
        'Ｋ',
        'Ｌ',
        'Ｍ',
        'Ｎ',
        'Ｏ',
        'Ｐ',
        'Ｑ',
        'Ｒ',
        'Ｓ',
        'Ｔ',
        'Ｕ',
        'Ｖ',
        'Ｗ',
        'Ｘ',
        'Ｙ',
        'Ｚ',
        'ａ',
        'ｂ',
        'ｃ',
        'ｄ',
        'ｅ',
        'ｆ',
        'ｇ',
        'ｈ',
        'ｉ',
        'ｊ',
        'ｋ',
        'ｌ',
        'ｍ',
        'ｎ',
        'ｏ',
        'ｐ',
        'ｑ',
        'ｒ',
        'ｓ',
        'ｔ',
        'ｕ',
        'ｖ',
        'ｗ',
        'ｘ',
        'ｙ',
        'ｚ',
        '－',
        '　',
        '：',
        '．',
        '，',
        '／',
        '％',
        '＃',
        '！',
        '＠',
        '＆',
        '（',
        '）',
        '＜',
        '＞',
        '＂',
        '＇',
        '？',
        '［',
        '］',
        '｛',
        '｝',
        '＼',
        '｜',
        '＋',
        '＝',
        '＿',
        '＾',
        '＄',
        '～',
        '｀'
    ];

    /**
     * md5短串(返回16位md5值)
     *
     * @param string $str
     *
     * @return string
     */
    public static function md5Short(string $str): string
    {
        return substr(md5(strval($str)), 8, 16);
    }

    /**
     * 字符串剪切(宽字符)
     *
     * @param string $str    字符串
     * @param int    $length 截取长度
     * @param int    $start  开始位置
     * @param string $dot    后接的省略符
     *
     * @return string
     */
    public static function cutStr(string $str, int $length, int $start = 0, string $dot = ''): string
    {
        //转换html实体
        $str = htmlspecialchars_decode($str);
        $len = mb_strlen($str, 'UTF-8');
        $str = mb_substr($str, $start, $length, 'UTF-8');

        if ($length && $length < $len - $start) {
            $str .= $dot;
        }

        return $str;
    }

    /**
     * 获取宽字符串长度函数
     *
     * @param string $str
     * @param bool   $filterTags 是否过滤(html/php)标签
     *
     * @return int
     */
    public static function length(string $str, bool $filterTags = false): int
    {
        if ($filterTags) {
            $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            $str = strip_tags($str);
        }

        return mb_strlen($str, 'UTF-8');
    }

    /**
     * 简单随机字符串
     *
     * @param int  $len        字符串长度
     * @param bool $hasSpecial 是否有特殊字符
     *
     * @return string
     */
    public static function randSimple(int $len = 6, bool $hasSpecial = false): string
    {
        $chars = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
        if ($hasSpecial) {
            $chars .= '!@#$%^&*()_+-=`~[]{}|<>?:';
        }

        $result = '';
        $max    = strlen($chars) - 1;
        for ($i = 0; $i < $len; $i++) {
            $result .= $chars[rand(0, $max)];
        }
        return $result;
    }

    /**
     * 随机数字
     *
     * @param int $len 字符串长度
     *
     * @return string
     */
    public static function randNumber(int $len = 6): string
    {
        if ($len <= 10) {
            $arr = range(0, 9);
        } else {
            $arr = range(0, pow(10, ceil($len / 10)) - 1);
        }
        shuffle($arr);
        $str = implode('', $arr);

        return substr($str, 0, $len);
    }

    /**
     * 生成随机字串
     *
     * @param int    $len      长度
     * @param int    $type     字串类型:1 不区分大小写的字母, 2 数字, 3 大写字母, 4 小写字母, 5 中文, 0 数值和字母
     * @param string $addChars 额外的随机字符
     *
     * @return string
     */
    public static function randString(int $len = 6, int $type = 0, string $addChars = ''): string
    {
        $str = '';
        switch ($type) {
            case 1:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 2:
                $chars = '0123456789';
                break;
            case 3:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 4:
                $chars = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 5:
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借";
                break;
            case 0:
            default:
                // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                break;
        }

        if (!empty($addChars)) {
            $chars .= $addChars;
        }

        //位数过长重复字符串一定次数
        $charLen = mb_strlen($chars, 'UTF-8');
        $diff    = $len / $charLen;
        if ($diff > 1) {
            $chars = str_repeat($chars, ceil($diff));
        }

        if ($type == 5) { // 中文随机字
            for ($i = 0; $i < $len; $i++) {
                $str .= mb_substr($chars, floor(mt_rand(0, $charLen - 1)), 1, 'UTF-8');
            }
        } else {
            $chars = str_shuffle($chars);
            $str   = substr($chars, 0, $len);
        }

        return $str;
    }

    /**
     * 修复未闭合的html标签.如
     * fixHtml('这是一段被截断的html文本<a href="#"');
     *
     * @param string $html
     *
     * @return string
     */
    public static function fixHtml(string $html): string
    {
        $isMultibyte = ValidateHelper::isMultibyte($html);
        $hasBodyTag  = preg_match_all("/<(\/?(html|body).*?)>/is", $html, $bodyMatch);

        $doc = new \DOMDocument();
        if ($isMultibyte) {
            $html = mb_convert_encoding($html, 'html-entities', 'UTF-8');
        }

        @$doc->loadHTML($html);
        $res = $doc->saveHTML();
        if ($isMultibyte) {
            $res = html_entity_decode($res);
        }

        if (!$hasBodyTag) {
            $res = preg_replace("/<(\!DOCTYPE.*?)>/is", '', $res);
            $res = preg_replace("/<(\/?(html|body).*?)>/is", '', $res);
        }

        return trim($res);
    }

    /**
     * 半角转全角字符
     *
     * @param string $str
     *
     * @return string
     */
    public static function DBC2SBC(string $str): string
    {
        return str_replace(self::$DBCChars, self::$SBCChars, $str);
    }

    /**
     * 全角转半角字符
     *
     * @param string $str
     *
     * @return string
     */
    public static function SBC2DBC(string $str): string
    {
        return str_replace(self::$SBCChars, self::$DBCChars, $str);
    }

    /**
     * 获取相似度最高的字符串,结果是数组,包含相似字符和编辑距离.
     *
     * @param string $word    要比较的字符串
     * @param array  $searchs 要查找的字符串数组
     *
     * @return array
     */
    public static function getClosestWord(string $word, array $searchs): array
    {
        $shortest = -1;
        $closest  = null;

        foreach ($searchs as $search) {
            $lev = levenshtein($word, $search);
            if ($lev == 0) { //完全相等
                $closest  = $search;
                $shortest = 0;
                break;
            }
            if ($lev <= $shortest || $shortest < 0) {
                $closest  = $search;
                $shortest = $lev;
            }
        }

        $res = [
            $closest,
            $shortest,
        ];

        return $res;
    }

    /**
     * escape编码
     *
     * @param string $str     待编码字符串
     * @param string $charset 字符集
     *
     * @return string
     */
    public static function escape(string $str, $charset = 'UTF-8'): string
    {
        preg_match_all("/[^\x{00}-\x{ff}]|[\x{00}-\x{ff}]+/u", $str, $matches);
        $arr = $matches[0] ?? [];
        foreach ($arr as $k => $v) {
            if (ord($v[0]) < 128) {
                $arr[$k] = rawurlencode($v);
            } else {
                //$arr[$k] = "%u" . bin2hex(@iconv($charset, "UCS-2", $v));
                $arr[$k] = "%u" . bin2hex(mb_convert_encoding($v, 'UCS-2', $charset));
            }
        }

        return join('', $arr);
    }

    /**
     * unescape解码
     *
     * @param string $str     待解码字符串
     * @param string $charset 字符集
     *
     * @return string
     */
    public static function unescape(string $str, $charset = 'UTF-8'): string
    {
        $str = rawurldecode($str);
        preg_match_all("/%u.{4}|&#x.{4};|&#\d+;|.+/U", $str, $matches);
        $arr = $matches[0] ?? [];

        foreach ($arr as $k => $v) {
            if (substr($v, 0, 2) == "%u") {
                $arr[$k] = mb_convert_encoding(pack("H4", substr($v, -4)), $charset, 'UCS-2');
            } elseif (substr($v, 0, 3) == "&#x") {
                $arr[$k] = mb_convert_encoding(pack("H4", substr($v, 3, -1)), $charset, 'UCS-2');
            } elseif (substr($v, 0, 2) == "&#") {
                $arr[$k] = mb_convert_encoding(pack("H4", substr($v, 2, -1)), $charset, 'UCS-2');
            }
        }

        return join('', $arr);
    }

    /**
     * 获取字符串的首字母
     *
     * @param string $str
     *
     * @return string
     */
    public static function getFirstLetter(string $str): string
    {
        $res = '';
        if (!empty($str)) {
            $firstChar = ord(strtoupper($str[0]));
            if ($firstChar >= 65 && $firstChar <= 91) {
                return strtoupper($str[0]);
            }

            //$s   = iconv("UTF-8", "gb2312", $str);
            $s   = mb_convert_encoding($str, 'gb2312');
            $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
            if ($asc >= -20319 && $asc <= -20284) {
                return "A";
            }
            if ($asc >= -20283 && $asc <= -19776) {
                return "B";
            }
            if ($asc >= -19775 && $asc <= -19219) {
                return "C";
            }
            if ($asc >= -19218 && $asc <= -18711) {
                return "D";
            }
            if ($asc >= -18710 && $asc <= -18527) {
                return "E";
            }
            if ($asc >= -18526 && $asc <= -18240) {
                return "F";
            }
            if ($asc >= -18239 && $asc <= -17923) {
                return "G";
            }
            if ($asc >= -17922 && $asc <= -17418) {
                return "H";
            }
            if ($asc >= -17417 && $asc <= -16475) {
                return "J";
            }
            if ($asc >= -16474 && $asc <= -16213) {
                return "K";
            }
            if ($asc >= -16212 && $asc <= -15641) {
                return "L";
            }
            if ($asc >= -15640 && $asc <= -15166) {
                return "M";
            }
            if ($asc >= -15165 && $asc <= -14923) {
                return "N";
            }
            if ($asc >= -14922 && $asc <= -14915) {
                return "O";
            }
            if ($asc >= -14914 && $asc <= -14631) {
                return "P";
            }
            if ($asc >= -14630 && $asc <= -14150) {
                return "Q";
            }
            if ($asc >= -14149 && $asc <= -14091) {
                return "R";
            }
            if ($asc >= -14090 && $asc <= -13319) {
                return "S";
            }
            if ($asc >= -13318 && $asc <= -12839) {
                return "T";
            }
            if ($asc >= -12838 && $asc <= -12557) {
                return "W";
            }
            if ($asc >= -12556 && $asc <= -11848) {
                return "X";
            }
            if ($asc >= -11847 && $asc <= -11056) {
                return "Y";
            }
            if ($asc >= -11055 && $asc <= -10247) {
                return "Z";
            }
        }

        return $res;
    }

    /**
     * 匹配图片(从html中提取img的地址)
     *
     * @param string $html
     *
     * @return array
     */
    public static function matchImages(string $html): array
    {
        $images = [];
        if (!empty($html)) {
            preg_match_all('/<img.*src=(.*)[>|\\s]/iU', $html, $matchs);
            if (isset($matchs[1]) && count($matchs[1]) > 0) {
                foreach ($matchs[1] as $v) {
                    $item = trim($v, "\"'"); //删除首尾的引号 ' "
                    array_push($images, $item);
                }
            }
        }

        return $images;
    }

    /**
     * br标签转换为nl
     *
     * @param string $str
     *
     * @return string
     */
    public static function br2nl(string $str): string
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $str);
    }

    /**
     * 去除字符串前后空格
     *
     * @param string $str
     *
     * @return string
     */
    public static function trim(string $str): string
    {
        return trim($str, " \t\n\r\v\f\0\x0B　");
    }

    /**
     * 移除字符串中的空格
     *
     * @param string $str
     * @param bool   $all 为true时移除全部空白,为false时只替换连续的空白字符为一个空格
     *
     * @return string
     */
    public static function removeSpace(string $str, bool $all = true): string
    {
        if ($str == '') {
            return '';
        }

        // 先将2个以上的连续空白符转为空格
        $str = preg_replace(RegularHelper::$patternWhitespaceDuplicate, ' ', $str);
        if ($all) {
            $str = str_replace([chr(13), chr(10), ' ', '　', '&nbsp;'], '', $str);
        }

        return self::trim($str);
    }

    /**
     * 获取纯文本(不保留行内空格)
     *
     * @param string $html
     *
     * @return string
     */
    public static function getText(string $html): string
    {
        if ($html == '') {
            return '';
        }

        //移除html,js,css标签
        $search = [
            '/<title[^>]*?>.*?<\/title>/si', // 去掉 title
            '/<script[^>]*?>.*?<\/script>/si', // 去掉 script
            '/<style[^>]*?>.*?<\/style>/si', // 去掉 style
            '/<option[^>]*?>.*?<\/option>/si', // 去掉 option
            '/<button[^>]*?>.*?<\/button>/si', // 去掉 button
            '/<!--[\/!]*?[^<>]*?>/si', // 去掉 注释标记
            '/<[\/!]*?[^<>]*?>/si', // 去掉 HTML标记
            '/([rn])[s]+/', // 去掉空白字符
            '/&(quot|#34);/i', // 替换 " 的HTML实体
            '/&(amp|#38);/i', // 替换 & 的HTML实体
            '/&(lt|#60);/i', // 替换 < 的HTML实体
            '/&(gt|#62);/i', // 替换 > 的HTML实体
            '/&(nbsp|#160);/i', // 替换 空格 的HTML实体
            '/&(iexcl|#161);/i', // 替换HTML实体
            '/&(cent|#162);/i', // 替换HTML实体
            '/&(pound|#163);/i', // 替换HTML实体
            '/&(copy|#169);/i', // 替换HTML实体
            '/&#(d+);/', // 作为PHP代码运行
        ];

        $replace = [
            '',
            '',
            '',
            '',
            '',
            '',
            "\1",
            '',
            '"',
            '&',
            '<',
            '>',
            ' ',
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            "chr(\1)",
        ];

        $str = preg_replace($search, $replace, $html);
        $str = strip_tags($str);
        $str = self::removeSpace($str);
        $str = mb_convert_encoding($str, 'UTF-8');

        return trim($str);
    }

    /**
     * 移除HTML标签(保留行内空格)
     *
     * @param string $html
     *
     * @return string
     */
    public static function removeHtml(string $html): string
    {
        if ($html == '') {
            return '';
        }

        $search = [
            '/\s+/', // 过滤多余回车
            '/<[ ]+/si', // 过滤<__("<"号后面带空格)
            '/<\!--.*?-->/is', // 过滤注释
            '/<(\!.*?)>/is', // 过滤DOCTYPE
            '/<(\/?html.*?)>/is', // 过滤 html 标签
            '/<(\/?head.*?)>/is', // 过滤 head 标签
            '/<(\/?meta.*?)>/is', // 过滤 meta 标签
            '/<(\/?body.*?)>/is', // 过滤 body 标签
            '/<(\/?link.*?)>/is', // 过滤 link 标签
            '/<(\/?form.*?)>/is', // 过滤 form 标签
            '/<style(.*?)<\/style>/is', // 过滤css
            '/<(style.*?)>(.*?)<(\/style.*?)>/is', // 过滤 style 标签
            '/<(\/?style.*?)>/is', // 过滤 style 标签
            '/<(applet.*?)>(.*?)<(\/applet.*?)>/is', // 过滤 applet 标签
            '/<(\/?applet.*?)>/is', // 过滤 applet 标签
            '/<(title.*?)>(.*?)<(\/title.*?)>/is', // 过滤 title 标签
            '/<(\/?title.*?)>/is', // 过滤 title 标签
            '/<(object.*?)>(.*?)<(\/object.*?)>/is', // 过滤 object 标签
            '/<(\/?objec.*?)>/is', // 过滤 object 标签
            '/<(noframes.*?)>(.*?)<(\/noframes.*?)>/is', // 过滤 noframes 标签
            '/<(\/?noframes.*?)>/is', // 过滤 noframes 标签
            '/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/is', // 过滤 frame 标签
            '/<(\/?i?frame.*?)>/is', // 过滤 frame 标签
            '/<(script.*?)>(.*?)<(\/script.*?)>/is', // 过滤 script 标签
            '/<(\/?script.*?)>/is', // 过滤 script 标签
            "/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/is", // 过滤img标签
            '/<option[^>]*?>.*?<\/option>/si', // 去掉 option
            '/<button[^>]*?>.*?<\/button>/si', // 去掉 button
            '/<(.*?)>/is', // 过滤标签
            '/cookie/i',
            '/javascript/is',
            '/vbscript/is',
            '/on([a-z]+)\s*=/is',
            '/&#/is',
        ];

        $replace = [
            ' ',
            '<',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'COOKIE',
            'Javascript',
            'Vbscript',
            "On\\1=",
            '&＃',
        ];

        $str = preg_replace($search, $replace, $html);
        return trim($str);
    }

    /**
     * 字符串/单词统计
     *
     * @param string $str
     * @param int    $type 统计类型: 0:按字符统计; 1:只统计英文单词; 2:按英文单词和中文字数
     *
     * @return int
     */
    public static function stringWordCount(string $str, int $type = 0): int
    {
        $str = trim($str);
        switch ($type) {
            case 0:
            default:
                $len = mb_strlen(self::removeHtml(self::removeSpace($str)), 'UTF-8');
                break;
            case 1:
                $str = self::removeHtml(html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
                $len = str_word_count($str);
                break;
            case 2:
                $str         = self::removeHtml(html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
                $utf8_cn     = "/[\x{4e00}-\x{9fff}\x{f900}-\x{faff}]/u"; //中文
                $utf8_symbol = "/[\x{ff00}-\x{ffef}\x{2000}-\x{206F}]/u"; //中文标点符号

                $str   = preg_replace($utf8_symbol, ' ', $str);
                $cnLen = preg_match_all($utf8_cn, $str, $textrr);

                $str   = preg_replace($utf8_cn, ' ', $str);
                $enLen = str_word_count($str);

                $len = intval($cnLen) + $enLen;
                break;
        }

        return $len;
    }

    /**
     * 隐藏证件号码
     *
     * @param string $str
     *
     * @return string
     */
    public static function hideCard(string $str): string
    {
        $res = '******';
        $len = strlen($str);
        if ($len > 4 && $len <= 10) {
            $res = substr($str, 0, 4) . '******';
        } elseif ($len > 10) {
            $res = substr($str, 0, 4) . '******' . substr($str, ($len - 4), $len);
        }

        return $res;
    }

    /**
     * 隐藏手机号
     *
     * @param string $str
     *
     * @return string
     */
    public static function hideMobile(string $str): string
    {
        $res = '***';
        $len = strlen($str);
        if ($len > 7) {
            $res = substr($str, 0, 3) . '****' . substr($str, ($len - 3), $len);
        }

        return $res;
    }

    /**
     * 隐藏真实名称(如姓名、账号、公司等)
     *
     * @param string $str
     *
     * @return string
     */
    public static function hideTrueName(string $str): string
    {
        $res = '**';
        if ($str != '') {
            $len = mb_strlen($str, 'UTF-8');
            if ($len <= 3) {
                $res = mb_substr($str, 0, 1, 'UTF-8') . $res;
            } elseif ($len < 5) {
                $res = mb_substr($str, 0, 2, 'UTF-8') . $res;
            } elseif ($len < 10) {
                $res = mb_substr($str, 0, 2, 'UTF-8') . '***' . mb_substr($str, ($len - 2), $len, 'UTF-8');
            } elseif ($len < 16) {
                $res = mb_substr($str, 0, 3, 'UTF-8') . '***' . mb_substr($str, ($len - 3), $len, 'UTF-8');
            } else {
                $res = mb_substr($str, 0, 4, 'UTF-8') . '***' . mb_substr($str, ($len - 4), $len, 'UTF-8');
            }
        }

        return $res;
    }

    /**
     * 统计base64字符串大小(字节)
     *
     * @param string $str base64字符串
     *
     * @return int
     */
    public static function countBase64Byte(string $str): int
    {
        if (empty($str)) {
            return 0;
        }

        $str = preg_replace('/^(data:\s*(image|img)\/(\w+);base64,)/', '', $str);
        $str = str_replace('=', '', $str);
        $len = strlen($str);
        $res = intval($len * (3 / 4));
        return $res;
    }

    /**
     * 将字符串转换成二进制
     *
     * @param string $str
     *
     * @return string
     */
    public static function str2Bin(string $str): string
    {
        //列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v    = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ', $arr);
    }

    /**
     * 将二进制转换成字符串
     *
     * @param string $str
     *
     * @return string
     */
    public static function bin2Str(string $str): string
    {
        $arr = explode(' ', $str);
        foreach ($arr as &$v) {
            $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }

        return join('', $arr);
    }

    /**
     * 多分隔符切割字符串
     *
     * @param string $str           源字符串
     * @param string ...$delimiters 分隔符数组
     *
     * @return array
     */
    public static function multiExplode(string $str, string ...$delimiters): array
    {
        $res = [];
        if ($str == '') {
            return $res;
        }

        $dLen = count($delimiters);
        if ($dLen == 0) {
            array_push($res, $str);
        } else {
            if ($dLen > 1) {
                $str = str_replace($delimiters, $delimiters[0], $str);
            }

            $res = explode($delimiters[0], $str);
        }

        return $res;
    }

    /**
     * 移除emoji表情字符
     *
     * @param string $str
     *
     * @return string
     */
    public static function removeEmoji(string $str): string
    {
        if ($str != '') {
            $hasTree = false;
            $str     = preg_replace_callback(
                '/./u',
                function (array $match) use (&$hasTree) {
                    $len = strlen($match[0]);
                    //存在3字节长度的表情符
                    if ($len == 3) {
                        $hasTree = true;
                    }
                    return $len >= 4 ? '' : $match[0];
                },
                $str
            );

            if ($hasTree) {
                $str = preg_replace(
                    '/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FF})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FE})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FD})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FC})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FB})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F970}\x{1F973}-\x{1F976}\x{1F97A}\x{1F97C}-\x{1F9A2}\x{1F9B0}-\x{1F9B9}\x{1F9C0}-\x{1F9C2}\x{1F9D0}-\x{1F9FF}]/u',
                    '',
                    $str
                );
            }
        }

        return $str;
    }

    /**
     * 是否字符转换连接符
     *
     * @param string $str
     *
     * @return bool
     */
    private static function isCaseConnector(string $str): bool
    {
        return mb_strlen($str) == 1 && ($str == '-' || $str == '_' || ValidateHelper::isSpace($str));
    }

    /**
     * 驼峰转为小写
     *
     * @param string $str
     * @param string $connector 连接符
     *
     * @return string
     */
    private static function camelCaseToLowerCase(string $str, string $connector): string
    {
        if ($str == '') {
            return '';
        }

        $res  = [];
        $prev = $r0 = $r1 = '';
        $r0   = $connector;

        while (mb_strlen($str) > 0) {
            $prev = $r0;
            $r0   = mb_substr($str, 0, 1);
            $str  = mb_substr($str, 1);

            switch ($r0) {
                case ValidateHelper::isUpperLetter($r0):
                    if ($prev != $connector && !is_numeric($prev)) {
                        array_push($res, $connector);
                    }

                    array_push($res, strtolower($r0));

                    if (strlen($str) == 0) {
                        break;
                    }

                    $r0  = mb_substr($str, 0, 1);
                    $str = mb_substr($str, 1);

                    if (!ValidateHelper::isUpperLetter($r0)) {
                        array_push($res, $r0);
                        break;
                    }

                    while (mb_strlen($str) > 0) {
                        $r1  = $r0;
                        $r0  = mb_substr($str, 0, 1);
                        $str = mb_substr($str, 1);

                        if (!ValidateHelper::isUpperLetter($r0)) {
                            if (self::isCaseConnector($r0)) {
                                $r0 = $connector;
                                array_push($res, strtolower($r1));
                            } elseif (is_numeric($r0)) {
                                array_push($res, strtolower($r1));
                                array_push($res, $connector);
                                array_push($res, $r0);
                            } else {
                                array_push($res, $connector);
                                array_push($res, strtolower($r1));
                                array_push($res, $r0);
                            }

                            break;
                        }

                        array_push($res, strtolower($r1));
                    }

                    if (strlen($str) == 0 || $r0 == $connector) {
                        array_push($res, strtolower($r0));
                    }

                    break;
                case is_numeric($r0):
                    if ($prev != $connector && !is_numeric($prev)) {
                        array_push($res, $connector);
                    }
                    array_push($res, $r0);

                    break;
                default:
                    if (self::isCaseConnector($r0)) {
                        $r0 = $connector;
                    }
                    array_push($res, $r0);
                    break;
            }
        }

        return implode('', $res);
    }

    /**
     * 转为驼峰写法
     *
     * @param string $str
     *
     * @return string
     */
    public static function toCamelCase(string $str): string
    {
        if ($str == '') {
            return '';
        }

        $res = [];
        $r0  = $r1 = '';

        while (strlen($str) > 0) {
            $r0  = mb_substr($str, 0, 1);
            $str = mb_substr($str, 1);

            if (!self::isCaseConnector($r0)) {
                $r0 = strtoupper($r0);
                break;
            }

            array_push($res, $r0);
        }

        while (strlen($str) > 0) {
            $r1  = $r0;
            $r0  = mb_substr($str, 0, 1);
            $str = mb_substr($str, 1);

            if (self::isCaseConnector($r0) && self::isCaseConnector($r1)) {
                array_push($res, $r1);
                continue;
            }

            if (self::isCaseConnector($r1)) {
                $r0 = strtoupper($r0);
            } else {
                $r0 = strtolower($r0);
                array_push($res, $r1);
            }
        }
        array_push($res, $r0);

        return implode('', $res);
    }

    /**
     * 转为蛇形写法
     *
     * @param string $str
     *
     * @return string
     */
    public static function toSnakeCase(string $str): string
    {
        return self::camelCaseToLowerCase($str, '_');
    }

    /**
     * 转为串形写法
     *
     * @param string $str
     *
     * @return string
     */
    public static function toKebabCase(string $str): string
    {
        return self::camelCaseToLowerCase($str, '-');
    }

    /**
     * 检查字符串 $str 是否包含数组$arr的元素之一
     *
     * @param string $str
     * @param array  $arr         字符串数组
     * @param bool   $returnValue 是否返回匹配的值
     * @param bool   $case        是否检查大小写
     *
     * @return bool|mixed
     */
    public static function dstrpos(string $str, array $arr, bool $returnValue = false, bool $case = false)
    {
        if (empty($str) || empty($arr)) {
            return false;
        }

        foreach ($arr as $v) {
            $v = strval($v);
            if ($case ? strpos($str, $v) !== false : stripos($str, $v) !== false) {
                $return = $returnValue ? $v : true;
                return $return;
            }
        }

        return false;
    }

    /**
     * 移除before之前的字符串
     *
     * @param string $str
     * @param string $before
     * @param bool   $include    是否移除包括before本身
     * @param bool   $ignoreCase 是否忽略大小写
     *
     * @return string
     */
    public static function removeBefore(string $str, string $before, bool $include = false, bool $ignoreCase = false): string
    {
        if ($str == '' || $before == '') {
            return $str;
        }

        $i = $ignoreCase ? mb_stripos($str, $before) : mb_strpos($str, $before);
        if ($i !== false) {
            if ($include) {
                $i += mb_strlen($before);
            }
            $str = mb_substr($str, $i);
        }

        return $str;
    }

    /**
     * 移除after之后的字符串
     *
     * @param string $str
     * @param string $after
     * @param bool   $include    是否移除包括after本身
     * @param bool   $ignoreCase 是否忽略大小写
     *
     * @return string
     */
    public static function removeAfter(string $str, string $after, bool $include = false, bool $ignoreCase = false): string
    {
        if ($str == '' || $after == '') {
            return $str;
        }

        $i = $ignoreCase ? mb_stripos($str, $after) : mb_strpos($str, $after);
        if ($i !== false) {
            if (!$include) {
                $i += mb_strlen($after);
            }
            $str = mb_substr($str, 0, $i);
        }

        return $str;
    }

    /**
     * 计算密码安全等级
     *
     * @param string $str 密码
     *
     * @return int 等级0~4
     */
    public static function passwdSafeGrade(string $str): int
    {
        $special = '/[\W_]/'; //特殊字符
        $partArr = [
            '/[0-9]/',
            '/[a-z]/',
            '/[A-Z]/',
            $special,
        ];
        $score   = 0;
        $leng    = strlen($str);

        //根据长度加分
        if ($leng > 0) {
            $score += ($leng >= 6) ? $leng : 1;
            //根据类型加分
            foreach ($partArr as $part) {
                //某类型存在加分
                if (preg_match($part, $str)) {
                    $score += ($part == $special) ? 7 : 3;
                }

                $regexCount = preg_match_all($part, $str, $out); //某类型存在，并且存在个数大于2加2分，个数大于5加6分
                if ($regexCount >= 5) {
                    $score += 6;
                } elseif ($regexCount >= 2) {
                    $score += 2;
                }
            }
        }

        //重复检测
        $repeatChar  = '';
        $repeatCount = 0;
        for ($i = 0; $i < $leng; $i++) {
            if ($str[$i] == $repeatChar) {
                $repeatCount++;
            } else {
                $repeatChar = $str[$i];
            }
        }
        $score -= $repeatCount * 2;

        //等级
        if ($score <= 0) { //极弱
            $level = 0;
        } elseif ($score <= 20) { //弱
            $level = 1;
        } elseif ($score <= 30) { //一般
            $level = 2;
        } elseif ($score <= 40) { //很好
            $level = 3;
        } else { //极佳
            $level = 4;
        }

        //如果是弱密码
        $weakPwds = [
            '00000000000',
            '01234567890',
            '09876543210',
            '11111111111',
            '123123123123',
            '1q2w3e4r5t',
            '22222222222',
            '31415926',
            '66666666666',
            '77777777777',
            '88888888888',
            'a123456789a',
            'aaaaaaaaaaa',
            'abc12345689',
            'abcd1234567',
            'asdasdasdasd',
            'asdfghjkl',
            'iloveyou',
            'password',
            'q1w2e3r4t5y6',
            'qazwsxedc',
            'qq123456qq',
            'qqqqqqqqqq',
            'qwertyuiop',
            'zxcvbnm',
        ];
        foreach ($weakPwds as $weakPwd) {
            if ($str == $weakPwd || stripos($weakPwd, $str) !== false) {
                $level = 1;
                break;
            }
        }

        return $level;
    }

    /**
     * 获取UUID(Version4)
     * @return string
     * @throws Exception
     */
    public static function uuidV4(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * 字符串$str是否包含$sub
     *
     * @param string $str
     * @param string $sub
     * @param bool   $ignoreCase 是否忽略大小写
     *
     * @return bool
     */
    public static function contains(string $str, string $sub, bool $ignoreCase = false): bool
    {
        if (is_null($str) || $str === '') {
            return false;
        }

        $res = $ignoreCase ? mb_stripos($str, $sub) : mb_strpos($str, $sub);
        return $res !== false;
    }

    /**
     * 截取指定定界符中间的内容
     *
     * @param string      $str   要截取的字符串
     * @param string|null $begin 开始定界符
     * @param string|null $end   结束定界符
     *
     * @return string
     */
    public static function middle(string $str, string $begin = null, string $end = null): string
    {
        if ($str === '') {
            return '';
        }

        // 如果提供了开始定界符
        if (!is_null($begin) && $begin !== '') {
            // 计算开始定界符的出现位置
            $beginPos = mb_stripos($str, $begin);

            // 如果没找到开始定界符,失败
            if ($beginPos === false) {
                return '';
            }

            // 去除开始定界符及以前的内容.
            $str = mb_substr($str, $beginPos + strlen($begin));
        }

        // 如果未提供结束定界符,直接 返回了.
        if (is_null($end) || $end === '') {
            return $str;
        }

        // 计算结束定界符的出现位置
        $endPos = mb_stripos($str, $end);

        // 如果没找到,失败
        if ($endPos === false) {
            return '';
        }

        // 如果位置为0,返回空字符串
        if ($endPos === 0) {
            return '';
        }

        // 返回 字符串直到定界符开始的地方
        return mb_substr($str, 0, $endPos);
    }
}
