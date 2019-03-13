<?php

namespace Base;

use Base\Exception\BadRequestException;
use Base\Request\Parser;
use EasySwoole\Config;
use EasySwoole\Core\Http\AbstractInterface\Controller;

/**
 * Class Base
 * @package Base\BaseController
 */
class BaseController extends Controller
{
    /**
     * 请求参数
     * @var array
     */
    protected $params = [];

    function index()
    {
        // TODO: Implement index() method.
    }

    protected function getRules()
    {
        return [];
    }

    /**
     * 权限校验
     */
    protected function checkUser()
    {

    }

    /**
     * 验证签名
     * @param $params
     * @throws BadRequestException
     */
    protected function checkSign($params)
    {
        (new SignFilter())->check($params);
    }

    /**
     * @param $action
     * @return bool|null
     */
    protected function onRequest($action): ?bool
    {
        $this->allowOrigin();
        $allRules = $this->getRules();
        if (!empty($allRules)) {
            $rules = [];
            if (isset($allRules[$action]) && is_array($allRules[$action])) {
                $rules = $allRules[$action];
            }
            if (isset($allRules['*'])) {
                $rules = array_merge($allRules['*'], $rules);
            }
            $data = $this->request()->getRequestParam();
            try {
                //请求参数
                foreach ($rules as $key => $rule) {
                    if ($key === 'ignore_sign' || $key === 'ignore_auth') continue;
                    $this->params[$key] = $this->validateByRule($key, $rule, $data);
                }
                //签名服务
                if (!isset($rules['ignore_sign']) || $rules['ignore_sign'] === false) {
                    $this->checkSign($data);
                } else {
                    unset($rules['ignore_sign']);
                }
                //用户权限验证
                if (!isset($rules['ignore_auth']) || $rules['ignore_auth'] === false) {
                    $this->checkUser();
                } else {
                    unset($rules['ignore_auth']);
                }
            } catch (\Exception $e) {
                $this->errorJson($e->getMessage(), $e->getCode());
                return false;
            }
        }
        return true;
    }

    /**
     * 返回json
     * @param int $code
     * @param null $data
     * @param string $msg
     * @return bool
     */
    protected function returnJson($data = null, $msg = '', $code = 200)
    {
        if (!$this->response()->isEndResponse()) {
            $return = [
                'code' => $code,
                'data' => $data,
                'msg' => $msg
            ];
            $this->response()->write(json_encode($return, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        } else {
            trigger_error('response has end');
            return false;
        }
    }

    /**
     * @param $key
     * @param $rule
     * @param $data
     * @return mixed
     * @throws BadRequestException
     */
    protected function validateByRule($key, $rule, $data)
    {
        $rule['name'] = $key;
        $rs = Parser::format($key, $rule, $data);
        if ($rs === NULL && (isset($rule['require']) && $rule['require'])) {
            throw new BadRequestException("缺少必要参数{$key}");
        }
        return $rs;
    }

    /**
     * 输出json格式错误信息
     * @param $msg
     * @param $code
     */
    protected function errorJson($msg, $code)
    {
        if (!$this->response()->isEndResponse()) {
            $return = ['msg' => $msg];
            $this->response()->write(json_encode($return, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($code);
        }
    }

    /**
     * 允许Origin
     */
    protected function allowOrigin()
    {
        $origin = $this->request()->getHeaderLine('origin') ?: '*';
        $allow_origin = Config::getInstance()->getConf('ORIGIN_WHITE_LIST');
        if (in_array($origin, $allow_origin) || in_array('*', $allow_origin)) {
            $this->response()->withHeader('Access-Control-Allow-Origin', $origin);
            $this->response()->withHeader('Access-Control-Allow-Credentials', 'true');
            $this->response()->withHeader('Access-Control-Allow-Headers', 'requested-to, Content-Type');
            $this->response()->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        }
    }
}