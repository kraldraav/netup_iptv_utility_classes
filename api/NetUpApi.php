<?php



namespace NetUp\Api;
$APILOGIN = 'apiuser';
$APIPASSWORD = '';
/**
 * Class NetUpApi
 *
 * @package NetUp\Api
 */
include_once('Exception.php');
include_once('HttpException.php');
include_once('InvalidJsonException.php');
include_once('InvalidLoginException.php');

class NetUpApi {

    /**
     * HTTP codes
     *
     * @var array
     */
    public static $codes = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing', // RFC2518
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // RFC4918
        208 => 'Already Reported', // RFC5842
        226 => 'IM Used', // RFC3229
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect', // RFC7238
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity', // RFC4918
        423 => 'Locked', // RFC4918
        424 => 'Failed Dependency', // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
        426 => 'Upgrade Required', // RFC2817
        428 => 'Precondition Required', // RFC6585
        429 => 'Too Many Requests', // RFC6585
        431 => 'Request Header Fields Too Large', // RFC6585
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)', // RFC2295
        507 => 'Insufficient Storage', // RFC4918
        508 => 'Loop Detected', // RFC5842
        510 => 'Not Extended', // RFC2774
        511 => 'Network Authentication Required', // RFC6585
    ];

    /**
     * DEFAULT RESOLUTION WIDTH
     */
    const SCREEN_WIDTH = 1920;

    /**
     * DEFAULT RESOLUTION HEIGHT
     */
    const SCREEN_HEIGHT = 1080;

    /**
     * DEFAULT MAX WIDTH ELEMENTS MOSAIC GENERATOR
     */
    const MAX_WIDTH_ELMS = 5;

    /**
     * DEFAULT MAX HEIGHT ELEMENTS MOSAIC GENERATOR
     */
    const MAX_HEIGHT_ELMS = 5;

    /**
     * Default http status code
     */
    const DEFAULT_STATUS_CODE = 200;

    /*     * *
     * Site address
     */

    /**
     * Url prefixes
     */
    private $URL_PREFIX = 'http://127.0.0.1/manager/api';

    /**
     * CURL object
     *
     * @var
     */
    protected $curl;

    /**
     * netup user login
     *
     * @var
     */
    protected $login;

    /**
     * netup user password
     *
     * @var
     */
    protected $password;

    /**
     * Check whether return associative array
     *
     * @var bool
     */
    protected $returnArray = true;

    /**
     * Constructor
     *
     * @param string $user netup login
     * @param string $ppwd netup password
     */
    public function __construct($user = NULL, $pwd = NULL) {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/cookie.txt');
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/cookie.txt');
        $this->login = ($user != NULL) ? $user : $GLOBALS['APILOGIN'];
        $this->password = ($pwd != NULL) ? $pwd : $GLOBALS['APIPASSWORD'];
    }

    /**
     * 
     * @return type Максимальная ширина разрешения установленная в константе SCREEN_WIDTH
     */
    public function GetMaxResolutionWidth() {
        return self::SCREEN_WIDTH;
    }

    /**
     * 
     * @return type Максимальная высота разрешения установленная в константе SCREEN_HEIGHT
     */
    public function GetMaxResolutionHeight() {
        return self::SCREEN_HEIGHT;
    }

    /**
     * 
     * @return type Максимальное количество элементов в мозайке по ширине
     */
    public function GetMaxWidthElm() {
        return self::MAX_WIDTH_ELMS;
    }

    /**
     * 
     * @return type Максимальное количество элементов в мозайке по высоте
     */
    public function GetMaxHeightElm() {
        return self::MAX_HEIGHT_ELMS;
    }

    /**
     * Call method
     *
     * @param string $method
     * @param array|null $data
     *
     * @return mixed
     * @throws \NetUp\Api\Exception
     * @throws \NetUp\Api\HttpException
     * @throws \NetUp\Api\InvalidJsonException
     */
    public function call($method, array $data = null, $json_encode = true) {
        $options = [
            CURLOPT_URL => $this->getUrl() . '/' . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_POST => null,
            CURLOPT_POSTFIELDS => null,
        ];

        if ($json_encode) {
            if ($data) {
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
            $response = self::jsonValidate($this->executeCurl($options), $this->returnArray);
        } else {
            if ($data) {
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $data;
            }
            $response = $this->executeCurl($data);
        }




        if ($this->returnArray) {
            if (isset($response['is_staff']) && $response['is_staff'] == 0) {
                throw new InvalidLoginException('Incorrect login or password!', $response['is_staff']);
            }
            return $response;
        }
        return $response;
    }

    /**
     * curl_exec wrapper for response validation
     *
     * @param array $options
     *
     * @return string
     *
     * @throws \NetUp\Api\HttpException
     */
    protected function executeCurl(array $options) {
        curl_setopt_array($this->curl, $options);
        $result = curl_exec($this->curl);
        self::curlValidate($this->curl, $result);
        if ($result === false) {
            //throw new HttpException(curl_error($this->curl), curl_errno($this->curl));
            $result = curl_errno($this->curl);
        }
        return $result;
    }

    protected function executeCurlNotJson(array $options) {
        curl_setopt_array($this->curl, $options);
        $result = curl_exec($this->curl);

        return $result;
    }

    /**
     * Response validation
     *
     * @param resource $curl
     * @param string $response
     * @throws HttpException
     */
    public static function curlValidate($curl, $response = null) {
        $json = json_decode($response, true) ?: [];
        if (($httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE)) && !in_array($httpCode, [self::DEFAULT_STATUS_CODE])
        ) {
            $errorDescription = array_key_exists('description', $json) ? $json['description'] : self::$codes[$httpCode];
            $errorParameters = array_key_exists('parameters', $json) ? $json['parameters'] : [];
            //throw new HttpException($errorDescription, $httpCode, null, $errorParameters);
        }
    }

    /**
     * JSON validation
     *
     * @param string $jsonString
     * @param boolean $asArray
     *
     * @return object|array
     * @throws \NetUp\Api\InvalidJsonException
     */
    public static function jsonValidate($jsonString, $asArray) {
        $json = json_decode($jsonString, $asArray);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidJsonException(json_last_error_msg(), json_last_error());
        }
        return $json;
    }

    /**
     * Authorization on the netup server
     *
     */
    public function Login() {
        return self::call('Staffs/login', ['login' => $this->login,
                    'password' => $this->password]);
    }

    public function addReceiver(array $data) {
        return self::call('Iptv/Relay/Nodes/createReceiver', $data);
    }

    public function deleteReceiver(array $data) {
        return self::call('Iptv/Relay/Nodes/delete', $data);
    }
    
    public function listReceiver(array $data){
        return self::call('Iptv/Relay/Nodes/list', $data);
    }

    public function deleteNode(array $data) {
        return self::call('Iptv/Relay/Nodes/delete', $data);
    }

    public function createRelay(array $data) {
        return self::call('Iptv/Relay/create', $data);
    }

    public function modifyRelay(array $data){
        return self::call('Iptv/Relay/modify', $data);
    }
    /**
     * Create mosaic
     * @param array $data 
     * @return array
     */
    public function createMosaic(array $data) {
        return self::call('Iptv/Relay/Mosaic/create', $data);
    }

    /** Add reciever to the mosaic
     * @param array $data
     * @return array
     */
    public function addReciever(array $data) {
        return self::call('Iptv/Relay/Mosaic/addReceiver', $data);
    }

    /** Delete reciever from mosaic
     * @param array $data
     * @return array
     */
    public function deleteReciever(array $data) {
        return self::call('Iptv/Relay/Mosaic/deleteReceiver', $data);
    }

    /**
     * Modify mosaic
     * @param array $data
     * @return array
     */
    public function modifyMosaic(array $data) {
        return self::call('Iptv/Relay/Mosaic/modify', $data);
    }

    /**
     * Get mosaic list from yhe netup server
     *
     */
    public function getMosaicList() {
        return self::call('Iptv/Relay/Mosaic/list', null);
    }

    /**
     * Delete mosaic
     */
    public function deleteMosaic(array $data) {
        return self::call('Iptv/Relay/Mosaic/delete', $data);
    }

    /**
     * Relay list
     */
    public function getRelayList() {
        return self::call('Iptv/Relay/list', null);
    }

    /**
     * GetAdaptivePlaylilsts
     */
    public function getAdaptivePlaylist() {
        return self::call('Iptv/Relay/AdaptivePlaylists/list', null);
    }

    public function createAdaptivePlaylist(array $data) {
        return self::call('Iptv/Relay/AdaptivePlaylists/create', $data);
    }

    public function deleteAdaptivePlaylist(array $data) {
        return self::call('Iptv/Relay/AdaptivePlaylists/delete', $data);
    }

    public function modifyAdaptivePlaylist(array $data) {
        return self::call('Iptv/Relay/AdaptivePlaylists/modify', $data);
    }

    public function addStreamToAdaptivePlaylist(array $data) {
        return self::call('Iptv/Relay/AdaptivePlaylists/addStream', $data);
    }

    /**
     * Presets list
     */
    public function getPresetList() {
        return self::call('Iptv/Relay/Presets/list', null);
    }

    public function createPreset(array $data) {
        return self::call('Iptv/Relay/Presets/create', $data);
    }

    public function modifyPreset(array $data) {
        return self::call('Iptv/Relay/Presets/modify', $data);
    }

    public function deletePreset(array $data) {
        return self::call('Iptv/Relay/Presets/delete', $data);
    }

    /**
     * Get Node List
     */
    public function getNodeList($type) {
        return self::call('Iptv/Relay/Nodes/list', $type);
    }

    public function getNodeInfo($data) {
        return self::call('Iptv/Relay/Nodes/info', $data);
    }

    public function createVodContent($data) {
        return self::call('Media/createVodContent', $data);
    }

    /**
     * Get Media file info
     */
    public function getRawInfo(array $data) {
        return self::call('Media/rawInfo', $data);
    }

    public function getTranscodeProfileList() {
        return self::call('Media/transcodeProfileList', null);
    }

    public function createProcessingContent(array $data) {
        return self::call('Media/createProcessingContent', $data);
    }

    public function modifyProcessingContent(array $data) {
        return self::call('Media/modifyProcessingContent', $data);
    }

    public function uploadlink(array $data) {
        return self::call('Upload/link', $data);
    }

    public function uploadMediaContent(array $data) {
        return self::call('Media/upload', $data);
    }

    public function processingContentList(array $data) {
        return self::call('Media/processingContentList', $data);
    }

    public function deleteProcessingContent(array $data) {
        return self::call('Media/deleteProcessingContent', $data);
    }

    public function resetProcessingContent(array $data) {
        return self::call('Media/resetProcessingContent', $data);
    }

    public function stopTranscode(array $data) {
        return self::call('Media/stopTranscode', $data);
    }

    public function getDvbAdapters() {
        return self::call('DvbAdapters/list', null);
    }

    /**
     * Обновит тарифы по подключенным шаблонам услуг.
     *
     * @param array $params
     * <pre>
     * {
     *      'tariff_plan_id'*: int,
     *      'media_content_code_list'*: array,
     *      'services_name_templates': array {
     *          'IPTV_VOD': string,
     *          'IPTV_NVOD': string
     *      }
     * }
     * </pre>
     * <br><b>tariff_plan_id</b> - ID тарифного плана.
     * <br><b>media_content_code_list</b> - массив медиа-контента.
     * <br><b>services_name_templates</b> - шаблон названия услуги. К примеру: Access to movie "%resourceName%" in virtual cinema
     * <br><br>В метод нужно передать или 'tariff_plan_id' или 'media_content_code'. В случае присутствия этих параметров одновременно, вернет ошибку.
     * <br><br>В шаблоне названия услуги, подстрока %resourceName% заменится на название медиа-контента.
     * @return array
     * <pre>{ result: bool }</pre>
     * <br><b>result</b> - в случае успеха - true, иначе - false
     * @static
     */
    public function updateIptvTemplates(array $data) {
        return self::call('/Tariffs/Plans/updateIptvTemplates', $data);
    }

    public function getStaffList() {
        $data = [];
        return self::call('Staffs/list', $data);
    }

    public function getCustomerList(array $data) {
        return self::call('Customers/list', $data);
    }
    
    public function getCustomerInfo(array $data) {
        return self::call('Customers/info', $data);
    }

    /**
     * 
     * @param type $method
     * Название API метода (например Staffs/login
     * @param type $data
     * Данные для передачи методу
     * @param type $encode
     * TRUE если хотим передавать в формате JSON (по-умолчанию)
     * @return type
     */
    public function CallApiMethod($method, $data, $encode) {
        return self::call($method, $data, $encode);
    }

    /**
     * Close curl
     */
    public function __destruct() {
        $this->curl && curl_close($this->curl);
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->URL_PREFIX;
    }

    public function changeUrl($url) {
        $this->URL_PREFIX = $url;
    }

    public function changeIpUrl($ip) {
        $this->URL_PREFIX = 'http://' . $ip . '/relay/api';
    }

}
