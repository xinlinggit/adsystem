<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | Company: YG | User: ShouKun Liu  |  Email:24147287@qq.com  | Time:2017/2/28 18:08
// +----------------------------------------------------------------------
// | TITLE: 文档
// +----------------------------------------------------------------------


namespace app\apilib\controller;


use app\common\model;
use think\Config;
use think\Request;
use think\Response;
use think\Url;
use think\Db;


class Index extends Base
{
    const METHOD_POSTFIX = 'Response';
    public static $titleDoc = 'API文档';
    /**
     * 返回字段
     * @var array
     */
    public static $returnFieldMaps = [
        'name' => '参数名',
        'type' => '类型',
        'desc' => '说明',
    ];
    /**
     * 请求字段
     * @var array
     */
    public static $dataFieldMaps = [
        'name' => '参数名',
        'require' => '必须',
        'type' => '类型',
        'default' => '默认值',
        'range' => '范围',
        'desc' => '说明',
        'example' => '举例',
    ];

    /**
     * 默认参数值
     * @var array
     */
    public static $dataFields = [
        'name' => '',
        'require' => '',
        'type' => '',
        'default' => '',
        'range' => '',
        'remark' => '',
        'desc' => '',
        'example' => '',
    ];

    /**
     * 字段类型
     * @var array
     */
    public static $typeMaps = [
        'string' => '字符串',
        'int' => '整型',
        'float' => '浮点型',
        'boolean' => '布尔型',
        'date' => '日期',
        'array' => '数组',
        'fixed' => '固定值',
        'enum' => '枚举类型',
        'object' => '对象',
    ];

    /**
     * 文档首页
     */
    public function main()
    {
        //字段
        $field = [
            'title'=>'标题',
            'module'=>'模型',
            'controller'=>'控制器',
            'readme'=>'文档',
            'state'=>'状态',
            'update_time'=>'更新时间',
        ];
        $data = ['field' => $field];
        return view('',$data);
    }

    public function mainList(){
        $menuApi = new model\MenuApi();
        $rules = $menuApi->where('state',1)->select();
        $responseData = array_values($rules);
        return Response::create($responseData, 'json');
    }

    public function postApi(){
        $request = Request::instance();
        $param = $request->param();

    }
    /**
     * 接口列表
     * @return \think\response\View
     */
    public function Index()
    {
        $apiList = self::getApiDocList();
        //$tree = new \SecurityLib\BigMath\PHPMath($apiList);
        //$tree = Tree::getNodes($apiList);
        //dump($tree);
        $data = ['menu' => self::buildMenuHtml(Tree::makeTree($apiList)), 'titleDoc' => self::$titleDoc];
        return view('apilist',$data);
    }

    /**
     * 接口详细文档
     * @param Request $request
     * @return \think\response\View
     */
    public function apiInfo(Request $request)
    {
        $id = $request->param('id');
        $apiOne = Db::name('menu_api')->where('id',$id)->find();
        $module = $apiOne['module'];
        $controller = $apiOne['controller'];

        $className = 'app\\' . $module . '\\' . 'controller\\' . $controller;

        //获取接口类注释
        $classDoc = self::getClassDoc($className);

        //没有接口类  判断是否有 Markdown文档
        if ($classDoc == false ) {
           //输出 Markdown文档
            if ( !isset($apiOne['readme']) || empty($apiOne['readme'])) return $this->sendError('','没有接口');
            return view('markdown', ['classDoc' => $apiOne,'titleDoc' => self::$titleDoc]);
        }
        $classDoc['module'] = $module;
        $classDoc['controller'] = $controller;
        $classDoc['id'] = $id;

        //获取请求列表文档
        $methodDoc = self::getMethodListDoc($className);

        //字段
        $fieldMaps['return'] = self::$returnFieldMaps;
        $fieldMaps['data'] = self::$dataFieldMaps;
        $fieldMaps['type'] = self::$typeMaps;

        return view('info', ['classDoc' => $classDoc, 'methodDoc' => $methodDoc, 'fieldMaps' => $fieldMaps, 'titleDoc' => self::$titleDoc]);
    }

    /**
     * 获取文档
     * @return mixed
     */
    public static function getApiDocList()
    {
        $apiList = Db::name('menu_api')->where('state',1)->select()->toArray();
        //$apiList = Config::get('api_doc');
        //dump(MenuApi::getLastsql());exit;
        return $apiList;
    }

    /**
     * 获取数据
     * @param Request $request
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function tableData(Request $request)
    {
        $id = $request->param('id');
        $apiOne = Db::name('menu_api')->where('id',$id)->find();
        $className = 'app\\' . $apiOne['module'] . '\\' . 'controller\\' . $apiOne['controller'];

        $method = $request->param('method', 'class');
        $dataType = $request->param('dataType', 'data');
        if ($method === 'class') {
            $responseData = self::getClassDoc($className);
            $responseData = array_values($responseData['return']);
        } else {
            //获取接口类注释
            $methodDoc = self::getMethodListDoc($className);
            switch ($dataType) {
                case 'data':
                    $responseData = array_values($methodDoc[$method]['requestRules']);
                    self::formatMethodRules($responseData);
                    break;
                case 'return':
                    $responseData = array_values($methodDoc[$method]['responseRules']);
                    break;
                default:
                    $responseData = [];
                    break;
            }
        }
        return Response::create($responseData, 'json');
    }

    /**
     * 格式化接口参数数据
     * @param $methodParams
     */
    public static function formatMethodRules(&$methodParams)
    {
        if ( ! empty($methodParams) && is_array($methodParams) ){
            foreach ($methodParams as $k => $v){
                if($v['type'] == 'enum'){
                    if(is_array($v['range'])){
                        $v['range'] = implode('</br>',$v['range']);
                    }else{
                        $v['range'] = str_replace(',','</br>',$v['range']);
                    }
                };
                $methodParams[$k] = array_merge(self::$dataFields,$v);
            }
        }
    }

    /**
     * 获取接口类文档
     * @param $className
     * @return array
     */
    public static function getClassDoc($className)
    {
        try {
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException  $e) {
            return false;
        }
        $docComment = $reflection->getDocComment();
        return self::getDoc($docComment);
    }

    /**
     * 获取各种方式响应文档
     * @param $className
     * @return mixed
     */
    public static function getMethodListDoc($className)
    {
        //获取参数规则
        $requestRules = $className::requestRules();
        //返回参数规则
        $responseRules = $className::responseRules();
        //返回CODE说明
        $responseCodes = $className::responseCodes();
        //获取方法列表
        $restMethodList = array_filter(get_class_methods($className), function ($item) {
            return preg_match('/^(get|post|put|delete|patch|head|options)/', $item) ? $item : false;
        });
        $methodDoc = [];
        //遍历方法绑定对应注释
        foreach ($restMethodList as $method) {
            $reflection = new \ReflectionMethod($className, $method );
            if ( $reflection->class === $className ){
                //获取title,desc,readme,return等说明
                $methodDoc[$method] = self::getDoc($reflection->getDocComment());
                $methodDoc[$method]['requestRules'] = isset($requestRules[$method]) ?  array_merge($requestRules['all'], $requestRules[$method]) : $requestRules['all'];
                $methodDoc[$method]['responseRules'] = isset($responseRules[$method]) ?  array_merge($responseRules['all'], $responseRules[$method]) : $responseRules['all'];
            }
        }

        //dump($methodDoc);
        return $methodDoc;
    }


    /**
     * 获取注释转换成数组
     * @param $docComment
     * @return mixed
     */
    private static function getDoc($docComment)
    {
        $docCommentArr = explode("\n", $docComment);
        foreach ($docCommentArr as $comment) {
            $comment = trim($comment);
            //接口名称
            $pos = stripos($comment, '@title');
            if ($pos !== false) {
                $data['title'] = trim(substr($comment, $pos + 6));
                continue;
            }
            //接口类型
            $pos = stripos($comment, '@type');
            if ($pos !== false) {
                $data['type'] = trim(substr($comment, $pos + 5));
                continue;
            }
            //接口描述
            $pos = stripos($comment, '@desc');
            if ($pos !== false) {
                $data['desc'] = trim(substr($comment, $pos + 5));
                continue;
            }
            //接口说明文档
            $pos = stripos($comment, '@readme');
            if ($pos !== false) {
                $data['readme'] = trim(substr($comment, $pos + 7));
                continue;
            }
            //接口url
            $pos = stripos($comment, '@url');
            if ($pos !== false) {
                $data['url'] = trim(substr($comment, $pos + 4));
                continue;
            }
            //接口url versions
            $pos = stripos($comment, '@version');
            if ($pos !== false) {
                $data['version'] = trim(substr($comment, $pos + 8));
                continue;
            }

            //返回字段说明
            //@return注释
            $pos = stripos($comment, '@return');
            //以上都没有匹配到直接下一行
            if ($pos === false) {
                continue;
            }
            $returnCommentArr = explode(' ', substr($comment, $pos + 8));
            //将数组中的空值过滤掉，同时将需要展示的值返回
            $returnCommentArr = array_values(array_filter($returnCommentArr));
            //如果小于3个也过滤
            if (count($returnCommentArr) < 2) {
                continue;
            }
            if (!isset($returnCommentArr[2])) {
                $returnCommentArr[2] = '';    //可选的字段说明
            } else {
                //兼容处理有空格的注释
                $returnCommentArr[2] = implode(' ', array_slice($returnCommentArr, 2));
            }
            $returnCommentArr[0] = (in_array(strtolower($returnCommentArr[0]), array_keys(self::$typeMaps))) ? self::$typeMaps[strtolower($returnCommentArr[0])] : $returnCommentArr[0];
            $data['return'][] = [
                'name' => $returnCommentArr[1],
                'type' => $returnCommentArr[0],
                'desc' => $returnCommentArr[2],
            ];

        }
        $data['title'] = (isset($data['title'])) ? $data['title'] : '';
        $data['type'] = (isset($data['type'])) ? $data['type'] : '';
        $data['desc'] = (isset($data['desc'])) ? $data['desc'] : '';
        $data['readme'] = (isset($data['readme'])) ? $data['readme'] : '';
        $data['return'] = (isset($data['return'])) ? $data['return'] : [];
        $data['url'] = (isset($data['url'])) ? $data['url'] : '';
        $data['version'] = (isset($data['version'])) ? $data['version'] : '';
        return $data;
    }

    /**
     * 生成 接口菜单
     * @param $data
     * @param string $html
     * @return string
     */
    private static function buildMenuHtml($data, $html = '')
    {
        foreach ($data as $k => $v) {
            //dump($v);
            $html .= '<li >';
            if (isset($v['children']) && is_array($v['children'])) {
                $html .= '<a href="javascript:;"><i class="fa fa-folder"></i> <span class="nav-label">' . $v['title'] . '</span><span class="fa arrow"></span></a>';//name
            } else {
                $html .= '<a href="' . Url::build('apiInfo', ['module' => $v['module'], 'controller' => $v['controller'], 'id' => $v['id']]) . '" class="J_menuItem"><i class="fa fa-file"></i> <span class="nav-label">' . $v['title'] . '</span></a>';//
            }
            //需要验证是否有子菜单
            if (isset($v['children']) && is_array($v['children'])) {

                $html .= '<ul class="nav nav-second-level">';
                $html .= self::buildMenuHtml($v['children']);
                //验证是否有子订单
                $html .= '</ul>';

            }
            $html .= '</li>';

        }
        return $html;

    }


}

class Tree
{

    protected static $config = array(
        /* 主键 */
        'primary_key' => 'id',
        /* 父键 */
        'parent_key' => 'pid',
        /* 展开属性 */
        'expanded_key' => 'expanded',
        /* 叶子节点属性 */
        'leaf_key' => 'leaf',
        /* 孩子节点属性 */
        'children_key' => 'children',
        /* 是否展开子节点 */
        'expanded' => false
    );

    /* 结果集 */
    protected static $result = array();

    /* 层次暂存 */
    protected static $level = array();

    /**
     * @name 生成树形结构
     * @param array $data 二维数组
     * @param array $options 二维数组
     * @return mixed 多维数组
     */
    public static function makeTree($data, $options = array())
    {
        $dataset = self::buildData($data, $options);
        $r = self::makeTreeCore(0, $dataset, 'normal');
        return $r;
    }

    /* 生成线性结构, 便于HTML输出, 参数同上 */
    public static function makeTreeForHtml($data, $options = array())
    {

        $dataset = self::buildData($data, $options);
        $r = self::makeTreeCore(0, $dataset, 'linear');
        return $r;
    }

    /* 格式化数据, 私有方法 */
    private static function buildData($data = [], $options = [])
    {
        is_array($data) ? '' : $data = [];
        $config = array_merge(self::$config, $options);
        self::$config = $config;
        //extract($config);
        $r = array();
        foreach ($data as $item) {
            $id = $item[$config['primary_key']];
            $parent_id = $item[$config['parent_key']];
            $r[$parent_id][$id] = $item;
        }
        return $r;
    }

    /* 生成树核心, 私有方法  */
    private static function makeTreeCore($index = 0, $data = [], $type = 'linear')
    {
        //extract(self::$config);
        if(!isset($data[$index])){
            return [];
        }
        foreach ($data[$index] as $id => $item) {
            if ($type == 'normal') {
                if (isset($data[$id])) {
                    $item[self::$config['expanded_key']] = self::$config['expanded'];
                    $item[self::$config['children_key']] = self::makeTreeCore($id, $data, $type);
                } else {
                    $item[self::$config['leaf_key']] = true;
                }
                $r[] = $item;
            } else if ($type == 'linear') {
                $parent_id = $item[self::$config['parent_key']];
                self::$level[$id] = $index == 0 ? 0 : self::$level[$parent_id] + 1;
                $item['level'] = self::$level[$id];
                self::$result[] = $item;
                if (isset($data[$id])) {
                    self::makeTreeCore($id, $data, $type);
                }

                $r = self::$result;
            }
        }
        return $r;
    }
}
