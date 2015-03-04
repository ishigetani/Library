<?php
/**
 * Class ApiController
 * API用基幹クラス
 *
 * @author ishigetani <k.subaru1230@gmail.com>
 * @package app.Controller
 * @version 1.0
 * @url http://be-hase.com/php/478/
 */

App::uses('Component', 'Controller');

class ApiComponent extends Component {
    public $component = array('RequestHandler');

    protected $result = array();

    public function initialize(Controller $controller) {
        $this->controller = $controller;

        if (Configure::read('debug') == 0) {
            if (!$this->controller->request->is('ajax')) throw new BadRequestException('I will not allow only Ajax.');
        }

        $this->result['meta'] = array(
            'url' => $this->controller->request->here,
            'method' => $this->controller->request->method()
        );

        $this->controller->response->header('X-Content-Type-Options', 'nosniff');
        $this->controller->viewClass = 'Json';
    }

    /**
     * 成功系レスポンス
     * HttpStatus 200
     * @param array $response
     */
    public function success($response = array())
    {
        $this->result['response'] = $response;

        $this->controller->set('meta', $this->result['meta']);
        $this->controller->set('response', $this->result['response']);
        $this->controller->set('_serialize', array('meta', 'response'));
    }

    /**
     * 失敗系レスポンス
     * HttpStatus 400
     * @param string $message
     * @param string $code
     */
    public function error($message = '', $code = null)
    {
        $this->result['error']['message'] = $message;
        $this->result['error']['code'] = $code;

        $this->controller->response->statusCode(400);
        $this->controller->set('meta', $this->result['meta']);
        $this->controller->set('error', $this->result['error']);
        $this->controller->set('_serialize', array('meta', 'error'));
    }

    /**
     * バリデーションエラー用
     * HttpStatus 400
     * @param $modelName
     * @param array $validationErrors
     */
    public function validationError($modelName, $validationErrors = array())
    {
        $this->result['error']['message'] = 'Validation Error';
        $this->result['error']['code'] = '422';
        $this->result['meta']['data'] = $this->controller->request->data;
        $this->result['error']['validation'][$modelName] = array();
        foreach ($validationErrors as $value) {
            $this->result['error']['validation'][$modelName][] = $value[0];
        }

        $this->controller->response->statusCode(400);
        $this->controller->set('meta', $this->result['meta']);
        $this->controller->set('error', $this->result['error']);
        $this->controller->set('_serialize', array('meta', 'error'));
    }

    /**
     * meta情報追加
     *
     * @param null $key
     * @param null $value
     * @return bool
     */
    public function metaAdd($key = null, $value = null) {
        if (empty($key)) return false;
        $this->result['meta'][$key] = $value;
        return true;
    }
}