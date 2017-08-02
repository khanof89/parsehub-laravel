<?php
/**
 * Created by PhpStorm.
 * User: shahrukhkhan
 * Date: 31/07/17
 * Time: 4:38 PM
 */

namespace Shahrukh\Parsehub;


use GuzzleHttp\Client;

class Parsehub
{

    const GET = 'get';
    const POST = 'post';

    public static $apiKey;

    public static $baseURI = 'https://www.parsehub.com/api/v2/';

    public static $client;

    /**
     * Parsehub constructor.
     */
    public function __construct()
    {
        self::$client = new Client();
        self::$apiKey = env('PARSEHUB_KEY');
    }

    /**
     * This will return the project object for a specific project.
     *
     * @param $projectToken
     * @return string
     * @throws ProjectTokenNotFoundException
     */
    public static function getProject($projectToken)
    {
        if(!$projectToken)
        {
            throw new ProjectTokenNotFoundException('Please provide a project token');
        }

        $url = self::buildProjectUri($projectToken);

        return self::httpRequest($url, self::GET);
    }

    /**
     * This gets a list of projects in your account.
     * You may pass the following optional values as an array:
     *
     * offset = Specifies the offset from which to start the projects.
     *          E.g. in order to get projects 21-40, specify an offset
     *          of 20. Defaults to 0.
     *
     * limit = Specifies how many entries will be returned in projects.
     *         Accepts values between 1 and 20 inclusively. Defaults to 20.
     *
     * include_options = Adds options_json, main_template, main_site and webhook
     *                   to the entries of projects. Set this parameter to 1 if you
     *                   intend to use them in ParseHub API calls. This parameter requires
     *                   use of the offset and limit parameters to access the full list of projects.
     * @return string
     */
    public static function getProjects()
    {
        $url = self::buildProjectUri();

        return self::httpRequest($url, self::GET);
    }

    /**
     * This will start running an instance of the project on the ParseHub cloud.
     * It will create a new run object.
     * This method will return immediately, while the run continues in the background.
     * You can use webhooks or polling to figure out when the data for this run is ready
     * in order to retrieve it. You may pass following values as an array:
     *
     * start_url
     * start_template
     * start_value_override
     *
     * @param $projectToken
     * @param array $params
     * @return string
     */
    public static function runProject($projectToken, $params = [])
    {
        $url = self::buildProjectUri($projectToken, $params);
        return self::httpRequest($url, self::POST, $params);
    }

    /**
     * @param $runToken
     * @return string
     */
    public static function getRun($runToken)
    {
        $url = self::buildRunUri($runToken);
        return self::httpRequest($url, self::GET);
    }

    /**
     * @param $runToken
     * @return string
     */
    public static function getRunData($runToken)
    {
        $url = self::buildRunUri($runToken, $options = [], 'data');
        return self::httpRequest($url, self::GET);
    }

    public static function getLastReadyRunData($projectToken)
    {
        $url = self::buildProjectUri($projectToken, $options = [], $extras = ['last_ready_run', 'data']);
        return self::httpRequest($url, self::GET);
    }

    public static function cancelRun($runToken)
    {
        $url = self::buildRunUri($runToken, $options = [], $extras = ['cancel']);
        return self::httpRequest($url, self::POST);
    }

    /**
     * @param string $projectToken
     * @param array $options
     * @return string
     */
    protected static function buildProjectUri($projectToken ='', $options = [], $extras = [])
    {
        $url = self::$baseURI.'projects';
        if($projectToken)
        {
            $url .= DIRECTORY_SEPARATOR. $projectToken;
        }
        if($extras)
        {
            $url .= implode('/', $extras);
        }
        if($options)
        {
            $url .= http_build_query($options);
        }
        $url .= '?api_key='. self::$apiKey;
        return $url;
    }

    /**
     * @param string $runToken
     * @param array $options
     * @return string
     */
    protected static function buildRunUri($runToken ='', $options = [], $extras = [])
    {
        $url = self::$baseURI.'runs';
        if($runToken)
        {
            $url .= DIRECTORY_SEPARATOR. $runToken;
        }
        if($extras)
        {
            $url .= implode('/', $extras);
        }
        if($options)
        {
            $url .= http_build_query($options);
        }
        $url .= '?api_key='.self::$apiKey;
        return $url;
    }

    /**
     * @param $url
     * @param $method
     * @return string
     */
    public static function httpRequest($url, $method, $params = [])
    {
        if($method === self::GET) {
            $request = self::$client->request($method, $url);
        }
        else
        {
            $request = self::$client->request($method, $url, $params);
        }

        $response             = new \stdClass();
        $response->statusCode = $request->getStatusCode();
        $response->headers    = $request->getHeader('content-type');
        $response->body       = json_decode($request->getBody());

        return json_encode($response);
    }
}